<?php
	
	require_once 'db_connection.php';


	$phrases    = null;
	$kategoriId = null;
	$imageId    = null;

	$jsonCategArray = array();
	$jsonImagesArray = array(); 

	if(!empty($_POST['search_shqip']) || !empty($_POST['search_ang']) || !empty($_POST['search_turq'])){
		
		$shqip = addslashes($_POST['search_shqip']);
		$ang   = addslashes($_POST['search_ang']);
		$turq  = addslashes($_POST['search_turq']);

		$phrases = array(array("shqip", $shqip), array("anglisht", $ang), array("turqisht", $turq));
		
	}

	if(!empty($_POST['kategori'])){
		
		$kategoriId = addslashes($_POST['kategori']);
	}

	if(!empty($_POST['img_name'])){
		
		$imgName = addslashes($_POST['img_name']);
		$row = queryExecute("SELECT id FROM images WHERE name='$imgName'", true);
		$imageId = $row[0];
	}

	$jsonValue = linkingTableInfo($phrases, $kategoriId, $imageId);  //The main variabel that holds all info for displaying in result front end!!!

	function linkingTableInfo($phrases, $kategoriId, $imageId) {

		$jsonReady = 0;
		$staticQueryString = "SELECT phrase.shqip,phrase.turqisht,phrase.anglisht,kategorite.kategoria,images.name FROM extra_info LEFT JOIN kategorite ON extra_info.kategori_id=kategorite.id LEFT JOIN images ON extra_info.images_id=images.id RIGHT JOIN phrase ON phrase.id=extra_info.phrase_id";

		if(!is_null($phrases) && is_null($kategoriId) && is_null($imageId)) {
			
			for($i=0; $i<3; $i++) {
				if(!empty($phrases[$i][1])){
					$language = $phrases[$i][0];
					$input = $phrases[$i][1];
					
					$row = queryExecute("$staticQueryString WHERE phrase.$language LIKE '%$input%'", true);
					
					$jsonReady = showResult($row);
					
					break;
				}
			}
		}
		elseif(!is_null($phrases) && !is_null($kategoriId) && is_null($imageId)){
			
			for($i=0; $i<3; $i++) {
				if(!empty($phrases[$i][1])){
					$language = $phrases[$i][0];
					$input = $phrases[$i][1];
					$row = queryExecute("$staticQueryString WHERE phrase.$language LIKE '%$input%' AND extra_info.kategori_id=$kategoriId", true);
					$jsonReady = showResult($row);
					break;
				}
			}
		}
		elseif (!is_null($phrases) && is_null($kategoriId) && !is_null($imageId)) {
			
			for($i=0; $i<3; $i++) {
				if(!empty($phrases[$i][1])){
					$language = $phrases[$i][0];
					$input = $phrases[$i][1];
					$row = queryExecute("$staticQueryString WHERE phrase.$language LIKE '%$input%' AND extra_info.images_id=$imageId", true);
					$jsonReady = showResult($row);
					break;
				}
			}
		}
		elseif (!is_null($phrases) && !is_null($kategoriId) && !is_null($imageId)) {
			
			for($i=0; $i<3; $i++) {
				if(!empty($phrases[$i][1])){
					$language = $phrases[$i][0];
					$input = $phrases[$i][1];
					$row = queryExecute("$staticQueryString WHERE phrase.$language LIKE '%$input%' AND extra_info.kategori_id=$kategoriId AND extra_info.images_id=$imageId", true);
					$jsonReady = showResult($row);
					var_dump($jsonReady);
					break;
				}
			}	
		}
		elseif (is_null($phrases) && !is_null($kategoriId) && is_null($imageId)) {

			$row = queryExecute("$staticQueryString WHERE extra_info.kategori_id=$kategoriId", true);
			$jsonReady = showResult($row);
		}
		elseif (is_null($phrases) && !is_null($kategoriId) && !is_null($imageId)) {
			
			$row = queryExecute("$staticQueryString WHERE extra_info.kategori_id=$kategoriId AND extra_info.images_id=$imageId", true);
			$jsonReady = showResult($row);
		}
		elseif (is_null($phrases) && is_null($kategoriId) && !is_null($imageId)) {
			
			$row = queryExecute("$staticQueryString WHERE extra_info.images_id=$imageId", true);
			$jsonReady = showResult($row);
		}

		return json_encode($jsonReady);
	}
		

	function showResult($info) {

		$jsonResultArray = array();

		if(is_object($info[0])) {
			// $row[0] is object meaning that the input value matched more than one phrase in DataBase
			
			for($i=0; $i<$info[1]; $i++) {
				$info[0]->data_seek($i);
				$row = $info[0]->fetch_array(MYSQLI_NUM);
				array_push($jsonResultArray, $row);
			}
		}
		elseif(is_array($info)) {
			// $row[0] is array meaning that the input value matched with only one phrase in DataBase
			array_push($jsonResultArray, $info);
		}

		return $jsonResultArray;
	}


	function queryExecute($query, $resultFlag) {
	
		global $conn;
		$result = $conn->query($query);
		if(!$result) die($conn->error);
		
		if($resultFlag && is_object($result)){
			$rows = $result->num_rows; 

			if($rows === 1){
				$row = $result->fetch_array(MYSQLI_NUM);
				return $row;
			}
			elseif ($rows > 1){
				return [$result, $rows];
			}
		}
		elseif($resultFlag)
			return 0;	
	}

	function listImg() {

		global $jsonImagesArray;

		$result = queryExecute("SELECT * FROM images", true);
		$jsonImagesArray = showResult($result);
		return json_encode($jsonImagesArray);
	}

	function getCategories() {

		global $jsonCategArray;

		$result = queryExecute("SELECT * FROM kategorite", true);
		$jsonCategArray = showResult($result);
		return json_encode($jsonCategArray);
	}

