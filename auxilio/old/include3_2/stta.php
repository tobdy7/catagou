<?php
	/*
	CONCEPTE de dév
		stta(id, value, branche, delai, semaine, status)
			//branche 1 PHYSIQUE
			//branche 2 ANALYSE
			//branche 3 ICC
			//branche 4 ALGEBRE LINEAIRE
			//branche 5 INTRO PROG
			
			//delai = quand ça doit être fait
			
			//status 0: pas encore fait, pas en retard
			//status 1: fait et devait être fait cette semaine(donc affiché)
			//status 2: pas encore fait mais devrait l'être (en retard)
			//status 3: fait et devait être fait les semaines passées (archives)
			
		update du status dans la fct de display
		
		ajouter une methode d'entrée (je pense dans param)
	
	
	*/
	function displayTaches(){//après automatiser pas que ça depende du param mais de TODAY()
		$semaine1 = new DateTime('2016-09-19 00:00:01');
		$now = new DateTime('NOW');
		$interval = $semaine1->diff($now)->format('%D');
		$semaine = (($interval-($interval%7))/7)+1;
		
		updateStatusStta($semaine);
		
		$db = loadDB(__FUNCTION__);
		
		$stmt = $db->prepare('SELECT * FROM stta WHERE (semaine <= :semaine AND status < 3)ORDER BY branche, delai');
		$stmt->bindParam(':semaine', $semaine);
		
		try{
			$stmt->execute();
		}catch (Exception $e){
			alertError($e->getMessage(), __FUNCTION__);
		}
		
		$sttaString = '<tr>
			<td>';
		$i = 1;
		
		//divTacheStyle0 = pas encore fait (vert?)
		//1=fait et devait être fait cette semaine (gris pale)
		//2=pas fait et passé le délai (rouge)
		while($result = $stmt->fetch()){
			//update le status
			
			if($result['branche'] != $i && $i <5){
				$i++;
				$sttaString .= '</td><td>
				';
			}	
			$sttaString .= '<div class="divTache divTacheStyle'.$result['status'].'" id="stta'.$result['id'].'">'.$result['value'].'<input type="checkbox" class="checkboxStta" onclick="updateStatus('.$result['id'].')"/></div>
			';
			
			
    		}
		$sttaString .= '</td>
		</tr>';
		
		unset($stmt);
		
   		return $sttaString;
	}
	
	function addTache($value, $branche, $delai , $semaine){
		$db = loadDB(__FUNCTION__);
		
		$stmt = $db->prepare("INSERT INTO stta (value, branche, delai, semaine) VALUES (:value, :branche, :delai, :semaine);");
		$stmt->bindParam(':value', $value);
		$stmt->bindParam(':branche', $branche);
		$stmt->bindParam(':delai', $delai);
		$stmt->bindParam(':semaine', $semaine);
		
		try{
			$stmt->execute();
		}catch (Exception $e){
			alertError($e->getMessage(), __FUNCTION__);
			die();
		}
		unset($stmt);
		unset($db);
		return True;
	}
	//creeTacheSemaine(2);
	//creeTacheSemaine(14);
	/*for($i=1;$i<=13;$i++){
		creeTacheSemaine($i);
	}*/
	//entre dans la base de donnée toutes les tâches pour une semaine donnée
	function creeTacheSemaine($semaine){
		//Tâche hebdomadaire par brache
		//pour le delai(3ème entrée)le format et le suivant: j hh:mm:00 ou j est le jour de la semaine(1=lundi, 2=mardi, ...)
		//branche 1 PHYSIQUE
		$arrayOfTaches = array(
			//branche 1 PHYSIQUE
			array('Série d exercices n°'.($semaine-1), 1, '3 19:00:00' , $semaine),
			array('Mise en forme de la théorie semaine '.($semaine), 1, '5 19:00:00' , $semaine),
			//branche 2 ANALYSE
			array('Série d exercices n°'.($semaine), 2, '3 17:00:00' , $semaine),
			array('Mise en forme de la théorie semaine '.($semaine), 2, '5 19:00:00' , $semaine),
			//branche 3 ICC
			array('Série d exercices n°'.($semaine), 3, '5 18:00:00' , $semaine),
			array('Mise en forme de la théorie semaine '.($semaine), 3, '1 19:00:00' , $semaine),
			//branche 4 ALGEBRE LINEAIRE
			array('Série d exercices n°'.($semaine), 4, '4 17:00:00' , $semaine),
			array('Mise en forme de la théorie semaine '.($semaine), 4, '5 19:00:00' , $semaine),
			//branche 5 INTRO PROG
			array('Série d exercices n°'.($semaine), 5, '5 16:00:00' , $semaine),
			array('Vidéo du MOOC semaine '.($semaine), 5, '4 09:00:00' , $semaine)
			//rajouter mini projet
		);
		
		$semaine1 = '2016-09-19 00:00:01';
		for($i=0; $i<count($arrayOfTaches); $i++){
			//crée une date à partire du jour et de l'heure entrée pour le délai de la tache comme si elle était lors de la première semaine -1j (le 11 est un dimanche et non un lundi)
			$intermediaryDate = new DateTime('2016-09-1'.$arrayOfTaches[$i][2]);
			//rajoute le nbr de jour en fct de la semaine
			$intermediaryDate->add(new DateInterval('P'.(($arrayOfTaches[$i][3]*7)+1).'D'));
			//entrée dans la base de donéée
			addTache(htmlspecialchars($arrayOfTaches[$i][0]), $arrayOfTaches[$i][1], $intermediaryDate->format('Y-m-d H:i:s'), $arrayOfTaches[$i][3]);
		}
		/*
		print '<pre>';
		print_r($arrayOfTaches);
		print '</pre>';
		*/
	}
	
	function updateStatusStta($semaine){
		$db = loadDB(__FUNCTION__);
		
		$stmt = $db->prepare('UPDATE stta SET status = 2 WHERE (status=0)  AND (semaine < :semaineActuel)');
		$stmt->bindParam(':semaineActuel', $semaine);
		
		$stmt2 = $db->prepare('UPDATE stta SET status = 3 WHERE (status=1)  AND (semaine < :semaineActuel)');
		$stmt2->bindParam(':semaineActuel', $semaine);
		
		try{
			$stmt->execute();
			$stmt2->execute();
		}catch (Exception $e){
			alertError($e->getMessage(), __FUNCTION__);
			die();
		}
		unset($stmt);
		unset($stmt2);
		unset($db);
		return True;
		
	}
	
	function updateStatus($id){
		$db = loadDB(__FUNCTION__);
		
		$stmt = $db->prepare('UPDATE stta SET status = 1 WHERE id = :id');
		$stmt->bindParam(':id', $id);
		
		try{
			$stmt->execute();
		}catch (Exception $e){
			alertError($e->getMessage(), __FUNCTION__);
			die();
		}
		unset($stmt);
		unset($db);
		return True;
	}
	
	if(isset($_POST['synchro'])){
		if(isset($_POST['updateStta'])){
			$i = 0;
			while(isset($_POST['idUpdateStta'.$i])){
				updateStatus($_POST['idUpdateStta'.$i]);
				$i++;
			}
		}
	}
?>
<script type="text/javascript">
		function updateStatus(id){
			waitForSynch();
			var nbrUpdate = Number(document.getElementById('nbrUpdateStta').value);
			
			var j = nbrUpdate;
			if(j == 0){
				prepForDesyncho('updateStta', 'True');
			}
			prepForDesyncho('idUpdateStta'+j, id);
			
			j++;
			
			document.getElementById('nbrUpdateStta').value = j;
			
			//implementaton de l'action en js
			
			document.getElementById('stta'+id).className = "divTache divTacheStyle1";
		}
</script>
<input type="hidden" id="nbrUpdateStta" value="0">
<table class="tableStTa">
	<tr>
		<th>
			Physique
		</th>
		<th>
			Analyse
		</th>
		<th>
			ICC
		</th>
		<th>
			Alg Lin
		</th>
		<th>
			Intro Prog
		</th>
	</tr>
	<?php
		$tacheDisplay = displayTaches();
		echo $tacheDisplay;
	?>
	
</table>