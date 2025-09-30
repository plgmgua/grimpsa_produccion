<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_produccion
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

// Access check
$user = Factory::getApplication()->getIdentity();
if (!$user->authorise('core.manage', 'com_produccion')) {
    throw new InvalidArgumentException(Text::_('JERROR_ALERTNOAUTHOR'), 404);
}
