<?php
require_once '../config/database.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add new item
    if (isset($_POST['add_item'])) {
        $name = sanitize($_POST['name']);
        $description = sanitize($_POST['description']);
        $price = floatval($_POST['price']);
        $category = sanitize($_POST['category']);
        $available = isset($_POST['available']) ? 1 : 0;
        
        if (empty($name) || $price <= 0) {
            $error = "Please fill in all required fields.";
        } else {
            $stmt = $conn->prepare("INSERT INTO menu_items (name, description, price, category, available) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdsi", $name, $description, $price, $category, $available);
            if ($stmt->execute()) {
                $success = "Menu item added successfully!";
            } else {
                $error = "Failed to add menu item.";
            }
            $stmt->close();
        }
    }
    
    // Update item
    if (isset($_POST['update_item'])) {
        $id = intval($_POST['id']);
        $name = sanitize($_POST['name']);
        $description = sanitize($_POST['description']);
        $price = floatval($_POST['price']);
        $category = sanitize($_POST['category']);
        $available = isset($_POST['available']) ? 1 : 0;
        
        $stmt = $conn->prepare("UPDATE menu_items SET name = ?, description = ?, price = ?, category = ?, available = ? WHERE id = ?");
        $stmt->bind_param("ssdsii", $name, $description, $price, $category, $available, $id);
        if ($stmt->execute()) {
            $success = "Menu item updated successfully!";
        } else {
            $error = "Failed to update menu item.";
        }
        $stmt->close();
    }
    
    // Delete item
    if (isset($_POST['delete_item'])) {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("DELETE FROM menu_items WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $success = "Menu item deleted successfully!";
        } else {
            $error = "Failed to delete menu item.";
        }
        $stmt->close();
    }
}

// Get menu items
$menu_items = $conn->query("SELECT * FROM menu_items ORDER BY category, name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Menu - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .modal.active {
            display: flex;
        }
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            width: 100%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }
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
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="orders.php"><i class="fas fa-list"></i> Orders</a>
            <a href="menu.php" class="active"><i class="fas fa-utensils"></i> Menu Items</a>
            <a href="users.php"><i class="fas fa-users"></i> Users</a>
        </div>

        <!-- Content -->
        <div class="admin-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h1 style="color: #333;">
                    <i class="fas fa-utensils"></i> Manage Menu
                </h1>
                <button class="btn btn-primary" onclick="openAddModal()">
                    <i class="fas fa-plus"></i> Add Item
                </button>
            </div>

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
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Available</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($menu_items && $menu_items->num_rows > 0): ?>
                            <?php while ($item = $menu_items->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $item['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($item['name']); ?></strong><br>
                                        <small style="color: #666;"><?php echo htmlspecialchars(substr($item['description'], 0, 50)); ?>...</small>
                                    </td>
                                    <td>
                                        <span class="category"><?php echo ucfirst($item['category']); ?></span>
                                    </td>
                                    <td><strong>₹<?php echo number_format($item['price'], 2); ?></strong></td>
                                    <td>
                                        <?php if ($item['available']): ?>
                                            <span style="color: #28a745;"><i class="fas fa-check-circle"></i> Yes</span>
                                        <?php else: ?>
                                            <span style="color: #dc3545;"><i class="fas fa-times-circle"></i> No</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-secondary" style="padding: 5px 10px;" 
                                                onclick="openEditModal(<?php echo htmlspecialchars(json_encode($item)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" style="display: inline;" 
                                              onsubmit="return confirm('Are you sure you want to delete this item?');">
                                            <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                            <button type="submit" name="delete_item" class="btn btn-danger" style="padding: 5px 10px;">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 30px;">No menu items found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Item Modal -->
    <div class="modal" id="addModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-plus"></i> Add Menu Item</h2>
                <button class="close-btn" onclick="closeModal('addModal')">&times;</button>
            </div>
            <form method="POST">
                <div class="form-group">
                    <label>Name *</label>
                    <input type="text" name="name" required placeholder="Item name">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3" placeholder="Item description"></textarea>
                </div>
                <div class="form-group">
                    <label>Price (₹) *</label>
                    <input type="number" name="price" step="0.01" min="0" required placeholder="0.00">
                </div>
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category" required>
                        <option value="breakfast">Breakfast</option>
                        <option value="lunch">Lunch</option>
                        <option value="snacks">Snacks</option>
                        <option value="beverages">Beverages</option>
                        <option value="desserts">Desserts</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="available" checked> Available
                    </label>
                </div>
                <button type="submit" name="add_item" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-plus"></i> Add Item
                </button>
            </form>
        </div>
    </div>

    <!-- Edit Item Modal -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-edit"></i> Edit Menu Item</h2>
                <button class="close-btn" onclick="closeModal('editModal')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group">
                    <label>Name *</label>
                    <input type="text" name="name" id="edit_name" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="edit_description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Price (₹) *</label>
                    <input type="number" name="price" id="edit_price" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category" id="edit_category" required>
                        <option value="breakfast">Breakfast</option>
                        <option value="lunch">Lunch</option>
                        <option value="snacks">Snacks</option>
                        <option value="beverages">Beverages</option>
                        <option value="desserts">Desserts</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="available" id="edit_available"> Available
                    </label>
                </div>
                <button type="submit" name="update_item" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-save"></i> Update Item
                </button>
            </form>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('addModal').classList.add('active');
        }

        function openEditModal(item) {
            document.getElementById('edit_id').value = item.id;
            document.getElementById('edit_name').value = item.name;
            document.getElementById('edit_description').value = item.description;
            document.getElementById('edit_price').value = item.price;
            document.getElementById('edit_category').value = item.category;
            document.getElementById('edit_available').checked = item.available == 1;
            document.getElementById('editModal').classList.add('active');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        // Close modal on outside click
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>
