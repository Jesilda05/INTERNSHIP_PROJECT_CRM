<?php
session_start();

// Destroy the session
session_unset();
session_destroy();

// Redirect to registration page
header('Location: register.php');
exit();
?>
