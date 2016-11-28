<?php
/*
* @project	zentralBank
* @author	UserOne0One
* @version 	1.0
*/
/*
	What's next in the developemnet of the project, ideas, problem to fix, ...
	
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

session_start();


/* ===== FUNCTIONS ===== */

/////////   ///   ///   ///   ///   /////////   /////////   ///   /////////   ///   ///   /////////
///         ///   ///   ////  ///   ///            ///      ///   ///   ///   ////  ///   ///      
///         ///   ///   ///// ///   ///            ///      ///   ///   ///   ///// ///   ///      
/////////   ///   ///   /////////   ///            ///      ///   ///   ///   /////////   /////////
///         ///   ///   /// /////   ///            ///      ///   ///   ///   /// /////         ///
///         ///   ///   ///  ////   ///            ///      ///   ///   ///   ///  ////         ///
///         /////////   ///   ///   /////////      ///      ///   /////////   ///   ///   /////////


include '../package/zb.php';
include '../package/usr.php';

/* --- Functions inherant to one page --- */


/////////   /////////   /////////   /////////   /////////
///   ///   ///   ///   ///         ///         ///      
///   ///   ///   ///   ///         ///         ///      
/////////   /////////   ///         /////////   /////////
///         ///   ///   ///  ////   ///               ///
///         ///   ///   ///   ///   ///               ///
///         ///   ///   /////////   /////////   /////////


if(empty($_GET['page'])){
	$_GET['page'] = '';
}

switch ($_GET['page']) {
	case 'gestion':
		
		if(isset($_POST['param1'])){
			//...
		}
		
		if(isset($_GET['param2'])){
			//...
		}

		break;
		
	case 'page2':
		$displayTarget = myFunction();
		
		break;
		
	
	case '':
	case 'home':
	default:
		
		break;
	}



?>

<!DOCTYPE html>
<html>
  <head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="shortcut icon" href="logo.ico" type="image/x-icon" />
	<title>zentralBank</title>
	<meta name="description" content="This is my zentralBank" />
	<meta name="keywords" content="zentral, bank, zentralBank, php" />
	<link type="text/css" rel="stylesheet" href="stylsheet.css" />
	<script type="text/javascript">
		function newCompt(){
			
		}
		
		function yep(){
			console.log('lala bumboum');
		}
		
		//pour quand don press enter dans un form
		function inputKeyUp(e) {
			e.which = e.which || e.keyCode;
			if(e.which == 13) {
				//saveNote();
			}
		}
		/*
			alert("bonjour dear sir");
			console.log('lala bumboum');
			location.href='showTracteur.php?'+argument;
		*/
	</script>
	<style>
		
	</style>
  </head>
  <body onLoad="yep(); displayLogin();">
	<div id="container">
		<header>
			<br>
			____________________________________________________________________________________________________<br>
			____________________________________________________________________________________________________<br>
			____________/////////___/////////___///___///___/////////___________________________________________<br>
			_________________///____///_________////__///______///______________________________________________<br>
			________________///_____///_________/////_///______///______////____////__//__//_//__//_____________<br>
			____________/////////___//////______/////////______///______//__//_//__//_///_//_//_//______________<br>
			______________///_______///_________///_/////______///______/////__//////_//////_////_______________<br>
			_____________///________///_________///__////______///______//__//_//__//_//_///_//_//______________<br>
			____________////////____/////////___///___///______///______/////__//__//_//__//_//__//_____________<br>
			____________________________________________________________________________________________________
		</header>
		<nav>
			<div class="navPage hcenter" id="page">page</div>
		</nav>
		<div class="article">
			<?php
	switch ($_GET['page']) {
		case 'gestion':
		    ?>
			
			<div id="gestion">
				<form id="gestion" methode="post">
					<input type="hidden" name="gestion" value="true">
				</form>
				<button onclick="newCompt()">Nouveau compte</button>
			<?php
			if(isset($displayTarget)){
				echo $displayTarget;
			}
			?>
			</div>
			
			<?php
		    break;// **********
		    
		case 'page2':
		    ?>
			<?php
			if(isset($displayTarget)){
				echo $displayTarget;
			}
			?>
			
			<?php
		    break;// **********
		    
		
		case '':
		case 'home':
		case 'accueil':
		default:
			?>
			<input type="text" id="homeInput" onKeyUp="inputKeyUp(event, 'saveNote')">
			
			<?php
			if(isset($displayTarget)){
				echo $displayTarget;
			}
			break;// **********
	}
			?>
			
		</div>
	</div>
	<footer>
		<p>Copyright &copy; 2016 userOneOOne</p>
	</footer>
  </body>
</html>
