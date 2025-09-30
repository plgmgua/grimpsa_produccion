<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_produccion
 *
 * @copyright   (C) 2024 Grimpsa. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

// Get the input
$input = Factory::getApplication()->input;

// Set the default view if not set
$input->set('view', $input->get('view', 'default'));

// Get the controller
$controller = BaseController::getInstance('Produccion');

// Perform the request task
$controller->execute($input->get('task'));

// Redirect if set by the controller
$controller->redirect();
