<?php
/**
 * Update Webhook Integration
 * Integrates webhook into component URL structure
 */

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html>
<head>
    <title>Update Webhook Integration</title>
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
    <h1>🔧 Update Webhook Integration</h1>
    <p><strong>Integrating webhook into component URL structure</strong></p>";

$joomla_root = dirname(__FILE__);
$admin_path = $joomla_root . '/administrator/components/com_produccion';
$site_path = $joomla_root . '/components/com_produccion';

// Get server URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$server_url = $protocol . $_SERVER['HTTP_HOST'];
$webhook_url = $server_url . '/index.php?option=com_produccion&task=webhook.receive';

echo "<h3>1. Creating Site Webhook Controller</h3>";

// Create site webhook controller
$webhook_controller = '<?php
namespace Joomla\\Component\\Produccion\\Site\\Controller;

defined(\'_JEXEC\') or die;

use Joomla\\CMS\\MVC\\Controller\\BaseController;
use Joomla\\CMS\\Factory;
use Joomla\\CMS\\Response\\JsonResponse;

class WebhookController extends BaseController
{
    public function receive()
    {
        // Set JSON response
        header(\'Content-Type: application/json\');
        
        // Get application
        $app = Factory::getApplication();
        
        // Log function
        $logFile = JPATH_ADMINISTRATOR . \'/logs/webhook.log\';
        
        try {
            // Get request data
            $input = $app->input;
            $method = $_SERVER[\'REQUEST_METHOD\'];
            
            // Get all headers
            $headers = [];
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == \'HTTP_\') {
                    $headers[str_replace(\' \', \'-\', ucwords(strtolower(str_replace(\'_\', \' \', substr($name, 5)))))] = $value;
                }
            }
            
            // Get body
            $body = file_get_contents(\'php://input\');
            $data = json_decode($body, true);
            
            if (!$data) {
                $data = $input->post->getArray();
            }
            
            // Log request
            $logMessage = "\\n=== WEBHOOK REQUEST ===\\n";
            $logMessage .= "Time: " . date(\'Y-m-d H:i:s\') . "\\n";
            $logMessage .= "Method: " . $method . "\\n";
            $logMessage .= "Headers: " . json_encode($headers) . "\\n";
            $logMessage .= "Data: " . json_encode($data) . "\\n";
            file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
            
            // Process webhook data
            $db = Factory::getDbo();
            $user = Factory::getUser(0); // System user
            
            if (!empty($data[\'orden_de_trabajo\'])) {
                // Check if order exists
                $query = $db->getQuery(true)
                    ->select(\'id\')
                    ->from($db->quoteName(\'#__produccion_ordenes\'))
                    ->where($db->quoteName(\'orden_de_trabajo\') . \' = \' . $db->quote($data[\'orden_de_trabajo\']));
                
                $db->setQuery($query);
                $ordenId = $db->loadResult();
                
                if ($ordenId) {
                    // Update existing order
                    $updateFields = [];
                    
                    if (!empty($data[\'estado\'])) {
                        $updateFields[] = $db->quoteName(\'estado\') . \' = \' . $db->quote($data[\'estado\']);
                    }
                    
                    if (!empty($data[\'tipo_orden\'])) {
                        $updateFields[] = $db->quoteName(\'tipo_orden\') . \' = \' . $db->quote($data[\'tipo_orden\']);
                    }
                    
                    $updateFields[] = $db->quoteName(\'modified\') . \' = NOW()\';
                    
                    if (!empty($updateFields)) {
                        $query = $db->getQuery(true)
                            ->update($db->quoteName(\'#__produccion_ordenes\'))
                            ->set($updateFields)
                            ->where($db->quoteName(\'id\') . \' = \' . (int)$ordenId);
                        
                        $db->setQuery($query);
                        $db->execute();
                    }
                } else {
                    // Insert new order
                    $query = $db->getQuery(true)
                        ->insert($db->quoteName(\'#__produccion_ordenes\'))
                        ->columns([
                            $db->quoteName(\'orden_de_trabajo\'),
                            $db->quoteName(\'estado\'),
                            $db->quoteName(\'tipo_orden\'),
                            $db->quoteName(\'created_by\'),
                            $db->quoteName(\'created\')
                        ])
                        ->values(
                            $db->quote($data[\'orden_de_trabajo\']) . \', \' .
                            $db->quote($data[\'estado\'] ?? \'nueva\') . \', \' .
                            $db->quote($data[\'tipo_orden\'] ?? \'interna\') . \', \' .
                            (int)$user->id . \', \' .
                            \'NOW()\'
                        );
                    
                    $db->setQuery($query);
                    $db->execute();
                    $ordenId = $db->insertid();
                }
                
                // Process EAV data
                if (!empty($data[\'info\']) && is_array($data[\'info\'])) {
                    foreach ($data[\'info\'] as $key => $value) {
                        // Check if attribute exists
                        $query = $db->getQuery(true)
                            ->select(\'id\')
                            ->from($db->quoteName(\'#__produccion_ordenes_info\'))
                            ->where($db->quoteName(\'orden_id\') . \' = \' . (int)$ordenId)
                            ->where($db->quoteName(\'attribute_key\') . \' = \' . $db->quote($key));
                        
                        $db->setQuery($query);
                        $attrId = $db->loadResult();
                        
                        if ($attrId) {
                            // Update
                            $query = $db->getQuery(true)
                                ->update($db->quoteName(\'#__produccion_ordenes_info\'))
                                ->set($db->quoteName(\'attribute_value\') . \' = \' . $db->quote($value))
                                ->where($db->quoteName(\'id\') . \' = \' . (int)$attrId);
                            
                            $db->setQuery($query);
                            $db->execute();
                        } else {
                            // Insert
                            $query = $db->getQuery(true)
                                ->insert($db->quoteName(\'#__produccion_ordenes_info\'))
                                ->columns([
                                    $db->quoteName(\'orden_id\'),
                                    $db->quoteName(\'attribute_key\'),
                                    $db->quoteName(\'attribute_value\')
                                ])
                                ->values(
                                    (int)$ordenId . \', \' .
                                    $db->quote($key) . \', \' .
                                    $db->quote($value)
                                );
                            
                            $db->setQuery($query);
                            $db->execute();
                        }
                    }
                }
                
                // Log success
                file_put_contents($logFile, "SUCCESS: Order ID: " . $ordenId . "\\n", FILE_APPEND | LOCK_EX);
                
                // Return success response
                echo json_encode([
                    \'status\' => \'success\',
                    \'message\' => \'Webhook processed successfully\',
                    \'orden_id\' => $ordenId,
                    \'orden_de_trabajo\' => $data[\'orden_de_trabajo\']
                ]);
            } else {
                echo json_encode([
                    \'status\' => \'error\',
                    \'message\' => \'Missing orden_de_trabajo\',
                    \'received_data\' => $data
                ]);
            }
            
        } catch (\\Exception $e) {
            file_put_contents($logFile, "ERROR: " . $e->getMessage() . "\\n", FILE_APPEND | LOCK_EX);
            
            http_response_code(500);
            echo json_encode([
                \'status\' => \'error\',
                \'message\' => $e->getMessage()
            ]);
        }
        
        $app->close();
    }
}';

$controller_file = $site_path . '/src/Controller/WebhookController.php';
if (!is_dir(dirname($controller_file))) {
    mkdir(dirname($controller_file), 0755, true);
}

if (file_put_contents($controller_file, $webhook_controller)) {
    echo "<div class='success'>✅ Created site webhook controller</div>";
} else {
    echo "<div class='error'>❌ Failed to create site webhook controller</div>";
}

echo "<h3>2. Creating Enhanced Webhook Template</h3>";

// Create enhanced webhook template with copy button and Postman export
$webhook_template = '<?php
defined(\'_JEXEC\') or die;

use Joomla\\CMS\\Language\\Text;
use Joomla\\CMS\\Uri\\Uri;

// Get server URL
$uri = Uri::getInstance();
$server_url = $uri->toString([\'scheme\', \'host\', \'port\']);
$webhook_url = $server_url . \'/public_webhook.php\';

// Sample webhook payload
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

// Postman collection
$postman_collection = [
    "info" => [
        "name" => "Production Management System - Webhook",
        "description" => "Webhook endpoint for Production Management System",
        "schema" => "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
    ],
    "item" => [
        [
            "name" => "Create/Update Production Order",
            "request" => [
                "method" => "POST",
                "header" => [
                    [
                        "key" => "Content-Type",
                        "value" => "application/json"
                    ]
                ],
                "body" => [
                    "mode" => "raw",
                    "raw" => json_encode($sample_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                ],
                "url" => [
                    "raw" => $webhook_url,
                    "protocol" => parse_url($webhook_url, PHP_URL_SCHEME),
                    "host" => [parse_url($webhook_url, PHP_URL_HOST)],
                    "path" => [ltrim(parse_url($webhook_url, PHP_URL_PATH), \'/\')]
                ],
                "description" => "Create or update a production order via webhook"
            ]
        ],
        [
            "name" => "Test Webhook Connection",
            "request" => [
                "method" => "GET",
                "header" => [],
                "url" => [
                    "raw" => $webhook_url,
                    "protocol" => parse_url($webhook_url, PHP_URL_SCHEME),
                    "host" => [parse_url($webhook_url, PHP_URL_HOST)],
                    "path" => [ltrim(parse_url($webhook_url, PHP_URL_PATH), \'/\')]
                ],
                "description" => "Test webhook connectivity"
            ]
        ]
    ]
];

$postman_json = json_encode($postman_collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>

<style>
.webhook-container {
    max-width: 100%;
    padding: 20px;
}

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
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
}

.btn-primary {
    background: #0d6efd;
    color: white;
}

.btn-primary:hover {
    background: #0b5ed7;
}

.btn-success {
    background: #198754;
    color: white;
}

.btn-success:hover {
    background: #157347;
}

.btn-info {
    background: #0dcaf0;
    color: #000;
}

.btn-info:hover {
    background: #31d2f2;
}

.alert {
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 20px;
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
    font-family: \'Courier New\', Courier, monospace;
}

#copyFeedback {
    display: none;
    margin-top: 10px;
}
</style>

<div class="webhook-container">
    <h1>🔗 Webhook Configuration</h1>
    
    <div class="webhook-card">
        <h3>📍 Webhook Endpoint URL</h3>
        <p>Use this URL to send production order data to the system:</p>
        
        <div class="webhook-url-box" id="webhookUrl">
            <?php echo htmlspecialchars($webhook_url); ?>
        </div>
        
        <div class="btn-group">
            <button onclick="copyWebhookUrl()" class="btn btn-primary">
                <span>📋</span> Copy URL
            </button>
            
            <button onclick="testWebhook()" class="btn btn-info">
                <span>🧪</span> Test Connection
            </button>
            
            <button onclick="downloadPostmanCollection()" class="btn btn-success">
                <span>📦</span> Download Postman Collection
            </button>
        </div>
        
        <div id="copyFeedback" class="alert alert-success">
            ✅ URL copied to clipboard!
        </div>
        
        <div id="testResult"></div>
    </div>
    
    <div class="webhook-card">
        <h3>📝 Sample Request</h3>
        <p>Example POST request body:</p>
        
        <div class="code-block">
            <pre><?php echo htmlspecialchars(json_encode($sample_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
        </div>
        
        <h4>Using cURL:</h4>
        <div class="code-block">
            <pre>curl -X POST "<?php echo htmlspecialchars($webhook_url); ?>" \\
  -H "Content-Type: application/json" \\
  -d \'<?php echo json_encode($sample_payload, JSON_UNESCAPED_UNICODE); ?>\'</pre>
        </div>
    </div>
    
    <div class="webhook-card">
        <h3>ℹ️ Important Information</h3>
        <div class="alert alert-info">
            <ul style="margin: 0; padding-left: 20px;">
                <li><strong>Public Access:</strong> This webhook is publicly accessible and does not require authentication</li>
                <li><strong>Method:</strong> Accepts both GET and POST requests</li>
                <li><strong>Content-Type:</strong> application/json</li>
                <li><strong>Logs:</strong> All requests are logged to <code>/administrator/logs/webhook.log</code></li>
            </ul>
        </div>
    </div>
</div>

<script>
// Copy webhook URL to clipboard
function copyWebhookUrl() {
    const url = document.getElementById(\'webhookUrl\').textContent.trim();
    
    navigator.clipboard.writeText(url).then(function() {
        const feedback = document.getElementById(\'copyFeedback\');
        feedback.style.display = \'block\';
        
        setTimeout(function() {
            feedback.style.display = \'none\';
        }, 3000);
    }).catch(function(err) {
        alert(\'Failed to copy: \' + err);
    });
}

// Test webhook connection
function testWebhook() {
    const url = document.getElementById(\'webhookUrl\').textContent.trim();
    const resultDiv = document.getElementById(\'testResult\');
    
    resultDiv.innerHTML = \'<div class="alert alert-info">🔄 Testing connection...</div>\';
    
    fetch(url, {
        method: \'GET\'
    })
    .then(response => response.json())
    .then(data => {
        resultDiv.innerHTML = `
            <div class="alert alert-success" style="margin-top: 15px;">
                <h4>✅ Connection Successful!</h4>
                <p>Response: ${JSON.stringify(data, null, 2)}</p>
            </div>
        `;
    })
    .catch(error => {
        resultDiv.innerHTML = `
            <div class="alert" style="background: #f8d7da; border: 1px solid #f5c2c7; color: #842029; margin-top: 15px;">
                <h4>❌ Connection Failed</h4>
                <p>Error: ${error.message}</p>
            </div>
        `;
    });
}

// Download Postman collection
function downloadPostmanCollection() {
    const collection = <?php echo $postman_json; ?>;
    
    const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(collection, null, 2));
    const downloadAnchor = document.createElement(\'a\');
    downloadAnchor.setAttribute("href", dataStr);
    downloadAnchor.setAttribute("download", "production_management_webhook.postman_collection.json");
    document.body.appendChild(downloadAnchor);
    downloadAnchor.click();
    downloadAnchor.remove();
    
    alert(\'✅ Postman collection downloaded!\\n\\nImport it in Postman: File → Import → Select the downloaded JSON file\');
}
</script>';

$webhook_file = $admin_path . '/tmpl/webhook/default.php';
$webhook_legacy = $admin_path . '/views/webhook/tmpl/default.php';

// Update both locations
$updated = 0;

if (file_put_contents($webhook_file, $webhook_template)) {
    echo "<div class='success'>✅ Updated webhook template (modern location)</div>";
    $updated++;
}

if (file_exists(dirname($webhook_legacy))) {
    if (file_put_contents($webhook_legacy, $webhook_template)) {
        echo "<div class='success'>✅ Updated webhook template (legacy location)</div>";
        $updated++;
    }
}

if ($updated > 0) {
    echo "<div class='success'>✅ Webhook view successfully updated with enhanced features!</div>";
} else {
    echo "<div class='error'>❌ Failed to update webhook view</div>";
}

echo "<h3>2. Features Added</h3>";

echo "<div class='info'>
    <h4>✨ New Webhook View Features:</h4>
    <ul>
        <li>📋 <strong>Copy URL Button</strong> - One-click copy webhook URL to clipboard</li>
        <li>🧪 <strong>Test Connection Button</strong> - Test webhook endpoint directly from admin</li>
        <li>📦 <strong>Download Postman Collection</strong> - Export ready-to-use Postman collection</li>
        <li>📝 <strong>Sample Request</strong> - Shows example payload and cURL command</li>
        <li>ℹ️ <strong>Important Info</strong> - Displays webhook configuration details</li>
    </ul>
</div>";

echo "<h3>3. Test the Updated View</h3>";

echo "<div class='info'>
    <p><strong>Access the webhook configuration:</strong></p>
    <p><a href='/administrator/index.php?option=com_produccion&view=webhook' target='_blank' class='btn btn-primary'>
        Open Webhook Configuration
    </a></p>
</div>";

echo "</div>
</body>
</html>";
?>
