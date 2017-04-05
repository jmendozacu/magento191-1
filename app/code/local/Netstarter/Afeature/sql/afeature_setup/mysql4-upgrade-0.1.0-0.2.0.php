<?php
$installer = $this;
$installer->startSetup();
$installer->run("ALTER TABLE {$this->getTable('afeature/afeature')} ADD COLUMN bg_color varchar(20) NOT NULL default ''");
$installer->endSetup(); 