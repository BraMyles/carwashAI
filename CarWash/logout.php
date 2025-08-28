<?php
require_once 'includes/init.php';

// Logout user
$auth->logout();

// Redirect to homepage
header("Location: index.php");
exit();
?>

