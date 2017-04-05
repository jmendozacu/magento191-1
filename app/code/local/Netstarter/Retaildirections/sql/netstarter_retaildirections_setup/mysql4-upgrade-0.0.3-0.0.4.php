<?php

$installer = $this;
$installer->startSetup();

/*
 * Simple call to add the new newzealand website.
 * For a new created website, a install script needs to be added. E.g. new USA store/website.
 */
$installer->addProductWebsiteScopeColumns('newzealand');

/*
 * Resets DDL cache since we changed schema.
 */

$installer->getConnection()->resetDdlCache();
$installer->endSetup();