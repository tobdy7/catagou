<?php
	if($_SESSION['connexion']!=1){
		if(isset($_COOKIE["userPseudo"]) AND isset($_COOKIE["userPassword"])){//userPseudo
			$msgToUsr = login($_COOKIE["userPseudo"], $_COOKIE["userPassword"]);
			//echo '<script type="text/javascript">window.location = "http://localhost/auxilio/auxilio'.$_SESSION['version'].'.php";</script>';
		}
		
		if(isset($_POST['loginPseudo'])){
			$msgToUsr = login($_POST['loginPseudo'], $_POST['loginPassword2']);
		}
		
		if(isset($_POST['signInPseudo'])){
			$msgToUsr = signIn($_POST['signInPseudo'], $_POST['signInPassword3']);
		}
	}
	
	function login($pseudo, $passwordHashed1){
		$pseudo = htmlspecialchars($pseudo);
		$passwordHashed2 = hash('sha256', $passwordHashed1);
		alert($passwordHashed2);
		$db = loadDB(__FUNCTION__);
		
		$stmt = $db->prepare("SELECT id, pseudo, access FROM users WHERE pseudo = :pseudo AND passwordHashed = :pwd ;");
		$stmt->bindParam(':pseudo', $pseudo);
		$stmt->bindParam(':pwd', $passwordHashed2);
		
		try{
			$stmt->execute();
		}catch (Exception $e){
			alertError($e->getMessage(), __FUNCTION__);
			die();
		}
		
		if($result = $stmt->fetch()){
			if($result['access']>=1){
				$_SESSION['pseudo'] = $result['pseudo'];
				$_SESSION['id'] = $result['id'];
				$_SESSION['connexion'] = 1;
			
				$return = '<script type="text/javascript">window.location = "http://localhost/catagou/auxilio";</script>';
			}else{
				$return = "<span class='red'>ACCESS DENIED</span>";
			}
		}else{
			$return = "<span class='red'>ACCESS DENIED</span>";
		}
		
		unset($stmt);
		unset($db);
		
		return $return;
	}
	
	function signIn($pseudo, $passwordHashed1){
		$db = loadDB(__FUNCTION__);
		
		$pseudo = htmlspecialchars($pseudo);
		
		$stmt = $db->prepare("SELECT id FROM users WHERE pseudo = :pseudo;");
		$stmt->bindParam(':pseudo', $pseudo);
		
		try{
			$stmt->execute();
		}catch (Exception $e){
			alertError($e->getMessage(), __FUNCTION__);
			die();
		}
		
		if($result = $stmt->fetch()){
			return $return = "<span class='red'>USERNAME ALlREADY TAKEN</span>";
		}
		
		$passwordHashed2 = hash('sha256', $passwordHashed1);
		
		
		$stmt2 = $db->prepare("INSERT INTO users (pseudo, passwordHashed) VALUES (:pseudo, :pwd);");
		$stmt2->bindParam(':pseudo', $pseudo);
		$stmt2->bindParam(':pwd', $passwordHashed2);
		
		try{
			$stmt2->execute();
		}catch (Exception $e){
			alertError($e->getMessage(), __FUNCTION__);
			die();
		}
		
		unset($stmt);
		unset($stmt2);
		unset($db);
		
		return "<span class='green'>SIGNIN SUCCESSFULL</span>";
	}
?>
<?php 
	if($_SESSION['connexion']==1){
?>
Home
présentation des service...
<?php
	if(isset($msgToUsr)){
		echo $msgToUsr;
	}
?>


<?php
	}else{
?>
<script type="text/javascript" src="jshash-2.2/sha256-min.js"></script>
<script type="text/javascript">
	function login(){
		var pwdClear = document.getElementById('loginPassword1').value;
		var pseudo = document.getElementById('loginPseudo').value;
		
		var pwdHash = hex_sha256('pseudo'+pseudo+pwdClear);
		
		document.getElementById('loginPassword2').value = pwdHash;
		document.getElementById('loginPassword1').value = "";
		
		document.getElementById('formLogin').submit();
	}
	
	function showSignIn(){
		document.getElementById('login').className = "hidden";
		document.getElementById('signIn').className = "login";
	}
	function showLogin(){
		document.getElementById('signIn').className = "hidden";
		document.getElementById('login').className = "login";
	}
	
	function signIn(){
		var pwdClear1 = document.getElementById('signInPassword1').value;
		var pwdClear2 = document.getElementById('signInPassword2').value;
		var pseudo = document.getElementById('signInPseudo').value;
		
		if(pwdClear1 == pwdClear2){
			pwdHash = hex_sha256('pseudo'+pseudo+pwdClear1);
			
			document.getElementById('signInPassword3').value = pwdHash;
			
			document.getElementById('formSignIn').submit();
		}else{
			document.getElementById('return').innerHTML = '<span class="red">PASSWORDS DONT MATCH</span>';
		}
	}
	
	function saveCookies(){
		var checked = document.getElementById('saveCookies').checked;
		if(checked){
			var pwdClear = document.getElementById('loginPassword1').value;
			var pseudo = document.getElementById('loginPseudo').value;
		
			var pwdHash = hex_sha256('pseudo'+pseudo+pwdClear);
			
			var d = new Date();
			d.setTime(d.getTime() + (60*24*60*60*1000));
			var expires = "expires="+d.toUTCString();
			document.cookie = "userPseudo=" + pseudo + "; " + expires;
			document.cookie = "userPassword=" + pwdHash + "; " + expires;
		}else{
			var d = new Date();
			d.setTime(d.getTime() + (60*24*60*60*1000));
			var expires = "expires="+d.toUTCString();
			document.cookie = "userPseudo=; ;expires=Thu, 01 Jan 1970 00:00:01 GMT";
			document.cookie = "userPassword=;expires=Thu, 01 Jan 1970 00:00:01 GMT";
		}
	}
</script>
<div class="login" id="login">
	pseudo:<br>
	<form method="POST" id="formLogin">
		<input type="text" id="loginPseudo" name="loginPseudo" onKeyUp="inputKeyUp(event, 'login');"><br>
		<input type="hidden" id="loginPassword2" name="loginPassword2" value="[EMPTY]">
		mot-de-passe:<br>
		<input type="password" id="loginPassword1" onKeyUp="inputKeyUp(event, 'login');"><br>
	</form>
	rester connecter 
	<input type="checkbox" id="saveCookies" onChange="saveCookies()"><br>
	<button onclick="login()" class="clickable">connexion</button><br>
	<button onclick="showSignIn()" class="clickable">inscription</button><br>
<?php
	if(isset($msgToUsr)){
		echo $msgToUsr;
	}
?>

</div>

<div class="hidden" id="signIn">
	pseudo:<br>
	<form method="POST" id="formSignIn">
		<input type="text" id="signInPseudo" name="signInPseudo" onKeyUp="inputKeyUp(event, 'signIn');">
		<input type="hidden" id="signInPassword3" name="signInPassword3" value="[EMPTY]">
	</form>
	mot-de-passe:<br>
	<input type="password" id="signInPassword1" onKeyUp="inputKeyUp(event, 'signIn');"><br>
	répeter le mot-de-passe:<br>
	<input type="password" id="signInPassword2" onKeyUp="inputKeyUp(event, 'signIn');"><br>
	<button onclick="signIn()" class="clickable">Inscription</button><br>
	<button onclick="showLogin()" class="clickable">Connexion</button><br>
	<div id="return">
<?php
	if(isset($msgToUsr)){
		echo $msgToUsr;
	}
?>
	</div>
</div>

<?php
	}
?>