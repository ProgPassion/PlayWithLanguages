<?php
	
	require_once 'db_connection.php';
	require_once 'functions.php';

	$phrases    = null;
	$kategoriId = null;
	$imageId    = null; 

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
		$result = queryExecute("SELECT id FROM images WHERE name='$imgName'");
		$row = $result->fetch_array(MYSQLI_NUM);
		$imageId = $row[0];
	}

	$jsonValue = linkingTableInfo($phrases, $kategoriId, $imageId);  //The main variabel that holds all info for displaying in result front end!!!

?>
<!DOCTYPE html>
<html>
<head>
	<title>User</title>
	<link href="https://fonts.googleapis.com/css?family=Roboto|Lato" rel="stylesheet"> 
	<link rel="stylesheet" type="text/css" href="./css/style.css">
	<style type="text/css">
		html, .wrap {
			min-height: 650px;
		}
		.optional {
			width: 66.6666666%;
			padding: 10px 80px 10px 10px;
		}
		.explain {
			line-height: 32px;
		}
		#infoOnClick tr:nth-child(2) {
			background-color: #f2f2f2;
			color: #000;
		}
	</style>
</head>
<body>
<!--<?php //echo $jsonValue; ?>-->
	<div class="wrap">
			<div class="wrapper">
				<div class="first-line-ttl"><h1 class="tlt">User Panel</h1></div><div class="first-line-bck"><a href=".">Shko Prapa</a></div>
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
