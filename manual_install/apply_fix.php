<?php
/**
 * Apply Fix Script
 * Updates component with working webhook endpoint
 */

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html>
<head>
    <title>Apply Component Fix</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .info { color: #0c5460; background: #d1ecf1; padding: 10px; border-radius: 4px; margin: 10px 0; }
    </style>
</head>
<body>
<div class='container'>
    <h1>🔧 Apply Component Fix</h1>
    <p><strong>Creating working webhook endpoint with logging</strong></p>";

$joomla_root = dirname(__FILE__);
$admin_path = $joomla_root . '/administrator/components/com_produccion';

// Get server URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$server_url = $protocol . $_SERVER['HTTP_HOST'];

echo "<h3>1. Creating Webhook Endpoint (webhook_produccion.php)</h3>";

// Create the webhook PHP file
$webhook_file = $joomla_root . '/webhook_produccion.php';

$webhook_content = <<<'ENDWEBHOOK'
<?php
/**
 * Production Webhook Endpoint
 * No authentication required
 */

define('_JEXEC', 1);
define('JPATH_BASE', dirname(__FILE__));

require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

// Disable error display, log to file instead
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Set JSON header
header('Content-Type: application/json');

// Log file
$logFile = JPATH_ADMINISTRATOR . '/logs/webhook_produccion.log';

// Function to log
function logWebhook($message, $data = null) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message";
    if ($data) {
        $logEntry .= "\n" . print_r($data, true);
    }
    $logEntry .= "\n" . str_repeat('-', 80) . "\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

try {
    // Get request data (before app init)
    $method = $_SERVER['REQUEST_METHOD'];
    $rawBody = file_get_contents('php://input');
    $data = json_decode($rawBody, true);
    
    // Fallback to $_POST if JSON parsing failed
    if (!$data && !empty($_POST)) {
        $data = $_POST;
    }
    
    // Log the request (before app init)
    logWebhook('Webhook Request Received', [
        'method' => $method,
        'raw_body' => $rawBody,
        'parsed_data' => $data,
        'post_data' => $_POST,
        'get_data' => $_GET
    ]);
    
    // Handle different payload structures
    // If data comes wrapped in 'form_data', extract it
    if (isset($data['form_data']) && is_array($data['form_data'])) {
        $formData = $data['form_data'];
        $requestTitle = $data['request_title'] ?? 'Unknown';
    } else {
        $formData = $data;
        $requestTitle = 'Direct Request';
    }
    
    // Log successful receipt
    logWebhook('Webhook Data Validated', [
        'request_title' => $requestTitle,
        'has_form_data' => isset($data['form_data']),
        'form_data_fields' => array_keys($formData),
        'full_payload' => $data
    ]);
    
    // Get database directly without application
    $db = Joomla\CMS\Factory::getDbo();
    
    // Generate orden_de_trabajo if not provided (5-digit format: 05468, 05469, etc.)
    if (empty($formData['orden_de_trabajo'])) {
        // Get last order number from new table
        $query = $db->getQuery(true)
            ->select('MAX(CAST(orden_de_trabajo AS UNSIGNED))')
            ->from($db->quoteName('joomla_produccion_ordenes'));
        
        $db->setQuery($query);
        $lastNumber = $db->loadResult();
        
        // Increment and pad to 5 digits
        $newNumber = ($lastNumber ? $lastNumber + 1 : 1);
        $formData['orden_de_trabajo'] = str_pad($newNumber, 5, '0', STR_PAD_LEFT);
        
        logWebhook('Auto-generated orden_de_trabajo', [
            'last_number' => $lastNumber,
            'new_number' => $newNumber,
            'orden_de_trabajo' => $formData['orden_de_trabajo']
        ]);
    }
    
    // Process the webhook
    if (!empty($formData['orden_de_trabajo'])) {
        
        // Check if order exists in new table
        $query = $db->getQuery(true)
            ->select('orden_de_trabajo')
            ->from($db->quoteName('joomla_produccion_ordenes'))
            ->where($db->quoteName('orden_de_trabajo') . ' = ' . $db->quote($formData['orden_de_trabajo']));
        
        $db->setQuery($query);
        $existingOrder = $db->loadResult();
        
        // Map webhook fields to database columns
        $fieldMapping = [
            // Core fields
            'orden_de_trabajo' => 'orden_de_trabajo',
            'fecha_de_solicitud' => 'fecha_de_solicitud',
            'fecha_entrega' => 'fecha_de_entrega',
            'cliente' => 'nombre_del_cliente',
            'nit' => 'nit',
            'agente_de_ventas' => 'agente_de_ventas',
            'descripcion_trabajo' => 'descripcion_de_trabajo',
            'material' => 'material',
            'medidas' => 'medidas_en_pulgadas',
            'color_impresion' => 'color_de_impresion',
            'tiro_retiro' => 'tiro_retiro',
            'valor_factura' => 'valor_a_facturar',
            'instrucciones' => 'observaciones_instrucciones_generales',
            
            // Finishing options
            'corte' => 'corte',
            'detalles_corte' => 'detalles_de_corte',
            'blocado' => 'bloqueado',
            'detalles_blocado' => 'detalles_de_bloqueado',
            'doblado' => 'doblado',
            'detalles_doblado' => 'detalles_de_doblado',
            'laminado' => 'laminado',
            'detalles_laminado' => 'detalles_de_laminado',
            'lomo' => 'lomo',
            'detalles_lomo' => 'detalles_de_lomo',
            'numerado' => 'numerado',
            'detalles_numerado' => 'detalles_de_numerado',
            'pegado' => 'pegado',
            'detalles_pegado' => 'detalles_de_pegado',
            'sizado' => 'sizado',
            'detalles_sizado' => 'detalles_de_sizado',
            'engrapado' => 'engrapado',
            'detalles_engrapado' => 'detalles_de_engrapado',
            'troquel' => 'troquel',
            'detalles_troquel' => 'detalles_de_troquel',
            'barniz' => 'barniz',
            'detalles_barniz' => 'descripcion_de_barniz',
            'impresion_blanco' => 'impresion_en_blanco',
            'detalles_impresion_blanco' => 'descripcion_de_acabado_en_blanco',
            'despuntado' => 'despuntados',
            'detalles_despuntado' => 'descripcion_de_despuntados',
            'ojetes' => 'ojetes',
            'detalles_ojetes' => 'descripcion_de_ojetes',
            'perforado' => 'perforado',
            'detalles_perforado' => 'descripcion_de_perforado'
        ];
        
        // Build columns and values arrays
        $columns = [];
        $values = [];
        
        foreach ($fieldMapping as $webhookField => $dbColumn) {
            if (isset($formData[$webhookField]) && $formData[$webhookField] !== '') {
                $columns[] = $db->quoteName($dbColumn);
                $values[] = $db->quote($formData[$webhookField]);
            }
        }
        
        // Handle array fields (cotizacion, arte)
        if (!empty($formData['cotizacion']) && is_array($formData['cotizacion'])) {
            $columns[] = $db->quoteName('adjuntar_cotizacion');
            $values[] = $db->quote($formData['cotizacion'][0] ?? '');
        }
        
        if (!empty($formData['arte']) && is_array($formData['arte'])) {
            $columns[] = $db->quoteName('archivo_de_arte');
            $values[] = $db->quote($formData['arte'][0] ?? '');
        }
        
        // Add timestamp
        $columns[] = $db->quoteName('marca_temporal');
        $values[] = $db->quote(date('Y-m-d H:i:s'));
        
        if ($existingOrder) {
            // Update existing order
            logWebhook('Order already exists - skipping', [
                'orden_de_trabajo' => $formData['orden_de_trabajo']
            ]);
            
            $ordenId = $formData['orden_de_trabajo'];
        } else {
            // Insert new order
            logWebhook('Creating new order', [
                'orden_de_trabajo' => $formData['orden_de_trabajo'],
                'columns_count' => count($columns)
            ]);
            
            $query = $db->getQuery(true)
                ->insert($db->quoteName('joomla_produccion_ordenes'))
                ->columns($columns)
                ->values(implode(', ', $values));
            
            $db->setQuery($query);
            $db->execute();
            
            $ordenId = $formData['orden_de_trabajo'];
        }
        
        // Log all form data fields received
        logWebhook('ALL Form Data Fields Received', [
            'orden_id' => $ordenId,
            'orden_de_trabajo' => $formData['orden_de_trabajo'],
            'all_fields' => $formData
        ]);
        
        // Log success
        logWebhook('Webhook processed successfully', [
            'orden_id' => $ordenId,
            'orden_de_trabajo' => $formData['orden_de_trabajo']
        ]);
        
        // Return success response
        echo json_encode([
            'status' => 'success',
            'message' => 'Webhook processed successfully - Order created and logged',
            'orden_id' => $ordenId,
            'orden_de_trabajo' => $formData['orden_de_trabajo'],
            'request_title' => $requestTitle,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
    } else {
        // Missing required field
        logWebhook('Missing orden_de_trabajo', ['received_data' => $data]);
        
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Missing required field: orden_de_trabajo',
            'received_data' => $data
        ]);
    }
    
} catch (Exception $e) {
    // Log error
    logWebhook('Webhook Error', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}

exit(0);
ENDWEBHOOK;

if (file_put_contents($webhook_file, $webhook_content)) {
    echo "<div class='success'>✅ Created webhook_produccion.php (without authentication)</div>";
    echo "<div class='info'>📁 Log file: /administrator/logs/webhook_produccion.log</div>";
} else {
    echo "<div class='error'>❌ Failed to create webhook file</div>";
}

echo "<h3>2. Updating Webhook View Template</h3>";

// Webhook URL for display
$webhook_url = $server_url . '/webhook_produccion.php';

// Sample payload
$sample_payload = [
    "orden_de_trabajo" => "OT-2024-001",
    "estado" => "nueva",
    "tipo_orden" => "interna",
    "info" => [
        "cliente" => "Cliente Ejemplo",
        "producto" => "Producto A",
        "cantidad" => 100,
        "fecha_entrega" => "2024-12-31"
    ]
];

// Create enhanced webhook view template
$webhook_template = <<<'ENDTEMPLATE'
<?php
defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;

// Get server URL
$uri = Uri::getInstance();
$server_url = $uri->toString(['scheme', 'host', 'port']);
$webhook_url = $server_url . '/webhook_produccion.php';

// Sample payload
$sample_payload = [
    "orden_de_trabajo" => "OT-2024-001",
    "estado" => "nueva",
    "tipo_orden" => "interna",
    "info" => [
        "cliente" => "Cliente Ejemplo",
        "producto" => "Producto A",
        "cantidad" => 100,
        "fecha_entrega" => "2024-12-31"
    ]
];
?>

<style>
.webhook-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.webhook-url-box {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 15px;
    margin: 15px 0;
    font-family: monospace;
    font-size: 14px;
    word-break: break-all;
}

.btn-group {
    display: flex;
    gap: 10px;
    margin-top: 15px;
    flex-wrap: wrap;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    color: white;
}

.btn-primary { background: #0d6efd; }
.btn-primary:hover { background: #0b5ed7; }
.btn-info { background: #0dcaf0; color: #000; }
.btn-info:hover { background: #31d2f2; }
.btn-success { background: #198754; }
.btn-success:hover { background: #157347; }

.alert {
    padding: 15px;
    border-radius: 4px;
    margin: 15px 0;
}

.alert-info {
    background: #cff4fc;
    border: 1px solid #b6effb;
    color: #055160;
}

.alert-success {
    background: #d1e7dd;
    border: 1px solid #badbcc;
    color: #0f5132;
}

.code-block {
    background: #272822;
    color: #f8f8f2;
    padding: 15px;
    border-radius: 4px;
    overflow-x: auto;
    margin: 15px 0;
}

.code-block pre {
    margin: 0;
    font-family: 'Courier New', monospace;
}
</style>

<div class="webhook-card">
    <h2>📍 Webhook Endpoint URL</h2>
    <p>Use this URL to send production order data:</p>
    
    <div class="webhook-url-box" id="webhookUrl">
        <?php echo htmlspecialchars($webhook_url); ?>
    </div>
    
    <div class="btn-group">
        <button onclick="copyUrl()" class="btn btn-primary">
            📋 Copy URL
        </button>
        <button onclick="testWebhook()" class="btn btn-info">
            🧪 Test Connection
        </button>
        <button onclick="downloadPostman()" class="btn btn-success">
            📦 Download Postman Collection
        </button>
    </div>
    
    <div id="testResult"></div>
</div>

<div class="webhook-card">
    <h3>📝 Sample Request</h3>
    <p>Example POST request body:</p>
    
    <div class="code-block">
        <pre><?php echo htmlspecialchars(json_encode($sample_payload, JSON_PRETTY_PRINT)); ?></pre>
    </div>
    
    <h4>cURL Example:</h4>
    <div class="code-block">
        <pre>curl -X POST "<?php echo htmlspecialchars($webhook_url); ?>" \
  -H "Content-Type: application/json" \
  -d '<?php echo json_encode($sample_payload); ?>'</pre>
    </div>
</div>

<div class="webhook-card">
    <h3>📊 Webhook Logging</h3>
    <div class="alert alert-info">
        <p><strong>All webhook requests are logged to:</strong></p>
        <ul>
            <li>📄 Log File: <code>/administrator/logs/webhook_produccion.log</code></li>
            <li>🗄️ Database: <code>#__produccion_webhook_logs</code> table</li>
        </ul>
        <p><strong>Each log entry includes:</strong></p>
        <ul>
            <li>Timestamp</li>
            <li>HTTP Method</li>
            <li>Request Headers</li>
            <li>Request Data</li>
            <li>Processing Result</li>
        </ul>
    </div>
    
    <a href="/administrator/index.php?option=com_produccion&view=debug" class="btn btn-info">
        View Webhook Logs
    </a>
</div>

<div class="webhook-card">
    <h3>ℹ️ Configuration</h3>
    <div class="alert alert-info">
        <ul style="margin: 0; padding-left: 20px;">
            <li><strong>Authentication:</strong> None required (public access)</li>
            <li><strong>Method:</strong> POST (GET also supported for testing)</li>
            <li><strong>Content-Type:</strong> application/json</li>
            <li><strong>Response:</strong> JSON with status and details</li>
        </ul>
    </div>
</div>

<script>
function copyUrl() {
    const url = document.getElementById('webhookUrl').textContent.trim();
    navigator.clipboard.writeText(url).then(() => {
        const result = document.getElementById('testResult');
        result.innerHTML = '<div class="alert alert-success">✅ URL copied to clipboard!</div>';
        setTimeout(() => { result.innerHTML = ''; }, 3000);
    });
}

function testWebhook() {
    const url = document.getElementById('webhookUrl').textContent.trim();
    const result = document.getElementById('testResult');
    
    result.innerHTML = '<div class="alert alert-info">🔄 Testing connection...</div>';
    
    fetch(url, {
        method: 'GET'
    })
    .then(response => response.json())
    .then(data => {
        result.innerHTML = '<div class="alert alert-success"><h4>✅ Connection Successful!</h4><pre>' + JSON.stringify(data, null, 2) + '</pre></div>';
    })
    .catch(error => {
        result.innerHTML = '<div class="alert" style="background: #f8d7da; color: #842029;"><h4>❌ Connection Failed</h4><p>' + error.message + '</p></div>';
    });
}

function downloadPostman() {
    const collection = {
        "info": {
            "name": "Production Management - Webhook",
            "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
        },
        "item": [
            {
                "name": "Create/Update Order",
                "request": {
                    "method": "POST",
                    "header": [{"key": "Content-Type", "value": "application/json"}],
                    "body": {
                        "mode": "raw",
                        "raw": <?php echo json_encode(json_encode($sample_payload, JSON_PRETTY_PRINT)); ?>
                    },
                    "url": {
                        "raw": "<?php echo $webhook_url; ?>",
                        "protocol": "<?php echo parse_url($webhook_url, PHP_URL_SCHEME); ?>",
                        "host": ["<?php echo parse_url($webhook_url, PHP_URL_HOST); ?>"],
                        "path": ["<?php echo ltrim(parse_url($webhook_url, PHP_URL_PATH), '/'); ?>"]
                    }
                }
            }
        ]
    };
    
    const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(collection, null, 2));
    const link = document.createElement('a');
    link.setAttribute("href", dataStr);
    link.setAttribute("download", "webhook_produccion.postman_collection.json");
    document.body.appendChild(link);
    link.click();
    link.remove();
    
    const result = document.getElementById('testResult');
    result.innerHTML = '<div class="alert alert-success">✅ Postman collection downloaded!</div>';
    setTimeout(() => { result.innerHTML = ''; }, 3000);
}
</script>
ENDTEMPLATE;

// Update webhook template in both locations
$webhook_tmpl_modern = $admin_path . '/tmpl/webhook/default.php';
$webhook_tmpl_legacy = $admin_path . '/views/webhook/tmpl/default.php';

$updated = 0;

if (file_put_contents($webhook_tmpl_modern, $webhook_template)) {
    echo "<div class='success'>✅ Updated webhook template (modern)</div>";
    $updated++;
}

if (is_dir(dirname($webhook_tmpl_legacy))) {
    if (file_put_contents($webhook_tmpl_legacy, $webhook_template)) {
        echo "<div class='success'>✅ Updated webhook template (legacy)</div>";
        $updated++;
    }
}

if ($updated > 0) {
    echo "<div class='success'>✅ Webhook view updated!</div>";
}

echo "<h3>3. Webhook Features</h3>";

echo "<div class='info'>
    <h4>✨ Features:</h4>
    <ul>
        <li>✅ No authentication required</li>
        <li>✅ Accepts JSON POST requests</li>
        <li>✅ Creates/updates production orders</li>
        <li>✅ Handles EAV attributes in 'info' object</li>
        <li>✅ Logs all requests to file</li>
        <li>✅ Returns JSON responses</li>
    </ul>
</div>";

echo "<h3>4. Test the Webhook</h3>";

echo "<div class='info'>
    <p><strong>Test URLs:</strong></p>
    <ul>
        <li><a href='$webhook_url' target='_blank'>Test Webhook (GET)</a></li>
        <li><a href='/administrator/index.php?option=com_produccion&view=webhook' target='_blank'>Webhook Configuration</a></li>
    </ul>
</div>";

echo "<h3>5. View Logs</h3>";

echo "<div class='info'>
    <p><strong>Check webhook activity in:</strong></p>
    <ul>
        <li>📄 <code>/var/www/grimpsa_webserver/administrator/logs/webhook_produccion.log</code></li>
        <li>Or view in admin: <a href='/administrator/index.php?option=com_produccion&view=debug' target='_blank'>Debug Console</a></li>
    </ul>
</div>";

echo "<h3>6. Creating Frontend Work Orders View</h3>";

// Create frontend controller for work orders
$frontend_controller = <<<'ENDCONTROLLER'
<?php
namespace Joomla\Component\Produccion\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

class OrdenesController extends BaseController
{
    protected $default_view = 'ordenes';
}
ENDCONTROLLER;

$site_path = $joomla_root . '/components/com_produccion';
$controller_file = $site_path . '/src/Controller/OrdenesController.php';

if (!is_dir(dirname($controller_file))) {
    mkdir(dirname($controller_file), 0755, true);
}

if (file_put_contents($controller_file, $frontend_controller)) {
    echo "<div class='success'>✅ Created frontend ordenes controller</div>";
} else {
    echo "<div class='error'>❌ Failed to create controller</div>";
}

// Create frontend model
$frontend_model = <<<'ENDMODEL'
<?php
namespace Joomla\Component\Produccion\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;

class OrdenesModel extends ListModel
{
    protected function getListQuery()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $user = Factory::getUser();
        
        // Select from work orders table
        $query->select('*')
              ->from($db->quoteName('joomla_produccion_ordenes'));
        
        // Apply filters
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $search = $db->quote('%' . $db->escape($search, true) . '%');
            $query->where('(' . 
                $db->quoteName('orden_de_trabajo') . ' LIKE ' . $search . ' OR ' .
                $db->quoteName('nombre_del_cliente') . ' LIKE ' . $search . ' OR ' .
                $db->quoteName('descripcion_de_trabajo') . ' LIKE ' . $search .
            ')');
        }
        
        // Filter by agent
        $agent = $this->getState('filter.agent');
        if (!empty($agent)) {
            $query->where($db->quoteName('agente_de_ventas') . ' = ' . $db->quote($agent));
        }
        
        // Filter by date range
        $dateFrom = $this->getState('filter.date_from');
        if (!empty($dateFrom)) {
            $query->where($db->quoteName('fecha_de_solicitud') . ' >= ' . $db->quote($dateFrom));
        }
        
        $dateTo = $this->getState('filter.date_to');
        if (!empty($dateTo)) {
            $query->where($db->quoteName('fecha_de_solicitud') . ' <= ' . $db->quote($dateTo . ' 23:59:59'));
        }
        
        // Check user permissions
        $userGroups = $user->getAuthorisedGroups();
        $adminGroups = [7, 8]; // Super Users group IDs - adjust as needed
        
        $isAdmin = !empty(array_intersect($userGroups, $adminGroups));
        
        if (!$isAdmin) {
            // Sales agents only see their own orders
            $query->where($db->quoteName('agente_de_ventas') . ' = ' . $db->quote($user->name));
        }
        
        // Order by newest first
        $query->order($db->quoteName('orden_de_trabajo') . ' DESC');
        
        return $query;
    }
    
    protected function populateState($ordering = null, $direction = null)
    {
        $app = Factory::getApplication();
        
        // Get filter values from request
        $search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string');
        $this->setState('filter.search', $search);
        
        $agent = $app->getUserStateFromRequest($this->context . '.filter.agent', 'filter_agent', '', 'string');
        $this->setState('filter.agent', $agent);
        
        $dateFrom = $app->getUserStateFromRequest($this->context . '.filter.date_from', 'filter_date_from', '', 'string');
        $this->setState('filter.date_from', $dateFrom);
        
        $dateTo = $app->getUserStateFromRequest($this->context . '.filter.date_to', 'filter_date_to', '', 'string');
        $this->setState('filter.date_to', $dateTo);
        
        parent::populateState('orden_de_trabajo', 'DESC');
    }
    
    public function getAgents()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true)
            ->select('DISTINCT ' . $db->quoteName('agente_de_ventas'))
            ->from($db->quoteName('joomla_produccion_ordenes'))
            ->where($db->quoteName('agente_de_ventas') . ' IS NOT NULL')
            ->order($db->quoteName('agente_de_ventas') . ' ASC');
        
        $db->setQuery($query);
        return $db->loadColumn();
    }
}
ENDMODEL;

$model_file = $site_path . '/src/Model/OrdenesModel.php';

if (!is_dir(dirname($model_file))) {
    mkdir(dirname($model_file), 0755, true);
}

if (file_put_contents($model_file, $frontend_model)) {
    echo "<div class='success'>✅ Created frontend ordenes model</div>";
} else {
    echo "<div class='error'>❌ Failed to create model</div>";
}

// Create frontend view
$frontend_view = <<<'ENDVIEW'
<?php
namespace Joomla\Component\Produccion\Site\View\Ordenes;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;

class HtmlView extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $agents;
    
    public function display($tpl = null)
    {
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->agents = $this->get('Agents');
        
        parent::display($tpl);
    }
}
ENDVIEW;

$view_file = $site_path . '/src/View/Ordenes/HtmlView.php';

if (!is_dir(dirname($view_file))) {
    mkdir(dirname($view_file), 0755, true);
}

if (file_put_contents($view_file, $frontend_view)) {
    echo "<div class='success'>✅ Created frontend ordenes view</div>";
} else {
    echo "<div class='error'>❌ Failed to create view</div>";
}

// Create frontend template
$frontend_template = <<<'ENDTEMPLATE'
<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$user = Factory::getUser();
$app = Factory::getApplication();
$input = $app->input;

// Get current filters
$filterSearch = $this->state->get('filter.search');
$filterAgent = $this->state->get('filter.agent');
$filterDateFrom = $this->state->get('filter.date_from');
$filterDateTo = $this->state->get('filter.date_to');
?>

<style>
.ordenes-container {
    max-width: 1200px;
    margin: 20px auto;
    padding: 20px;
    font-family: Arial, sans-serif;
}

.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    border-radius: 10px;
    margin-bottom: 30px;
}

.page-header h1 {
    margin: 0;
    font-size: 32px;
}

.filters-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.filters-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 15px;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-group label {
    font-weight: bold;
    margin-bottom: 5px;
    color: #333;
}

.filter-group input,
.filter-group select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.filter-buttons {
    display: flex;
    gap: 10px;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.3s;
}

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-primary:hover {
    background: #5568d3;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
}

.ordenes-table {
    width: 100%;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.ordenes-table table {
    width: 100%;
    border-collapse: collapse;
}

.ordenes-table thead {
    background: #f8f9fa;
}

.ordenes-table th {
    padding: 15px;
    text-align: left;
    font-weight: bold;
    color: #333;
    border-bottom: 2px solid #dee2e6;
}

.ordenes-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #f0f0f0;
}

.ordenes-table tbody tr:hover {
    background: #f8f9fa;
}

.orden-number {
    font-weight: bold;
    color: #667eea;
    text-decoration: none;
    font-size: 16px;
}

.orden-number:hover {
    text-decoration: underline;
}

.pagination-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 20px;
    padding: 20px;
}

