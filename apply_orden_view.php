<?php
/**
 * Add Order Detail View with Permissions
 * Run this after apply_fix.php to add the single order view
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Order Detail View</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .info { color: #0c5460; background: #d1ecf1; padding: 10px; border-radius: 4px; margin: 10px 0; }
    </style>
</head>
<body>
<div class='container'>
    <h1>🔧 Add Order Detail View</h1>
    
<?php
$joomla_root = dirname(__FILE__);
$site_path = $joomla_root . '/components/com_produccion';

echo "<h3>1. Creating Order Detail View Class</h3>";

$orden_view = '<?php
defined(\'_JEXEC\') or die;

use Joomla\\CMS\\MVC\\View\\HtmlView;
use Joomla\\CMS\\Factory;

class ProduccionViewOrden extends HtmlView
{
    protected $item;
    
    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $user = Factory::getUser();
        $ordenId = $app->input->getString(\'id\');
        
        // Get the order
        $model = $this->getModel();
        $this->item = $model->getItem($ordenId);
        
        // Check permissions
        if (!$this->item) {
            $app->enqueueMessage(\'Orden no encontrada\', \'error\');
            $app->redirect(\'index.php?option=com_produccion&view=ordenes\');
            return;
        }
        
        // Check if user can view this order
        $userGroups = $user->getAuthorisedGroups();
        $adminGroups = [7, 8]; // Super Users - can see all orders
        $isAdmin = !empty(array_intersect($userGroups, $adminGroups));
        
        if (!$isAdmin) {
            // Check if this order belongs to the current user
            if ($this->item->agente_de_ventas !== $user->name) {
                $app->enqueueMessage(\'No tiene permiso para ver esta orden\', \'error\');
                $app->redirect(\'index.php?option=com_produccion&view=ordenes\');
                return;
            }
        }
        
        parent::display($tpl);
    }
}';

$orden_view_dir = $site_path . '/views/orden';
if (!is_dir($orden_view_dir . '/tmpl')) {
    mkdir($orden_view_dir . '/tmpl', 0755, true);
}

if (file_put_contents($orden_view_dir . '/view.html.php', $orden_view)) {
    echo "<div class='success'>✅ Created orden view class</div>";
} else {
    echo "<div class='error'>❌ Failed</div>";
}

echo "<h3>2. Creating Order Model</h3>";

$orden_model = '<?php
defined(\'_JEXEC\') or die;

use Joomla\\CMS\\MVC\\Model\\BaseDatabaseModel;

class ProduccionModelOrden extends BaseDatabaseModel
{
    public function getItem($ordenId)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true)
            ->select(\'*\')
            ->from($db->quoteName(\'joomla_produccion_ordenes\'))
            ->where($db->quoteName(\'orden_de_trabajo\') . \' = \' . $db->quote($ordenId));
        
        $db->setQuery($query);
        return $db->loadObject();
    }
}';

if (file_put_contents($site_path . '/models/orden.php', $orden_model)) {
    echo "<div class='success'>✅ Created orden model</div>";
} else {
    echo "<div class='error'>❌ Failed</div>";
}

echo "<h3>3. Download and Upload Template</h3>";

echo "<div class='info'>
<p>Due to file size, please download the template file from GitHub:</p>
<p><code>manual_install/site/tmpl/orden/default.php</code></p>
<p>And upload it to:</p>
<p><code>/var/www/grimpsa_webserver/components/com_produccion/views/orden/tmpl/default.php</code></p>
<p>Or I can provide the template code for you to create manually.</p>
</div>";

echo "<div class='success'><h3>✅ Order Detail View Structure Created!</h3>
<p>Permissions implemented: Sales agents can only view their own orders, admins see all.</p></div>";

echo "</div></body></html>";
?>

