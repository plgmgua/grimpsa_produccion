<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_produccion
 *
 * @copyright   (C) 2024 Grimpsa. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log debug information
$debug_log = JPATH_ADMINISTRATOR . '/logs/com_produccion_debug.log';
$debug_info = [];

$debug_info[] = "=== PRODUCCION COMPONENT DEBUG ===";
$debug_info[] = "Timestamp: " . date('Y-m-d H:i:s');
$debug_info[] = "Joomla Version: " . JVERSION;
$debug_info[] = "PHP Version: " . PHP_VERSION;
$debug_info[] = "Request URI: " . $_SERVER['REQUEST_URI'];
$debug_info[] = "Option: " . (isset($_GET['option']) ? $_GET['option'] : 'not set');
$debug_info[] = "View: " . (isset($_GET['view']) ? $_GET['view'] : 'not set');
$debug_info[] = "Task: " . (isset($_GET['task']) ? $_GET['task'] : 'not set');

// Check if component files exist
$component_path = JPATH_ADMINISTRATOR . '/components/com_produccion';
$debug_info[] = "Component Path: " . $component_path;
$debug_info[] = "Component Exists: " . (is_dir($component_path) ? 'YES' : 'NO');

// Check specific files
$files_to_check = [
    'produccion.php',
    'controller.php',
    'views/dashboard/view.html.php',
    'views/ordenes/view.html.php',
    'views/webhook/view.html.php',
    'views/dashboard/tmpl/default.php',
    'views/ordenes/tmpl/default.php',
    'views/webhook/tmpl/default.php'
];

foreach ($files_to_check as $file) {
    $full_path = $component_path . '/' . $file;
    $debug_info[] = "File: $file - " . (file_exists($full_path) ? 'EXISTS' : 'MISSING');
}

// Check if we can load Joomla classes
try {
    $debug_info[] = "Joomla Factory: " . (class_exists('Joomla\CMS\Factory') ? 'LOADED' : 'NOT LOADED');
    $debug_info[] = "Joomla Application: " . (class_exists('Joomla\CMS\Application\CMSApplication') ? 'LOADED' : 'NOT LOADED');
} catch (Exception $e) {
    $debug_info[] = "Joomla Classes Error: " . $e->getMessage();
}

// Check database connection
try {
    $db = JFactory::getDbo();
    $debug_info[] = "Database: CONNECTED";
    
    // Check if our tables exist
    $tables = ['#__produccion_ordenes', '#__produccion_config'];
    foreach ($tables as $table) {
        $query = "SHOW TABLES LIKE '" . str_replace('#__', $db->getPrefix(), $table) . "'";
        $db->setQuery($query);
        $result = $db->loadResult();
        $debug_info[] = "Table $table: " . ($result ? 'EXISTS' : 'MISSING');
    }
} catch (Exception $e) {
    $debug_info[] = "Database Error: " . $e->getMessage();
}

// Write debug log
file_put_contents($debug_log, implode("\n", $debug_info) . "\n\n", FILE_APPEND);

// Display debug information
echo "<h1>PRODUCCION COMPONENT DEBUG</h1>";
echo "<pre>";
echo implode("\n", $debug_info);
echo "</pre>";

// Try to load the component manually
echo "<h2>Manual Component Test</h2>";
echo "<p>Attempting to load component manually...</p>";

try {
    // Include the main component file
    if (file_exists($component_path . '/produccion.php')) {
        echo "<p>Loading produccion.php...</p>";
        include_once $component_path . '/produccion.php';
    } else {
        echo "<p>ERROR: produccion.php not found!</p>";
    }
    
    // Try to load controller
    if (file_exists($component_path . '/controller.php')) {
        echo "<p>Loading controller.php...</p>";
        include_once $component_path . '/controller.php';
    } else {
        echo "<p>ERROR: controller.php not found!</p>";
    }
    
} catch (Exception $e) {
    echo "<p>ERROR: " . $e->getMessage() . "</p>";
}

echo "<p><strong>Debug log written to:</strong> $debug_log</p>";
?>
