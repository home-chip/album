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
$admin				= 0;

if(isset($_SESSION["rights"])) {
	$admin 		= nbit($_SESSION["rights"],32);
}

// Define variables and initialize with empty values
$username = $email = "";
$status = $username_err = $email_err = "";
$rights = 0;

if($_SERVER["REQUEST_METHOD"] == "POST"){
	
	// Include config file
	require_once "config.php";
	 
	// Check if username is empty
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter username.";
    } else{
        $username = trim($_POST["username"]);
   	}
    
	// Validate email
    if(empty(trim($_POST["email"]))){
        $email_err = "Please enter an email.";     
    } elseif(!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)){
        $email_err = "Please enter a valid email.";     
    }
    else {
        $email = trim($_POST["email"]);
    }

	for ($i = 1; $i <= 31; $i++) {
		if (isset($_POST["right".$i]) && ($_POST["right".$i] == 1)) {
			$rights = $rights + pow(2,($i-1));			
		}
	}
	if (isset($_POST["right32"]) && ($_POST["right32"] == 1)) {
		$rights = $rights - pow(2,31);;			
	}

    if(empty($username_err) && empty($email_err)){
		
	    $sql = "SELECT id FROM users WHERE username = ? and id != ".$_POST["id"];
	        
	    if($stmt = mysqli_prepare($link, $sql)){
	    	// Bind variables to the prepared statement as parameters
	        mysqli_stmt_bind_param($stmt, "s", $username);
	        
	        // Attempt to execute the prepared statement
	        if(mysqli_stmt_execute($stmt)){
	        	/* store result */
	            mysqli_stmt_store_result($stmt);
	                
	            if(mysqli_stmt_num_rows($stmt) == 1){
	            	$username_err = "This username is already taken.";
	            }
	         	else {

				    // Close statement
			        mysqli_stmt_close($stmt);

		   			// Prepare a select statement
			        $sql = "UPDATE users set username = ? , email = ? , rights = ? WHERE id = ".$_POST["id"];
			        
			        if($stmt = mysqli_prepare($link, $sql)){
			            // Bind variables to the prepared statement as parameters
			            mysqli_stmt_bind_param($stmt, "ssi", $username, $email, $rights);
			           
			 			if(mysqli_stmt_execute($stmt)){
			                /* store result */
			                mysqli_stmt_store_result($stmt);
			                $status = "Saved.";
		
			            } else{
			                $status = "Oops! Something went wrong. Please try again later.";
			            }
			        	// Close statement
		    		    mysqli_stmt_close($stmt);

					    // Close connection
					    mysqli_close($link);
			 		}
			 	}
			}
	   }
	}
} 


function list_users()
{
	
	$mysql = mysqli_connect("localhost", "album_user", "USER.Album1","album") or die("Could not Connect.");
    
    $sQuery = "select * from users";
    
	if ($sql = mysqli_query($mysql,$sQuery)) {
		    
	    if($sql){

        
	    	echo '<table>';
	    	echo '<tr>';
	    	
	        echo '<td rowspan=2>Update</td>';	
	        echo '<td rowspan=2>username</td>';	
	        echo '<td rowspan=2>email</td>';	
	        
	        echo '<td colspan=32>rights</td>';	
	    	echo '<td rowspan=2>Delete</td>';	
	        echo '</tr>';
	    	echo '<tr>';
	    	for ($i = 1; $i <= 32; $i++) {
				echo '<td>'.$i.'</td>';	
			}
	        
	        echo '</tr>';
	    	echo '<tr>';
	    			
			while($row = mysqli_fetch_array($sql)){
	        
			    	echo '<form action="'.htmlspecialchars($_SERVER["PHP_SELF"]).'" method="post">';
	        		#echo '<td>'.$row['id'].'</td>';	
	        		echo '<td>';
			        echo '<input type="submit" name="id" value="'.$row['id'].'" >';
			        echo '</td>';
	        		echo '<td><INPUT type="text" name="username" value="'.$row['username'].'" ></td>';
					echo '<td><INPUT type="text" name="email" value="'.$row['email'].'" ></td>';
							        		
					for ($i = 1; $i <= 32; $i++) {
						if (nbit($row['rights'],$i) == 1) {
							echo '<td><INPUT type="checkbox" name="right'.$i.'" value="1" checked></td>';
						}
						else {
							echo '<td><INPUT type="checkbox" name="right'.$i.'" value="1" ></td>';
						}
					}
					echo '<td>';
			        	echo '<input type="submit" name="delete" value="'.$row['id'].'" >';
			        echo '</td>';
	        		
		        echo '</tr>';
		    	echo '<tr>';
					
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
        <h1>Users</h1>
   	</div>
    
	<div class="page-menu">
		<a href="welcome.php" class="btn btn-default">Welcome</a>
		<a href="album.php" class="btn btn-default">Album</a>
		<a href="users_management.php" class="btn btn-default">Users</a>
	</div>
	
	<?php	
		
		if ($admin == 1) {
			list_users();	
			if(!empty($username_err)) {
				echo $username_err;
			} 
			if (!empty($email_err)) {
				echo $email_err;
			}
			if (!empty($status)) {
				echo $status;
			}
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