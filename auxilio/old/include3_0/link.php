<?php
	
	function displayLink(){
		$db = loadDB(__FUNCTION__);
		
		$stmt = $db->prepare('SELECT * FROM link');
		
    	try{
			$stmt->execute();
		}catch (Exception $e){
			alertError($e->getMessage(), __FUNCTION__);
		}
		
		$linkString = '';
		
    	while($result = $stmt->fetch()){
    		$linkString .= '<div class="divSite divSite-style'.$result['type'].'" title="'.$result['name'].'">
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
		if(isset($link)){
			if(!isset($imgLink)){$imgLink ='[NONE]';}
			if(!isset($name)){$name ='[NONE]';}else{$name=htmlspecialchars($name);}
			if($type == 0 OR $type == 1 OR $type==2){
				$db = loadDB(__FUNCTION__);
				
				$stmt = $db->prepare("INSERT INTO link (link, imgLink, name, type) VALUES (:link, :imgLink, :name, :type);");
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
			
			$stmt = $db->prepare("Update link SET link = :link, imgLink = :imgLink, name = :name WHERE id = :id ;");
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
	
	
	
	
	if(isset($_POST['link'])){
		addLink($_POST['link'], $_POST['imgLink'], $_POST['name'], $_POST['type']);
	}
	if(isset($_POST['linkUpdate'])){
		updateLink($_POST['linkUpdate'], $_POST['imgLinkUpdate'], $_POST['nameUpdate'], $_POST['idUpdate']);
	}
	if(isset($_POST['idSupr'])){
		deleteLink($_POST['idSupr']);
	}
	
?>

<script type="text/javascript">
		function newLink(){
			document.getElementById('newLink').className = 'divFormLink';
			getFocus('linkNew');
			//document.getElementById('linkNew').focus();
		}
		
		function saveLink(){
			document.getElementById('newLinkForm').submit();
		}
		
		function showEditButton(id){
			document.getElementById('buttonEdit'+id).className = 'divEdit';
		}
		function hideEditButton(id){
			document.getElementById('buttonEdit'+id).className = 'hidden';
		}
		
		function editLink(link, imgLink, name, id){
			//gives the value to the inputs
			document.getElementById('linkUpdate').value = link;
			document.getElementById('imgLinkUpdate').value = imgLink;
			document.getElementById('nameUpdate').value = name;
			document.getElementById('idUpdate').value = id;
			document.getElementById('idSupr').value = id;
			//display the form
			document.getElementById('updateLink').className = 'divFormLink';
			getFocus('linkUpdate');
		}
		function saveUpdateLink(){
			document.getElementById('updateLinkForm').submit();
		}
		function delLink(){
			document.getElementById('delForm').submit();
		}
		function cancelEdit(){
			document.getElementById('updateLink').className = 'hidden';
			document.getElementById('newLink').className = 'hidden';
		}
		
		
</script>

<div class="divAdd" onclick="newLink();">
	&#65122;<!-- + -->
</div>

<!--ADD a new link-->
<div class="hidden" id="newLink">
	<form id="newLinkForm" method="POST">
		<input type="text" id="linkNew" name="link" class="formInput" onKeyUp="inputKeyUp(event, 'saveLink')" placeholder="Lien">
		<input type="text" name="imgLink" class="formInput" onKeyUp="inputKeyUp(event, 'saveLink')" placeholder="Lien vignette">
		<input type="text" name="name" class="formInput" onKeyUp="inputKeyUp(event, 'saveLink')" placeholder="nom">
		<input type="hidden" name="page" value="link">
	</form>
	<select name="type" onKeyUp="inputKeyUp(event, 'saveLink')" form="newLinkForm">
		<option value="0">normal</option>
		<option value="1">multi-lien</option>
		<option value="2">dossier</option>
	</select>
	<button onclick="cancelEdit()">Annuler</button>
</div>

<!--UPDATE a link-->
<div class="hidden" id="updateLink">
	<form id="updateLinkForm" method="POST">
		<input type="text" id="linkUpdate" name="linkUpdate" class="formInput" onKeyUp="inputKeyUp(event, 'saveUpdateLink')" placeholder="Lien">
		<input type="text" id="imgLinkUpdate" name="imgLinkUpdate" class="formInput" onKeyUp="inputKeyUp(event, 'saveUpdateLink')" placeholder="Lien vignette">
		<input type="text" id="nameUpdate" name="nameUpdate" class="formInput" onKeyUp="inputKeyUp(event, 'saveUpdateLink')" placeholder="nom">
		<input type="hidden" id="idUpdate" name="idUpdate" value="0">
		
		<input type="hidden" name="page" value="link">
	</form>
	<button onclick="cancelEdit()">Annuler</button>
	<button class="buttonFormLink" onclick="delLink()">Suprimer</button>
	<form method="POST" id="delForm">
		<input type="hidden" id="idSupr" name="idSupr" value="0">
	</form>
</div>
<?php
	$links = displayLink();
	echo $links;
?>