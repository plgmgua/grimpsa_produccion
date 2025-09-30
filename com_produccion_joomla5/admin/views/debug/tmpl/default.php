<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_produccion
 *
 * @copyright   (C) 2024 Grimpsa. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Debug logging
$debug_log = JPATH_ADMINISTRATOR . '/logs/com_produccion_debug.log';
file_put_contents($debug_log, "=== DEBUG TEMPLATE LOADED ===\n" . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

// Get request information
$request_info = [
    'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'Not set',
    'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? 'Not set',
    'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? 'Not set',
    'SERVER_NAME' => $_SERVER['SERVER_NAME'] ?? 'Not set',
    'QUERY_STRING' => $_SERVER['QUERY_STRING'] ?? 'Not set',
    'GET' => $_GET,
    'POST' => $_POST,
    'Current Time' => date('Y-m-d H:i:s'),
    'Joomla Version' => JVERSION,
    'PHP Version' => PHP_VERSION
];

// Check component files
$component_path = JPATH_ADMINISTRATOR . '/components/com_produccion';
$files_status = [];
$files_to_check = [
    'produccion.php',
    'controller.php',
    'views/dashboard/view.html.php',
    'views/ordenes/view.html.php',
    'views/webhook/view.html.php',
    'views/debug/view.html.php'
];

foreach ($files_to_check as $file) {
    $full_path = $component_path . '/' . $file;
    $files_status[$file] = file_exists($full_path) ? 'EXISTS' : 'MISSING';
}

// Check database
$db_status = 'NOT CONNECTED';
try {
    $db = JFactory::getDbo();
    $db_status = 'CONNECTED';
} catch (Exception $e) {
    $db_status = 'ERROR: ' . $e->getMessage();
}
?>

<div class="produccion-debug">
    <h1>🐛 Debug Console - Production Management Component</h1>
    
    <div class="success">
        <h2>✅ Debug Console is Working!</h2>
                <p><strong>Version:</strong> 1.0.27</p>
        <p><strong>Current Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
    </div>
    
    <div class="info">
        <h3>📡 Request Information</h3>
        <table>
            <tr><th>Parameter</th><th>Value</th></tr>
            <?php foreach ($request_info as $key => $value): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($key); ?></strong></td>
                <td><?php echo htmlspecialchars(is_array($value) ? json_encode($value) : $value); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    
    <div class="info">
        <h3>📁 Component Files Status</h3>
        <table>
            <tr><th>File</th><th>Status</th></tr>
            <?php foreach ($files_status as $file => $status): ?>
            <tr>
                <td><?php echo htmlspecialchars($file); ?></td>
                <td class="<?php echo $status === 'EXISTS' ? 'success' : 'error'; ?>">
                    <?php echo $status; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    
    <div class="info">
        <h3>🗄️ Database Status</h3>
        <p><strong>Connection:</strong> <?php echo $db_status; ?></p>
    </div>
    
    <div class="info">
        <h3>📋 Debug Log</h3>
        <p><strong>Log File:</strong> <?php echo $debug_log; ?></p>
        <p><strong>Log Exists:</strong> <?php echo file_exists($debug_log) ? 'YES' : 'NO'; ?></p>
        <?php if (file_exists($debug_log)): ?>
        <p><strong>Log Size:</strong> <?php echo filesize($debug_log); ?> bytes</p>
        <p><strong>Last Modified:</strong> <?php echo date('Y-m-d H:i:s', filemtime($debug_log)); ?></p>
        <?php endif; ?>
    </div>
    
    <div class="info">
        <h3>🔧 System Information</h3>
        <table>
            <tr><td><strong>Component Path:</strong></td><td><?php echo $component_path; ?></td></tr>
            <tr><td><strong>Joomla Root:</strong></td><td><?php echo JPATH_ROOT; ?></td></tr>
            <tr><td><strong>Administrator Path:</strong></td><td><?php echo JPATH_ADMINISTRATOR; ?></td></tr>
            <tr><td><strong>Site Path:</strong></td><td><?php echo JPATH_SITE; ?></td></tr>
        </table>
    </div>
    
    <h3>🚀 Quick Navigation</h3>
    <p>
        <a href="index.php?option=com_produccion&view=dashboard" style="background: #6c757d; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px; margin-right: 10px;">🏠 Dashboard</a>
        <a href="index.php?option=com_produccion&view=ordenes" style="background: #007cba; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px; margin-right: 10px;">📋 Work Orders</a>
        <a href="index.php?option=com_produccion&view=webhook" style="background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px;">🔗 Webhook Config</a>
    </p>
    
    <hr>
    <p><em>Debug console is working correctly! Check the debug log for detailed information.</em></p>
</div>

<style>
.produccion-debug { font-family: Arial, sans-serif; margin: 20px; }
.produccion-debug .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0; }
.produccion-debug .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 5px; margin: 20px 0; }
.produccion-debug table { width: 100%; border-collapse: collapse; margin: 10px 0; }
.produccion-debug th, .produccion-debug td { border: 1px solid #ddd; padding: 8px; text-align: left; }
.produccion-debug th { background-color: #f2f2f2; }
.produccion-debug .success { color: #155724; font-weight: bold; }
.produccion-debug .error { color: #dc3545; font-weight: bold; }
</style>