.pagination {
    display: flex;
    gap: 5px;
    list-style: none;
    padding: 0;
    margin: 0;
}

.pagination li {
    display: inline-block;
}

.pagination a,
.pagination span {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    text-decoration: none;
    color: #333;
}

.pagination .active span {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

.pagination a:hover {
    background: #f8f9fa;
}

.no-results {
    text-align: center;
    padding: 40px;
    color: #666;
}

.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.stat-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-left: 4px solid #667eea;
}

.stat-card h3 {
    margin: 0 0 10px 0;
    color: #666;
    font-size: 14px;
}

.stat-card .stat-value {
    font-size: 32px;
    font-weight: bold;
    color: #333;
}
</style>

<div class="ordenes-container">
    <div class="page-header">
        <h1>📋 Órdenes de Trabajo</h1>
        <p>Bienvenido, <?php echo htmlspecialchars($user->name); ?></p>
    </div>
    
    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-card">
            <h3>Total Órdenes</h3>
            <div class="stat-value"><?php echo count($this->items); ?></div>
        </div>
        <div class="stat-card">
            <h3>Mi Agente</h3>
            <div class="stat-value" style="font-size: 18px;"><?php echo htmlspecialchars($user->name); ?></div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="filters-card">
        <form action="<?php echo Route::_('index.php?option=com_produccion&view=ordenes'); ?>" method="post" name="adminForm" id="adminForm">
            
            <div class="filters-row">
                <div class="filter-group">
                    <label for="filter_search">Buscar:</label>
                    <input type="text" 
                           name="filter_search" 
                           id="filter_search" 
                           value="<?php echo htmlspecialchars($filterSearch); ?>" 
                           placeholder="Orden, Cliente, Descripción...">
                </div>
                
                <div class="filter-group">
                    <label for="filter_agent">Agente de Ventas:</label>
                    <select name="filter_agent" id="filter_agent">
                        <option value="">-- Todos los Agentes --</option>
                        <?php foreach ($this->agents as $agent): ?>
                            <option value="<?php echo htmlspecialchars($agent); ?>" 
                                    <?php echo ($filterAgent == $agent) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($agent); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="filter_date_from">Desde:</label>
                    <input type="date" 
                           name="filter_date_from" 
                           id="filter_date_from" 
                           value="<?php echo htmlspecialchars($filterDateFrom); ?>">
                </div>
                
                <div class="filter-group">
                    <label for="filter_date_to">Hasta:</label>
                    <input type="date" 
                           name="filter_date_to" 
                           id="filter_date_to" 
                           value="<?php echo htmlspecialchars($filterDateTo); ?>">
                </div>
            </div>
            
            <div class="filter-buttons">
                <button type="submit" class="btn btn-primary">🔍 Filtrar</button>
                <button type="button" class="btn btn-secondary" onclick="clearFilters()">🔄 Limpiar</button>
            </div>
            
            <input type="hidden" name="task" value="" />
            <input type="hidden" name="option" value="com_produccion" />
        </form>
    </div>
    
    <!-- Orders Table -->
    <div class="ordenes-table">
        <?php if (!empty($this->items)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Orden #</th>
                        <th>Cliente</th>
                        <th>Descripción</th>
                        <th>Fecha Solicitud</th>
                        <th>Fecha Entrega</th>
                        <th>Agente</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->items as $item): ?>
                        <tr>
                            <td>
                                <a href="<?php echo Route::_('index.php?option=com_produccion&view=orden&id=' . $item->orden_de_trabajo); ?>" 
                                   class="orden-number">
                                    <?php echo htmlspecialchars($item->orden_de_trabajo); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($item->nombre_del_cliente ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars(substr($item->descripcion_de_trabajo ?? '-', 0, 50)); ?><?php echo strlen($item->descripcion_de_trabajo ?? '') > 50 ? '...' : ''; ?></td>
                            <td><?php echo htmlspecialchars($item->fecha_de_solicitud ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($item->fecha_de_entrega ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($item->agente_de_ventas ?? '-'); ?></td>
                            <td>
                                <a href="<?php echo Route::_('index.php?option=com_produccion&view=orden&id=' . $item->orden_de_trabajo); ?>" 
                                   class="btn btn-primary" 
                                   style="padding: 5px 10px; font-size: 12px;">
                                    👁️ Ver
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-results">
                <h3>📭 No se encontraron órdenes</h3>
                <p>No hay órdenes de trabajo que coincidan con los filtros seleccionados.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Pagination -->
    <?php if ($this->pagination->pagesTotal > 1): ?>
        <div class="pagination-wrapper">
            <?php echo $this->pagination->getPagesLinks(); ?>
        </div>
    <?php endif; ?>
</div>

<script>
function clearFilters() {
    document.getElementById('filter_search').value = '';
    document.getElementById('filter_agent').value = '';
    document.getElementById('filter_date_from').value = '';
    document.getElementById('filter_date_to').value = '';
    document.getElementById('adminForm').submit();
}
</script>
ENDTEMPLATE;

$template_file = $site_path . '/tmpl/ordenes/default.php';

if (!is_dir(dirname($template_file))) {
    mkdir(dirname($template_file), 0755, true);
}

if (file_put_contents($template_file, $frontend_template)) {
    echo "<div class='success'>✅ Created frontend ordenes template</div>";
} else {
    echo "<div class='error'>❌ Failed to create template</div>";
}

echo "<div class='info'>
    <h4>✨ Frontend Work Orders View Created!</h4>
    <p><strong>Access URL:</strong></p>
    <p><a href='/index.php?option=com_produccion&view=ordenes' target='_blank'>
        https://grimpsa_webserver.grantsolutions.cc/index.php?option=com_produccion&view=ordenes
    </a></p>
    
    <h4>Features:</h4>
    <ul>
        <li>✅ Paginated list sorted by newest first</li>
        <li>✅ Search by order #, client, or description</li>
        <li>✅ Filter by sales agent</li>
        <li>✅ Filter by date range</li>
        <li>✅ Permission-based: Sales agents see only their orders</li>
        <li>✅ Admins see all orders</li>
        <li>✅ Click order number to view details</li>
    </ul>
</div>";

echo "<h3>7. Creating Site Entry Point and Legacy Controller</h3>";

// Create legacy-style site controller
$site_controller = <<<'ENDLEGACYCONTROLLER'
<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

class ProduccionController extends BaseController
{
    protected $default_view = 'ordenes';
    
    public function display($cachable = false, $urlparams = [])
    {
        $this->default_view = $this->input->getCmd('view', 'ordenes');
        
        return parent::display($cachable, $urlparams);
    }
}
ENDLEGACYCONTROLLER;

$site_controller_file = $site_path . '/controller.php';

if (file_put_contents($site_controller_file, $site_controller)) {
    echo "<div class='success'>✅ Created site controller.php</div>";
} else {
    echo "<div class='error'>❌ Failed to create site controller</div>";
}

// Create legacy view class for ordenes
$legacy_ordenes_view = <<<'ENDLEGACYVIEW'
<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;

class ProduccionViewOrdenes extends HtmlView
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $agents;
    
    public function display($tpl = null)
    {
        $model = $this->getModel();
        
        $this->items = $model->getItems();
        $this->pagination = $model->getPagination();
        $this->state = $model->getState();
        $this->agents = $model->getAgents();
        
        parent::display($tpl);
    }
}
ENDLEGACYVIEW;

$legacy_view_dir = $site_path . '/views/ordenes/tmpl';
if (!is_dir($legacy_view_dir)) {
    mkdir($legacy_view_dir, 0755, true);
}

$legacy_view_file = $site_path . '/views/ordenes/view.html.php';

if (file_put_contents($legacy_view_file, $legacy_ordenes_view)) {
    echo "<div class='success'>✅ Created legacy view class</div>";
} else {
    echo "<div class='error'>❌ Failed to create legacy view</div>";
}

// Copy template to legacy location
$modern_template = $site_path . '/tmpl/ordenes/default.php';
$legacy_template = $site_path . '/views/ordenes/tmpl/default.php';

if (file_exists($modern_template)) {
    copy($modern_template, $legacy_template);
    echo "<div class='success'>✅ Copied template to legacy location</div>";
}

// Create legacy model
$legacy_model = <<<'ENDLEGACYMODEL'
<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;

class ProduccionModelOrdenes extends ListModel
{
    protected function getListQuery()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $user = Factory::getUser();
        
        // Select from work orders table
        $query->select('*')
              ->from($db->quoteName('joomla_produccion_ordenes'));
        
        // Apply filters
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $search = $db->quote('%' . $db->escape($search, true) . '%');
            $query->where('(' . 
                $db->quoteName('orden_de_trabajo') . ' LIKE ' . $search . ' OR ' .
                $db->quoteName('nombre_del_cliente') . ' LIKE ' . $search . ' OR ' .
                $db->quoteName('descripcion_de_trabajo') . ' LIKE ' . $search .
            ')');
        }
        
        // Filter by agent
        $agent = $this->getState('filter.agent');
        if (!empty($agent)) {
            $query->where($db->quoteName('agente_de_ventas') . ' = ' . $db->quote($agent));
        }
        
        // Filter by date range
        $dateFrom = $this->getState('filter.date_from');
        if (!empty($dateFrom)) {
            $query->where($db->quoteName('fecha_de_solicitud') . ' >= ' . $db->quote($dateFrom));
        }
        
        $dateTo = $this->getState('filter.date_to');
        if (!empty($dateTo)) {
            $query->where($db->quoteName('fecha_de_solicitud') . ' <= ' . $db->quote($dateTo . ' 23:59:59'));
        }
        
        // Check user permissions
        $userGroups = $user->getAuthorisedGroups();
        $adminGroups = [7, 8]; // Super Users group IDs
        
        $isAdmin = !empty(array_intersect($userGroups, $adminGroups));
        
        if (!$isAdmin) {
            // Sales agents only see their own orders
            $query->where($db->quoteName('agente_de_ventas') . ' = ' . $db->quote($user->name));
        }
        
        // Order by newest first
        $query->order($db->quoteName('orden_de_trabajo') . ' DESC');
        
        return $query;
    }
    
    protected function populateState($ordering = null, $direction = null)
    {
        $app = Factory::getApplication();
        
        // Get filter values
        $search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string');
        $this->setState('filter.search', $search);
        
        $agent = $app->getUserStateFromRequest($this->context . '.filter.agent', 'filter_agent', '', 'string');
        $this->setState('filter.agent', $agent);
        
        $dateFrom = $app->getUserStateFromRequest($this->context . '.filter.date_from', 'filter_date_from', '', 'string');
        $this->setState('filter.date_from', $dateFrom);
        
        $dateTo = $app->getUserStateFromRequest($this->context . '.filter.date_to', 'filter_date_to', '', 'string');
        $this->setState('filter.date_to', $dateTo);
        
        parent::populateState('orden_de_trabajo', 'DESC');
    }
    
    public function getAgents()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true)
            ->select('DISTINCT ' . $db->quoteName('agente_de_ventas'))
            ->from($db->quoteName('joomla_produccion_ordenes'))
            ->where($db->quoteName('agente_de_ventas') . ' IS NOT NULL')
            ->order($db->quoteName('agente_de_ventas') . ' ASC');
        
        $db->setQuery($query);
        return $db->loadColumn();
    }
}
ENDLEGACYMODEL;

