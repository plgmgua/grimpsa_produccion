<?php
/**
 * Server Diagnostic Script for Joomla Component Installation
 * Place this file in your Joomla root and access via browser
 */

// Security check
if (!defined('_JEXEC')) {
    define('_JEXEC', 1);
}

// Load Joomla
require_once 'administrator/includes/defines.php';
require_once 'administrator/includes/framework.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;

echo "<h1>Joomla Component Installation Diagnostic</h1>";
echo "<p><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</p>";

// 1. Check Joomla version
echo "<h2>1. Joomla Environment</h2>";
echo "<p><strong>Joomla Version:</strong> " . JVERSION . "</p>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";

// 2. Check directory permissions
echo "<h2>2. Directory Permissions</h2>";
$dirs = [
    'administrator/components',
    'components',
    'tmp',
    'cache',
    'administrator/cache',
    'administrator/tmp'
];

foreach ($dirs as $dir) {
    $path = JPATH_ROOT . '/' . $dir;
    $exists = is_dir($path);
    $writable = $exists ? is_writable($path) : false;
    $perms = $exists ? substr(sprintf('%o', fileperms($path)), -4) : 'N/A';
    
    echo "<p><strong>$dir:</strong> ";
    echo $exists ? "✅ Exists" : "❌ Missing";
    echo " | ";
    echo $writable ? "✅ Writable" : "❌ Not Writable";
    echo " | Perms: $perms</p>";
}

// 3. Check ZIP functionality
echo "<h2>3. ZIP Functionality Test</h2>";
if (class_exists('ZipArchive')) {
    echo "<p>✅ ZipArchive class available</p>";
    
    // Test creating a simple ZIP
    $testZip = JPATH_ROOT . '/tmp/test_zip_' . time() . '.zip';
    $zip = new ZipArchive();
    $result = $zip->open($testZip, ZipArchive::CREATE);
    
    if ($result === TRUE) {
        $zip->addFromString('test.txt', 'Test content');
        $zip->close();
        
        if (file_exists($testZip)) {
            echo "<p>✅ Can create ZIP files</p>";
            unlink($testZip); // Clean up
        } else {
            echo "<p>❌ ZIP file not created</p>";
        }
    } else {
        echo "<p>❌ Cannot create ZIP files (Error: $result)</p>";
    }
} else {
    echo "<p>❌ ZipArchive class not available</p>";
}

// 4. Check temporary directory
echo "<h2>4. Temporary Directory Test</h2>";
$tmpDir = JPATH_ROOT . '/tmp';
$testFile = $tmpDir . '/test_file_' . time() . '.txt';

if (file_put_contents($testFile, 'Test content')) {
    echo "<p>✅ Can write to tmp directory</p>";
    unlink($testFile);
} else {
    echo "<p>❌ Cannot write to tmp directory</p>";
}

// 5. Check upload limits
echo "<h2>5. PHP Upload Limits</h2>";
echo "<p><strong>Upload Max Filesize:</strong> " . ini_get('upload_max_filesize') . "</p>";
echo "<p><strong>Post Max Size:</strong> " . ini_get('post_max_size') . "</p>";
echo "<p><strong>Max Execution Time:</strong> " . ini_get('max_execution_time') . " seconds</p>";
echo "<p><strong>Memory Limit:</strong> " . ini_get('memory_limit') . "</p>";

// 6. Check for existing component
echo "<h2>6. Existing Component Check</h2>";
$adminComponent = JPATH_ROOT . '/administrator/components/com_produccion';
$siteComponent = JPATH_ROOT . '/components/com_produccion';

echo "<p><strong>Admin Component:</strong> " . (is_dir($adminComponent) ? "✅ Exists" : "❌ Missing") . "</p>";
echo "<p><strong>Site Component:</strong> " . (is_dir($siteComponent) ? "✅ Exists" : "❌ Missing") . "</p>";

// 7. Test file extraction
echo "<h2>7. File Extraction Test</h2>";
$testZipPath = JPATH_ROOT . '/com_produccion_v1.0.44_simple_debug.zip';
if (file_exists($testZipPath)) {
    echo "<p>✅ Test package found</p>";
    
    $zip = new ZipArchive();
    if ($zip->open($testZipPath) === TRUE) {
        echo "<p>✅ Can open ZIP package</p>";
        echo "<p><strong>Files in package:</strong></p><ul>";
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            echo "<li>$filename</li>";
        }
        echo "</ul>";
        $zip->close();
    } else {
        echo "<p>❌ Cannot open ZIP package</p>";
    }
} else {
    echo "<p>⚠️ Test package not found - upload it first</p>";
}

// 8. Check error logs
echo "<h2>8. Error Logs</h2>";
$errorLogs = [
    JPATH_ROOT . '/administrator/logs/error.php',
    JPATH_ROOT . '/logs/error.php',
    '/var/log/apache2/error.log',
    '/var/log/php_errors.log'
];

foreach ($errorLogs as $logFile) {
    if (file_exists($logFile)) {
        echo "<p><strong>" . basename($logFile) . ":</strong> ✅ Found</p>";
        $recentErrors = shell_exec("tail -10 '$logFile' 2>/dev/null");
        if ($recentErrors) {
            echo "<pre style='background:#f5f5f5;padding:10px;font-size:12px;'>" . htmlspecialchars($recentErrors) . "</pre>";
        }
    } else {
        echo "<p><strong>" . basename($logFile) . ":</strong> ❌ Not found</p>";
    }
}

echo "<h2>9. Recommendations</h2>";
echo "<ul>";
echo "<li>Check that all directories are writable by the web server user</li>";
echo "<li>Ensure ZIP extension is properly installed</li>";
echo "<li>Check PHP upload limits are adequate</li>";
echo "<li>Verify no existing component conflicts</li>";
echo "<li>Check server error logs for specific errors</li>";
echo "</ul>";

echo "<p><strong>Security Note:</strong> Delete this file after use!</p>";
?>
