<?php
	
	function displayLink(){
		$db = loadDB(__FUNCTION__);
		
		$stmt = $db->prepare('SELECT id, link, imgLink, name, type FROM link WHERE user_id = :user_id');
		$stmt->bindParam(':user_id', $_SESSION['id']);
    	try{
			$stmt->execute();
		}catch (Exception $e){
			alertError($e->getMessage(), __FUNCTION__);
		}
		
		$linkString = '';
		
    	while($result = $stmt->fetch()){
    		$linkString .= '<div class="divSite divSite-style'.$result['type'].'" title="'.$result['name'].'" id="divLink'.$result['id'].'">
				<img src="'.$result['imgLink'].'"  height="100" width="180" alt="'.$result['name'].'">
				<div class="divOverlay" onclick="window.open(\''.$result['link']."','_blank');".'" onMouseOver="showEditButton('.$result['id'].')" onmouseout="hideEditButton('.$result['id'].')">
				</div>
				<div id="buttonEdit'.$result['id'].'" class="hidden" onclick="editLink(\''.$result['link'].'\', \''.$result['imgLink'].'\', \''.$result['name'].'\', \''.$result['id'].'\')" onMouseOver="showEditButton('.$result['id'].')">
					&#9998;
				</div>
			</div>';
    	}
		
		unset($stmt);
		
   		return $linkString;
	}
	
	function addLink($link, $imgLink, $name, $type){
		if(isset($link) && $link != '[DELETED]'){
			if(!isset($imgLink)){$imgLink ='[NONE]';}
			if(!isset($name)){$name ='[NONE]';}else{$name=htmlspecialchars($name);}
			if($type == 0 OR $type == 1 OR $type==2){
				$db = loadDB(__FUNCTION__);
				
				$stmt = $db->prepare("INSERT INTO link (user_id, link, imgLink, name, type) VALUES (:user_id, :link, :imgLink, :name, :type);");
				$stmt->bindParam(':user_id', $_SESSION['id']);
				$stmt->bindParam(':link', $link);
				$stmt->bindParam(':imgLink', $imgLink);
				$stmt->bindParam(':name', $name);
				$stmt->bindParam(':type', $type);
				
				try{
					$stmt->execute();
				}catch (Exception $e){
					alertError($e->getMessage(), __FUNCTION__);
					die();
				}
				unset($stmt);
				return True;
			}
		}
	}
	
	function updateLink($link, $imgLink, $name, $id){
		if(isset($link)){
			if(!isset($imgLink)){$imgLink ='[NONE]';}
			if(!isset($name)){$name ='[NONE]';}else{$name=htmlspecialchars($name);}
			$db = loadDB(__FUNCTION__);
			
			$stmt = $db->prepare("UPDATE link SET link = :link, imgLink = :imgLink, name = :name WHERE id = :id ;");
			$stmt->bindParam(':link', $link);
			$stmt->bindParam(':imgLink', $imgLink);
			$stmt->bindParam(':name', $name);
			$stmt->bindParam(':id', $id);
				
			try{
				$stmt->execute();
			}catch (Exception $e){
				alertError($e->getMessage(), __FUNCTION__);
				die();
			}
			unset($stmt);
			return True;
		}
	}
	
	function deleteLink($id){
		$db = loadDB(__FUNCTION__);
		
		$stmt = $db->prepare('DELETE FROM link WHERE id = :id');
		$stmt->bindParam(':id', $id);
		
    	try{
			$stmt->execute();
		}catch (Exception $e){
			alertError($e->getMessage(), __FUNCTION__);
		}
		
		unset($stmt);
	}
	
	
	
	//en cas de nouveau lien
	if(isset($_POST['synchro'])){
		if(isset($_POST['newLink'])){
			$i = 0;
			while(isset($_POST['linkNew'.$i])){
				//entrée dans la bdd
				addLink($_POST['linkNew'.$i], $_POST['linkImg'.$i], $_POST['linkName'.$i], $_POST['linkType'.$i]);
				$i++;
			}
			
		}
		
		if(isset($_POST['updateLink'])){
			$i = 0;
			while(isset($_POST['linkUpdate'.$i])){
				updateLink($_POST['linkUpdate'.$i], $_POST['linkUpdateImg'.$i], $_POST['linkUpdateName'.$i], $_POST['idUpdate'.$i]);
				$i++;
			}
		}
		
		if(isset($_POST['deleteLink'])){
			$i = 0;
			while(isset($_POST['idDelete'.$i])){
				deleteLink($_POST['idDelete'.$i]);
				$i++;
			}
		}
	}
	
