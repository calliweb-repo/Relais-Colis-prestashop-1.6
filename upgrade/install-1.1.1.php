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

function upgrade_module_1_1_1($module)
{
    $query = 'UPDATE `' . _DB_PREFIX_ . 'carrier` SET url = "' . pSQL(RelaisColisApi::TRACKING_URL) . '" WHERE url = "http://service.relaiscolis.com/tracking/trackandtrace.aspx?ens_id=@"';
    if (!Db::getInstance()->Execute($query)) {
        return false;
    }

    Configuration::updateGlobalValue('RC_MODULE_VERSION', '1.1.1');

    return true;
}
