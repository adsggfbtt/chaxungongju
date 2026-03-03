<?php
require __DIR__ . '/../lib/bootstrap.php';
$_SESSION = [];
session_destroy();
header('Location: ' . BASE_URL . '/admin/login.php');
