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

function upgrade_module_1_1_0($module)
{
    $module->uninstallModuleTab('AdminManageRelaisColis');
    $module->uninstallModuleTab('AdminManageRelaisColisMenu');
    $module->uninstallModuleTab('AdminManageRelaisColisReturn');
    $module->uninstallModuleTab('AdminManageRelaisColisConfig');
    $module->uninstallModuleTab('AdminManageRelaisColisHome');
    $module->uninstallModuleTab('AdminManageRelaisColisHomeOptions');
    $module->uninstallModuleTab('AdminManageRelaisColisOrderProduct');

    $id_lang_en = LanguageCore::getIdByIso('en');
    $id_lang_fr = LanguageCore::getIdByIso('fr');

    if (!Tab::getIdFromClassName('AdminManageRelaisColisMenu')) {
        $module->installModuleTab(
            'AdminManageRelaisColisMenu',
            [
            $id_lang_fr => 'Relais Colis',
            $id_lang_en => 'Relais Colis',
            ],
            0,
            true,
            1,
            'local_shipping'
        );
    }
    $id_rc_tab = Tab::getIdFromClassName('AdminManageRelaisColisMenu');

    if (!Tab::getIdFromClassName('AdminManageRelaisColisConfig')) {
        $module->installModuleTab(
            'AdminManageRelaisColisConfig',
            [
                $id_lang_fr => 'Configuration',
                $id_lang_en => 'Configuration',
            ],
            $id_rc_tab,
            true,
            0,
            false
        );
    }
    if (!Tab::getIdFromClassName('AdminManageRelaisColisHome')) {
        $module->installModuleTab(
            'AdminManageRelaisColisHome',
            [
                $id_lang_fr => 'Configuration produits Relais Colis',
                $id_lang_en => 'Relais Colis Products Configuration',
            ],
            $id_rc_tab,
            true,
            1,
            false
        );
    }
    if (!Tab::getIdFromClassName('AdminManageRelaisColisHomeOptions')) {
        $module->installModuleTab(
            'AdminManageRelaisColisHomeOptions',
            [
                $id_lang_fr => 'Configuration prix options Relais Colis',
                $id_lang_en => 'Relais Colis Options price Configuration',
            ],
            $id_rc_tab,
            true,
            2,
            false
        );
    }
    if (!Tab::getIdFromClassName('AdminManageRelaisColisOrderProduct')) {
        $module->installModuleTab(
            'AdminManageRelaisColisOrderProduct',
            [
                $id_lang_fr => 'Liste des multicolis Relais Colis',
                $id_lang_en => 'Relais Colis list multiple packages',
            ],
            $id_rc_tab,
            true,
            3,
            false
        );
    }
    if (!Tab::getIdFromClassName('AdminManageRelaisColis')) {
        $module->installModuleTab(
            'AdminManageRelaisColis',
            [
                $id_lang_fr => 'Commandes Relais Colis',
                $id_lang_en => 'Relais Colis Orders',
            ],
            $id_rc_tab,
            true,
            4,
            false
        );
    }
    if (!Tab::getIdFromClassName('AdminManageRelaisColisReturn')) {
        $module->installModuleTab(
            'AdminManageRelaisColisReturn',
            [
                $id_lang_fr => 'Retour Relais Colis',
                $id_lang_en => 'Relais Colis Returns',
            ],
            $id_rc_tab,
            true,
            5,
            false
        );
    }

    $query = 'UPDATE `' . _DB_PREFIX_ . 'carrier` SET url = "' . pSQL(RelaisColisApi::TRACKING_URL) . '" WHERE url = "http://service.relaiscolis.com/tracking/trackandtrace.aspx?ens_id=@"';
    if (!Db::getInstance()->Execute($query)) {
        return false;
    }

    $query = 'ALTER TABLE `' . _DB_PREFIX_ . 'relaiscolis_order` MODIFY `order_weight` FLOAT(10) NOT NULL ;';
    if (!Db::getInstance()->Execute($query)) {
        return false;
    }

    Configuration::updateGlobalValue('RC_MODULE_VERSION', '1.1.0');

    return true;
}
