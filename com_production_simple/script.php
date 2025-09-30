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
     * Install method
     */
    public function install($adapter)
    {
        $this->logInstallation('INSTALL', $adapter);
        return true;
    }
    
    /**
     * Update method
     */
    public function update($adapter)
    {
        $this->logInstallation('UPDATE', $adapter);
        return true;
    }
    
    /**
     * Uninstall method
     */
    public function uninstall($adapter)
    {
        $this->logInstallation('UNINSTALL', $adapter);
        return true;
    }
    
    /**
     * Pre-flight check
     */
    public function preflight($type, $adapter)
    {
        $this->logInstallation('PREFLIGHT: ' . $type, $adapter);
        return true;
    }
    
    /**
     * Post-flight check
     */
    public function postflight($type, $adapter)
    {
        $this->logInstallation('POSTFLIGHT: ' . $type, $adapter);
        return true;
    }
    
    /**
     * Log installation details
     */
    private function logInstallation($action, $adapter)
    {
        $logFile = JPATH_ADMINISTRATOR . '/logs/com_produccion_install.log';
        $logContent = "\n=== COM_PRODUCCION INSTALLATION LOG ===\n";
        $logContent .= "Action: " . $action . "\n";
        $logContent .= "Time: " . date('Y-m-d H:i:s') . "\n";
        
        if (isset($adapter)) {
            $installer = $adapter->getParent();
            $logContent .= "Install Path: " . $installer->getPath('source') . "\n";
            $logContent .= "Admin Path: " . $installer->getPath('extension_administrator') . "\n";
            $logContent .= "Site Path: " . $installer->getPath('extension_site') . "\n";
        }
        
        $logContent .= "=== END LOG ===\n\n";
        
        // Write to log file
        file_put_contents($logFile, $logContent, FILE_APPEND | LOCK_EX);
    }
}
