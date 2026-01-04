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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Guide - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .checkout-steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin: 3rem 0;
        }
        .step-card {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
        }
        .step-card:hover {
            border-color: var(--primary-color);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
            transform: translateY(-2px);
        }
        .step-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 50%;
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        .step-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 0.8rem;
            color: #1f2937;
        }
        .step-description {
            color: #6b7280;
            font-size: 0.95rem;
            line-height: 1.6;
        }
        .payment-methods {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin: 2rem 0;
        }
        .payment-card {
            background: #f9fafb;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .payment-card:hover {
            border-color: var(--primary-color);
            background: #f3f4f6;
        }
        .payment-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .timeline {
            position: relative;
            padding: 2rem 0;
        }
        .timeline-item {
            display: flex;
            margin-bottom: 2rem;
            position: relative;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: 29px;
            top: 50px;
            width: 2px;
            height: 60px;
            background: #e5e7eb;
        }
        .timeline-item:last-child::before {
            display: none;
        }
        .timeline-marker {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            min-width: 60px;
            background: white;
            border: 3px solid var(--primary-color);
            border-radius: 50%;
            margin-right: 2rem;
            font-weight: bold;
            color: var(--primary-color);
            z-index: 1;
        }
        .timeline-content {
            flex: 1;
        }
        .timeline-content h4 {
            margin: 0 0 0.5rem 0;
            color: #1f2937;
            font-size: 1.1rem;
        }
        .timeline-content p {
            margin: 0;
            color: #6b7280;
        }
        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }
        .faq-section {
            margin: 2rem 0;
        }
        .faq-item {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 1rem;
            overflow: hidden;
        }
        .faq-question {
            padding: 1.5rem;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            transition: background 0.3s ease;
        }
        .faq-question:hover {
            background: #f3f4f6;
        }
        .faq-answer {
            padding: 1.5rem;
            background: #f9fafb;
            color: #6b7280;
            display: none;
        }
        .faq-answer.active {
            display: block;
        }
        .icon {
            display: inline-block;
            margin-right: 0.5rem;
        }
        .highlight-box {
            background: #eff6ff;
            border-left: 4px solid var(--primary-color);
            padding: 1.5rem;
            border-radius: 4px;
            margin: 2rem 0;
        }
        .info-box {
            background: #f0fdf4;
            border-left: 4px solid #22c55e;
            padding: 1.5rem;
            border-radius: 4px;
            margin: 1rem 0;
        }
        .warning-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 1.5rem;
            border-radius: 4px;
            margin: 1rem 0;
        }
    </style>
