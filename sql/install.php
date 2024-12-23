<?php
/**
 * 1969-2024 Relais Colis
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
 *  @author    Calliweb <contact@calliweb.fr>
 *  @copyright 1969-2024 Relais Colis
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) { exit; }

$sql = [];
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'relaiscolis_info` (
            `id_relais_colis_info` int(10) NOT NULL AUTO_INCREMENT,
            `id_cart` int(10) NOT NULL,
            `id_customer` int(10) NOT NULL,
			`rel` varchar(64) NOT NULL,
			`rel_name` varchar(64) NOT NULL,
			`rel_adr` text NOT NULL,
			`rel_cp` text(10) NOT NULL,
			`rel_vil` varchar(64) NOT NULL,
			`pseudo_rvc` varchar(64) NOT NULL,
			`frc_max` varchar(32) NOT NULL,
			`floc_rel` varchar(64) NOT NULL,
			`fcod_pays` varchar(10) NOT NULL,
			`type_liv` varchar(20) NOT NULL,
			`age_code` varchar(10) NOT NULL,
			`age_nom` varchar(64) NOT NULL,
            `age_adr` text NOT NULL,
            `age_vil` varchar(64) NOT NULL,
			`age_cp` varchar(10) NOT NULL,
            `ouvlun` varchar(128) NOT NULL,
            `ouvmar` varchar(128) NOT NULL,
            `ouvmer` varchar(128) NOT NULL,
            `ouvjeu` varchar(128) NOT NULL,
            `ouvven` varchar(128) NOT NULL,
            `ouvsam` varchar(128) NOT NULL,
            `ouvdim` varchar(128) NOT NULL,
            `selected_date` datetime NOT NULL,
            `smart` TINYINT NULL DEFAULT NULL,
            `sending_date` DATE NULL DEFAULT NULL,
			PRIMARY KEY  (`id_relais_colis_info`)
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;';

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

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'relaiscolis_order` (
            `id_relais_colis_order` int(10) NOT NULL AUTO_INCREMENT,
            `id_relais_colis_info` int(10) NOT NULL,
            `id_order` int(10) NOT NULL,
            `id_customer` int(10) NOT NULL,
            `order_weight` float(10) NOT NULL,
			`is_send` TINYINT(1) NOT NULL,
			`pdf_number` varchar(32) NOT NULL,
            `is_exported` TINYINT NULL DEFAULT NULL,
            `letter_exported` TINYINT NULL DEFAULT NULL,
            `letter_date` DATE NULL DEFAULT NULL,
			PRIMARY KEY  (`id_relais_colis_order`)
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'relaiscolis_return` (
            `id_relais_colis_return` int(10) NOT NULL AUTO_INCREMENT,
            `id_order_return` int(10) NOT NULL,
            `id_order` int(10) NOT NULL,
            `id_customer` int(10) NOT NULL,
			`is_send` TINYINT(1) NOT NULL,
			`pdf_number` varchar(32) NOT NULL,
            `token` varchar(255) NOT NULL,
            `image_url` varchar(500) DEFAULT NULL,
            `bordereau_smart_url` varchar(500) DEFAULT NULL,
            `services` varchar(100) NOT NULL,
			PRIMARY KEY  (`id_relais_colis_return`)
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'relaiscolis_product` (
            `id_relais_colis_product` int(11) NOT NULL AUTO_INCREMENT,
            `id_product_home` int(11) NOT NULL,
            `schedule` tinyint(1) NOT NULL,
            `retrieve_old_equipment` tinyint(1) NOT NULL,
            `delivery_on_floor` tinyint(1) NOT NULL,
            `delivery_at_two` tinyint(1) NOT NULL,
            `turn_on_home_appliance` tinyint(1) NOT NULL,
            `mount_furniture` tinyint(1) NOT NULL,
            `non_standard` tinyint(1) NOT NULL,
            `unpacking` tinyint(1) NOT NULL,
            `evacuation_packaging` tinyint(1) NOT NULL,
            `recovery` tinyint(1) NOT NULL,
            `delivery_desired_room` tinyint(1) NOT NULL,
            `recover_old_bedding` tinyint(1) NOT NULL,
            `assembly` tinyint(1) NOT NULL,
            `top` tinyint(1) NOT NULL,
            `sensible` tinyint(1) NOT NULL,
            `package_quantity` INT NULL DEFAULT 1,
            PRIMARY KEY (`id_relais_colis_product`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'relaiscolis_infohome` (
           `id_relais_colis_info_home` int(10) NOT NULL AUTO_INCREMENT,
            `id_cart` int(10) NOT NULL,
            `id_customer` int(10) NOT NULL,
            `id_order` int(10) NOT NULL,
            `schedule` tinyint(1) NOT NULL,
            `retrieve_old_equipment` tinyint(1) NOT NULL,
            `delivery_on_floor` tinyint(1) NOT NULL,
            `delivery_at_two` tinyint(1) NOT NULL,
            `turn_on_home_appliance` tinyint(1) NOT NULL,
            `mount_furniture` tinyint(1) NOT NULL,
            `non_standard` tinyint(1) NOT NULL,
            `unpacking` tinyint(1) NOT NULL,
            `evacuation_packaging` tinyint(1) NOT NULL,
            `recovery` tinyint(1) NOT NULL,
            `delivery_desired_room` tinyint(1) NOT NULL,
            `recover_old_bedding` tinyint(1) NOT NULL,
            `delivery_eco` tinyint(1) NOT NULL,
            `assembly` tinyint(1) NOT NULL,
            `top` tinyint(1) NOT NULL,
            `sensible` tinyint(1) NOT NULL,
            `digicode` varchar(64) DEFAULT NULL,
            `floor_delivery` int(3) NOT NULL,
            `type_home` int(3) NOT NULL,
            `elevator` tinyint(1) NOT NULL,
            PRIMARY KEY (`id_relais_colis_info_home`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'relaiscolis_homeoptions` (
            `id_relais_colis_home_option` int(11) NOT NULL AUTO_INCREMENT,
            `option` varchar(80) CHARACTER SET utf8 NOT NULL,
            `cost` float(12,6) NOT NULL,
            `active` tinyint(1) NOT NULL,
            `customer_choice` tinyint(1) NOT NULL,
            `label` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
            PRIMARY KEY (`id_relais_colis_home_option`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=latin1 AUTO_INCREMENT=15 ;';

$sql[] = 'TRUNCATE TABLE `' . _DB_PREFIX_ . 'relaiscolis_homeoptions`';

$sql[] = 'INSERT INTO `' . _DB_PREFIX_ . 'relaiscolis_homeoptions` (`id_relais_colis_home_option`, `option`, `cost`, `active`, `customer_choice`, `label`) VALUES
(1, "schedule", 0.000000, 0, 0, "Prise de Rendez-vous"),
(2, "retrieve_old_equipment", 0.000000, 1, 0, "Enlèvement de l\'ancien matériel"),
(3, "delivery_on_floor", 0.000000, 1, 0, "Livraison à l \'étage"),
(4, "delivery_at_two", 0.000000, 1, 0, "Livraison à deux"),
(5, "turn_on_home_appliance", 0.000000, 1, 0, "M.E.S. gros électro-ménager"),
(6, "mount_furniture", 0.000000, 1, 0, "Montage petit meuble"),
(7, "non_standard", 0.000000, 1, 0, "Hors Norme"),
(8, "unpacking", 0.000000, 1, 0, "Déballage produit"),
(9, "evacuation_packaging", 0.000000, 1, 0, "Evacuation Emballage"),
(10, "recovery", 0.000000, 1, 0, "Reprise D3E"),
(11, "delivery_desired_room", 0.000000, 1, 0, "Livraison dans la pièce souhaitée"),
(12, "recover_old_bedding", 0.000000, 1, 0, "Reprise Ancienne Literie"),
(13, "delivery_eco", 0.000000, 0, 0, "Livraison «ECO»"),
(14, "assembly", 0.000000, 1, 0, "Assemblage");';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'relaiscolis_evts` (
            `id_relais_colis_evts` int(11) NOT NULL AUTO_INCREMENT,
            `id_status` int(11) NOT NULL,
            `code_evenement` varchar(5) CHARACTER SET utf8 DEFAULT NULL,
            `code_justificatif` varchar(5) CHARACTER SET utf8 DEFAULT NULL,
            PRIMARY KEY (`id_relais_colis_evts`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;';

$sql[] = 'TRUNCATE TABLE `' . _DB_PREFIX_ . 'relaiscolis_evts`';

$sql[] = 'INSERT INTO `' . _DB_PREFIX_ . "relaiscolis_evts` (`id_status`, `code_evenement`, `code_justificatif`) VALUES
(4, 'AAR', 'AV'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'APF', 'EL9'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'APF', 'ERP'),
(4, 'APF', 'L3'),
(4, 'APF', 'L4'),
(4, 'APF', 'L6'),
(" . (int) Configuration::get('RC_STATE_RETURNED') . ", 'APF', 'P5'),
(4, 'APF', 'PST'),
(4, 'APF', 'RAQ'),
(" . (int) Configuration::get('RC_STATE_RETURNED') . ", 'APF', 'RDF'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'APF', 'RPA'),
(4, 'APF', 'SAG'),
(4, 'APF', 'SL2'),
(4, 'APF', 'SL3'),
(4, 'APF', 'SL4'),
(4, 'APF', 'SL6'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'APF', 'SL9'),
(4, 'APF', 'SNG'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'APF', 'SRA'),
(4, 'CPF', 'SL2'),
(" . (int) Configuration::get('RC_STATE_DER') . ", 'DEP', 'REL'),
(4, 'MAD', 'MDE'),
(4, 'MAD', 'MDI'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'REN', '1P'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'REN', 'AGL'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'REN', 'AUT'),
(" . (int) Configuration::get('RC_STATE_DER') . ", 'REN', 'AV'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'REN', 'AVA'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'REN', 'DJR'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'REN', 'R2'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'REN', 'R20'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'REN', 'R3'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'REN', 'R30'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'REN', 'R32'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'REN', 'R33'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'REN', 'R40'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'REN', 'R41'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'REN', 'R42'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'REN', 'R43'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'REN', 'R44'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'REN', 'R45'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'REN', 'R46'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'REN', 'R47'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'REN', 'R4a'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'REN', 'R4b'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'REN', 'R5'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'REN', 'R6'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'REN', 'R7a'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'REN', 'R7b'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'REN', 'R8'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'REN', 'R9'),
(" . (int) Configuration::get('RC_STATE_LEC') . ", 'RST', '1P'),
(" . (int) Configuration::get('RC_STATE_LEC') . ", 'RST', '2P'),
(" . (int) Configuration::get('RC_STATE_LEC') . ", 'RST', 'CA'),
(" . (int) Configuration::get('RC_STATE_LEC') . ", 'RST', 'CPS'),
(" . (int) Configuration::get('RC_STATE_LEC') . ", 'RST', 'EAR'),
(" . (int) Configuration::get('RC_STATE_LEC') . ", 'RST', 'MDR'),
(" . (int) Configuration::get('RC_STATE_DER') . ", 'RST', 'REC'),
(" . (int) Configuration::get('RC_STATE_LEC') . ", 'RST', 'RL'),
(" . (int) Configuration::get('RC_STATE_LEC') . ", 'RST', 'RMQ'),
(5, 'SOL', 'LIC'),
(5, 'SOL', 'LID'),
(5, 'SOL', 'LIM'),
(5, 'SOL', 'LIP'),
(5, 'SOL', 'LIR'),
(5, 'SOL', 'LIS'),
(5, 'SOL', 'LIV'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'SOR', 'AVA'),
(5, 'SOR', 'LIV'),
(" . (int) Configuration::get('RC_STATE_LITIGE') . ", 'SOR', 'R18'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'SOR', 'R2'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'SOR', 'R20'),
(" . (int) Configuration::get('RC_STATE_LITIGE') . ", 'SOR', 'R25'),
(" . (int) Configuration::get('RC_STATE_RC') . ", 'SOR', 'R3'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'SOR', 'R30'),
(" . (int) Configuration::get('RC_STATE_RC') . ", 'SOR', 'R31'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'SOR', 'R32'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'SOR', 'R33'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'SOR', 'R34'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'SOR', 'R40'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'SOR', 'R41'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'SOR', 'R42'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'SOR', 'R43'),
(" . (int) Configuration::get('RC_STATE_RC') . ", 'SOR', 'R44'),
(" . (int) Configuration::get('RC_STATE_RC') . ", 'SOR', 'R45'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'SOR', 'R46'),
(" . (int) Configuration::get('RC_STATE_RC') . ", 'SOR', 'R47'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'SOR', 'R4a'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'SOR', 'R4b'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'SOR', 'R5'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'SOR', 'R6'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'SOR', 'R7a'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'SOR', 'R7b'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'SOR', 'R8'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'SOR', 'RNC'),
(" . (int) Configuration::get('RC_STATE_ECR') . ", 'SOR', 'RPP')";

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
