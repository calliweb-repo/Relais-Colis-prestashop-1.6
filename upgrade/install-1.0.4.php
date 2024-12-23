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
function upgrade_module_1_0_4($module)
{
    $sql = [];

    $module->createOrderStates();

    $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'relaiscolis_evts` (
            `id_relais_colis_evts` int(11) NOT NULL AUTO_INCREMENT,
            `id_status` int(11) NOT NULL,
            `code_evenement` varchar(5) CHARACTER SET utf8 DEFAULT NULL,
            `code_justificatif` varchar(5) CHARACTER SET utf8 DEFAULT NULL,
            PRIMARY KEY (`id_relais_colis_evts`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;';

    $sql[] = 'TRUNCATE TABLE `' . _DB_PREFIX_ . 'relaiscolis_evts`';

    $sql[] = 'INSERT INTO `' . _DB_PREFIX_ . 'relaiscolis_evts` (`id_status`, `code_evenement`, `code_justificatif`) VALUES
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "REN", "1P"),
        (' . (int) Configuration::get('RC_STATE_LEC') . ', "RST", "1P"),
        (' . (int) Configuration::get('RC_STATE_LEC') . ', "RST", "2P"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "REN", "AGL"),
        (4, "APF", "ANA"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "REN", "AUT"),
        (4, "AAR", "AV"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "REN", "AV"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "REN", "AVA"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "SOR", "AVA"),
        (' . (int) Configuration::get('RC_STATE_LEC') . ', "RST", "CA"),
        (' . (int) Configuration::get('RC_STATE_LEC') . ', "RST", "CPS"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "REN", "DJR"),
        (4, "APF", "EAG"),
        (4, "RST", "EAR"),
        (4, "APF", "ECP"),
        (4, "APF", "EJD"),
        (4, "APF", "EL1"),
        (4, "APF", "EL4"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "APF", "EL9"),
        (4, "APF", "ENG"),
        (4, "APF", "EPS"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "APF", "ERP"),
        (4, "APF", "GAR"),
        (4, "APF", "L1"),
        (4, "APF", "L2"),
        (4, "APF", "L3"),
        (5, "SOL", "LIC"),
        (5, "SOL", "LID"),
        (5, "SOL", "LIM"),
        (5, "SOL", "LIP"),
        (5, "SOL", "LIR"),
        (5, "SOL", "LIS"),
        (5, "SOL", "LIV"),
        (5, "SOR", "LIV"),
        (4, "MAD", "MDE"),
        (4, "MAD", "MDI"),
        (4, "RST", "MDR"),
        (' . (int) Configuration::get('RC_STATE_RETURNED') . ', "APF", "P5"),
        (4, "APF", "PST"),
        (' . (int) Configuration::get('RC_STATE_LITIGE') . ', "SOR", "R18"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "REN", "R2"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "SOR", "R2"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "REN", "R20"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "SOR", "R20"),
        (' . (int) Configuration::get('RC_STATE_LITIGE') . ', "SOR", "R25"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "REN", "R3"),
        (' . (int) Configuration::get('RC_STATE_RC') . ', "SOR", "R3"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "REN", "R30"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "SOR", "R30"),
        (' . (int) Configuration::get('RC_STATE_RC') . ', "SOR", "R31"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "REN", "R32"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "SOR", "R32"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "REN", "R33"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "SOR", "R33"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "SOR", "R34"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "REN", "R40"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "SOR", "R40"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "REN", "R41"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "SOR", "R41"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "REN", "R42"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "SOR", "R42"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "REN", "R43"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "SOR", "R43"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "REN", "R44"),
        (' . (int) Configuration::get('RC_STATE_RC') . ', "SOR", "R44"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "REN", "R45"),
        (' . (int) Configuration::get('RC_STATE_RC') . ', "SOR", "R45"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "REN", "R46"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "SOR", "R46"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "REN", "R47"),
        (' . (int) Configuration::get('RC_STATE_RC') . ', "SOR", "R47"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "REN", "R4a"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "SOR", "R4a"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "REN", "R4b"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "SOR", "R4b"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "REN", "R5"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "SOR", "R5"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "REN", "R6"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "SOR", "R6"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "REN", "R7a"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "SOR", "R7a"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "REN", "R7b"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "SOR", "R7b"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "REN", "R8"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "SOR", "R8"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "REN", "R9"),
        (4, "APF", "RAQ"),
        (' . (int) Configuration::get('RC_STATE_RETURNED') . ', "APF", "RDF"),
        (' . (int) Configuration::get('RC_STATE_DER') . ', "RST", "REC"),
        (' . (int) Configuration::get('RC_STATE_DER') . ', "DEP", "REL"),
        (4, "RST", "RL"),
        (4, "RST", "RMQ"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "SOR", "RNC"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "APF", "RPA"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "SOR", "RPP"),
        (4, "APF", "SAG"),
        (4, "APF", "SL2"),
        (4, "CPF", "SL2"),
        (4, "APF", "SL3"),
        (4, "APF", "SL4"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "APF", "SL9"),
        (4, "APF", "SNG"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "APF", "SRA"),
        (' . (int) Configuration::get('RC_STATE_ECR') . ', "APF", "SRL")';

    foreach ($sql as $query) {
        if (Db::getInstance()->execute($query) == false) {
            return false;
        }
    }

    Configuration::updateGlobalValue('RC_MODULE_VERSION', '1.0.4');

    return true;
}
