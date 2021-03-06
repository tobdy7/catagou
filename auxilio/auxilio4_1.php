<?php
/*
	
	implémentation des multi link et des dossiers
	rajouter une initialisation des tables après avoir checker qu'elle existent ou pas si elles existent pas => un peu de regex
	ajouter un système de termin...
	agenda en gros
	rajouter des animations je pense ça va avec le fait de différencier le php et le js sur une app. on verra avec auxilio 4.2
	rajouter des tag pour les notes et les todo
	update l'icone
	possibilité d'update todo/notes
	
*/
/*
	Dump:
	
	header('Location: http://localhost/showTracteur/showTracteur.php?page=tvShow&imdbID='.$result['show_imdbID']);
	die();
	
	echo '<script type="text/javascript">
	window.location = "http://localhost/showTracteur/showTracteur.php?page=tvShow&imdbID='.$result['show_imdbID'].'"
</script>';

	echo '<script type="text/javascript">
		alert('.$alert.');
	</script>';

print "<pre>";
	print_r($episodeToWatch);
	print "</pre>";

*/
session_start();

$_SESSION['version']='4_1';
///functions
//deconect();
function deconect(){
	setcookie("userPseudo","",time()-1);
	setcookie("userPassword","",time()-1);
	
	session_destroy();
	
	header('Location: http://localhost/auxilio/auxilio'.$_SESSION['version'].'.php');
	die();
}

function loadDB($caller, $dbName='auxilio'){
	try{
		$bdd = new PDO('mysql:host=localhost;dbname='.$dbName, 'root', '');
		$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		return $bdd;
	}catch (Exception $e){
		alertError($e->getMessage(), $caller);
	}
}

//checkDB();
function checkDB(){
	$table_list = ['link','note','todo'];
	$db = loadDB(__FUNCTION__, 'information_schema');
	$db2 = loadDB(__FUNCTION__);
	
	$stmt = $db->prepare("SELECT TABLE_NAME FROM TABLES WHERE TABLE_SCHEMA = 'auxilio' ORDER BY TABLE_NAME;");	
		
	try{
		$stmt->execute();
	}catch (Exception $e){
		alertError($e->getMessage(), __FUNCTION__);
		die();
	}
	
	$i=0;
	while($result = $stmt->fetch()){
		if($result['TABLE_NAME']==$table_list[$i]){
			$i++;
		}
	}
	if($i == count($table_list)){
		$stmt2 = $db2->prepare("DESCRIBE link");	
			
		try{
			$stmt2->execute();
		}catch (Exception $e){
			alertError($e->getMessage(), __FUNCTION__);
			die();
		}
		$res = $stmt2->fetchAll();
		//en gros faut comparer à un tableau que j'ai... mais faut que le stock dans un include c'est trop de la m sinon
		/*
		print '<pre>';
		print_r($res);
		print '</pre>';
		*/
	}else{
		//add table $i
	}
	
	unset($stmt);
	
	//...
	//create auxilio
	
	//CREATE TABLE `auxilio`.`note` ( `id` INT NOT NULL AUTO_INCREMENT , `value` TEXT NOT NULL , `displayNote` BOOLEAN NOT NULL DEFAULT TRUE , PRIMARY KEY (`id`)) ENGINE = MyISAM;
	//CREATE TABLE `auxilio`.`todo` ( `id` INT NOT NULL AUTO_INCREMENT , `value` INT NOT NULL , `displayStatus` BOOLEAN NOT NULL DEFAULT TRUE , `prio` INT(1) NOT NULL , PRIMARY KEY (`id`)) ENGINE = MyISAM; 
	//CREATE TABLE `auxilio`.`stta` ( `id` INT NOT NULL AUTO_INCREMENT , `branche` INT(2) NOT NULL , `value` TEXT NOT NULL , `status` INT(1) NOT NULL , `semaine` INT(2) NOT NULL , PRIMARY KEY (`id`)) ENGINE = MyISAM;
	//CREATE TABLE `auxilio`.`user` ( `id` INT NOT NULL AUTO_INCREMENT , `pseudo` VARCHAR(256) NOT NULL , `passwordHashed` VARCHAR(100) NOT NULL , `access` INT(1) NOT NULL DEFAULT '1' , PRIMARY KEY (`id`)) ENGINE = MyISAM; 
	//CREATE TABLE `auxilio`.`tag` ( `id` INT NOT NULL AUTO_INCREMENT , `name` VARCHAR(100) NOT NULL , `user_id` INT NOT NULL , `color` VARCHAR(7) NOT NULL DEFAULT '#333333' , PRIMARY KEY (`id`)) ENGINE = MyISAM; 
}


