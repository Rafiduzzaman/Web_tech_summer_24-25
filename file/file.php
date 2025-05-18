<?php

  if(isset($_POST['upload']))
  {
  	$src=$_FILES['my_file']['tmp_name'];
  	$des="C:/xampp/htdocs/file/".$_FILES['my_file']['name'];
  	if(move_uploaded_file($src, $des))
  	{
  		echo "uploaded";
  	}
  	else
  	{
  		echo"Error";
  	}
  }

?>
<!DOCTYPE html>
<html>
<head>
	
	<title></title>
</head>
<body>
     <form method="post" enctype="multipart/form-data">
	     <input type="file" name="my_file">
	     <input type="submit" name="upload">
     </form>

</body>
</html>