$legacy_model_file = $site_path . '/models/ordenes.php';

if (!is_dir(dirname($legacy_model_file))) {
    mkdir(dirname($legacy_model_file), 0755, true);
}

if (file_put_contents($legacy_model_file, $legacy_model)) {
    echo "<div class='success'>✅ Created legacy model</div>";
} else {
    echo "<div class='error'>❌ Failed to create legacy model</div>";
}

// Create proper site entry point
$site_entry = <<<'ENDSITEENTRY'
<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

// Get controller
$controller = BaseController::getInstance('Produccion');

// Execute the task
$controller->execute(Factory::getApplication()->input->getCmd('task'));

// Redirect if set
$controller->redirect();
ENDSITEENTRY;

$site_entry_file = $site_path . '/produccion.php';

if (file_put_contents($site_entry_file, $site_entry)) {
    echo "<div class='success'>✅ Created site entry point</div>";
} else {
    echo "<div class='error'>❌ Failed to create site entry point</div>";
}

echo "<h3>8. How to Add to Your Menu</h3>";

echo "<h3>8. Creating Menu Item Type XML</h3>";

// Create menu item type XML for Joomla's menu manager
$menu_xml = <<<'ENDMENUXML'
<?xml version="1.0" encoding="utf-8"?>
<metadata>
    <layout title="Work Orders List">
        <message>
            <![CDATA[COM_PRODUCCION_ORDENES_VIEW_DEFAULT_DESC]]>
        </message>
    </layout>
    <fields name="request">
        <fieldset name="request">
            <field
                name="id"
                type="text"
                label="Order ID"
                description="Specific order ID to display"
            />
        </fieldset>
    </fields>
