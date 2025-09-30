<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_produccion
 *
 * @copyright   (C) 2024 Grimpsa. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\Router\RouterFactoryInterface;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\Extension\Service\Provider\RouterFactory;
use Joomla\CMS\HTML\Registry;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Form\FormHelper;
use Joomla\Component\Produccion\Administrator\Extension\ProduccionComponent;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * The produccion component service provider.
 *
 * @since  1.0.0
 */
return new class implements ServiceProviderInterface
{
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   1.0.0
     */
    public function register(Container $container)
    {
        $container->registerServiceProvider(new MVCFactory('\\Joomla\\Component\\Produccion'));
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\Joomla\\Component\\Produccion'));
        $container->registerServiceProvider(new RouterFactory('\\Joomla\\Component\\Produccion'));
        
        // Register custom form field types
        FormHelper::addFieldPath(JPATH_ADMINISTRATOR . '/components/com_produccion/src/Field');
        FormHelper::addFieldPrefix('Joomla\\Component\\Produccion\\Administrator\\Field');
        
        // Register form paths
        FormHelper::addFormPath(JPATH_ADMINISTRATOR . '/components/com_produccion/forms');
        
        $container->set(
            ComponentInterface::class,
            function (Container $container) {
                $component = new ProduccionComponent($container->get(ComponentDispatcherFactoryInterface::class));

                $component->setRegistry($container->get(Registry::class));
                $component->setMVCFactory($container->get(MVCFactoryInterface::class));
                $component->setRouterFactory($container->get(RouterFactoryInterface::class));

                return $component;
            }
        );
    }
};
