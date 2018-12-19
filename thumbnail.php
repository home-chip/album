<?php

// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
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
    	<?php 
		    echo '<h1>Photo with id '.$_GET['id'].'</h1>'
   		?>
   	</div>
    
	<div class="page-menu">
		<a href="welcome.php" class="btn btn-default">Welcome</a>
		<a href="album.php" class="btn btn-default">Album</a>
	</div>

	<?php 
	 
	 	$mysql = mysqli_connect("localhost", "album_user", "USER.Album1","album") or die("Could not Connect.");
		$sQuery = "SELECT path FROM items WHERE id = ".$_GET['id']; 
	 	$sql = mysqli_query($mysql,$sQuery);
	 	$row = mysqli_fetch_array($sql);
	 	echo "<img src=\"".$row['path']."\" >";
	 	$exif = exif_read_data($row['path'], 0, true);
	 	
	 	echo '<pre>';

		print_r ($exif);

	 	echo '</pre>';		

	?>
</body>
</html>