?>

<script type="text/javascript">
		//affiche le formulaire pour ajouter un lien
		function newLink(){
			document.getElementById('formNewLink').className = 'divFormLink';
			getFocus('linkNew');
		}
		
		//enregistre un lien
		function saveLink(){
			waitForSynch();
			
			var inputLink = document.getElementById('linkNew').value;
			var inputImg = document.getElementById('linkImg').value;
			var inputName = document.getElementById('linkName').value;
			var selectType = document.getElementById('linkType').value;
			var i = document.getElementById('nbrNew').value;//nbr de new link déjà prép pour la désynchro
			
			if(i=='0'){
				prepForDesyncho('newLink', 'True');
			}
			
			prepForDesyncho('linkNew'+i, inputLink);
			prepForDesyncho('linkImg'+i, inputImg);
			prepForDesyncho('linkName'+i, inputName);
			prepForDesyncho('linkType'+i, selectType);
			
			document.getElementById('linkNew').value = "";
			document.getElementById('linkImg').value = "";
			document.getElementById('linkName').value = "";
			document.getElementById('linkType').value = "0";
			j = Number(i);
			j++;
			document.getElementById('nbrNew').value = j;
			
			//recache le formulaire
			document.getElementById('formNewLink').className = 'hidden';
			
			
			document.getElementById('linkDisplay').innerHTML += '<div class="divSite divSite-style'+selectType+'" title="'+inputName+'"  id="divLink-'+i+'"><img src="'+inputImg+'"  height="100" width="180" alt="'+inputName+'"><div class="divOverlay" onclick="window.open(\''+inputLink+"','_blank');"+'" onMouseOver="showEditButton(\'-'+i+'\')" onmouseout="hideEditButton(\'-'+i+'\')"></div><div id="buttonEdit-'+i+'" class="hidden" onclick="editLink(\''+inputLink+'\', \''+inputImg+'\', \''+inputName+'\', \'-'+i+'\')" onMouseOver="showEditButton(\'-'+i+'\')">&#9998;</div></div>';
			
			
			
		}
		
		//affiche le bouton pour editer au passage de la souris sur la div
		function showEditButton(id){
			document.getElementById('buttonEdit'+id).className = 'divEdit';
		}
		//cache le bouton quand la souris sort de la div
		function hideEditButton(id){
			document.getElementById('buttonEdit'+id).className = 'hidden';
		}
		
		//affiche le fomulaire d'edition des liens
		function editLink(link, imgLink, name, id){
			//gives the value to the inputs
			document.getElementById('linkUpdate').value = link;
			document.getElementById('imgLinkUpdate').value = imgLink;
			document.getElementById('nameUpdate').value = name;
			document.getElementById('idUpdate').value = id;
			//display the form
			document.getElementById('formUpdateLink').className = 'divFormLink';
			getFocus('linkUpdate');
		}
		
		function saveUpdateLink(){
			waitForSynch();
			
			var linkUpdate = document.getElementById('linkUpdate').value;
			var imgLinkUpdate = document.getElementById('imgLinkUpdate').value;
			var nameUpdate = document.getElementById('nameUpdate').value;
			var idUpdate = document.getElementById('idUpdate').value;
			var i = Number(idUpdate);
			var nbrUpdate = Number(document.getElementById('nbrUpdate').value);
			
			if(i <= 0){
				//c'est un lien qui n'est pas sauvegarder donc faut modifier directement les input pasqu'on a pas l'id réel
				i = -i;
				document.getElementById('linkNew'+i).value = linkUpdate;
				document.getElementById('linkImg'+i).value = imgLinkUpdate;
				document.getElementById('linkName'+i).value = nameUpdate;
				i = -i;
			}else{
				var j = nbrUpdate;
				if(j == 0){
					prepForDesyncho('updateLink', 'True');
				}
				prepForDesyncho('idUpdate'+j, idUpdate);
				prepForDesyncho('linkUpdate'+j, linkUpdate);
				prepForDesyncho('linkUpdateImg'+j, imgLinkUpdate);
				prepForDesyncho('linkUpdateName'+j, nameUpdate);
				//reste le "backend" à faire
				
				j++;
				
				document.getElementById('nbrUpdate').value = j;
			}
			//implementaton de l'action en js
			//document.getElementById('divLink'+i).title = inputName;
			document.getElementById('divLink'+i).innerHTML = '<img src="'+imgLinkUpdate+'"  height="100" width="180" alt="'+nameUpdate+'"><div class="divOverlay" onclick="window.open(\''+linkUpdate+"','_blank');"+'" onMouseOver="showEditButton(\''+i+'\')" onmouseout="hideEditButton(\''+i+'\')"></div><div id="buttonEdit'+i+'" class="hidden" onclick="editLink(\''+linkUpdate+'\', \''+imgLinkUpdate+'\', \''+nameUpdate+'\', \''+i+'\')" onMouseOver="showEditButton(\''+i+'\')">&#9998;</div>';
			
			
			document.getElementById('linkUpdate').value = "";
			document.getElementById('imgLinkUpdate').value = "";
			document.getElementById('nameUpdate').value = "";
			document.getElementById('idUpdate').value = '0';
			
			document.getElementById('formUpdateLink').className = 'hidden';
			
		}
		function delLink(){
			waitForSynch();
			
			var i = Number(document.getElementById('idUpdate').value);
			var j = Number(document.getElementById('nbrDelete').value);
			if(i <= 0){
				i = -i;
				document.getElementById('linkNew'+i).value = '[DELETED]';
				document.getElementById('linkImg'+i).value = '[DELETED]';
				document.getElementById('linkNew'+i).value = '[DELETED]';
				document.getElementById('linkType'+i).value = '[DELETED]';
				i = -i;
			}else{
				if(j == 0){
					prepForDesyncho('deleteLink', 'True');
				}
				prepForDesyncho('idDelete'+j, i);
				j++;
				document.getElementById('nbrDelete').value = j;
			}
			
			document.getElementById('divLink'+i).className = 'hidden';
			
			document.getElementById('linkUpdate').value = "";
			document.getElementById('imgLinkUpdate').value = "";
			document.getElementById('nameUpdate').value = "";
			document.getElementById('idUpdate').value = '0';
			
			document.getElementById('formUpdateLink').className = 'hidden';
		}
		function cancelEdit(){
			document.getElementById('formUpdateLink').className = 'hidden';
			document.getElementById('formNewLink').className = 'hidden';
		}
		
		
		
		
