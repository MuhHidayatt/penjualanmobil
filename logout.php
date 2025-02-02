<?php
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();
session_unset();
session_reset();

// Redirect to login page
header("Location: login.php");
exit();
?>
