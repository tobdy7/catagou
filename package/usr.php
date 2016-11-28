<?php
/*
* @project	usr
* @author	UserOne0One
* @version 	1.0
*/
/**
*	Bon la co fonctionne...
*	il faut csser la div je pense tous simplement en rajoutant une page csser
*	rajouter inscription
*	prestation ne fctionne pas dans le sense que ça n'affiche pas dans la bdd si tu a visité un site mais je pense qu'on peut pas avoire un nom de colonne en param tt simplement
*	bon mais encore bravo couillon
*	après faut désafecter home je pense que pour le style ... je sais pas si on fait le même pour tous le monde...
*/
/*
	Dump:
	
	header('Location: http://localhost/showTracteur/showTracteur.php?page=tvShow&imdbID='.$result['show_imdbID']);
	die();
	
	echo '<script type="text/javascript">
		   window.location = "http://localhost/showTracteur/showTracteur.php?page=tvShow&imdbID='.$result['show_imdbID'].'"
	  </script>';
	  
	http://www.omdbapi.com/?i=tt3143980
	
	print "<pre>";
	print_r($episodeToWatch);
	print "</pre>";
	
	print_r(getFromAPI('i='));
  
*/



/* ===== FUNCTIONS ===== */

/////////   ///   ///   ///   ///   /////////   /////////   ///   /////////   ///   ///   /////////
///         ///   ///   ////  ///   ///            ///      ///   ///   ///   ////  ///   ///      
///         ///   ///   ///// ///   ///            ///      ///   ///   ///   ///// ///   ///      
/////////   ///   ///   /////////   ///            ///      ///   ///   ///   /////////   /////////
///         ///   ///   /// /////   ///            ///      ///   ///   ///   /// /////         ///
///         ///   ///   ///  ////   ///            ///      ///   ///   ///   ///  ////         ///
///         /////////   ///   ///   /////////      ///      ///   /////////   ///   ///   /////////

include '../package/utils.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function addUsr($pseudo, $passwordHashedOnce){

	$db = loadPDO('user');
	
	$pseudoSafe = htmlspecialchars($pseudo);
	
	$stmt = $db->prepare("SELECT id FROM user WHERE pseudo = :pseudo;");
	$stmt->bindParam(':pseudo', $pseudoSafe);
	
	executeStmt($stmt);
	
	if($result = $stmt->fetch()){
		errorMessageForUsr("THIS PSEUDO IS ALREADY TAKEN");
		return false;
	}
	
	$passwordHashedTwice = hash('sha256', $passwordHashedOnce);
	
	
	$stmt2 = $db->prepare("INSERT INTO users (pseudo, passwordHashed) VALUES (:pseudo, :pwd);");
	$stmt2->bindParam(':pseudo', $pseudoSafe);
	$stmt2->bindParam(':pwd', $passwordHashedTwice);
	
	executeStmt($stmt2);
	
	return true;
}

function login($pseudo, $passwordHashedOnce){
	
	$db = loadPDO('user');
	
	$pseudoSafe = htmlspecialchars($pseudo);
	$passwordHashedTwice = hash('sha256', $passwordHashedOnce);
	
	$stmt = $db->prepare("SELECT id, pseudo, access FROM user WHERE pseudo = :pseudo AND passwordHashed = :pwd ;");
	$stmt->bindParam(':pseudo', $pseudoSafe);
	$stmt->bindParam(':pwd', $passwordHashedTwice);
	
	executeStmt($stmt);
		
	if($result = $stmt->fetch()){
		if($result['access']>=1){
			$_SESSION['access'] = $result['access'];
			$_SESSION['pseudo'] = $result['pseudo'];
			$_SESSION['id'] = $result['id'];
			$_SESSION['connexion'] = 1;
		}else{
			errorMessageForUsr("ACCESS DENIED: UNSIFFICIENT CREDENTIAL");
			return false;
		}
	}else{
		errorMessageForUsr("ACCESS DENIED: INSUFFICIENT CREDENTIAL");
		return false;
	}
	
	return true;
}

function logout(){
	$_SESSION['access'] = null;
	$_SESSION['pseudo'] = null;
	$_SESSION['id'] = null;
	$_SESSION['connexion'] = null;
	
	session_destroy();
	
	return true;
}

function access($prestateur){
	
	$db = loadPDO('user');

	$stmt = $db->prepare("SELECT :prestateur FROM user WHERE id = :id ;");
	$stmt->bindParam(':prestateur', $prestateur);
	$stmt->bindParam(':id', $_SESSION['id']);
	
	executeStmt($stmt);
	
	$result = $stmt->fetch();
	
	if(!$result[$prestateur]){
		$stmt2 = $db->prepare("UPDATE user SET :prestateur = 1 WHERE id = :id ;");
		$stmt2->bindParam(':prestateur', $prestateur);
		$stmt2->bindParam(':id', $_SESSION['id']);
	
		executeStmt($stmt2);
	}
	
	
	$_SESSION['prestateur'] = $prestateur;
	$_SESSION['connexion'] = 2;
	enter($prestateur);
}

