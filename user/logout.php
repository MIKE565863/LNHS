<?php
require_once '../includes/auth.php';

// Logout and redirect
$auth->logout();
header('Location: ../index.php?logged_out=1');
exit();
?>