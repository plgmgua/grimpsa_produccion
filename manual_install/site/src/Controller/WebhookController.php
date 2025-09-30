<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_produccion
 *
 * @copyright   (C) 2024 Grimpsa. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Produccion\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

/**
 * Webhook Controller
 */
class WebhookController extends BaseController
{
    /**
     * Receive webhook data (alias for process)
     */
    public function receive()
    {
        return $this->process();
    }

    /**
     * Process incoming webhook data
     */
    public function process()
    {
        // Get the application
        $app = Factory::getApplication();
        
        // Set content type to JSON
        $app->setHeader('Content-Type', 'application/json');
        
        // Bypass authentication for webhook access
        // This allows anyone to access the webhook without being logged in
        
        try {
            // Get the input data
            $input = $app->input;
            $rawData = file_get_contents('php://input');
            $data = json_decode($rawData, true);
            
            // Log incoming webhook request
            $this->logToFile('Webhook Request Received', [
                'raw_data' => $rawData,
                'parsed_data' => $data,
                'headers' => $this->getAllHeaders()
            ]);
            
            // Validate webhook secret if configured
            $this->validateWebhook($input);
            
            // Log the webhook request
            $this->logWebhookRequest('work_order', $data);
            
            // Process the work order data
            $result = $this->processWorkOrder($data);
            
            // Return success response
            $app->setHeader('HTTP/1.1 200 OK');
            echo json_encode([
                'status' => 'success',
                'message' => 'Work order processed successfully',
                'orden_id' => $result['orden_id'] ?? null
            ]);
            
        } catch (Exception $e) {
            // Log the error
            Log::add('Webhook Error: ' . $e->getMessage(), Log::ERROR, 'com_produccion');
            
            // Also log to custom file
            $this->logToFile('Webhook Error: ' . $e->getMessage());
            
            // Return error response
            $app->setHeader('HTTP/1.1 400 Bad Request');
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
        
        $app->close();
    }
    
    /**
     * Validate webhook (simplified - no header validation)
     */
    private function validateWebhook($input)
    {
        // No validation required - accept all requests
        // This allows simple JSON payloads without custom headers
        return true;
    }
    
    /**
     * Log webhook request
     */
    private function logWebhookRequest($type, $data)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        
        $query->insert($db->quoteName('#__produccion_webhook_logs'))
              ->columns([
                  $db->quoteName('webhook_type'),
                  $db->quoteName('orden_id'),
                  $db->quoteName('payload'),
                  $db->quoteName('status')
              ])
              ->values([
                  $db->quote($type),
                  $db->quote($data['orden_de_trabajo'] ?? ''),
                  $db->quote(json_encode($data)),
                  $db->quote('pending')
              ]);
        
        $db->setQuery($query);
        $db->execute();
    }
    
    /**
     * Process work order data
     */
    private function processWorkOrder($data)
    {
        $db = Factory::getDbo();
        
        try {
            $db->transactionStart();
            
            // Extract order ID
            $ordenId = $data['orden_de_trabajo'] ?? $this->generateOrderId();
            
            // Create or update main order record
            $this->createOrUpdateOrder($ordenId, $data);
            
            // Process all the EAV data
            $this->processOrderInfo($ordenId, $data);
            
            $db->transactionCommit();
            
            return ['orden_id' => $ordenId];
            
        } catch (Exception $e) {
            $db->transactionRollback();
            throw $e;
        }
    }
    
    /**
     * Create or update main order record
     */
    private function createOrUpdateOrder($ordenId, $data)
    {
        $db = Factory::getDbo();
        // Use system user (ID 0) for webhook operations
        $user = Factory::getUser(0);
        
        // Check if order exists
        $query = $db->getQuery(true);
        $query->select('id')
              ->from($db->quoteName('#__produccion_ordenes'))
              ->where($db->quoteName('orden_de_trabajo') . ' = ' . $db->quote($ordenId));
        
        $db->setQuery($query);
        $existingId = $db->loadResult();
        
        if ($existingId) {
            // Update existing order
            $query = $db->getQuery(true);
            $query->update($db->quoteName('#__produccion_ordenes'))
                  ->set($db->quoteName('modified') . ' = NOW()')
                  ->where($db->quoteName('id') . ' = ' . $db->quote($existingId));
            
            $db->setQuery($query);
            $db->execute();
        } else {
            // Create new order
            $query = $db->getQuery(true);
            $query->insert($db->quoteName('#__produccion_ordenes'))
                  ->columns([
                      $db->quoteName('orden_de_trabajo'),
                      $db->quoteName('estado'),
                      $db->quoteName('tipo_orden'),
                      $db->quoteName('created_by')
                  ])
                  ->values([
                      $db->quote($ordenId),
                      $db->quote('nueva'),
                      $db->quote($data['tipo_de_orden'] ?? 'interna'),
                      $db->quote($user->id)
                  ]);
            
            $db->setQuery($query);
            $db->execute();
        }
    }
    
