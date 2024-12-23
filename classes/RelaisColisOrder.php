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

class RelaisColisOrder extends ObjectModel
{
    public $id_relais_colis_order;
    public $id_relais_colis_info;
    public $id_order;
    public $id_customer;
    public $order_weight;
    public $is_send = false;
    public $pdf_number;
    public $is_exported = false;
    public $letter_exported = false;
    public $letter_date;
    public static $definition = [
        'table' => 'relaiscolis_order',
        'primary' => 'id_relais_colis_order',
        'multilang' => false,
        'fields' => [
            'id_relais_colis_order' => [
                'type' => ObjectModel::TYPE_INT,
            ],
            'id_relais_colis_info' => [
                'type' => ObjectModel::TYPE_INT,
            ],
            'id_order' => [
                'type' => ObjectModel::TYPE_INT,
                'required' => true,
            ],
            'id_customer' => [
                'type' => ObjectModel::TYPE_INT,
                'required' => true,
            ],
            'order_weight' => [
                'type' => ObjectModel::TYPE_FLOAT,
                'required' => false,
            ],
            'is_send' => [
                'type' => ObjectModel::TYPE_BOOL,
                'required' => false,
            ],
            'pdf_number' => [
                'type' => ObjectModel::TYPE_STRING,
                'required' => false,
            ],
            'is_exported' => [
                'type' => ObjectModel::TYPE_BOOL,
                'required' => false,
             ],
            'letter_exported' => [
                'type' => ObjectModel::TYPE_BOOL,
                'required' => false,
            ],
            'letter_date' => [
                'type' => ObjectModel::TYPE_DATE,
                'required' => false,
            ],
        ],
    ];

    /**
     * Get valid delivery addresses for the order
     *
     * @param int $id_customer
     *
     * @return array
     */
    public static function getRCDeliveryAddresses($id_order)
    {
        if ((int) $id_order) {
            $order = new Order($id_order);

            $date_add_time = strtotime($order->date_add);
            $date_add_time_m1s = $date_add_time - 1;
            $date_add_time_p1s = $date_add_time + 1;

            $date_add_m1s = date('Y-m-d H:i:s', $date_add_time_m1s);
            $date_add_p1s = date('Y-m-d H:i:s', $date_add_time_p1s);

            return Db::getInstance()->executeS(
                'SELECT * 
                FROM ' . _DB_PREFIX_ . 'address 
                WHERE `id_customer` = ' . (int) $order->id_customer . " 
                    AND `alias` LIKE 'Relais colis -%' 
                    AND `date_add` BETWEEN '" . pSQL($date_add_m1s) . "' AND '" . pSQL($date_add_p1s) . "' 
                ORDER BY id_address DESC"
            );
        }
    }

    /**
     * Get the pdf number with order's ID
     *
     * @param int $id_order
     *
     * @return string
     */
    public static function getPdfNumber($id_order)
    {
        return Db::getInstance()->getValue(
            'SELECT pdf_number 
            FROM ' . _DB_PREFIX_ . pSQL(self::$definition['table']) . ' 
            WHERE id_order = ' . (int) $id_order
        );
    }

    /**
     * Get the pdf number with a relais colis order's ID
     *
     * @param int $id_relais_colis_order
     *
     * @return string
     */
    public static function getPdfNumberByIdRelais($id_relais_colis_order)
    {
        return Db::getInstance()->getValue(
            'SELECT pdf_number 
            FROM ' . _DB_PREFIX_ . pSQL(self::$definition['table']) . ' 
            WHERE id_relais_colis_order = ' . (int) $id_relais_colis_order
        );
    }

    /**
     * Get the relais colis order's ID with an order's ID
     *
     * @param type $id_order
     *
     * @return bool
     */
    public static function getRelaisColisOrderId($id_order)
    {
        if ((int) $id_order) {
            return Db::getInstance()->getValue(
                'SELECT id_relais_colis_order 
                FROM ' . _DB_PREFIX_ . pSQL(self::$definition['table']) . ' 
                WHERE id_order = ' . (int) $id_order
            );
        }

        return false;
    }

    /**
     * Get the Order's ID from the RelaisColisInfo's ID
     *
     * @param int $id_relais_colis_info
     *
     * @return mixed RelaisColisInfo's ID or false if not exists
     */
    public static function getRelaisColisOrderIdByIdRelaisColisInfo($id_relais_colis_info)
    {
        if ((int) $id_relais_colis_info) {
            return Db::getInstance()->getValue(
                'SELECT id_order 
                FROM ' . _DB_PREFIX_ . pSQL(self::$definition['table']) . ' 
                WHERE id_relais_colis_info = ' . (int) $id_relais_colis_info
            );
        }

        return false;
    }

    /**
     * Get the Relais Colis Order's ID with the EXTRACTED Number
     *
     * @param string $extract_number
     *
     * @return mixed Relais Colis Order's ID or false if there is no $extract_number
     */
    public static function getRelaisColisOrderIdWithExtractNumber($extract_number)
    {
        if ($extract_number) {
            return Db::getInstance()->getValue(
                'SELECT id_order 
                FROM ' . _DB_PREFIX_ . pSQL(self::$definition['table']) . '
                WHERE pdf_number LIKE "%' . pSQL($extract_number) . '%"'
            );
        }

        return false;
    }
}
