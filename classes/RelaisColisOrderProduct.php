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

class RelaisColisOrderProduct extends ObjectModel
{
    public $id_relais_colis_order_product;
    public $id_relais_colis_order;
    public $id_relais_colis_order_pdf;
    public $id_product;
    public $package_number;
    public $weight;
    public static $definition = [
        'table' => 'relaiscolis_order_product',
        'primary' => 'id_relais_colis_order_product',
        'multilang' => false,
        'fields' => [
            'id_relais_colis_order_product' => [
                'type' => ObjectModel::TYPE_INT,
            ],
            'id_relais_colis_order' => [
                'type' => ObjectModel::TYPE_INT,
                'required' => true,
            ],
            'id_product' => [
                'type' => ObjectModel::TYPE_INT,
                'required' => true,
            ],
            'id_relais_colis_order_pdf' => [
                'type' => ObjectModel::TYPE_INT,
                'required' => false,
            ],
            'package_number' => [
                'type' => ObjectModel::TYPE_INT,
                'required' => true,
            ],
            'weight' => [
                'type' => ObjectModel::TYPE_FLOAT,
                'required' => true,
            ],
        ],
    ];

    public static function getPackagesByRcOrderIdDispatchByPackageNumber($rc_order_id)
    {
        if (!empty($rc_order_id) || $rc_order_id == 0) {
            $sql = '
                SELECT rop.*, rp.*
                FROM `' . _DB_PREFIX_ . pSQL(self::$definition['table']) . '` rop
                LEFT JOIN `' . _DB_PREFIX_ . pSQL(RelaisColisProduct::$definition['table']) . '` rp ON (rp.`id_product_home` = rop.`id_product`)
                WHERE rop.`id_relais_colis_order` = ' . (int) $rc_order_id . '
                ORDER BY rop.package_number
            ';

            $packages = Db::getInstance()->executeS($sql);
            $results = [];
            foreach ($packages as $package) {
                if (!isset($results[$package['package_number']]['weight'])) {
                    $results[$package['package_number']]['weight'] = Tools::ps_round($package['weight'], 2);
                } else {
                    $results[$package['package_number']]['weight'] += Tools::ps_round($package['weight'], 2);
                }
                $results[$package['package_number']]['ids'][] = $package['id_relais_colis_order_product'];

                foreach ($package as $option => $value) {
                    if ($value == true && $option != 'weight') {
                        $results[$package['package_number']][$option] = $value;
                    } elseif (!isset($results[$package['package_number']][$option])) {
                        $results[$package['package_number']][$option] = false;
                    }
                }
            }

            return $results;
        }
    }

    public static function getPackagesByRcOrderId($rc_order_id)
    {
        if (!empty($rc_order_id) || $rc_order_id == 0) {
            $sql = '
                SELECT *
                FROM `' . _DB_PREFIX_ . pSQL(self::$definition['table']) . '`
                WHERE `id_relais_colis_order` = ' . (int) $rc_order_id . '
                ORDER BY `package_number` ASC
            ';

            $results = Db::getInstance()->executeS($sql);

            $order_products = [];
            foreach ($results as $row) {
                $product = new Product((int) $row['id_product']);
                $row['product_name'] = $product->name[Configuration::get('PS_LANG_DEFAULT')];

                $order_products[$row['package_number']]['package'][] = $row;
                if (!isset($order_products[$row['package_number']]['count'])) {
                    $order_products[$row['package_number']]['count'] = 2;
                } else {
                    ++$order_products[$row['package_number']]['count'];
                }
            }

            return $order_products;
        }
    }

    public static function getOrderWeightDetail($rc_order_id, Order $order)
    {
        $sql = '
            SELECT rop.id_product, SUM(rop.weight) as product_weight
            FROM `' . _DB_PREFIX_ . pSQL(self::$definition['table']) . '` rop
            WHERE rop.`id_relais_colis_order` = ' . (int) $rc_order_id . '
            GROUP BY rop.id_product
            ORDER BY rop.`package_number` ASC
        ';

        $rc_order_products = Db::getInstance()->executeS($sql);

        $results = [];
        $results['same_total'] = true;
        $total_weight = 0;
        foreach ($order->getProducts() as $product) {
            foreach ($rc_order_products as $rc_order_product) {
                if ($rc_order_product['id_product'] == $product['product_id']) {
                    $results['detail'][(int) $product['product_id']]['total_weight'] = Tools::ps_round($product['product_weight'] * $product['product_quantity'], 2);
                    $results['detail'][(int) $product['product_id']]['saved_weight'] = Tools::ps_round($rc_order_product['product_weight'], 2);
                    $results['detail'][(int) $product['product_id']]['product_name'] = (string) $product['product_name'];
                    $total_weight += $results['detail'][(int) $product['product_id']]['total_weight'];
                    if (Tools::ps_round($product['product_weight'] * $product['product_quantity'], 2) != Tools::ps_round($rc_order_product['product_weight'], 2)) {
                        $results['same_total'] = false;
                    }
                }
            }
        }

        if ($order->getTotalWeight() != $total_weight) {
            $results['same_total'] = false;
            foreach ($order->getProducts() as $product) {
                if (!isset($results['detail'][(int) $product['product_id']])) {
                    $results['detail'][(int) $product['product_id']]['total_weight'] = Tools::ps_round($product['product_weight'] * $product['product_quantity'], 2);
                    $results['detail'][(int) $product['product_id']]['saved_weight'] = 0;
                    $results['detail'][(int) $product['product_id']]['product_name'] = (string) $product['product_name'];
                }
            }
        }

        return $results;
    }
}
