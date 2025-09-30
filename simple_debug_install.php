<?php
/**
 * Simple Debug Install Script
 * This script can be run directly on your server to debug installation issues
 */

// Security check - only allow from localhost
if (!in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1', 'localhost'])) {
    die('Access denied. This script can only be run from localhost.');
}

echo "<h1>Joomla Component Installation Debug</h1>";
echo "<p>Current Time: " . date('Y-m-d H:i:s') . "</p>";

// Check if we're in a Joomla directory
if (!file_exists('configuration.php')) {
    die('<p style="color: red;">Error: Not in Joomla root directory. Please place this file in your Joomla root.</p>');
}

echo "<h2>1. System Information</h2>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
echo "<p><strong>Server Software:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>Current Directory:</strong> " . getcwd() . "</p>";

echo "<h2>2. Joomla Environment</h2>";
if (file_exists('libraries/src/Version.php')) {
    $version_content = file_get_contents('libraries/src/Version.php');
    if (preg_match('/RELEASE\s*=\s*[\'"]([^\'"]+)[\'"]/', $version_content, $matches)) {
        echo "<p><strong>Joomla Version:</strong> " . $matches[1] . "</p>";
    }
}

echo "<h2>3. Directory Permissions</h2>";
$dirs = ['tmp', 'administrator/tmp', 'cache', 'administrator/cache', 'administrator/components', 'components', 'media'];
foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        $writable = is_writable($dir) ? '✅ Writable' : '❌ Not Writable';
        echo "<p><strong>$dir:</strong> Permissions: $perms, $writable</p>";
    } else {
        echo "<p><strong>$dir:</strong> ❌ Directory not found</p>";
    }
}

echo "<h2>4. Package Analysis</h2>";
$package_files = glob('com_produccion*.zip');
if (!empty($package_files)) {
    $latest_package = $package_files[0];
    echo "<p><strong>Latest Package:</strong> $latest_package</p>";
    
    // Extract and analyze package
    $extract_dir = 'tmp/debug_extract_' . time();
    if (mkdir($extract_dir, 0755, true)) {
        $zip = new ZipArchive();
        if ($zip->open($latest_package) === TRUE) {
            $zip->extractTo($extract_dir);
            $zip->close();
            
            echo "<p><strong>Package Contents:</strong></p>";
            echo "<ul>";
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($extract_dir));
            foreach ($files as $file) {
                if ($file->isFile()) {
                    $relative_path = str_replace($extract_dir . '/', '', $file->getPathname());
                    echo "<li>$relative_path</li>";
                }
            }
            echo "</ul>";
            
            // Check for manifest file
            if (file_exists($extract_dir . '/com_produccion.xml')) {
                echo "<p style='color: green;'>✅ Manifest file found: com_produccion.xml</p>";
            } else {
                echo "<p style='color: red;'>❌ Manifest file NOT found: com_produccion.xml</p>";
                echo "<p>Looking for XML files:</p>";
                $xml_files = glob($extract_dir . '/*.xml');
                foreach ($xml_files as $xml_file) {
                    echo "<p>- " . basename($xml_file) . "</p>";
                }
            }
            
            // Check for entry points
            if (file_exists($extract_dir . '/com_produccion_joomla5/admin/produccion.php')) {
                echo "<p style='color: green;'>✅ Admin entry point found</p>";
            } else {
                echo "<p style='color: red;'>❌ Admin entry point NOT found</p>";
            }
            
            if (file_exists($extract_dir . '/com_produccion_joomla5/site/produccion.php')) {
                echo "<p style='color: green;'>✅ Site entry point found</p>";
            } else {
                echo "<p style='color: red;'>❌ Site entry point NOT found</p>";
            }
            
            // Clean up
            $this->removeDirectory($extract_dir);
        } else {
            echo "<p style='color: red;'>❌ Could not open package file</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Could not create extraction directory</p>";
    }
} else {
    echo "<p style='color: red;'>❌ No component packages found</p>";
}

echo "<h2>5. Temporary Installation Directories</h2>";
$tmp_dirs = ['tmp', 'administrator/tmp'];
foreach ($tmp_dirs as $tmp_dir) {
    if (is_dir($tmp_dir)) {
        $install_dirs = glob($tmp_dir . '/install_*', GLOB_ONLYDIR);
        if (!empty($install_dirs)) {
            echo "<p><strong>$tmp_dir:</strong> Found " . count($install_dirs) . " old installation directories</p>";
            foreach (array_slice($install_dirs, 0, 3) as $install_dir) {
                echo "<p>- " . basename($install_dir) . "</p>";
            }
        } else {
            echo "<p><strong>$tmp_dir:</strong> No old installation directories found</p>";
        }
    }
}

echo "<h2>6. Error Logs</h2>";
$log_files = [
    'administrator/logs/error.php',
    'logs/error.php',
    '/var/log/apache2/error.log'
];

foreach ($log_files as $log_file) {
    if (file_exists($log_file)) {
        echo "<p><strong>$log_file:</strong></p>";
        $recent_lines = array_slice(file($log_file), -10);
        $install_errors = array_filter($recent_lines, function($line) {
            return stripos($line, 'install') !== false || stripos($line, 'copy') !== false || stripos($line, 'xml') !== false;
        });
        
        if (!empty($install_errors)) {
            echo "<pre style='background: #f0f0f0; padding: 10px;'>";
            foreach ($install_errors as $error) {
                echo htmlspecialchars($error);
            }
            echo "</pre>";
        } else {
            echo "<p>No recent installer errors found</p>";
        }
    }
}

echo "<h2>7. Recommendations</h2>";
echo "<ul>";
echo "<li>Ensure the manifest file (com_produccion.xml) is in the root of the zip package</li>";
echo "<li>Verify all file paths in the manifest match the actual package structure</li>";
echo "<li>Check that all directories are writable by the web server user</li>";
echo "<li>Clear any old installation directories in tmp/</li>";
echo "<li>Verify the package contains the expected entry point files</li>";
echo "</ul>";

echo "<hr>";
echo "<p style='color: red; font-weight: bold;'>Security Note: Please delete this file after use!</p>";

// Helper function to remove directory
function removeDirectory($dir) {
    if (is_dir($dir)) {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}
?>
