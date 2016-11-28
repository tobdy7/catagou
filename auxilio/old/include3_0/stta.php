<?php
	function displayTaches($semaine){//après automatiser pas que ça depende du param mais de TODAY()
		$db = loadDB(__FUNCTION__);
		
		$stmt = $db->prepare('SELECT id, value, branche, delai, status FROM stta WHERE (semaine < :semaine AND status = 0) OR (semaine = :semaine) ORDER BY branche, delai');
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
			
			if($result['branche'] == $i){
				$sttaString .= '<div class="divTache divTacheStyle'.$result['status'].'">'.$result['value'].'<input type="checkbox" onclick="updateStatus('.$result['id'].')"/></div>
				';
			}elseif($i!=5){
				$i++;
				$sttaString .= '</td><td>
				<div class="divTache divTacheStyle'.$result['status'].'">'.$result['value'].'</div>
				';
			}
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
	//entre dans la base de donnée toutes les tâches pour une semaine donnée
	function creeTacheSemaine($semaine){
		//Tâche hebdomadaire par brache
		//pour le delai(3ème entrée)le format et le suivant: j hh:mm:00 ou j est le jour de la semaine(1=lundi, 2=mardi, ...)
		//branche 1 PHYSIQUE
		$arrayOfTaches = array(
			//branche 1 PHYSIQUE
			array('Série d exercices n°'.($semaine-1), 1, '3 19:00:00' , $semaine),
			array('Mise en forme de la théorie', 1, '5 19:00:00' , $semaine),
			//branche 2 ANALYSE
			array('Série d exercices n°'.($semaine), 2, '3 17:00:00' , $semaine),
			array('Mise en forme de la théorie', 2, '5 19:00:00' , $semaine),
			//branche 3 ICC
			array('Série d exercices n°'.($semaine), 3, '5 18:00:00' , $semaine),
			array('Mise en forme de la théorie', 3, '1 19:00:00' , $semaine),
			//branche 4 ALGEBRE LINEAIRE
			array('Série d exercices n°'.($semaine), 4, '4 17:00:00' , $semaine),
			array('Mise en forme de la théorie', 4, '5 19:00:00' , $semaine),
			//branche 5 INTRO PROG
			array('Série d exercices n°'.($semaine), 5, '5 16:00:00' , $semaine),
			array('Vidéo du MOOC', 5, '4 09:00:00' , $semaine)
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
?>
<script type="text/javascript">
		function updateStatus(id){
			//...
		}
</script>

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
		echo displayTaches(2);
	?>
	<tr>
		<td id="sttaBranche1">
			Physique
		</td>
		<td id="sttaBranche2">
			Analyse
		</td>
		<td id="sttaBranche3">
			ICC
		</td>
		<td id="sttaBranche4">
			Alg Lin
		</td>
		<td id="sttaBranche54">
			<div id="sttaBranche5">
				Intro Prog
			</div>
		</td>
	</tr>
</table>