function alertError($error, $process){
	echo '<script type="text/javascript">
	alert("The process identified as || '.$process.' || recieved the error :'.$error.'");
</script>';
}

function alert($message){
	echo '<script type="text/javascript">
		alert('.$message.');
	</script>';
}

//CONNEXION
if(!isset($_SESSION['connexion'])){
	$_SESSION['connexion'] = 0;
}
if(isset($_POST['deco'])){
	deconect();
}
?>
<!-- over this line everything is strictly php-->
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="shortcut icon" href="images/icone4.ico" type="image/x-icon" />
	<title>Auxilio <?php echo $_SESSION['version']; ?> #Beta</title>
	<meta name="description" content="To serve and help" />
	<meta name="keywords" content="auxilio,accuile,starting page, <?php echo $_SESSION['version']; ?>"/>
	<link type="text/css" rel="stylesheet" href="default.css" />
	<script type="text/javascript">
		/* ########## affichage de la bonne page ########## */
		function setArticle(article){
			
			document.getElementById('inputPage').value = article;
			
			//en fct de la page détérminer la classe hidden			
			var x = document.getElementById("article");
			var y = x.getElementsByTagName("article");
			var i;
			for (i = 0; i < y.length; i++) {
				y[i].className = "hidden";
			}
			document.getElementById(article).className = "show";
			
			
			//just un peu de styling
			var b = document.getElementsByName("navLink");
			var j;
			for (j = 0; j < b.length; j++) {
				b[j].style.backgroundColor = '#0074D9';
				b[j].style.color = '#222';
			}
			//c est la divLink de l'article séléctionné
			var c = document.getElementById('nav'+ article.charAt(0).toUpperCase() + article.slice(1))
			c.style.backgroundColor = '#0064C9';
			c.style.color = '#000';
		}
		
		//pour quand on press enter dans un form
		function inputKeyUp(e, action) {
			e.which = e.which || e.keyCode;
			if(e.which == 13){
				if(action == 'saveNote') {
					saveNote();
				}else if(action == 'saveTodo') {
					saveTodo();
				}else if(action == 'saveLink') {
					saveLink();
				}else if(action == 'saveUpdateLink') {
					saveUpdateLink();
				}else if(action == 'login') {
					login();
				}else if(action == 'signIn') {
					signIn();
				}
			}
		}
			
			
		/* ########## clock ########## */
		//source:http://www.w3schools.com/js/tryit.asp?filename=tryjs_timing_clock
		function showClock() {
			var today = new Date();
			var h = today.getHours();
			var m = today.getMinutes();
			var s = today.getSeconds();
			h = checkTime(h);
			m = checkTime(m);
			s = checkTime(s);
			document.getElementById('clockToSleep').innerHTML = h + ":" + m + ":" + s;
			var t = setTimeout(showClock, 500);
		}
		
		function checkTime(i) {
			if (i < 10) {i = "0" + i};  // add zero in front of numbers < 10
			return i;
		}
		
		function getFocus(input) {
			document.getElementById(input).focus();
		}
		
		
		//####DESYCHRONISATION####
		
		
		function synchro(){
			synchron = false;
			document.getElementById('formDesynchro').submit();
		}
		
		function prepForDesyncho(name, value){
			
			var theForm = document.getElementById('formDesynchro');
			
			theForm.innerHTML += '<input type="hidden" id="'+name+'" name="'+name+'" value="'+value+'"/>';
		}
		
		var synchron = false;
		function waitForSynch(){
			document.getElementById('synchro').className= "divSynchro";
			synchron = true;
		}
		
		
		window.onbeforeunload = confirmExit;
		function confirmExit(){
			if(synchron){
				return false;
			}else{
				return null;
			}
		}
		
		
		//console.log('salut');
		//alert(linkOfHome[0][0]);
	</script>
	<style>
	
	</style>
