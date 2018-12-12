

<html>

<?php

function list_files($dir)
{
    if ($handle = opendir($dir)) {
 		echo "Directory : $dir\n";
		while (false !== ($entry = readdir($handle))) {

	        if ($entry != "." && $entry != "..") {
	        	 if (list_files($dir."/".$entry)) {
	        	 }
	        	 else {
		 		     echo "	file : $entry\n";
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
