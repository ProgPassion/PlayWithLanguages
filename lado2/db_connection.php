<?php
 	$dbhost = 'localhost';
        $dbname = 'lado';
        $dbuser = 'root';
        $dbpass = '1234';

        $conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
        if ($conn->connect_error) die($conn->connect_error);
?>
