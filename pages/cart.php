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

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$cart = $_SESSION['cart'];
$total = 0;

foreach ($cart as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php renderUserNavbar(); ?>

    <!-- Page Header -->
    <section class="hero">
        <div class="container">
            <h1>Shopping Cart</h1>
            <p>Review your selected products before checkout</p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container" style="margin: 3rem auto; min-height: 60vh;">
        
        <?php if (empty($cart)): ?>
            <!-- Empty Cart -->
            <div class="card" style="text-align: center; padding: 3rem;">
                <h2 style="color: var(--medium-gray); margin-bottom: 1rem;">Your Cart is Empty</h2>
                <p style="color: #6b7280; margin-bottom: 2rem;">You haven't added any products to your cart yet.</p>
                <a href="dashboard.php" class="btn btn-primary" style="display: inline-block;">Continue Shopping</a>
            </div>
        
        <?php else: ?>
            <div class="row">
                <!-- Cart Items -->
                <div class="col-8">
                    <div class="card">
                        <h2 style="margin-top: 0;">Cart Items (<?php echo count($cart); ?>)</h2>
                        
                        <div style="overflow-x: auto;">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th style="width: 40%;">Product</th>
                                        <th style="width: 15%;">Price</th>
                                        <th style="width: 15%;">Quantity</th>
                                        <th style="width: 15%;">Total</th>
                                        <th style="width: 15%;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart as $cartKey => $item): ?>
                                        <tr>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 1rem;">
                                                    <?php if (!empty($item['image_path'])): ?>
                                                        <img src="../<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                                                    <?php else: ?>
                                                        <div style="width: 60px; height: 60px; background-color: var(--light-gray); border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">📦</div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <h4 style="margin: 0 0 0.5rem 0;"><?php echo htmlspecialchars($item['name']); ?></h4>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo formatCurrency($item['price']); ?></td>
                                            <td>
                                                <div class="quantity-selector" style="margin: 0; justify-content: flex-start;">
                                                    <button type="button" onclick="updateQuantity('<?php echo htmlspecialchars($cartKey); ?>', parseInt(document.querySelector('input[data-cart-key=&quot;<?php echo htmlspecialchars($cartKey); ?>&quot;]').value) - 1)">−</button>
                                                    <input type="number" value="<?php echo $item['quantity']; ?>" min="1" data-cart-key="<?php echo htmlspecialchars($cartKey); ?>" style="width: 50px; text-align: center;">
                                                    <button type="button" onclick="updateQuantity('<?php echo htmlspecialchars($cartKey); ?>', parseInt(document.querySelector('input[data-cart-key=&quot;<?php echo htmlspecialchars($cartKey); ?>&quot;]').value) + 1)">+</button>
                                                </div>
                                            </td>
                                            <td><?php echo formatCurrency($item['price'] * $item['quantity']); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;" onclick="removeFromCart('<?php echo htmlspecialchars($cartKey); ?>')">Remove</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Cart Summary -->
                <div class="col-4">
                    <div class="card" style="position: sticky; top: 2rem;">
                        <h3>Order Summary</h3>
                        
                        <div style="border-bottom: 1px solid var(--light-gray); padding-bottom: 1rem; margin-bottom: 1rem;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span>Subtotal:</span>
                                <span><?php echo formatCurrency($total); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span>Shipping:</span>
                                <span>Free</span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span>Tax (GST):</span>
                                <span><?php echo formatCurrency($total * (TAX_RATE / 100)); ?></span>
                            </div>
                        </div>

                        <div style="background-color: var(--light-gray); padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
                            <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: bold;">
                                <span>Total:</span>
                                <span><?php echo formatCurrency($total + ($total * (TAX_RATE / 100))); ?></span>
                            </div>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                            <a href="checkout.php" class="btn btn-primary btn-block" style="text-align: center; text-decoration: none;">
                                Proceed to Checkout
                            </a>
                            <a href="dashboard.php" class="btn btn-secondary btn-block" style="text-align: center; text-decoration: none;">
                                Continue Shopping
                            </a>
                        </div>

                        <button type="button" class="btn btn-link" style="width: 100%; margin-top: 1rem; color: var(--danger-color);" onclick="if(confirm('Are you sure you want to clear your cart?')) clearCart();">
                            Clear Cart
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <!-- Footer -->
    <?php renderFooter(); ?>

    <script src="../assets/js/main.js"></script>
    <script>
        function updateQuantity(cartKey, newQuantity) {
            if (newQuantity < 1) {
                alert('Quantity must be at least 1');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'update_quantity');
            formData.append('item_id', cartKey);
            formData.append('quantity', newQuantity);

            fetch('cart_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating quantity');
            });
        }

        function removeFromCart(cartKey) {
            if (confirm('Are you sure you want to remove this item?')) {
                const formData = new FormData();
                formData.append('action', 'remove_from_cart');
                formData.append('item_id', cartKey);

                fetch('cart_handler.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while removing item');
                });
            }
        }

        function clearCart() {
            const formData = new FormData();
            formData.append('action', 'clear_cart');

            fetch('cart_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while clearing cart');
            });
        }
    </script>
</body>
</html>
