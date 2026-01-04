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

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = intval($_POST['order_id'] ?? 0);
    $paymentId = intval($_POST['payment_id'] ?? 0);
    
    if ($orderId <= 0 || $paymentId <= 0) {
        $error_message = 'Invalid order or payment ID';
    } else {
        // Verify order belongs to user
        global $db;
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $orderId, $_SESSION['user_id']);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        
        if (!$order) {
            $error_message = 'Order not found';
        } elseif (empty($_FILES['proof']['name'])) {
            $error_message = 'No file uploaded';
        } else {
            // Validate file
            $uploadResult = validateFileUpload($_FILES['proof']);
            
            if (!$uploadResult['success']) {
                $error_message = $uploadResult['message'];
            } else {
                // Create upload directory
                $uploadDir = '../uploads/payment_proofs/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                // Generate unique filename
                $fileName = 'proof_' . $orderId . '_' . time() . '_' . basename($_FILES['proof']['name']);
                $filePath = $uploadDir . $fileName;
                $relativePath = 'uploads/payment_proofs/' . $fileName;
                
                if (move_uploaded_file($_FILES['proof']['tmp_name'], $filePath)) {
                    // Update payment record
                    $stmt = $db->prepare("UPDATE payments SET payment_proof_path = ?, status = 'pending' WHERE id = ?");
                    $stmt->bind_param("si", $relativePath, $paymentId);
                    
                    if ($stmt->execute()) {
                        // Update order status
                        $stmt = $db->prepare("UPDATE orders SET status = 'pending_payment' WHERE id = ?");
                        $stmt->bind_param("i", $orderId);
                        $stmt->execute();
                        
                        $_SESSION['success_message'] = 'Payment proof uploaded successfully! Admin will verify it soon.';
                        header("Location: order_detail.php?id=" . $orderId);
                        exit();
                    } else {
                        $error_message = 'Failed to update payment information';
                        // Delete uploaded file if database update fails
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                    }
                } else {
                    $error_message = 'Failed to upload file';
                }
            }
        }
    }
    
    if ($error_message) {
        $_SESSION['error_message'] = $error_message;
        header("Location: order_detail.php?id=" . $orderId);
        exit();
    }
} else {
    header("Location: dashboard.php");
    exit();
}
?>
