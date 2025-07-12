<?php
require_once 'classes/Auth.php';

$auth = new Auth();
$auth->logout();

header('Location: index.php?message=logged_out');
exit();
?> 