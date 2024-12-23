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

class RelaisColisInfo extends ObjectModel
{
    public $id_relais_colis_info;
    public $id_cart;
    public $id_customer;
    public $rel = '';
    public $rel_name = '';
    public $rel_adr = '';
    public $rel_cp = '';
    public $rel_vil = '';
    public $pseudo_rvc = '';
    public $frc_max = '';
    public $floc_rel = '';
    public $fcod_pays = '';
    public $type_liv = '';
    public $age_code = '';
    public $age_nom = '';
    public $age_adr = '';
    public $age_vil = '';
    public $age_cp = '';
    public $ouvlun = '';
    public $ouvmar = '';
    public $ouvmer = '';
    public $ouvjeu = '';
    public $ouvven = '';
    public $ouvsam = '';
    public $ouvdim = '';
    public $selected_date;
    public $smart;
    public $sending_date;

    public static $definition = [
        'table' => 'relaiscolis_info',
        'primary' => 'id_relais_colis_info',
        'multilang' => false,
        'fields' => [
            'id_relais_colis_info' => [
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
            'rel' => [
                'type' => ObjectModel::TYPE_STRING,
                'required' => false,
            ],
            'rel_name' => [
                'type' => ObjectModel::TYPE_STRING,
                'required' => false,
            ],
            'rel_adr' => [
                'type' => ObjectModel::TYPE_STRING,
                'required' => false,
            ],
            'rel_cp' => [
                'type' => ObjectModel::TYPE_STRING,
                'required' => false,
            ],
            'rel_vil' => [
                'type' => ObjectModel::TYPE_STRING,
                'required' => false,
            ],
            'pseudo_rvc' => [
                'type' => ObjectModel::TYPE_STRING,
                'required' => false,
            ],
            'frc_max' => [
                'type' => ObjectModel::TYPE_STRING,
                'required' => false,
            ],
            'floc_rel' => [
                'type' => ObjectModel::TYPE_STRING,
                'required' => false,
            ],
            'fcod_pays' => [
                'type' => ObjectModel::TYPE_STRING,
                'required' => false,
            ],
            'type_liv' => [
                'type' => ObjectModel::TYPE_STRING,
                'required' => false,
            ],
            'age_code' => [
                'type' => ObjectModel::TYPE_STRING,
                'required' => false,
            ],
            'age_nom' => [
                'type' => ObjectModel::TYPE_STRING,
                'required' => false,
            ],
            'age_adr' => [
                'type' => ObjectModel::TYPE_STRING,
                'required' => false,
            ],
            'age_vil' => [
                'type' => ObjectModel::TYPE_STRING,
                'required' => false,
            ],
            'age_cp' => [
                'type' => ObjectModel::TYPE_STRING,
                'required' => false,
            ],
            'ouvlun' => [
                'type' => ObjectModel::TYPE_STRING,
                'required' => false,
            ],
            'ouvmar' => [
                'type' => ObjectModel::TYPE_STRING,
                'required' => false,
            ],
            'ouvmer' => [
                'type' => ObjectModel::TYPE_STRING,
                'required' => false,
            ],
            'ouvjeu' => [
                'type' => ObjectModel::TYPE_STRING,
                'required' => false,
            ],
            'ouvven' => [
                'type' => ObjectModel::TYPE_STRING,
                'required' => false,
            ],
            'ouvsam' => [
                'type' => ObjectModel::TYPE_STRING,
                'required' => false,
            ],
            'ouvdim' => [
                'type' => ObjectModel::TYPE_STRING,
                'required' => false,
            ],
            'selected_date' => [
                'type' => ObjectModel::TYPE_DATE,
                'required' => true,
            ],
            'smart' => [
                'type' => ObjectModel::TYPE_BOOL,
                'required' => false,
            ],
            'sending_date' => [
                'type' => ObjectModel::TYPE_DATE,
                'required' => false,
            ],
        ],
    ];

    /**
     * Check if a Relais Colis Info already exists for a Cart and Customer IDs tuple
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
            'SELECT id_relais_colis_info 
            FROM ' . _DB_PREFIX_ . pSQL(self::$definition['table']) . '
            WHERE id_cart = ' . (int) $id_cart . ' 
                AND id_customer =' . (int) $id_customer
        );
    }

    /**
     * Get the last Relais Colis selected by a Customer for a specific Cart.
     * If there is no id_cart or id_customer, it takes them from context.
     *
     * @param int $id_cart
     * @param int $id_customer
     *
     * @return mixed Relais Colis Info's ID or false if not exists
     */
    public static function lastSelectedPoint($id_cart = 0, $id_customer = 0)
    {
        if (!$id_cart) {
            $id_cart = Context::getContext()->context->cart->id;
        }

        if (!$id_customer) {
            $id_customer = Context::getContext()->context->customer->id;
        }

        $date_day = date('Y-m-d');

        return Db::getInstance()->getValue(
            'SELECT id_relais_colis_info 
            FROM ' . _DB_PREFIX_ . pSQL(self::$definition['table']) . ' 
            WHERE id_cart <> ' . (int) $id_cart . ' 
                AND id_customer =' . (int) $id_customer . ' 
                AND selected_date >="' . pSQL($date_day) . ' 00:00:00" 
            ORDER BY id_cart DESC'
        );
    }

    /**
     * Get the total product quantity
     *
     * @param int id_order
     *
     * @return mixed Total product quantity or false if not exists
     */
    public static function getTotalProductsForOrderId($id_order)
    {
        if (!$id_order) {
            return false;
        }

        return Db::getInstance()->getValue(
            'SELECT SUM(product_quantity) 
            FROM ' . _DB_PREFIX_ . 'order_detail 
            WHERE id_order = ' . (int) $id_order
        );
    }

    /**
     * Format Opening Time for front rendering
     * #37453
     *
     * @param string openingTime : 'XX:XX-XX:XX@XX:XX-XX:XX' or '-@-'
     *
     * @return string Fermé or 'XX:XX-XX:XX à XX:XX-XX:XX'
     */
    public static function formatOpeningTime(string $openingTime)
    {
        $times = explode('@', $openingTime);

        foreach ($times as $i => $time) {
            if ($time == '-') {
                unset($times[$i]);
            }
        }

        $formatTime = implode(' à ', $times);

        return $formatTime == '' ? 'Fermé' : $formatTime;
    }
}
