<?php
/**
 * Manual Component Registration Script
 * Run this script to register com_produccion in Joomla database
 */

// Security check
if (!defined('_JEXEC')) {
    define('_JEXEC', 1);
}

// Load Joomla
require_once 'administrator/includes/defines.php';
require_once 'administrator/includes/framework.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Table\Extension;

echo "<h1>Manual Component Registration</h1>";
echo "<p><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</p>";

try {
    $db = Factory::getDbo();
    
    // Check if component already exists
    $query = $db->getQuery(true)
        ->select('extension_id')
        ->from('#__extensions')
        ->where('element = ' . $db->quote('com_produccion'))
        ->where('type = ' . $db->quote('component'));
    
    $db->setQuery($query);
    $existing = $db->loadResult();
    
    if ($existing) {
        echo "<p>⚠️ Component already exists (ID: $existing). Updating...</p>";
        
        // Update existing component
        $query = $db->getQuery(true)
            ->update('#__extensions')
            ->set('enabled = 1')
            ->set('state = 0')
            ->where('extension_id = ' . (int)$existing);
        
        $db->setQuery($query);
        $db->execute();
        
        echo "<p>✅ Component updated successfully!</p>";
    } else {
        echo "<p>📝 Registering new component...</p>";
        
        // Insert into extensions table
        $extension = new Extension($db);
        $extension->name = 'com_produccion';
        $extension->type = 'component';
        $extension->element = 'com_produccion';
        $extension->folder = '';
        $extension->client_id = 1; // Admin
        $extension->enabled = 1;
        $extension->access = 1;
        $extension->protected = 0;
        $extension->manifest_cache = json_encode([
            'name' => 'com_produccion',
            'type' => 'component',
            'creationDate' => '2024-12-18',
            'author' => 'Grimpsa',
            'copyright' => 'Copyright (C) 2024 Grimpsa. All rights reserved.',
            'authorEmail' => 'info@grimpsa.com',
            'authorUrl' => 'https://grimpsa.com',
            'version' => '1.0.44',
            'description' => 'COM_PRODUCCION_XML_DESCRIPTION'
        ]);
        $extension->params = '{}';
        $extension->custom_data = '';
        $extension->system_data = '';
        $extension->checked_out = 0;
        $extension->checked_out_time = '0000-00-00 00:00:00';
        $extension->ordering = 0;
        $extension->state = 0;
        
        if ($extension->store()) {
            echo "<p>✅ Component registered successfully! (ID: " . $extension->extension_id . ")</p>";
        } else {
            echo "<p>❌ Failed to register component: " . $extension->getError() . "</p>";
        }
    }
    
    // Clear Joomla cache
    $cache = Factory::getCache();
    $cache->clean('com_produccion');
    $cache->clean('_system');
    
    echo "<p>✅ Cache cleared</p>";
    
    // Check if files exist
    echo "<h2>File Structure Check</h2>";
    $adminPath = JPATH_ROOT . '/administrator/components/com_produccion';
    $sitePath = JPATH_ROOT . '/components/com_produccion';
    
    echo "<p><strong>Admin Component:</strong> " . (is_dir($adminPath) ? "✅ Exists" : "❌ Missing") . "</p>";
    echo "<p><strong>Site Component:</strong> " . (is_dir($sitePath) ? "✅ Exists" : "❌ Missing") . "</p>";
    
    if (is_dir($adminPath)) {
        echo "<p><strong>Admin Files:</strong></p><ul>";
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($adminPath));
        foreach ($files as $file) {
            if ($file->isFile()) {
                $relativePath = str_replace($adminPath . '/', '', $file->getPathname());
                echo "<li>$relativePath</li>";
            }
        }
        echo "</ul>";
    }
    
    echo "<h2>Next Steps</h2>";
    echo "<ul>";
    echo "<li>1. Upload all component files to the correct directories</li>";
    echo "<li>2. Set proper file permissions (755 for directories, 644 for files)</li>";
    echo "<li>3. Test the component by accessing: /administrator/index.php?option=com_produccion</li>";
    echo "<li>4. Delete this registration script for security</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<p><strong>Security Note:</strong> Delete this file after use!</p>";
?>
