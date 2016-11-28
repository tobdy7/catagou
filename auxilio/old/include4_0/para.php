<?php
	if(isset($_POST['newSttaPara'])){
		addTache($_SESSION['id'], $_POST['valueSttaPara'], $_POST['brancheSttaPara'], $_POST['delaiSttaPara'] , $_POST['semaineSttaPara']);
		$tacheDisplay = displayTaches();
	}
?>
<div class="articleSection">
	les param c'esdt ma vie<br><br>
	enregistrer (temporairement) un link<br>
	loader les cookies sur un serveur mysql et les télécharger<br>
	évt les paramêtre de show (maj des données etc)<br>
	<div class="articleForm">
		<!--
		<input type="radio" name="paramLoadRadio" id="paramLoadRadiophp" value="php" checked="checked">
		<label>Load php page when possible (default)</label>
		<input type="radio" name="paramLoadRadio" id="paramLoadRadiohtml" value="html">
		<label>Allways load html page</label><br>
		<button onclick="saveParam()">Save</button>
		-->
		<button onclick="window.open('http://localhost/auxilio/auxilio.php')">auxilio2.0</button> 
	</div>
</div>
<div class="articleSection">
	<h1>formulaire d'ajout d'entrée pour StudentTask</h1>
	<table>
		<form method="POST">
			<input type="hidden" name="page" value="para"/>
			<input type="hidden" name="newSttaPara" value="True"/>
			<tr>
				<td>
					Nom de la tâche:
				</td>
				<td>
					<input type="text" name="valueSttaPara"/>
				</td>
			</tr>
			<tr>
				<td>
					Branche (1 PHYS, 2 AN, 3 ICC, 4 A-L, 5 PROG):
				</td>
				<td>
					<input type="number" name="brancheSttaPara" min="1" max="5" value="1"/>
				</td>
			</tr>
			<tr>
				<td>
					Délai de la tâche (aaaa-mm-jj (hh:mm:ss)):
				</td>
				<td>
					<input type="date" name="delaiSttaPara"/>
				</td>
			</tr>
			<tr>
				<td>
					Semaine:
				</td>
				<td>
					<input type="number" name="semaineSttaPara" min="0" max="16" value="1"/>
				</td>
			</tr>
			<tr>
				<td>
				</td>
				<td>
					<input type="submit" value="ajouter Tâche"/>
				</td>
			</tr>
		</form>
	</table
</div>