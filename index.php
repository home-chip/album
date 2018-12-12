

<html>

<head>
	<title>Site Web</title>
</head>

<body>

Voici la liste des photos trouvées sur le nas :

<?php


	if ($handle = opendir('/media')) {

    	while (false !== ($entry = readdir($handle))) {

	        if ($entry != "." && $entry != "..") {

	            echo "$entry\n";
	        }
	    }

	    closedir($handle);
}

?>

</body>

</html>
