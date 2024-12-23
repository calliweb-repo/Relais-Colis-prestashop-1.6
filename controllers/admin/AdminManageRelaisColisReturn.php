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
require_once _PS_MODULE_DIR_ . 'relaiscolis/classes/RelaisColisApi.php';

if (!defined('_PS_VERSION_')) { exit; }

class AdminManageRelaisColisReturnController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->context = Context::getContext();
        $this->table = 'order_return';
        $this->className = 'OrderReturn';
        $this->colorOnBackground = true;
        $this->_select = 'ors.color, orsl.`name`, o.`id_shop`';
        $this->_join = 'LEFT JOIN ' . _DB_PREFIX_ . 'order_return_state ors ON (ors.`id_order_return_state` = a.`state`)';
        $this->_join .= 'LEFT JOIN ' . _DB_PREFIX_ . 'order_return_state_lang orsl ON (orsl.`id_order_return_state` = a.`state` AND orsl.`id_lang` = ' . (int) $this->context->language->id . ')';
        $this->_join .= ' LEFT JOIN ' . _DB_PREFIX_ . 'orders o ON (o.`id_order` = a.`id_order`)';
        $this->_join .= ' INNER JOIN ' . _DB_PREFIX_ . 'relaiscolis_order rco ON (rco.`id_order` = a.`id_order`)';
        $this->fields_list = [
            'id_order_return' => [
                'title' => $this->l('ID'),
                'align' => 'center',
                'width' => 25, ],
            'id_order' => [
                'title' => $this->l('Order ID'),
                'width' => 100,
                'align' => 'center',
                'filter_key' => 'a!id_order', ],
            'name' => [
                'title' => $this->l('Status'),
                'color' => 'color',
                'width' => 'auto',
                'align' => 'left', ],
            'date_add' => [
                'title' => $this->l('Date issued'),
                'width' => 150,
                'type' => 'date',
                'align' => 'right',
                'filter_key' => 'a!date_add', ],
        ];

        parent::__construct();
        $this->_where = Shop::addSqlRestriction(false, 'o');
        $this->_use_found_rows = false;
    }

    public function renderList()
    {
        if (!RelaisColisApi::isFeatureActivated(RelaisColisApi::TYPE_RETURN)) {
            $this->errors[] = Tools::displayError($this->l('You don\'t have acces to that option currently.'));

            return false;
        }

        return parent::renderList();
    }

    public function renderForm()
    {
        if (!RelaisColisApi::isFeatureActivated(RelaisColisApi::TYPE_RETURN)) {
            $this->errors[] = Tools::displayError($this->l('You don\'t have acces to that option currently.'));

            return false;
        }
        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Return Merchandise Authorization (RMA)'),
                'image' => '../img/admin/return.gif',
            ],
            'input' => [
                [
                    'type' => 'hidden',
                    'name' => 'id_order',
                ],
                [
                    'type' => 'hidden',
                    'name' => 'id_customer',
                ],
                [
                    'type' => 'text_customer',
                    'label' => $this->l('Customer'),
                    'name' => '',
                    'size' => '',
                    'required' => false,
                ],
                [
                    'type' => 'text_order',
                    'label' => $this->l('Order'),
                    'name' => '',
                    'size' => '',
                    'required' => false,
                ],
                [
                    'type' => 'free',
                    'label' => $this->l('Customer explanation'),
                    'name' => 'question',
                    'size' => '',
                    'required' => false,
                ],
                [
                    'type' => 'select',
                    'label' => $this->l('Status'),
                    'name' => 'state',
                    'required' => false,
                    'options' => [
                        'query' => OrderReturnState::getOrderReturnStates($this->context->language->id),
                        'id' => 'id_order_return_state',
                        'name' => 'name',
                    ],
                    'desc' => $this->l('Merchandise return (RMA) status.'),
                ],
                [
                    'type' => 'list_products',
                    'label' => $this->l('Products'),
                    'name' => '',
                    'size' => '',
                    'required' => false,
                    'desc' => $this->l('List of products in return package.'),
                ],
                [
                    'type' => 'pdf_order_return',
                    'label' => $this->l('Return slip'),
                    'name' => '',
                    'size' => '',
                    'required' => false,
                    'desc' => $this->l('The link is only available after validation and before the parcel gets delivered.'),
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];

        $order = new Order($this->object->id_order);
        $quantity_displayed = [];
        // Customized products */
        if ($returned_customizations = OrderReturn::getReturnedCustomizedProducts((int) $this->object->id_order)) {
            foreach ($returned_customizations as $returned_customization) {
                $quantity_displayed[(int) $returned_customization['id_order_detail']] = isset($quantity_displayed[(int) $returned_customization['id_order_detail']]) ? $quantity_displayed[(int) $returned_customization['id_order_detail']]
                    + (int) $returned_customization['product_quantity'] : (int) $returned_customization['product_quantity'];
            }
        }

        // Classic products
        $products = OrderReturn::getOrdersReturnProducts($this->object->id, $order);

        // Prepare customer explanation for display
        $this->object->question = '<span class="normal-text">' . nl2br($this->object->question) . '</span>';

        // get pdf Number if exists
        $pdf_number = RelaisColisReturn::getPdfNumber($this->object->id);
        $token_number = RelaisColisReturn::getTokenNumber($this->object->id);
        $image_url = RelaisColisReturn::getImageUrl($this->object->id);
        $bordereau_smart_url = RelaisColisReturn::getBordereauSmartUrl($this->object->id);
        $print_pdf_url = Configuration::get('RC_REST_URL') . 'etiquette/generateReturn';
        $services = RelaisColisReturn::getServices($this->object->id);
        $registred_services = [];
        if ($services) {
            $registred_services = explode('-', $services);
            foreach ($registred_services as $key => $service) {
                $registred_services[$key] = $this->getReturnServiceLabel($service);
            }
        }
        // Get available return service
        $prestations = [];
        if (!$this->isBelgiumReturn($order)) {
            for ($i = 1; $i < 10; ++$i) {
                if (RelaisColisApi::isFeatureActivated('wrp' . $i)) {
                    $prestations[$i] = $this->getReturnServiceLabel($i);
                }
            }
        }
        $this->tpl_form_vars = [
            'customer' => new Customer($this->object->id_customer),
            'url_customer' => 'index.php?tab=AdminCustomers&id_customer=' . (int) $this->object->id_customer . '&viewcustomer&token=' . Tools::getAdminToken('AdminCustomers' . (int) Tab::getIdFromClassName('AdminCustomers') . (int) $this->context->employee->id),
            'text_order' => sprintf($this->l('Order #%1$d from %2$s'), $order->id, Tools::displayDate($order->date_upd)),
            'url_order' => 'index.php?tab=AdminOrders&id_order=' . (int) $order->id . '&vieworder&token=' . Tools::getAdminToken('AdminOrders' . (int) Tab::getIdFromClassName('AdminOrders') . (int) $this->context->employee->id),
            'picture_folder' => _THEME_PROD_PIC_DIR_,
            'returnedCustomizations' => $returned_customizations,
            'customizedDatas' => Product::getAllCustomizedDatas((int) $order->id_cart),
            'products' => $products,
            'quantityDisplayed' => $quantity_displayed,
            'id_order_return' => $this->object->id,
            'state_order_return' => $this->object->state,
            'pdf_number' => $pdf_number,
            'image_url' => $image_url,
            'bordereau_smart_url' => $bordereau_smart_url,
            'sendMailAjax' => $this->context->link->getAdminLink('AdminManageRelaisColisReturn'),
            'token_number' => $token_number,
            'print_pdf_url' => $print_pdf_url,
            'activationKey' => Configuration::get('RC_ACTIVATION_KEY'),
            'prestations' => $prestations,
            'registred_services' => $registred_services,
            'order_ref' => $order->id,
        ];

        return parent::renderForm();
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

    public function isBelgiumReturn($order)
    {
        if ((int) $order->id) {
            $address = new Address((int) $order->id_address_invoice);
            $country_address = new Country($address->id_country);
            if ($country_address->iso_code == 'BE') {
                return true;
            }

            $id_relais_colis_order = RelaisColisOrder::getRelaisColisOrderId((int) $order->id);
            if ($id_relais_colis_order !== false) {
                $relais_colis_order = new RelaisColisOrder((int) $id_relais_colis_order);
                $relais_colis_info = new RelaisColisInfo((int) $relais_colis_order->id_relais_colis_info);
                if (Tools::substr($relais_colis_info->rel, 0, 2) == 'BE') {
                    return true;
                }
            }
        }

        return false;
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
                                $this->errors[] = Tools::displayError('An error occurred while deleting the details of your order return.');
                            }
                        } else {
                            $this->errors[] = Tools::displayError('You need at least one product.');
                        }
                    } else {
                        $this->errors[] = Tools::displayError('The order return is invalid.');
                    }
                } else {
                    $this->errors[] = Tools::displayError('The order return content is invalid.');
                }
            } else {
                $this->errors[] = Tools::displayError('You do not have permission to delete this.');
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
                            $prestations = '';
                            $first = true;
                            for ($i = 1; $i < 10; ++$i) {
                                if (Tools::isSubmit('wrp' . $i)) {
                                    if ($first) {
                                        $first = false;
                                        $prestations = $i;
                                    } else {
                                        $prestations .= '-' . $i;
                                    }
                                }
                            }
                            $order_return = new OrderReturn((int) $id_order_return);
                            $order = new Order((int) $order_return->id_order);
                            if (RelaisColisOrder::getRelaisColisOrderId((int) $order->id)) {
                                if (!RelaisColisApi::processSendingReturn($id_order_return, $prestations)) {
                                    $send_error = true;
                                } else {
                                    Tools::redirectAdmin(self::$currentIndex . '&conf=4&token=' . $this->token . '&updateorder_return&id_order_return=' . (int) $id_order_return);
                                }
                            } else {
                                $this->errors[] = Tools::displayError('This order is not a relais colis order.');
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

    public function getReturnServiceLabel($service_number = 0)
    {
        $label = '';
        if ((int) $service_number) {
            switch ($service_number) {
                default:
                    break;
                case 1:
                    $label = $this->l('SWAP');
                    break;
                case 2:
                    $label = $this->l('SWAP synchro');
                    break;
                case 3:
                    $label = $this->l('Customer signature');
                    break;
                case 4:
                    $label = $this->l('Piece of identity is mandatory');
                    break;
                case 5:
                    $label = $this->l('Content Inventory');
                    break;
                case 6:
                    $label = $this->l('Presence of all imperative elements to accept the return');
                    break;
                case 7:
                    $label = $this->l('Checking the condition of the main equipment');
                    break;
                case 8:
                    $label = $this->l('Express return');
                    break;
                case 9:
                    $label = $this->l('Smart');
                    break;
            }
        }

        return $label;
    }

    public function ajaxProcessSendLabelByMail()
    {
        if ('SendLabelByMail' == Tools::getValue('action')) {
            $data = [
                    '{firstname}' => Tools::getValue('firstname'),
                    '{lastname}' => Tools::getValue('lastname'),
                    '{order_ref}' => Tools::getValue('order_ref'),
                    '{email}' => Tools::getValue('email'),
                    '{label_url}' => Tools::getValue('label_url'),
                    '{smart_label_url}' => Tools::getValue('smart_label_url'),
                ];
            Mail::Send(
                (int) Configuration::get('PS_LANG_DEFAULT'), // defaut language id
                'relaiscolis_send_label_by_mail', // email template file to be use
                $this->l('Return Label Relais Colis - ') . Tools::getValue('order_ref'), // email subject
                $data,
                Tools::getValue('email'),
                Tools::getValue('email'),
                null,
                null,
                null,
                null,
                _PS_MODULE_DIR_ . '/relaiscolis/mails/'
            );
        }
    }

    public function ajaxProcessSendSmartLabelByMail()
    {
        if ('SendSmartLabelByMail' == Tools::getValue('action')) {
            $data = [
                    '{firstname}' => Tools::getValue('firstname'),
                    '{lastname}' => Tools::getValue('lastname'),
                    '{order_ref}' => Tools::getValue('order_ref'),
                    '{email}' => Tools::getValue('email'),
                    '{smart_label_url}' => Tools::getValue('smart_label_url'),
                ];
            Mail::Send(
                (int) Configuration::get('PS_LANG_DEFAULT'), // defaut language id
                'relaiscolis_send_smart_label_by_mail', // email template file to be use
                $this->l('Smart URL Relais Colis - ') . Tools::getValue('order_ref'), // email subject
                $data,
                Tools::getValue('email'),
                Tools::getValue('email'),
                null,
                null,
                null,
                null,
                _PS_MODULE_DIR_ . '/relaiscolis/mails/'
            );
        }
    }
}