</head>
<?php
	if($_SESSION['connexion']==1){
		
		//$page = 'home';
		$page = 'link';
		//$page='';
		$fct = '';
		if(isset($_GET['page'])){
			$page = $_GET['page'];
			if($page=='note'){
				$fct .= " getFocus('noteInput');";
			}elseif($page=='todo'){
				$fct .= " getFocus('todoInput');";
			}
		}
		if(isset($_POST['page'])){
			$page = $_POST['page'];
			if($page=='note'){
				$fct .= " getFocus('noteInput');";
			}elseif($page=='todo'){
				$fct .= " getFocus('todoInput');";
			}
		}
		
		echo '<body onload="setArticle(\''.$page.'\'); showClock();'.$fct.'">';
	}else{
		echo '<body onload="setArticle(\'home\'); showClock();">';
	}
?>
	
	<form method="POST" class="hidden" id="formDesynchro">
		<input type="hidden" name="synchro" value="True"/>
		<input type="hidden" name="page" id="inputPage"/>
	</form>

	<header>
		<div id="logo" class="clickable" onclick="window.location = 'http://localhost/auxilio/auxilio<?php echo $_SESSION['version']; ?>.php'">
			<img src="images/logo.png" class="imgLogo">
			Auxilio <?php echo $_SESSION['version']; ?> <span class="red">#Beta</span>
		</div>
		<div id="clockToSleep" class="clock">
			06:13:12
		</div>
		<div id="synchro" class="hidden">
			<button onclick="synchro()" class="clickable">&#x21bb;</button><!--synchroniser &#10555; &#8404; &#10555;-->
		</div>
		<form method="POST">
			<input type="hidden" name="deco" value="True"/>
			<input type="submit" value="deco"/>
		</form>
		
</header>
<nav>
	<div id="navMenu">
		<div class="navLink" name="navLink" onclick="setArticle('home')" id="navHome">
			Home
		</div>

<?php 
	if($_SESSION['connexion']==1){
?>

		<div class="navLink" name="navLink" onclick="setArticle('link')" id="navLink">
			Liens
		</div>
		<div class="navLink" name="navLink" onclick="setArticle('note'); getFocus('noteInput');" id="navNote">
			Notes
		</div>
		<div class="navLink" name="navLink" onclick="setArticle('todo'); getFocus('todoInput');" id="navTodo">
			A faire
		</div>
		<div class="navLink" name="navLink" onclick="setArticle('stta')" id="navStta">
			StuTa
		</div>
		<div class="navLink" name="navLink" onclick="setArticle('para')" id="navPara">
			Param
		</div>

<?php 
	}
?>
	</div>
</nav>

	<div class="container" id="article">
		<!-- ========== HOME ========== -->
		<article id="home">
		<?php include 'include'.$_SESSION['version'].'/home.php';?>		
		</article>
		
<?php 
	if($_SESSION['connexion']==1){
?>
		
			<!-- ========== LINK ========== -->
		<article id="link" class="hidden">
		<?php include 'include'.$_SESSION['version'].'/link.php';?>		
		</article>
		<!-- ========== NOTE ========== -->
		<article id="note" class="hidden">
		<?php include 'include'.$_SESSION['version'].'/note.php';?>		
		</article>
		<!-- ========== TODO ========== -->
		<article id="todo" class="hidden">
		<?php include 'include'.$_SESSION['version'].'/todo.php';?>		
		</article>
			<!-- ========== STTA ========== -->
		<article id="stta" class="hidden">
		<?php include 'include'.$_SESSION['version'].'/stta.php';?>		
		</article>
		<!-- ========== PARA ========== -->
		<article id="para" class="hidden">
		<?php include 'include'.$_SESSION['version'].'/para.php';?>		
		</article>
<?php 
	}
?>
	</div>
<footer>
	<p>
		Copyright &copy; 2016 userOne0One
	</p>
</footer>
<script type="text/javascript">
	//where to call functions
		
	//setArticle();
	
</script>
</body>
</html>
