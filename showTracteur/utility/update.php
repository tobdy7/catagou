<?php
/*
	implémenter les changement dans la bdd
	rajouter un bouton update les données sql
	styler un peu quoi
	supprimer ce qui est inutil
	
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
function loadPDO(){
	try{
		$db = new PDO('mysql:host=localhost;dbname=showTracteur', 'root', '');
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}catch (Exception $e){
		die('Error : ' . $e->getMessage());
	}
	
	return $db;
}


//request on the API
function getFromAPI($search) {
	$request = 'http://www.omdbapi.com/?'.$search;
	$response = file_get_contents($request);
	$jsonobj = json_decode($response, true);
	return $jsonobj;
}



function updateEpisode($imdbID, $whatToUpdate){
	$db = loadPDO();
	
	$newValues = getFromAPI('i='.$imdbID);
	
	if($whatToUpdate != 'New'){
		if($whatToUpdate == 'title'){
			$toChange = htmlspecialchars($newValues['Title'], ENT_QUOTES);
		}elseif($whatToUpdate == 'released'){
			$toChange = date("Y-m-d", strtotime($newValues['Released']));
			
		}elseif($whatToUpdate == 'episode'){
			$toChange = $newValues['Episode'];
		}elseif($whatToUpdate == 'imdbID'){
			$toChange = $newValues['imdbID'];
		}elseif($whatToUpdate == 'season'){
			$toChange = $newValues['Season'];
		}
		
		
		$stmt = $db->prepare("UPDATE episode SET ".$whatToUpdate." = :".$whatToUpdate." WHERE imdbID = :imdbID");
		$stmt->bindParam(':imdbID', $imdbID);
		$stmt->bindParam(':'.$whatToUpdate, $toChange);
	}else{
		$newValuesDate = date("Y-m-d", strtotime($newValues['Released']));
		$stmt = $db->prepare("INSERT INTO episode (imdbID, show_imdbID, title, season, episode, released)
		VALUES (:imdbID, :show_imdbID, :title, :season, :episode, :released)");
		$stmt->bindParam(':imdbID', $newValues['imdbID']);
		$stmt->bindParam(':show_imdbID', $newValues['seriesID']);
		$stmt->bindParam(':title', htmlspecialchars($newValues['Title'], ENT_QUOTES));
		$stmt->bindParam(':season', $newValues['Season']);
		$stmt->bindParam(':episode', $newValues['Episode']);
		$stmt->bindParam(':released', $newValuesDate);
	}
				
	
	try{
		$stmt->execute();
	}catch (Exception $e){
		die('Error : ' . $e->getMessage());
	}
}


function imdbIDArrayString(){
	$db = loadPDO();

	$stmt = $db->prepare("SELECT imdbID FROM tvShow");
	$stmt->execute();

	$imdbIDArrayString = "";

	while($result = $stmt->fetch()){
		$imdbIDArrayString .= '"'.$result['imdbID'].'", ';
	}
	
	return $imdbIDArrayString;
}

function episodeJSONString(){
	$db = loadPDO();

	$stmt = $db->prepare("SELECT imdbID FROM tvShow");
	$stmt->execute();

	$episodePHPArray = array();

	while($result = $stmt->fetch()){
		$stmt2 = $db->prepare("SELECT title, released, episode, imdbID, season FROM episode WHERE show_imdbID = :show_imdbID ORDER BY season, episode");
		$stmt2->bindParam(':show_imdbID', $result['imdbID']);
		
		try{
			$stmt2->execute();
		}catch (Exception $e){
			alertError($e->getMessage(), __FUNCTION__);
		}
		
		$seriePHPArray = [];
		$season = 1;
		while($result2 = $stmt2->fetch()){
			if($result2['season'] != $season){
				//echo $result['imdbID'].' '.$season.'<br>';
				$seriePHPArray[] = [
					"season" => $season,
					"episode" => $seasonPHPArray
				];
				$season = $result2['season'];
				$seasonPHPArray = [];
				
			}
			
			$seasonPHPArray[] = [
				"episode" => $result2['episode'],
				"season" => $result2['season'],
				"imdbID" => $result2['imdbID'],
				"title" => $result2['title'],
				"released" => $result2['released']
			];
		}
		
		$seriePHPArray[] = [
			"season" => $season,
			"episode" => $seasonPHPArray
		];
		$seasonPHPArray = [];
				
		$episodePHPArray[] = [
			"imdbID" => $result['imdbID'],
			"episodes" => $seriePHPArray
		];
	}

	$episodeJSONString = json_encode($episodePHPArray);
	return $episodeJSONString;
}

/* --- Functions inherant to one page --- */


