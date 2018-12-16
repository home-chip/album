<?php

function create_thumbnail($sTempFileName) {
		
		$oTempFile = fopen($sTempFileName, "r"); 
		$sBinaryPhoto = fread($oTempFile, fileSize($sTempFileName));
		// Try to read image 
		$nOldErrorReporting = error_reporting(E_ALL & ~(E_WARNING)); // ingore warnings 
		$oSourceImage = imagecreatefromstring($sBinaryPhoto); // try to create image error_reporting($nOldErrorReporting); 
		if (!$oSourceImage) // error, image is not a valid jpg 
		{ 
			return ""; 
		}

		$nWidth = imagesx($oSourceImage); // get original source image width 
		$nHeight = imagesy($oSourceImage); // and height // create small thumbnail 
	   	
		$nDestinationHeight = 120;
	   	$nDestinationWidth = $nDestinationHeight*($nWidth/$nHeight); 
		//$oDestinationImage = imagecreatetruecolor($nDestinationWidth, $nDestinationHeight); 
		$oDestinationImage = imagecreatetruecolor($nDestinationWidth, $nDestinationHeight); 
		/*$oResult = imagecopyresampled( $oDestinationImage, $oSourceImage, 0, 0, 0, 0, $nDestinationWidth, $nDestinationHeight, $nWidth, $nHeight); // resize the image */ 
		imagecopyresized( $oDestinationImage, $oSourceImage, 0, 0, 0, 0, $nDestinationWidth, $nDestinationHeight, $nWidth, $nHeight); // resize the image 
		ob_start(); // Start capturing stdout. 
		imageJPEG($oDestinationImage); // As though output to browser. 
		$sBinaryThumbnail = ob_get_contents();
		ob_end_clean(); // the raw jpeg image data. 
		return addslashes($sBinaryThumbnail);	
}

function file_exists_in_db($path) {

	$mysqli = new mysqli("localhost", "album_user", "USER.Album1","album");
    if ($mysqli -> connect_errno)
    {
    	printf("Error: %s\n", $mysqli->connect_error);
       	exit();
    }
    $sql_statement = "select id from items where path = '$path'"; 
	$result = $mysqli -> prepare($sql_statement);
    $result -> execute();
    $result -> store_result();
	
    if ($result -> num_rows >= 1)
    {
    	$result -> close();
	    $mysqli -> close();
	
        return true;  
    }else
    {
          $result -> close();
    }
    $mysqli -> close();
	return false;
}

function insert_to_db($name, $path, $icon, $create_date, $description) {
	
   	$mysqli = new mysqli("localhost", "album_user", "USER.Album1","album");
    if ($mysqli -> connect_errno)
    {
    	printf("Error: %s\n", $mysqli->connect_error);
       	exit();
    }
    if (is_null($create_date )) {
    	$sql_statement  = "insert into items(name, path, icon, description) values ('$name', '$path', '$icon', '$description')"; 
    }
    else {
    	$sql_statement  = "insert into items(name, path, icon, create_date, description) values ('$name', '$path', '$icon', '$create_date', '$description')"; 	
    }
    $result = $mysqli -> prepare($sql_statement);
    if (!$result -> execute()) {
    	printf("Error: %s\n", $result->error);
       	exit();
    }
    $result -> close();
    $mysqli -> close();
}

function insert_file_to_db($filename) {

	if (file_exists($filename)) {
		$exif = exif_read_data($filename, 0, true);
		//print_r ($exif);
		$create_date 	= $exif['EXIF']['DateTimeOriginal'];
		$name 			= $exif['FILE']['FileName'];
		$description	= "";
		$icon 			= create_thumbnail($filename);
		if ($icon != "") {
			insert_to_db($name, $filename, $icon, $create_date, $description);			
		}
	}
}

function save_files_to_db_by_Id($sBinaryThumbnail,$nId) {

	$oDatabase = mysqli_connect("localhost", "album_user", "USER.Album1","album"); 
	$sQuery = "UPDATE items SET icon = '$sBinaryThumbnail' WHERE id = '$nId'";
	mysqli_query($sQuery, $oDatabase);

}

function insert_files_to_db($dir)
{
	if (is_dir($dir) && $handle = opendir($dir)) {
	
		while (false !== ($entry = readdir($handle))) {

	        if ($entry != "." && $entry != "..") {
	        	 if (insert_files_to_db($dir."/".$entry)) {
	        	 }
	        	 else if (!is_dir($dir."/".$entry) && !file_exists_in_db($dir."/".$entry)) {
	        	 	insert_file_to_db($dir."/".$entry);
				 }
	        }
	    }

	    closedir($handle);
		return true;
	}
	return false; 
}

function list_files()
{
	
	$mysql = mysqli_connect("localhost", "album_user", "USER.Album1","album") or die("Could not Connect.");
    $sQuery = "select * from items order by create_date";

	if ($sql = mysqli_query($mysql,$sQuery)) {
		    
	    if($sql){
	    	echo '<table>';
	        echo '<tr>';
	        $nb = 0;
	        while($row = mysqli_fetch_array($sql)){
	        	if ($nb > 10) {
	        		echo '</tr>';
	        		echo '<tr>';	
	        		$nb = 0;
	        	}
	        	$nb++;
	        	echo '<td>';
	        	$uri = "data:image/jpeg;base64," . base64_encode($row['icon']);
				echo "<a href=\"thumbnail.php?id=".$row['ID']."\"><img src=\"".$uri."\" alt=\"".$row['path']."\" ></a>";
     			echo '</td>';	
	        		
	        }
	        echo '</tr>';
	    	echo '</table>';
		}
	}
}

?>


<html>
<meta charset="UTF-8"> 

<head>
	<title>Site Web</title>
</head>

<body>

Voici la liste des photos trouvées sur le nas :

<pre>
<?php	
	insert_files_to_db("./nas");	
	list_files();	


?>
</pre>

</body>

</html>