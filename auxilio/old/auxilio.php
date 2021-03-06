<?php
//connection to bdd with PDO

?>
<!-- over this line everything is strictly php-->
<!DOCTYPE html>
<html>
  <head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="shortcut icon" href="images/icone.ico" type="image/x-icon" />
	<title>Auxilio 2.0 #Beta</title>
	<meta name="description" content="To serve and help" />
	<meta name="keywords" content="auxilio,accuile,starting page"/>
    <link type="text/css" rel="stylesheet" href="default.css" />
    <script type="text/javascript">
    	/* ########## affichage de la bonne page ########## */
    	function setArticle(article ){
			var x = document.getElementById("article");
			var y = x.getElementsByTagName("article");
			var i;
			for (i = 0; i < y.length; i++) {
			    y[i].className = "hidden";
			}
    		document.getElementById(article).className = "show";
    		//just un peu de styling
			var b = document.getElementsByName("navLink");
			var j;
			for (j = 0; j < b.length; j++) {
			    b[j].style.backgroundColor = '#0074D9';
    			b[j].style.color = '#222';
			}
			//c est la divLink de l'article séléctionné
    		var c = document.getElementById('nav'+ article.charAt(0).toUpperCase() + article.slice(1))
    		c.style.backgroundColor = '#0064C9';
    		c.style.color = '#000';
    		//en fct de la page détérminer la classe hidden
    	}
    	
    	/* ########## fonction d'affichage d'éléments ########## */
    	function showLinkHome(){
    		//link of home[link,image,title,style]
	    	var linkOfHome = [
    			["https://google.com","images/google.jpg","google",0],
    			["http://www.couchtuner.ch","images/couch.jpg","couchtuner",0],
    			["http://www.airdates.tv/#today","images/airedates.png","airedates",0],
    			["https://youtube.com","images/youtube.jpg","youtube",0],
    			[" https://www.reddit.com/r/robintracking/comments/4czzo2/robin_chatter_leader_board_official/','_blank');window.open('https://monstrouspeace.com/robintracker/','_blank');window.open('https://www.reddit.com/robin","images/robin.png","robin",1],
    			["https://www.evernote.com/Home.action","images/evernote.png","evernote",0],
    			["http://localhost/phpmyadmin/","images/phpmyadmin.png","phpmyadmin",0]
    		];
    		var articleString = "";
    		for (var i = 0; typeof linkOfHome[i] !== 'undefined'; i++) {
			    articleString += '<div class="divSite divSite-style' + linkOfHome[i][3] + '" title="' + linkOfHome[i][2] + '"><img src="' + linkOfHome[i][1] + '"  height="100" width="180" alt="' + linkOfHome[i][2] + '"><div class="divOverlay" onclick="window.open('+"'"+ linkOfHome[i][0] + "','_blank');"+'"></div></div>';
			} 
    		document.getElementById("home").innerHTML= articleString;
    	}
    	
    	/* ########## cookies ########## */
    	function setCookie(cname, cvalue, exdays) {
    		var d = new Date();
 			d.setTime(d.getTime() + (exdays*24*60*60*1000));
 			var expires = "expires="+d.toUTCString();
		    document.cookie = cname + "=" + cvalue + "; " + expires;
		}

		function getCookie(cname) {
  		 	var name = cname + "=";
 		 	var ca = document.cookie.split(';');
    		for(var i=0; i<ca.length; i++) {
    		    var c = ca[i];
    		    while (c.charAt(0)==' ') c = c.substring(1);
    		    if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
    		}
    		return false;
		}
		
		/* ########## note ########## */
		function saveNote(){
			var i=-1;
			do{
				i++;
			}while(getCookie('note'+i))
			var noteValue = document.getElementById('noteInput').value;
			setCookie('note'+i, noteValue, 500);
			setCookie('noteShow'+i, "true", 500);
			document.getElementById('noteInput').value = '';
			showNote();
		}
		
		//pour quand don press enter dans un form
		function inputKeyUp(e, action) {
    		e.which = e.which || e.keyCode;
    		if(e.which == 13 && action == 'saveNote') {
     		   saveNote();
    		}else if(e.which == 13 && action == 'saveTodo') {
     		   saveTodo();
    		}
		}
    	
    	function showNote(){
    		var i=0;
    		var noteString='';
    		while(getCookie('note'+i)){
    			if(getCookie('noteShow'+i) == "true"){
    				noteString += '<div class="noteEntity noteEntity-style0">' + getCookie('note'+i) + '<div class="supr" onclick="hideNote('+i+')">&#10006;</div></div>';
    			}
    			i++;	
    		}
    		document.getElementById('noteDisplay').innerHTML = noteString;
    	}
    	
    	function hideNote(nNote){
    		setCookie('noteShow'+nNote, "false", 500);
    		showNote();
    	}
    	
		/* ===== TODO ===== */
		
    	function saveTodo(){
			var i=-1;
			do{
				i++;
			}while(getCookie('todo'+i))
			var todoValue = document.getElementById('todoInput').value;
			var elements = document.getElementsByName('todoRadio');
   			for (var j = 0, l = elements.length; j < l; j++){
        		if (elements[j].checked){
            		var todoRadioValue = elements[j].value;
        		}
    		}
			setCookie('todo'+i, todoValue, 500);
			setCookie('todoShow'+i, "true", 500);
			setCookie('todoPrio'+i, todoRadioValue, 500);
			setCookie('nbrCookie', getCookie('nbrCookie')+1, 500);
			document.getElementById('todoInput').value = '';
			showTodo();
			console.log(i);
		}
    	
    	function showTodo(){
    		var todoString='';
    		for(var j=3; j > 0; j--){
    			for(var i=0; getCookie('nbrCookie') >= i; i++){
    				if(getCookie('todoShow'+i) == "true" && getCookie('todoPrio'+i) == j){
    					todoString += '<div class="noteEntity noteEntity-style'+getCookie('todoPrio'+i)+'">' + getCookie('todo'+i) + '<div class="supr" onclick="hideTodo('+i+')">&#10006;</div></div>';
    				}
    			}
    		}
    		document.getElementById('todoDisplay').innerHTML = todoString;
    	}
    	
    	function hideTodo(nTodo){
    		setCookie('todo'+nTodo, '&empty', 500);
			setCookie('todoShow'+nTodo, "false", 500);
    		showTodo();
    	}
    	function getFocus(input) {
    		document.getElementById(input).focus();
		}
    	
    	/* ########## show ########## */
    	
    	
    	
    	/* ########## clock ########## */
    	//source:http://www.w3schools.com/js/tryit.asp?filename=tryjs_timing_clock
    	function showClock() {
    		var today = new Date();
    		var h = 23-today.getHours();
    		var m = 59-today.getMinutes();
    		var s = 59-today.getSeconds();
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
		
		
		/* ########## connexion ########## */
		function backToHtml(){
			if(getCookie('pageToLoad') == 'html'){
				console.log('html should be reloaded');
				window.location.href = "file:///Users/lautre/Sites/auxilio/auxilio.html?pageToLoad=html";
			}
			document.getElementById('connectedStatus').className = "connectedStatus-style0";
		}

		
		
		/* ########## parametres ########## */
		
		function saveParam(){
			console.log('sal');
			var elements = document.getElementsByName('paramLoadRadio');
   			for (var j = 0, l = elements.length; j < l; j++){
        		if (elements[j].checked){
            		setCookie('pageToLoad',elements[j].value,500);
            		console.log(elements[j].value);
        		}
    		}
    		window.location.reload(true);
		}
		
		function getParam(){
			document.getElementById('paramLoadRadio'+getCookie('pageToLoad')).checked = true;
		}
		
    	//console.log('salut');
    	//alert(linkOfHome[0][0]);
    </script>
    <style>
    	
    </style>
  </head>
  <body onload="setArticle('show'); showLinkHome(); showNote(); showTodo(); showClock(); backToHtml(); getParam();">
    <header>
    		<div id="logo" class="clickable" onclick="setArticle('home')">
    			Auxilio 2.0 <span class="red">#Beta</span>
    		</div>
    		<div id="clockToSleep" class="clock">
    			06:13:12
    		</div>
    		<div id="connectedStatus" class="connectedStatus-style0">
    		</div>
    </header>
    <nav>
    	<div id="navMenu">
    		<div class="navLink" name="navLink" onclick="setArticle('home')" id="navHome">
    			Accueil
    		</div>
    		<div class="navLink" name="navLink" onclick="setArticle('note'); getFocus('noteInput');" id="navNote">
    			Notes
    		</div>
    		<div class="navLink" name="navLink" onclick="setArticle('todo'); getFocus('todoInput');" id="navTodo">
    			A faire
    		</div>
    		<div class="navLink" name="navLink" onclick="setArticle('show')" id="navShow">
    			Series
    		</div>
    		<div class="navLink" name="navLink" onclick="setArticle('para')" id="navPara">
    			Param
    		</div>
    	</div>
    
    </nav>
    
    <div class="container" id="article">
    	<!-- ========== HOME ========== -->
    	<article id="home">
    		
    	</article>
    	<!-- ========== NOTE ========== -->
    	<article id="note">
    		<div class="articleForm">
    			<input type="text" id="noteInput" class="formInput" onKeyUp="inputKeyUp(event, 'saveNote')">
    		</div>
    		<div id="noteDisplay" class="displayList">
    			youhou j ai des notes
    		</div>
    	</article>
    	<!-- ========== TODO ========== -->
    	<article id="todo">
    		<div class="articleForm">
    			<input type="text" id="todoInput" class="formInput" onKeyUp="inputKeyUp(event, 'saveTodo');">
    			<input type="radio" id="formRadio1" name="todoRadio" class="formRadio formRadio-style1" value="1" onclick="getFocus('todoInput')" checked="checked">
    			<label for="formRadio1" class="radioLabel radioLabel-style1"></label>
    			<input type="radio" id="formRadio2" name="todoRadio" class="formRadio formRadio-style2" value="2" onclick="getFocus('todoInput')">
    			<label for="formRadio2" class="radioLabel radioLabel-style2"></label>
    			<input type="radio" id="formRadio3" name="todoRadio" class="formRadio formRadio-style3" value="3" onclick="getFocus('todoInput')">
    			<label for="formRadio3" class="radioLabel radioLabel-style3"></label>

    		</div>
    		<div id="todoDisplay" class="displayList">
    			Je sais que je le ferai
    		</div>
    	</article>
<!-- ========== SHOW ========== -->
    	<article id="show">
    		j'irai sur couchtuner <br>
    		<button onclick="window.open('http://localhost/showTracteur/showTracteur.php')">showTracteur</button> 
    	</article>
    	<!-- ========== PARA ========== -->
    	<article id="para">
    		les param c'esdt ma vie<br><br>
    		enregistrer (temporairement) un link<br>
    		loader les cookies sur un serveur mysql et les télécharger<br>
    		évt les paramêtre de show (maj des données etc)<br>
    		<div class="articleForm">
    			<input type="radio" name="paramLoadRadio" id="paramLoadRadiophp" value="php">
    			<label>Load php page when possible (default)</label>
    			<input type="radio" name="paramLoadRadio" id="paramLoadRadiohtml" value="html">
    			<label>Allways load html page</label><br>
    			<button onclick="saveParam()">Save</button> 
    		</div>
    		
    	</article>
	</div>
    
    <footer>
    	<p>
    		Copyright &copy; 2016 userOne0One
    	</p>
    </footer>
    <script type="text/javascript">
    	//where to call functions
		
    	//setArticle();
    	
    </script>
  </body>
</html>