/////////   /////////   /////////   /////////   /////////
///   ///   ///   ///   ///         ///         ///      
///   ///   ///   ///   ///         ///         ///      
/////////   /////////   ///         /////////   /////////
///         ///   ///   ///  ////   ///               ///
///         ///   ///   ///   ///   ///               ///
///         ///   ///   /////////   /////////   /////////

/*
if(empty($_GET['page'])){
	$_GET['page'] = '';
}

switch ($_GET['page']) {
	
	case 'param':
		
		
		//choice of episodes to update
		if(isset($_GET['update'])){
			//check differences between the API and de db
			$possiblyToUpdate = checkForUpdates($_GET['update']);
			if($possiblyToUpdate){
				
				//format in an orderly fashion
				$stringOfUpdates = '<form class="updateList">
					<table class="tableInLineSmall ">
						<th>imdbID</th>
						<th>Error</th>
						<th>sql</th>
						<th>API</th>
						<th><input type="checkbox" id="checkThem" onclick="checkThemAll('.(count($possiblyToUpdate)-1).')"/></th>';
				$j=0;
				for($i=0; isset($possiblyToUpdate[$i]); $i++){
					$stringOfUpdates .= '<tr>
						<td>'.$possiblyToUpdate[$i]['imdbID'].'</td>
						<td>'.$possiblyToUpdate[$i]['error'].'</td>
						<td>'.$possiblyToUpdate[$i]['sql'].'</td>
						<td>'.$possiblyToUpdate[$i]['API'].'</td>
						<td>
							<input type="checkbox" name="episodeToUpdate'.$i.'" id="episodeToUpdate'.$i.'" value="'.$possiblyToUpdate[$i]['imdbID'].'"/>
							<input type="hidden" name="episodeToUpdate'.$i.'" id="episodeToUpdateHidden'.$i.'" value="False"/>
							<input type="hidden" name="typeOfUpdate'.$i.'" id="typeOfUpdate'.$i.'" value="'.$possiblyToUpdate[$i]['error'].'"/>
						</td>
					</tr>';
					$j = $i;
				}
				$stringOfUpdates .= '<tr><td><input type="submit" value="update" onclick="uncheckedForm('.$j.')"  class="form-submit updateButton"/></td></tr>
				</table>
				<input type="hidden" name="page" value="param"/>
				<input type="hidden" name="update" value="'.$_GET['update'].'"/>
				<input type="hidden" name="updateEpisode" value="True"/>
				
			</form>';
			}else{
				$stringOfUpdates = 'No update Found!!!';
			}
		}
		
		//check for update on all the shows
		if(isset($_GET['allUpdate'])){
			try{
				$db = new PDO('mysql:host=localhost;dbname=showTracteur', 'root', '');
				$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			}catch (Exception $e){
				die('Error : ' . $e->getMessage());
			}
			
			$statement = $db->prepare("SELECT imdbID FROM tvShow");
			$statement->execute();
			
			$i = 0;
			while($result = $statement->fetch())
			{
				$i++;
				//check differences between the API and de db
				$listOfChange[] = checkForUpdates($result['imdbID']);
			}
			
			//format in an orderly fashion
			$stringOfUpdates = '<form  class="updateList">
				<table class="tableInLineSmall vcenter infoShow">
					<th>imdbID</th>
					<th>Error</th>
					<th>sql</th>
					<th>API</th>
					<th><input type="checkbox" id="checkThem" onclick="checkThemAll('.(((sizeof($listOfChange,1)-sizeof($listOfChange))/5)-1).')"/></th>';
			$j=0;
			
			
			for($k=0; $k<sizeof($listOfChange); $k++){
				for($i=0; isset($listOfChange[$k][$i]); $i++){
					$stringOfUpdates .= '<tr>
						<td>'.$listOfChange[$k][$i]['imdbID'].'</td>
						<td>'.$listOfChange[$k][$i]['error'].'</td>
						<td>'.$listOfChange[$k][$i]['sql'].'</td>
						<td>'.$listOfChange[$k][$i]['API'].'</td>
						<td>
							<input type="checkbox" name="episodeToUpdate'.$j.'" id="episodeToUpdate'.$j.'" value="'.$listOfChange[$k][$i]['imdbID'].'"/>
							<input type="hidden" name="episodeToUpdate'.$j.'" id="episodeToUpdateHidden'.$j.'" value="False"/>
							<input type="hidden" name="typeOfUpdate'.$j.'" id="typeOfUpdate'.$j.'" value="'.$listOfChange[$k][$i]['error'].'"/>
						</td>
					</tr>';
					$j++; 
				}
			}
			$stringOfUpdates .= '<tr><td><input type="submit" value="update" onclick="uncheckedForm('.$j.')"  class="form-submit updateButton"/></td></tr>
			</table>
			<input type="hidden" name="page" value="param"/>
			
			<input type="hidden" name="updateEpisode" value="True"/>
			
		</form>';
		}//<input type="hidden" name="allUpdate" value="True"/>
		
		$displayTarget = loadListOfShow();
		break;
		
	
		break;
	}




*/



