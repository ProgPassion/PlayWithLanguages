<?php

	ini_set('display_errors', '1');
	require_once 'db_connection.php';
	require_once 'functions.php';

	$phraseId 	 = null;
	$categoryId  = null;
	$imageId 	 = null;
	$img_warning = '';

	$jsonValue = json_encode(0);

	session_start();
	
	if(isset($_SESSION['username'])){

		$_SESSION['open'] = true;
		if(isset($_GET['logout']) && $_GET['logout'] == 1){
				
				destroy_session_and_data();
				header('Location: .');
		}
	}
	else header('Location: .');



    if(isset($_POST['page_form'])) {

		switch ($_POST['page_form']) {
	    case "1": 
	    	
	    	AdminInsertPanel_check();
		    break; //Break if the first case is matched..
		case "2": 

		    AdminSearchPanel_check();
		    break; //Break if the second case is matched..
	    case "3":
		    
		    AdminModifyPanel_check();	
		    break; //Break if the third case is matched..
	    case "4":

	    	AdminModifyCategPanel_check();
	    	break; //Break if the forth case is matched..
	    default:
	        die ("Go back and Refresh The Page<br>or Close the page and open it again");
	    }
	}

	function AdminInsertPanel_check() {

		global $phraseId, $categoryId, $imageId;

		if (!empty($_POST['shqip']) && !empty($_POST['turqisht']) && !empty($_POST['anglisht'])){
			$shqip    = addslashes($_POST['shqip']);
			$turqisht = addslashes($_POST['turqisht']);
			$anglisht = addslashes($_POST['anglisht']);

			queryExecute("INSERT INTO phrase(shqip, turqisht, anglisht) VALUES('$shqip', '$turqisht', '$anglisht')");
			$result   = queryExecute("SELECT id FROM phrase ORDER BY id DESC LIMIT 1");
			$row 	  = $result->fetch_array(MYSQLI_NUM);
			$phraseId = $row[0]; 
		}

		if (!empty($_POST['newCateg']) && !empty($_POST['categName'])){
			$categName  = addslashes($_POST['categName']);
			$categoryId = createNewCategori($categName, true);	
		}
		elseif (!empty($_POST['kategori'])){
			$categoryId = addslashes($_POST['kategori']);
		}
		else {
			$categoryId = 1;
		}	

		if (isset($_FILES['pic']['name']) && !empty($_POST['img-name']) && !is_null($phraseId) && !is_null($categoryId)){
			imgUpload('pic');
		}

		if(!is_null($phraseId)){
			link_tables($phraseId, $categoryId, $imageId);
		}
	}

	function AdminSearchPanel_check() {

		global $jsonValue;

		if(!empty($_POST['search_shqip_mod']) || !empty($_POST['search_ang_mod']) || !empty($_POST['search_turq_mod'])){
				
			$shqip = addslashes($_POST['search_shqip_mod']);
			$ang   = addslashes($_POST['search_ang_mod']);
			$turq  = addslashes($_POST['search_turq_mod']);

			$phrases   = array(array("shqip", $shqip), array("anglisht", $ang), array("turqisht", $turq));
			$jsonValue = linkingTableModify($phrases);
		}
	}

	function AdminModifyPanel_check() {

		global $phraseId, $categoryId, $imageId;

		if(isset($_POST['modify_phrase_id'])){
	
	    	$phraseId = $_POST['modify_phrase_id'];
	    		
	    	$result = queryExecute("SELECT images.id FROM extra_info LEFT JOIN kategorite ON extra_info.kategori_id=kategorite.id LEFT JOIN images ON extra_info.images_id=images.id RIGHT JOIN phrase ON phrase.id=extra_info.phrase_id WHERE phrase.id=$phraseId");
	    	$row = $result->fetch_array(MYSQLI_NUM);
	    	$id_result_img = $row[0];

			if(isset($_POST['fshi'])) {

			    if(!is_null($id_result_img)) {
			    	$img_id = $id_result_img; 
			    	$img_name = "phrase_images_related/image$img_id.jpg";

			    	if(preg_match('/^phrase_images_related\/image\d+.jpg$/', $img_name)){
			    		queryExecute("DELETE FROM images WHERE id=$img_id");
			    		$img_name = "phrase_images_related/image$img_id.jpg";
			    		unlink($img_name);
			    	}
			    	else die("Don't try to modify the html code1!!!<br>Go Back again.");
			    }
			    queryExecute("DELETE FROM phrase WHERE id=$phraseId"); //delete all the phrases that are related to each other from DB
			    queryExecute("DELETE FROM extra_info WHERE phrase_id=$phraseId"); //delete the row with ids of different table that are connected with the phrase
			    	 
			    //delete from table images if there exists any image related to phrases that are deleted;
			}
			else {

			    if(!empty($_POST['shqip_mod']) && !empty($_POST['turq_mod']) && !empty($_POST['ang_mod'])) {

			    	$shqip = addslashes($_POST['shqip_mod']);
					$ang   = addslashes($_POST['ang_mod']);
					$turq  = addslashes($_POST['turq_mod']);

					$phrases = array(array("shqip", $shqip), array("anglisht", $ang), array("turqisht", $turq));
					for($i=0; $i<sizeof($phrases); $i++)
						queryExecute("UPDATE phrase SET " . $phrases[$i][0] . "='" . $phrases[$i][1] . "'" .  "WHERE id=$phraseId");
						
			    }

		    	if (!empty($_POST['kategori_mod'])){

					$categoryId = addslashes($_POST['kategori_mod']);
					queryExecute("UPDATE extra_info SET kategori_id=$categoryId WHERE phrase_id=$phraseId");
				}
					
			    if(!is_null($id_result_img)){
					$img_id   = $id_result_img;
					$img_name = "phrase_images_related/image$img_id.jpg";
					
					if (empty($_POST['img-name_mod'])){
							
						if(preg_match('/^phrase_images_related\/image\d+.jpg$/', $img_name)){
							queryExecute("DELETE FROM images WHERE id='$img_id'");
							queryExecute("UPDATE extra_info SET images_id=NULL WHERE images_id='$img_id'");
							unlink($img_name);	
						}
						else die("Don't try to modify the html code1!!!<br>Go Back again.");
					}
					elseif (!empty($_FILES['pic_mod']['name'])){
						
						if(preg_match('/^phrase_images_related\/image\d+.jpg$/', $img_name)){
							queryExecute("DELETE FROM images WHERE id='$img_id'");
							unlink($img_name);
							imgUpload('pic_mod');
							queryExecute("UPDATE extra_info SET images_id=$imageId WHERE images_id=$img_id");
						}
						else die("Don't try to modify the html code2!!!<br>Go Back again.");
					}
					else {
						$imgName = $_POST['img-name_mod'];
						queryExecute("UPDATE images SET name='$imgName' WHERE id=$img_id", false); 
					}
				}
				elseif (!empty($_FILES['pic_mod']['name']) && !empty($_POST['img-name_mod'])){

					imgUpload('pic_mod');
					queryExecute("UPDATE extra_info SET images_id=$imageId WHERE phrase_id=$phraseId");
				}	
			}	
	    }
	    else die("Don't try to modify the html code!!!<br>Go Back again.");
	}

	function AdminModifyCategPanel_check() {

		global $categoriId;

		if(!empty($_POST['kategori-mod_entity']) && $_POST['kategori-mod_entity'] != 1) {

    		$categoriId = $_POST['kategori-mod_entity'];
    		if(isset($_POST['categ-delete-butt'])) {
    			queryExecute("DELETE FROM kategorite WHERE id=$categoriId");
    			queryExecute("UPDATE extra_info SET kategori_id=1 WHERE kategori_id=$categoriId");
    		}
    		elseif(!empty($_POST['categ-modify-name'])){
    			$newCategoriName = $_POST['categ-modify-name'];
    			queryExecute("UPDATE kategorite SET kategoria='$newCategoriName' WHERE id=$categoriId");
    		}
    	}
	}

	function destroy_session_and_data(){
	
		$_SESSION = array();
		setcookie(session_name(), '', time() - 2592000, '/');
		session_destroy();
	}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Admin</title>
	<link href="https://fonts.googleapis.com/css?family=Roboto|Lato" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="./css/style.css">
	<style type="text/css">
		
		html, body, .wrap {
			min-height: 910px;
		}
		
		.explain { 
			line-height: 24px;
			margin-top: 5px; 
		}
		.container {
			overflow: hidden;
		}
		
		.optional {
			width: 60%;
			border-right: 1px dashed #ccc;
		}
		.kategori {
			width: 40%;
		}
		.kategori input[type=text] {
			width: 60%;
			float: right;
		}
		.kategori input[type=checkbox] {	
			vertical-align: middle;
			cursor: pointer;
		}
		.kategori select {
			cursor: pointer;
			width: 100%;
		}
		input[type=file] {
			cursor: pointer;
		}
		input[type=text].img-name{
			float: left;
			margin-top: 6px;
		}
		.main-text-field {
			width: 100%;
			padding: 35px 2px 0px 2px;
			box-sizing: border-box;
		}
		.main-text-field table {
			width: 100%;
		}
		.main-text-field table th {
			font-size: 18px;
		}
		.main-text-field table td {
			padding: 0 5px 5px 5px;
		}
		.main-text-field table td input[type=text]{
			width: 200px;
		}
		.subm-butt {
			background-color: #2d3951;
			padding: 5px;
			box-sizing: border-box;
			border-radius: 3px;
		}
		.subm-butt input[type=submit] {
			display: block;
			margin: 0 auto;
			width: 240px;
			height: 30px;
			cursor: pointer;
		}
		
		.modify-type {
			float: right; 
		}
		.shqip, .ang, .turq, .modify-butt, .modify-categ, .empty, .modify-categ-butt {
			margin-top: 10px; 
			padding: 0px;
		}
		
		#chk-apr-container, #categ-name-container{
			float: left;
			overflow: auto;
			width: 40%;
		}
		#categ-name-container {
			width: 60%;
		}
		#categ-name-container > input[type=text]{
			float: left;
			width: 90%;
		}
		.kategori-conteiner{
			overflow: hidden;
			margin: 6px 0 20px 0;
		}
		.kategoria > input[type=text] {
			width: calc(90% - 4px);
		}
		.result {
			
			height: 120px;
			/*margin: 0;
			margin-top: 10px;*/
		}
		
		.result img {
			margin: 4px auto;
		}
		.struct {
			width: 100%;
			overflow: auto;
		}
		.modify-butt > input[type=submit]{
			width: 90%;
			height: 30px;
			margin-left: 10px;
			border-radius: 10px;
			font-family: 'Lato', sans-serif;
			font-size: 18px;
			cursor: pointer 
		}
		input[type=submit]:hover {
			background: linear-gradient(#eee,#999);
		}
		select {
			margin: 0;
			width: 90%;
			cursor: pointer;
		}
		.modify-categ > input[type=text] {
			margin-right: 10%;
			float:right;
			-webkit-margin-before: 2px;
		}
		.modify-categ > input[type=submit] {
			cursor: pointer;
			margin-right: 10px;
			font-size: 16px;
			padding: 0 10px 0 10px;
			font-family: 'Lato', sans-serif;
		}
	
		.modal-content {
		    width: 700px;
		}
		
		#infoOnClick tr:nth-child(even){background-color: #f2f2f2;}
		#infoOnClick tr:hover {background-color: #ddd;}
		#infoOnClick tr:first-child, #infoOnClick tr:nth-child(2), #infoOnClick tr:nth-child(4) {
		    font-weight: bold;
		}

		#infoOnClick tr:nth-child(2) {
			background-color: #fefefe;
		}
		textarea {
		    resize: none;
		}
		.modal input[type=file] {
			width: 100%;
			cursor: pointer;
		}
		.modal input[type=submit] {
			margin-top: 10px;
			margin-right: 10px;
			width: 90px;
			height: 30px;
			font-size: 16px;
			cursor: pointer;
			font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
		}

		/*Stilizimi per pjesen warning nga inputet e perdoruesit*/

		.warning {
			color: #A90000;
			text-shadow: 2px 2px 2px #C90000;
			font-family: 'Roboto' , sans-serif;
			font-size: 11px;
		}

		#wrg-emerto-img1 {
			margin-left: 2px;
		}
	</style>
