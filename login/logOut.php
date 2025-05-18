<?php
session_start();
session_destroy();
header("Location: view/loginView.php");
exit();
?>
