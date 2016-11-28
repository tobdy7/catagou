<?php
/*
* @project	zentralBankInclude
* @author	UserOne0One
* @version 	1.0
*/
/*
	Have to create a central account management too
	add the alwaysSolvable option implementations
	
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

//include '../package/utils.php';

//check solde
function solde($idCompte){
	
	$db = loadPDO('zb');
	
	$stmt = $db->prepare("SELECT solde FROM comptes WHERE id = :id;");
	$stmt->bindParam(':id', $idCompte);
	executeStmt($stmt);
	
	$result = $stmt->fetch();
	return $result['solde'];
}

function pay($theOneWhoPays, $theOneWhoGetPayed, $label, $montant){
	
	$db = loadPDO('zb');
	
	if($montant<0){
		return earn($theOneWhoPays, $theOneWhoGetPayed, $label, (-$montant));
	}
	//rajouter la condition que le montant est positif
	
	$stmt0 = $db->prepare("SELECT solde, tjrSolvable FROM comptes WHERE id = :id");
	$stmt0->bindParam(':id', $theOneWhoPays);
	
	executeStmt($stmt0);
	
	$result0 = $stmt0->fetch();
	
	if($result0['solde'] < $montant AND $result0['tjrSolvable']){
		console("Error: Solde insufisant");
		return false;
	}
	
	
	//insertion de la transaction dans la bdd
	$stmt = $db->prepare("INSERT INTO transaction (`fromCompte_id`, `toCompte_id`, `label`, `montant`) VALUES (:fromCompte_id, :toCompte_id, :label, :montant)");
	$stmt->bindParam(':fromCompte_id', $theOneWhoPays);
	$stmt->bindParam(':toCompte_id', $theOneWhoGetPayed);
	$stmt->bindParam(':label', $label);
	$stmt->bindParam(':montant', $montant);
	
	executeStmt($stmt);
	
	$lastid = $db->lastInsertId();
	
	//soustrait le montant de celuiQuiPaye
	$newSoldPayer = solde($theOneWhoPays)-$montant;
	$stmt2 = $db->prepare("UPDATE comptes SET solde = :solde, lastUpdatedId = :lastId WHERE id = :id");
	$stmt2->bindParam(':solde', $newSoldPayer);
	$stmt2->bindParam(':lastId', $lastid);
	$stmt2->bindParam(':id', $theOneWhoPays);
	
	executeStmt($stmt2);
	
	
	//ajoute le montant de celuiQuiEstPayé
	$newSoldEarner = solde($theOneWhoGetPayed)+$montant;
	$stmt3 = $db->prepare("UPDATE comptes SET solde = :solde, lastUpdatedId = :lastId WHERE id = :id");
	$stmt3->bindParam(':solde', $newSoldEarner);
	$stmt3->bindParam(':lastId', $lastid);
	$stmt3->bindParam(':id', $theOneWhoGetPayed);
	
	executeStmt($stmt3);
	
	return true;
}

function earn($theOneWhoGetPayed, $theOneWhoPays, $label, $montant){
	return pay($theOneWhoPays, $theOneWhoGetPayed, $label, $montant);
}



//check if the account is up to date and update it if not
function update($compteId){
	return true;
	
	$db = loadPDO('zb');
	
	$stmt = $db->prepare("SELECT solde, lastUpdatedId FROM comptes WHERE id = :id;");
	$stmt->bindParam(':id', $compteId);
	$stmt->execute();
	
	executeStmt($stmt);
	
	$result = $stmt->fetch();
	$solde = $result['solde'];
	
	//reçu
	$stmt2 = $db->prepare("SELECT montant, id FROM transaction WHERE toCompte_id = :id AND id > :lastid;");
	$stmt2->bindParam(':id', $compteId);
	$stmt2->bindParam(':lastid', $result['lastUpdatedId']);
	
	executeStmt($stmt2);
	
	$idRecu = 0;
	
	while($result2 = $stmt2->fetch()){
		$solde += $result2['montant'];
		$idRecu = max($idRecu, $result2['id']);
	}
	
	//donné
	$stmt3 = $db->prepare("SELECT montant, id FROM transaction WHERE fromCompte_id = :id AND id > :lastid;");
	$stmt3->bindParam(':id', $compteId);
	$stmt3->bindParam(':lastid', $result['lastUpdatedId']);
	
	executeStmt($stmt3);
	
	$idDonne = 0;
	
	while($result3 = $stmt3->fetch()){
		$solde -= $result3['montant'];
		$idDonne = max($idDonne, $result3['id']);
	}
	
	//mise à jour du solde et de lastUpdate
	$stmt4 = $db->prepare("UPDATE comptes SET solde = :solde, lastUpdatedId = :lastId WHERE id = :id");
	$stmt4->bindParam(':solde', $solde);
	$stmt4->bindParam(':lastId', max($idRecu, $idDonne));
	$stmt4->bindParam(':id', $compteId);
	$stmt4->execute();
	
	executeStmt($stmt4);
	
	return true;
}

function createAccount($usrId){
	$db = loadPDO('zb');
	
	
	$stmt = $db->prepare("INSERT INTO comptes (id_usr, solde) VALUES (:id_usr, 0);");
	$stmt->bindParam(':id_usr', $usrId);
	
	executeStmt($stmt);
	
	return $db->lastInsertId();
}

?>