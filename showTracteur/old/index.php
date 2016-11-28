<?php
/*
	update all doesn't work
	I can't freaking update the release correctly don't now why
	-si j'ajoute un show les release sont correct
	-si je l'upload pas...
	
	put the button on the bottom
	on welcome the episodes I need to watch
	add problem solving like add an episode or add a show from scratch
	we shouldn't be able to add a show already added
	//style the shit out of this page
	 possibility to not show a show on the welcome
	maybe a new table for re-watching
	redirect to thisShow after add
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
  
*/
//print_r(getFromAPI('i='));
try{
    $bdd = new PDO('mysql:host=localhost;dbname=showTracteur', 'root', '');
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}catch (Exception $e){
    die('Error : ' . $e->getMessage());
}

/* ===== FUNCTIONS ===== */

//request on the API
function getFromAPI($search) {
	$request = 'http://www.omdbapi.com/?'.$search;
	$response = file_get_contents($request);
	$jsonobj = json_decode($response, true);
	return $jsonobj;
}

//save the show in the db
function insertNewShow($showId){
	
	try{
	    $bdd = new PDO('mysql:host=localhost;dbname=showTracteur', 'root', '');
    	$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}catch (Exception $e){
	    die('Error : ' . $e->getMessage());
	}
	
	//check if the show is allready in the db
	$stmt2 = $bdd->prepare("SELECT imdbID FROM tvShow WHERE imdbID = :imdbID;");
    $stmt2->bindParam(':imdbID', $showId);
    $stmt2->execute();
    if($result2 = $stmt2->fetch){
    	return False;
    }else{
		$erroCount == 0;
		//insertion of episodes
		$stmt = $bdd->prepare("INSERT INTO episode (imdbID, show_imdbID, title, season, episode, released)
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
				
				$title = $listEpisode['Episodes'][$j]['Title'];
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
		
		$stmt = $bdd->prepare("INSERT INTO tvShow (imdbID, showName, poster, nbrSeason ) VALUES (:imdbID2, :showName, :poster, :nbrSeason );");
		
		$stmt->bindParam(':imdbID2', $imdbID2);
		$stmt->bindParam(':showName', $showName);
		$stmt->bindParam(':poster', $poster);
		$stmt->bindParam(':nbrSeason', $nbrSeason);
		
		$infoShow = getFromAPI('i='.$showId);
		
		$imdbID2 = $showId;
		$showName = $infoShow['Title'];
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
	
	try{
	    $bdd = new PDO('mysql:host=localhost;dbname=showTracteur', 'root', '');
    	$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}catch (Exception $e){
	    die('Error : ' . $e->getMessage());
	}
	
	
	$statement = $bdd->prepare("SELECT * FROM tvShow");
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
	try{
	    $bdd = new PDO('mysql:host=localhost;dbname=showTracteur', 'root', '');
    	$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}catch (Exception $e){
	    die('Error : ' . $e->getMessage());
	}
	
	
	$statement = $bdd->prepare("SELECT * FROM tvShow WHERE imdbID = :imdbID");
	$statement->execute(array(':imdbID' => $imdbID));
	
	$result = $statement->fetch();
	
	$statement2 = $bdd->prepare("SELECT imdbID, title, season, episode, statusForMe, released FROM episode WHERE show_imdbID = :show_imdbID ORDER BY season DESC, episode DESC");
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
	try{
	    $bdd = new PDO('mysql:host=localhost;dbname=showTracteur', 'root', '');
    	$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}catch (Exception $e){
	    die('Error : ' . $e->getMessage());
	}
	
	$statement = $bdd->prepare("DELETE FROM tvShow WHERE imdbID = :imdbID");
	$statement->execute(array(':imdbID' => $imdbID));
	$statement2 = $bdd->prepare("DELETE FROM episode WHERE show_imdbID = :show_imdbID");
	$statement2->execute(array(':show_imdbID' => $imdbID));

}

function loadListOfShow(){
	try{
	    $bdd = new PDO('mysql:host=localhost;dbname=showTracteur', 'root', '');
    	$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}catch (Exception $e){
	    die('Error : ' . $e->getMessage());
	}
	
	$statement = $bdd->prepare("Select imdbID, showName FROM tvShow ORDER BY showName");
	$statement->execute();
	
	while($result = $statement->fetch()){
    	$string .= '<option value="'.$result['imdbID'].'">'.$result['showName'].'</option>';
	}
	
	return $string;
}

function updateStatus($episodeImdbID){
	try{
	    $bdd = new PDO('mysql:host=localhost;dbname=showTracteur', 'root', '');
    	$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}catch (Exception $e){
	    die('Error : ' . $e->getMessage());
	}
	
	$statement = $bdd->prepare("SELECT statusForMe, episode, season, show_imdbID FROM episode WHERE imdbID = :imdbID");
	$statement->execute(array(':imdbID' => $episodeImdbID));
	$result = $statement->fetch();
	
	if($result['statusForMe'] == 0){
		$statement2 = $bdd->prepare("UPDATE episode SET statusForMe = 2 WHERE (show_imdbID = :show_imdbID AND season < :season) OR (show_imdbID = :show_imdbID AND season = :season AND episode <= :episode)");
		$statement2->execute(array(
			':show_imdbID' => $result['show_imdbID'],
			':season' => $result['season'],
			':episode' => $result['episode']
		));
		$statement3 = $bdd->prepare("UPDATE episode SET statusForMe = 1 WHERE (show_imdbID = :show_imdbID AND season > :season) OR (show_imdbID = :show_imdbID AND season = :season AND episode > :episode)");
		$statement3->execute(array(
			':show_imdbID' => $result['show_imdbID'],
			':season' => $result['season'],
			':episode' => $result['episode']
		));	
			//set the others to 1
	}elseif($result['statusForMe'] == 1){
		$statement4 = $bdd->prepare("UPDATE episode SET statusForMe = 2 WHERE imdbID = :imdbID");
		$statement4->execute(array(':imdbID' => $episodeImdbID));
	}elseif($result['statusForMe'] == 2){
		$statement5 = $bdd->prepare("UPDATE episode SET statusForMe = 1 WHERE imdbID = :imdbID");
		$statement5->execute(array(':imdbID' => $episodeImdbID));
	}
	
}

function checkForUpdates($showId){
	try{
	    $bdd = new PDO('mysql:host=localhost;dbname=showTracteur', 'root', '');
    	$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}catch (Exception $e){
	    die('Error : ' . $e->getMessage());
	}

	
	$changeCount == 0;
	$listOfChange = array();


	//comparison of episodes
	$statement = $bdd->prepare("SELECT title, released, episode, imdbID, season FROM episode WHERE show_imdbID = :show_imdbID ORDER BY season, episode");
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
				$statement2 = $bdd->prepare("SELECT season FROM episode WHERE imdbID = :imdbID");
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
				if($result['title'] != $APITitle){
					$listOfChange[] = array('imdbID'=>$result['imdbID'], 'error'=>'title', 'sql'=>$result['title'],'API'=>$APITitle);
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
	try{
	    $bdd = new PDO('mysql:host=localhost;dbname=showTracteur', 'root', '');
    	$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}catch (Exception $e){
	    die('Error : ' . $e->getMessage());
	}
	
	$newValues = getFromAPI('i='.$imdbID);
	
	if($whatToUpdate != 'New'){
		if($whatToUpdate == 'title'){
			$toChange = $newValues['Title'];
		}elseif($whatToUpdate == 'released'){
			$toChange = date("Y-m-d", strtotime($newValues['Released']));
			
		}elseif($whatToUpdate == 'episode'){
			$toChange = $newValues['Episode'];
		}elseif($whatToUpdate == 'imdbID'){
			$toChange = $newValues['imdbID'];
		}elseif($whatToUpdate == 'season'){
			$toChange = $newValues['Season'];
		}
		
		
		$stmt = $bdd->prepare("UPDATE episode SET ".$whatToUpdate." = :".$whatToUpdate." WHERE imdbID = :imdbID");
		$stmt->bindParam(':imdbID', $imdbID);
		$stmt->bindParam(':'.$whatToUpdate, $toChange);
	}else{
		$stmt = $bdd->prepare("INSERT INTO episode (imdbID, show_imdbID, title, season, episode, released)
		VALUES (:imdbID, :show_imdbID, :title, :season, :episode, :released)");
		$stmt->bindParam(':imdbID', $newValues['imdbID']);
		$stmt->bindParam(':show_imdbID', $newValues['seriesID']);
		$stmt->bindParam(':title', $newValues['Title']);
		$stmt->bindParam(':season', $newValues['Season']);
		$stmt->bindParam(':episode', $newValues['Episode']);
		$stmt->bindParam(':released', date("Y-m-d", strtotime($newValues['Released'])));
	}
				
	
	try{
		$stmt->execute();
	}catch (Exception $e){
		die('Error : ' . $e->getMessage());
	}
}

function getToWatch(){
	try{
	    $bdd = new PDO('mysql:host=localhost;dbname=showTracteur', 'root', '');
    	$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}catch (Exception $e){
	    die('Error : ' . $e->getMessage());
	}
	
	$toWatch = array();
	
	$statement = $bdd->prepare("SELECT title, show_imdbID, episode, imdbID, season FROM episode WHERE statusForMe = 0 OR statusForMe = 1 ORDER BY show_imdbID, season, episode");
	$statement->execute();
	
	$episodeToWatch = array();
	
	$result = $statement->fetch();
	
	$showID = 0.2;
	
	for($i=0;$result['show_imdbID'];$i++){
		$showID = $result['show_imdbID'];
		
		$statement2 = $bdd->prepare("SELECT showName, poster FROM tvShow WHERE imdbID = :showImdbID");
		$statement2->execute(array(':showImdbID' => $showID));
		$result2 = $statement2->fetch();
		
		$episodeToWatch[$i] = array('showName' => $result2['showName'], 'poster' => $result2['poster']);
		
		for($j=0, $showID = $result['show_imdbID']; $result['show_imdbID'] == $showID AND $j <= 150;$j++, $result = $statement->fetch()){
			$episodeToWatch[$i][episodes][] = array('title' => $result['title'], 'showImdbID' => $result['show_imdbID'], 'episode' => $result['episode'], 'imdbID' => $result['imdbID'], 'season' => $result['season']);
		}		
	}
	
	return $episodeToWatch;
	
}



/* --- Functions inherant to one page --- */




switch ($_GET['page']) {
    case 'addShow':
    
    	//display the show from the result of the search
        if(isset($_POST['searchShow'])){
			$listOfShows = getFromAPI('s='.urlencode($_POST['showTitle']));
			if($listOfShows['Response']=='True'){
				$displayTarget = '<table class="tableInLine">';
				for($i=1, $n=$listOfShows['totalResults']; 10*($i-1) < $n; $i++){
					$listePage = getFromAPI('s='.urlencode($_POST['showTitle']).'&page='.$i);
					for($j=0;$j<10;$j++){
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
				$displayTarget .= 'No show was found with that parameter';
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
        	for($i=0; $_GET['episodeToUpdate'.$i]; $i++){
        		if($_GET['episodeToUpdate'.$i] != 'False'	){
        			updateEpisode($_GET['episodeToUpdate'.$i], $_GET['typeOfUpdate'.$i]);
        		}
        	}
        }
        
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
				for($i=0; $possiblyToUpdate[$i]; $i++){
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
				$stringOfUpdates .= '</table>
				<input type="hidden" name="page" value="param"/>
				<input type="hidden" name="update" value="'.$_GET['update'].'"/>
				<input type="hidden" name="updateEpisode" value="True"/>
				<input type="submit" value="update" onclick="uncheckedForm('.$j.')"  class="form-submit updateButton"/>
			</form>';
			}else{
				$stringOfUpdates = 'No update Found!!!';
			}
		}
		
        //check for update on all the shows
        if(isset($_GET['allUpdate'])){
        	try{
				$bdd = new PDO('mysql:host=localhost;dbname=showTracteur', 'root', '');
				$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			}catch (Exception $e){
				die('Error : ' . $e->getMessage());
			}
			
			$statement = $bdd->prepare("SELECT imdbID FROM tvShow");
			$statement->execute();
			
			$i = 0;
			while($result = $statement->fetch())
			{
				$i++;
				//check differences between the API and de db
       			$listOfChange[] = checkForUpdates($result['imdbID']);
			}
			
        	//format in an orderly fashion
        	$stringOfUpdates = '<form>
        		<table class="tableInLineSmall vcenter infoShow">
        			<th>imdbID</th>
        			<th>Error</th>
        			<th>sql</th>
        			<th>API</th>
        			<th><input type="checkbox" id="checkThem" onclick="checkThemAll('.(((sizeof($listOfChange,1)-sizeof($listOfChange))/5)-1).')"/></th>';
        	$j=0;
        	for($k=0; $k<sizeof($listOfChange); $k++){
				for($i=0; $listOfChange[$k][$i]; $i++){
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
        	$stringOfUpdates .= '</table>
        	<input type="hidden" name="page" value="param"/>
        	
        	<input type="hidden" name="updateEpisode" value="True"/>
        	<input type="submit" value="update" onclick="uncheckedForm('.$j.')"  class="form-submit updateButton"/>
        </form>';
        }//<input type="hidden" name="allUpdate" value="True"/>
        
        $displayTarget = loadListOfShow();
        break;
        
    case '':
    case 'home':
	default:
		$toWatch = getToWatch();
		
       	
        break;

}



?>