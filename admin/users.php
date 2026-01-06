<?php
require_once '../config/database.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$success = '';
$error = '';

// Handle delete user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $user_id = intval($_POST['user_id']);
    
    // Don't allow deleting yourself
    if ($user_id === $_SESSION['user_id']) {
        $error = "You cannot delete your own account.";
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $success = "User deleted successfully!";
        } else {
            $error = "Failed to delete user.";
        }
        $stmt->close();
    }
}

// Get users
$users = $conn->query("SELECT u.*, 
                       (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count,
                       (SELECT SUM(total_amount) FROM orders WHERE user_id = u.id AND status = 'delivered') as total_spent
                       FROM users u ORDER BY u.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
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
            <a href="orders.php"><i class="fas fa-list"></i> Orders</a>
            <a href="menu.php"><i class="fas fa-utensils"></i> Menu Items</a>
            <a href="users.php" class="active"><i class="fas fa-users"></i> Users</a>
        </div>

        <!-- Content -->
        <div class="admin-content">
            <h1 style="margin-bottom: 20px; color: #333;">
                <i class="fas fa-users"></i> Manage Users
            </h1>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="admin-card">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Orders</th>
                            <th>Total Spent</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($users && $users->num_rows > 0): ?>
                            <?php while ($user = $users->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                        <?php if ($user['id'] === $_SESSION['user_id']): ?>
                                            <span style="color: #ff6b35; font-size: 12px;">(You)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <?php if ($user['role'] === 'admin'): ?>
                                            <span style="background: #6f42c1; color: white; padding: 3px 10px; border-radius: 15px; font-size: 12px;">
                                                <i class="fas fa-crown"></i> Admin
                                            </span>
                                        <?php else: ?>
                                            <span style="background: #17a2b8; color: white; padding: 3px 10px; border-radius: 15px; font-size: 12px;">
                                                <i class="fas fa-user"></i> User
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $user['order_count']; ?></td>
                                    <td>₹<?php echo number_format($user['total_spent'] ?: 0, 2); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <?php if ($user['id'] !== $_SESSION['user_id'] && $user['role'] !== 'admin'): ?>
                                            <form method="POST" style="display: inline;" 
                                                  onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" name="delete_user" class="btn btn-danger" style="padding: 5px 10px;">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span style="color: #999;">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 30px;">No users found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