function enter($prestateur){
	alert($_SESSION['connexion']);

	if($_SESSION['connexion'] == 2){
		//dans le site trkl
		$_SESSION['usrLogedIn'] = true;
	}elseif($_SESSION['connexion'] == 1){
		if($_SESSION['access'] > 0 AND isset($_SESSION['pseudo']) AND isset($_SESSION['id'])){
			access($prestateur);
		}else{
			$_SESSION['connexion'] = 0;
		}
	}else{
		//on doit s'authentifier, se connecter quoi
		
		//dabor on check si y a des cookies d'accès
		if(isset($_COOKIE["userPseudo"]) AND isset($_COOKIE["userPasswordHashed"])){
			if(login($_COOKIE["userPseudo"], $_COOKIE["userPassword"])){
				enter($prestateur);
			}
		}elseif(isset($_POST['loginPseudo'])){
			if(login($_POST['loginPseudo'], $_POST['loginPassword2'])){
				enter($prestateur);
			}
		}elseif(isset($_POST['signInPseudo'])){
			addUsr($_POST['signInPseudo'], $_POST['signInPassword3']);
		}
		
		if(!isset($_SESSION['connexion']) OR $_SESSION['connexion'] == 0){
			$_SESSION['connexion'] = 0;
			displayLogin();
		}
	}
}

function displayLogin(){
	echo '
	<script type="text/javascript" src="../package/jshash-2.2/sha256-min.js"></script>
	<script type="text/javascript">
		function displayLogin(){
			var div = document.createElement("DIV");
			var title = document.createElement("H1");
			var t = document.createTextNode("sign in");
			title.appendChild(t);
			div.appendChild(title);
			
			
			//FORM
			
			var form = document.createElement("FORM");
			form.setAttribute("method","post");
			form.setAttribute("id","formLogin");
			
			
			//PSEUDO
			
			//span pseudo;
			var spanPseudo = document.createElement("SPAN");
			var tSpanPseudo = document.createTextNode("PSEUDO :");
			spanPseudo.appendChild(tSpanPseudo);
			
			//input pseudo
			var inputPseudo = document.createElement("INPUT");
			inputPseudo.setAttribute("type","text");
			inputPseudo.setAttribute("name","loginPseudo");
			inputPseudo.setAttribute("id","loginPseudo");
			
			
			//PASSWORD2
			
			var inputPwd2 = document.createElement("INPUT"); //input element, Submit button
			inputPwd2.setAttribute("type","hidden");
			inputPwd2.setAttribute("name","loginPassword2");
			inputPwd2.setAttribute("id","loginPassword2");
			inputPwd2.setAttribute("value","");
			
			
			//assemblage du form
			form.appendChild(spanPseudo);
			form.appendChild(inputPseudo);
			form.appendChild(inputPwd2);
			
			
			div.appendChild(form);
			
			
			
			//PASSWORD1
			
			//span pwd;
			var spanPwd = document.createElement("SPAN");
			var tSpanPwd = document.createTextNode("PASSWORD :");
			spanPwd.appendChild(tSpanPwd);
			
			//input pseudo
			var inputPwd1 = document.createElement("INPUT");
			inputPwd1.setAttribute("type","password");
			inputPwd1.setAttribute("id","loginPassword1");
			
			spanPwd.appendChild(inputPwd1);
			
			var br = document.createElement("BR");
			
			//BUTTON
			var button = document.createElement("BUTTON");
			var tButton = document.createTextNode("login");
			button.appendChild(tButton);
			//button.onclick = login();
			button.addEventListener("click", function() {
					login();
				}, false);
			
			
			
			div.appendChild(spanPwd);
			div.appendChild(inputPwd1);
			div.appendChild(br);
			div.appendChild(button);

			document.getElementsByTagName("body")[0].appendChild(div);
		}
		
		function login(){
			var pwdClear = document.getElementById("loginPassword1").value;
			var pseudo = document.getElementById("loginPseudo").value;
			
			var pwdHash = hex_sha256("pseudo"+pseudo+pwdClear);
			
			document.getElementById("loginPassword2").value = pwdHash;
			document.getElementById("loginPassword1").value = "";
		
			document.getElementById("formLogin").submit();
		}
		
	</script>';
	
	
}


function errorMessageForUsr($msg){
	console($msg);
}

function implementUsrConnexion($prestateur){
	if(isset($_SESSION['prestateur'])){
		if($_SESSION['prestateur'] != $prestateur){
			$_SESSION['connexion'] = 1;
		}
	}
	echo $_SESSION['connexion'];
	//alert();
	enter($prestateur);
	if($_SESSION['connexion'] == 2){
		alert("vous ête co");

		return true;
	}else{
		return false;
	}
	
	
}



/* ===== CALL ===== */

 ////////    ///////    ///         ///      
////        ///   ///   ///         ///      
///         ///   ///   ///         ///      
///         /////////   ///         ///      
///         ///   ///   ///         ///      
////        ///   ///   ///         ///      
 ////////   ///   ///    ////////    ////////

if(implementUsrConnexion('auxilio')){
	echo 'tadada';
}


?>