</head>
<body>
    <?php renderUserNavbar(); ?>

    <!-- Page Header -->
    <section class="hero">
        <div class="container">
            <h1>Complete Checkout Process</h1>
            <p>Step-by-step guide for placing your order</p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container" style="margin: 3rem auto;">
        
        <!-- Quick Overview -->
        <div class="highlight-box">
            <h2 style="margin-top: 0; color: var(--primary-color);">Ready to Order?</h2>
            <p>The checkout process is simple and secure. Follow the steps below to complete your purchase.</p>
            <a href="cart.php" class="btn btn-primary" style="display: inline-block; margin-top: 1rem;">← Back to Cart</a>
        </div>

        <!-- 4-Step Checkout Process -->
        <h2 style="text-align: center; margin: 3rem 0 2rem 0;">The 4-Step Checkout Process</h2>
        <div class="checkout-steps">
            <div class="step-card">
                <div class="step-number">1</div>
                <div class="step-title">Review Cart</div>
                <div class="step-description">Check all products, quantities, and prices in your cart</div>
            </div>
            <div class="step-card">
                <div class="step-number">2</div>
                <div class="step-title">Select Payment</div>
                <div class="step-description">Choose between COD or UPI payment method</div>
            </div>
            <div class="step-card">
                <div class="step-number">3</div>
                <div class="step-title">Confirm Order</div>
                <div class="step-description">Review and confirm your order details</div>
            </div>
            <div class="step-card">
                <div class="step-number">4</div>
                <div class="step-title">Track Order</div>
                <div class="step-description">Monitor your order status and delivery</div>
            </div>
        </div>

        <!-- Detailed Step-by-Step Timeline -->
        <h2 style="margin-top: 4rem;">Detailed Checkout Timeline</h2>
        <div class="timeline">
            <div class="timeline-item">
                <div class="timeline-marker">1</div>
                <div class="timeline-content">
                    <h4>Review Your Cart</h4>
                    <p>Go to your shopping cart (click cart icon in navbar). Review all items, quantities, prices, and the total amount including GST. Adjust quantities or remove items if needed.</p>
                    <small style="color: #9ca3af; display: block; margin-top: 0.5rem;">💡 Tip: Make sure stock is available for all items before proceeding.</small>
                </div>
            </div>

            <div class="timeline-item">
                <div class="timeline-marker">2</div>
                <div class="timeline-content">
                    <h4>Click "Proceed to Checkout"</h4>
                    <p>On the cart page, click the prominent <strong>"PROCEED TO CHECKOUT"</strong> button in the order summary section (right side of the cart).</p>
                    <small style="color: #9ca3af; display: block; margin-top: 0.5rem;">💡 Tip: This button is large and highlighted for easy visibility.</small>
                </div>
            </div>

            <div class="timeline-item">
                <div class="timeline-marker">3</div>
                <div class="timeline-content">
                    <h4>Select Payment Method</h4>
                    <p>Choose your preferred payment method:</p>
                    <div class="payment-methods" style="margin-top: 1rem; max-width: 100%;">
                        <div class="payment-card">
                            <div class="payment-icon">🏦</div>
                            <h5>Cash on Delivery</h5>
                            <p>Pay when you receive your order</p>
                        </div>
                        <div class="payment-card">
                            <div class="payment-icon">📱</div>
                            <h5>UPI Payment</h5>
                            <p>Instant payment using UPI</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="timeline-item">
                <div class="timeline-marker">4</div>
                <div class="timeline-content">
                    <h4>Review Order Details</h4>
                    <p>Verify your:</p>
                    <ul style="margin: 0.5rem 0; color: #6b7280;">
                        <li>All products and quantities</li>
                        <li>Subtotal amount</li>
                        <li>GST (tax) amount</li>
                        <li>Final total amount</li>
                        <li>Shipping address (if applicable)</li>
                    </ul>
                </div>
            </div>

            <div class="timeline-item">
                <div class="timeline-marker">5</div>
                <div class="timeline-content">
                    <h4>Confirm and Place Order</h4>
                    <p>Click the "Place Order" button to complete your purchase. Your order will be immediately created in the system.</p>
                    <small style="color: #9ca3af; display: block; margin-top: 0.5rem;">💡 You'll receive an order confirmation with your order number.</small>
                </div>
            </div>

            <div class="timeline-item">
                <div class="timeline-marker">6</div>
                <div class="timeline-content">
                    <h4>Complete Payment (if UPI)</h4>
                    <p>If you selected UPI payment, you'll see a QR code. Scan it with your UPI app to complete payment within 30 minutes.</p>
                    <small style="color: #9ca3af; display: block; margin-top: 0.5rem;">💡 For COD, no payment is needed at this step. Pay when delivery arrives.</small>
                </div>
            </div>

            <div class="timeline-item">
                <div class="timeline-marker">7</div>
                <div class="timeline-content">
                    <h4>Track Your Order</h4>
                    <p>Go to "My Orders" in your dashboard to:</p>
                    <ul style="margin: 0.5rem 0; color: #6b7280;">
                        <li>View order status (Processing, Packed, Shipped, Delivered)</li>
                        <li>See delivery details</li>
                        <li>Upload payment proof (if UPI)</li>
                        <li>Download bill/invoice</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Payment Methods Detail -->
        <h2 style="margin-top: 4rem;">Payment Methods Explained</h2>
        
        <div style="background: white; border-radius: 8px; padding: 2rem; margin: 2rem 0; border: 2px solid #e5e7eb;">
            <h3 style="margin-top: 0; display: flex; align-items: center;">
                <span style="font-size: 2rem; margin-right: 1rem;">🏦</span>
                Cash on Delivery (COD)
            </h3>
            <p><strong>How it works:</strong> Pay the delivery agent when they deliver your order.</p>
            <ul style="color: #6b7280;">
                <li>No advance payment required</li>
                <li>Inspect products before payment</li>
                <li>Order status available online</li>
                <li>Fast processing and delivery</li>
            </ul>
            <div class="info-box">
                <strong>✓ Best for:</strong> First-time orders, large orders, or when you prefer to inspect before paying
            </div>
        </div>

        <div style="background: white; border-radius: 8px; padding: 2rem; margin: 2rem 0; border: 2px solid #e5e7eb;">
            <h3 style="margin-top: 0; display: flex; align-items: center;">
                <span style="font-size: 2rem; margin-right: 1rem;">📱</span>
                UPI Payment
            </h3>
            <p><strong>How it works:</strong> Pay instantly using your preferred UPI app (Google Pay, PhonePe, etc.).</p>
            <ul style="color: #6b7280;">
                <li>Scan QR code with your UPI app</li>
                <li>Instant payment confirmation</li>
                <li>Immediate order processing</li>
                <li>Faster delivery</li>
            </ul>
            <div class="info-box">
                <strong>✓ Best for:</strong> Quick orders, guaranteed inventory, or when you want fastest delivery
            </div>
        </div>

        <!-- Important Notes -->
        <h2 style="margin-top: 4rem;">Important Notes</h2>
        <div class="warning-box">
            <h4 style="margin-top: 0;">⚠️ Before Checkout</h4>
            <ul>
                <li><strong>Verify Stock:</strong> Check that all items are in stock</li>
                <li><strong>Review Quantities:</strong> Make sure quantities are correct</li>
                <li><strong>Check Prices:</strong> Prices can change - review final total</li>
                <li><strong>GST Calculation:</strong> GST (tax) is included in the final total</li>
            </ul>
        </div>

        <div class="info-box">
            <h4 style="margin-top: 0;">ℹ️ After Order Confirmation</h4>
            <ul>
                <li><strong>Order Number:</strong> You'll receive a unique order number (e.g., ORD20260104XXXX)</li>
                <li><strong>Status Updates:</strong> Check "My Orders" for real-time status</li>
                <li><strong>Contact Support:</strong> Reach out if you have any issues</li>
                <li><strong>Invoice Download:</strong> Available after payment confirmation</li>
            </ul>
        </div>

        <!-- FAQ Section -->
        <h2 style="margin-top: 4rem;">Frequently Asked Questions</h2>
        <div class="faq-section">
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <span>Can I modify my order after placing it?</span>
                    <span style="font-size: 1.5rem;">+</span>
                </div>
                <div class="faq-answer">
                    <p>Orders in "Pending" status may be modifiable. Check your order details page or contact support immediately to request changes.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <span>What if I selected UPI but want to pay later?</span>
                    <span style="font-size: 1.5rem;">+</span>
                </div>
                <div class="faq-answer">
                    <p>You have 30 minutes to complete UPI payment. After that, the order may be cancelled. Contact support to extend the deadline if needed.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <span>Is there a minimum order value?</span>
                    <span style="font-size: 1.5rem;">+</span>
                </div>
                <div class="faq-answer">
                    <p>No minimum order value. You can order any amount, though bulk orders may have better pricing available.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <span>How long does delivery take?</span>
                    <span style="font-size: 1.5rem;">+</span>
                </div>
                <div class="faq-answer">
                    <p>Typical delivery time is 2-5 business days depending on location and order size. Track your order for real-time updates.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <span>Can I cancel my order?</span>
                    <span style="font-size: 1.5rem;">+</span>
                </div>
                <div class="faq-answer">
                    <p>Orders can be cancelled if they haven't been shipped yet. Go to "My Orders" and click "Cancel Order" if available. Contact support for assistance.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <span>What if my order has an issue?</span>
                    <span style="font-size: 1.5rem;">+</span>
                </div>
                <div class="faq-answer">
                    <p>Contact our support team immediately with your order number. We'll help resolve any issues with missing items, damaged products, or other concerns.</p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="button-group" style="justify-content: center; margin-top: 3rem;">
            <a href="cart.php" class="btn btn-primary" style="padding: 1rem 2rem;">
                ← Go Back to Cart
            </a>
            <a href="dashboard.php" class="btn btn-secondary" style="padding: 1rem 2rem;">
                Continue Shopping →
            </a>
        </div>

    </div>

    <!-- Footer -->
    <?php renderFooter(); ?>

    <script>
        function toggleFAQ(element) {
            const answer = element.nextElementSibling;
            const isActive = answer.classList.contains('active');
            
            // Close all other FAQs
            document.querySelectorAll('.faq-answer.active').forEach(el => {
                el.classList.remove('active');
                el.previousElementSibling.querySelector('span:last-child').textContent = '+';
            });
            
            // Toggle current FAQ
            if (!isActive) {
                answer.classList.add('active');
                element.querySelector('span:last-child').textContent = '−';
            }
        }
    </script>
</body>
</html>