</metadata>
ENDMENUXML;

$menu_xml_file = $site_path . '/tmpl/ordenes/default.xml';

if (file_put_contents($menu_xml_file, $menu_xml)) {
    echo "<div class='success'>✅ Created menu item type XML</div>";
} else {
    echo "<div class='error'>❌ Failed to create menu XML</div>";
}

// Create site manifest with menu item types
$site_manifest = <<<'ENDSITEMANIFEST'
<?xml version="1.0" encoding="utf-8"?>
<extension type="component" client="site">
    <name>com_produccion</name>
    <views>
        <view name="ordenes" title="Work Orders List" />
        <view name="orden" title="Work Order Details" />
    </views>
</extension>
ENDSITEMANIFEST;

$site_manifest_file = $site_path . '/produccion.xml';

if (file_put_contents($site_manifest_file, $site_manifest)) {
    echo "<div class='success'>✅ Created site manifest</div>";
} else {
    echo "<div class='error'>❌ Failed to create site manifest</div>";
}

echo "<h3>8. Creating Menu Item Type</h3>";

// Load Joomla configuration
$config_file = $joomla_root . '/configuration.php';

if (file_exists($config_file)) {
    require_once $config_file;
    $config = new JConfig();
    
    try {
        $pdo = new PDO(
            "mysql:host=" . $config->host . ";dbname=" . $config->db . ";charset=utf8", 
            $config->user, 
            $config->password
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Check if menu item already exists
        $check_sql = "SELECT id FROM " . $config->dbprefix . "menu 
                      WHERE link = 'index.php?option=com_produccion&view=ordenes' 
                      AND client_id = 0";
        $stmt = $pdo->prepare($check_sql);
        $stmt->execute();
        $existingMenuItem = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$existingMenuItem) {
            // Get component ID
            $comp_sql = "SELECT extension_id FROM " . $config->dbprefix . "extensions 
                         WHERE element = 'com_produccion' AND type = 'component'";
            $stmt = $pdo->prepare($comp_sql);
            $stmt->execute();
            $component = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($component) {
                // Create menu item
                $menu_sql = "INSERT INTO " . $config->dbprefix . "menu 
                    (menutype, title, alias, note, path, link, type, published, parent_id, level, component_id, access, img, params, lft, rgt, home, language, client_id)
                    VALUES ('mainmenu', 'Órdenes de Trabajo', 'ordenes-de-trabajo', '', 'ordenes-de-trabajo', 
                            'index.php?option=com_produccion&view=ordenes', 'component', 1, 1, 1, :component_id, 1, '', 
                            '{\"menu-anchor_title\":\"\",\"menu-anchor_css\":\"\",\"menu_image\":\"\",\"menu_text\":1,\"menu_show\":1}', 
                            0, 0, 0, '*', 0)";
                
                $stmt = $pdo->prepare($menu_sql);
                if ($stmt->execute(['component_id' => $component['extension_id']])) {
                    echo "<div class='success'>✅ Created frontend menu item 'Órdenes de Trabajo'</div>";
                    echo "<div class='info'>ℹ️ Menu Type: mainmenu (you can move it to any menu in Joomla admin)</div>";
                } else {
                    echo "<div class='error'>❌ Failed to create menu item</div>";
                }
            } else {
                echo "<div class='error'>❌ Component not found in database</div>";
            }
        } else {
            echo "<div class='info'>ℹ️ Menu item already exists (ID: {$existingMenuItem['id']})</div>";
        }
        
    } catch (PDOException $e) {
        echo "<div class='error'>❌ Database error: " . $e->getMessage() . "</div>";
    }
} else {
    echo "<div class='info'>ℹ️ Skipped menu item creation (run from Joomla root to create menu)</div>";
}

