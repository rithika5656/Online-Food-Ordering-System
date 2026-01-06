<?php
require_once 'config/database.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Get user's orders
$sql = "SELECT o.*, 
        GROUP_CONCAT(CONCAT(m.name, ' x', oi.quantity) SEPARATOR ', ') as items
        FROM orders o 
        LEFT JOIN order_items oi ON o.id = oi.order_id 
        LEFT JOIN menu_items m ON oi.menu_item_id = m.id 
        WHERE o.user_id = ? 
        GROUP BY o.id 
        ORDER BY o.order_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$orders = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Campus Food</title>
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
            <a href="#" class="cart-icon" onclick="toggleCart(); return false;">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-count">0</span>
            </a>
        </div>
    </nav>

    <div class="container">
        <h1 style="text-align: center; margin: 30px 0; color: #ff6b35;">
            <i class="fas fa-list"></i> My Orders
        </h1>

        <div class="orders-container">
            <?php if ($orders->num_rows > 0): ?>
                <?php while ($order = $orders->fetch_assoc()): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <span class="order-id">Order #<?php echo $order['id']; ?></span>
                                <span style="color: #666; font-size: 14px; margin-left: 15px;">
                                    <i class="fas fa-clock"></i> 
                                    <?php echo date('M d, Y h:i A', strtotime($order['order_date'])); ?>
                                </span>
                            </div>
                            <span class="order-status status-<?php echo $order['status']; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </div>
                        <div class="order-items">
                            <p><strong>Items:</strong> <?php echo htmlspecialchars($order['items']); ?></p>
                        </div>
                        <div class="order-total">
                            Total: ₹<?php echo number_format($order['total_amount'], 2); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state" style="background: white; padding: 50px; border-radius: 15px;">
                    <i class="fas fa-shopping-bag" style="font-size: 60px; color: #ddd;"></i>
                    <h3 style="margin-top: 20px; color: #666;">No Orders Yet</h3>
                    <p style="color: #999; margin-bottom: 20px;">You haven't placed any orders yet.</p>
                    <a href="menu.php" class="btn btn-primary">
                        <i class="fas fa-utensils"></i> Browse Menu
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Cart Sidebar -->
    <div class="cart-sidebar">
        <div class="cart-header">
            <h3><i class="fas fa-shopping-cart"></i> Your Cart</h3>
            <button onclick="toggleCart()" style="background:none;border:none;color:white;font-size:20px;cursor:pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="cart-items" id="cart-items"></div>
        <div class="cart-footer">
            <div class="cart-total">
                <span>Total:</span>
                <span id="cart-total">₹0.00</span>
            </div>
            <button class="btn btn-primary" style="width:100%" onclick="placeOrder()">
                <i class="fas fa-check"></i> Place Order
            </button>
        </div>
    </div>

    <!-- Overlay -->
    <div class="overlay"></div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2024 Campus Food Ordering System. All rights reserved.</p>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
