<?php
/*
* @project	utils
* @author	UserOne0One
* @version 	1.0
*/
/*
	
	
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



//load db
function loadPDO($dbName){
	try{
		$db = new PDO('mysql:host=localhost;dbname='.$dbName, 'root', '');
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}catch (Exception $e){
		die('Error : ' . $e->getMessage());
	}
	
	return $db;
}

function executeStmt($stmt){
	try{
		$stmt->execute();
	}catch (Exception $e){
		console($e);
		return false;
	}
	return true;
}

//display a message in the console
function console($message){
	echo '<script type="text/javascript">
		console.log("'.$message.'");
		</script>';
}

function alert($msg = "hi"){
	echo '<script type="text/javascript">
		alert("'.$msg.'");
	</script>';
}

//print an array in areadable manner
function printa($array){
	print '<pre>';
	print_r($array);
	print '</pre>';
}
?>