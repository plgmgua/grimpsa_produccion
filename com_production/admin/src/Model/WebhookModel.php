<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_produccion
 *
 * @copyright   (C) 2024 Grimpsa. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Produccion\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;

/**
 * Webhook model
 */
class WebhookModel extends AdminModel
{
    /**
     * Method to get the record form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form should load its own data (default case).
     *
     * @return  \Joomla\CMS\Form\Form|boolean  A Form object on success, false on failure
     */
    public function getForm($data = [], $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_produccion.webhook', 'webhook', ['control' => 'jform', 'load_data' => $loadData]);

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed  The data for the form.
     */
    protected function loadFormData()
    {
        $data = [];
        $db = Factory::getDbo();
        
        // Load webhook configuration
        $query = $db->getQuery(true)
            ->select('clave, valor')
            ->from('#__produccion_config')
            ->where('clave IN (' . $db->quote('webhook_url') . ',' . $db->quote('webhook_secret') . ',' . $db->quote('webhook_enabled') . ')');
        
        $db->setQuery($query);
        $config = $db->loadObjectList('clave');
        
        foreach ($config as $item) {
            $data[$item->clave] = $item->valor;
        }
        
        return $data;
    }

    /**
     * Method to save the form data.
     *
     * @param   array  $data  The form data.
     *
     * @return  boolean  True on success, False on error.
     */
    public function save($data)
    {
        $db = Factory::getDbo();
        
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
    }
}
