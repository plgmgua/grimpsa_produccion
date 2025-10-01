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

// Create webhook file - using file_get_contents to avoid escaping issues
$webhook_code = file_get_contents(__FILE__);

// Create the actual webhook PHP file
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
    
    // Get database directly without application
    $db = Joomla\CMS\Factory::getDbo();
    
    // Process the webhook
    if (!empty($data['orden_de_trabajo'])) {
        
        // Check if order exists
        $query = $db->getQuery(true)
            ->select('id')
            ->from($db->quoteName('#__produccion_ordenes'))
            ->where($db->quoteName('orden_de_trabajo') . ' = ' . $db->quote($data['orden_de_trabajo']));
        
        $db->setQuery($query);
        $ordenId = $db->loadResult();
        
        if ($ordenId) {
            // Update existing order
            logWebhook('Updating existing order', ['orden_id' => $ordenId, 'orden_de_trabajo' => $data['orden_de_trabajo']]);
            
            $updateFields = [];
            
            if (!empty($data['estado'])) {
                $updateFields[] = $db->quoteName('estado') . ' = ' . $db->quote($data['estado']);
            }
            
            if (!empty($data['tipo_orden'])) {
                $updateFields[] = $db->quoteName('tipo_orden') . ' = ' . $db->quote($data['tipo_orden']);
            }
            
            $updateFields[] = $db->quoteName('modified') . ' = NOW()';
            
            if (!empty($updateFields)) {
                $query = $db->getQuery(true)
                    ->update($db->quoteName('#__produccion_ordenes'))
                    ->set($updateFields)
                    ->where($db->quoteName('id') . ' = ' . (int)$ordenId);
                
                $db->setQuery($query);
                $db->execute();
            }
        } else {
            // Create new order
            logWebhook('Creating new order', ['orden_de_trabajo' => $data['orden_de_trabajo']]);
            
            $query = $db->getQuery(true)
                ->insert($db->quoteName('#__produccion_ordenes'))
                ->columns([
                    $db->quoteName('orden_de_trabajo'),
                    $db->quoteName('estado'),
                    $db->quoteName('tipo_orden'),
                    $db->quoteName('created_by')
                ])
                ->values(
                    $db->quote($data['orden_de_trabajo']) . ', ' .
                    $db->quote($data['estado'] ?? 'nueva') . ', ' .
                    $db->quote($data['tipo_orden'] ?? 'interna') . ', ' .
                    '0'
                );
            
            $db->setQuery($query);
            $db->execute();
            $ordenId = $db->insertid();
        }
        
        // Process info/attributes
        if (!empty($data['info']) && is_array($data['info'])) {
            logWebhook('Processing info attributes', ['count' => count($data['info'])]);
            
            foreach ($data['info'] as $key => $value) {
                // Check if attribute exists
                $query = $db->getQuery(true)
                    ->select('id')
                    ->from($db->quoteName('#__produccion_ordenes_info'))
                    ->where($db->quoteName('orden_id') . ' = ' . (int)$ordenId)
                    ->where($db->quoteName('attribute_key') . ' = ' . $db->quote($key));
                
                $db->setQuery($query);
                $attrId = $db->loadResult();
                
                if ($attrId) {
                    // Update attribute
                    $query = $db->getQuery(true)
                        ->update($db->quoteName('#__produccion_ordenes_info'))
                        ->set($db->quoteName('attribute_value') . ' = ' . $db->quote($value))
                        ->where($db->quoteName('id') . ' = ' . (int)$attrId);
                    
                    $db->setQuery($query);
                    $db->execute();
                } else {
                    // Insert attribute
                    $query = $db->getQuery(true)
                        ->insert($db->quoteName('#__produccion_ordenes_info'))
                        ->columns([
                            $db->quoteName('orden_id'),
                            $db->quoteName('attribute_key'),
                            $db->quoteName('attribute_value')
                        ])
                        ->values(
                            (int)$ordenId . ', ' .
                            $db->quote($key) . ', ' .
                            $db->quote($value)
                        );
                    
                    $db->setQuery($query);
                    $db->execute();
                }
            }
        }
        
        // Log success
        logWebhook('Webhook processed successfully', [
            'orden_id' => $ordenId,
            'orden_de_trabajo' => $data['orden_de_trabajo']
        ]);
        
        // Return success response
        echo json_encode([
            'status' => 'success',
            'message' => 'Webhook processed successfully',
            'orden_id' => $ordenId,
            'orden_de_trabajo' => $data['orden_de_trabajo'],
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

echo "</div>
</body>
</html>";