    /**
     * Process order information (EAV pattern)
     */
    private function processOrderInfo($ordenId, $data)
    {
        $db = Factory::getDbo();
        // Use system user (ID 0) for webhook operations
        $user = Factory::getUser(0);
        
        // Define the fields to process
        $fields = [
            'fecha_de_solicitud', 'fecha_de_entrega', 'nombre_del_cliente', 'nit',
            'direccion_de_entrega', 'descripcion_de_trabajo', 'material', 'medidas_en_pulgadas',
            'adjuntar_cotizacion', 'observaciones_instrucciones_generales', 'color_de_impresion',
            'direccion_de_correo_electronico', 'tiro_retiro', 'valor_a_facturar', 'archivo_de_arte',
            'contacto_nombre', 'contacto_telefono', 'contacto_correo_electronico', 'tipo_de_orden',
            'estado', 'tecnico', 'detalles'
        ];
        
        // Add all finish types and their details
        $finishTypes = [
            'corte', 'doblado', 'laminado', 'lomo', 'numerado', 'pegado', 'sizado',
            'engrapado', 'troquel', 'troquel_cameo', 'despuntados', 'ojetes', 'perforado',
            'bloqueado', 'barniz', 'impresion_en_blanco'
        ];
        
        foreach ($finishTypes as $finish) {
            $fields[] = $finish;
            $fields[] = 'detalles_de_' . $finish;
        }
        
        // Process each field
        foreach ($fields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                $this->insertOrderInfo($ordenId, $field, $data[$field], 'webhook');
            }
        }
    }
    
    /**
     * Insert order information record
     */
    private function insertOrderInfo($ordenId, $tipoCampo, $valor, $usuario)
    {
        $db = Factory::getDbo();
        
        // Check if record already exists
        $query = $db->getQuery(true);
        $query->select('id')
              ->from($db->quoteName('#__produccion_ordenes_info'))
              ->where($db->quoteName('orden_id') . ' = ' . $db->quote($ordenId))
              ->where($db->quoteName('tipo_de_campo') . ' = ' . $db->quote($tipoCampo));
        
        $db->setQuery($query);
        $existingId = $db->loadResult();
        
        if ($existingId) {
            // Update existing record
            $query = $db->getQuery(true);
            $query->update($db->quoteName('#__produccion_ordenes_info'))
                  ->set($db->quoteName('valor') . ' = ' . $db->quote($valor))
                  ->set($db->quoteName('usuario') . ' = ' . $db->quote($usuario))
                  ->set($db->quoteName('timestamp') . ' = NOW()')
                  ->where($db->quoteName('id') . ' = ' . $db->quote($existingId));
            
            $db->setQuery($query);
            $db->execute();
        } else {
            // Insert new record
            $query = $db->getQuery(true);
            $query->insert($db->quoteName('#__produccion_ordenes_info'))
                  ->columns([
                      $db->quoteName('orden_id'),
                      $db->quoteName('tipo_de_campo'),
                      $db->quoteName('valor'),
                      $db->quoteName('usuario')
                  ])
                  ->values([
                      $db->quote($ordenId),
                      $db->quote($tipoCampo),
                      $db->quote($valor),
                      $db->quote($usuario)
                  ]);
            
            $db->setQuery($query);
            $db->execute();
        }
    }
    
    /**
     * Generate new order ID if not provided
     */
    private function generateOrderId()
    {
        $db = Factory::getDbo();
        
        // Get the last order number
        $query = $db->getQuery(true);
        $query->select('MAX(CAST(orden_de_trabajo AS UNSIGNED))')
              ->from($db->quoteName('#__produccion_ordenes'));
        
        $db->setQuery($query);
        $lastNumber = $db->loadResult();
        
        // Generate new number
        $newNumber = ($lastNumber ? $lastNumber + 1 : 1);
        
        return str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }
    
    /**
     * Log to custom file
     */
    private function logToFile($message, $data = null)
    {
        $logFile = JPATH_ROOT . '/administrator/logs/com_produccion_log.txt';
        
        // Ensure directory exists
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logEntry = date('Y-m-d H:i:s') . " - " . $message;
        if ($data) {
            $logEntry .= " - " . json_encode($data);
        }
        $logEntry .= "\n";
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Get all HTTP headers
     */
    private function getAllHeaders()
    {
        $headers = [];
        
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } else {
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }
        }
        
        return $headers;
    }
}
