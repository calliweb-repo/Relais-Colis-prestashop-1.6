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
function upgrade_module_1_0_11($module)
{
    // TODO hook
    $module->registerHook('displayAdminProductsExtra');
    $module->registerHook('actionProductUpdate');
    // TAB
    $id_lang_en = LanguageCore::getIdByIso('en');
    $id_lang_fr = LanguageCore::getIdByIso('fr');
    $id_root_tab = Tab::getIdFromClassName('AdminParentModules');
    if (!Tab::getIdFromClassName('AdminManageRelaisColisOrderProduct')) {
        $module->installModuleTab(
            'AdminManageRelaisColisOrderProduct',
            [
                $id_lang_fr => 'Liste des multicolis Relais Colis',
                $id_lang_en => 'Relais Colis list multiple packages',
            ],
            $id_root_tab,
            false
        );
    }

    $sql = [];

    $sql[] = 'INSERT INTO `' . _DB_PREFIX_ . 'relaiscolis_evts` (`id_status`, `code_evenement`, `code_justificatif`) VALUES
        (5, "APF", "L4"),
        (5, "APF", "MPA")';

    // Relais Colis Product
    $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'relaiscolis_product` ADD `package_quantity` INT NULL DEFAULT 1;';

    // Relais Colis Order Product
    $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'relaiscolis_order_product` (
                `id_relais_colis_order_product` int(10) NOT NULL AUTO_INCREMENT,
                `id_relais_colis_order` int(10) NOT NULL,
                `id_product` int(10) NOT NULL,
    			`package_number` int(10) NOT NULL,
                `weight` float(10) NOT NULL,
                `id_relais_colis_order_pdf` int(10) NULL,
    			PRIMARY KEY  (`id_relais_colis_order_product`)
    			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;';

    $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'relaiscolis_order_pdf` (
                `id_relais_colis_order_pdf` int(10) NOT NULL AUTO_INCREMENT,
                `id_relais_colis_order` int(10) NOT NULL,
                `package_number` int(10) NOT NULL,
                `pdf_number` varchar(32) NOT NULL,
                PRIMARY KEY  (`id_relais_colis_order_pdf`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;';

    foreach ($sql as $query) {
        if (Db::getInstance()->execute($query) == false) {
            return false;
        }
    }

    return true;
}
