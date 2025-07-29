# 🚀 Task 5: Final Project Setup Guide

**Complete Installation and Configuration Guide for the Final Blog Certification Project**

## 📋 Prerequisites

### Required Software
- **XAMPP** (Apache + MySQL + PHP 8.0+)
- **Web Browser** (Chrome, Firefox, Safari, Edge)
- **Git** (for version control)
- **Text Editor** (VS Code, Sublime Text, etc.)

### System Requirements
- **Operating System**: Windows 10/11, macOS, or Linux
- **RAM**: Minimum 4GB (8GB recommended)
- **Storage**: 500MB free space
- **Network**: Internet connection for CDN resources

## 🔧 Installation Steps

### Step 1: Download and Setup XAMPP

1. **Download XAMPP**
   - Visit: https://www.apachefriends.org/
   - Download XAMPP for your operating system
   - Choose version with PHP 8.0 or higher

2. **Install XAMPP**
   - Run the installer as administrator
   - Install to default location: `C:\xampp` (Windows)
   - Select Apache, MySQL, and PHP components

3. **Start XAMPP Services**
   - Open XAMPP Control Panel
   - Start **Apache** service (should show green "Running")
   - Start **MySQL** service (should show green "Running")
   - Verify by visiting: `http://localhost`

### Step 2: Clone the Project

```bash
# Clone the repository
git clone https://github.com/PAVAwuywhe3u7/task5-final-blog-certification.git

# Navigate to project directory
cd task5-final-blog-certification
```

### Step 3: Deploy to XAMPP

**Windows:**
```cmd
xcopy task5-final-blog-certification C:\xampp\htdocs\task5-final-blog-certification /E /I /Y
```

**macOS/Linux:**
```bash
cp -r task5-final-blog-certification /Applications/XAMPP/htdocs/
```

### Step 4: Database Setup

1. **Access phpMyAdmin**
   - Open browser and go to: `http://localhost/phpmyadmin`
   - Login with username: `root` (no password by default)

2. **Create Database**
   - Click "New" in the left sidebar
   - Database name: `php_blog_final`
   - Collation: `utf8mb4_unicode_ci`
   - Click "Create"

3. **Import Database Schema**
   - Select the `php_blog_final` database
   - Click "Import" tab
   - Choose file: `database_setup.sql` from project root
   - Click "Go" to import

4. **Verify Database Setup**
   - Check that these tables were created:
     - `users` (5 sample users)
     - `posts` (5 sample posts)
     - `categories` (6 categories)
     - `comments` (5 sample comments)
     - `user_sessions` (empty)
     - `post_views` (sample data)

### Step 5: Configuration

1. **Verify Configuration**
   - Open: `config/config.php`
   - Ensure database settings match your setup:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'php_blog_final');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     ```

2. **Set Permissions** (Linux/macOS only)
   ```bash
   chmod 755 -R task5-final-blog-certification/
   chmod 777 task5-final-blog-certification/assets/uploads/
   chmod 777 task5-final-blog-certification/logs/
   chmod 777 task5-final-blog-certification/cache/
   ```

### Step 6: Test Installation

1. **Access Application**
   - Homepage: `http://localhost/task5-final-blog-certification/public/`
   - Should display the blog homepage with sample posts

2. **Run Test Suite**
   - Tests: `http://localhost/task5-final-blog-certification/tests/comprehensive-tests.php`
   - All tests should pass (green status)

3. **Test Login**
   - Login page: `http://localhost/task5-final-blog-certification/public/login.php`
   - Use demo credentials:
     - Admin: `admin` / `AdminPass123!`
     - Editor: `editor` / `EditorPass123!`

## 🔑 Demo Credentials

| Role | Username | Password | Access Level |
|------|----------|----------|--------------|
| **Administrator** | `admin` | `AdminPass123!` | Full system access |
| **Editor** | `editor` | `EditorPass123!` | Content management |
| **Editor** | `john_doe` | `JohnPass123!` | Content management |
| **User** | `user` | `UserPass123!` | Basic access |
| **User** | `jane_smith` | `JanePass123!` | Basic access |

