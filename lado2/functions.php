<?php

	function showResult($info) {

		$jsonResultArray = array();
		
		$rows = $info->num_rows;		
		for($i=0; $i<$rows; $i++) {
			$info->data_seek($i);
			$row = $info->fetch_array(MYSQLI_NUM);
			array_push($jsonResultArray, $row);
		}
		
		return $jsonResultArray;
	}

	function queryExecute($query) {
	
		global $conn;
		$result = $conn->query($query);
		if(!$result) die($conn->error);
		
		if (is_object($result)) { 		//This special flag checks to see if the result is object so we can fetch info to return  
									    //to other parts of the program that may use it.			
			return $result;
		}
			

		//This function is used for making the code more short and clear
		//It always return the result and the num of rows that the result has
		//First index is the result array and the second is the rows numer.	
	}

	function listImg() {

		$result = queryExecute("SELECT * FROM images");
		$jsonImagesArray = showResult($result);
		return json_encode($jsonImagesArray);
	}

	function getCategories() {

		$result = queryExecute("SELECT * FROM kategorite");
		$jsonCategArray = showResult($result);
		return json_encode($jsonCategArray);
	}

	function extractPhrasesFromInput($phrases, $query) {

		$result = queryExecute($query);
		return showResult($result);			
	}

	function linkingTableInfo($phrases, $kategoriId, $imageId) {

		$jsonReady = 0;
		$staticQueryString = "SELECT phrase.shqip,phrase.turqisht,phrase.anglisht,kategorite.kategoria,images.name FROM extra_info LEFT JOIN kategorite ON extra_info.kategori_id=kategorite.id LEFT JOIN images ON extra_info.images_id=images.id RIGHT JOIN phrase ON phrase.id=extra_info.phrase_id";


		for($i=0; $i<3; $i++) {
			if(!empty($phrases) && !empty($phrases[$i][1])){
				$language = $phrases[$i][0];
				$input    = $phrases[$i][1];
				break;
			}
		}

		if(!is_null($phrases) && is_null($kategoriId) && is_null($imageId)) {
			$query = "$staticQueryString WHERE phrase.$language LIKE '%$input%'";
			$jsonReady = extractPhrasesFromInput($phrases, $query);
		}

		elseif(!is_null($phrases) && !is_null($kategoriId) && is_null($imageId)) {		
			$query = "$staticQueryString WHERE phrase.$language LIKE '%$input%' AND extra_info.kategori_id=$kategoriId";
			$jsonReady = extractPhrasesFromInput($phrases, $query);
		}	
		
		elseif (!is_null($phrases) && is_null($kategoriId) && !is_null($imageId)) {
			$query = "$staticQueryString WHERE phrase.$language LIKE '%$input%' AND extra_info.images_id=$imageId";
			$jsonReady = extractPhrasesFromInput($phrases, $query);
		}
		
		elseif (!is_null($phrases) && !is_null($kategoriId) && !is_null($imageId)) 	{
			$query = "$staticQueryString WHERE phrase.$language LIKE '%$input%' AND extra_info.kategori_id=$kategoriId AND extra_info.images_id=$imageId";
			$jsonReady = extractPhrasesFromInput($phrases, $query);
		}
		
		elseif (is_null($phrases) && !is_null($kategoriId) && is_null($imageId)) {
			$query = "$staticQueryString WHERE extra_info.kategori_id=$kategoriId";
			$jsonReady = extractPhrasesFromInput($phrases, $query);
		}
	
		elseif (is_null($phrases) && !is_null($kategoriId) && !is_null($imageId)) {
			$query = "$staticQueryString WHERE extra_info.kategori_id=$kategoriId AND extra_info.images_id=$imageId";
			$jsonReady = extractPhrasesFromInput($phrases, $query);
		}
		
		elseif (is_null($phrases) && is_null($kategoriId) && !is_null($imageId)) {
			$query = "$staticQueryString WHERE extra_info.images_id=$imageId";
			$jsonReady = extractPhrasesFromInput($phrases, $query);
		}
		
		return json_encode($jsonReady);
	}

	function imgUpload($upload_file_input) {

		global $phraseId;
		$typeok = TRUE;

		if(isset($_POST['img-name']))
			$tmp_name = $_POST['img-name'];
		else
			$tmp_name = $_POST['img-name_mod'];

		switch ($_FILES[$upload_file_input]['type']) {
			case 'image/jpeg': $name = createImageName($tmp_name, $upload_file_input); $src = imagecreatefromjpeg($name); break;
			case 'image/gif' : $name = createImageName($tmp_name, $upload_file_input); $src = imagecreatefromgif($name); break;
			case 'image/png' : $name = createImageName($tmp_name, $upload_file_input); $src = imagecreatefrompng($name); break;
			default 		 : $typeok = FALSE;	 break;
		}
			
		if($typeok) {
					
			list($w, $h) = getimagesize($name);

			$max = 100;
			$tw  = $w;
			$th  = $h;

			if ($w > $h && $max < $w) {
				$th = $max / $w * $h;
				$tw = $max;
			}
			elseif ($h > $w && $max < $h) {
				$tw = $max / $h * $w;
				$th = $max;
			}
			elseif ($max < $w) {
				$tw = $th = $max;
			}

			$tmp = imagecreatetruecolor($tw, $th);
			imagecopyresampled($tmp, $src, 0, 0, 0, 0, $tw, $th, $w, $h);
			imageconvolution($tmp, array(array(-1, -1, -1), array(-1, 16, -1), array(-1, -1, -1)), 8, 0);
			imagejpeg($tmp, $name);
			imagedestroy($tmp);
			imagedestroy($src);
		}
		else{
			$img_warning = "Imazhi qe upload-uat nuk eshte nje file i pranueshem!\nFormatet e pranueshme jane jpg, png ose giff.\nGjithashtu upload-oni serish informacioni per frazat e sapo futura ose kategorine e tyre.";
			if(!is_null($phraseId)) {queryExecute("DELETE FROM phrase WHERE id=$phraseId"); $phraseId = null;}
		}
	}

	function createImageName($tmp_name, $name_file_input) {

		global $imageId;

		$imgNickname = addslashes($tmp_name);
		queryExecute("INSERT INTO images(name) VALUES('$imgNickname')");
		
		$result  = queryExecute("SELECT id FROM images ORDER BY id DESC LIMIT 1");
		$row     = $result->fetch_array(MYSQLI_NUM); 
		$imageId = $row[0];
		$name    = "phrase_images_related/image$imageId.jpg";
		
		move_uploaded_file($_FILES[$name_file_input]['tmp_name'], $name);
		return $name;
	}
	
	
	function createNewCategori($name, $n_c_n_ph){
		queryExecute("INSERT INTO kategorite(kategoria) VALUES('$name')");
		if($n_c_n_ph){
			$result = queryExecute("SELECT id FROM kategorite ORDER BY id DESC LIMIT 1");
			$row    = $result->fetch_array(MYSQLI_NUM);
			return $row[0];
		}
	}
	
	function link_tables($phraseId, $categoryId, $imageId) {
		
		global $conn;

		$stmt = $conn->prepare("INSERT INTO extra_info(phrase_id, kategori_id, images_id) VALUES(?, ?, ?)");
		$stmt->bind_param('sss', $phraseId, $categoryId, $imageId);
		$stmt->execute();
	} 


	function linkingTableModify($phrases) {

		$staticQueryString = "SELECT phrase.id,phrase.shqip,phrase.turqisht,phrase.anglisht,kategorite.kategoria,images.name FROM extra_info LEFT JOIN kategorite ON extra_info.kategori_id=kategorite.id LEFT JOIN images ON extra_info.images_id=images.id RIGHT JOIN phrase ON phrase.id=extra_info.phrase_id";

		for($i=0; $i<3; $i++) {
			if(!empty($phrases) && !empty($phrases[$i][1])){
				$language = $phrases[$i][0];
				$input    = $phrases[$i][1];
				break;
			}
		}

		$query = "$staticQueryString WHERE phrase.$language LIKE '%$input%'";
		$jsonReady = extractPhrasesFromInput($phrases, $query);		
		return json_encode($jsonReady);
	}

?>

