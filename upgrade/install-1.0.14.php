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
function upgrade_module_1_0_14($module)
{
    $sql = [];
    $sql[] = 'UPDATE `' . _DB_PREFIX_ . "carrier` SET `url` = 'http://service.relaiscolis.com/tracking/trackandtrace.aspx?ens_id=@' WHERE `deleted` = 0 AND `is_module` = 1 AND (`external_module_name` = 'relaiscolis' OR `external_module_name` = 'relaiscolisplus')";

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

    Configuration::updateGlobalValue('RC_MODULE_VERSION', '1.0.14');

    return true;
}
