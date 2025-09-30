<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_produccion
 *
 * @copyright   (C) 2024 Grimpsa. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

$app = Factory::getApplication();
$input = $app->input;
$webhookUrl = $input->getString('webhook_url', '');
$webhookSecret = $input->getString('webhook_secret', '');
$webhookEnabled = $input->getString('webhook_enabled', '1');

// Get current configuration from database
$db = Factory::getDbo();
$query = $db->getQuery(true)
    ->select('clave, valor')
    ->from('#__produccion_config')
    ->where('clave IN (' . $db->quote('webhook_url') . ',' . $db->quote('webhook_secret') . ',' . $db->quote('webhook_enabled') . ')');

$db->setQuery($query);
$config = $db->loadObjectList('clave');

$currentUrl = isset($config['webhook_url']) ? $config['webhook_url']->valor : '';
$currentSecret = isset($config['webhook_secret']) ? $config['webhook_secret']->valor : '';
$currentEnabled = isset($config['webhook_enabled']) ? $config['webhook_enabled']->valor : '1';

// Generate webhook URL
$baseUrl = Factory::getApplication()->get('uri')->base();
$webhookEndpoint = $baseUrl . 'index.php?option=com_produccion&task=webhook.receive';
?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-webhook"></i> <?php echo Text::_('COM_PRODUCCION_WEBHOOK_CONFIGURATION'); ?>
                </h3>
            </div>
            <div class="card-body">
                <form action="<?php echo Route::_('index.php?option=com_produccion&task=webhook.save'); ?>" method="post" id="webhook-form">
                    <div class="form-group">
                        <label for="webhook_enabled"><?php echo Text::_('COM_PRODUCCION_WEBHOOK_ENABLED'); ?></label>
                        <select name="webhook_enabled" id="webhook_enabled" class="form-control">
                            <option value="1" <?php echo $currentEnabled == '1' ? 'selected' : ''; ?>><?php echo Text::_('JYES'); ?></option>
                            <option value="0" <?php echo $currentEnabled == '0' ? 'selected' : ''; ?>><?php echo Text::_('JNO'); ?></option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="webhook_url"><?php echo Text::_('COM_PRODUCCION_WEBHOOK_URL'); ?></label>
                        <input type="url" name="webhook_url" id="webhook_url" class="form-control" 
                               value="<?php echo htmlspecialchars($currentUrl); ?>" 
                               placeholder="https://your-external-system.com/webhook">
                        <small class="form-text text-muted"><?php echo Text::_('COM_PRODUCCION_WEBHOOK_URL_DESC'); ?></small>
                    </div>
                    
                    <div class="form-group">
                        <label for="webhook_secret"><?php echo Text::_('COM_PRODUCCION_WEBHOOK_SECRET'); ?></label>
                        <div class="input-group">
                            <input type="password" name="webhook_secret" id="webhook_secret" class="form-control" 
                                   value="<?php echo htmlspecialchars($currentSecret); ?>" readonly>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" onclick="generateSecret()">
                                    <i class="fas fa-sync"></i> <?php echo Text::_('COM_PRODUCCION_GENERATE_SECRET'); ?>
                                </button>
                            </div>
                        </div>
                        <small class="form-text text-muted"><?php echo Text::_('COM_PRODUCCION_WEBHOOK_SECRET_DESC'); ?></small>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> <?php echo Text::_('JSAVE'); ?>
                        </button>
                        <button type="button" class="btn btn-info" onclick="testWebhook()">
                            <i class="fas fa-play"></i> <?php echo Text::_('COM_PRODUCCION_TEST_WEBHOOK'); ?>
                        </button>
                    </div>
                    
                    <?php echo Factory::getSession()->getFormToken(); ?>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5><?php echo Text::_('COM_PRODUCCION_WEBHOOK_INFO'); ?></h5>
            </div>
            <div class="card-body">
                <h6><?php echo Text::_('COM_PRODUCCION_WEBHOOK_ENDPOINT'); ?></h6>
                <code><?php echo $webhookEndpoint; ?></code>
                
                <hr>
                
                <h6><?php echo Text::_('COM_PRODUCCION_WEBHOOK_USAGE'); ?></h6>
                <p><?php echo Text::_('COM_PRODUCCION_WEBHOOK_USAGE_DESC'); ?></p>
                
                <h6><?php echo Text::_('COM_PRODUCCION_WEBHOOK_HEADERS'); ?></h6>
                <ul>
                    <li><code>Content-Type: application/json</code></li>
                    <li><code>X-Webhook-Signature: [HMAC-SHA256]</code></li>
                </ul>
                
                <h6><?php echo Text::_('COM_PRODUCCION_WEBHOOK_SAMPLE'); ?></h6>
                <pre><code>{
  "orden_de_trabajo": "12345",
  "nombre_del_cliente": "Client Name",
  "nit": "12345678-9",
  "descripcion_de_trabajo": "Work description"
}</code></pre>
            </div>
        </div>
    </div>
</div>

<script>
function generateSecret() {
    if (confirm('<?php echo Text::_('COM_PRODUCCION_GENERATE_SECRET_CONFIRM'); ?>')) {
        window.location.href = '<?php echo Route::_('index.php?option=com_produccion&task=webhook.generateSecret'); ?>';
    }
}

function testWebhook() {
    var url = document.getElementById('webhook_url').value;
    var secret = document.getElementById('webhook_secret').value;
    
    if (!url || !secret) {
        alert('<?php echo Text::_('COM_PRODUCCION_WEBHOOK_URL_SECRET_REQUIRED'); ?>');
        return;
    }
    
    if (confirm('<?php echo Text::_('COM_PRODUCCION_TEST_WEBHOOK_CONFIRM'); ?>')) {
        window.location.href = '<?php echo Route::_('index.php?option=com_produccion&task=webhook.testWebhook'); ?>&webhook_url=' + encodeURIComponent(url) + '&webhook_secret=' + encodeURIComponent(secret);
    }
}
</script>