//effectue les changement dans la bdd
if(isset($_POST['update'])){
	$nbrCheck = 0;
	while(isset($_POST['id'.$nbrCheck])){
		if(isset($_POST['check'.$nbrCheck])){
			updateEpisode($_POST['id'.$nbrCheck], $_POST['type'.$nbrCheck]);
			
		}
		$nbrCheck++;
	}
}





//Structure of the answer from the API
/* STRUCTURE

http://www.omdbapi.com/?i=tt3143980
Array ( 
	[Title] => Earth Skills 
	[Year] => 2014 
	[Rated] => TV-14 
	[Released] => 26 Mar 2014 
	[Season] => 1 
	[Episode] => 2 
	[Runtime] => 45 min 
	[Genre] => Drama, Mystery, Sci-Fi 
	[Director] => Dean White 
	[Writer] => Jason Rothenberg (developed by), Jason Rothenberg, Kass Morgan (based upon the book by) 
	[Actors] => Eliza Taylor, Paige Turco, Thomas McDonell, Eli Goree 
	[Plot] => Discovering that Jasper may still be alive, Clarke, Bellamy, Finn, Wells and Murphy head out to find him. On the Ark, Abby is determined to prove Earth is habitable, and enlists a mechanic to craft an escape pod. 
	[Language] => English 
	[Country] => USA 
	[Awards] => N/A 
	[Poster] => http://ia.media-imdb.com/images/M/MV5BMTAzMDA3MDQ4NjJeQTJeQWpwZ15BbWU4MDMzNjA3NDEx._V1_SX300.jpg 
	[Metascore] => N/A 
	[imdbRating] => 7.7 
	[imdbVotes] => 1684 
	[imdbID] => tt3143980 
	[seriesID] => tt2661044 
	[Type] => episode 
	[Response] => True ) 


http://www.omdbapi.com/?i=tt0364845		(ncis)
Array ( 
	[Title] => NCIS 
	[Year] => 2003– 
	[Rated] => TV-14 
	[Released] => 23 Sep 2003
	[Runtime] => 60 min 
	[Genre] => Action, Comedy, Crime 
	[Director] => N/A 
	[Writer] => Donald P. Bellisario, Don McGill 
	[Actors] => Mark Harmon, Michael Weatherly, Pauley Perrette, David McCallum 
	[Plot] => The cases of the Naval Criminal Investigative Service's Washington DC Major Case Response Team, led by Special Agent Leroy Jethro Gibbs. 
	[Language] => English 
	[Country] => USA 
	[Awards] => Nominated for 3 Primetime Emmys. Another 19 wins & 27 nominations. 
	[Poster] => http://ia.media-imdb.com/images/M/MV5BMTYyMTQ0MTU1OF5BMl5BanBnXkFtZTcwMjI0Njg4Ng@@._V1_SX300.jpg 
	[Metascore] => N/A 
	[imdbRating] => 7.9 
	[imdbVotes] => 83,419
	[imdbID] => tt0364845 
	[Type] => series 
	[Response] => True 
) 





http://www.omdbapi.com/?i=tt0364845&Season=1		(ncis)
Array ( 
	[Title] => NCIS
	[Season] => 1 
	[Episodes] => Array (
		[0] => Array (
			[Title] => Yankee White 
			[Released] => 2003-09-23
			[Episode] => 1 
			[imdbRating] => 8.2 
			[imdbID] => tt0658039 ) 
		[1] => Array ( [Title] => Hung Out to Dry [Released] => 2003-09-30 [Episode] => 2 [imdbRating] => 7.5 [imdbID] => tt0658001 ) 
		[2] => Array ( [Title] => Seadog [Released] => 2003-10-07 [Episode] => 3 [imdbRating] => 7.5 [imdbID] => tt0658019 )
		[3] => Array ( [Title] => The Immortals [Released] => 2003-10-14 [Episode] => 4 [imdbRating] => 7.4 [imdbID] => tt0658030 )
		[4] => Array ( [Title] => The Curse [Released] => 2003-10-28 [Episode] => 5 [imdbRating] => 7.8 [imdbID] => tt0658027 ) 
		[5] => Array ( [Title] => High Seas [Released] => 2003-11-04 [Episode] => 6 [imdbRating] => 7.6 [imdbID] => tt0657998 ) 
		[6] => Array ( [Title] => Sub Rosa [Released] => 2003-11-18 [Episode] => 7 [imdbRating] => 7.9 [imdbID] => tt0658023 ) 
		[7] => Array ( [Title] => Minimum Security [Released] => 2003-11-25 [Episode] => 8 [imdbRating] => 7.5 [imdbID] => tt0658009 )
		[8] => Array ( [Title] => Marine Down [Released] => 2003-12-16 [Episode] => 9 [imdbRating] => 7.9 [imdbID] => tt0658007 )
		[9] => Array ( [Title] => Left for Dead [Released] => 2004-01-06 [Episode] => 10 [imdbRating] => 7.6 [imdbID] => tt0658004 ) 
		[10] => Array ( [Title] => Eye Spy [Released] => 2004-01-13 [Episode] => 11 [imdbRating] => 7.5 [imdbID] => tt0657993 ) 
		[11] => Array ( [Title] => My Other Left Foot [Released] => 2004-02-03 [Episode] => 12 [imdbRating] => 7.9 [imdbID] => tt0658012 )
		[12] => Array ( [Title] => One Shot, One Kill [Released] => 2004-02-10 [Episode] => 13 [imdbRating] => 8.1 [imdbID] => tt0658013 )
		[13] => Array ( [Title] => The Good Samaritan [Released] => 2004-02-17 [Episode] => 14 [imdbRating] => 7.6 [imdbID] => tt0658028 ) 
		[14] => Array ( [Title] => Enigma [Released] => 2004-02-24 [Episode] => 15 [imdbRating] => 7.7 [imdbID] => tt0657992 ) 
		[15] => Array ( [Title] => Bête Noire [Released] => 2004-03-02 [Episode] => 16 [imdbRating] => 8.2 [imdbID] => tt0657984 ) 
		[16] => Array ( [Title] => The Truth Is Out There [Released] => 2004-03-16 [Episode] => 17 [imdbRating] => 7.3 [imdbID] => tt0658032 )
		[17] => Array ( [Title] => UnSEALeD [Released] => 2004-04-06 [Episode] => 18 [imdbRating] => 7.8 [imdbID] => tt0658035 )
		[18] => Array ( [Title] => Dead Man Talking [Released] => 2004-04-27 [Episode] => 19 [imdbRating] => 8.1 [imdbID] => tt0657989 )
		[19] => Array ( [Title] => Missing [Released] => 2004-05-04 [Episode] => 20 [imdbRating] => 7.8 [imdbID] => tt0658010 ) 
		[20] => Array ( [Title] => Split Decision [Released] => 2004-05-11 [Episode] => 21 [imdbRating] => 7.7 [imdbID] => tt0658022 )
		[21] => Array ( [Title] => A Weak Link [Released] => 2004-05-18 [Episode] => 22 [imdbRating] => 7.3 [imdbID] => tt0657979 ) 
		[22] => Array ( [Title] => Reveille [Released] => 2004-05-25 [Episode] => 23 [imdbRating] => 8.0 [imdbID] => tt0658017 )
	)
	[Response] => True
)




http://www.omdbapi.com/?s=ncis

Array ( 
	[Search] => Array (
		[0] => Array (
			[Title] => NCIS [Year] => 2003– [imdbID] => tt0364845 [Type] => series [Poster] => http://ia.media-imdb.com/images/M/MV5BMTYyMTQ0MTU1OF5BMl5BanBnXkFtZTcwMjI0Njg4Ng@@._V1_SX300.jpg )
		[1] => Array (
			[Title] => NCIS: Los Angeles [Year] => 2009– [imdbID] => tt1378167 [Type] => series [Poster] => http://ia.media-imdb.com/images/M/MV5BMjIwNzY4NDk2NV5BMl5BanBnXkFtZTcwNTM4NDk3Mg@@._V1_SX300.jpg ) 
		[2] => Array (
			[Title] => NCIS: New Orleans [Year] => 2014– [imdbID] => tt3560084 [Type] => series [Poster] => http://ia.media-imdb.com/images/M/MV5BMTQ2ODIxNzE5NV5BMl5BanBnXkFtZTgwNjYzNDAxMzE@._V1_SX300.jpg ) 
		[3] => Array ( 
			[Title] => NCIS: Special Agent DiNozzo Visits Dr. Phil [Year] => 2012 [imdbID] => tt2554808 [Type] => movie [Poster] => N/A ) 
		[4] => Array (
			[Title] => NCIS Video Game [Year] => 2011 [imdbID] => tt1869580 [Type] => game [Poster] => N/A ) [5] => Array ( [Title] => NCIS: Season 9 - The Finish Line [Year] => 2012 [imdbID] => tt2523308 [Type] => movie [Poster] => N/A ) 
		[6] => Array (
			[Title] => Inside NCIS [Year] => 2012 [imdbID] => tt2591874 [Type] => series [Poster] => N/A ) 
		[7] => Array (
			[Title] => NCIS: Season 9 - Cast Roundtable [Year] => 2012 [imdbID] => tt2523400 [Type] => movie [Poster] => N/A )
		[8] => Array ( 
			[Title] => NCIS: Season 11 - NCIS in New Orleans [Year] => 2014 [imdbID] => tt4007422 [Type] => movie [Poster] => N/A ) 
		[9] => Array ( 
			[Title] => NCIS: Ducky's World [Year] => 2007 [imdbID] => tt2224357 [Type] => movie [Poster] => N/A )
	) 
	[totalResults] => 55 
	[Response] => True 
) 
*/
?>

