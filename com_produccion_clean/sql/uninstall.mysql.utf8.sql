-- Drop tables in reverse order to avoid foreign key constraints
DROP TABLE IF EXISTS `#__produccion_webhook_logs`;
DROP TABLE IF EXISTS `#__produccion_asistencia`;
DROP TABLE IF EXISTS `#__produccion_ordenes_info`;
DROP TABLE IF EXISTS `#__produccion_ordenes`;
DROP TABLE IF EXISTS `#__produccion_config`;