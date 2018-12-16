<html>
<body>
<?php 
 
 	$mysql = mysqli_connect("localhost", "album_user", "USER.Album1","album") or die("Could not Connect.");
	$sQuery = "SELECT path FROM items WHERE id = ".$_GET['id']; 
 	$sql = mysqli_query($mysql,$sQuery);
 	$row = mysqli_fetch_array($sql);
 	echo "<img src=\"".$row['path']."\" >"

?>
</body>
</html>