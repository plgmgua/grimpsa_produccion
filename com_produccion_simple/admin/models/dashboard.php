<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_produccion
 *
 * @copyright   (C) 2024 Grimpsa. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Dashboard model
 */
class ProduccionModelDashboard extends BaseDatabaseModel
{
    /**
     * Get dashboard statistics
     *
     * @return  array
     */
    public function getStats()
    {
        $db = $this->getDatabase();
        
        $stats = [
            'total_orders' => 0,
            'pending_orders' => 0,
            'completed_orders' => 0,
            'total_technicians' => 0
        ];
        
        try {
            // Get total orders
            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from('#__produccion_ordenes');
            $db->setQuery($query);
            $stats['total_orders'] = $db->loadResult();
            
            // Get pending orders
            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from('#__produccion_ordenes')
                ->where('estado = ' . $db->quote('pendiente'));
            $db->setQuery($query);
            $stats['pending_orders'] = $db->loadResult();
            
            // Get completed orders
            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from('#__produccion_ordenes')
                ->where('estado = ' . $db->quote('completado'));
            $db->setQuery($query);
            $stats['completed_orders'] = $db->loadResult();
            
        } catch (Exception $e) {
            // Database might not be ready yet
        }
        
        return $stats;
    }
}
