-- Manual Component Registration for com_produccion
-- Run this SQL in your Joomla database to register the component

-- Insert component into extensions table
INSERT INTO `joomla_extensions` (
    `name`, 
    `type`, 
    `element`, 
    `folder`, 
    `client_id`, 
    `enabled`, 
    `access`, 
    `protected`, 
    `manifest_cache`, 
    `params`, 
    `custom_data`, 
    `system_data`, 
    `checked_out`, 
    `checked_out_time`, 
    `ordering`, 
    `state`
) VALUES (
    'com_produccion',
    'component',
    'com_produccion',
    '',
    1,
    1,
    1,
    0,
    '{"name":"com_produccion","type":"component","creationDate":"2024-12-18","author":"Grimpsa","copyright":"Copyright (C) 2024 Grimpsa. All rights reserved.","authorEmail":"info@grimpsa.com","authorUrl":"https://grimpsa.com","version":"1.0.44","description":"COM_PRODUCCION_XML_DESCRIPTION","group":""}',
    '{}',
    '',
    '',
    0,
    '0000-00-00 00:00:00',
    0,
    0
);

-- Get the extension ID (you'll need this for the next step)
SET @extension_id = LAST_INSERT_ID();

-- Insert into components table
INSERT INTO `joomla_components` (
    `name`,
    `link`,
    `menuid`,
    `parent`,
    `admin_menu_link`,
    `admin_menu_alt`,
    `option`,
    `ordering`,
    `admin_menu_img`,
    `iscore`,
    `params`,
    `enabled`
) VALUES (
    'com_produccion',
    'option=com_produccion',
    0,
    0,
    'option=com_produccion',
    'COM_PRODUCCION',
    'com_produccion',
    0,
    'class:component',
    0,
    '',
    1
);

-- Insert menu items for admin interface
INSERT INTO `joomla_menu` (
    `menutype`,
    `title`,
    `alias`,
    `note`,
    `path`,
    `link`,
    `type`,
    `published`,
    `parent_id`,
    `level`,
    `component_id`,
    `checked_out`,
    `checked_out_time`,
    `browserNav`,
    `access`,
    `img`,
    `template_style_id`,
    `params`,
    `lft`,
    `rgt`,
    `home`,
    `language`,
    `client_id`
) VALUES 
('main', 'COM_PRODUCCION', 'com-produccion', '', 'com-produccion', 'index.php?option=com_produccion', 'component', 1, 1, 1, @extension_id, 0, '0000-00-00 00:00:00', 0, 1, 'class:component', 0, '{"menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}', 0, 0, 0, '*', 1),
('main', 'COM_PRODUCCION_DASHBOARD', 'dashboard', '', 'com-produccion/dashboard', 'index.php?option=com_produccion&view=dashboard', 'component', 1, (SELECT id FROM joomla_menu WHERE alias = 'com-produccion'), 2, @extension_id, 0, '0000-00-00 00:00:00', 0, 1, 'class:component', 0, '{"menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}', 0, 0, 0, '*', 1),
('main', 'COM_PRODUCCION_ORDENES', 'ordenes', '', 'com-produccion/ordenes', 'index.php?option=com_produccion&view=ordenes', 'component', 1, (SELECT id FROM joomla_menu WHERE alias = 'com-produccion'), 2, @extension_id, 0, '0000-00-00 00:00:00', 0, 1, 'class:component', 0, '{"menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}', 0, 0, 0, '*', 1),
('main', 'COM_PRODUCCION_WEBHOOK', 'webhook', '', 'com-produccion/webhook', 'index.php?option=com_produccion&view=webhook', 'component', 1, (SELECT id FROM joomla_menu WHERE alias = 'com-produccion'), 2, @extension_id, 0, '0000-00-00 00:00:00', 0, 1, 'class:component', 0, '{"menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}', 0, 0, 0, '*', 1),
('main', 'COM_PRODUCCION_DEBUG', 'debug', '', 'com-produccion/debug', 'index.php?option=com_produccion&view=debug', 'component', 1, (SELECT id FROM joomla_menu WHERE alias = 'com-produccion'), 2, @extension_id, 0, '0000-00-00 00:00:00', 0, 1, 'class:component', 0, '{"menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}', 0, 0, 0, '*', 1);

-- Update menu hierarchy (lft/rgt values)
-- This is a simplified version - you may need to adjust lft/rgt values based on your menu structure
