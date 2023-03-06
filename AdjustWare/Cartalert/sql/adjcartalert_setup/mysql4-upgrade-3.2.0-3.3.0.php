<?php

$installer = $this;

$installer->startSetup();

$date = Mage::getStoreConfig('catalog/adjcartalert/from_date');

$installer->run("
ALTER TABLE {$this->getTable('adjcartalert')} ADD `quote_is_active` tinyint(1) unsigned NOT NULL default '1';
ALTER TABLE {$this->getTable('adjcartalert_history')} ADD `quote_is_active` tinyint(1) unsigned NOT NULL default '1';

INSERT INTO {$this->getTable('core/config_data')} (`scope` , `scope_id` , `path` , `value` )
    VALUES ('default', '0', 'catalog/adjcartalert/order_from_date', '$date');
");

$installer->endSetup();  
