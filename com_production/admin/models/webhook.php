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
 * Webhook model
 */
class ProduccionModelWebhook extends BaseDatabaseModel
{
    /**
     * Get webhook configuration
     *
     * @return  array
     */
    public function getConfig()
    {
        $db = $this->getDatabase();
        $config = [];
        
        try {
            $query = $db->getQuery(true)
                ->select('clave, valor')
                ->from('#__produccion_config')
                ->where('clave IN (' . $db->quote('webhook_url') . ',' . $db->quote('webhook_secret') . ',' . $db->quote('webhook_enabled') . ')');
            
            $db->setQuery($query);
            $results = $db->loadObjectList('clave');
            
            foreach ($results as $item) {
                $config[$item->clave] = $item->valor;
            }
        } catch (Exception $e) {
            // Database might not be ready yet
        }
        
        return $config;
    }
    
    /**
     * Save webhook configuration
     *
     * @param   array  $data  Configuration data
     *
     * @return  boolean
     */
    public function saveConfig($data)
    {
        $db = $this->getDatabase();
        
        try {
            foreach ($data as $key => $value) {
                if (in_array($key, ['webhook_url', 'webhook_secret', 'webhook_enabled'])) {
                    $query = $db->getQuery(true)
                        ->update('#__produccion_config')
                        ->set('valor = ' . $db->quote($value))
                        ->where('clave = ' . $db->quote($key));
                    
                    $db->setQuery($query);
                    $db->execute();
                }
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