</head>
<body>
	<div class="wrap">
		<div class="wrapper">
			<form action="admin.php" method="get">
			<div class="first-line-ttl"><h1 class="tlt">Admin Panel</h1></div><div class="first-line-bck"><a href="admin.php?logout=1">Logout</a></div>
		    </form>
			<hr class="style">
			<p class="explain">Paneli Admin jep mundesine per shtimin e te dhenave ne fjalorth ne 3 gjuhe. Pasi te keni perfunduar me shtimin e te dhenave mund te <a href="user.php">KLIKONI ketu</a> per tu drejtuar direkt ne Panelin User dhe te navigoni ne aplikacion. Nese keni perfunduar plotsisht me shtimin e te dhenave beni logout!</p>
			<div class="container">
				<div class="sub-cont1">
				<form method="post" action="admin.php" enctype="multipart/form-data" onsubmit="return validateForm1()">
					<div class="optional">
						<h1>Opsionale</h1>
						<h4>Per te krijuar nje kerkim me specifik mund te zgjidhni kategorine e shprehjes ose mund ti bashkangjitni nje imazhe.</h4>
						<div class="main-text-field">
							<table>
								<tr><th colspan="2">Gjuhet</th></tr>
								<tr><td>Shqip</td><td><input type="text" id="shq-input" name="shqip" onkeyup="checkValidation(this.id)"></td><td><span class="warning" id="wrg-shq-input">Ju lutem vendosni frazen ne Shqip</span></td></tr>
								<tr><td>Turqisht</td><td><input type="text" id="tq-input" name="turqisht" onkeyup="checkValidation(this.id)"></td><td><span class="warning" id="wrg-tq-input">Ju lutem vendosni frazen ne Turqisht</span></td></tr>
								<tr><td>Anglisht</td><td><input type="text" id="en-input" name="anglisht" onkeyup="checkValidation(this.id)"></td><td><span class="warning" id="wrg-en-input">Ju lutem vendosni frazen ne Anglisht</span></td></tr>
							</table>
						</div>
					</div>
					<div class="kategori">
						<div class="kategori-conteiner">
							<h5>Kategori e re</h5>
							<div id='chk-apr-container'><label class="chk">Aprovo <input type="checkbox" name="newCateg" id="apr-chk-box" value="1" onclick="checkValidation(this.id)"></label><br><span class="warning" id="wrg-chk-apr">Kliko per aprovim</span></div><div id='categ-name-container'><input type="text" id="new-categ-name" name="categName" placeholder="Emri Kategorise" onkeyup="checkValidation(this.id)"><br><span class="warning" id="wrg-categ-name">Ju lutem shkruani kategorine e re</span></div>
						</div>
						<div class="kategori-conteiner">
							<h5>Kategori ekzistuese</h5>
							<select name="kategori" id="list-kategori">
									<option value="0">Kategori</option>
							</select>
						</div>
						<div class="kategori-conteiner">
							<h5>Ngarko nje imazhe</h5>
							<input type="file" id="upl-img" name="pic" accept="image/*" onclick="checkValidation(this.id)">
							<span class="warning" id="wrg-zgjidh-img">Ju lutem zgjidhni nje imazh jpg, png, gif</span>
						</div>
						<div class="kategori-conteiner">
							<h5>Emerto imazhin</h5>
							<input class="img-name" type="text"  id="img-name" name="img-name" onkeyup="checkValidation(this.id)">
							<span class="warning" id="wrg-emerto-img1">Ju lutem emertoni imazhin</span><span class="warning" id="wrg-emerto-img2">Ju lutem perdorini [A-Za-z',"]</span>
						</div>
					</div>
					<div class="subm-butt">
						<input type="submit" name="submit" value="Shto ne Fjalorth">
					</div>
					<input type="hidden" name="page_form" value="1">
				</form>
				</div>
				<div class="sub-cont2">
					<h2>Modifiko</h2>
					<form method="post" action="admin.php">
					<div class="shqip">
						<input type="text" name="search_shqip_mod" placeholder="Shqip">
						<div class="result" id="sh" onmouseover="addEvent('sh')" onmouseout="rmEvent('sh')">
								
						</div>
					</div>
					<div class="turq">
						<input type="text" name="search_turq_mod" placeholder="Turqisht">
						<div class="result" id="t" onmouseover="addEvent('t')" onmouseout="rmEvent('t')">
								<img src="img/icon_coursebook.png">	
						</div>
					</div>
					<div class="ang">
						<input type="text" name="search_ang_mod" placeholder="Anglisht">
						<div class="result" id="a" onmouseover="addEvent('a')" onmouseout="rmEvent('a')">
								<img src="img/icon_coursebook.png">
						</div>
					</div>
					<div class="struct">
						<div class="modify-butt">		
							<input type="hidden" name="page_form" value="2">
							<input type="submit" name="submit_mod" value="Kerko">
						</div>
					</div>
					</form>
					<br>
					<div class="struct">
						<h2>Modifiko Kategorite</h2>
					</div>
					<form method="post" action="admin.php">
					<div class="struct">	
						<div class="modify-categ">
							<select name="kategori-mod_entity" id="kategori-mod_entity">
								<option value=0>Kategorite</option>
							</select>
							<input type="hidden" name="page_form" value="4">
						</div>
						<div class="modify-categ">
							<input type="text" name="categ-modify-name" placeholder="Shkruaj emrin e ri">
						</div>
						<div class="modify-categ">
							<input type="submit" name="categ-modify-butt" value="Modifiko">
							<input type="submit" name="categ-delete-butt" value="Fshi">
						</div>
					</div>
					</form>
				</div>
			</div>
		</div>
		<div id="myModal" class="modal">
			<form method="post" action="admin.php" enctype="multipart/form-data">
			  <!-- Modal content -->
			  <div class="modal-content">
			    <span class="close">&times;</span>
			    <table id="infoOnClick"></table>
			    <input type="hidden" name="page_form" value="3">
			    <input type="submit" name="modifiko" value="Modifiko">
			    <input type="submit" name="fshi" value="Fshi">
			  </div>
			</form>  
		</div>
	</div>
	<script type="text/javascript">
		<?php echo "var myJSON = " . getCategories() . ";\n\t\tvar myJSONphrases = " . $jsonValue . ";\n\t\tvar imgWarning = '" . $img_warning . "';\n"; ?>
		
		if (imgWarning.length > 0) {alert(imgWarning);}
		var categoryIds = ["list-kategori", "kategori-mod_entity"];

		for(j=0; j<2; j++){
			for(i=0; i<myJSON.length; i++){

				var option   = document.createElement("option");
				option.text  = myJSON[i][1];
				option.value = myJSON[i][0];
				var select   = document.getElementById(categoryIds[j]);
				select.appendChild(option);
			}
		}
		
		displayPhrases(myJSONphrases);

		document.getElementById("wrg-shq-input").style.display = "none";
		document.getElementById("wrg-tq-input").style.display  = "none";
		document.getElementById("wrg-en-input").style.display  = "none";

		document.getElementById("wrg-chk-apr").style.display    = "none";
		document.getElementById("wrg-categ-name").style.display = "none";
			
		document.getElementById("wrg-zgjidh-img").style.display  = "none";
		document.getElementById("wrg-emerto-img1").style.display = "none";
		document.getElementById("wrg-emerto-img2").style.display = "none";
		
			

		function displayPhrases(myJSONphrases) {

			var shqipPhrases    = '';
			var turqishtPhrases = '';
			var anglishtPhrases = '';

			for(i=0; i<myJSONphrases.length; i++) {
				var phrase_num = i+1;
				var shqip      = myJSONphrases[i][1];
				var turqisht   = myJSONphrases[i][2];
				var anglisht   = myJSONphrases[i][3];
				
				shqipPhrases    += "<p id='sh_phr" + phrase_num + "' onclick='showInfoPhrase(this.id)'>" + phrase_num + ". " + shqip + "</p>";	
				turqishtPhrases += "<p id='t_phr"  + phrase_num + "' onclick='showInfoPhrase(this.id)'>" + phrase_num + ". " + turqisht + "</p>";   
				anglishtPhrases += "<p id='a_phr"  + phrase_num + "' onclick='showInfoPhrase(this.id)'>" + phrase_num + ". " + anglisht + "</p>";
			}

			if(shqipPhrases == ''){
				shqipPhrases    = '<img src="img/icon_coursebook.png">';
				turqishtPhrases = '<img src="img/icon_coursebook.png">';
				anglishtPhrases = '<img src="img/icon_coursebook.png">';
			}

			document.getElementById("sh").innerHTML = shqipPhrases;
			document.getElementById("t").innerHTML  = turqishtPhrases;
			document.getElementById("a").innerHTML  = anglishtPhrases;
		}


		var visible = function(el) {

			var rect = el.getBoundingClientRect(), top = rect.top, height = rect.height,
			el = el.parentNode;
			rect = el.getBoundingClientRect();
			if (top <= rect.bottom === false) return false;
			if ((top + height) <= rect.top)   return false;

			return true;
		}

		var eventObjId = '';
		function addEvent(block_id){

			document.getElementById(block_id).addEventListener("scroll", update);
			eventObjId = block_id;
			console.log("add: " + eventObjId);
		}

		function rmEvent(block_id){

			document.getElementById(block_id).removeEventListener("scroll", update);
			console.log("remove: " + eventObjId);
		}
		
		var update = function (){

			if (eventObjId == 'a'){
				for(i=1; i<=myJSONphrases.length; i++){
					shphr_id = 'sh_phr' + i;
					tphr_id  = 't_phr' + i;
					aphr_id  = 'a_phr' + i;

					if (visible(document.getElementById(aphr_id))){
						document.getElementById(shphr_id).scrollIntoView();
						document.getElementById(tphr_id).scrollIntoView();
						break;
					}
				}
			}
			else if (eventObjId == 't'){
				for(i=1; i<=myJSONphrases.length; i++){
					shphr_id = 'sh_phr' + i;
					tphr_id  = 't_phr'  + i;
					aphr_id  = 'a_phr'  + i;

					if (visible(document.getElementById(tphr_id))){
						document.getElementById(shphr_id).scrollIntoView();
						document.getElementById(aphr_id).scrollIntoView();
						break;
					}
				}
			}
			else if (eventObjId == 'sh'){
				for(i=1; i<=myJSONphrases.length; i++){
					shphr_id = 'sh_phr' + i;
					tphr_id  = 't_phr'  + i;
					aphr_id  = 'a_phr'  + i;

					if (visible(document.getElementById(shphr_id))){
						document.getElementById(aphr_id).scrollIntoView();
						document.getElementById(tphr_id).scrollIntoView();
						break;
					}
				}	
			}
			
		};

		function showInfoPhrase(phr_id){

			var n            = phr_id.search(/\d/);
			var id_num       = phr_id.substring(n);
			var phrase_db_id = myJSONphrases[id_num-1][0];
			var shqip        = myJSONphrases[id_num-1][1];
			var turqisht     = myJSONphrases[id_num-1][2];
			var anglisht     = myJSONphrases[id_num-1][3];
			var kategoria    = myJSONphrases[id_num-1][4];
			var imazhi       = (myJSONphrases[id_num-1][5]) ? myJSONphrases[id_num-1][5]:"";

			var table = document.getElementById("infoOnClick");

			var row   = table.insertRow(0);
			var cell1 = row.insertCell(0);
			var cell2 = row.insertCell(1);
			var cell3 = row.insertCell(2);
		
			cell1.innerHTML = "Shqip";
			cell2.innerHTML = "Turqisht";
			cell3.innerHTML = "Anglisht";
			

			row   = table.insertRow(1);
			cell1 = row.insertCell(0);
			cell2 = row.insertCell(1);
			cell3 = row.insertCell(2);
			
			cell1.innerHTML = "&nbsp;";
			cell2.innerHTML = "<input type='hidden' name='modify_phrase_id' value='" + phrase_db_id + "'>&nbsp;";  // Essential sensitive part to not be modify by the user
			cell3.innerHTML = "&nbsp;";

			row   = table.insertRow(2);
			cell1 = row.insertCell(0);
			cell2 = row.insertCell(1);
			cell3 = row.insertCell(2);
		
			cell1.innerHTML = "<textarea id='shqip_modify_text' rows='10' cols='24' name='shqip_mod'>" + shqip + "</textarea>";
			cell2.innerHTML = "<textarea id='turq_modify_text' rows='10' cols='24' name='turq_mod'>" + turqisht + "</textarea>";
			cell3.innerHTML = "<textarea id='ang_modify_text' rows='10' cols='24' name='ang_mod'>" + anglisht + "</textarea>";
		

			row   = table.insertRow(3);
			cell1 = row.insertCell(0);
			cell2 = row.insertCell(1);
		
			cell1.innerHTML = "Kategoria";
			cell2.innerHTML = "Imazhi";
			cell2.colSpan   = "2";
			
			row   = table.insertRow(4);
			cell1 = row.insertCell(0);
			cell2 = row.insertCell(1);
			cell3 = row.insertCell(2);

			cell1.innerHTML = "<select name='kategori_mod' id='list-kategori-mod'></select>";

			for(i=0; i<myJSON.length; i++){
				var option   = document.createElement("option");
				option.text  = myJSON[i][1];	
				option.value = myJSON[i][0];

				if(myJSON[i][1] === kategoria)
					option.selected = true;

				var select = document.getElementById("list-kategori-mod");
				select.appendChild(option);
			}

			cell2.innerHTML = '<input type="file" id="upl-img_modify" name="pic_mod" accept="image/*">' + '<input class="img-name" type="text" id="img-name_modify" name="img-name_mod" value="' + imazhi + '">';
			cell3.innerHTML = "Nese doni te fshini nje imazh mjafton te leni input-in e emrit bosh.";

			/*document.getElementById("wrg-zgjidh_modify-img").style.display = "none";
			document.getElementById("wrg-emerto_modify-img1").style.display = "none";*/

			displayDivModal();
		}

		var modal = document.getElementById('myModal');

		// Get the <span> element that closes the modal
		var span = document.getElementsByClassName("close")[0];

		// When the user clicks the button, open the modal 
		function displayDivModal() {
		    modal.style.display = "block";
		}

		// When the user clicks on <span> (x), close the modal
		span.onclick = function() {
		    modal.style.display = "none";
		    clearDivModal();
		}

		// When the user clicks anywhere outside of the modal, close it
		window.onclick = function(event) {
		    if (event.target == modal) {
		        modal.style.display = "none";
		        clearDivModal();
		    }
		}
		
		function clearDivModal() {
			for(i=0; i<5; i++) 
				document.getElementById("infoOnClick").deleteRow(0);	
		}


		/////////////////////////////////////////////////////////////
		//////////
		////////// Javascript V A L I D A T I O N
		/////////////////////////////////////////////////////////////

		function validateForm1() {
			var inpNCDetect = false;

			var shqip      = document.getElementById("shq-input").value;
			var turqisht   = document.getElementById("tq-input").value;
			var anglisht   = document.getElementById("en-input").value;
			var checkbox   = document.getElementById("apr-chk-box").checked;
			var nCategName = document.getElementById("new-categ-name").value;
			var imgUp      = document.getElementById("upl-img").value;
			var imgName    = document.getElementById("img-name").value;
			

			if(shqip.length === 0)    {document.getElementById("wrg-shq-input").style.display = "block"; inpNCDetect = true;}
			if(turqisht.length === 0) {document.getElementById("wrg-tq-input").style.display  = "block"; inpNCDetect = true;}
			if(anglisht.length === 0) {document.getElementById("wrg-en-input").style.display  = "block"; inpNCDetect = true;}

			if(!checkbox && nCategName.length > 0)  {document.getElementById("wrg-chk-apr").style.display    = "block"; inpNCDetect = true;}
			if(checkbox && nCategName.length === 0) {document.getElementById("wrg-categ-name").style.display = "block"; inpNCDetect = true;}
			
			if(imgUp.length === 0 && imgName.length > 0) {document.getElementById("wrg-zgjidh-img").style.display  = "block"; inpNCDetect = true;}
			if(imgUp.length > 0 && imgName.length === 0) {document.getElementById("wrg-emerto-img1").style.display = "block"; inpNCDetect = true;}
			

			if(inpNCDetect)
				return false;
		}

		function checkValidation(theEntityId){

			if(document.getElementById(theEntityId).value.replace(/\s/g, '').length > 0) {
				var mainObj = recursSearch(document.getElementById(theEntityId));
				var spanTags = mainObj.getElementsByTagName("SPAN");
				var len = spanTags.length;

				for(i=0; i<len; i++){
					if(spanTags[i].style.display === "block")
						spanTags[i].style.display = "none";
				}
			}	
		}

		function recursSearch(tagVerify) {

			if(tagVerify.tagName != "DIV" && tagVerify.tagName != "TR"){
				return recursSearch(tagVerify.parentNode);
			}
			return tagVerify;
		}

	</script>
</body>
</html>
