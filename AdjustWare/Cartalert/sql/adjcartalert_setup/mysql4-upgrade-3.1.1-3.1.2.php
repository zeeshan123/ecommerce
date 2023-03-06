<?php

$installer = $this;

$installer->startSetup();


$installer->run("

ALTER TABLE `".$installer->getTable('adjcartalert/cartalert')."` ADD `customer_group_id` INT(10) UNSIGNED DEFAULT NULL;

");

$installer->endSetup(); 
