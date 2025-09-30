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
file_put_contents($debug_log, "=== ORDENES TEMPLATE LOADED ===\n" . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
?>

<div class="produccion-ordenes">
    <h1>📋 Work Orders Management</h1>
    
    <div class="success">
        <h2>✅ Work Orders View is Working!</h2>
                <p><strong>Version:</strong> 1.0.27</p>
        <p><strong>Current Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
    </div>
    
    <div class="info">
        <h3>📊 Order Statistics</h3>
        <table>
            <tr>
                <th>Status</th>
                <th>Count</th>
            </tr>
            <tr>
                <td>Pending</td>
                <td>0</td>
            </tr>
            <tr>
                <td>In Progress</td>
                <td>0</td>
            </tr>
            <tr>
                <td>Completed</td>
                <td>0</td>
            </tr>
        </table>
    </div>
    
    <h3>🚀 Quick Actions</h3>
    <p>
        <a href="#" style="background: #007cba; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px; margin-right: 10px;">➕ New Order</a>
        <a href="#" style="background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px; margin-right: 10px;">🔍 Search Orders</a>
        <a href="#" style="background: #ffc107; color: black; padding: 10px 15px; text-decoration: none; border-radius: 3px;">📊 Export Orders</a>
    </p>
    
    <hr>
    <p><em>Work Orders management interface is working correctly!</em></p>
</div>

<style>
.produccion-ordenes { font-family: Arial, sans-serif; margin: 20px; }
.produccion-ordenes .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0; }
.produccion-ordenes .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 5px; margin: 20px 0; }
.produccion-ordenes table { width: 100%; border-collapse: collapse; margin: 20px 0; }
.produccion-ordenes th, .produccion-ordenes td { border: 1px solid #ddd; padding: 8px; text-align: left; }
.produccion-ordenes th { background-color: #f2f2f2; }
</style>