## 🧪 Testing Checklist

### Functional Tests
- [ ] Homepage loads with posts and pagination
- [ ] Search functionality works
- [ ] User registration and login
- [ ] Post creation and editing (Editor/Admin)
- [ ] User dashboard and profile
- [ ] Admin panel access (Admin only)
- [ ] Responsive design on mobile

### Security Tests
- [ ] CSRF protection on forms
- [ ] XSS prevention in inputs
- [ ] SQL injection prevention
- [ ] Password strength validation
- [ ] Session security and timeout
- [ ] Role-based access control

### Performance Tests
- [ ] Page load times under 2 seconds
- [ ] Database queries optimized
- [ ] Image loading and optimization
- [ ] Mobile responsiveness

## 🔧 Troubleshooting

### Common Issues

**1. "Database connection failed"**
- Ensure MySQL is running in XAMPP
- Check database credentials in `config/config.php`
- Verify database `php_blog_final` exists

**2. "Page not found" errors**
- Check Apache is running in XAMPP
- Verify files are in correct XAMPP directory
- Check file permissions (Linux/macOS)

**3. "Permission denied" errors**
- Set proper file permissions
- Ensure uploads directory is writable
- Check Apache user permissions

**4. "Session errors"**
- Clear browser cookies and cache
- Restart Apache service
- Check session directory permissions

**5. "CSS/JS not loading"**
- Check internet connection (CDN resources)
- Verify file paths in templates
- Clear browser cache

### Debug Mode

Enable debug mode for detailed error messages:

1. Edit `config/config.php`
2. Add at the top:
   ```php
   define('DEVELOPMENT', true);
   ```
3. Refresh the page to see detailed errors

### Log Files

Check these log files for errors:
- `logs/error.log` - PHP errors
- `logs/security.log` - Security events
- XAMPP logs in `C:\xampp\apache\logs\`

## 🚀 Production Deployment

### Security Checklist
- [ ] Change default database passwords
- [ ] Enable HTTPS/SSL
- [ ] Set secure session settings
- [ ] Configure proper file permissions
- [ ] Enable error logging (disable display)
- [ ] Set up regular database backups

### Performance Optimization
- [ ] Enable PHP OPcache
- [ ] Configure MySQL query cache
- [ ] Optimize images and assets
- [ ] Set up CDN for static files
- [ ] Enable gzip compression

## 📞 Support

### Getting Help
- **Documentation**: Check README.md for detailed information
- **Test Suite**: Run comprehensive tests to identify issues
- **Debug Mode**: Enable for detailed error messages
- **Log Files**: Check application and server logs

### Common Resources
- **XAMPP Documentation**: https://www.apachefriends.org/docs/
- **PHP Manual**: https://www.php.net/manual/
- **MySQL Documentation**: https://dev.mysql.com/doc/
- **Bootstrap Documentation**: https://getbootstrap.com/docs/

## ✅ Success Verification

Your installation is successful when:

1. ✅ **Homepage loads** with sample blog posts
2. ✅ **Login works** with demo credentials
3. ✅ **Dashboard accessible** after login
4. ✅ **Test suite passes** all security and functionality tests
5. ✅ **Post creation works** for Editor/Admin users
6. ✅ **Search and pagination** function correctly
7. ✅ **Responsive design** works on mobile devices

## 🎯 Next Steps

After successful installation:

1. **Explore Features**: Test all functionality with demo accounts
2. **Create Content**: Add your own blog posts and categories
3. **Customize Design**: Modify CSS and templates as needed
4. **Security Review**: Run security tests and review logs
5. **Performance Testing**: Test with larger datasets
6. **Documentation**: Review code and architecture
7. **Portfolio Preparation**: Prepare for demonstration

---

**🎉 Congratulations! You have successfully set up the Task 5 Final Project & Certification blog application. This represents the culmination of comprehensive PHP web development training.**
