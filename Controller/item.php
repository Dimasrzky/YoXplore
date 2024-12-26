<?php
session_start();
require_once '../Config/db_connect.php';
require_once '../Controllers/get_item_detail.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Validate item ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: home.php');
    exit;
}

$itemId = $_GET['id'];
$itemController = new ItemController($conn);
$data = $itemController->getItem($itemId);

if (!$data) {
    header('Location: home.php?error=item_not_found');
    exit;
}

// Format waktu
date_default_timezone_set('Asia/Jakarta');
$openingHours = !empty($data['item']['opening_hours']) ? 
    date('H:i', strtotime($data['item']['opening_hours'])) : '-';
$closingHours = !empty($data['item']['closing_hours']) ? 
    date('H:i', strtotime($data['item']['closing_hours'])) : '-';

// Include view
include '../Views/item.view.php';