?>
<!DOCTYPE html>
<html>
<head>
	<title>User</title>
	<link href="https://fonts.googleapis.com/css?family=Roboto|Lato" rel="stylesheet"> 
	<style type="text/css">
		* {
			margin: 0;
			padding: 0;
		}
		html, body {
			height: 100%;
			min-height: 650px;
			background-image: url('img/notebook.png');
			background-repeat: repeat;
		}
		p {
			font-family: 'Lato', sans-serif;
		}
		.wrap {
			width: 930px;
			height: 100%;
			min-height: 650px;
			margin: 0px auto;
			background: linear-gradient(#07233f, #5598db);
			padding: 20px 0;
			box-sizing: border-box;
		}
		.wrapper {
			width: 720px;
			margin: 0px auto;
			box-shadow: 3px 3px 6px rgba(0, 0, 0, 1), 3px 3px 6px rgba(0, 0, 0, 1);
			border-width:3px;
			border-style:solid;
			border-bottom-color:#aaa;
			border-right-color:#aaa;
			border-top-color:#efefef;
			border-left-color:#efefef;
			border-radius: 3px;
			background-color: #fff;
			padding: 15px 25px;
		}
		div.first-line-ttl {
			height: 56px;
			width: 70%;
			float: left;
			overflow: hidden;
		}
		h1.tlt {
			font-family: 'Roboto' , sans-serif;
			font-size: 48px;
		}
		div.first-line-bck {
			height: 56px;
			width: 30%;
			float: right;
			overflow: hidden;

		}
		div.first-line-bck a {
			text-decoration: none;
			display: inline-block;
			float: right;
			margin-right: 30px;
			margin-top: 20px;
			font-family: 'Roboto' , sans-serif;
			font-size: 24px;
			vertical-align: text-bottom;
		}
		div.first-line-bck a:link, div.first-line-bck a:visited {
			color: #1a4168;
			text-decoration: none;
		}
		div.first-line-bck a:hover {
			color: #1c5ea0;
			text-decoration: underline;
		}
		hr.style {
			 border: 0; 
			 height: 1px; 
			 background: #333; 
			 background-image: linear-gradient(to right, #ccc, #333, #ccc);	
		}
		.sub-cont1, .sub-cont2 {
			width: 100%;
			overflow: auto;
		}
		.sub-cont2 input[type=text] {
			width: calc(90% - 4px);
			margin: 0 auto;
			display: block;
		}
		.optional {
			width: 66.6666666%;
			float: left;
			padding: 10px 80px 10px 10px;
			box-sizing: border-box;
		}
		.kategori {
			width: 33.3333333%;;
			float: left;
			padding: 10px;
			box-sizing: border-box;
		}
		.shqip, .ang, .turq {
			width: 33.3333333%;
			float: left;
			margin-top: 5px; 
			padding: 10px;
			box-sizing: border-box;
		}
		.explain {
			padding-left: 20px;
			color: #4c4a37; 
			font-family: 'Source Sans Pro', sans-serif; 
			font-size: 16px; 
			line-height: 32px; 
		}
		select, button {
			margin: 20px auto 0 auto;
			width: 90%;
			cursor: pointer;
			display: block
		}
		.optional h1, .optional h4 {
			font-family: 'Lato', sans-serif;
		}
		.result {
			width:90%;
			height: 220px;
			background-color: #dedede;
			border-width:3px;
			border-style:solid;
			border-bottom-color:#aaa;
			border-right-color:#aaa;
			border-top-color:#efefef;
			border-left-color:#efefef;
			border-radius: 5px;
			margin: 10px auto 0 auto;
			padding: 6px 8px;
			box-sizing: border-box;
			overflow: auto;
		}
		.result img {
			width: 100px;
			display: block;
			margin: 50px auto;
		}
		.result p {
			line-height: 17px;
			margin-bottom: 20px;
			font-family: 'Lato', sans-serif;
			cursor: pointer;
		}
		#dropPanel {
			width: 220px;
			height: 270px;
			background-color: #bebebe;
			overflow: auto;
			margin-top: 0px;
			margin-left: 0px;
			position: absolute;
			top: 198px;
			left: 761px;
			padding: 3px 8px;
		}	
		#dropPanel img {
			width: 100px;
			float: right;
		}
		#dropPanel table {
			width: 100%;
			border-collapse: separate;
    		border-spacing: 5px;
		}	
		.search_butt {
			width: 33.3333333%;
			height: 55px;
			padding: 10px;
			box-sizing: border-box;
		}
		.search_butt input[type=submit]{
			width: 90%;
			height: 100%;
			font-size: 25px;
			font-family: 'Lato' sans-serif;
			cursor: pointer;
			margin: 0 auto;
			display: block;
		}

		#tabImages tr {
			cursor: pointer;
		}


		/* The Modal (background) */
		.modal {
		    display: none; /* Hidden by default */
		    position: fixed; /* Stay in place */
		    z-index: 1; /* Sit on top */
		    padding-top: 50px; /* Location of the box */
		    left: 0;
		    top: 0;
		    width: 100%; /* Full width */
		    height: 100%; /* Full height */
		    overflow: auto; /* Enable scroll if needed */
		    background-color: rgb(0,0,0); /* Fallback color */
		    background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
		}

		/* Modal Content */
		.modal-content {
		    background-color: #fefefe;
		    margin: auto;
		    padding: 25px 20px;
		    border: 1px solid #888;
		    width: 600px;
		}

		/* The Close Button */
		.close {
		    color: #aaaaaa;
		    float: right;
		    font-size: 28px;
		    font-weight: bold;
		}

		.close:hover,
		.close:focus {
		    color: #000;
		    text-decoration: none;
		    cursor: pointer;
		}

		#infoOnClick {
		    font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
		    border-collapse: collapse;
		    width: 100%;
		}
		#infoOnClick td {
		    border: 1px solid #ddd;
		    padding: 8px;
		}

		#infoOnClick tr:nth-child(even){background-color: #f2f2f2;}

		#infoOnClick tr:hover {background-color: #ddd;}

		#infoOnClick tr:first-child {
			padding-top: 12px;
		    padding-bottom: 12px;
		    text-align: left;
		    background-color: #0059b3;
		    color: white;
		    font-size: 20px;
		}
	</style>
