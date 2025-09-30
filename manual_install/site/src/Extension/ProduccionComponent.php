<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_produccion
 *
 * @copyright   (C) 2024 Grimpsa. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Produccion\Site\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\LegacyComponent;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\HTML\Registry;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * The produccion component.
 *
 * @since  1.0.0
 */
class ProduccionComponent extends LegacyComponent implements ComponentInterface
{
    /**
     * The extension namespace.
     *
     * @var string
     */
    protected $namespace = 'Joomla\\Component\\Produccion';

    /**
     * Booting the extension. This is the function to set up the environment of the extension like
     * registering new class loaders, etc.
     *
     * If required, some initial set up can be done from services of the container, eg.
     * registering HTML services.
     *
     * @param   Container  $container  The container
     *
     * @return  void
     *
     * @since   1.0.0
     */
    public function boot(Container $container)
    {
        parent::boot($container);
    }

    /**
     * Render the component
     *
     * @return  string  The component output
     *
     * @since   1.0.0
     */
    public function render()
    {
        // Get the application
        $app = \Joomla\CMS\Factory::getApplication();
        $input = $app->input;
        
        // Get the view
        $view = $input->getCmd('view', 'default');
        
        // Debug logging
        $debug_log = JPATH_ADMINISTRATOR . '/logs/com_produccion_debug.log';
        file_put_contents($debug_log, "=== SITE RENDER METHOD CALLED ===\nView: $view\n" . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
        
        // Simple site routing
        switch ($view) {
            case 'webhook':
                include_once JPATH_SITE . '/components/com_produccion/src/Controller/WebhookController.php';
                $controller = new \Joomla\Component\Produccion\Site\Controller\WebhookController();
                $controller->receive();
                break;
                
            default:
                echo "<h1>Production Management Component - Site</h1>";
                echo "<p>This is the site component. Use the admin interface for management.</p>";
                break;
        }
        
        return '';
    }
}
