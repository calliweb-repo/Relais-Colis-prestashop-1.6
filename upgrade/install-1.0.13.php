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
function upgrade_module_1_0_13($module)
{
    $sql = [];
    $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'relaiscolis_order` ADD `letter_exported` TINYINT NULL DEFAULT NULL';
    $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'relaiscolis_order` ADD `letter_date` DATE NULL DEFAULT NULL';

    foreach ($sql as $query) {
        if (Db::getInstance()->execute($query) == false) {
            return false;
        }
    }

    Configuration::updateGlobalValue('RC_MODULE_VERSION', '1.0.13');

    return true;
}
