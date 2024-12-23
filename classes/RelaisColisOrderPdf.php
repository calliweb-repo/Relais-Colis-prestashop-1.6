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

class RelaisColisOrderPdf extends ObjectModel
{
    public $id_relais_colis_order_pdf;
    public $id_relais_colis_order;
    public $package_number;
    public $pdf_number;
    public static $definition = [
        'table' => 'relaiscolis_order_pdf',
        'primary' => 'id_relais_colis_order_pdf',
        'multilang' => false,
        'fields' => [
            'id_relais_colis_order_pdf' => [
                'type' => ObjectModel::TYPE_INT,
            ],
            'id_relais_colis_order' => [
                'type' => ObjectModel::TYPE_INT,
                'required' => true,
            ],
            'package_number' => [
                'type' => ObjectModel::TYPE_INT,
                'required' => true,
            ],
            'pdf_number' => [
                'type' => ObjectModel::TYPE_STRING,
                'required' => true,
            ],
        ],
    ];

    public static function getPdfsNumber($id_relais_colis_order)
    {
        if ($id_relais_colis_order) {
            $sql = '
                SELECT rop.*, ROUND(SUM(ropr.`weight`), 2) as weight, SUBSTR(rop.pdf_number, 1, 14) as pdf_number14
                FROM `' . _DB_PREFIX_ . pSQL(self::$definition['table']) . '` rop
                LEFT JOIN `' . _DB_PREFIX_ . pSQL(RelaisColisOrderProduct::$definition['table']) . '` ropr ON (ropr.`package_number` = rop.`package_number` AND ropr.`id_relais_colis_order` = rop.`id_relais_colis_order`)
                WHERE rop.`id_relais_colis_order` = ' . (int) $id_relais_colis_order . '
                GROUP BY rop.`package_number`
            ';
            $results = Db::getInstance()->executeS($sql);

            return $results;
        }

        return [];
    }

    public static function getAllPdfFromNumber($pdf)
    {
        $sql = '
            SELECT `id_relais_colis_order`
            FROM `' . _DB_PREFIX_ . pSQL(self::$definition['table']) . '`
            WHERE `pdf_number` = "' . pSQL($pdf) . '"
        ';
        $id_relais_colis_order = Db::getInstance()->getValue($sql);

        if ($id_relais_colis_order) {
            $sql = '
                SELECT pdf_number
                FROM `' . _DB_PREFIX_ . pSQL(self::$definition['table']) . '`
                WHERE `id_relais_colis_order` = ' . (int) $id_relais_colis_order . '
            ';

            return Db::getInstance()->executeS($sql);
        }

        return false;
    }
}
