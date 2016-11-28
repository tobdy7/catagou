<?php
	/*
	concepte multi link
	si c'est un multi link je pense qu'il faut faire plusieurs entrées avec un id multi commun
	
	
	
	*/
	function displayLink(){
		$db = loadDB(__FUNCTION__);
		
		$stmt = $db->prepare('SELECT id, link, imgLink, name, type FROM link WHERE user_id = :user_id AND type < 3');
		$stmt->bindParam(':user_id', $_SESSION['id']);
		try{
			$stmt->execute();
		}catch (Exception $e){
			alertError($e->getMessage(), __FUNCTION__);
		}
		
		$linkString = '';
		
		while($result = $stmt->fetch()){
			$stringMulti = '';
			if($result['type'] == 1){
				$stmt2 = $db->prepare('SELECT link FROM link WHERE id_multi = :id');
				$stmt2->bindParam(':id', $result['id']);
				try{
					$stmt2->execute();
				}catch (Exception $e){
					alertError($e->getMessage(), __FUNCTION__);
				}
				
				while($result2 = $stmt2->fetch()){
					$stringMulti .= 'window.open(\''.$result2['link']."','_blank');";
				}
			}
			$linkString .= '<div class="divSite divSite-style'.$result['type'].'" title="'.$result['name'].'" id="divLink'.$result['id'].'">
					<img src="'.$result['imgLink'].'"  height="100" width="180" alt="'.$result['name'].'">
					<div class="divOverlay" onclick="window.open(\''.$result['link']."','_blank');".$stringMulti.'" onMouseOver="showEditButton('.$result['id'].')" onmouseout="hideEditButton('.$result['id'].')">
					</div>
					<div id="buttonEdit'.$result['id'].'" class="hidden" onclick="editLink(\''.$result['link'].'\', \''.$result['imgLink'].'\', \''.$result['name'].'\', \''.$result['id'].'\')" onMouseOver="showEditButton('.$result['id'].')">
						&#9998;
					</div>
				</div>';
		}
		
		unset($stmt);
		
   		return $linkString;
	}
	
	function addLink($link, $imgLink, $name, $type, $idMulti = NULL){
		if(isset($link) && $link != '[DELETED]'){
			if(!isset($imgLink)){$imgLink ='[NONE]';}
			if(!isset($name)){$name ='[NONE]';}else{$name=htmlspecialchars($name);}
			if($type == 0 OR $type == 1 OR $type==2 OR $type==3){
				$db = loadDB(__FUNCTION__);
				
				$stmt = $db->prepare("INSERT INTO link (user_id, link, imgLink, name, type, id_multi) VALUES (:user_id, :link, :imgLink, :name, :type, :id_multi);");
				$stmt->bindParam(':user_id', $_SESSION['id']);
				$stmt->bindParam(':link', $link);
				$stmt->bindParam(':imgLink', $imgLink);
				$stmt->bindParam(':name', $name);
				$stmt->bindParam(':type', $type);
				$stmt->bindParam(':id_multi', $idMulti);
				
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
				if($_POST['linkType'.$i] == 1){
					$db = loadDB('synchroMultiLinkNew');
					//grosse merde pasqu'il faut que je chop l'id et y a une possibilité d'avoire du doublon ducoup ce que je fait je prend le plus grand id
					$stmt = $db->prepare('SELECT id FROM link WHERE user_id = :user_id ORDER BY id DESC LIMIT 1');
					$stmt->bindParam(':user_id', $_SESSION['id']);
					try{
						$stmt->execute();
					}catch (Exception $e){
						alertError($e->getMessage(), __FUNCTION__);
					}
					$result = $stmt->fetch();
					
					//linkMulti'+h+'New'+i
					
					$j = 0;
					while(isset($_POST['linkMulti'.$j.'New'.$i])){
						addLink($_POST['linkMulti'.$j.'New'.$i], '', '', '3', $result['id']);
						$j++;
					}
					
					
					unset($stmt);
					unset($db);
				}
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
			document.getElementById('formUpdateLink').className = 'hidden';
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
			if(selectType==1){
				var nbrMultiLink = document.getElementById('nbrMultiNew').value;
			}
			var i = document.getElementById('nbrNew').value;//nbr de new link déjà prép pour la désynchro
			
			if(i=='0'){
				prepForDesyncho('newLink', 'True');
			}
			
			prepForDesyncho('linkNew'+i, inputLink);
			prepForDesyncho('linkImg'+i, inputImg);
			prepForDesyncho('linkName'+i, inputName);
			prepForDesyncho('linkType'+i, selectType);
			
			//rajout des multiLink
			if(selectType==1){
				var nbrMultiLink = document.getElementById('nbrMultiNew').value;
				for(var h = 0;h<=nbrMultiLink;h++){
					var valueMultiLink = document.getElementById('linkMultiNew'+h).value;
					//faut indiquer le nbr de link ou on peut juste faire avec un while en faite
					prepForDesyncho('linkMulti'+h+'New'+i, valueMultiLink);
				}
			}
			
			document.getElementById('linkNew').value = "";
			document.getElementById('linkImg').value = "";
			document.getElementById('linkName').value = "";
			document.getElementById('linkType').value = "0";
			j = Number(i);
			j++;
			document.getElementById('nbrNew').value = j;
			
			//recache le formulaire
			document.getElementById('formNewLink').className = 'hidden';
			
			//rajout des autres liens en cas de multi link
			var stringMultiLink = "";
			if(selectType==1){
				for(var h = 0;h<=nbrMultiLink;h++){
					var valueMultiLink = document.getElementById('linkMultiNew'+h).value;
					
					stringMultiLink += "window.open('"+valueMultiLink+"','_blank');"
					
					document.getElementById('linkMultiNew'+h).value = "";
					//le truc c'est qu'il faut remettre à zéro donc supprimer la div et la recéer quoi...
				}
				//remise à zéro pour les multi
				document.getElementById('additionalNewLink').innerHTML = '<input type="hidden" id="nbrMultiNew" value="0"><input type="text" id="linkMultiNew0" class="formInput" value="" onKeyUp="inputKeyUp(event, \'saveLink\')" placeholder="Lien 2">';
				
				document.getElementById('additionalNewLink').className = 'hidden';
				document.getElementById('buttonAddMultiLink').className = 'hidden';
			}
			
			
			//implémentation du nouveau élément
			document.getElementById('linkDisplay').innerHTML += '<div class="divSite divSite-style'+selectType+'" title="'+inputName+'"  id="divLink-'+i+'"><img src="'+inputImg+'"  height="100" width="180" alt="'+inputName+'"><div class="divOverlay" onclick="window.open(\''+inputLink+"','_blank');"+stringMultiLink+'" onMouseOver="showEditButton(\'-'+i+'\')" onmouseout="hideEditButton(\'-'+i+'\')"></div><div id="buttonEdit-'+i+'" class="hidden" onclick="editLink(\''+inputLink+'\', \''+inputImg+'\', \''+inputName+'\', \'-'+i+'\')" onMouseOver="showEditButton(\'-'+i+'\')">&#9998;</div></div>';
			
			
			
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
			document.getElementById('formNewLink').className = 'hidden';
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
				i = idUpdate;
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
			
			var id = document.getElementById('idUpdate').value;
			var j = Number(document.getElementById('nbrDelete').value);
			if(Number(id) <= 0){
				i = -Number(id);
				document.getElementById('linkNew'+i).value = '[DELETED]';
				document.getElementById('linkImg'+i).value = '[DELETED]';
				document.getElementById('linkNew'+i).value = '[DELETED]';
				document.getElementById('linkType'+i).value = '[DELETED]';
				i = -i;
			}else{
				if(j == 0){
					prepForDesyncho('deleteLink', 'True');
				}
				prepForDesyncho('idDelete'+j, id);
				j++;
				document.getElementById('nbrDelete').value = j;
			}
			
			document.getElementById('divLink'+id).className = 'hidden';
			
			document.getElementById('linkUpdate').value = "";
			document.getElementById('imgLinkUpdate').value = "";
			document.getElementById('nameUpdate').value = "";
			document.getElementById('idUpdate').value = '0';
			
			document.getElementById('formUpdateLink').className = 'hidden';
		}
		function cancelEdit(){
			document.getElementById('formUpdateLink').className = 'hidden';
			document.getElementById('formNewLink').className = 'hidden';
			
			//remise à zéro pour les multi
			document.getElementById('additionalNewLink').innerHTML = '<input type="hidden" id="nbrMultiNew" value="0"><input type="text" id="linkMultiNew0" class="formInput" value="" onKeyUp="inputKeyUp(event, \'saveLink\')" placeholder="Lien 2">';
		}
		
		function linkChangeType(){
			var selectType = document.getElementById('linkType').value;
			if(selectType == 1){
				document.getElementById('buttonAddMultiLink').className = "buttonSmall";
				document.getElementById('additionalNewLink').className = "";
			}else{
				document.getElementById('buttonAddMultiLink').className = "hidden";
				document.getElementById('additionalNewLink').className = "hidden";
			}
		}
		
		function addMultiLink(){
			var i = document.getElementById('nbrMultiNew').value;
			i++;
			var string = '<input type="hidden" id="nbrMultiNew" value="'+i+'">';
			for(var j=0;j<i;j++){
				var value = document.getElementById('linkMultiNew'+j).value;
				string += '<input type="text" id="linkMultiNew'+j+'" class="formInput" value="'+value+'" onKeyUp="inputKeyUp(event, \'saveLink\')" placeholder="Lien '+(j+2)+'">';
			}
			document.getElementById('additionalNewLink').innerHTML = string + '<input type="text" id="linkMultiNew'+i+'" class="formInput" value="" onKeyUp="inputKeyUp(event, \'saveLink\')" placeholder="Lien '+(i+2)+'">';
		}
		
		
</script>

<div class="divAdd" onclick="newLink();">
	&#65122;<!-- + -->
</div>

<!--ADD a new link-->
<div class="hidden" id="formNewLink">
	<select id="linkType" onKeyUp="inputKeyUp(event, 'saveLink')" onchange="linkChangeType()" form="newLinkForm">
		<option value="0">normal</option>
		<option value="1">multi-lien</option>
		<option value="2">dossier</option>
	</select>
	<button onclick="addMultiLink()"  id="buttonAddMultiLink" class="hidden">&#65122;<!-- + --></button>
	<form id="newLinkForm">
		<input type="text" id="linkNew" class="formInput" onKeyUp="inputKeyUp(event, 'saveLink')" placeholder="Lien">
		<div id="additionalNewLink" class="hidden">
			<input type="hidden" id="nbrMultiNew" value="0">
			<input type="text" id="linkMultiNew0" class="formInput" value="" onKeyUp="inputKeyUp(event, 'saveLink')" placeholder="Lien 2">
		</div>
		<input type="text" id="linkImg" class="formInput" onKeyUp="inputKeyUp(event, 'saveLink')" placeholder="Lien vignette">
		<input type="text" id="linkName" class="formInput" onKeyUp="inputKeyUp(event, 'saveLink')" placeholder="Nom">
		<input type="hidden" id="nbrNew" value="0">
	</form>
	
	<button onclick="cancelEdit()">Annuler</button>
</div>

<!--UPDATE a link-->
<div class="hidden" id="formUpdateLink">
	<form id="updateLinkForm">
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