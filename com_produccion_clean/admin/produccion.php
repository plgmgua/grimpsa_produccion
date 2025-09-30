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
file_put_contents($debug_log, "=== PRODUCCION.PHP LOADED ===\n" . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

// Get the application
$app = \Joomla\CMS\Factory::getApplication();
$input = $app->input;

// Get the view
$view = $input->getCmd('view', 'dashboard');
$task = $input->getCmd('task', 'display');

// Debug log
file_put_contents($debug_log, "View: $view, Task: $task\n", FILE_APPEND);

// Simple routing
switch ($view) {
    case 'dashboard':
        include_once JPATH_ADMINISTRATOR . '/components/com_produccion/views/dashboard/view.html.php';
        $viewClass = new ProduccionViewDashboard();
        $viewClass->display();
        break;
        
    case 'ordenes':
        include_once JPATH_ADMINISTRATOR . '/components/com_produccion/views/ordenes/view.html.php';
        $viewClass = new ProduccionViewOrdenes();
        $viewClass->display();
        break;
        
    case 'webhook':
        include_once JPATH_ADMINISTRATOR . '/components/com_produccion/views/webhook/view.html.php';
        $viewClass = new ProduccionViewWebhook();
        $viewClass->display();
        break;
        
    default:
        // Default to dashboard
        include_once JPATH_ADMINISTRATOR . '/components/com_produccion/views/dashboard/view.html.php';
        $viewClass = new ProduccionViewDashboard();
        $viewClass->display();
        break;
}