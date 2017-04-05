<?php
$installer = $this;
$installer->startSetup();
$installer->run("ALTER TABLE {$this->getTable('afeature/afeature')} MODIFY title varchar(40) NOT NULL default ''");
$installer->endSetup(); 