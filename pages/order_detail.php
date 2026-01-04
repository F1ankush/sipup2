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
$csrf_token = generateCSRFToken();

// Get order ID
$orderId = intval($_GET['id'] ?? 0);
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

// Get order items
$stmt = $db->prepare("SELECT oi.*, p.name, p.image_path FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get payment info
$stmt = $db->prepare("SELECT * FROM payments WHERE order_id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();

// Get bill info if generated
$stmt = $db->prepare("SELECT * FROM bills WHERE order_id = ? LIMIT 1");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$bill = $stmt->get_result()->fetch_assoc();

$success_message = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php renderUserNavbar(); ?>

    <!-- Page Header -->
    <section class="hero">
        <div class="container">
            <h1>Order Details</h1>
            <p>Track your order status</p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container" style="margin: 3rem auto;">
        
        <?php if ($success_message): ?>
            <div class="alert alert-success" style="margin-bottom: 2rem;">
                <strong>Success!</strong> <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Order Info -->
            <div class="col-8">
                <!-- Status Timeline -->
                <div class="card" style="margin-bottom: 2rem;">
                    <h2 style="margin-top: 0;">Order Status</h2>
                    
                    <div style="display: flex; justify-content: space-between; margin-bottom: 2rem;">
                        <?php
                            $statuses = ['pending_payment' => 'Pending Payment', 'payment_verified' => 'Payment Verified', 'bill_generated' => 'Bill Generated', 'completed' => 'Completed'];
                            $currentStatusIndex = array_search($order['status'], array_keys($statuses));
                            $statusKeys = array_keys($statuses);
                        ?>
                        
                        <?php foreach ($statuses as $key => $label): ?>
                            <?php $index = array_search($key, $statusKeys); ?>
                            <div style="flex: 1; text-align: center;">
                                <div style="display: flex; justify-content: center; margin-bottom: 0.5rem;">
                                    <div style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; background-color: <?php echo $index <= $currentStatusIndex ? 'var(--success-color)' : 'var(--light-gray)'; ?>; color: white;">
                                        <?php echo $index + 1; ?>
                                    </div>
                                </div>
                                <p style="margin: 0; font-size: 0.9rem; color: <?php echo $index <= $currentStatusIndex ? 'var(--success-color)' : '#6b7280'; ?>;">
                                    <?php echo $label; ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="card" style="margin-bottom: 2rem;">
                    <h2 style="margin-top: 0;">Order Items</h2>
                    
                    <div style="overflow-x: auto;">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th style="width: 40%;">Product</th>
                                    <th style="width: 15%;">Price</th>
                                    <th style="width: 15%;">Quantity</th>
                                    <th style="width: 15%;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                <?php if (!empty($item['image_path'])): ?>
                                                    <img src="../<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                                                <?php else: ?>
                                                    <div style="width: 40px; height: 40px; background-color: var(--light-gray); border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 1rem;">📦</div>
                                                <?php endif; ?>
                                                <span><?php echo htmlspecialchars($item['name']); ?></span>
                                            </div>
                                        </td>
                                        <td><?php echo formatCurrency($item['unit_price']); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td><?php echo formatCurrency($item['total_price']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Payment Info -->
                <?php if ($payment): ?>
                    <div class="card" style="margin-bottom: 2rem;">
                        <h2 style="margin-top: 0;">Payment Information</h2>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div>
                                <label style="font-weight: bold; display: block; margin-bottom: 0.5rem;">Payment Method</label>
                                <p style="margin: 0;"><?php echo strtoupper($payment['payment_method']); ?></p>
                            </div>
                            <div>
                                <label style="font-weight: bold; display: block; margin-bottom: 0.5rem;">Payment Status</label>
                                <p style="margin: 0;">
                                    <span style="background-color: <?php 
                                        if ($payment['status'] === 'verified') echo 'var(--success-color)';
                                        elseif ($payment['status'] === 'rejected') echo 'var(--danger-color)';
                                        else echo 'var(--warning-color)';
                                    ?>; color: white; padding: 0.3rem 0.8rem; border-radius: 4px; font-size: 0.85rem;">
                                        <?php echo ucfirst($payment['status']); ?>
                                    </span>
                                </p>
                            </div>
                            <div>
                                <label style="font-weight: bold; display: block; margin-bottom: 0.5rem;">Amount</label>
                                <p style="margin: 0;"><?php echo formatCurrency($payment['amount']); ?></p>
                            </div>
                        </div>

                        <?php if ($payment['payment_method'] === 'upi' && $payment['status'] === 'pending' && $payment['qr_code_url']): ?>
                            <div style="text-align: center; margin-top: 1.5rem;">
                                <h3>Scan QR Code to Pay</h3>
                                <img src="<?php echo htmlspecialchars($payment['qr_code_url']); ?>" alt="UPI QR Code" style="max-width: 300px; margin-bottom: 1rem;">
                                <p style="color: #6b7280;">UPI ID: <?php echo htmlspecialchars($payment['upi_id']); ?></p>
                                <p style="font-size: 0.9rem; color: #6b7280;">After payment, upload the proof below</p>
                            </div>

                            <!-- Payment Proof Upload -->
                            <div style="margin-top: 1.5rem; background-color: var(--light-gray); padding: 1rem; border-radius: 4px;">
                                <h3 style="margin-top: 0;">Upload Payment Proof</h3>
                                <form enctype="multipart/form-data" action="upload_payment_proof.php" method="POST">
                                    <input type="hidden" name="order_id" value="<?php echo $orderId; ?>">
                                    <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    
                                    <div class="form-group">
                                        <label for="proof">Payment Proof Image (JPG/PNG, Max 5MB)</label>
                                        <input type="file" id="proof" name="proof" class="form-control" accept="image/jpeg,image/png" required>
                                        <small>Upload a screenshot of the payment confirmation</small>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">Upload Proof & Submit</button>
                                </form>
                            </div>
                        <?php endif; ?>

                        <?php if ($payment['payment_method'] === 'cod' && $order['status'] !== 'completed'): ?>
                            <div style="margin-top: 1.5rem; background-color: #ecfdf5; padding: 1rem; border-radius: 4px; border-left: 4px solid var(--success-color);">
                                <h3 style="margin-top: 0; color: var(--success-color);">✓ COD Order</h3>
                                <p style="margin: 0; color: #6b7280;">Your order will be delivered and you can pay the amount on delivery. No payment proof required.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Order Summary -->
            <div class="col-4">
                <!-- Order Details Card -->
                <div class="card" style="margin-bottom: 2rem; position: sticky; top: 2rem;">
                    <h3 style="margin-top: 0;">Order Details</h3>
                    
                    <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--light-gray);">
                        <label style="font-weight: bold; display: block; margin-bottom: 0.3rem;">Order Number</label>
                        <p style="margin: 0; font-family: monospace; background-color: var(--light-gray); padding: 0.5rem; border-radius: 4px;">
                            <?php echo htmlspecialchars($order['order_number']); ?>
                        </p>
                    </div>

                    <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--light-gray);">
                        <label style="font-weight: bold; display: block; margin-bottom: 0.3rem;">Order Date</label>
                        <p style="margin: 0;"><?php echo date('d M Y, h:i A', strtotime($order['order_date'])); ?></p>
                    </div>

                    <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--light-gray);">
                        <label style="font-weight: bold; display: block; margin-bottom: 0.3rem;">Payment Method</label>
                        <p style="margin: 0;"><?php echo strtoupper($order['payment_method']); ?></p>
                    </div>

                    <div style="background-color: var(--light-gray); padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
                        <label style="font-weight: bold; display: block; margin-bottom: 0.5rem;">Order Total</label>
                        <p style="margin: 0; font-size: 1.3rem; color: var(--primary-color);">
                            <?php echo formatCurrency($order['total_amount']); ?>
                        </p>
                    </div>

                    <!-- Bill Download -->
                    <?php if ($bill): ?>
                        <div style="margin-top: 1rem;">
                            <a href="download_bill.php?bill_id=<?php echo $bill['id']; ?>" class="btn btn-primary btn-block" style="text-align: center; text-decoration: none;">
                                📄 Download Bill (PDF)
                            </a>
                        </div>
                    <?php endif; ?>

                    <!-- Delivery Address -->
                    <div style="margin-top: 1.5rem; background-color: #f0f9ff; padding: 1rem; border-radius: 4px;">
                        <h4 style="margin-top: 0;">Delivery Address</h4>
                        <p style="margin: 0; font-size: 0.9rem; line-height: 1.5;">
                            <?php echo htmlspecialchars($user['shop_address']); ?><br>
                            <small>Phone: <?php echo htmlspecialchars($user['phone']); ?></small>
                        </p>
                    </div>
                </div>

                <!-- Back Button -->
                <a href="orders.php" class="btn btn-secondary btn-block" style="text-align: center; text-decoration: none;">
                    ← Back to Orders
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php renderFooter(); ?>

    <script src="../assets/js/main.js"></script>
</body>
</html>
