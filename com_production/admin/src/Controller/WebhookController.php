<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_produccion
 *
 * @copyright   (C) 2024 Grimpsa. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Produccion\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/**
 * Webhook Controller
 */
class WebhookController extends BaseController
{
    /**
     * The default view for the display method.
     *
     * @var string
     */
    protected $default_view = 'webhook';

    /**
     * Generate webhook secret
     *
     * @return  void
     */
    public function generateSecret()
    {
        $this->checkToken();

        $secret = bin2hex(random_bytes(32));
        
        // Save to database
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->update('#__produccion_config')
            ->set('valor = ' . $db->quote($secret))
            ->where('clave = ' . $db->quote('webhook_secret'));
        
        $db->setQuery($query);
        $db->execute();

        $this->setMessage(Text::_('COM_PRODUCCION_WEBHOOK_SECRET_GENERATED'));
        $this->setRedirect(Route::_('index.php?option=com_produccion&view=webhook', false));
    }

    /**
     * Test webhook
     *
     * @return  void
     */
    public function testWebhook()
    {
        $this->checkToken();

        $app = Factory::getApplication();
        $input = $app->input;
        
        $webhookUrl = $input->getString('webhook_url');
        $secret = $input->getString('webhook_secret');
        
        if (empty($webhookUrl) || empty($secret)) {
            $this->setMessage(Text::_('COM_PRODUCCION_WEBHOOK_URL_SECRET_REQUIRED'), 'error');
            $this->setRedirect(Route::_('index.php?option=com_produccion&view=webhook', false));
            return;
        }

        // Create test payload
        $testData = [
            'orden_de_trabajo' => 'TEST001',
            'nombre_del_cliente' => 'Test Client',
            'nit' => '12345678-9',
            'descripcion_de_trabajo' => 'Test work order',
            'fecha_de_solicitud' => date('Y-m-d'),
            'fecha_de_entrega' => date('Y-m-d', strtotime('+7 days'))
        ];

        // Generate HMAC signature
        $payload = json_encode($testData);
        $signature = hash_hmac('sha256', $payload, $secret);

        // Send test request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $webhookUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-Webhook-Signature: ' . $signature
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $this->setMessage(Text::_('COM_PRODUCCION_WEBHOOK_TEST_SUCCESS'));
        } else {
            $this->setMessage(Text::_('COM_PRODUCCION_WEBHOOK_TEST_FAILED') . ' (HTTP ' . $httpCode . ')', 'error');
        }

        $this->setRedirect(Route::_('index.php?option=com_produccion&view=webhook', false));
    }
}
