<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if logged in
if (!isLoggedIn()) {
    http_response_code(401);
    header("Location: login.php");
    exit();
}

// Validate session
if (!validateSession($_SESSION['user_id'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$billId = intval($_GET['bill_id'] ?? 0);

if ($billId <= 0) {
    $_SESSION['error_message'] = 'Invalid bill ID';
    header("Location: dashboard.php");
    exit();
}

global $db;

// Get bill and verify it belongs to user
$stmt = $db->prepare("SELECT b.*, o.user_id FROM bills b JOIN orders o ON b.order_id = o.id WHERE b.id = ? AND o.user_id = ?");
$stmt->bind_param("ii", $billId, $_SESSION['user_id']);
$stmt->execute();
$bill = $stmt->get_result()->fetch_assoc();

if (!$bill) {
    $_SESSION['error_message'] = 'Bill not found or access denied';
    header("Location: dashboard.php");
    exit();
}

// Check if bill file exists
$billFile = '../' . htmlspecialchars_decode($bill['bill_file_path']);

if (!file_exists($billFile)) {
    $_SESSION['error_message'] = 'Bill file not found';
    header("Location: dashboard.php");
    exit();
}

// Send file for download
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . basename($billFile) . '"');
header('Content-Length: ' . filesize($billFile));
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

readfile($billFile);
exit();
?>
