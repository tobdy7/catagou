<?php
	function addNote($noteValue){
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
    		$noteString .= '<div class="noteEntity noteEntity-style0">'.$result['value'].'<div class="supr" onclick="hideNote('.$result['id'].')">&#10006;</div></div>';
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
	
	if(isset($_GET['newNote'])){
		addNote($_GET['newNote']);
		
		//pas nécessaire de redirigé comme normalement je ne devrai pas recharger la page...
		/*echo '<script type="text/javascript"><?php echo $_SESSION['version']; ?>
           window.location = "http://localhost/auxilio/auxilio3_0.php?page=note"
      	</script>';
		*/
	}
	if(isset($_GET['hideNote'])){
		hideNote($_GET['hideNote']);
		
		//pas nécessaire de redirigé comme normalement je ne devrai pas recharger la page...
		/*echo '<script type="text/javascript">
           window.location = "http://localhost/auxilio/auxilio3_0.php?page=note"
      	</script>';
		*/
	}
	
	
?>
<script type="text/javascript">
		function saveNote(){
			var noteValue = document.getElementById('noteInput').value;
			document.getElementById('noteInput').value = '';
			window.location = "http://localhost/auxilio/auxilio<?php echo $_SESSION['version']; ?>.php?page=note&newNote="+noteValue;
		}
		
		function hideNote(id){
			window.location = "http://localhost/auxilio/auxilio<?php echo $_SESSION['version']; ?>.php?page=note&hideNote="+id;
		}
		
</script>
				
			<div class="articleForm">
    			<input type="text" id="noteInput" class="formInput" onKeyUp="inputKeyUp(event, 'saveNote')">
    		</div>
    		<div id="noteDisplay" class="displayList">
    			<?php
    				$notes = loadNotes();
    				echo $notes;
    			?>
    		</div>