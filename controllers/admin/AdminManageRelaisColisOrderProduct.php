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
require_once _PS_MODULE_DIR_ . 'relaiscolis/classes/RelaisColisReturn.php';
require_once _PS_MODULE_DIR_ . 'relaiscolis/classes/RelaisColisOrder.php';
require_once _PS_MODULE_DIR_ . 'relaiscolis/classes/RelaisColisOrderProduct.php';
require_once _PS_MODULE_DIR_ . 'relaiscolis/classes/RelaisColisApi.php';

if (!defined('_PS_VERSION_')) { exit; }

class AdminManageRelaisColisOrderProductController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->context = Context::getContext();
        $this->identifier = 'id_relais_colis_order_product';
        $this->table = 'relaiscolis_order_product';
        $this->className = 'RelaisColisOrderProduct';
        $this->colorOnBackground = true;
        $this->_select = 'rco.id_order, pl.name as product_name, package_number, weight';
        $this->_join = 'LEFT JOIN ' . _DB_PREFIX_ . 'product_lang pl ON (pl.`id_product` = a.`id_product` AND pl.id_lang = ' . pSQL((int) Configuration::get('PS_LANG_DEFAULT')) . ')
        LEFT JOIN ' . _DB_PREFIX_ . 'relaiscolis_order rco ON (rco.`id_relais_colis_order` = a.`id_relais_colis_order`)';

        parent::__construct();
        if (Tools::getIsset('id_rc_order')) {
            $this->_where = 'a.id_relais_colis_order =' . pSQL(Tools::getValue('id_rc_order'));
        }

        $this->fields_list = [
            'id_order' => [
                'title' => $this->l('Id order'),
                'callback' => 'orderLink',
                'align' => 'center',
            ],
            'product_name' => [
                'title' => $this->l('Product name'),
            ],
            'package_number' => [
                'title' => $this->l('Package numerous'),
                'align' => 'center',
            ],
            'weight' => [
                'title' => $this->l('Weight'),
                'align' => 'center',
            ],
        ];
    }

    public function orderLink($id_order)
    {
        $smarty = $this->context->smarty;
        $link = new Link();
        $smarty->assign('id_order', $id_order);
        $smarty->assign('link', $link);

        return $smarty->fetch(_PS_MODULE_DIR_ . 'relaiscolis/views/templates/admin/admin_link.tpl');
    }

    public function ajaxProcessAddPackage()
    {
        $rc_order_product = new RelaisColisOrderProduct();
        $rc_order_product->id_product = Tools::getValue('id_product');
        $rc_order_product->weight = Tools::getValue('weight');
        $rc_order_product->package_number = Tools::getValue('package_number');
        $rc_order_product->id_relais_colis_order = Tools::getValue('id_relais_colis_order');
        $result = $rc_order_product->add();

        if ($result == true) {
            echo Tools::jsonEncode(['result' => 'success', 'id' => $rc_order_product->id, 'http' => 200]);
        } else {
            echo Tools::jsonEncode(['result' => 'failed', 'http' => 500]);
        }
    }

    public function ajaxProcessGetDetailWeightPackage()
    {
        $order = new Order((int) Tools::getValue('id_order'));
        $relais_colis_op_details = RelaisColisOrderProduct::getOrderWeightDetail((int) Tools::getValue('id_relais_colis_order'), $order);
        echo Tools::jsonEncode($relais_colis_op_details);
    }

    public function ajaxProcessDeletePackage()
    {
        $rc_order_product = new RelaisColisOrderProduct(Tools::getValue('id_relais_colis_order_product'));
        if ($rc_order_product->id) {
            if (!$rc_order_product->delete()) {
                echo json_encode(['result' => 'failed', 'message' => $this->module->l('An error occured while deleting package')]);
            }
        }

        echo json_encode(['result' => 'success']);
    }

    public function initToolbar()
    {
        // If display list, we don't want the "add" button
        if (!$this->display || $this->display == 'list') {
            return;
        } elseif ($this->display != 'options') {
            $this->toolbar_btn['save-and-stay'] = [
                'short' => 'SaveAndStay',
                'href' => '#',
                'desc' => $this->l('Save and stay'),
                'force_desc' => true,
            ];
        }

        parent::initToolbar();
    }

    public function postProcess()
    {
        $this->context = Context::getContext();
        $send_error = false;
        if (Tools::isSubmit('deleteorder_return_detail')) {
            if ($this->tabAccess['delete'] === '1') {
                if (($id_order_detail = (int) Tools::getValue('id_order_detail')) && Validate::isUnsignedId($id_order_detail)) {
                    if (($id_order_return = (int) Tools::getValue('id_order_return')) && Validate::isUnsignedId($id_order_return)) {
                        $orderReturn = new OrderReturn($id_order_return);
                        if (!Validate::isLoadedObject($orderReturn)) {
                            exit(Tools::displayError());
                        }
                        if ((int) $orderReturn->countProduct() > 1) {
                            if (OrderReturn::deleteOrderReturnDetail($id_order_return, $id_order_detail, (int) Tools::getValue('id_customization', 0))) {
                                Tools::redirectAdmin(self::$currentIndex . '&conf=4token=' . $this->token);
                            } else {
                                $this->errors[] = Tools::displayError($this->l('An error occurred while deleting the details of your order return.'));
                            }
                        } else {
                            $this->errors[] = Tools::displayError($this->l('You need at least one product.'));
                        }
                    } else {
                        $this->errors[] = Tools::displayError($this->l('The order return is invalid.'));
                    }
                } else {
                    $this->errors[] = Tools::displayError($this->l('The order return content is invalid.'));
                }
            } else {
                $this->errors[] = Tools::displayError($this->l('You do not have permission to delete this.'));
            }
        } elseif (Tools::isSubmit('submitAddorder_return') || Tools::isSubmit('submitAddorder_returnAndStay')) {
            if ($this->tabAccess['edit'] === '1') {
                if (($id_order_return = (int) Tools::getValue('id_order_return')) && Validate::isUnsignedId($id_order_return)) {
                    $orderReturn = new OrderReturn($id_order_return);
                    $order = new Order($orderReturn->id_order);
                    $customer = new Customer($orderReturn->id_customer);
                    $orderReturn->state = (int) Tools::getValue('state');
                    if ($orderReturn->save()) {
                        $orderReturnState = new OrderReturnState($orderReturn->state);
                        $vars = [
                            '{lastname}' => $customer->lastname,
                            '{firstname}' => $customer->firstname,
                            '{id_order_return}' => $id_order_return,
                            '{state_order_return}' => (isset($orderReturnState->name[(int) $order->id_lang]) ? $orderReturnState->name[(int) $order->id_lang] : $orderReturnState->name[(int) Configuration::get('PS_LANG_DEFAULT')]),
                        ];

                        Mail::Send(
                            (int) $order->id_lang,
                            'order_return_state',
                            Mail::l('Your order return status has changed', $order->id_lang),
                            $vars,
                            $customer->email,
                            $customer->firstname . ' ' . $customer->lastname,
                            null,
                            null,
                            null,
                            null,
                            _PS_MAIL_DIR_,
                            true,
                            (int) $order->id_shop
                        );

                        if (Tools::isSubmit('submitSendingLabel')) {
                            $order_return = new OrderReturn((int) $id_order_return);
                            $order = new Order((int) $order_return->id_order);
                            if (RelaisColisOrder::getRelaisColisOrderId((int) $order->id)) {
                                if (!RelaisColisApi::processSendingReturn($id_order_return)) {
                                    $send_error = true;
                                } else {
                                    Tools::redirectAdmin(self::$currentIndex . '&conf=4&token=' . $this->token . '&updateorder_return&id_order_return=' . (int) $id_order_return);
                                }
                            } else {
                                $this->errors[] = Tools::displayError('Cette commande n\'est pas une commande relais colis.');
                                $send_error = true;
                            }
                        }
                        if (Tools::isSubmit('submitAddorder_returnAndStay')) {
                            if (!$send_error) {
                                Tools::redirectAdmin(self::$currentIndex . '&conf=4&token=' . $this->token . '&updateorder_return&id_order_return=' . (int) $id_order_return);
                            }
                        } else {
                            if (!$send_error) {
                                Tools::redirectAdmin(self::$currentIndex . '&conf=4&token=' . $this->token);
                            }
                        }
                    }
                } else {
                    $this->errors[] = Tools::displayError('No order return ID has been specified.');
                }
            } else {
                $this->errors[] = Tools::displayError('You do not have permission to edit this.');
            }
        }
        parent::postProcess();
    }

    /**
     * Retourne le prix des paquets
     */
    public function ajaxProcessGetPackagesPrice()
    {
        try {
            $weights = Tools::getValue('weight', []);

            if (!is_array($weights)) {
                $weights = [$weights];
            }

            foreach ($weights as $weight) {
                if (!is_numeric($weight)) {
                    throw new Exception($this->l('Format of Weight is invalid. (required int or [int])'));
                }
                if (!$weight || $weight > RelaisColisApi::MAX_WEIGHT_RC) {
                    throw new Exception($this->l('Weight is required to make the reservation. It must be higher than 0 and lower than 20 000g.'));
                }
            }

            $prices = RelaisColisApi::getTokenPrice($weights);
            if (!$prices) {
                throw new Exception($this->l('An error occured.'));
            }

            echo Tools::jsonEncode(['prices' => $prices]);
        } catch (Exception $e) {
            echo Tools::jsonEncode(['error' => $e->getMessage()]);
        }
        exit;
    }
}