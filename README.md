# Online Food Ordering System

A simple web-based food ordering system for campus canteen, built with PHP, MySQL, HTML, CSS, and JavaScript.

## Features

### For Students/Users
- Browse menu items by category (Breakfast, Lunch, Snacks, Beverages, Desserts)
- Add items to cart with quantity management
- Place orders with real-time cart updates
- View order history and track order status
- User registration and authentication

### For Admin
- Dashboard with statistics (total orders, revenue, pending orders)
- Manage menu items (add, edit, delete)
- Update order status (pending → preparing → ready → delivered)
- View and manage registered users

## Tech Stack

- **Frontend:** HTML5, CSS3, JavaScript
- **Backend:** PHP 7+
- **Database:** MySQL
- **Icons:** Font Awesome 6

## Installation

### Prerequisites
- XAMPP/WAMP/MAMP or any PHP development environment
- MySQL database server
- Web browser

### Setup Steps

1. **Clone the repository:**
   ```bash
   git clone https://github.com/rithika5656/Online-Food-Ordering-System.git
   ```

2. **Move to web server directory:**
   - For XAMPP: Move folder to `C:/xampp/htdocs/`
   - For WAMP: Move folder to `C:/wamp/www/`

3. **Create Database:**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named `food_ordering_system`
   - Import the SQL file from `database/food_ordering.sql`

4. **Configure Database Connection:**
   - Open `config/database.php`
   - Update database credentials if needed:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'food_ordering_system');
     ```

5. **Access the Application:**
   - Open browser and go to: `http://localhost/Online-Food-Ordering-System/`

## Default Login Credentials

### Admin Account
- **Username:** admin
- **Password:** admin123

## Project Structure

```
Online-Food-Ordering-System/
├── admin/
│   ├── dashboard.php      # Admin dashboard
│   ├── menu.php           # Manage menu items
│   ├── orders.php         # Manage orders
│   └── users.php          # Manage users
├── assets/
│   ├── css/
│   │   └── style.css      # Main stylesheet
│   ├── js/
│   │   └── main.js        # JavaScript functions
│   └── images/            # Food images
├── config/
│   └── database.php       # Database configuration
├── database/
│   └── food_ordering.sql  # Database schema
├── index.php              # Home page with menu
├── menu.php               # Full menu page
├── login.php              # User login
├── register.php           # User registration
├── logout.php             # Logout handler
├── orders.php             # User order history
├── place_order.php        # Order processing
└── README.md              # This file
```

## Screenshots

### Home Page
- Displays featured menu items
- Category filter buttons
- Shopping cart sidebar

### Admin Dashboard
- Order statistics
- Recent orders table
- Quick navigation

## Future Enhancements

- [ ] Payment gateway integration
- [ ] Email notifications
- [ ] Order pickup time selection
- [ ] Food item reviews and ratings
- [ ] Mobile responsive improvements
- [ ] Real-time order tracking

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is open source and available under the [MIT License](LICENSE).

## Author

**Rithika**

---

⭐ Star this repository if you find it helpful!
