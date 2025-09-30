<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_produccion
 *
 * @copyright   (C) 2024 Grimpsa. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Ordenes controller
 */
class ProduccionControllerOrdenes extends BaseController
{
    /**
     * The default view for the display method.
     *
     * @var string
     */
    protected $default_view = 'ordenes';

    /**
     * Display method
     *
     * @param   boolean  $cachable   If true, the view output will be cached
     * @param   array    $urlparams  An array of safe URL parameters and their variable types
     *
     * @return  \Joomla\CMS\MVC\Controller\BaseController  This object to support chaining.
     */
    public function display($cachable = false, $urlparams = [])
    {
        return parent::display($cachable, $urlparams);
    }
}
