<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_1_0($module)
{
    $sql = [];
    
    // Add custom fields support
    $sql[] = 'ALTER TABLE `'._DB_PREFIX_.'smsauth_codes` 
              ADD COLUMN `custom_data` TEXT AFTER `attempts`';
    
    // Add provider statistics
    $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'smsauth_stats` (
        `id_stat` int(11) NOT NULL AUTO_INCREMENT,
        `id_provider` int(11) NOT NULL,
        `success_count` int(11) DEFAULT 0,
        `fail_count` int(11) DEFAULT 0,
        `date_stat` date NOT NULL,
        PRIMARY KEY (`id_stat`),
        KEY `id_provider` (`id_provider`),
        KEY `date_stat` (`date_stat`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8mb4;';
    
    foreach ($sql as $query) {
        if (!Db::getInstance()->execute($query)) {
            return false;
        }
    }
    
    // Update module version
    Configuration::updateValue('SMSAUTH_VERSION', '1.1.0');
    
    return true;
}