</head>
<body>
<!--<?php //echo $jsonValue; ?>-->
	<div class="wrap">
			<div class="wrapper">
				<div class="first-line-ttl"><h1 class="tlt">User Panel</h1></div><div class="first-line-bck"><a href="/lado/">Shko Prapa</a></div>
				<hr class="style">
				<p class="explain">Zgjidh kategorine(opsionale) dhe gjuhen per shprehjen ose fjalen qe kerkoni.</p>
				<div class="container">
					<form method="post" action="user.php">
					<div class="sub-cont1">
						<div class="optional">
							<h1>Opsionale</h1>
							<h4>Per nje kerkim me specifik mund te zgjidhni kategorine dhe-ose nje imazh qe lidhet me ate qe kerkoni.</h4>
						</div>
						<div class="kategori">
							<select name="kategori" id="list-kategori">
								<option value="0">Kategori</option>
							</select>
							<button type="button" id="drop_img" onclick="buttEvent()">Imazhet</button>
							<div id="dropPanel">
								<table id="tabImages">
									
								</table>
							</div>
							<input type="hidden" name="img_name" id="img_name">
						</div>
					</div>
					<div class="sub-cont2">
						<div class="shqip">
							<input type="text" name="search_shqip" placeholder="Shqip">
							<div class="result" id="sh" onmouseover="addEvent('sh')" onmouseout="rmEvent('sh')">
								
							</div>
						</div>
						<div class="turq">
							<input type="text" name="search_turq" placeholder="Turqisht">
							<div class="result" id="t" onmouseover="addEvent('t')" onmouseout="rmEvent('t')">
								
							</div>
						</div>
						<div class="ang">
							<input type="text" name="search_ang" placeholder="Anglisht">
							<div class="result" id="a" onmouseover="addEvent('a')" onmouseout="rmEvent('a')">
								
							</div>
						</div>
					</div>
					<div class="search_butt">
						<input type="submit" name="submit" value="Kerko">
					</div>
					</form>
				</div>

			</div>
			<div id="myModal" class="modal">

			  <!-- Modal content -->
			  <div class="modal-content">
			    <span class="close">&times;</span>
			    <table id="infoOnClick"></table>
			  </div>

			</div>
	</div>
	<script type="text/javascript">
		var element = document.getElementById("drop_img");
		var rect    = element.getBoundingClientRect();
		var topPos  = Math.round(rect.y + rect.height) + 1 + "px";
		var leftPos = Math.round(rect.x + rect.width / 4) + "px";
		
		
		<?php echo "var myJSONphrases = " . $jsonValue . ";\n\t\tvar myJSONcategories = " . getCategories() . ";\n\t\tvar myJSONimages = " . listImg() . ";\n\n"; ?>
		
		document.getElementById("dropPanel").style.top = topPos;
		document.getElementById("dropPanel").style.left = leftPos;
		document.getElementById("dropPanel").style.display = "none";
		
		displayPhrases(myJSONphrases);
		displayCategory(myJSONcategories);
		displayImages(myJSONimages);

		
		function displayPhrases(myJSONphrases) {

			var shqipPhrases = '';
			var turqishtPhrases = '';
			var anglishtPhrases = '';

			for(i=0; i<myJSONphrases.length; i++) {
				var phrase_num = i+1;
				var shqip    = myJSONphrases[i][0];
				var turqisht = myJSONphrases[i][1];
				var anglisht = myJSONphrases[i][2];
				
				shqipPhrases += "<p id='sh_phr" + phrase_num + "' onclick='showInfoPhrase(this.id)'>" + phrase_num + ". " + shqip + "</p>";	
				turqishtPhrases += "<p id='t_phr" + phrase_num + "' onclick='showInfoPhrase(this.id)'>" + phrase_num + ". " + turqisht + "</p>";   
				anglishtPhrases += "<p id='a_phr" + phrase_num + "' onclick='showInfoPhrase(this.id)'>" + phrase_num + ". " + anglisht + "</p>";
			}

			if(shqipPhrases == ''){
				shqipPhrases    = '<img src="img/icon_coursebook.png">';
				turqishtPhrases = '<img src="img/icon_coursebook.png">';
				anglishtPhrases = '<img src="img/icon_coursebook.png">';
			}

			document.getElementById("sh").innerHTML = shqipPhrases;
			document.getElementById("t").innerHTML = turqishtPhrases;
			document.getElementById("a").innerHTML = anglishtPhrases;
		}
		
		function displayCategory(myJSONcategories) {
			
			for(i=0; i<myJSONcategories.length; i++){
				var option = document.createElement("option");
				option.text  = myJSONcategories[i][1];
				option.value = myJSONcategories[i][0];
				var select = document.getElementById("list-kategori");
				select.appendChild(option);
			}
		}

		function displayImages(myJSONimages) {

			var tabElements = '';
			for(i=0; i<myJSONimages.length; i++){
				var img_num = i+1;
				var imgId = myJSONimages[i][0];
				var imgName = myJSONimages[i][1];
				tabElements += "<tr onclick='imgEvent(this.cells)'><td>" + img_num + ". </td><td>" + imgName + "</td><td><img src='./phrase_images_related/image" + imgId + ".jpg'></td></tr>"; 
			}
			document.getElementById("tabImages").innerHTML = tabElements;
		}
		
		function buttEvent(){

			if(document.getElementById("dropPanel").style.display === "none"){
				document.getElementById("dropPanel").style.display = "block";
			}
			else{
				document.getElementById("dropPanel").style.display = "none";
			}
		}

		function imgEvent(cells){
			
			var imgName = cells[1].innerHTML;
			document.getElementById("img_name").value = imgName;
			//alert("Ju zgjodhet imazhin me Emrin: " + imgName);
			document.getElementById("dropPanel").style.display = "none";
			document.getElementById("drop_img").innerHTML = imgName;
		}

		
		var visible = function(el) {

			var rect = el.getBoundingClientRect(), top = rect.top, height = rect.height,
			el = el.parentNode;
			rect = el.getBoundingClientRect();
			if (top <= rect.bottom === false) return false;
			if ((top + height) <= rect.top) return false;

			//return top < document.documentElement.clientHeight;
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
			//alert(ShClickEvent + "\n" + block_id);

			if (eventObjId == 'a'){
				console.log("inside: " + eventObjId);
				//ShClickEvent = false;
				//alert(ShClickEvent + "\n" + block_id);
				for(i=1; i<=myJSONphrases.length; i++){
					shphr_id = 'sh_phr' + i;
					tphr_id = 't_phr' + i;
					aphr_id = 'a_phr' + i;

					if (visible(document.getElementById(aphr_id))){
						document.getElementById(shphr_id).scrollIntoView();
						document.getElementById(tphr_id).scrollIntoView();
						break;
					}
				}
			}
			else if (eventObjId == 't'){
				console.log("inside: " + eventObjId);
				//alert(AClickEvent + "\n" + block_id);
				//AClickEvent = false;
				//alert(AClickEvent + "\n" + block_id);

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
				console.log("inside: " + eventObjId);
			    //TClickEvent = false;

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

			var n         = phr_id.search(/\d/);
			var id_num    = phr_id.substring(n);
			var shqip     = myJSONphrases[id_num-1][0];
			var anglisht  = myJSONphrases[id_num-1][1];
			var turqisht  = myJSONphrases[id_num-1][2];
			var kategoria = myJSONphrases[id_num-1][3];
			var imazhi    = (myJSONphrases[id_num-1][4]) ? myJSONphrases[id_num-1][4]:"S'ka imazhe";

			var table = document.getElementById("infoOnClick");

			var row = table.insertRow(0);

			var cell1 = row.insertCell(0);
			var cell2 = row.insertCell(1);
			var cell3 = row.insertCell(2);
			var cell4 = row.insertCell(3);
			var cell5 = row.insertCell(4);

			cell1.innerHTML = "Shqip";
			cell2.innerHTML = "Turqisht";
			cell3.innerHTML = "Anglisht";
			cell4.innerHTML = "Kategoria";
			cell5.innerHTML = "Imazhi";

			row = table.insertRow(1);

			cell1 = row.insertCell(0);
			cell2 = row.insertCell(1);
			cell3 = row.insertCell(2);
			cell4 = row.insertCell(3);
			cell5 = row.insertCell(4);

			cell1.innerHTML = shqip;
			cell2.innerHTML = anglisht;
			cell3.innerHTML = turqisht;
			cell4.innerHTML = kategoria;
			cell5.innerHTML = imazhi;

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
			document.getElementById("infoOnClick").deleteRow(0);
			document.getElementById("infoOnClick").deleteRow(0);
		}
	</script>
</body>
</html>
