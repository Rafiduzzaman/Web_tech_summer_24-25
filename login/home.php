<?php
session_start();
  if(isset($_SESSION['status']))
  {

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Home Page</title>
</head>
<body>
    <h1>Home Page</h1>
    <form method="post" action="logOut.php">
    	<input type="submit" name="logOut" value="Logout">
    </form>
</body>
</html>
<?php

  }
  else
  {
  	header('location:login.php');
  }
	?>