<?php
/**
 * Component Installation Verification Script
 * Run this to check if all files are in the correct locations
 */

// Security check
if (!defined('_JEXEC')) {
    define('_JEXEC', 1);
}

// Load Joomla
require_once 'administrator/includes/defines.php';
require_once 'administrator/includes/framework.php';

use Joomla\CMS\Factory;

echo "<h1>Component Installation Verification</h1>";
echo "<p><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Check if component is registered in database
echo "<h2>1. Database Registration Check</h2>";
try {
    $db = Factory::getDbo();
    $query = $db->getQuery(true)
        ->select('extension_id, name, enabled, state')
        ->from('#__extensions')
        ->where('element = ' . $db->quote('com_produccion'))
        ->where('type = ' . $db->quote('component'));
    
    $db->setQuery($query);
    $component = $db->loadObject();
    
    if ($component) {
        echo "<p>✅ Component registered in database (ID: {$component->extension_id})</p>";
        echo "<p><strong>Name:</strong> {$component->name}</p>";
        echo "<p><strong>Enabled:</strong> " . ($component->enabled ? 'Yes' : 'No') . "</p>";
        echo "<p><strong>State:</strong> {$component->state}</p>";
    } else {
        echo "<p>❌ Component not found in database</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
}

// Check file structure
echo "<h2>2. File Structure Check</h2>";

$requiredFiles = [
    'administrator/components/com_produccion/produccion.php',
    'administrator/components/com_produccion/controller.php',
    'administrator/components/com_produccion/com_produccion.xml',
    'administrator/components/com_produccion/config.xml',
    'administrator/components/com_produccion/debug.php',
    'components/com_produccion/produccion.php',
    'components/com_produccion/com_produccion.xml',
    'media/com_produccion/css/com_produccion.css',
    'media/com_produccion/js/com_produccion.js'
];

$requiredFolders = [
    'administrator/components/com_produccion/controllers',
    'administrator/components/com_produccion/models',
    'administrator/components/com_produccion/services',
    'administrator/components/com_produccion/src',
    'administrator/components/com_produccion/language',
    'administrator/components/com_produccion/tmpl',
    'components/com_produccion/services',
    'components/com_produccion/src',
    'components/com_produccion/language'
];

echo "<h3>Required Files:</h3>";
foreach ($requiredFiles as $file) {
    $fullPath = JPATH_ROOT . '/' . $file;
    $exists = file_exists($fullPath);
    $readable = $exists ? is_readable($fullPath) : false;
    $size = $exists ? filesize($fullPath) : 0;
    
    echo "<p>";
    echo $exists ? "✅" : "❌";
    echo " <strong>$file</strong> ";
    echo $exists ? "($size bytes)" : "(Missing)";
    echo $readable ? " - Readable" : " - Not Readable";
    echo "</p>";
}

echo "<h3>Required Folders:</h3>";
foreach ($requiredFolders as $folder) {
    $fullPath = JPATH_ROOT . '/' . $folder;
    $exists = is_dir($fullPath);
    $writable = $exists ? is_writable($fullPath) : false;
    
    echo "<p>";
    echo $exists ? "✅" : "❌";
    echo " <strong>$folder</strong> ";
    echo $exists ? "Directory" : "Missing";
    echo $writable ? " - Writable" : " - Not Writable";
    echo "</p>";
}

// Check permissions
echo "<h2>3. Permissions Check</h2>";
$adminComponent = JPATH_ROOT . '/administrator/components/com_produccion';
$siteComponent = JPATH_ROOT . '/components/com_produccion';
$mediaComponent = JPATH_ROOT . '/media/com_produccion';

$paths = [
    'Admin Component' => $adminComponent,
    'Site Component' => $siteComponent,
    'Media Component' => $mediaComponent
];

foreach ($paths as $name => $path) {
    $exists = is_dir($path);
    $writable = $exists ? is_writable($path) : false;
    $perms = $exists ? substr(sprintf('%o', fileperms($path)), -4) : 'N/A';
    
    echo "<p><strong>$name:</strong> ";
    echo $exists ? "✅ Exists" : "❌ Missing";
    echo " | ";
    echo $writable ? "✅ Writable" : "❌ Not Writable";
    echo " | Perms: $perms</p>";
}

// Test component access
echo "<h2>4. Component Access Test</h2>";
echo "<p><strong>Admin URL:</strong> <a href='/administrator/index.php?option=com_produccion' target='_blank'>/administrator/index.php?option=com_produccion</a></p>";
echo "<p><strong>Site URL:</strong> <a href='/index.php?option=com_produccion' target='_blank'>/index.php?option=com_produccion</a></p>";
echo "<p><strong>Webhook URL:</strong> <a href='/index.php?option=com_produccion&task=webhook.receive' target='_blank'>/index.php?option=com_produccion&task=webhook.receive</a></p>";

// Check for common issues
echo "<h2>5. Common Issues Check</h2>";

// Check if files are in wrong location
$wrongLocations = [
    'administrator/components/com_produccion/admin/',
    'components/com_produccion/site/',
    'media/com_produccion/media/'
];

foreach ($wrongLocations as $wrongPath) {
    if (is_dir(JPATH_ROOT . '/' . $wrongPath)) {
        echo "<p>⚠️ Found files in wrong location: $wrongPath</p>";
    }
}

// Check for duplicate files
$duplicateCheck = [
    'administrator/components/com_produccion/produccion.php',
    'components/com_produccion/produccion.php'
];

foreach ($duplicateCheck as $file) {
    if (file_exists(JPATH_ROOT . '/' . $file)) {
        echo "<p>✅ $file exists</p>";
    } else {
        echo "<p>❌ $file missing</p>";
    }
}

echo "<h2>6. Recommendations</h2>";
echo "<ul>";
echo "<li>If files are missing, upload them from the manual installation package</li>";
echo "<li>If permissions are wrong, run: chown -R www-data:www-data /var/www/grimpsa_webserver/administrator/components/com_produccion</li>";
echo "<li>If component is not enabled, check the database registration</li>";
echo "<li>Clear Joomla cache after making changes</li>";
echo "</ul>";

echo "<p><strong>Security Note:</strong> Delete this file after use!</p>";
?>
