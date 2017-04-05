<?php

$installer = $this;
$installer->startSetup();

/*
 * Simple call to add the new nz website.
 * Full Synch will need to be rnu again, as data is not migrated.
 */
$installer->dropProductWebsiteScopeColumns('newzealand');
$installer->addProductWebsiteScopeColumns('bntnz');

/*
 * Resets DDL cache since we changed schema.
 */

$installer->getConnection()->resetDdlCache();
$installer->endSetup();