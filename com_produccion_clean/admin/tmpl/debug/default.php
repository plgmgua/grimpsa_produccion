<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_produccion
 *
 * @copyright   (C) 2024 Grimpsa. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-bug"></i> <?php echo Text::_('COM_PRODUCCION_DEBUG'); ?>
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-success">
                    <h4><i class="fas fa-check-circle"></i> Component is Working!</h4>
                    <p>If you can see this page, the component is properly installed and functioning.</p>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <h5>Component Information</h5>
                        <ul class="list-group">
                            <li class="list-group-item">
                                <strong>Component:</strong> com_produccion
                            </li>
                            <li class="list-group-item">
                                <strong>View:</strong> debug
                            </li>
                            <li class="list-group-item">
                                <strong>Template:</strong> default
                            </li>
                            <li class="list-group-item">
                                <strong>Joomla Version:</strong> <?php echo JVERSION; ?>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="col-md-6">
                        <h5>System Information</h5>
                        <ul class="list-group">
                            <li class="list-group-item">
                                <strong>PHP Version:</strong> <?php echo PHP_VERSION; ?>
                            </li>
                            <li class="list-group-item">
                                <strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?>
                            </li>
                            <li class="list-group-item">
                                <strong>Current Time:</strong> <?php echo date('Y-m-d H:i:s'); ?>
                            </li>
                            <li class="list-group-item">
                                <strong>User:</strong> <?php echo Factory::getUser()->username; ?>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Database Connection Test</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                try {
                                    $db = Factory::getDbo();
                                    $query = $db->getQuery(true)
                                        ->select('COUNT(*)')
                                        ->from('#__produccion_config');
                                    $db->setQuery($query);
                                    $count = $db->loadResult();
                                    echo '<div class="alert alert-success"><i class="fas fa-check"></i> Database connection successful. Config records: ' . $count . '</div>';
                                } catch (Exception $e) {
                                    echo '<div class="alert alert-danger"><i class="fas fa-times"></i> Database error: ' . $e->getMessage() . '</div>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Component Files Check</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                $files = [
                                    'admin/produccion.php' => JPATH_ADMINISTRATOR . '/components/com_produccion/produccion.php',
                                    'admin/src/Extension/ProduccionComponent.php' => JPATH_ADMINISTRATOR . '/components/com_produccion/src/Extension/ProduccionComponent.php',
                                    'admin/services/provider.php' => JPATH_ADMINISTRATOR . '/components/com_produccion/services/provider.php',
                                    'site/produccion.php' => JPATH_SITE . '/components/com_produccion/produccion.php'
                                ];
                                
                                foreach ($files as $name => $path) {
                                    if (file_exists($path)) {
                                        echo '<div class="alert alert-success"><i class="fas fa-check"></i> ' . $name . ' - OK</div>';
                                    } else {
                                        echo '<div class="alert alert-danger"><i class="fas fa-times"></i> ' . $name . ' - MISSING</div>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
