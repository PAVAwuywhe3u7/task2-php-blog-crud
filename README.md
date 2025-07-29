# PHP Blog Application - Task 2

## 🚀 Project Overview

A complete CRUD blog application built with PHP and MySQL for the Aerospace Internship Project. This application demonstrates professional web development practices including user authentication, secure database operations, and responsive design.

## ✨ Features

### 🔐 User Authentication
- **User Registration** with validation
- **Secure Login** with password hashing
- **Session Management** with CSRF protection
- **Logout** functionality

### 📝 Blog Management (CRUD)
- **Create** new blog posts
- **Read** all posts and individual posts
- **Update** existing posts (author only)
- **Delete** posts (author only)

### 🛡️ Security Features
- Password hashing using `password_hash()` and `password_verify()`
- Prepared statements for SQL injection prevention
- CSRF token protection
- Session security with regeneration
- Input validation and sanitization

### 🎨 User Interface
- Responsive design with Bootstrap 5
- Beautiful gradient themes
- Font Awesome icons
- Flash message system
- Professional layout and typography

## 🛠️ Technologies Used

- **PHP 8+** - Server-side scripting
- **MySQL** - Database management
- **PDO** - Database abstraction layer
- **Bootstrap 5** - CSS framework
- **Font Awesome** - Icons
- **HTML5/CSS3** - Frontend markup and styling

## 📁 Project Structure

```
task2-php-blog/
├── database_setup.sql          # Database schema and sample data
├── index.php                   # Homepage with post listings
├── config/
│   └── database.php           # Database configuration
├── includes/
│   ├── session.php           # Session management functions
│   ├── auth.php              # Authentication functions
│   └── posts.php             # Blog post CRUD functions
├── auth/
│   ├── login.php             # User login page
│   ├── register.php          # User registration page
│   └── logout.php            # Logout functionality
├── posts/
│   ├── create.php            # Create new post
│   ├── view.php              # View single post
│   ├── edit.php              # Edit post
│   ├── delete.php            # Delete post
│   └── my-posts.php          # User's posts management
└── assets/
    ├── css/                  # Custom stylesheets
    └── js/                   # Custom JavaScript
```

## 🚀 Installation & Setup

### Prerequisites
- XAMPP (Apache + MySQL + PHP 8+)
- Web browser
- Text editor (VS Code recommended)

### Step 1: Setup XAMPP
1. Start **Apache** and **MySQL** services in XAMPP Control Panel
2. Ensure both services show **green "Running"** status

### Step 2: Database Setup
1. Open **phpMyAdmin**: `http://localhost/phpmyadmin`
2. Import the database:
   - Click **"Import"** tab
   - Choose file: `database_setup.sql`
   - Click **"Go"**
3. Verify database `php_blog_task2` is created with sample data

### Step 3: Deploy Application
1. Copy project to XAMPP htdocs:
   ```bash
   xcopy "task2-php-blog" "C:\xampp\htdocs\task2-php-blog\" /E /I /Y
   ```

### Step 4: Access Application
1. Open browser and navigate to: `http://localhost/task2-php-blog`
2. You should see the beautiful homepage with blog posts

## 🔑 Demo Credentials

Use these credentials to test the application:

**Admin User:**
- Username: `admin`
- Password: `password123`

**Regular Users:**
- Username: `john_doe` | Password: `password123`
- Username: `jane_smith` | Password: `password123`

## 📖 Usage Guide

### For Visitors (Not Logged In)
- View all blog posts on homepage
- Read individual posts
- Register for new account
- Login to existing account

### For Logged-In Users
- Create new blog posts
- Edit your own posts
- Delete your own posts
- View all your posts in "My Posts"
- Logout securely

## 🗄️ Database Schema

### Users Table
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Posts Table
```sql
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    author_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
);
```

## 🧪 Testing the Application

### Test User Registration
1. Go to `http://localhost/task2-php-blog/auth/register.php`
2. Fill in the registration form
3. Verify account creation and automatic redirect

### Test User Login
1. Go to `http://localhost/task2-php-blog/auth/login.php`
2. Use demo credentials or your registered account
3. Verify successful login and dashboard access

### Test CRUD Operations
1. **Create**: Login and click "Create New Post"
2. **Read**: View posts on homepage and individual post pages
3. **Update**: Edit your own posts from "My Posts" or post view
4. **Delete**: Delete your posts with confirmation modal

### Test Security Features
1. Try accessing protected pages without login
2. Try editing/deleting other users' posts
3. Verify CSRF protection on forms
4. Test input validation on all forms

## 🔧 Configuration

### Database Configuration
Edit `config/database.php` to modify database settings:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'php_blog_task2');
define('DB_USER', 'root');
define('DB_PASS', '');
```

## 🎯 Key Learning Objectives Achieved

1. **PHP Fundamentals**: Variables, functions, classes, includes
2. **Database Operations**: PDO, prepared statements, CRUD operations
3. **Security**: Password hashing, SQL injection prevention, CSRF protection
4. **Session Management**: Login/logout, user state management
5. **Frontend Integration**: HTML, CSS, JavaScript, Bootstrap
6. **Project Structure**: MVC-like organization, separation of concerns

## 🏆 Project Highlights

- **Professional Code Quality**: Clean, documented, and organized
- **Security Best Practices**: Industry-standard security measures
- **Responsive Design**: Works on desktop, tablet, and mobile
- **User Experience**: Intuitive interface with helpful feedback
- **Scalable Architecture**: Easy to extend and maintain

## 📝 Author

**Pavan Karthik Tummepalli**  
Aerospace Internship Project - Task 2  
Built with PHP, MySQL, Bootstrap & ❤️

---

*This project demonstrates complete full-stack web development skills using PHP and MySQL, showcasing both technical proficiency and attention to user experience design.*
