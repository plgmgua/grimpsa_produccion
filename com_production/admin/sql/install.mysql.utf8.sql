-- Create the main work orders table
CREATE TABLE IF NOT EXISTS `joomla_produccion_ordenes` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `orden_de_trabajo` varchar(15) NOT NULL,
    `estado` enum('nueva','en_proceso','terminada','cerrada') DEFAULT 'nueva',
    `tipo_orden` enum('interna','externa') DEFAULT 'interna',
    `created_by` int(11) NOT NULL,
    `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `modified` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    `publish_up` datetime DEFAULT NULL,
    `publish_down` datetime DEFAULT NULL,
    `checked_out` int(11) DEFAULT NULL,
    `checked_out_time` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_orden_trabajo` (`orden_de_trabajo`),
    KEY `idx_estado` (`estado`),
    KEY `idx_tipo_orden` (`tipo_orden`),
    KEY `idx_created` (`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create the EAV table for order information (following your existing pattern)
CREATE TABLE IF NOT EXISTS `joomla_produccion_ordenes_info` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `orden_id` char(5) NOT NULL,
    `tipo_de_campo` varchar(50) NOT NULL,
    `valor` mediumtext NOT NULL,
    `usuario` varchar(50) DEFAULT NULL,
    `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_orden_id` (`orden_id`),
    KEY `idx_tipo_campo` (`tipo_de_campo`),
    KEY `idx_timestamp` (`timestamp`),
    KEY `idx_orden_tipo` (`orden_id`, `tipo_de_campo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create table for daily attendance (for technician selection)
CREATE TABLE IF NOT EXISTS `joomla_produccion_asistencia` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `personname` varchar(100) NOT NULL,
    `authdate` date NOT NULL,
    `authdatetime` datetime NOT NULL,
    `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_authdate` (`authdate`),
    KEY `idx_personname` (`personname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create table for webhook logs
CREATE TABLE IF NOT EXISTS `joomla_produccion_webhook_logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `webhook_type` varchar(50) NOT NULL,
    `orden_id` varchar(15) DEFAULT NULL,
    `payload` longtext NOT NULL,
    `response` text DEFAULT NULL,
    `status` enum('success','error','pending') DEFAULT 'pending',
    `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_webhook_type` (`webhook_type`),
    KEY `idx_orden_id` (`orden_id`),
    KEY `idx_status` (`status`),
    KEY `idx_created` (`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create configuration table
CREATE TABLE IF NOT EXISTS `joomla_produccion_config` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `param` varchar(100) NOT NULL,
    `value` text,
    `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `modified` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_param` (`param`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default configuration
INSERT INTO `joomla_produccion_config` (`param`, `value`) VALUES
('webhook_secret', ''),
('webhook_enabled', '1'),
('webhook_url', ''),
('default_orden_prefix', 'ORD'),
('auto_assign_tecnico', '0');