<?php
require_once 'config/database.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_data = isset($_POST['cart_data']) ? $_POST['cart_data'] : '';
    $total = isset($_POST['total']) ? floatval($_POST['total']) : 0;
    
    if (empty($cart_data) || $total <= 0) {
        $error = 'Invalid order data.';
    } else {
        $cart = json_decode($cart_data, true);
        
        if (empty($cart)) {
            $error = 'Cart is empty.';
        } else {
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Insert order
                $order_sql = "INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'pending')";
                $order_stmt = $conn->prepare($order_sql);
                $order_stmt->bind_param("id", $_SESSION['user_id'], $total);
                $order_stmt->execute();
                $order_id = $conn->insert_id;
                $order_stmt->close();
                
                // Insert order items
                $item_sql = "INSERT INTO order_items (order_id, menu_item_id, quantity, price) VALUES (?, ?, ?, ?)";
                $item_stmt = $conn->prepare($item_sql);
                
                foreach ($cart as $item) {
                    $item_stmt->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
                    $item_stmt->execute();
                }
                $item_stmt->close();
                
                // Commit transaction
                $conn->commit();
                $success = "Order #$order_id placed successfully!";
                
            } catch (Exception $e) {
                $conn->rollback();
                $error = 'Failed to place order. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Placed - Campus Food</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <a href="index.php" class="logo">
            <i class="fas fa-utensils"></i>Campus Food
        </a>
        <div class="nav-links">
            <a href="index.php"><i class="fas fa-home"></i> Home</a>
            <a href="menu.php"><i class="fas fa-book-open"></i> Menu</a>
            <a href="orders.php"><i class="fas fa-list"></i> My Orders</a>
            <?php if (isAdmin()): ?>
                <a href="admin/dashboard.php"><i class="fas fa-cog"></i> Admin</a>
            <?php endif; ?>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="form-container" style="max-width: 500px; text-align: center;">
            <?php if ($success): ?>
                <div style="font-size: 60px; color: #28a745; margin-bottom: 20px;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2 style="color: #28a745; margin-bottom: 20px;">Order Placed Successfully!</h2>
                <p style="color: #666; margin-bottom: 30px;"><?php echo $success; ?></p>
                <p style="color: #666; margin-bottom: 30px;">Your order is being prepared. You can track the status in My Orders.</p>
                <div style="display: flex; gap: 15px; justify-content: center;">
                    <a href="orders.php" class="btn btn-primary">
                        <i class="fas fa-list"></i> View My Orders
                    </a>
                    <a href="menu.php" class="btn btn-secondary">
                        <i class="fas fa-utensils"></i> Order More
                    </a>
                </div>
            <?php else: ?>
                <div style="font-size: 60px; color: #dc3545; margin-bottom: 20px;">
                    <i class="fas fa-times-circle"></i>
                </div>
                <h2 style="color: #dc3545; margin-bottom: 20px;">Order Failed</h2>
                <p style="color: #666; margin-bottom: 30px;"><?php echo $error ?: 'Something went wrong. Please try again.'; ?></p>
                <a href="menu.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Back to Menu
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2024 Campus Food Ordering System. All rights reserved.</p>
    </footer>

    <script>
        // Clear cart after successful order
        <?php if ($success): ?>
        localStorage.removeItem('cart');
        <?php endif; ?>
    </script>
</body>
</html>
