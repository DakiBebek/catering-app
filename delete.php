<?php
require_once 'config.php';
require_once 'auth.php';
requireLogin();

$id = (int) ($_GET['id'] ?? 0);
supabaseDelete('caterings', 'id=eq.' . $id);
header('Location: history.php');
exit;