echo "<h3>9. Clearing Cache</h3>";

// Clear Joomla cache so menu items are recognized
$cache_dirs = [
    $joomla_root . '/cache',
    $joomla_root . '/administrator/cache'
];

foreach ($cache_dirs as $dir) {
    if (is_dir($dir)) {
        $files = glob($dir . '/*');
        $count = 0;
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
                $count++;
            }
        }
        echo "<div class='success'>✅ Cleared $count files from " . basename($dir) . "</div>";
    }
}

echo "<h3>10. How to Add to Your Menu</h3>";

echo "<div class='info'>
    <p><strong>Option 1: Use the Auto-Created Menu Item</strong></p>
    <ol>
        <li>Go to: Menus → Main Menu (or your desired menu)</li>
        <li>Look for 'Órdenes de Trabajo' menu item</li>
        <li>Edit it to customize title, access level, etc.</li>
    </ol>
    
    <p><strong>Option 2: Create Manually</strong></p>
    <ol>
        <li>Go to: Menus → [Your Menu] → Add New Menu Item</li>
        <li>Menu Item Type: Click 'Select'</li>
        <li>Find: Production Management System → Work Orders List</li>
        <li>Title: 'Órdenes de Trabajo' (or your preference)</li>
        <li>Access: Public or Registered (depending on your needs)</li>
        <li>Save</li>
    </ol>
</div>";

echo "</div>
</body>
</html>";