<!DOCTYPE html>
<html>
  <head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="shortcut icon" href="logo.ico" type="image/x-icon" />
	<title>showTracteur</title>
	<meta name="description" content="This is a beautiful website about my TVshow" />
	<meta name="keywords" content="tv, show, tvshow, website, php" />
	<!--<link type="text/css" rel="stylesheet" href="stylsheet.css" />-->
	<script type="text/javascript">
		
		
		function getFromAPI(request){
			var xhr = new XMLHttpRequest();
			xhr.open("GET", "http://www.omdbapi.com/?"+request, false);
			
			xhr.send();
			
			var response = JSON.parse(xhr.responseText);
			
			return response;
		}
		
				
		function checkForDifference(imdbID){
			var nbrCheck = document.getElementById('nbrCheck').value;
			
			document.getElementById("output").innerHTML += "<br>Checking for differences for imdbID : "+imdbID;
			updateScroll();
			
			var API = getFromAPI('i='+imdbID);
			
			for(var i = 0; i < episodeJSON.length; i++){
				if(episodeJSON[i].imdbID == imdbID){
					var SQL = episodeJSON[i];
				}
			}
			
			if(API.totalSeasons == SQL.episodes.length){
				for(var i = 0; i < SQL.episodes.length; i++){
					document.getElementById("output").innerHTML += '<br>Season '+(i+1)+' : <span id="'+imdbID+i+'">0%</span>';
					updateScroll();
					
					var APISeason = getFromAPI('i='+imdbID+'&season='+(i+1));
					if(APISeason.Episodes.length == SQL.episodes[i].episode.length){
						for(var j = 0; j < SQL.episodes[i].episode.length; j++){
							document.getElementById(imdbID+i).innerHTML = (100*(j/(SQL.episodes[i].episode.length-1)))+'%';
							
							if(APISeason.Episodes[j].Episode == SQL.episodes[i].episode[j].episode){
								if(APISeason.Episodes[j].imdbID != SQL.episodes[i].episode[j].imdbID){
									document.getElementById("error").innerHTML += '<br><span class="red">Pas le même imdbID; API : '+APISeason.Episodes[j].imdbID+" SQL : "+SQL.episodes[i].episode[j].imdbID+'</span><button onclick="investigateEpisode(\''+APISeason.Episodes[j].imdbID+'\',\''+SQL.episodes[i].episode[j].imdbID+'\',\''+i+'\',\''+j+'\')">+</button>';
									//je sais pas trop comment updater ça
								}else{
									if(APISeason.Episodes[j].Released != SQL.episodes[i].episode[j].released && APISeason.Episodes[j].Released != 'N/A'){
										document.getElementById("error").innerHTML += '<br><span class="red"><input type="checkbox" name="check'+nbrCheck+'" value="1"><input type="hidden" name="type'+nbrCheck+'" value="released"><input type="hidden" name="id'+nbrCheck+'" value="'+SQL.episodes[i].episode[j].imdbID+'">Pas le même Released; API : '+APISeason.Episodes[j].Released+" SQL : "+SQL.episodes[i].episode[j].released+'</span>';
										nbrCheck++;
									}
									if(escapeHtml(APISeason.Episodes[j].Title) != SQL.episodes[i].episode[j].title){
										document.getElementById("error").innerHTML += '<br><span class="red"><input type="checkbox" name="check'+nbrCheck+'" value="1"><input type="hidden" name="type'+nbrCheck+'" value="title"><input type="hidden" name="id'+nbrCheck+'" value="'+SQL.episodes[i].episode[j].imdbID+'">Pas le même Title; API : '+APISeason.Episodes[j].Title+" SQL : "+SQL.episodes[i].episode[j].title+'</span>';
										nbrCheck++;
									}
								}
							}else{
								document.getElementById("error").innerHTML += '<br><span class="red">Pas le même épisodes dont on parle pour la serie avec imdbID: '+imdbID+' à la saison: '+(i+1)+" ; API : "+APISeason.Episodes[j].Episode+" SQL : "+SQL.episodes[i].episode[j].episode+'</span><button onclick="investigateSeason(\''+imdbID+'\',\''+(i+1)+'\')">+</button>';
							}
							
							if(j == SQL.episodes[i].episode.length-1){
								document.getElementById(imdbID+i).innerHTML = 'DONE';
							}
						}
					}else{
						document.getElementById("error").innerHTML += '<br><span class="red">Pas le même nbr d\'épisodes pour la serie avec imdbID: '+imdbID+' à la saison: '+(i+1)+" ; API : "+APISeason.Episodes.length+" SQL : "+SQL.episodes[i].episode.length+'</span><button onclick="investigateSeason(\''+imdbID+'\',\''+(i+1)+'\')">+</button>';
					}
				}
			}else{
				document.getElementById("error").innerHTML += '<br><span class="red">Pas le même nbr de saison pour la serie avec imdbID: '+imdbID+'</span>';
			}
			
			document.getElementById('nbrCheck').value = nbrCheck;
			
			//
			//console.log(APISeason);
			//console.log(SQL);
		}
		
		function escapeHtml(text) {
			return text
			.replace(/&/g, "&amp;")
			.replace(/</g, "&lt;")
			.replace(/>/g, "&gt;")
			.replace(/"/g, "&quot;")
			.replace(/'/g, "&#039;");
		}
		
		function checkAll(){
			for(var i = 0; i < imdbIDArray.length; i++){
				checkForDifference(imdbIDArray[i]);
			}
			//alert("ALL DONE");
		}
		
		function updateScroll(){
			var element = document.getElementById("output");
			element.scrollTop = element.scrollHeight;
		}
		
		function updateEpisode(imdbID, error){
			switch(error){
				case 'released':
					alert('yep');
				break;
				case 'title':
					alert('yop');
				break;
			}
		}
		
		function investigateEpisode(api, sql, i, j){
			//console.log(episodeJSON);
			document.getElementById("output").innerHTML += '<br>checking API : <span id="api'+api+'">0%</span>';
			updateScroll();
			
			var APIObject = getFromAPI('i='+api);
			document.getElementById("error").innerHTML += '<br>API:';
			document.getElementById("error").innerHTML += '<br>'+JSON.stringify(APIObject);
			
			document.getElementById('api'+api).innerHTML = 100;
			
			document.getElementById("output").innerHTML += '<br>checking sql : <span id="sql'+api+'">0%</span>';
			updateScroll();
			
			var k = 0;
			
			for(var h = 0; h < episodeJSON.length; h++){
				for(var i = 0; i < episodeJSON[h].episodes.length; i++){
					for(var j = 0; j < episodeJSON[h].episodes[i].episode.length; j++){
						if(episodeJSON[h].episodes[i].episode[j].imdbID == sql){
							SQLObject = episodeJSON[h].episodes[i].episode[j];
							k++;
							document.getElementById('sql'+api).innerHTML = (100*(k/((episodeJSON.length-1)*(episodeJSON[h].episodes.length-1)*(episodeJSON[h].episodes[i].episode.length-1))))+'%';
						}
					}
				}
			}
			
			//JSON.parse(sql);
			
			document.getElementById("error").innerHTML += '<br>SQL:';
			document.getElementById("error").innerHTML += '<br>'+JSON.stringify(SQLObject);
			
						
			
		}
		
		function investigateSeason(imdbID, season){
			var nbrCheck = document.getElementById('nbrCheck').value;
			//console.log(episodeJSON);
			document.getElementById("output").innerHTML += '<br>Fetching info for imdbID : '+imdbID+' season '+season+' : <span id="season'+imdbID+imdbID+'">0%</span>';
			updateScroll();
			
			var APIObject = getFromAPI('i='+imdbID+'&season='+season);
			
			
			document.getElementById('season'+imdbID+imdbID).innerHTML = '50%';
			
			for(var i = 0; i < episodeJSON.length; i++){
				if(episodeJSON[i].imdbID == imdbID){
					for(var j = 0; j < episodeJSON[i].episodes.length; j++){
						if(episodeJSON[i].episodes[j].season == season){
							var SQLObject = episodeJSON[i].episodes[j];
						}
					}
				}
			}
			
			document.getElementById('season'+imdbID+imdbID).innerHTML = 'DONE';
			
			document.getElementById("error").innerHTML += '<br>Results for '+APIObject.Title+' imdbID : '+imdbID+' season : '+season;
			
			var tableString = '<br><table><tr><th>API</th><th>n°</th><th>imdbID</th><th>title</th><th>SQL</th><th>n°</th><th>imdbID</th><th>title</th></tr>';
			for(var i = 0; i < APIObject.Episodes.length || i < SQLObject.episode.length ; i++){
				if(i < APIObject.Episodes.length && i < SQLObject.episode.length){
					tableString += '<td><input type="checkbox" name="check'+nbrCheck+'" value="1"><input type="hidden" name="type'+nbrCheck+'" value="New"><input type="hidden" name="id'+nbrCheck+'" value="'+APIObject.Episodes[i].imdbID+'"></td><td>'+APIObject.Episodes[i].Episode+'</td><td>'+APIObject.Episodes[i].imdbID+'</td><td>'+APIObject.Episodes[i].Title+'</td><td></td><td>'+SQLObject.episode[i].episode+'</td><td>'+SQLObject.episode[i].imdbID+'</td><td>'+SQLObject.episode[i].title+'</td></tr>';
					nbrCheck++;
				}else if(i < APIObject.Episodes.length){
					tableString += '<td><input type="checkbox" name="check'+nbrCheck+'" value="1"><input type="hidden" name="type'+nbrCheck+'" value="New"><input type="hidden" name="id'+nbrCheck+'" value="'+APIObject.Episodes[i].imdbID+'"></td><td>'+APIObject.Episodes[i].Episode+'</td><td>'+APIObject.Episodes[i].imdbID+'</td><td>'+APIObject.Episodes[i].Title+'</td><td></td><td></td><td></td><td></td></tr>';
					nbrCheck++;
				}else if(i < SQLObject.episode.length){
					tableString += '<td></td><td></td><td></td><td></td><td></td><td>'+SQLObject.episode[i].episode+'</td><td>'+SQLObject.episode[i].imdbID+'</td><td>'+SQLObject.episode[i].title+'</td></tr>';
				}
			}
			
			tableString += '</table>';
			document.getElementById("error").innerHTML += tableString;
			
			document.getElementById('nbrCheck').value = nbrCheck;
		}
		
		function update(){
			document.getElementById('formError').submit();
			//alert('hi');
		}
		
		function clearIt(){
			//console.log("hey");
			//document.getElementById("error").innerHTML = "";
			document.getElementById("output").innerHTML = "";
		}
		
	</script>
	<style>
		footer{position:fixed; bottom:0px;display:box;}
		.red{color:red;}
		.console{height:200px; background-color:black; color:green; overflow-y:hidden;}
		.error{position:absolute; top:250px; bottom:40px; overflow-y:auto}
	</style>
  </head>
  <body onload="checkAll();">
	<button onClick="checkAll();">Check All</button>
	<button onClick="window.location = 'http://localhost/showTracteur/utility/update.php';">Reload</button>
	<button onClick="clearIt();">clear</button>
	<!--<button onClick="clearIt()">Raccourci</button>-->
	<div id="output" class="console">
		
	</div>
	<input type="hidden" value="0" id="nbrCheck">
	<form id="formError" method="post">
		<input type="hidden" name="update" value="1">
		<div id="error" class="error">
		
		</div>
	</form>
	
	<button onclick="update();">Update</button>
	
	<footer>
		<p>Copyright &copy; 2016 userOneOOne</p>
	</footer>
	<script type="text/javascript">
		<?php
		$imdbIDArrayString = imdbIDArrayString();
		echo 'var imdbIDArray = ['.$imdbIDArrayString.'];';
		?>
		
		<?php
		$episodeJSONString = episodeJSONString();
		echo 'var episodeJSON = '.$episodeJSONString.';';
		?>
	</script>
  </body>
</html>
