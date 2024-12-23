<?php
/**
 * 1969-2018 Relais Colis
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@relaiscolis.com so we can send you a copy immediately.
 *
 *  @author    Quadra Informatique <modules@quadra-informatique.fr>
 *  @copyright 1969-2018 Relais Colis
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
function upgrade_module_1_0_22($module)
{
    $query = 'SELECT * FROM INFORMATION_SCHEMA.COLUMNS
		WHERE COLUMN_NAME= "image_url"
		AND TABLE_NAME=  "' . _DB_PREFIX_ . 'relaiscolis_return"
		AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';

    $result = Db::getInstance()->ExecuteS($query);

    // adding column image_url
    if (!$result) {
        $query = 'ALTER TABLE `' . _DB_PREFIX_ . 'relaiscolis_return` ADD `image_url` VARCHAR(500) NULL DEFAULT NULL AFTER `token`';
        Db::getInstance()->Execute($query);
    }

    $query = 'SELECT * FROM INFORMATION_SCHEMA.COLUMNS
			  WHERE COLUMN_NAME= "bordereau_smart_url"
			  AND TABLE_NAME=  "' . _DB_PREFIX_ . 'relaiscolis_return"
			  AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';

    $result = Db::getInstance()->ExecuteS($query);

    // adding column bordereau_smart_url
    if (!$result) {
        $query = 'ALTER TABLE `' . _DB_PREFIX_ . 'relaiscolis_return` ADD `bordereau_smart_url` VARCHAR(500) NULL DEFAULT NULL AFTER `image_url`';
        Db::getInstance()->Execute($query);
    }

    $query = 'SELECT * FROM INFORMATION_SCHEMA.COLUMNS
			  WHERE COLUMN_NAME= "services"
			  AND TABLE_NAME=  "' . _DB_PREFIX_ . 'relaiscolis_return"
			  AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';

    $result = Db::getInstance()->ExecuteS($query);

    // adding column services
    if (!$result) {
        $query = 'ALTER TABLE `' . _DB_PREFIX_ . 'relaiscolis_return` ADD `services` VARCHAR(100) NULL DEFAULT NULL ;';
        Db::getInstance()->Execute($query);
    }

    if (Module::isInstalled('relaiscolisplus')) {
        $relaiscolisplus = new RelaisColisPlus();
        $relaiscolisplus->uninstall();
    }

    $carrier = $module->addCarrierHomePlus();
    $module->addZones($carrier);
    $module->addGroups($carrier);
    $module->addRangesHome($carrier);
    Configuration::updateGlobalValue('RC_MODULE_VERSION', '1.0.22');

    return true;
}
