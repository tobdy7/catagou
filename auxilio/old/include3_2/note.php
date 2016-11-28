<?php
	function addNote($noteValue){
		if($noteValue != '[DELETED]'){
			$noteValue = htmlspecialchars($noteValue);
			if($noteValue == ''){
				$noteValue = htmlspecialchars('[EMPTY]');
			}
			$db = loadDB(__FUNCTION__);
			
			$stmt = $db->prepare("INSERT INTO note (value)
			VALUES (:value);");
			$stmt->bindParam(':value', $noteValue);
			
			try{
				$stmt->execute();
			}catch (Exception $e){
				alertError($e->getMessage(), __FUNCTION__);
				die();
			}
			unset($stmt);
		}
	}
	
	function loadNotes(){
		$db = loadDB(__FUNCTION__);
		
		$stmt = $db->prepare('SELECT id, value FROM note WHERE displayNote = 1');
		
		try{
			$stmt->execute();
		}catch (Exception $e){
			alertError($e->getMessage(), __FUNCTION__);
		}
		
		$noteString = '';
		
		while($result = $stmt->fetch()){
			$noteString .= '<div class="noteEntity noteEntity-style0" id="note'.$result['id'].'">'.$result['value'].'<div class="supr" onclick="hideNote('.$result['id'].')">&#10006;</div></div>';
		}
		
		unset($stmt);
		
   		return $noteString;
   		
	}
	
	function hideNote($id){
		if(is_numeric($id)){
			$db = loadDB(__FUNCTION__);
			
			$stmt = $db->prepare('UPDATE note SET displayNote = 0 WHERE id = :id');
			$stmt->bindParam(':id', $id);
			
			try{
				$stmt->execute();
			}catch (Exception $e){
				alertError($e->getMessage(), __FUNCTION__);
			}
			
			unset($stmt);
		}
	}
	
	if(isset($_POST['synchro'])){
		if(isset($_POST['newNote'])){
			$i = 0;
			while(isset($_POST['noteNew'.$i])){
				//entrée dans la bdd
				addNote($_POST['noteNew'.$i]);
				$i++;
			}
			
		}
		
		if(isset($_POST['deleteNote'])){
			$i = 0;
			while(isset($_POST['idDeleteNote'.$i])){
				hideNote($_POST['idDeleteNote'.$i]);
				$i++;
			}
		}
	}
	
?>
<script type="text/javascript">
		function saveNote(){
			waitForSynch();
			
			var noteValue = document.getElementById('noteInput').value;
			var i = document.getElementById('nbrNewNote').value;//nbr de new notes déjà prép pour la désynchro
			
			if(i=='0'){
				prepForDesyncho('newNote', 'True');
			}
			
			prepForDesyncho('noteNew'+i, noteValue);
			
			document.getElementById('noteInput').value = '';
			
			j = Number(i);
			j++;
			document.getElementById('nbrNewNote').value = j;
			
			//peut être redonner le focus
			if(noteValue == ""){
				noteValue = '[EMPTY]';
			}
			
			document.getElementById('noteDisplay').innerHTML += '<div class="noteEntity noteEntity-style0" id="note-'+i+'">'+noteValue+'<div class="supr" onclick="hideNote(\'-'+i+'\')">&#10006;</div></div>';
		}
		
		function hideNote(id){
			waitForSynch();
			
			var i = Number(id);
			var j = Number(document.getElementById('nbrDeleteNote').value);
			if(i <= 0){
				i = -i;
				document.getElementById('noteNew'+i).value = '[DELETED]';
				i = -i;
			}else{
				if(j == 0){
					prepForDesyncho('deleteNote', 'True');
				}
				prepForDesyncho('idDeleteNote'+j, i);
				j++;
				document.getElementById('nbrDeleteNote').value = j;
			}
			document.getElementById('note'+i).className = 'hidden';
			
		}
		
</script>
				
			<div class="articleForm">
    			<input type="text" id="noteInput" class="formInput" onKeyUp="inputKeyUp(event, 'saveNote')">
    			<input type="hidden" id="nbrNewNote" value="0">
    			<input type="hidden" id="nbrDeleteNote" value="0">
    		</div>
    		<div id="noteDisplay" class="displayList">
    			<?php
    				$notes = loadNotes();
    				echo $notes;
    			?>
    		</div>