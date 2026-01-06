<?php
require_once '../config/database.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$success = '';
$error = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status = sanitize($_POST['status']);
    
    $valid_statuses = ['pending', 'preparing', 'ready', 'delivered', 'cancelled'];
    if (in_array($status, $valid_statuses)) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $order_id);
        if ($stmt->execute()) {
            $success = "Order #$order_id status updated to $status.";
        } else {
            $error = "Failed to update order status.";
        }
        $stmt->close();
    }
}

// Get filter
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';

// Get orders
$sql = "SELECT o.*, u.username, u.email,
        GROUP_CONCAT(CONCAT(m.name, ' x', oi.quantity) SEPARATOR ', ') as items
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        LEFT JOIN order_items oi ON o.id = oi.order_id 
        LEFT JOIN menu_items m ON oi.menu_item_id = m.id";

if ($status_filter) {
    $sql .= " WHERE o.status = '$status_filter'";
}

$sql .= " GROUP BY o.id ORDER BY o.order_date DESC";

$orders = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="orders.php" class="active"><i class="fas fa-list"></i> Orders</a>
            <a href="menu.php"><i class="fas fa-utensils"></i> Menu Items</a>
            <a href="users.php"><i class="fas fa-users"></i> Users</a>
        </div>

        <!-- Content -->
        <div class="admin-content">
            <h1 style="margin-bottom: 20px; color: #333;">
                <i class="fas fa-list"></i> Manage Orders
            </h1>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Filters -->
            <div style="margin-bottom: 20px;">
                <a href="orders.php" class="btn <?php echo !$status_filter ? 'btn-primary' : 'btn-secondary'; ?>" style="margin-right: 5px;">All</a>
                <a href="orders.php?status=pending" class="btn <?php echo $status_filter === 'pending' ? 'btn-primary' : 'btn-secondary'; ?>" style="margin-right: 5px;">Pending</a>
                <a href="orders.php?status=preparing" class="btn <?php echo $status_filter === 'preparing' ? 'btn-primary' : 'btn-secondary'; ?>" style="margin-right: 5px;">Preparing</a>
                <a href="orders.php?status=ready" class="btn <?php echo $status_filter === 'ready' ? 'btn-primary' : 'btn-secondary'; ?>" style="margin-right: 5px;">Ready</a>
                <a href="orders.php?status=delivered" class="btn <?php echo $status_filter === 'delivered' ? 'btn-primary' : 'btn-secondary'; ?>" style="margin-right: 5px;">Delivered</a>
                <a href="orders.php?status=cancelled" class="btn <?php echo $status_filter === 'cancelled' ? 'btn-primary' : 'btn-secondary'; ?>">Cancelled</a>
            </div>

            <div class="admin-card">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($orders && $orders->num_rows > 0): ?>
                            <?php while ($order = $orders->fetch_assoc()): ?>
                                <tr>
                                    <td><strong>#<?php echo $order['id']; ?></strong></td>
                                    <td>
                                        <?php echo htmlspecialchars($order['username']); ?><br>
                                        <small style="color: #666;"><?php echo htmlspecialchars($order['email']); ?></small>
                                    </td>
                                    <td style="max-width: 200px;"><?php echo htmlspecialchars($order['items']); ?></td>
                                    <td><strong>₹<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                    <td>
                                        <span class="order-status status-<?php echo $order['status']; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, h:i A', strtotime($order['order_date'])); ?></td>
                                    <td>
                                        <form method="POST" style="display: flex; gap: 5px;">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <select name="status" style="padding: 5px; border-radius: 5px; border: 1px solid #ddd;">
                                                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="preparing" <?php echo $order['status'] === 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                                                <option value="ready" <?php echo $order['status'] === 'ready' ? 'selected' : ''; ?>>Ready</option>
                                                <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                            <button type="submit" name="update_status" class="btn btn-primary" style="padding: 5px 10px;">
                                                <i class="fas fa-save"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 30px;">No orders found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
