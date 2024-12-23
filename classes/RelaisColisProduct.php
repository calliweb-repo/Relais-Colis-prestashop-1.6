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

class RelaisColisProduct extends ObjectModel
{
    public $id_relais_colis_product;
    public $id_product_home;
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
    public $assembly;
    public $top;
    public $sensible;
    public $package_quantity;

    public static $definition = [
        'table' => 'relaiscolis_product',
        'primary' => 'id_relais_colis_product',
        'multilang' => false,
        'fields' => [
            'id_relais_colis_product' => [
                'type' => ObjectModel::TYPE_INT,
            ],
            'id_product_home' => [
                'type' => ObjectModel::TYPE_INT,
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
            'package_quantity' => [
                'type' => ObjectModel::TYPE_INT,
                'required' => false,
            ],
        ],
    ];

    /**
     * Get the Relais Colis product's ID with a product's ID
     *
     * @param int $id_product
     *
     * @return mixed Relais Colis product's ID or false if there's no id_product
     */
    public static function getRelaisColisProductId($id_product)
    {
        if ((int) $id_product) {
            return Db::getInstance()->getValue(
                'SELECT id_relais_colis_product 
                FROM ' . _DB_PREFIX_ . pSQL(self::$definition['table']) . ' 
                WHERE id_product_home = ' . (int) $id_product
            );
        }

        return false;
    }

    /**
     * Get the Relais Colis product's options with a product's ID
     *
     * @param int $id_product
     *
     * @return mixed Relais Colis product's options (array) or false if there's no id_product
     */
    public static function getProductOptions($id_product)
    {
        if ((int) $id_product) {
            return Db::getInstance()->executeS(
                'SELECT * 
                FROM ' . _DB_PREFIX_ . pSQL(self::$definition['table']) . ' 
                WHERE id_product_home = ' . (int) $id_product
            );
        }

        return false;
    }

    /**
     * Saves current object to database (add or update)
     *
     * @param bool $null_values
     * @param bool $auto_date
     *
     * @return bool Insertion result
     *
     * @throws PrestaShopException
     */
    public function save($null_values = false, $auto_date = true)
    {
        $this->id = $this->id_relais_colis_product;

        return (int) $this->id > 0 ? $this->update($null_values) : $this->add($auto_date, $null_values);
    }

    /**
     * Update package Quantity to 0
     */
    public static function removePackageQuantity()
    {
        Db::getInstance()->update(
            self::$definition['table'],
            [
                'package_quantity' => (int) 0,
            ]
        );
    }
}
