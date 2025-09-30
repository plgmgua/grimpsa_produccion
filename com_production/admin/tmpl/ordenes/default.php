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
                    <i class="fas fa-clipboard-list"></i> <?php echo Text::_('COM_PRODUCCION_ORDENES'); ?>
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <?php echo Text::_('COM_PRODUCCION_ORDENES_DESCRIPTION'); ?>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><?php echo Text::_('COM_PRODUCCION_ORDENES_STATS'); ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-primary"><i class="fas fa-clock"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text"><?php echo Text::_('COM_PRODUCCION_PENDING'); ?></span>
                                                <span class="info-box-number">0</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-warning"><i class="fas fa-cog"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text"><?php echo Text::_('COM_PRODUCCION_IN_PROGRESS'); ?></span>
                                                <span class="info-box-number">0</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text"><?php echo Text::_('COM_PRODUCCION_COMPLETED'); ?></span>
                                                <span class="info-box-number">0</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><?php echo Text::_('COM_PRODUCCION_QUICK_ACTIONS'); ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="btn-group-vertical w-100">
                                    <a href="#" class="btn btn-primary mb-2">
                                        <i class="fas fa-plus"></i> <?php echo Text::_('COM_PRODUCCION_NEW_ORDER'); ?>
                                    </a>
                                    <a href="#" class="btn btn-info mb-2">
                                        <i class="fas fa-search"></i> <?php echo Text::_('COM_PRODUCCION_SEARCH_ORDERS'); ?>
                                    </a>
                                    <a href="#" class="btn btn-success mb-2">
                                        <i class="fas fa-download"></i> <?php echo Text::_('COM_PRODUCCION_EXPORT_ORDERS'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><?php echo Text::_('COM_PRODUCCION_RECENT_ORDERS'); ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th><?php echo Text::_('COM_PRODUCCION_ORDER_ID'); ?></th>
                                                <th><?php echo Text::_('COM_PRODUCCION_CLIENT'); ?></th>
                                                <th><?php echo Text::_('COM_PRODUCCION_STATUS'); ?></th>
                                                <th><?php echo Text::_('COM_PRODUCCION_DATE'); ?></th>
                                                <th><?php echo Text::_('COM_PRODUCCION_ACTIONS'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">
                                                    <?php echo Text::_('COM_PRODUCCION_NO_ORDERS_FOUND'); ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
