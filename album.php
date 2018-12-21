<?php

//Check secure
if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off"){
    $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $redirect);
    exit();
}

// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}


// Check rights
function nbit($number, $n) { return ($number >> $n-1) & 1;}
$view_pictures			= 0;
$view_details			= 0;
$control				= 0;

if(isset($_SESSION["rights"])) {

	$view_pictures 	= nbit($_SESSION["rights"],2);
	$view_details 	= nbit($_SESSION["rights"],3);
	$control 		= nbit($_SESSION["rights"],4);
}

// Functions album
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

function insert_to_db($name, $path, $icon, $create_date, $description, $file_date) {
	
   	$mysqli = new mysqli("localhost", "album_user", "USER.Album1","album");
    if ($mysqli -> connect_errno)
    {
    	printf("Error: %s\n", $mysqli->connect_error);
       	exit();
    }
    if (is_null($create_date )) {
    	$sql_statement  = "insert into items(name, path, icon, create_date, description) values ('$name', '$path', '$icon', from_unixtime($file_date) , '$description')"; 
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
		$create_date 	= $exif['EXIF']['DateTimeOriginal'];
		$name 			= $exif['FILE']['FileName'];
		$file_date		= $exif['FILE']['FileDateTime'];
		$description	= "";
		$icon 			= create_thumbnail($filename);
		if ($icon != "") {
			insert_to_db($name, $filename, $icon, $create_date, $description, $file_date);			
		}
	}
}

function save_files_to_db_by_Id($sBinaryThumbnail,$nId) {
	echo "test";
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
    
    $sQuery = "select * from items";

	if (isset($_GET['sort'])) {
		if ($_GET['sort'] == "date") {
	    	$sQuery .= " order by create_date";
	    }
    }
    
	if ($sql = mysqli_query($mysql,$sQuery)) {
		    
	    if($sql){
	    	echo '<table><tr>';
	    	$row_img = '';
	        $row_date = '';
	        $nb = 0;
	        while($row = mysqli_fetch_array($sql)){
	        	if ($nb > 10) {
	        		echo $row_img;
	        		echo '</tr>';
	        		echo '<tr>';
	        		echo $row_date;
	        		echo '</tr>';
	        		echo '<tr>';
	        		$row_img = '';
	        		$row_date = '';
	        		$nb = 0;
	        	}
	        	$nb++;

	        	if ($view_details == 1) {
					$uri = "data:image/jpeg;base64," . base64_encode($row['icon']);
	        		$row_img .= '<td><a href="thumbnail.php?id='.$row['ID'].'"><img src="'.$uri.'" alt="'.$row['path'].'"></a></td>';	
	        		$row_date .= '<td>'.$row['create_date'].'</td>';	
			    }
			    else {
					$uri = "data:image/jpeg;base64," . base64_encode($row['icon']);
	        		$row_img .= '<td><img src="'.$uri.'" alt="'.$row['path'].'"></td>';	
	        		$row_date .= '<td>'.$row['create_date'].'</td>';	
			    
			    }
	        	
	        }
	        if ($nb > 0) {
		        echo $row_img;
		        echo '</tr>';
		        echo '<tr>';
		        echo $row_date;
	        }
	        echo '</tr>';
	    	echo '</table>';
		}
	}
}

?>

<!DOCTYPE html>
<html>
	<meta charset="UTF-8"> 
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <style type="text/css">
        body{ font: 14px sans-serif; text-align: center; }
    </style>
<head>
	<title>Site Web</title>
</head>

<body>
	<div class="page-header">
        <h1>Album</h1>
   	</div>
    
	<div class="page-menu">
		<a href="welcome.php" class="btn btn-default">Welcome</a>
		<a href="album.php" class="btn btn-default">Album</a>
		<a href="users_management.php" class="btn btn-default">Users</a>
	</div>
	<table>
		<tr>
			<td>
				<input type="button" onclick="location.href='./album.php?update';" value="Update" />
			</td>
			<td>
				<input type="button" onclick="location.href='./album.php?sort=date';" value="Order by date" />
			</td>
		</tr>
	</table>

	<?php	
		
		if ($control == 1) {
			if (isset($_GET['update'])) {
				insert_files_to_db("./nas");		
			}
		}
		else {
			echo "You don't have the right to update.";		
		}

		if ($view_pictures == 1) {
			list_files();	
		}
		else {
			echo "You don't have the right to view.";
		}
			
	?>

	<p>
	        <a href="reset-password.php" class="btn btn-warning">Reset Your Password</a>
	        <a href="logout.php" class="btn btn-danger">Sign Out of Your Account</a>
	</p>
</body>

</html>