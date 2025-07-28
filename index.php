<?php
/**
 * PHP Blog - Internship Task 1
 * Main entry point for the blog application
 *
 * @author Your Name
 * @date <?php echo date('Y-m-d'); ?>
 */

// Start session for user management
session_start();

// Set timezone
date_default_timezone_set('UTC');

// Basic configuration
$config = [
    'site_name' => 'My PHP Blog',
    'version' => '1.0.0',
    'environment' => 'development'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $config['site_name']; ?> - Welcome</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            text-align: center;
            max-width: 600px;
            width: 90%;
        }

        h1 {
            color: #667eea;
            margin-bottom: 1rem;
            font-size: 2.5rem;
        }

        .subtitle {
            color: #666;
            margin-bottom: 2rem;
            font-size: 1.2rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 2rem 0;
        }

        .info-card {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }

        .info-card h3 {
            color: #667eea;
            margin-bottom: 0.5rem;
        }

        .status {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: #28a745;
            color: white;
            border-radius: 20px;
            font-weight: bold;
            margin: 1rem 0;
        }

        .footer {
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo $config['site_name']; ?></h1>
        <p class="subtitle">Internship Task 1 - Development Environment Setup</p>

        <div class="status">
            ✅ Environment Successfully Configured!
        </div>

        <div class="info-grid">
            <div class="info-card">
                <h3>PHP Version</h3>
                <p><?php echo phpversion(); ?></p>
            </div>

            <div class="info-card">
                <h3>Server</h3>
                <p><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></p>
            </div>

            <div class="info-card">
                <h3>Current Time</h3>
                <p><?php echo date('Y-m-d H:i:s'); ?></p>
            </div>

            <div class="info-card">
                <h3>Project Version</h3>
                <p><?php echo $config['version']; ?></p>
            </div>
        </div>

        <div class="info-card">
            <h3>Database Connection Test</h3>
            <p>
                <?php
                // Test MySQL connection (will be configured later)
                try {
                    $host = 'localhost';
                    $dbname = 'test'; // Default MySQL test database
                    $username = 'root';
                    $password = '';

                    $pdo = new PDO("mysql:host=$host", $username, $password);
                    echo "✅ MySQL Connection: <strong style='color: green;'>Success</strong>";
                } catch (PDOException $e) {
                    echo "⚠️ MySQL Connection: <strong style='color: orange;'>Not configured yet</strong>";
                }
                ?>
            </p>
        </div>

        <div class="footer">
            <p><strong>Next Steps:</strong></p>
            <p>1. Configure MySQL database</p>
            <p>2. Set up user authentication</p>
            <p>3. Create blog post management system</p>
            <p>4. Add responsive design</p>
        </div>
    </div>
</body>
</html>