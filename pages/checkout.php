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

// Check if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    $_SESSION['error_message'] = 'Your cart is empty';
    header("Location: cart.php");
    exit();
}

$cart = $_SESSION['cart'];
$total = 0;

foreach ($cart as $item) {
    $total += $item['price'] * $item['quantity'];
}

$gst_amount = $total * (TAX_RATE / 100);
$final_total = $total + $gst_amount;

$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = sanitize($_POST['payment_method'] ?? '');
    
    if (empty($payment_method) || !in_array($payment_method, ['cod', 'upi'])) {
        $error_message = 'Please select a valid payment method';
    } else {
        // Generate order number
        $order_number = 'ORD' . date('YmdHis') . rand(1000, 9999);
        
        global $db;
        
        try {
            // Create order
            $stmt = $db->prepare("INSERT INTO orders (user_id, order_number, total_amount, payment_method, status) VALUES (?, ?, ?, ?, 'pending_payment')");
            $stmt->bind_param("issd", $_SESSION['user_id'], $order_number, $final_total, $payment_method);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to create order: " . $stmt->error);
            }
            
            $order_id = $db->getLastId();
            
            // Add order items
            foreach ($cart as $item) {
                $stmt = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");
                $item_total = $item['price'] * $item['quantity'];
                $stmt->bind_param("iiddd", $order_id, $item['product_id'], $item['quantity'], $item['price'], $item_total);
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to add order items: " . $stmt->error);
                }
            }
            
            // Create payment record
            $upi_id = null;
            $qr_code_url = null;
            
            if ($payment_method === 'upi') {
                // Generate UPI ID
                $upi_id = UPI_ID;
                // Generate QR code URL using a QR code service
                $qr_code_url = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=upi://pay?pa=' . urlencode($upi_id) . '&pn=' . urlencode(SITE_NAME) . '&am=' . $final_total . '&tn=Order%20' . $order_number;
            }
            
            $stmt = $db->prepare("INSERT INTO payments (order_id, payment_method, upi_id, qr_code_url, amount, status) VALUES (?, ?, ?, ?, ?, 'pending')");
            $stmt->bind_param("isdsd", $order_id, $payment_method, $upi_id, $qr_code_url, $final_total);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to create payment record: " . $stmt->error);
            }
            
            // Clear cart
            $_SESSION['cart'] = [];
            
            // Redirect to payment page based on method
            if ($payment_method === 'cod') {
                $_SESSION['success_message'] = 'Order placed successfully!';
                header("Location: order_detail.php?id=" . $order_id);
            } else {
                // Redirect to UPI payment page
                header("Location: payment.php?order_id=" . $order_id);
            }
            exit();
            
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php renderUserNavbar(); ?>

    <!-- Page Header -->
    <section class="hero">
        <div class="container">
            <h1>Checkout</h1>
            <p>Complete your order and proceed to payment</p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container" style="margin: 3rem auto; min-height: 60vh;">
        <div class="row">
            <!-- Order Review -->
            <div class="col-8">
                <div class="card">
                    <h2>Order Review</h2>
                    
                    <!-- Customer Information -->
                    <div style="background-color: var(--light-gray); padding: 1rem; border-radius: 4px; margin-bottom: 2rem;">
                        <h3 style="margin-top: 0;">Delivery Information</h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div>
                                <label style="font-weight: bold; display: block; margin-bottom: 0.5rem;">Name</label>
                                <p style="margin: 0;"><?php echo htmlspecialchars($user['email']); ?></p>
                            </div>
                            <div>
                                <label style="font-weight: bold; display: block; margin-bottom: 0.5rem;">Phone</label>
                                <p style="margin: 0;"><?php echo htmlspecialchars($user['phone']); ?></p>
                            </div>
                            <div style="grid-column: 1 / -1;">
                                <label style="font-weight: bold; display: block; margin-bottom: 0.5rem;">Delivery Address</label>
                                <p style="margin: 0;"><?php echo htmlspecialchars($user['shop_address']); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <h3>Items in Order</h3>
                    <div style="overflow-x: auto; margin-bottom: 2rem;">
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
                                <?php foreach ($cart as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                                        <td><?php echo formatCurrency($item['price']); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td><?php echo formatCurrency($item['price'] * $item['quantity']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Payment Method Selection -->
                    <div style="background-color: #f0f9ff; padding: 2rem; border-radius: 4px; border: 2px solid var(--primary-color);">
                        <h2 style="margin-top: 0;">Select Payment Method</h2>

                        <?php if ($error_message): ?>
                            <div class="alert alert-danger" style="margin-bottom: 1rem;">
                                <strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <!-- COD Option -->
                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: flex; align-items: center; cursor: pointer; padding: 1rem; background-color: white; border: 2px solid var(--light-gray); border-radius: 4px; transition: all 0.3s ease;">
                                    <input type="radio" name="payment_method" value="cod" style="margin-right: 1rem; width: 20px; height: 20px; cursor: pointer;" required>
                                    <div style="flex: 1;">
                                        <h4 style="margin: 0 0 0.5rem 0;">💰 Cash on Delivery (COD)</h4>
                                        <p style="margin: 0; color: #6b7280; font-size: 0.9rem;">Pay when you receive your order. No upfront payment required.</p>
                                    </div>
                                </label>
                            </div>

                            <!-- UPI Option -->
                            <div style="margin-bottom: 2rem;">
                                <label style="display: flex; align-items: center; cursor: pointer; padding: 1rem; background-color: white; border: 2px solid var(--light-gray); border-radius: 4px; transition: all 0.3s ease;">
                                    <input type="radio" name="payment_method" value="upi" style="margin-right: 1rem; width: 20px; height: 20px; cursor: pointer;" required>
                                    <div style="flex: 1;">
                                        <h4 style="margin: 0 0 0.5rem 0;">📱 UPI Payment</h4>
                                        <p style="margin: 0; color: #6b7280; font-size: 0.9rem;">Pay instantly using UPI. You will receive a QR code to scan.</p>
                                    </div>
                                </label>
                            </div>

                            <div style="display: flex; gap: 1rem;">
                                <button type="submit" class="btn btn-primary" style="flex: 1;">
                                    Place Order
                                </button>
                                <a href="cart.php" class="btn btn-secondary" style="flex: 1; text-align: center; text-decoration: none;">
                                    Back to Cart
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-4">
                <div class="card" style="position: sticky; top: 2rem;">
                    <h3 style="margin-top: 0;">Order Summary</h3>
                    
                    <div style="border-bottom: 1px solid var(--light-gray); padding-bottom: 1rem; margin-bottom: 1rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span>Items (<?php echo count($cart); ?>):</span>
                            <span><?php echo formatCurrency($total); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span>Shipping:</span>
                            <span>Free</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Tax (<?php echo TAX_RATE; ?>% GST):</span>
                            <span><?php echo formatCurrency($gst_amount); ?></span>
                        </div>
                    </div>

                    <div style="background-color: var(--light-gray); padding: 1rem; border-radius: 4px;">
                        <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: bold;">
                            <span>Total:</span>
                            <span><?php echo formatCurrency($final_total); ?></span>
                        </div>
                    </div>

                    <div style="margin-top: 1.5rem; padding: 1rem; background-color: #ecfdf5; border-radius: 4px; border-left: 4px solid var(--success-color);">
                        <h4 style="margin-top: 0; color: var(--success-color);">✓ Order Info</h4>
                        <ul style="margin: 0; padding-left: 1.5rem; font-size: 0.9rem; color: #6b7280;">
                            <li>Your order will be confirmed after payment</li>
                            <li>We'll send you an order confirmation email</li>
                            <li>Track your order status anytime</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php renderFooter(); ?>

    <script src="../assets/js/main.js"></script>
</body>
</html>
