<?php
// clear session and log the user out
session_start();
session_destroy();
header("Location: ../pages/login-page.php?success=You have been logged out");
exit;
?>
