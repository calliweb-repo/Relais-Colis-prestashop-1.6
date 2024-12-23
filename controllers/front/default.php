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
require_once _PS_MODULE_DIR_ . 'relaiscolis/classes/RelaisColisReturn.php';

if (!defined('_PS_VERSION_')) { exit; }

class RelaiscolisDefaultModuleFrontController extends ModuleFrontController
{
    public $auth = true;
    public $ssl = true;

    public $display_column_left = false;
    public $display_column_right = false;

    public function process()
    {
        parent::process();
        $id_customer = (int) $this->context->customer->id;
        $return_list = RelaisColisReturn::getRelaisColisReturnByCustomer((int) $id_customer);
        if (count($return_list)) {
            foreach ($return_list as $key => $row) {
                $order = new Order($row['id_order']);
                $return_list[$key]['reference'] = $order->reference;
                $state = new OrderReturnState((int) $row['state']);
                $return_list[$key]['state_name'] = $state->name[(int) $this->context->language->id];
            }
            $print_pdf_url = Configuration::get('RC_REST_URL') . 'etiquette/generateReturn';
            $this->context->smarty->assign([
                'orders' => $return_list,
                'print_pdf_url' => $print_pdf_url,
            ]);
        }
    }

    /**
     * Assign template vars related to page content
     *
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();
        $this->setTemplate('my_returns.tpl');
    }
}
