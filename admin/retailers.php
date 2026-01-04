<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if logged in as admin
if (!isAdminLoggedIn()) {
    redirectToAdminLogin();
}

$admin = getAdminData($_SESSION['admin_id']);
global $db;

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $user_id = intval($_POST['user_id']);
    $action = $_POST['action'];
    
    if ($action === 'deactivate') {
        $stmt = $db->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $message = "Retailer deactivated successfully.";
            $message_type = "success";
        } else {
            $message = "Error deactivating retailer.";
            $message_type = "danger";
        }
    } elseif ($action === 'activate') {
        $stmt = $db->prepare("UPDATE users SET is_active = 1 WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $message = "Retailer activated successfully.";
            $message_type = "success";
        } else {
            $message = "Error activating retailer.";
            $message_type = "danger";
        }
    }
}

// Get all retailers with their order and payment info
$stmt = $db->prepare("
    SELECT 
        u.id,
        u.username,
        u.email,
        u.phone,
        u.shop_address,
        u.created_at,
        u.is_active,
        COUNT(DISTINCT o.id) as total_orders,
        COALESCE(SUM(o.total_amount), 0) as total_spent,
        ra.name as retailer_name
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id
    LEFT JOIN retailer_applications ra ON u.application_id = ra.id
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
$stmt->execute();
$retailers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get summary stats
$stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
$stmt->execute();
$active_retailers = $stmt->get_result()->fetch_assoc();

$stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE is_active = 0");
$stmt->execute();
$inactive_retailers = $stmt->get_result()->fetch_assoc();

$stmt = $db->prepare("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders");
$stmt->execute();
$total_orders_amount = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Retailers - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .retailer-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .retailer-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
        }
        .retailer-name {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
            margin: 0;
        }
        .retailer-status {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: bold;
            color: white;
        }
        .status-active {
            background-color: #28a745;
        }
        .status-inactive {
            background-color: #dc3545;
        }
        .retailer-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }
        .info-item {
            display: flex;
            flex-direction: column;
        }
        .info-label {
            color: #666;
            font-size: 0.85rem;
            font-weight: bold;
            margin-bottom: 0.2rem;
        }
        .info-value {
            color: #333;
        }
        .retailer-stats {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
            padding: 1rem;
            background: #f9f9f9;
            border-radius: 6px;
        }
        .stat-box {
            text-align: center;
        }
        .stat-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: #667eea;
        }
        .stat-label {
            font-size: 0.85rem;
            color: #666;
            margin-top: 0.3rem;
        }
        .retailer-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        .btn-action {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s;
        }
        .btn-deactivate {
            background-color: #ffc107;
            color: white;
        }
        .btn-deactivate:hover {
            background-color: #e0a800;
        }
        .btn-activate {
            background-color: #28a745;
            color: white;
        }
        .btn-activate:hover {
            background-color: #218838;
        }
        .btn-view-orders {
            background-color: #17a2b8;
            color: white;
        }
        .btn-view-orders:hover {
            background-color: #138496;
        }
        .stats-header {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            text-align: center;
            padding: 1.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .stat-card h3 {
            margin: 0;
            font-size: 2rem;
        }
        .stat-card p {
            margin: 0.5rem 0 0 0;
            font-size: 0.9rem;
            opacity: 0.9;
        }
        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .filter-section {
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .filter-btn {
            padding: 0.6rem 1.2rem;
            border: 2px solid #667eea;
            background: white;
            color: #667eea;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s;
        }
        .filter-btn.active {
            background: #667eea;
            color: white;
        }
        .filter-btn:hover {
            background: #667eea;
            color: white;
        }
        .search-box {
            flex: 1;
            min-width: 250px;
        }
        .search-box input {
            width: 100%;
            padding: 0.6rem 1rem;
            border: 2px solid #ddd;
            border-radius: 4px;
            font-size: 0.95rem;
        }
        .search-box input:focus {
            outline: none;
            border-color: #667eea;
        }
    </style>
</head>
<body>
    <?php renderAdminNavbar(); ?>

    <!-- Main Content -->
    <div class="container" style="margin: 3rem auto;">
        <h1>Manage Retailers</h1>

        <!-- Alert Messages -->
        <?php if (isset($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats-header">
            <div class="stat-card">
                <h3><?php echo count($retailers); ?></h3>
                <p>Total Retailers</p>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <h3><?php echo $active_retailers['count']; ?></h3>
                <p>Active Retailers</p>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <h3><?php echo formatCurrency($total_orders_amount['total']); ?></h3>
                <p>Total Orders Value</p>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <button class="filter-btn active" onclick="filterRetailers('all')">All Retailers</button>
            <button class="filter-btn" onclick="filterRetailers('active')">Active Only</button>
            <button class="filter-btn" onclick="filterRetailers('inactive')">Inactive Only</button>
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search by name, email, or phone..." onkeyup="searchRetailers()">
            </div>
        </div>

        <!-- Retailers List -->
        <div id="retailersList">
            <?php if (empty($retailers)): ?>
                <div style="text-align: center; padding: 3rem; background: #f9f9f9; border-radius: 8px;">
                    <p style="color: #666; font-size: 1.1rem;">No retailers yet.</p>
                    <a href="applications.php" class="btn btn-primary">Review Applications</a>
                </div>
            <?php else: ?>
                <?php foreach ($retailers as $retailer): ?>
                    <div class="retailer-card" data-status="<?php echo $retailer['is_active'] ? 'active' : 'inactive'; ?>" data-search="<?php echo strtolower($retailer['username'] . ' ' . $retailer['email'] . ' ' . $retailer['phone'] . ' ' . $retailer['retailer_name']); ?>">
                        <div class="retailer-header">
                            <div>
                                <h3 class="retailer-name"><?php echo htmlspecialchars($retailer['retailer_name'] ?? $retailer['username']); ?></h3>
                                <p style="color: #666; margin: 0; font-size: 0.9rem;">@<?php echo htmlspecialchars($retailer['username']); ?></p>
                            </div>
                            <span class="retailer-status <?php echo $retailer['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo $retailer['is_active'] ? '✓ ACTIVE' : '✗ INACTIVE'; ?>
                            </span>
                        </div>

                        <div class="retailer-info">
                            <div class="info-item">
                                <span class="info-label">📧 Email</span>
                                <span class="info-value"><?php echo htmlspecialchars($retailer['email']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">📱 Phone</span>
                                <span class="info-value"><?php echo htmlspecialchars($retailer['phone']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">📍 Address</span>
                                <span class="info-value"><?php echo htmlspecialchars(substr($retailer['shop_address'], 0, 50)) . (strlen($retailer['shop_address']) > 50 ? '...' : ''); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">📅 Member Since</span>
                                <span class="info-value"><?php echo date('d M Y', strtotime($retailer['created_at'])); ?></span>
                            </div>
                        </div>

                        <div class="retailer-stats">
                            <div class="stat-box">
                                <div class="stat-number"><?php echo $retailer['total_orders']; ?></div>
                                <div class="stat-label">Total Orders</div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-number"><?php echo formatCurrency($retailer['total_spent']); ?></div>
                                <div class="stat-label">Total Spent</div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-number"><?php echo $retailer['total_orders'] > 0 ? round($retailer['total_spent'] / $retailer['total_orders'], 2) : 0; ?></div>
                                <div class="stat-label">Avg Order Value</div>
                            </div>
                        </div>

                        <div class="retailer-actions">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="user_id" value="<?php echo $retailer['id']; ?>">
                                <?php if ($retailer['is_active']): ?>
                                    <button type="submit" name="action" value="deactivate" class="btn-action btn-deactivate" onclick="return confirm('Are you sure you want to deactivate this retailer?');">
                                        🔒 Deactivate
                                    </button>
                                <?php else: ?>
                                    <button type="submit" name="action" value="activate" class="btn-action btn-activate">
                                        🔓 Activate
                                    </button>
                                <?php endif; ?>
                            </form>
                            <a href="../admin/orders.php?user_id=<?php echo $retailer['id']; ?>" class="btn-action btn-view-orders">
                                📋 View Orders
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <?php renderFooter(); ?>

    <script src="../assets/js/main.js"></script>
    <script>
        function filterRetailers(filter) {
            // Update active button
            document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');

            // Filter cards
            const cards = document.querySelectorAll('.retailer-card');
            cards.forEach(card => {
                const status = card.dataset.status;
                if (filter === 'all' || status === filter) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function searchRetailers() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const cards = document.querySelectorAll('.retailer-card');
            
            cards.forEach(card => {
                const searchData = card.dataset.search;
                if (searchData.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
