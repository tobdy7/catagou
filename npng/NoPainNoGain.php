<?php
/*
* @project	NoPainNoGain
* @author	UserOne0One
* @version 	1.0
*/
/*
	rester en js sur le site et implémenter la désychro
	offrir la possibilité de changer de comptes pour un utilisateur
	faire en sorte que l'update PUISSE se faire directement on click
	gérer les erreurs
	
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

//implémentation de la connexion

$_SESSION['id'] = 8;
$_SESSION['compteId'] = 3;
$_SESSION['pseudo'] = "loutch";

include '../package/utils.php';
include '../package/zb.php';

function listAllGet(){

	/*
	Types de répétitions:
	0	->	n'apparait qu'une seul fois
	1	->	récurence journalière (tu peux faire cette action une fois entre 00:00 et 23:59
	2	->	récurence journalière (tu peux faire cette action qu'une fois toute les 24h
	3	->	récurence hébdomadaire (tu peux faire cette action une fois par semaine (lu 00:00 au di 23:59))
	4	->	récurence hébdomadaire (tu peux faire cette action une fois tout les 7j)
	...
	augmenter la complexité
	*/
	/*
	$allGet = array();
	$db = loadPDO('npng');
	
	$stmt = $db->prepare("SELECT id, labe, montant, repetition, doneTime FROM transactions WHERE id_usr = :id_usr AND type = 0;");
	$stmt->bindParam(':id_usr', $_SESSION['id']);
	executeStmt($stmt);
	
	while($result = $stmt->fetch()){
		$time = strtotime($result['doneTime']);
		switch($result['repetition']) {
			case 0:
				if($time==0){
					$allGet[] = ['id' => $result['id'], 'label' => $result['label'], 'montant' => $result['montant']];
				}
				break;
			case 1:
				if($time<strtotime(date('Y-m-d 00:00:00'))){
					$allGet[] = ['id' => $result['id'], 'label' => $result['label'], 'montant' => $result['montant']];
				}
				break;
			case 2:
				if(($time-strtotime(date("Y-m-d H:i:s"))))> strtotime('0000-00-01 00:00:00')){
					$allGet[] = ['id' => $result['id'], 'label' => $result['label'], 'montant' => $result['montant']];
				}
				break;
			case 3:
				if($time<(strtotime(date('Y-m-d 00:00:00'))-strtotime(date('0000-00-0N 00:00:00')))){
					$allGet[] = ['id' => $result['id'], 'label' => $result['label'], 'montant' => $result['montant']];
				}
				break;
			case 4:
				if(($time-strtotime(date("Y-m-d H:i:s")))>strtotime('0000-00-07 00:00:00')){
					$allGet[] = ['id' => $result['id'], 'label' => $result['label'], 'montant' => $result['montant']];
				}
				break;
			
		}
	}
	
	return $allGet:
}

function listAllGive(){
	$allGive = array();
	$db = loadPDO('npng');
	
	$stmt = $db->prepare("SELECT id, labe, montant, repetition, doneTime FROM transactions WHERE id_usr = :id_usr AND type = 1;");
	$stmt->bindParam(':id_usr', $_SESSION['id']);
	executeStmt($stmt);
	
	while($result = $stmt->fetch()){
		$time = strtotime($result['doneTime']);
		switch($result['repetition']) {
			case 0:
				if($time==0){
					$allGive[] = ['id' => $result['id'], 'label' => $result['label'], 'montant' => $result['montant']];
				}
				break;
			case 1:
				if($time<date('Y-m-d 00:00:00')){
					$allGive[] = ['id' => $result['id'], 'label' => $result['label'], 'montant' => $result['montant']];
				}
				break;
			case 2:
				if($time-date("Y-m-d H:i:s"))>'0000-00-01 00:00:00'){
					$allGive[] = ['id' => $result['id'], 'label' => $result['label'], 'montant' => $result['montant']];
				}
				break;
			case 3:
				if($time<(date('Y-m-d 00:00:00')-date('0000-00-0N 00:00:00'))){
					$allGive[] = ['id' => $result['id'], 'label' => $result['label'], 'montant' => $result['montant']];
				}
				break;
			case 4:
				if($time-date("Y-m-d H:i:s"))>'0000-00-07 00:00:00'){
					$allGive[] = ['id' => $result['id'], 'label' => $result['label'], 'montant' => $result['montant']];
				}
				break;
			
		}
	}
	
	return $allGive;
	*/
}

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
	case 'page1':
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

