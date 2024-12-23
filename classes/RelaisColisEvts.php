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

class RelaisColisEvts extends ObjectModel
{
    public $id_relais_colis_evts;
    public $id_status;
    public $code_evenement;
    public $code_justificatif;

    public static $definition = [
        'table' => 'relaiscolis_evts',
        'primary' => 'id_relais_colis_evts',
        'multilang' => false,
        'fields' => [
            'id_relais_colis_evts' => [
                'type' => ObjectModel::TYPE_INT,
            ],
            'id_status' => [
                'type' => ObjectModel::TYPE_INT,
            ],
            'code_evenement' => [
                'type' => ObjectModel::TYPE_STRING,
            ],
            'code_justificatif' => [
                'type' => ObjectModel::TYPE_STRING,
            ],
        ],
    ];

    /**
     * Get the status's ID of an Order's event with the event's code and justification's code
     *
     * @param type $evts_code the event's code
     * @param type $evts_justif the justification's code
     *
     * @return mixed status's ID or false if there is no event's code and/or no justification's code
     */
    public static function getIdstate($evts_code, $evts_justif)
    {
        if ($evts_code && $evts_justif) {
            return Db::getInstance()->getValue('
                SELECT id_status 
                FROM ' . _DB_PREFIX_ . pSQL(self::$definition['table']) . '
                WHERE code_evenement = "' . pSQL($evts_code) . '" 
                AND code_justificatif = "' . pSQL($evts_justif) . '"'
            );
        }

        return false;
    }
}