</script>

<div class="divAdd" onclick="newLink();">
	&#65122;<!-- + -->
</div>

<!--ADD a new link-->
<div class="hidden" id="formNewLink">
	<form id="newLinkForm" method="POST">
		<input type="text" id="linkNew" class="formInput" onKeyUp="inputKeyUp(event, 'saveLink')" placeholder="Lien">
		<input type="text" id="linkImg" class="formInput" onKeyUp="inputKeyUp(event, 'saveLink')" placeholder="Lien vignette">
		<input type="text" id="linkName" class="formInput" onKeyUp="inputKeyUp(event, 'saveLink')" placeholder="Nom">
		<input type="hidden" id="nbrNew" value="0">
	</form>
	<select id="linkType" onKeyUp="inputKeyUp(event, 'saveLink')" form="newLinkForm">
		<option value="0">normal</option>
		<option value="1">multi-lien</option>
		<option value="2">dossier</option>
	</select>
	<button onclick="cancelEdit()">Annuler</button>
</div>

<!--UPDATE a link-->
<div class="hidden" id="formUpdateLink">
	<form id="updateLinkForm" method="POST">
		<input type="text" id="linkUpdate" class="formInput" onKeyUp="inputKeyUp(event, 'saveUpdateLink')" placeholder="Lien">
		<input type="text" id="imgLinkUpdate" class="formInput" onKeyUp="inputKeyUp(event, 'saveUpdateLink')" placeholder="Lien vignette">
		<input type="text" id="nameUpdate" class="formInput" onKeyUp="inputKeyUp(event, 'saveUpdateLink')" placeholder="nom">
		<input type="hidden" id="idUpdate" value="0">
		<input type="hidden" id="nbrUpdate" value="0">
		<input type="hidden" id="nbrDelete" value="0">
		
		<input type="hidden" name="page" value="link">
	</form>
	<button onclick="cancelEdit()">Annuler</button>
	<button class="buttonFormLink" onclick="delLink()">Suprimer</button>
</div>
<div id="linkDisplay">
<?php
	$links = displayLink();
	echo $links;
?>
</div>