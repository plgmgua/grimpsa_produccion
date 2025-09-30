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
file_put_contents($debug_log, "=== WEBHOOK TEMPLATE LOADED ===\n" . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
?>

<div class="produccion-webhook">
    <h1>🔗 Webhook Configuration</h1>
    
    <div class="success">
        <h2>✅ Webhook Configuration is Working!</h2>
                <p><strong>Version:</strong> 1.0.27</p>
        <p><strong>Current Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
    </div>
    
    <div class="info">
        <h3>⚙️ Webhook Settings</h3>
        <form>
            <div class="form-group">
                <label for="webhook_enabled">Enable Webhook:</label>
                <select id="webhook_enabled" name="webhook_enabled">
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="webhook_url">Webhook URL:</label>
                <input type="url" id="webhook_url" name="webhook_url" placeholder="https://your-external-system.com/webhook">
            </div>
            
            <div class="form-group">
                <label for="webhook_secret">Webhook Secret:</label>
                <input type="password" id="webhook_secret" name="webhook_secret" placeholder="Your secret key">
            </div>
            
            <button type="submit">💾 Save Configuration</button>
            <button type="button">🧪 Test Webhook</button>
        </form>
    </div>
    
    <div class="info">
        <h3>📡 Webhook Information</h3>
        <p><strong>Endpoint URL:</strong></p>
        <div class="code"><?php echo $_SERVER['HTTP_HOST'] . '/index.php?option=com_produccion&task=webhook.receive'; ?></div>
        
        <p><strong>Public Access:</strong> ✅ No authentication required - accessible to anyone</p>
        
        <p><strong>Required Headers:</strong></p>
        <div class="code">
Content-Type: application/json
        </div>
        
        <p><strong>Note:</strong> No custom headers required! Send simple JSON payload.</p>
    </div>
    
    <h3>🚀 Quick Navigation</h3>
    <p>
        <a href="index.php?option=com_produccion&view=dashboard" style="background: #6c757d; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px; margin-right: 10px;">🏠 Dashboard</a>
        <a href="index.php?option=com_produccion&view=ordenes" style="background: #007cba; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px; margin-right: 10px;">📋 Work Orders</a>
    </p>
    
    <hr>
    <p><em>Webhook configuration interface is working correctly!</em></p>
</div>

<style>
.produccion-webhook { font-family: Arial, sans-serif; margin: 20px; }
.produccion-webhook .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0; }
.produccion-webhook .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 5px; margin: 20px 0; }
.produccion-webhook .form-group { margin: 15px 0; }
.produccion-webhook label { display: block; margin-bottom: 5px; font-weight: bold; }
.produccion-webhook input, .produccion-webhook select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
.produccion-webhook button { background: #007cba; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px; }
.produccion-webhook button:hover { background: #005a87; }
.produccion-webhook .code { background: #f8f9fa; border: 1px solid #e9ecef; padding: 10px; border-radius: 4px; font-family: monospace; }
</style>