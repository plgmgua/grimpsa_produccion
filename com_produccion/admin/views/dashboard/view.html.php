<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_produccion
 *
 * @copyright   (C) 2024 Grimpsa. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Debug logging
$debug_log = JPATH_ADMINISTRATOR . '/logs/com_produccion_debug.log';
file_put_contents($debug_log, "=== DASHBOARD VIEW LOADED ===\n" . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

/**
 * Dashboard view
 */
class ProduccionViewDashboard
{
    /**
     * Display the view
     *
     * @param   string  $tpl  The name of the template file to parse
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        // Debug log
        $debug_log = JPATH_ADMINISTRATOR . '/logs/com_produccion_debug.log';
        file_put_contents($debug_log, "=== DASHBOARD DISPLAY METHOD ===\n" . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
        
        // Include the template
        $template_path = JPATH_ADMINISTRATOR . '/components/com_produccion/views/dashboard/tmpl/default.php';
        if (file_exists($template_path)) {
            file_put_contents($debug_log, "Loading template: $template_path\n", FILE_APPEND);
            include $template_path;
        } else {
            file_put_contents($debug_log, "Template not found: $template_path\n", FILE_APPEND);
            echo "<h1>Template not found!</h1>";
        }
    }
}