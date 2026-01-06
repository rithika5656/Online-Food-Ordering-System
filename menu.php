<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Campus Food</title>
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
            <?php
            require_once 'config/database.php';
            if (isLoggedIn()): ?>
                <a href="orders.php"><i class="fas fa-list"></i> My Orders</a>
                <?php if (isAdmin()): ?>
                    <a href="admin/dashboard.php"><i class="fas fa-cog"></i> Admin</a>
                <?php endif; ?>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php else: ?>
                <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                <a href="register.php"><i class="fas fa-user-plus"></i> Register</a>
            <?php endif; ?>
            <a href="#" class="cart-icon" onclick="toggleCart(); return false;">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-count">0</span>
            </a>
        </div>
    </nav>

    <div class="container">
        <h1 style="text-align: center; margin: 30px 0; color: #ff6b35;">Our Menu</h1>

        <!-- Category Filter -->
        <div class="category-filter">
            <button class="category-btn active" data-category="all" onclick="filterMenu('all')">All Items</button>
            <button class="category-btn" data-category="breakfast" onclick="filterMenu('breakfast')">🍳 Breakfast</button>
            <button class="category-btn" data-category="lunch" onclick="filterMenu('lunch')">🍛 Lunch</button>
            <button class="category-btn" data-category="snacks" onclick="filterMenu('snacks')">🍟 Snacks</button>
            <button class="category-btn" data-category="beverages" onclick="filterMenu('beverages')">🥤 Beverages</button>
            <button class="category-btn" data-category="desserts" onclick="filterMenu('desserts')">🍰 Desserts</button>
        </div>

        <!-- Menu Grid -->
        <div class="menu-grid">
            <?php
            $sql = "SELECT * FROM menu_items WHERE available = TRUE ORDER BY category, name";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0):
                while ($item = $result->fetch_assoc()):
            ?>
                <div class="menu-card" data-category="<?php echo htmlspecialchars($item['category']); ?>">
                    <img src="assets/images/<?php echo htmlspecialchars($item['image']); ?>" 
                         alt="<?php echo htmlspecialchars($item['name']); ?>"
                         onerror="this.src='assets/images/default.jpg'">
                    <div class="card-content">
                        <span class="category"><?php echo ucfirst(htmlspecialchars($item['category'])); ?></span>
                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                        <p><?php echo htmlspecialchars($item['description']); ?></p>
                        <div class="card-footer">
                            <span class="price">₹<?php echo number_format($item['price'], 2); ?></span>
                            <button class="btn btn-primary" 
                                    onclick="addToCart(<?php echo $item['id']; ?>, '<?php echo addslashes($item['name']); ?>', <?php echo $item['price']; ?>)">
                                <i class="fas fa-plus"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
            <?php
                endwhile;
            else:
            ?>
                <div class="empty-state" style="grid-column: 1 / -1;">
                    <i class="fas fa-utensils"></i>
                    <p>No menu items available at the moment.</p>
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
            <?php if (isLoggedIn()): ?>
                <button class="btn btn-primary" style="width:100%" onclick="placeOrder()">
                    <i class="fas fa-check"></i> Place Order
                </button>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary" style="width:100%;display:block;text-align:center;text-decoration:none;">
                    <i class="fas fa-sign-in-alt"></i> Login to Order
                </a>
            <?php endif; ?>
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
