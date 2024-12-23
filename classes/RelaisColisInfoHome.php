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

class RelaisColisInfoHome extends ObjectModel
{
    public $id_relais_colis_info_home;
    public $id_cart;
    public $id_customer;
    public $id_order;
    public $schedule;
    public $retrieve_old_equipment;
    public $delivery_on_floor;
    public $delivery_at_two;
    public $turn_on_home_appliance;
    public $mount_furniture;
    public $non_standard;
    public $unpacking;
    public $evacuation_packaging;
    public $recovery;
    public $delivery_desired_room;
    public $recover_old_bedding;
    public $delivery_eco;
    public $assembly;
    public $top;
    public $sensible;
    public $digicode;
    public $floor_delivery;
    public $type_home;
    public $elevator;
    public static $definition = [
        'table' => 'relaiscolis_infohome',
        'primary' => 'id_relais_colis_info_home',
        'multilang' => false,
        'fields' => [
            'id_relais_colis_info_home' => [
                'type' => ObjectModel::TYPE_INT,
            ],
            'id_cart' => [
                'type' => ObjectModel::TYPE_INT,
                'required' => true,
            ],
            'id_customer' => [
                'type' => ObjectModel::TYPE_INT,
                'required' => true,
            ],
            'id_order' => [
                'type' => ObjectModel::TYPE_INT,
                'required' => false,
            ],
            'schedule' => [
                'type' => ObjectModel::TYPE_BOOL,
            ],
            'retrieve_old_equipment' => [
                'type' => ObjectModel::TYPE_BOOL,
                'required' => false,
            ],
            'delivery_on_floor' => [
                'type' => ObjectModel::TYPE_BOOL,
                'required' => false,
            ],
            'delivery_at_two' => [
                'type' => ObjectModel::TYPE_BOOL,
                'required' => false,
            ],
            'turn_on_home_appliance' => [
                'type' => ObjectModel::TYPE_BOOL,
                'required' => false,
            ],
            'mount_furniture' => [
                'type' => ObjectModel::TYPE_BOOL,
                'required' => false,
            ],
            'non_standard' => [
                'type' => ObjectModel::TYPE_BOOL,
                'required' => false,
            ],
            'unpacking' => [
                'type' => ObjectModel::TYPE_BOOL,
                'required' => false,
            ],
            'evacuation_packaging' => [
                'type' => ObjectModel::TYPE_BOOL,
                'required' => false,
            ],
            'recovery' => [
                'type' => ObjectModel::TYPE_BOOL,
                'required' => false,
            ],
            'delivery_desired_room' => [
                'type' => ObjectModel::TYPE_BOOL,
                'required' => false,
            ],
            'recover_old_bedding' => [
                'type' => ObjectModel::TYPE_BOOL,
                'required' => false,
            ],
            'delivery_eco' => [
                'type' => ObjectModel::TYPE_BOOL,
                'required' => false,
            ],
            'assembly' => [
                'type' => ObjectModel::TYPE_BOOL,
                'required' => false,
            ],
            'top' => [
                'type' => ObjectModel::TYPE_BOOL,
                'required' => false,
            ],
            'sensible' => [
                'type' => ObjectModel::TYPE_BOOL,
                'required' => false,
            ],
            'digicode' => [
                'type' => ObjectModel::TYPE_STRING,
                'required' => false,
            ],
            'floor_delivery' => [
                'type' => ObjectModel::TYPE_INT,
                'required' => false,
            ],
            'type_home' => [
                'type' => ObjectModel::TYPE_INT,
                'required' => false,
            ],
            'elevator' => [
                'type' => ObjectModel::TYPE_BOOL,
                'required' => false,
            ],
        ],
    ];

    /**
     * Check if a Relais Colis Info Home already exists for a Cart and Customer IDs tuple
     *
     * @param int $id_cart
     * @param int $id_customer
     *
     * @return mixed Relais Colis Info's ID or false if not exists
     */
    public static function alreadyExists($id_cart = 0, $id_customer = 0)
    {
        if (!$id_cart || !$id_customer) {
            return false;
        }

        return Db::getInstance()->getValue(
            'SELECT id_relais_colis_info_home 
            FROM ' . _DB_PREFIX_ . pSQL(self::$definition['table']) . '
            WHERE id_cart = ' . (int) $id_cart . ' 
                AND id_customer =' . (int) $id_customer
        );
    }

    /**
     * Get the last Relais Colis Home selected by a Customer for a specific Cart.
     * If there is no id_cart or id_customer, it takes them from context.
     *
     * @param int $id_cart
     * @param int $id_customer
     *
     * @return mixed Relais Colis Info's ID or false if not exists
     */
    public static function getInfoHomeByIdOrder($id_order = 0)
    {
        if (!(int) $id_order) {
            return false;
        }

        return Db::getInstance()->getValue(
            'SELECT id_relais_colis_info_home 
            FROM ' . _DB_PREFIX_ . pSQL(self::$definition['table']) . '
            WHERE id_order = ' . (int) $id_order
        );
    }
}
