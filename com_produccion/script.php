<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_produccion
 *
 * @copyright   (C) 2024 Grimpsa. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Factory;

/**
 * Installation script for com_produccion
 * 
 * COMPLETELY SAFE INSTALLER - NO ACL TOUCHING
 * This installer does NOT register any ACL rules to prevent corruption
 */
class Com_ProduccionInstallerScript extends InstallerScript
{
    /**
     * Minimum Joomla version required
     */
    protected $minimumJoomla = '4.0';
    
    /**
     * Minimum PHP version required
     */
    protected $minimumPhp = '7.4';
    
    /**
     * Extension name
     */
    protected $extension = 'com_produccion';
    
    /**
     * Install method - COMPLETELY SAFE
     */
    public function install($adapter)
    {
        // DO NOTHING - Let Joomla handle everything automatically
        // This prevents any ACL corruption issues
        return true;
    }
    
    /**
     * Update method - COMPLETELY SAFE
     */
    public function update($adapter)
    {
        // DO NOTHING - Let Joomla handle everything automatically
        // This prevents any ACL corruption issues
        return true;
    }
    
    /**
     * Uninstall method - COMPLETELY SAFE
     */
    public function uninstall($adapter)
    {
        // DO NOTHING - Let Joomla handle everything automatically
        // This prevents any ACL corruption issues
        return true;
    }
    
    /**
     * Pre-flight check - COMPLETELY SAFE
     */
    public function preflight($type, $adapter)
    {
        // DO NOTHING - Let Joomla handle everything automatically
        return true;
    }
    
    /**
     * Post-flight check - COMPLETELY SAFE
     */
    public function postflight($type, $adapter)
    {
        // DO NOTHING - Let Joomla handle everything automatically
        return true;
    }
}