if(isset($_POST['Update']) AND $_POST['Update']){
	update(0);
	//when you earn §
	if($_POST['nbrOfEarn'] > 0){
		for($i=1;$i<=$_POST['nbrOfEarn'];$i++){
			if(isset($_POST['earnLabel'.$i]) AND isset($_POST['earnMontant'.$i])){
				if(!earn($_SESSION['compteId'], 0, $_POST['earnLabel'.$i], $_POST['earnMontant'.$i])){
					console("problem");
				}
				console('earn: '.$_POST['earnLabel'.$i].' '.$_POST['earnMontant'.$i]);
			}
		}
	}
	
	//when you pay §
	if($_POST['nbrOfPay'] > 0){
		for($i=1;$i<=$_POST['nbrOfPay'];$i++){
			if(isset($_POST['payLabel'.$i]) AND isset($_POST['payMontant'.$i])){
				pay($_SESSION['compteId'], 0, $_POST['payLabel'.$i], $_POST['payMontant'.$i]);
				console('pay: '.$_POST['payLabel'.$i].' '.$_POST['payMontant'.$i]);
			}
		}
	}
}


?>

<!DOCTYPE html>
<html>
  <head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="shortcut icon" href="logo.ico" type="image/x-icon" />
	<title>NoPainNoGain</title>
	<meta name="description" content="No Pain, No Gain!" />
	<meta name="keywords" content="no, pain, gain, NoPainNoGain, php" />
	<link type="text/css" rel="stylesheet" href="NoPainNoGain.css" />
	<script type="text/javascript">
		
		/*	########## PAGES MANAGEMENT ##########	*/
		
		var pageActuel = "home";
		
		function page(destination){
			document.getElementById("getPage").className="hidden";
			document.getElementById("givePage").className="hidden";
			document.getElementById("homePage").className="hidden";
			
			document.getElementById(destination+"Page").className="bigDiv";
			
			pageActuel = destination;
			//location.href='NoPainNoGain.php?page='+destination;
		}
		
		//redirect to a page that is one above in the architecture
		function goBack(){
			switch(pageActuel) {
				case 'get':
				case 'give':
					page('home');
					break;
				default:
					break;
				
			}
		}
		
		/*	########## UPDATING ##########	*/
		
		//submit the update form
		function update(){
			var form = document.getElementById('update')
			form.action = "NoPainNoGain.php?page="+pageActuel;
			form.submit();
		}
		
		
		/*	########## DESYNCHRONISATION DES TRANSACTIONS ##########	*/
		
		function earn(label, montant){
			var nbr = document.getElementById('nbrOfEarn').value;
			nbr++;
			document.getElementById('update').innerHTML += '<input type="hidden" name="earnLabel'+nbr+'" value="'+label+'"><input type="hidden" name="earnMontant'+nbr+'" value="'+montant+'">';
			document.getElementById('nbrOfEarn').value = nbr;
			//console.log('earn :'+label+' '+montant);
		}
		
		function pay(label, montant){
			var nbr = document.getElementById('nbrOfPay').value;
			nbr++;
			document.getElementById('update').innerHTML += '<input type="hidden" name="payLabel'+nbr+'" value="'+label+'"><input type="hidden" name="payMontant'+nbr+'" value="'+montant+'">';
			document.getElementById('nbrOfPay').value = nbr;
			//console.log('pay :'+label+' '+montant);
		}
		
		/*	########## AJOUT DES POSSIBILITE DANS L'INTERFACE ##########	*/
		
		function addGetMethode(title, montant){
			document.getElementById("get").innerHTML += '<div class="smallDiv smallhlist"><div class="radius_all border  tcenter"><h3>'+title+'</h3>gagne: '+montant+'§<br><button onclick="earn(\''+title+'\','+montant+')">FINI</button></div></div>';
		}
		
		function addGiveMethode(title, montant){
			document.getElementById("give").innerHTML += '<div class="smallDiv smallhlist"><div class="radius_all border  tcenter"><h3>'+title+'</h3>gagne: '+montant+'§<br><button onclick="pay(\''+title+'\','+montant+')">FINI</button></div></div>';
		}
		
		function addGetMethodeTimed(title, rewardMin){
			document.getElementById("get").innerHTML += '<div class="smallDiv smallhlist"><div class="radius_all border  tcenter"><h3>'+title+'</h3>gagne: '+rewardMin+'§/min<br><div><button onclick="startTime(this, \''+title+'\', '+rewardMin+')">start</button></div></div></div>';
		}
		
		
		/*	########## TIMED ENTRYSE MANAGEMENT ##########	*/
		
		function startTime(element, title, rewardMin){
			var d = new Date();
			var n = d.getTime();
			var dady = element.parentElement;
			dady.innerHTML = '<button onclick="stopTime('+n+', this, \''+title+'\', '+rewardMin+')">stop</button>';
			//showTime(element, '+n+')
			//<div onload="showTime(this, '+n+')">45</div>
			var div = document.createElement("DIV");        // Create a <button> element
			var t = document.createTextNode("ha");       // Create a text node
			div.appendChild(t);                                // Append the text to <button>
			dady.appendChild(div);
			showTime(div, n, rewardMin)
		}
		
		function stopTime(startingTime, element, title, rewardMin){
			var d = new Date();
			var n = d.getTime();
			var timeMs = n - startingTime;
			var timeS = (timeMs-(timeMs%1000))/1000;
			var timeMin = (timeS-(timeS%60))/60;
			
			if(timeMin>0){
				earn(title+" "+timeMin+"min", rewardMin*timeMin);
			}
			
			element.parentElement.parentElement.innerHTML = '<h3>'+title+'</h3>gagne: '+rewardMin+'§/min<br><div><button onclick="startTime(this, \''+title+'\', '+rewardMin+')">start</button></div>';
		}
		
		function showTime(element, startingTime, rewardMin) {
			console.log(rewardMin);
			var d = new Date();
			var n = d.getTime();
			var timeMs = n - startingTime;
			
			var timeS = (timeMs-(timeMs%1000))/1000;
			var s = timeS%60;
			
			var timeMin = (timeS-s)/60;
			var m = timeMin%60;
			
			var timeH = (timeMin-m)/60;
			var h = timeH;
			
			
			if(m>0){
				if(h>0){
					theTime = h + ":" + checkTime(m) + ":" + checkTime(s);
				}else{
					theTime = m + ":" + checkTime(s);
				}
			}else{
				theTime = s;
			}
			
			element.innerHTML = theTime+" "+(rewardMin*timeMin)+"§";
			
			var timer = setTimeout(function() {showTime(element, startingTime, rewardMin)}, 500);
		}
		
		function checkTime(i) {
			if (i < 10) {i = "0" + i};  // add zero in front of numbers < 10
			return i;
		}
		
		
		/*	########## CONCRETISATION DES ELEMENTS ##########	*/
		
		
		function add(){
			<?php
			//les choses que tu peux faires qui te coutent des §
			
			//constant
			echo 'addGiveMethode("un épisode", 500);';
			echo 'addGiveMethode("une glace", 200);';
			
			
			//les choses que tu peux faire pour gagner des §
			
			//constant
			echo 'addGetMethode("20 pompes", 40);';
			echo 'addGetMethode("20 abdos", 20);';
			echo 'addGetMethode("courire 1h", 100);';
			echo 'addGetMethode("1h boulot", 100);';
			echo 'addGetMethode("préparer sac demain", 20);';
			echo 'addGetMethode("check mails", 20);';
			//echo 'addGetMethode("1h prog", 50);';
			
			echo 'addGetMethodeTimed("boulot", 2);';
			echo 'addGetMethodeTimed("prog", 1);';
			echo 'addGetMethodeTimed("rangement", 1.5);';
			
			//one time deal
			echo 'addGetMethode("implémenter usr", 200);';
			echo 'addGetMethode("finir le css", 30);';
			echo 'addGetMethode("passer à auxilio 4.2", 50);';
			echo 'addGetMethode("gestion des erreurs généralisées", 20);';
			echo 'addGetMethode("coucher levé", 20);';
			echo 'addGetMethode("implémenter un lien entre todo et npng", 20);';
			?>
		}
		
		
		/*	########## HORLOGE ##########	*/
		
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
		
		
		
	</script>
	<style>
		
	</style>
  </head>
  <body onload="page('home'); add(); showClock();">
	<div>
		<form id="update" method="post">
			<input type="hidden" name="Update" value="true">
			<input type="hidden" id="nbrOfEarn" name="nbrOfEarn" value="0">
			<input type="hidden" id="nbrOfPay" name="nbrOfPay" value="0">
		</form>
		
		
	</div>
	
	<div id="container">
		<header>
			<div onclick="goBack()" class="selectable">BACK</div>
			<div onclick="update()" class="selectable">UPDATE</div>
			<div><?php echo solde($_SESSION['compteId']); ?></div>
			<div id="clockToSleep"></div>
		</header>
		<div class="article">
			<div id="getPage" class="bigDiv">
				<div id="get" class="border radius_all">
					<span class="hcenter mainText">Possibility to earn:</span>
				</div>
			</div>
			<?php
			if(isset($displayTarget)){
				echo $displayTarget;
			}
			?>
			
			<div id="givePage" class="bigDiv">
				<div id="give" class="border radius_all">
					<span class="hcenter mainText">What you can get:</span>
				</div>
				
			</div>
			
				<?php
			if(isset($displayTarget)){
				echo $displayTarget;
			}
				?>
			
			
			<div id="homePage" class="bigDiv">
				<div class="half_left radius_left selectable" onclick="page('get')">
					<span class="vcenter hcenter mainText">earn</span>
				</div>
				<div class="half_right radius_right selectable" onclick="page('give')">
					<span class="vcenter hcenter mainText">pay</span>
				</div>
			</div>
		</div>
	</div>
	<footer>
		<p>Copyright &copy; 2016 userOneOOne</p>
	</footer>
  </body>
</html>
