<?php
require_once '../config/database.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Get statistics
$stats = [
    'total_orders' => 0,
    'pending_orders' => 0,
    'total_revenue' => 0,
    'menu_items' => 0,
    'users' => 0
];

// Total orders
$result = $conn->query("SELECT COUNT(*) as count FROM orders");
if ($result) $stats['total_orders'] = $result->fetch_assoc()['count'];

// Pending orders
$result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
if ($result) $stats['pending_orders'] = $result->fetch_assoc()['count'];

// Total revenue
$result = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'delivered'");
if ($result) {
    $row = $result->fetch_assoc();
    $stats['total_revenue'] = $row['total'] ?: 0;
}

// Menu items
$result = $conn->query("SELECT COUNT(*) as count FROM menu_items");
if ($result) $stats['menu_items'] = $result->fetch_assoc()['count'];

// Users
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
if ($result) $stats['users'] = $result->fetch_assoc()['count'];

// Recent orders
$recent_orders = $conn->query("SELECT o.*, u.username FROM orders o 
                               JOIN users u ON o.user_id = u.id 
                               ORDER BY o.order_date DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Campus Food</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        .stat-card h3 {
            font-size: 2rem;
            margin: 10px 0;
        }
        .stat-card p {
            color: #666;
        }
        .stat-orders { color: #ff6b35; }
        .stat-pending { color: #ffc107; }
        .stat-revenue { color: #28a745; }
        .stat-menu { color: #17a2b8; }
        .stat-users { color: #6f42c1; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <a href="../index.php" class="logo">
            <i class="fas fa-utensils"></i>Campus Food
        </a>
        <div class="nav-links">
            <a href="../index.php"><i class="fas fa-home"></i> Home</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

    <div class="admin-container">
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="orders.php"><i class="fas fa-list"></i> Orders</a>
            <a href="menu.php"><i class="fas fa-utensils"></i> Menu Items</a>
            <a href="users.php"><i class="fas fa-users"></i> Users</a>
        </div>

        <!-- Content -->
        <div class="admin-content">
            <h1 style="margin-bottom: 30px; color: #333;">
                <i class="fas fa-tachometer-alt"></i> Admin Dashboard
            </h1>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-shopping-bag stat-orders"></i>
                    <h3><?php echo $stats['total_orders']; ?></h3>
                    <p>Total Orders</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-clock stat-pending"></i>
                    <h3><?php echo $stats['pending_orders']; ?></h3>
                    <p>Pending Orders</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-rupee-sign stat-revenue"></i>
                    <h3>₹<?php echo number_format($stats['total_revenue'], 2); ?></h3>
                    <p>Total Revenue</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-utensils stat-menu"></i>
                    <h3><?php echo $stats['menu_items']; ?></h3>
                    <p>Menu Items</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-users stat-users"></i>
                    <h3><?php echo $stats['users']; ?></h3>
                    <p>Registered Users</p>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="admin-card">
                <h2 style="margin-bottom: 20px;"><i class="fas fa-clock"></i> Recent Orders</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recent_orders && $recent_orders->num_rows > 0): ?>
                            <?php while ($order = $recent_orders->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                                    <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td>
                                        <span class="order-status status-<?php echo $order['status']; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, h:i A', strtotime($order['order_date'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">No orders yet</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <a href="orders.php" style="display: inline-block; margin-top: 15px; color: #ff6b35;">
                    View All Orders <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
