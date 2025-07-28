# 🚀 Complete Setup Guide - PHP Blog Internship Task 1

## 📋 Overview
This guide provides step-by-step instructions for setting up a complete PHP & MySQL development environment for your internship project.

---

## 🛠️ Step 1: Install XAMPP (Local Server)

### Download & Install
1. **Visit**: [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. **Download**: XAMPP for Windows (latest version with PHP 8.x)
3. **Install**: Run installer as Administrator
4. **Components**: Select Apache, MySQL, PHP, phpMyAdmin

### Start Services
1. Open **XAMPP Control Panel**
2. Click **Start** for:
   - ✅ Apache (Port 80, 443)
   - ✅ MySQL (Port 3306)
3. **Verify**: Green "Running" status for both services

### Test Installation
- Open browser: `http://localhost`
- Should see XAMPP dashboard
- Click **phpMyAdmin** to verify MySQL

---

## 💻 Step 2: Configure VS Code for PHP

### Install VS Code
1. Download from: [https://code.visualstudio.com/](https://code.visualstudio.com/)
2. Install with default settings

### Essential Extensions
Install these extensions (Ctrl+Shift+X):

```
1. PHP Intelephense (bmewburn.vscode-intelephense-client)
2. PHP Debug (xdebug.php-debug)
3. GitLens (eamodio.gitlens)
4. Bracket Pair Colorizer (coenraads.bracket-pair-colorizer)
5. Auto Rename Tag (formulahendry.auto-rename-tag)
6. Live Server (ritwickdey.liveserver)
```

### Configure PHP Path
1. Open VS Code Settings (Ctrl+,)
2. Search: "php executable"
3. Set path: `C:\xampp\php\php.exe`

---

## 🔧 Step 3: Git Configuration

### Install Git
1. Download: [https://git-scm.com/download/win](https://git-scm.com/download/win)
2. Install with default settings
3. Restart VS Code

### Configure Git
Open terminal in VS Code (Ctrl+`) and run:

```bash
git config --global user.name "Your Full Name"
git config --global user.email "your.email@example.com"
git config --global init.defaultBranch main
```

---

## 📁 Step 4: Project Setup

### Create Project Directory
1. Navigate to: `C:\xampp\htdocs\`
2. Create folder: `php-blog`
3. Open folder in VS Code

### Project Structure Created ✅
```
php-blog/
│
├── index.php          # ✅ Main application file
├── README.md          # ✅ Project documentation
├── .gitignore         # ✅ Git ignore rules
├── SETUP_GUIDE.md     # ✅ This setup guide
└── /assets/           # ✅ Static assets
    ├── /css/          # ✅ Stylesheets
    ├── /js/           # ✅ JavaScript files
    └── /images/       # ✅ Image assets
```

---

## 🌐 Step 5: GitHub Repository Setup

### Create GitHub Repository
1. **Login**: [https://github.com](https://github.com)
2. **Click**: "New repository" (green button)
3. **Repository name**: `php-blog-internship`
4. **Description**: `PHP Blog project for Aerospace Internship - Task 1`
5. **Visibility**: Public
6. **Initialize**: ❌ Don't check any boxes
7. **Click**: "Create repository"

### Connect Local Repository
In your project terminal, run these commands:

```bash
# Add remote origin
git remote add origin https://github.com/YOUR_USERNAME/php-blog-internship.git

# Push to GitHub
git branch -M main
git push -u origin main
```

**Replace `YOUR_USERNAME` with your actual GitHub username!**

---

## ✅ Step 6: Verify Everything Works

### Test Local Server
1. **Start XAMPP**: Apache + MySQL running
2. **Open browser**: `http://localhost/php-blog`
3. **Expected**: Beautiful welcome page with:
   - ✅ PHP version display
   - ✅ Server information
   - ✅ Current timestamp
   - ✅ MySQL connection status

### Test Git Integration
```bash
# Check Git status
git status

# View commit history
git log --oneline

# Check remote connection
git remote -v
```

---

## 🎥 Recording Your Demo

### What to Show in Video
1. **XAMPP Control Panel**: Starting Apache & MySQL
2. **VS Code**: Opening project, showing file structure
3. **Browser**: Demonstrating working application
4. **Terminal**: Git commands and GitHub push
5. **GitHub**: Show repository with files

### Professional Tips
- **Clean desktop** before recording
- **Close unnecessary applications**
- **Speak clearly** while demonstrating
- **Show each step** methodically
- **Highlight key features** of your application

---

## 🚨 Troubleshooting

### Common Issues & Solutions

#### XAMPP Port Conflicts
```
Problem: Apache won't start (Port 80 busy)
Solution:
1. Open XAMPP Config for Apache
2. Change port from 80 to 8080
3. Access via: http://localhost:8080/php-blog
```

#### PHP Not Working
```
Problem: PHP code shows as plain text
Solution:
1. Ensure Apache is running
2. File must be in htdocs folder
3. Access via localhost, not file://
```

#### Git Push Issues
```
Problem: Authentication failed
Solution:
1. Use GitHub Personal Access Token
2. Or configure SSH keys
3. Check repository URL is correct
```

---

## 📊 Success Checklist

Mark each item when completed:

- [ ] ✅ XAMPP installed and running
- [ ] ✅ VS Code configured with PHP extensions
- [ ] ✅ Git installed and configured
- [ ] ✅ Project files created
- [ ] ✅ Local Git repository initialized
- [ ] ✅ First commit made
- [ ] ✅ GitHub repository created
- [ ] ✅ Code pushed to GitHub
- [ ] ✅ Application accessible via browser
- [ ] ✅ Demo video recorded

---

## 🎯 Next Steps (Future Tasks)

1. **Database Design**: Create MySQL tables for blog posts
2. **User Authentication**: Login/registration system
3. **CRUD Operations**: Create, read, update, delete posts
4. **Advanced Features**: Comments, categories, search
5. **Deployment**: Host on live server

---

**🎉 Congratulations! You've successfully completed Task 1!**

Your development environment is now professional-grade and ready for advanced PHP development.