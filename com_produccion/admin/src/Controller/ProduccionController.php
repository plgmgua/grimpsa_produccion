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

/**
 * Produccion Controller
 */
class ProduccionController extends BaseController
{
    /**
     * The default view for the display method.
     *
     * @var string
     */
    protected $default_view = 'dashboard';

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
        $view = Factory::getApplication()->input->getCmd('view', 'dashboard');
        Factory::getApplication()->input->set('view', $view);

        return parent::display($cachable, $urlparams);
    }
}
