<?php
require_once 'config.php';

// Destroy session
session_unset();
session_destroy();

// Redirect to landing page (bukan login lagi)
header("Location: index.php");
exit();
?>