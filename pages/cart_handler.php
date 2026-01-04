<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Initialize cart in session if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = sanitize($_POST['action'] ?? '');
$response = ['success' => false, 'message' => 'Invalid action'];

switch ($action) {
    case 'add_to_cart':
        $productId = intval($_POST['product_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 1);
        
        if ($productId <= 0 || $quantity <= 0) {
            $response = ['success' => false, 'message' => 'Invalid product or quantity'];
            break;
        }
        
        // Get product details
        $product = getProduct($productId);
        if (!$product) {
            $response = ['success' => false, 'message' => 'Product not found'];
            break;
        }
        
        // Check stock
        if ($product['quantity_in_stock'] < $quantity) {
            $response = ['success' => false, 'message' => 'Insufficient stock. Available: ' . $product['quantity_in_stock']];
            break;
        }
        
        // Add to cart
        $cartKey = 'product_' . $productId;
        if (isset($_SESSION['cart'][$cartKey])) {
            $_SESSION['cart'][$cartKey]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$cartKey] = [
                'product_id' => $productId,
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => $quantity,
                'image_path' => $product['image_path']
            ];
        }
        
        $cartCount = count($_SESSION['cart']);
        $response = [
            'success' => true, 
            'message' => 'Product added to cart',
            'cart_count' => $cartCount
        ];
        break;
    
    case 'remove_from_cart':
        $cartKey = sanitize($_POST['item_id'] ?? '');
        
        if (isset($_SESSION['cart'][$cartKey])) {
            unset($_SESSION['cart'][$cartKey]);
            $response = ['success' => true, 'message' => 'Item removed from cart'];
        } else {
            $response = ['success' => false, 'message' => 'Item not found in cart'];
        }
        break;
    
    case 'update_quantity':
        $cartKey = sanitize($_POST['item_id'] ?? '');
        $quantity = intval($_POST['quantity'] ?? 0);
        
        if ($quantity < 1) {
            $response = ['success' => false, 'message' => 'Quantity must be at least 1'];
            break;
        }
        
        if (!isset($_SESSION['cart'][$cartKey])) {
            $response = ['success' => false, 'message' => 'Item not found in cart'];
            break;
        }
        
        // Check stock
        $product = getProduct($_SESSION['cart'][$cartKey]['product_id']);
        if ($product['quantity_in_stock'] < $quantity) {
            $response = ['success' => false, 'message' => 'Insufficient stock. Available: ' . $product['quantity_in_stock']];
            break;
        }
        
        $_SESSION['cart'][$cartKey]['quantity'] = $quantity;
        $response = ['success' => true, 'message' => 'Quantity updated'];
        break;
    
    case 'get_cart':
        $cartItems = $_SESSION['cart'] ?? [];
        $cartCount = count($cartItems);
        $total = 0;
        
        foreach ($cartItems as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        
        $response = [
            'success' => true,
            'cart_count' => $cartCount,
            'total' => $total,
            'items' => $cartItems
        ];
        break;
    
    case 'clear_cart':
        $_SESSION['cart'] = [];
        $response = ['success' => true, 'message' => 'Cart cleared'];
        break;
    
    default:
        $response = ['success' => false, 'message' => 'Unknown action'];
}

header('Content-Type: application/json');
echo json_encode($response);
exit();
?>
