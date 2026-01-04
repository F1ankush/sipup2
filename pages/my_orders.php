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
$filter = sanitize($_GET['status'] ?? 'all');

// Get all orders for this user
global $db;

$query = "SELECT id, order_number, total_amount, payment_method, status, created_at FROM orders WHERE user_id = ?";
$params = [$_SESSION['user_id']];

if ($filter !== 'all') {
    $query .= " AND status = ?";
    $params[] = $filter;
}

$query .= " ORDER BY created_at DESC";

$stmt = $db->prepare($query);
if (count($params) === 1) {
    $stmt->bind_param("i", $params[0]);
} else {
    $stmt->bind_param("is", $params[0], $params[1]);
}

$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get order count by status
$statusCounts = [];
$stmt = $db->prepare("SELECT status, COUNT(*) as count FROM orders WHERE user_id = ? GROUP BY status");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$statusData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
foreach ($statusData as $row) {
    $statusCounts[$row['status']] = $row['count'];
}
$totalOrders = array_sum($statusCounts);

// Status colors and labels
$statusInfo = [
    'pending_payment' => ['label' => 'Pending Payment', 'color' => '#f59e0b', 'icon' => '⏳'],
    'pending' => ['label' => 'Pending', 'color' => '#3b82f6', 'icon' => '📦'],
    'processing' => ['label' => 'Processing', 'color' => '#06b6d4', 'icon' => '⚙️'],
    'packed' => ['label' => 'Packed', 'color' => '#8b5cf6', 'icon' => '📮'],
    'shipped' => ['label' => 'Shipped', 'color' => '#ec4899', 'icon' => '🚚'],
    'delivered' => ['label' => 'Delivered', 'color' => '#22c55e', 'icon' => '✅'],
    'cancelled' => ['label' => 'Cancelled', 'color' => '#ef4444', 'icon' => '❌'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .filter-tabs {
            display: flex;
            gap: 1rem;
            margin: 2rem 0;
            flex-wrap: wrap;
        }
        .filter-tab {
            padding: 0.8rem 1.5rem;
            border: 2px solid #e5e7eb;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: #6b7280;
            font-weight: 500;
        }
        .filter-tab:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }
        .filter-tab.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        .order-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }
        .order-card:hover {
            border-color: var(--primary-color);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }
        .order-number {
            font-weight: bold;
            color: #1f2937;
            font-size: 1.1rem;
        }
        .order-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            color: white;
        }
        .order-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin: 1rem 0;
        }
        .info-item {
            display: flex;
            flex-direction: column;
        }
        .info-label {
            font-size: 0.85rem;
            color: #6b7280;
            font-weight: 500;
            margin-bottom: 0.3rem;
            text-transform: uppercase;
        }
        .info-value {
            font-size: 1.1rem;
            color: #1f2937;
            font-weight: 600;
        }
        .order-actions {
            display: flex;
            gap: 0.8rem;
            margin-top: 1rem;
        }
        .action-btn {
            padding: 0.6rem 1.2rem;
            border: 1px solid var(--primary-color);
            background: white;
            color: var(--primary-color);
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-block;
        }
        .action-btn:hover {
            background: var(--primary-color);
            color: white;
        }
        .action-btn.primary {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        .action-btn.primary:hover {
            background: #5568d3;
        }
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            background: #f9fafb;
            border-radius: 8px;
            border: 2px dashed #e5e7eb;
        }
        .empty-state h3 {
            color: #6b7280;
            margin: 0 0 0.5rem 0;
        }
        .empty-state p {
            color: #9ca3af;
            margin: 0 0 1.5rem 0;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin: 2rem 0;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 8px;
            text-align: center;
        }
        .stat-card h3 {
            margin: 0 0 0.5rem 0;
            font-size: 2rem;
        }
        .stat-card p {
            margin: 0;
            font-size: 0.9rem;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <?php renderUserNavbar(); ?>

    <!-- Page Header -->
    <section class="hero">
        <div class="container">
            <h1>My Orders</h1>
            <p>Track and manage your orders</p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container" style="margin: 3rem auto; min-height: 60vh;">
        
        <!-- Quick Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $totalOrders; ?></h3>
                <p>Total Orders</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $statusCounts['pending_payment'] ?? 0; ?></h3>
                <p>Awaiting Payment</p>
            </div>
            <div class="stat-card">
                <h3><?php echo ($statusCounts['processing'] ?? 0) + ($statusCounts['packed'] ?? 0) + ($statusCounts['shipped'] ?? 0); ?></h3>
                <p>In Progress</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $statusCounts['delivered'] ?? 0; ?></h3>
                <p>Delivered</p>
            </div>
        </div>

        <!-- Filter Tabs -->
        <h2 style="margin-top: 2rem;">Filter Orders</h2>
        <div class="filter-tabs">
            <a href="?status=all" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">
                All Orders (<?php echo $totalOrders; ?>)
            </a>
            <a href="?status=pending_payment" class="filter-tab <?php echo $filter === 'pending_payment' ? 'active' : ''; ?>">
                Awaiting Payment (<?php echo $statusCounts['pending_payment'] ?? 0; ?>)
            </a>
            <a href="?status=processing" class="filter-tab <?php echo $filter === 'processing' ? 'active' : ''; ?>">
                Processing (<?php echo $statusCounts['processing'] ?? 0; ?>)
            </a>
            <a href="?status=shipped" class="filter-tab <?php echo $filter === 'shipped' ? 'active' : ''; ?>">
                Shipped (<?php echo $statusCounts['shipped'] ?? 0; ?>)
            </a>
            <a href="?status=delivered" class="filter-tab <?php echo $filter === 'delivered' ? 'active' : ''; ?>">
                Delivered (<?php echo $statusCounts['delivered'] ?? 0; ?>)
            </a>
        </div>

        <!-- Orders List -->
        <h2 style="margin-top: 2rem;">Your Orders</h2>
        
        <?php if (empty($orders)): ?>
            <!-- Empty State -->
            <div class="empty-state">
                <h3>📦 No Orders Yet</h3>
                <p>You haven't placed any orders <?php echo $filter !== 'all' ? 'in this category' : 'yet'; ?>.</p>
                <a href="dashboard.php" class="btn btn-primary" style="display: inline-block;">Start Shopping</a>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <div class="order-number">Order #<?php echo htmlspecialchars($order['order_number']); ?></div>
                            <small style="color: #6b7280;">Placed on <?php echo date('d M Y, g:i A', strtotime($order['created_at'])); ?></small>
                        </div>
                        <div class="order-status-badge" style="background-color: <?php echo $statusInfo[$order['status']]['color']; ?>;">
                            <span><?php echo $statusInfo[$order['status']]['icon']; ?></span>
                            <span><?php echo $statusInfo[$order['status']]['label']; ?></span>
                        </div>
                    </div>

                    <div class="order-info">
                        <div class="info-item">
                            <span class="info-label">Total Amount</span>
                            <span class="info-value"><?php echo formatCurrency($order['total_amount']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Payment Method</span>
                            <span class="info-value"><?php echo $order['payment_method'] === 'cod' ? 'Cash on Delivery' : 'UPI'; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Order Status</span>
                            <span class="info-value" style="color: <?php echo $statusInfo[$order['status']]['color']; ?>;">
                                <?php echo $statusInfo[$order['status']]['label']; ?>
                            </span>
                        </div>
                    </div>

                    <div class="order-actions">
                        <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="action-btn primary">
                            View Details
                        </a>
                        
                        <?php if ($order['status'] === 'pending_payment'): ?>
                            <a href="payment.php?order_id=<?php echo $order['id']; ?>" class="action-btn">
                                Complete Payment
                            </a>
                        <?php endif; ?>

                        <?php if ($order['status'] !== 'delivered' && $order['status'] !== 'cancelled'): ?>
                            <a href="order_detail.php?id=<?php echo $order['id']; ?>#track" class="action-btn">
                                Track Order
                            </a>
                        <?php endif; ?>

                        <a href="checkout_guide.php" class="action-btn" style="border-color: #9ca3af; color: #6b7280;">
                            📖 Checkout Help
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Help Section -->
        <div style="background: #f0fdf4; border-left: 4px solid #22c55e; padding: 2rem; border-radius: 4px; margin-top: 3rem;">
            <h3 style="margin-top: 0; color: #16a34a;">💡 Need Help with Your Orders?</h3>
            <ul style="margin: 1rem 0; color: #4b5563;">
                <li><strong>Tracking Your Order:</strong> Click "View Details" to see full order information and tracking status</li>
                <li><strong>Pending Payment:</strong> Click "Complete Payment" to finish payment for UPI orders</li>
                <li><strong>Download Invoice:</strong> Go to order details to download your bill/invoice</li>
                <li><strong>Order Issues:</strong> Contact support if you have any problems with your order</li>
                <li><strong>Learn the Process:</strong> Check out our <a href="checkout_guide.php" style="color: var(--primary-color); font-weight: bold;">complete checkout guide</a></li>
            </ul>
        </div>

        <!-- Back to Shopping -->
        <div style="text-align: center; margin-top: 3rem;">
            <a href="dashboard.php" class="btn btn-primary" style="display: inline-block; padding: 1rem 2rem;">
                Continue Shopping →
            </a>
        </div>

    </div>

    <!-- Footer -->
    <?php renderFooter(); ?>
</body>
</html>
