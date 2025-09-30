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
     * Install method - WITH VALIDATION AND DEBUGGING
     */
    public function install($adapter)
    {
        $this->validateInstallation($adapter);
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
     * Post-flight check - WITH VALIDATION
     */
    public function postflight($type, $adapter)
    {
        $this->validateInstallation($adapter);
        return true;
    }
    
    /**
     * Validate installation and debug file locations
     */
    private function validateInstallation($adapter)
    {
        $logFile = JPATH_ADMINISTRATOR . '/logs/com_produccion_debug.log';
        $logContent = "\n=== COM_PRODUCCION INSTALLATION DEBUG ===\n";
        $logContent .= "Time: " . date('Y-m-d H:i:s') . "\n";
        $logContent .= "Installation Type: " . (isset($adapter) ? get_class($adapter) : 'Unknown') . "\n";
        
        // Get installation paths
        $installer = $adapter->getParent();
        $installPath = $installer->getPath('source');
        $adminPath = $installer->getPath('extension_administrator');
        $sitePath = $installer->getPath('extension_site');
        
        $logContent .= "Install Path: " . $installPath . "\n";
        $logContent .= "Admin Path: " . $adminPath . "\n";
        $logContent .= "Site Path: " . $sitePath . "\n";
        
        // Check if SQL files exist in expected locations
        $sqlFiles = [
            'admin/sql/install.mysql.utf8.sql',
            'admin/sql/uninstall.mysql.utf8.sql'
        ];
        
        $logContent .= "\n=== SQL FILE VALIDATION ===\n";
        foreach ($sqlFiles as $sqlFile) {
            $fullPath = $installPath . '/' . $sqlFile;
            $exists = file_exists($fullPath);
            $logContent .= "SQL File: " . $sqlFile . "\n";
            $logContent .= "Full Path: " . $fullPath . "\n";
            $logContent .= "Exists: " . ($exists ? 'YES' : 'NO') . "\n";
            $logContent .= "Size: " . ($exists ? filesize($fullPath) . ' bytes' : 'N/A') . "\n";
            $logContent .= "Readable: " . ($exists ? (is_readable($fullPath) ? 'YES' : 'NO') : 'N/A') . "\n";
            $logContent .= "---\n";
        }
        
        // Check package contents
        $logContent .= "\n=== PACKAGE CONTENTS DEBUG ===\n";
        $logContent .= "Installation directory contents:\n";
        if (is_dir($installPath)) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($installPath));
            foreach ($files as $file) {
                if ($file->isFile()) {
                    $relativePath = str_replace($installPath . '/', '', $file->getPathname());
                    $logContent .= "- " . $relativePath . " (" . $file->getSize() . " bytes)\n";
                }
            }
        } else {
            $logContent .= "Installation directory does not exist!\n";
        }
        
        // Check for SQL files anywhere in the package
        $logContent .= "\n=== SQL FILES SEARCH ===\n";
        if (is_dir($installPath)) {
            $sqlFilesFound = [];
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($installPath));
            foreach ($iterator as $file) {
                if ($file->isFile() && preg_match('/\.sql$/i', $file->getFilename())) {
                    $relativePath = str_replace($installPath . '/', '', $file->getPathname());
                    $sqlFilesFound[] = $relativePath;
                }
            }
            
            if (empty($sqlFilesFound)) {
                $logContent .= "❌ NO SQL FILES FOUND IN PACKAGE!\n";
            } else {
                $logContent .= "✅ SQL files found:\n";
                foreach ($sqlFilesFound as $sqlFile) {
                    $logContent .= "- " . $sqlFile . "\n";
                }
            }
        }
        
        // Check manifest file
        $logContent .= "\n=== MANIFEST VALIDATION ===\n";
        $manifestPath = $installPath . '/com_produccion.xml';
        $logContent .= "Manifest Path: " . $manifestPath . "\n";
        $logContent .= "Manifest Exists: " . (file_exists($manifestPath) ? 'YES' : 'NO') . "\n";
        
        if (file_exists($manifestPath)) {
            $manifestContent = file_get_contents($manifestPath);
            $logContent .= "Manifest Size: " . strlen($manifestContent) . " bytes\n";
            
            // Check for SQL references in manifest
            if (strpos($manifestContent, 'admin/sql/install.mysql.utf8.sql') !== false) {
                $logContent .= "✅ Manifest contains correct SQL path: admin/sql/install.mysql.utf8.sql\n";
            } else {
                $logContent .= "❌ Manifest does NOT contain correct SQL path!\n";
            }
        }
        
        $logContent .= "\n=== END DEBUG ===\n\n";
        
        // Write to log file
        file_put_contents($logFile, $logContent, FILE_APPEND | LOCK_EX);
        
        // Also try to write to Joomla's error log
        $joomlaLogFile = JPATH_ADMINISTRATOR . '/logs/error.php';
        if (is_writable(dirname($joomlaLogFile))) {
            $joomlaLogContent = "\n" . date('Y-m-d H:i:s') . " - COM_PRODUCCION DEBUG:\n" . $logContent;
            file_put_contents($joomlaLogFile, $joomlaLogContent, FILE_APPEND | LOCK_EX);
        }
    }
}
