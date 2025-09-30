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
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-tachometer-alt"></i> <?php echo Text::_('COM_PRODUCCION_DASHBOARD'); ?>
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-clipboard-list"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text"><?php echo Text::_('COM_PRODUCCION_ORDENES'); ?></span>
                                <span class="info-box-number">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-users"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text"><?php echo Text::_('COM_PRODUCCION_TECNICOS'); ?></span>
                                <span class="info-box-number">0</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><?php echo Text::_('COM_PRODUCCION_WEBHOOK'); ?></h5>
                            </div>
                            <div class="card-body">
                                <p><?php echo Text::_('COM_PRODUCCION_WEBHOOK_DESCRIPTION'); ?></p>
                                <a href="index.php?option=com_produccion&view=webhook" class="btn btn-primary">
                                    <i class="fas fa-cog"></i> <?php echo Text::_('COM_PRODUCCION_WEBHOOK'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
