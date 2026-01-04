<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if logged in
if (!isLoggedIn()) {
    redirectToLogin();
}

// Validate session
if (!validateSession($_SESSION['user_id'])) {
    session_destroy();
    redirectToLogin();
}

$user = getUserData($_SESSION['user_id']);

// Get order ID
$orderId = intval($_GET['order_id'] ?? 0);
if ($orderId <= 0) {
    redirectToDashboard();
}

// Get order details
global $db;
$stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $orderId, $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    $_SESSION['error_message'] = 'Order not found';
    redirectToDashboard();
}

// Get payment info
$stmt = $db->prepare("SELECT * FROM payments WHERE order_id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();

if (!$payment || $payment['payment_method'] !== 'upi') {
    $_SESSION['error_message'] = 'Invalid payment information';
    redirectToDashboard();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UPI Payment - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php renderUserNavbar(); ?>

    <!-- Page Header -->
    <section class="hero">
        <div class="container">
            <h1>Complete Payment</h1>
            <p>UPI Payment for Order <?php echo htmlspecialchars($order['order_number']); ?></p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container" style="margin: 3rem auto; max-width: 600px; min-height: 60vh;">
        <div class="card">
            <h2 style="text-align: center;">Payment Details</h2>

            <!-- Order Summary -->
            <div style="background-color: var(--light-gray); padding: 1.5rem; border-radius: 4px; margin-bottom: 2rem; text-align: center;">
                <p style="margin: 0 0 0.5rem 0; color: #6b7280;">Order Amount</p>
                <h2 style="margin: 0; color: var(--primary-color);">
                    <?php echo formatCurrency($order['total_amount']); ?>
                </h2>
                <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; color: #6b7280;">
                    Order: <?php echo htmlspecialchars($order['order_number']); ?>
                </p>
            </div>

            <!-- QR Code Section -->
            <div style="text-align: center; margin-bottom: 2rem;">
                <h3>Scan QR Code to Pay</h3>
                <p style="color: #6b7280; margin-bottom: 1rem;">Use any UPI app to scan and complete the payment</p>
                
                <?php if ($payment['qr_code_url']): ?>
                    <div style="display: inline-block; padding: 1rem; background-color: var(--light-gray); border-radius: 4px;">
                        <img src="<?php echo htmlspecialchars($payment['qr_code_url']); ?>" alt="UPI QR Code" style="width: 300px; height: 300px; object-fit: contain;">
                    </div>
                <?php endif; ?>
            </div>

            <!-- UPI Details -->
            <div style="background-color: #f0f9ff; padding: 1.5rem; border-radius: 4px; margin-bottom: 2rem; border-left: 4px solid var(--primary-color);">
                <h3 style="margin-top: 0;">UPI Payment Details</h3>
                
                <div style="margin-bottom: 1rem;">
                    <label style="font-weight: bold; display: block; margin-bottom: 0.5rem;">UPI ID</label>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="text" value="<?php echo htmlspecialchars($payment['upi_id']); ?>" readonly style="flex: 1; padding: 0.5rem; border: 1px solid var(--light-gray); border-radius: 4px; background-color: white;">
                        <button type="button" onclick="copyToClipboard('<?php echo htmlspecialchars($payment['upi_id']); ?>')" class="btn btn-secondary" style="padding: 0.5rem 1rem;">Copy</button>
                    </div>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="font-weight: bold; display: block; margin-bottom: 0.5rem;">Amount</label>
                    <p style="margin: 0; padding: 0.5rem; background-color: white; border: 1px solid var(--light-gray); border-radius: 4px;">
                        ₹<?php echo number_format($order['total_amount'], 2); ?>
                    </p>
                </div>

                <div>
                    <label style="font-weight: bold; display: block; margin-bottom: 0.5rem;">Reference</label>
                    <p style="margin: 0; padding: 0.5rem; background-color: white; border: 1px solid var(--light-gray); border-radius: 4px; font-family: monospace; font-size: 0.9rem;">
                        <?php echo htmlspecialchars($order['order_number']); ?>
                    </p>
                </div>
            </div>

            <!-- Instructions -->
            <div style="background-color: #fef3c7; padding: 1.5rem; border-radius: 4px; margin-bottom: 2rem; border-left: 4px solid #f59e0b;">
                <h3 style="margin-top: 0;">Payment Instructions</h3>
                <ol style="margin: 0; padding-left: 1.5rem; color: #6b7280;">
                    <li>Open any UPI app (Google Pay, PhonePe, BHIM, etc.)</li>
                    <li>Scan the QR code above OR enter UPI ID</li>
                    <li>Verify the amount and details</li>
                    <li>Complete the payment with your PIN</li>
                    <li>Take a screenshot of the success message</li>
                    <li>Upload the screenshot in the next step</li>
                </ol>
            </div>

            <!-- Next Steps -->
            <div style="display: flex; gap: 1rem;">
                <a href="order_detail.php?id=<?php echo $orderId; ?>" class="btn btn-primary btn-block" style="text-align: center; text-decoration: none;">
                    ✓ I've Completed Payment
                </a>
                <a href="cart.php" class="btn btn-secondary btn-block" style="text-align: center; text-decoration: none;">
                    Back to Cart
                </a>
            </div>

            <p style="text-align: center; margin-top: 1rem; font-size: 0.9rem; color: #6b7280;">
                After payment, you'll be able to upload proof on the order details page
            </p>
        </div>
    </div>

    <!-- Footer -->
    <?php renderFooter(); ?>

    <script>
        function copyToClipboard(text) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            alert('UPI ID copied to clipboard!');
        }
    </script>
</body>
</html>
