<?php
	function addTodo($todoValue, $prio){
		$todoValue = htmlspecialchars($todoValue);
		if($todoValue == ''){
			$todoValue = htmlspecialchars('[EMPTY]');
		}
		if($prio == 1 OR $prio == 2 OR $prio == 3){
			//all good
		}else{
			alertError('Wrong entry for $prio', __FUNCTION__);
			$prio = 3;
		}
		
		$db = loadDB(__FUNCTION__);
		
		$stmt = $db->prepare("INSERT INTO todo (value, prio)
		VALUES (:value, :prio);");
		$stmt->bindParam(':value', $todoValue);
		$stmt->bindParam(':prio', $prio);
		
		try{
			$stmt->execute();
		}catch (Exception $e){
			alertError($e->getMessage(), __FUNCTION__);
			die();
		}
		unset($stmt);
	}
	
	function loadTodo(){
		$db = loadDB(__FUNCTION__);
		
		$stmt = $db->prepare('SELECT id, value, prio FROM todo WHERE displayStatus = 1 ORDER BY prio DESC');
		
    	try{
			$stmt->execute();
		}catch (Exception $e){
			alertError($e->getMessage(), __FUNCTION__);
		}
		
		$todoString = '';
		
    	while($result = $stmt->fetch()){
    		$todoString .= '<div class="noteEntity noteEntity-style'.$result['prio'].'">'.$result['value'].'<div class="supr" onclick="hideTodo('.$result['id'].')">&#10006;</div></div>';
    	}
		unset($stmt);
   		return $todoString;
   		
	}
	
	function hideTodo($id){
		if(is_numeric($id)){
			$db = loadDB(__FUNCTION__);
			
			$stmt = $db->prepare('UPDATE todo SET displayStatus = 0 WHERE id = :id');
			$stmt->bindParam(':id', $id);
			
			try{
				$stmt->execute();
			}catch (Exception $e){
				alertError($e->getMessage(), __FUNCTION__);
			}
			unset($stmt);
		}
	}
	
	if(isset($_GET['newTodo']) AND isset($_GET['todoPrio'])){
		addTodo($_GET['newTodo'], $_GET['todoPrio']);
		
		//pas nécessaire de redirigé comme normalement je ne devrai pas recharger la page...
		/*echo '<script type="text/javascript"><?php echo $_SESSION['version']; ?>
           window.location = "http://localhost/auxilio/auxilio3_0.php?page=note"
      	</script>';
		*/
	}
	if(isset($_GET['hideTodo'])){
		hideTodo($_GET['hideTodo']);
		
		//pas nécessaire de redirigé comme normalement je ne devrai pas recharger la page...
		/*echo '<script type="text/javascript">
           window.location = "http://localhost/auxilio/auxilio3_0.php?page=note"
      	</script>';
		*/
	}
	
	
?>
<script type="text/javascript">
		function saveTodo(){
			var todoValue = document.getElementById('todoInput').value;
			document.getElementById('todoInput').value = '';
			
			var elements = document.getElementsByName('todoRadio');
   			for (var j = 0, l = elements.length; j < l; j++){
        		if (elements[j].checked){
            		var todoRadioValue = elements[j].value;
        		}
    		}
			
			window.location = "http://localhost/auxilio/auxilio<?php echo $_SESSION['version']; ?>.php?page=todo&newTodo="+todoValue+"&todoPrio="+todoRadioValue;
			
		}
		
		function hideTodo(id){
			window.location = "http://localhost/auxilio/auxilio<?php echo $_SESSION['version']; ?>.php?page=todo&hideTodo="+id;
		}
		
		
</script>




			<div class="articleForm">
    			<input type="text" id="todoInput" class="formInput" onKeyUp="inputKeyUp(event, 'saveTodo');">
    			<input type="radio" id="formRadio1" name="todoRadio" class="formRadio formRadio-style1" value="1" onclick="getFocus('todoInput')" checked="checked">
    			<label for="formRadio1" class="radioLabel radioLabel-style1"></label>
    			<input type="radio" id="formRadio2" name="todoRadio" class="formRadio formRadio-style2" value="2" onclick="getFocus('todoInput')">
    			<label for="formRadio2" class="radioLabel radioLabel-style2"></label>
    			<input type="radio" id="formRadio3" name="todoRadio" class="formRadio formRadio-style3" value="3" onclick="getFocus('todoInput')">
    			<label for="formRadio3" class="radioLabel radioLabel-style3"></label>

    		</div>
    		<div id="todoDisplay" class="displayList">
				<?php
    				$todos = loadTodo();
    				echo $todos;
    			?>
    		</div>