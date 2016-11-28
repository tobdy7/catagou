<?php
/*
	corect the imdb update problem
	some bug on the order of display modification
	addn the ability to sort the entry to updates
	add problem solving like add an episode or add a show from scratch
	//style the shit out of this page
	possibility to not show a show on the welcome
	maybe a new table for re-watching
	a function to search particular episode based on name id,...
	do i realy need to get a new PDO object every time¿
	
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

try{
	$db = new PDO('mysql:host=localhost;dbname=showTracteur', 'root', '');
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}catch (Exception $e){
	die('Error : ' . $e->getMessage());
}

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
session_start();

//save the show in the db
function insertNewShow($showId){
	
	$db = loadPDO();
	
	//check if the show is allready in the db
	$stmt2 = $db->prepare("SELECT imdbID FROM tvShow WHERE imdbID = :imdbID;");
	$stmt2->bindParam(':imdbID', $showId);
	$stmt2->execute();
	if($stmt2->fetch()){
		return False;
	}else{
		$erroCount = 0;
		//insertion of episodes
		$stmt = $db->prepare("INSERT INTO episode (imdbID, show_imdbID, title, season, episode, released)
		VALUES (:imdbID, :show_imdbID, :title, :season, :episode, :released);");
		$stmt->bindParam(':imdbID', $imdbID);
		$stmt->bindParam(':show_imdbID', $show_imdbID);
		$stmt->bindParam(':title', $title);
		$stmt->bindParam(':season', $season);
		$stmt->bindParam(':episode', $episode);
		$stmt->bindParam(':released', $released);
		
		$i=1;
		do {
			$listEpisode = getFromAPI('i='.$showId.'&Season='.$i);
			$j=0;
			do{
				
				$title = htmlspecialchars($listEpisode['Episodes'][$j]['Title'], ENT_QUOTES);
				$released = date("Y-m-d", strtotime($listEpisode['Episodes'][$j]['Released']));
				$episode = $listEpisode['Episodes'][$j]['Episode'];
				$imdbID = $listEpisode['Episodes'][$j]['imdbID'];
				$show_imdbID = $showId;
				$season = $listEpisode['Season'];
				
				$test2 = getFromAPI('i='.$showId.'&Season='.$i);
				
				try{
					$stmt->execute();
				}catch (Exception $e){
					die('Error : ' . $e->getMessage());
					$erroCount++;
				}
				
				$j++;
			}while(isset($test2['Episodes'][$j]));
	
			unset($listeEpisode);
			
			$i++;
			$test = getFromAPI('i='.$showId.'&Season='.$i);
		} while ($test['Response'] == 'True');
		unset($test);
		
		//insertion dans show
		
		$stmt = $db->prepare("INSERT INTO tvShow (imdbID, showName, poster, nbrSeason ) VALUES (:imdbID2, :showName, :poster, :nbrSeason );");
		
		$stmt->bindParam(':imdbID2', $imdbID2);
		$stmt->bindParam(':showName', $showName);
		$stmt->bindParam(':poster', $poster);
		$stmt->bindParam(':nbrSeason', $nbrSeason);
		
		$infoShow = getFromAPI('i='.$showId);
		
		$imdbID2 = $showId;
		$showName = htmlspecialchars($infoShow['Title'], ENT_QUOTES);
		$poster = urlencode($infoShow['Poster']);
		$nbrSeason = $i-1;
		
		try{
			$stmt->execute();
		}catch (Exception $e){
			die('Error : ' . $e->getMessage());
			$erroCount++;
		}
		unset($infoShow);
		
		if($erroCount != 0){
			return False;
		}else{
			return True;
		}
	}
}

//load and return a string to display all the tv show
function loadMyShow(){
	$db = loadPDO();
	
	$statement = $db->prepare("SELECT * FROM tvShow");
	$statement->execute();
	
	$string = '<div>';
	while($result = $statement->fetch())
	{
		$string .= '<div class="horizontalList" onClick="linkIn(\'page=tvShow&imdbID='.$result['imdbID'].'\');"><img src="'.urldecode($result['poster']).'" height="300" width="200"><br><h4>'.$result['showName'].'</h4></div>';//<span onClick="linkIn(\'page=tvShow&toDelete='.$result['imdbID'].'\');" class="red deleteButton">&#10006;</span>
		//print_r($result);
	}
	$string .= '</div>
';
	
	
	return $string;
}

//load and return a string to display ONE tv show
function loadThisShow($imdbID){
	$db = loadPDO();
	
	$season = "not today";
	
	$statement = $db->prepare("SELECT * FROM tvShow WHERE imdbID = :imdbID");
	$statement->execute(array(':imdbID' => $imdbID));
	
	$result = $statement->fetch();
	
	$statement2 = $db->prepare("SELECT imdbID, title, season, episode, statusForMe, released FROM episode WHERE show_imdbID = :show_imdbID ORDER BY season DESC, episode DESC");
	$statement2->execute(array(':show_imdbID' => $imdbID));
	
	$string = '<div>
		<div class="infoShow">
			<img src="'.urldecode($result['poster']).'" height="300"><br>
			<h4>'.$result['showName'].'</h4>
		</div>
		<table class="tableInLineSmall">';
	while($result2 = $statement2->fetch()){
		switch($result2['statusForMe']){
			case 0://not yet define
				$status = '&#9898;';
				break;
			case 1://defined as not seen
				$status = '&#9898;';
				break;
			case 2://defined as seen
				$status = '&#9899;';
				break;
		}
		
		
		if($result2['season'] != $season){
			$string .= 
			'<tr>
				<td></td>
				<td><span class="b">Season: </span>'.$result2['season'].'
				</td>
			</tr>';
			$season = $result2['season'];
		}
		$string .= 
		'<tr onClick="linkIn(\'page=tvShow&imdbID='.$imdbID.'&episodeID='.$result2['imdbID'].'\');">
			<td>'.$result2['episode'].'</td>
			<td>'.$result2['title'].'</td>
			<td>'.date('d-m-Y', strtotime($result2['released'])).'</td>
			<td class="big">'.$status.'</td>
		</tr>';
	}
   
	$string .= '</table></div>
';
	
	
	return $string;
}

function deletShow($imdbID){
	$db = loadPDO();
	
	$statement = $db->prepare("DELETE FROM tvShow WHERE imdbID = :imdbID");
	$statement->execute(array(':imdbID' => $imdbID));
	$statement2 = $db->prepare("DELETE FROM episode WHERE show_imdbID = :show_imdbID");
	$statement2->execute(array(':show_imdbID' => $imdbID));

}

function loadListOfShow(){
	$db = loadPDO();
	
	$string = '';
	$statement = $db->prepare("Select imdbID, showName FROM tvShow ORDER BY showName");
	$statement->execute();
	
	while($result = $statement->fetch()){
		$string .= '<option value="'.$result['imdbID'].'">'.$result['showName'].'</option>';
	}
	
	return $string;
}

function updateStatus($episodeImdbID){
	$db = loadPDO();
	
	$statement = $db->prepare("SELECT statusForMe, episode, season, show_imdbID FROM episode WHERE imdbID = :imdbID");
	$statement->execute(array(':imdbID' => $episodeImdbID));
	$result = $statement->fetch();
	
	if($result['statusForMe'] == 0){
		$statement2 = $db->prepare("UPDATE episode SET statusForMe = 2 WHERE (show_imdbID = :show_imdbID AND season < :season) OR (show_imdbID = :show_imdbID AND season = :season AND episode <= :episode)");
		$statement2->execute(array(
			':show_imdbID' => $result['show_imdbID'],
			':season' => $result['season'],
			':episode' => $result['episode']
		));
		$statement3 = $db->prepare("UPDATE episode SET statusForMe = 1 WHERE (show_imdbID = :show_imdbID AND season > :season) OR (show_imdbID = :show_imdbID AND season = :season AND episode > :episode)");
		$statement3->execute(array(
			':show_imdbID' => $result['show_imdbID'],
			':season' => $result['season'],
			':episode' => $result['episode']
		));	
			//set the others to 1
	}elseif($result['statusForMe'] == 1){
		$statement4 = $db->prepare("UPDATE episode SET statusForMe = 2 WHERE imdbID = :imdbID");
		$statement4->execute(array(':imdbID' => $episodeImdbID));
	}elseif($result['statusForMe'] == 2){
		$statement5 = $db->prepare("UPDATE episode SET statusForMe = 1 WHERE imdbID = :imdbID");
		$statement5->execute(array(':imdbID' => $episodeImdbID));
	}
	
}

function checkForUpdates($showId){
	$db = loadPDO();

	
	$changeCount = 0;
	$listOfChange = array();


	//comparison of episodes
	$statement = $db->prepare("SELECT title, released, episode, imdbID, season FROM episode WHERE show_imdbID = :show_imdbID ORDER BY season, episode");
	$statement->execute(array(':show_imdbID' => $showId));
	
		
	$i=1;
	do{
		$listEpisode = getFromAPI('i='.$showId.'&Season='.$i);
		
				
		$j=0;
		do{
			$APITitle = $listEpisode['Episodes'][$j]['Title'];
			$APIReleased = $listEpisode['Episodes'][$j]['Released'];
			$APIEpisode = $listEpisode['Episodes'][$j]['Episode'];
			$APIImdbID = $listEpisode['Episodes'][$j]['imdbID'];
			$APISeason = $listEpisode['Season'];
			
			$thisEpisodeChangeCount = 0;
			
			$result = $statement->fetch();
			if($result['imdbID'] != $APIImdbID){
				$statement2 = $db->prepare("SELECT season FROM episode WHERE imdbID = :imdbID");
				$statement2->execute(array('imdbID' => $APIImdbID));
				if($result2 = $statement2->fetch()){
					$listOfChange[] = array('imdbID'=>$result['imdbID'], 'error'=>'imdbID', 'sql'=>$result['imdbID'],'API'=>$APIImdbID);
					$changeCount++;
					$thisEpisodeChangeCount++;
				}else{
					$listOfChange[] = array('imdbID'=>$APIImdbID, 'error'=>'New', 'sql'=>'ø','API'=>$APITitle);
					$changeCount++;
					$thisEpisodeChangeCount++;
				}
				
			}else{
				if($result['title'] != htmlspecialchars($APITitle, ENT_QUOTES)){
					$listOfChange[] = array('imdbID'=>$result['imdbID'], 'error'=>'title', 'sql'=>$result['title'],'API'=>htmlspecialchars($APITitle, ENT_QUOTES));
					$changeCount++;
					$thisEpisodeChangeCount++;
				}
				
				if($result['released'] != $APIReleased AND !(date("Y-m-d", strtotime($result['released'])) == 0000-00-00 OR $APIReleased == 'N/A')){
					$listOfChange[] = array('imdbID'=>$result['imdbID'], 'error'=>'released', 'sql'=>$result['released'],'API'=>$APIReleased);
					$changeCount++;
					$thisEpisodeChangeCount++;
				}
				if($result['episode'] != $APIEpisode){
					$listOfChange[] = array('imdbID'=>$result['imdbID'], 'error'=>'episode', 'sql'=>$result['episode'],'API'=>$APIEpisode);
					$changeCount++;
					$thisEpisodeChangeCount++;
				}
				if($result['season'] != $APISeason){
					$listOfChange[] = array('imdbID'=>$result['imdbID'], 'error'=>'season', 'sql'=>$result['season'],'API'=>$APISeason);
					$changeCount++;
					$thisEpisodeChangeCount++;
				}
			}
			if($thisEpisodeChangeCount == 0){
				//tout est ok
			}
			
			
			$test2 = getFromAPI('i='.$showId.'&Season='.$i);
			
			
			$j++;
		}while(isset($test2['Episodes'][$j]));
		unset($test2);
		unset($listeEpisode);
		
		$i++;
		$test = getFromAPI('i='.$showId.'&Season='.$i);
	} while ($test['Response'] == 'True');
	unset($test);
	
	
	
	if($changeCount != 0){
		return ($listOfChange);
	}else{
		return False;
	}
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

function getToWatch(){
	
	$db = loadPDO();
	
	$toWatch = array();
	
	$statement = $db->prepare("
	SELECT 
		e.title AS title, 
		e.show_imdbID AS show_imdbID, 
		e.episode AS episode, 
		e.imdbID AS imdbID, 
		e.season AS season 
	FROM 
		episode e 
	JOIN 
		tvShow s ON s.imdbID = e.show_imdbID 
	WHERE 
		(e.statusForMe = 0 OR e.statusForMe = 1) 
		AND s.display > 0 
		AND DATE(e.released) <= CURDATE() 
		AND DATE_FORMAT(e.released, '%Y-%m-%d') != 0000-00-00 
		AND DATE_FORMAT(e.released, '%Y-%m-%d') != '1970-01-01' 
	ORDER BY s.display, e.show_imdbID, e.season, e.episode
	;");
	$statement->execute();
	
	$episodeToWatch = array();
	
	$result = $statement->fetch();
	
	$showID = 0.2;
	
	for($i=0;$result['show_imdbID'];$i++){
		$showID = $result['show_imdbID'];
		
		$statement2 = $db->prepare("SELECT showName, poster FROM tvShow WHERE imdbID = :showImdbID");
		$statement2->execute(array(':showImdbID' => $showID));
		$result2 = $statement2->fetch();
		
		$episodeToWatch[$i] = array('showName' => $result2['showName'], 'imdbID' => $showID, 'poster' => $result2['poster']);
		
		for($j=0, $showID = $result['show_imdbID']; $result['show_imdbID'] == $showID AND $j <= 150;$j++, $result = $statement->fetch()){
			$episodeToWatch[$i]['episodes'][] = array('title' => $result['title'], 'showImdbID' => $result['show_imdbID'], 'episode' => $result['episode'], 'imdbID' => $result['imdbID'], 'season' => $result['season']);
		}		
	}
	
	return $episodeToWatch;
	
}

//hide a show from the home page
function hideShow($imdbID){
	$db = loadPDO();
	
	$statement = $db->prepare("UPDATE tvShow SET display = 0 WHERE imdbID = :imdbID");
	$statement->execute(array(':imdbID' => $imdbID));
	
	return True;
}

//change the display order
function moveDisplayOrder($imdbID, $incrementation){
	//don't know where the bug is yet the bug is thtat somme show aren't display allthougt they shgould check statusForMe
	$db = loadPDO();
	$displayOrder = array();
	$i = 0;
	
	$statement = $db->prepare("SELECT imdbID FROM tvShow WHERE display != 0 ORDER BY display");
	$statement->execute();
	
	while($result = $statement->fetch()){
		if($imdbID == $result['imdbID']){
			$displayOrder[$i+$incrementation] = $result['imdbID'];
		}else{
			$i += 2;
			$displayOrder[$i] = $result['imdbID'];
		}
	}
	
	ksort($displayOrder);
	
	
	//debug
	/*
	print "<pre>";
	print_r($displayOrder);
	print "</pre>";
	*/
	
	$order = 2;
	foreach($displayOrder as $id){
		//echo $id.'<br>';
		$statement2 = $db->prepare("UPDATE tvShow SET display = :order WHERE imdbID = :imdbID");
		$statement2->execute(array(':order' => $order, ':imdbID' => $id));
		$order++;
	}
}

