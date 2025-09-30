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
file_put_contents($debug_log, "=== DASHBOARD TEMPLATE LOADED ===\n" . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
?>

<div class="produccion-dashboard">
    <h1>🎉 SUCCESS! Production Management Component is Working!</h1>
    
    <div class="success">
        <h2>✅ Component Status: ACTIVE</h2>
                <p><strong>Version:</strong> 1.0.27</p>
        <p><strong>Current Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
        <p><strong>Joomla Version:</strong> <?php echo JVERSION; ?></p>
        <p><strong>PHP Version:</strong> <?php echo PHP_VERSION; ?></p>
    </div>
    
    <div class="info">
        <h3>📊 Dashboard Features</h3>
        <ul>
            <li>Work Order Management</li>
            <li>Production Tracking</li>
            <li>Technician Assignment</li>
            <li>Webhook Integration</li>
        </ul>
    </div>
    
    <div class="debug">
        <h3>🔧 Debug Information</h3>
        <p><strong>Request URI:</strong> <?php echo $_SERVER['REQUEST_URI']; ?></p>
        <p><strong>Component Path:</strong> <?php echo JPATH_ADMINISTRATOR . '/components/com_produccion'; ?></p>
        <p><strong>Debug Log:</strong> <?php echo JPATH_ADMINISTRATOR . '/logs/com_produccion_debug.log'; ?></p>
    </div>
    
    <h3>🚀 Quick Navigation</h3>
    <p>
        <a href="index.php?option=com_produccion&view=ordenes" style="background: #007cba; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px; margin-right: 10px;">📋 Work Orders</a>
        <a href="index.php?option=com_produccion&view=webhook" style="background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px; margin-right: 10px;">🔗 Webhook Config</a>
        <a href="index.php?option=com_produccion&view=debug" style="background: #dc3545; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px;">🐛 Debug Console</a>
    </p>
    
    <hr>
    <p><em>If you can see this page, the component is working correctly!</em></p>
</div>

<style>
.produccion-dashboard { font-family: Arial, sans-serif; margin: 20px; }
.produccion-dashboard .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0; }
.produccion-dashboard .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 5px; margin: 20px 0; }
.produccion-dashboard .debug { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0; }
</style>