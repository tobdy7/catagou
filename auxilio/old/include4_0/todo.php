<?php
	function addTodo($todoValue, $prio){
		if($todoValue != '[DELETED]'){
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
			
			$stmt = $db->prepare("INSERT INTO todo (user_id, value, prio) VALUES (:user_id, :value, :prio);");
			$stmt->bindParam(':user_id', $_SESSION['id']);
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
	}
	
	function loadTodo(){
		$db = loadDB(__FUNCTION__);
		
		$stmt = $db->prepare('SELECT id, value, prio FROM todo WHERE displayStatus = 1 AND user_id = :user_id ORDER BY prio DESC');
		$stmt->bindParam(':user_id', $_SESSION['id']);
		
		try{
			$stmt->execute();
		}catch (Exception $e){
			alertError($e->getMessage(), __FUNCTION__);
		}
		
		$todoString = '';
		
		while($result = $stmt->fetch()){
			$todoString .= '<div class="noteEntity noteEntity-style'.$result['prio'].'" id="todo'.$result['id'].'">'.$result['value'].'<div class="supr" onclick="hideTodo('.$result['id'].')">&#10006;</div></div>';
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
	
	if(isset($_POST['synchro'])){
		if(isset($_POST['newTodo'])){
			$i = 0;
			while(isset($_POST['todoNew'.$i])){
				//entrée dans la bdd
				addTodo($_POST['todoNew'.$i], $_POST['todoPrioNew'.$i]);
				$i++;
			}
			
		}
		
		if(isset($_POST['deleteTodo'])){
			$i = 0;
			while(isset($_POST['idDeleteTodo'.$i])){
				hideTodo($_POST['idDeleteTodo'.$i]);
				$i++;
			}
		}
	}
	
	
	
?>
<script type="text/javascript">
		function saveTodo(){
			waitForSynch();
			
			var todoValue = document.getElementById('todoInput').value;
			var elements = document.getElementsByName('todoRadio');
   			for (var j = 0, l = elements.length; j < l; j++){
				if (elements[j].checked){
					var todoRadioValue = elements[j].value;
				}
			}
			var i = document.getElementById('nbrNewTodo').value;
			
			
			if(i=='0'){
				prepForDesyncho('newTodo', 'True');
			}
			
			prepForDesyncho('todoNew'+i, todoValue);
			prepForDesyncho('todoPrioNew'+i, todoRadioValue);
			
			document.getElementById('todoInput').value = '';
			
			j = Number(i);
			j++;
			document.getElementById('nbrNewTodo').value = j;
			
			document.getElementById('todoDisplay').innerHTML += '<div class="noteEntity noteEntity-style'+todoRadioValue+'" id="todo-'+i+'">'+todoValue+'<div class="supr" onclick="hideTodo(\'-'+i+'\')">&#10006;</div></div>';
			
			
		}
		
		function hideTodo(id){
			waitForSynch();
			
			var i = Number(id);
			var j = Number(document.getElementById('nbrDeleteTodo').value);
			if(i <= 0){
				i = -i;
				document.getElementById('todoNew'+i).value = '[DELETED]';
				i = -i;
			}else{
				if(j == 0){
					prepForDesyncho('deleteTodo', 'True');
				}
				prepForDesyncho('idDeleteTodo'+j, i);
				j++;
				document.getElementById('nbrDeleteTodo').value = j;
			}
			document.getElementById('todo'+id).className = 'hidden';
			
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
    			<input type="hidden" id="nbrNewTodo" value="0">
    			<input type="hidden" id="nbrDeleteTodo" value="0">

    		</div>
    		<div id="todoDisplay" class="displayList">
				<?php
    				$todos = loadTodo();
    				echo $todos;
    			?>
    		</div>