//check if all the episode out yet have been seen
function checkStatus(){
	$db = loadPDO();
	
	//set status to 2 for the show where all available episode have been seen
	$statement = $db->prepare("
	SELECT 
		s.imdbID AS imdbID, 
	FROM 
		tvShow s 
	JOIN 
		episode e ON e.show_imdbID = s.imdbID 
	WHERE 
		(e.statusForMe = 0 OR e.statusForMe = 1) 
		AND DATE(e.released) <= CURDATE() 
		AND DATE_FORMAT(e.released, '%Y-%m-%d') != 0000-00-00 
		AND DATE_FORMAT(e.released, '%Y-%m-%d') != '1970-01-01' 
	");
	$statement->execute();
	$result = $statement->fetch();
	print $result;
}

//checkStatus();
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
	case 'addShow':
		//display the show from the result of the search
		if(isset($_POST['searchShow'])){
			$listOfShows = getFromAPI('s='.urlencode($_POST['showTitle']));
			if($listOfShows['Response']=='True'){
				$displayTarget = '<table class="tableInLine">';
				for($i=1, $n=$listOfShows['totalResults']; 10*($i-1) < $n; $i++){
					$listePage = getFromAPI('s='.urlencode($_POST['showTitle']).'&page='.$i);
					for($j=0;$j<10, isset($listePage['Search'][$j]['Type']);$j++){
						if($listePage['Search'][$j]['Type']=='series'){
							$displayTarget .= '<tr onClick="document.location.href=\'http://localhost/showTracteur/showTracteur.php?page=addShow&showID='.$listePage['Search'][$j]['imdbID'].'\'">
								<td>
									<img src="'.$listePage['Search'][$j]['Poster'].'" height="160">
									<td>
										<span class="b big">Show title: </span>
										<span class="big">'.$listePage['Search'][$j]['Title'].'</span>
										<br><br>
										<span class="b big">Released:   </span>
										<span class="big">'.$listePage['Search'][$j]['Year'].'</span>
									</td>
								</td>
							</tr>';
						}
					}
				}
				$displayTarget .= '</table>';
			}else{
				$displayTarget = 'No show was found with that parameter';
			}
		}
		
		//saving the show in the db
		if(isset($_GET['showID'])){
			if(insertNewShow($_GET['showID'])){
				header('Location: http://localhost/showTracteur/showTracteur.php?page=tvShow&imdbID='.$_GET['showID']);
				die();
			}else{
				$displayTarget = 'an error occurred';
			}
		}

		break;
		
	case 'myShow':
		$displayTarget = loadMyShow();
		
		break;
		
	case 'tvShow':
		if(isset($_GET['episodeID'])){
			updateStatus($_GET['episodeID']);
		}
		
		$displayTarget = loadThisShow($_GET['imdbID']);
		break;
		
	case 'param':
		//delete a show
		if(isset($_GET['toDelete'])){
			deletShow($_GET['toDelete']);
		}
		
		//episode update
		if(isset($_GET['updateEpisode'])){
			for($i=0; isset($_GET['episodeToUpdate'.$i]); $i++){
				if($_GET['episodeToUpdate'.$i] != 'False'	){
					updateEpisode($_GET['episodeToUpdate'.$i], $_GET['typeOfUpdate'.$i]);
				}
			}
		}
		
		//choice of episodes to update
		if(isset($_GET['update'])){
			//check differences between the API and de db
			$possiblyToUpdate = checkForUpdates($_GET['update']);
			print '<pre>';
			print_r($possiblyToUpdate);
			print '</pre>';
			
			if($possiblyToUpdate){
				
				//format in an orderly fashion
				$stringOfUpdates = '<form class="updateList">
				<input type="hidden" name="page" value="param"/>
				<input type="hidden" name="update" value="'.$_GET['update'].'"/>
				<input type="hidden" name="updateEpisode" value="True"/>
					<table class="tableInLineSmall ">
						<th>imdbID</th>
						<th>Error</th>
						<th>sql</th>
						<th>API</th>
						<th><input type="checkbox" id="checkThem" onclick="checkThemAll('.(count($possiblyToUpdate)-1).')"/></th>';
				$j=0;
				for($i=0; isset($possiblyToUpdate[$i]); $i++){
					//if($possiblyToUpdate[$i]['error'] != 'imdbID'){
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
					//}
				}
				$stringOfUpdates .= '<tr><td><input type="submit" value="update" onclick="uncheckedForm('.$j.')"  class="form-submit updateButton"/></td></tr>
				</table>
				
				
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
		
	case '':
	case 'home':
	default:
		//hide a show from the home page
		if(isset($_GET['toHide'])){
			hideShow($_GET['toHide']);
		}
		
		//change the order of display of the shows
		if(isset($_GET['up'])){
			moveDisplayOrder($_GET['up'],-1);
		}
		if(isset($_GET['down'])){
			moveDisplayOrder($_GET['down'],1);
		}
		
		
		$toWatch = getToWatch();
		
		
		$displayTarget = '<div class="infoShow"><table class="tableSimple">';
		
		for($i=0;$i<count($toWatch);$i++){
			$displayTarget .= '
				<tr>
					<td>
						<img src="'.urldecode($toWatch[$i]['poster']).'" height="231" width="154">
					</td>
					<td>
						<h4>
							<span onClick="linkIn(\'page=tvShow&imdbID='.$toWatch[$i]['imdbID'].'\');" class="clickable">
								'.$toWatch[$i]['showName'].'
							</span>
							<span onClick="linkIn(\'up='.$toWatch[$i]['imdbID'].'\');" class="up clickable">&#8679;</span>
							<span onClick="linkIn(\'down='.$toWatch[$i]['imdbID'].'\');" class="down clickable">&#8681;</span>
							<span onClick="linkIn(\'toHide='.$toWatch[$i]['imdbID'].'\');" class="cross clickable">&#10006;</span>
						</h4>
						<table class="tableInLineSmall">';
			$j=0;
			while(isset($toWatch[$i]['episodes'][$j]) AND $j<5){// 
			//for($j=0;$j<5;$j++){
				$displayTarget .= '<tr>
					<td width="35px" onClick="linkIn(\'page=tvShow&imdbID='.$toWatch[$i]['imdbID'].'\');" >s'.$toWatch[$i]['episodes'][$j]['season'].'e'.$toWatch[$i]['episodes'][$j]['episode'].'</td>
					<td width="200px" onClick="linkIn(\'page=tvShow&imdbID='.$toWatch[$i]['imdbID'].'\');" > '.$toWatch[$i]['episodes'][$j]['title'].'</td>
					<td width="20px" class="big" onClick="linkIn(\'page=tvShow&imdbID='.$toWatch[$i]['episodes'][$j]['showImdbID'].'&episodeID='.$toWatch[$i]['episodes'][$j]['imdbID'].'\');" >&#9898;</td>
				</tr>
				';
			
			
				$j++;
			}
			
			//display the number of episode not displayed
			if(count($toWatch[$i]['episodes'])>5){
				$displayTarget .= '
					<tr onClick="linkIn(\'page=tvShow&imdbID='.$toWatch[$i]['imdbID'].'\');">
						<td>+'.(count($toWatch[$i]['episodes'])-5).'</td>
						<td></td>
						<td></td>
					</tr>';
			}
			$displayTarget .= '
			</table>
		</td>
	</tr>';
		}
		
		$displayTarget .= '</table></div>';
		/*
		print "<pre>";
		print_r($toWatch);
		print "</pre>";
		*/
		
		break;
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
	<link rel="shortcut icon" href="res/logo.ico" type="image/x-icon" />
	<title>showTracteur</title>
	<meta name="description" content="This is a beautiful website about my TVshow" />
	<meta name="keywords" content="tv, show, tvshow, website, php" />
	<link type="text/css" rel="stylesheet" href="stylsheet.css" />
	<script type="text/javascript">
		function linkIn(argument){
			location.href='showTracteur.php?'+argument;
		}
		function selectNavLink(){
			page = <?php if($_GET['page']!='tvShow'){echo '"'.$_GET['page'].'Nav"';}else{echo '"myShowNav"';}?>;
			document.getElementById(page).className += " navLink-selected";
		}
		function uncheckedForm(nbr){//so that if a checkbox is not checked the value return is false
			for(var i = 0; i<= nbr; i++){
				var check = document.getElementById("episodeToUpdate"+i);
				if(check.checked) {
					document.getElementById("episodeToUpdateHidden"+i).disabled = true;
				}else{
					document.getElementById("typeOfUpdate"+i).disabled = true;
				}
			}
		}
		
		function checkThemAll(nbr) {
			var checkStatus = document.getElementById("checkThem");
			for(var i = 0; i<= nbr; i++){
				var elementsToChange = document.getElementById("episodeToUpdate"+i);
				elementsToChange.checked = checkStatus.checked;
			}
			
		}
		/*
		    console.log('lala bumboum');
		*/
	</script>
	<style>
		
	</style>
  </head>
  <body onLoad="selectNavLink()">
	<div id="container">
		<header>
			<img src="res/logo.png" class="imgHeader">
			<span class="title">showTracteur</span>
		</header>
		<nav>
			<div onClick="linkIn('page=home');" class="navLink" id="homeNav">Home</div>
			<div onClick="linkIn('page=addShow');" class="navLink" id="addShowNav">Add a TV show</div>
			<div onClick="linkIn('page=myShow');"  class="navLink" id="myShowNav">My TV show</div>
			<div onClick="linkIn('page=param');"  class="navLink" id="paramNav">Parameters</div>
		</nav>
		<div class="article">
			<?php
	switch ($_GET['page']) {
	case 'addShow':
			?>
			<div>
				<form class="form" method="post" action="showTracteur.php?page=addShow">
		   			<input type="text" name="showTitle" class="form-input" autofocus/>
					<input type="submit" name="searchShow" value="Search" class="form-submit"/>
		 		</form>
			</div>
			<div>
			<?php
			if(isset($displayTarget)){
				echo $displayTarget;
			}
			?>
			
			</div>
			<?php
			break;// **********
			
		case 'myShow':
		    ?>
			
			<div id="myshow">
			<?php
			if(isset($displayTarget)){
				echo $displayTarget;
			}
			?>
			</div>
			
			<?php
		    break;// **********
		    
		case 'tvShow':
		    ?>
			<?php
			if(isset($displayTarget)){
				echo $displayTarget;
			}
			?>
			
			<?php
		    break;// **********
		    
		case 'param':
		    ?>
		    <form class="form" action="showTracteur.php">
				Delete a show
				<select name="toDelete">
					<?php
			if(isset($displayTarget)){
				echo $displayTarget;
			}
					?>
				</select>
				<input type="hidden" name="page" value="param"/>
				<input type="submit" value="Delete"  class="form-submit"/>
			</form>
			<br><br>
			
			<form class="form" action="showTracteur.php">
				Update a show
				<select name="update">
					<?php
			if(isset($displayTarget)){
				echo $displayTarget;
			}
					?>
				</select>
				<input type="hidden" name="page" value="param"/>
				<input type="submit" value="Check for updates" class="form-submit"/>
			</form>
			<br><br>
			
			<form class="form" action="showTracteur.php">
				Update all show
				<input type="hidden" name="allUpdate" value="true"/>
				<input type="hidden" name="page" value="param"/>
				<input type="submit" value="Check for updates for all" class="form-submit"/>
			</form>
			
			<?php
			if(isset($stringOfUpdates)){
				echo $stringOfUpdates;
			}
			?>
			<button onclick="window.open('utility/update.php','_blank')">update BETA</button>
			<?php
		    break;// **********
		    
		case '':
		case 'accueil':
		default:
			?>
			<h1>What's still to watch:</h1><br>
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
