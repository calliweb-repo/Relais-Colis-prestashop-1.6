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

class RelaisColisReturn extends ObjectModel
{
    public $id_relais_colis_return;
    public $id_order_return;
    public $id_order;
    public $id_customer;
    public $is_send = false;
    public $pdf_number;
    public $token;
    public $image_url;
    public $bordereau_smart_url;
    public $services;
    public static $definition = [
        'table' => 'relaiscolis_return',
        'primary' => 'id_relais_colis_return',
        'multilang' => false,
        'fields' => [
            'id_relais_colis_return' => [
                'type' => ObjectModel::TYPE_INT,
            ],
            'id_order_return' => [
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
            'is_send' => [
                'type' => ObjectModel::TYPE_BOOL,
                'required' => false,
            ],
            'pdf_number' => [
                'type' => ObjectModel::TYPE_STRING,
                'required' => false,
            ],
            'token' => [
                'type' => ObjectModel::TYPE_STRING,
                'required' => false,
            ],
            'image_url' => [
                'type' => ObjectModel::TYPE_STRING,
                'required' => false,
            ],
            'bordereau_smart_url' => [
                'type' => ObjectModel::TYPE_STRING,
                'required' => false,
            ],
            'services' => [
                'type' => ObjectModel::TYPE_STRING,
                'required' => false,
            ],
        ],
    ];

    /**
     * Get the PDF number with an OrderReturn's ID
     *
     * @param int $id_order_return
     *
     * @return string
     */
    public static function getPdfNumber($id_order_return)
    {
        return Db::getInstance()->getValue(
            'SELECT pdf_number 
            FROM ' . _DB_PREFIX_ . pSQL(self::$definition['table']) . ' 
            WHERE id_order_return = ' . (int) $id_order_return
        );
    }

    /**
     * Get the services with an OrderReturn's ID
     *
     * @param int $id_order_return
     *
     * @return string
     */
    public static function getServices($id_order_return)
    {
        return Db::getInstance()->getValue(
            'SELECT services 
            FROM ' . _DB_PREFIX_ . pSQL(self::$definition['table']) . ' 
            WHERE id_order_return = ' . (int) $id_order_return
        );
    }

    /**
     * Get the image URL with an OrderReturn's ID
     *
     * @param int $id_order_return
     *
     * @return string
     */
    public static function getImageUrl($id_order_return)
    {
        return Db::getInstance()->getValue(
            'SELECT image_url 
            FROM ' . _DB_PREFIX_ . pSQL(self::$definition['table']) . ' 
            WHERE id_order_return = ' . (int) $id_order_return
        );
    }

    /**
     * Get the Bordereau Smart Url URL with an OrderReturn's ID
     *
     * @param int $id_order_return
     *
     * @return string
     */
    public static function getBordereauSmartUrl($id_order_return)
    {
        return Db::getInstance()->getValue(
            'SELECT bordereau_smart_url 
            FROM ' . _DB_PREFIX_ . pSQL(self::$definition['table']) . ' 
            WHERE id_order_return = ' . (int) $id_order_return
        );
    }

    /**
     * Get the token with an OrderReturn's ID
     *
     * @param int $id_order_return
     *
     * @return string
     */
    public static function getTokenNumber($id_order_return)
    {
        return Db::getInstance()->getValue(
            'SELECT token 
            FROM ' . _DB_PREFIX_ . pSQL(self::$definition['table']) . ' 
            WHERE id_order_return = ' . (int) $id_order_return
        );
    }

    /**
     * Get the PDF number with a Relais Colis Return's ID
     *
     * @param int $id_order_return
     *
     * @return string
     */
    public static function getPdfNumberByIdRelais($id_relais_colis_return)
    {
        return Db::getInstance()->getValue(
            'SELECT pdf_number 
            FROM ' . _DB_PREFIX_ . pSQL(self::$definition['table']) . ' 
            WHERE id_relais_colis_return = ' . (int) $id_relais_colis_return
        );
    }

    /**
     * Get the Relais Colis Return's ID with the OrderReturn's ID
     *
     * @param int $id_order_return
     *
     * @return int
     */
    public static function getRelaisColisReturnId($id_order_return)
    {
        if ((int) $id_order_return) {
            return Db::getInstance()->getValue(
                'SELECT id_relais_colis_return 
                FROM ' . _DB_PREFIX_ . pSQL(self::$definition['table']) . ' 
                WHERE id_order_return = ' . (int) $id_order_return
            );
        }

        return false;
    }

    /**
     * Get the Relais Colis Return with the Customer's ID
     *
     * @param int $id_customer
     *
     * @return array
     */
    public static function getRelaisColisReturnByCustomer($id_customer)
    {
        if ((int) $id_customer) {
            return Db::getInstance()->executeS(
                'SELECT * 
                FROM ' . _DB_PREFIX_ . pSQL(self::$definition['table']) . ' rr
                LEFT JOIN ' . _DB_PREFIX_ . 'order_return oret ON oret.id_order_return = rr.id_order_return
                WHERE rr.id_customer = ' . (int) $id_customer
            );
        }

        return false;
    }
}
