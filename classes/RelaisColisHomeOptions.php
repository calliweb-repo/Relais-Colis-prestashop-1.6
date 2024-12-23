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

class RelaisColisHomeOptions extends ObjectModel
{
    public $id_relais_colis_home_option;
    public $option;
    public $cost;
    public $customer_choice;
    public $label;
    public static $definition = [
        'table' => 'relaiscolis_homeoptions',
        'primary' => 'id_relais_colis_home_option',
        'multilang' => false,
        'fields' => [
            'id_relais_colis_home_option' => [
                'type' => ObjectModel::TYPE_INT,
            ],
            'option' => [
                'type' => ObjectModel::TYPE_STRING,
            ],
            'cost' => [
                'type' => ObjectModel::TYPE_FLOAT,
            ],
            'customer_choice' => [
                'type' => ObjectModel::TYPE_BOOL,
            ],
            'label' => [
                'type' => ObjectModel::TYPE_STRING,
            ],
        ],
    ];

    /**
     * Get ID of Relais Colis home options
     *
     * @param string $options
     *
     * @return mixed false if there is no options, ID else
     */
    public static function getRelaisColisHomeOptionsIdByOptions($options)
    {
        if ($options) {
            return Db::getInstance()->getValue(
                'SELECT id_relais_colis_home_options 
                FROM ' . _DB_PREFIX_ . pSQL(self::$definition['table']) . '
                WHERE option = "' . pSQL($options) . '"'
            );
        }

        return false;
    }

    /**
     * Get all active Relais Colis Home options
     *
     * @return array names of options
     */
    public static function getRelaisColisHomeOptionsActive()
    {
        return Db::getInstance()->executeS(
            'SELECT * 
            FROM ' . _DB_PREFIX_ . pSQL(self::$definition['table']) . '
            WHERE active = 1'
        );
    }
}
