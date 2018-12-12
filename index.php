

<html>

<?php

function list_files($dir,$nb)
{
    if ($handle = opendir($dir)) {
		while (false !== ($entry = readdir($handle))) {

	        if ($entry != "." && $entry != "..") {
	        	 if (list_files($dir."/".$entry,$nb)) {
	        	 }
	        	 else {
				if ($nb >= 10) {
					echo "</tr><tr>";
					$nb = 0;
				}
		 	 	echo "<td><img src=".$dir."/".$entry." style=\"height:150px;\"></td>";
			 	$nb++;	
	        	 }
	        }
	    }

	    closedir($handle);
		return true;
	}
	return false; 
}

?>


<head>
	<title>Site Web</title>
</head>

<body>

Voici la liste des photos trouvées sur le nas :

	<table>
	<tr>
<?php	
	list_files("./nas");	
?>
	</tr>
	</table>


</body>

</html>
