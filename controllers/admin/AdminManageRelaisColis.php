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
require_once _PS_MODULE_DIR_ . 'relaiscolis/classes/RelaisColisOrder.php';
require_once _PS_MODULE_DIR_ . 'relaiscolis/classes/RelaisColisApi.php';
require_once _PS_MODULE_DIR_ . 'relaiscolis/classes/RelaisColisInfo.php';

if (!defined('_PS_VERSION_')) { exit; }

class AdminManageRelaisColisController extends ModuleAdminController
{
    public function __construct()
    {
        $this->limited_countries = [
            'france' => [
                'iso2' => 'FR',
                'iso3' => 'FRA',
                'name' => $this->l('France'), ],
            'belgique' => [
                'iso2' => 'BE',
                'iso3' => 'BEL',
                'name' => $this->l('Belgium'), ],
            'monaco' => [
                'iso2' => 'MC',
                'iso3' => 'MCO',
                'name' => $this->l('Monaco'), ],
        ];

        if (Tools::isSubmit('export_csv')) {
            $this->exportCSVC2c([
                Tools::getValue('relay_c2c_id'), ]);
        }

        $this->table = 'relaiscolis_order';
        $this->identifier = 'id_relais_colis_order';
        $this->className = 'RelaisColisOrder';
        $this->requiredDatabase = true;
        $this->bootstrap = true;
        $this->list_no_link = true;
        $this->deleted = false;
        $this->lang = false;
        $this->_orderBy = 'id_relais_colis_order';
        $this->_orderWay = 'DESC';
        $this->context = Context::getContext();

        $this->_select = 'b.*, osl.`name` AS `osname`, rop.`number` as package_number';

        $statuses = OrderState::getOrderStates((int) $this->context->language->id);
        foreach ($statuses as $status) {
            $this->statuses_array[$status['id_order_state']] = $status['name'];
        }
        $this->_join = '
            INNER JOIN `' . _DB_PREFIX_ . 'customer` b ON (a.`id_customer` = b.`id_customer`)
            LEFT JOIN (
            	SELECT COUNT(DISTINCT package_number) as number, rop.*
                FROM `' . _DB_PREFIX_ . 'relaiscolis_order_product` rop
                GROUP BY rop.`id_relais_colis_order`
            ) rop ON a.`id_relais_colis_order` = rop.`id_relais_colis_order`
          LEFT JOIN `' . _DB_PREFIX_ . 'orders` od ON (od.`id_order` = a.`id_order`)
          LEFT JOIN `' . _DB_PREFIX_ . 'order_state` os ON (os.`id_order_state` = od.`current_state`)
          LEFT JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = ' . (int) $this->context->language->id . ')';

        parent::__construct();
        if (RelaisColisApi::isFeatureActivated(RelaisColisApi::TYPE_C2C)) {
            $this->bulk_actions['exportCsv'] = [
                'text' => $this->l('Export CSV'),
                'confirm' => $this->l('Send the request for the selected items ?'),
            ];
        } else {
            $this->bulk_actions = [
                'askprinting' => [
                    'text' => $this->l('Label request'),
                    'confirm' => $this->l('Send the request for the selected items ?'),
                ],
                'printing' => [
                    'text' => $this->l('Print PDF labels'),
                    'confirm' => $this->l('Send the request for the selected items ?'),
                ],
                'letter' => [
                    'text' => $this->l('Print shipping letter'),
                    'confirm' => $this->l('Send the request for the selected items ?'),
                ],
            ];
        }
        $this->fields_list = [
            'id_order' => [
                'title' => $this->l('Id Order'),
                'callback' => 'orderLink',
                'align' => 'center',
            ],
            'osname' => [
                'title' => $this->l('Status'),
                'type' => 'select',
                'color' => 'color',
                'list' => $this->statuses_array,
                'filter_key' => 'os!id_order_state',
                'filter_type' => 'int',
                'order_key' => 'osname',
            ],
            'firstname' => [
                'title' => $this->l('firstname'),
            ],
            'lastname' => [
                'title' => $this->l('lastname'),
            ],
            'email' => [
                'title' => $this->l('email'),
            ],
            'order_weight' => [
                'title' => $this->l('Weight (grams)'),
            ],
            'package_number' => [
                'title' => $this->l('Package Number'),
            ],
        ];

        // If CtoC option is active, we only display if order as been CSV exported or not
        if (RelaisColisApi::isFeatureActivated(RelaisColisApi::TYPE_C2C)) {
            $this->fields_list['is_exported'] = [
                'title' => $this->l('Is exported'),
                'align' => 'center',
                'active' => 'is_exported',
                'type' => 'bool',
                'class' => 'fixed-width-sm',
                'orderby' => false,
            ];
        } else {
            $this->fields_list['is_send'] = [
                'title' => $this->l('Is send'),
                'align' => 'center',
                'active' => 'is_send',
                'type' => 'bool',
                'class' => 'fixed-width-sm',
                'orderby' => false,
            ];
        }
        $this->fields_list['letter_exported'] = [
            'title' => $this->l('letter edited'),
            'align' => 'center',
            'active' => 'letter_exported',
            'type' => 'bool',
            'class' => 'fixed-width-sm',
            'orderby' => false,
        ];
        $this->fields_list['letter_date'] = [
            'title' => $this->l('letter edited date'),
            'align' => 'center',
            'width' => 150,
            'type' => 'date',
            'align' => 'right',
            'filter_key' => 'a!letter_date',
        ];
    }

    public function initContent()
    {
        if (Tools::getValue('message_letter')) {
            $this->errors[] = Tools::displayError($this->l('Order(s) is(are) not in valid state to generate letter :') . Tools::getValue('message_letter'));
        }
        if (Tools::getValue('list_pdf')) {
            $list_pdf = explode('-', Tools::getValue('list_pdf'));
            foreach ($list_pdf as $key => $pdf_number) {
                $all_pdf = RelaisColisOrderPdf::getAllPdfFromNumber($pdf_number);

                if (!empty($all_pdf)) {
                    unset($list_pdf[$key]);
                    foreach ($all_pdf as $row) {
                        $list_pdf[] = $row['pdf_number'];
                    }
                }
            }

            $print_pdf_url = Configuration::get('RC_REST_URL') . 'etiquette/generate';
            $url_back = $this->context->link->getAdminLink('AdminManageRelaisColis');
            $smarty = $this->context->smarty;
            $smarty->assign('activationKey', Configuration::get('RC_ACTIVATION_KEY'));
            $smarty->assign('list_pdf', $list_pdf);
            $smarty->assign('print_pdf_url', $print_pdf_url);
            $smarty->assign('url_back', $url_back);
            $content = $smarty->fetch(_PS_MODULE_DIR_ . 'relaiscolis/views/templates/admin/redirect.tpl');
            $this->context->smarty->assign([
                'content' => $this->content . $content, ]);
        } elseif (Tools::getValue('list_letter')) {
            $all_letter = explode('-', Tools::getValue('list_letter'));
            $list_letter = [];
            foreach ($all_letter as $key => $letter_number) {
                $list_letter[] = $letter_number;
            }
            $print_letter_url = Configuration::get('RC_REST_URL') . 'transport/generate';
            $url_back = $this->context->link->getAdminLink('AdminManageRelaisColis');
            $smarty = $this->context->smarty;
            $smarty->assign('activationKey', Configuration::get('RC_ACTIVATION_KEY'));
            $smarty->assign('list_letter', $list_letter);
            $smarty->assign('print_letter_url', $print_letter_url);
            if (Tools::getValue('message_letter')) {
                $url_back .= '&message_letter=' . Tools::getValue('message_letter');
            }
            $smarty->assign('url_back', $url_back);
            $content = $smarty->fetch(_PS_MODULE_DIR_ . 'relaiscolis/views/templates/admin/redirect_letter.tpl');
            $this->context->smarty->assign([
                'content' => $this->content . $content, ]);
        } elseif (Tools::getValue('list_c2c')) {
            if (RelaisColisApi::isFeatureActivated(RelaisColisApi::TYPE_C2C)) {
                return $this->renderView('c2c', Tools::getValue('list_c2c'));
            }
        } else {
            parent::initContent();
        }
    }

    public function renderView($template = null, $data = null)
    {
        switch ($template) {
            default:
                break;
        }
    }

    public function postProcess()
    {
        if (Tools::isSubmit('is_send' . $this->table)) {
            if ($this->tabAccess['edit'] !== '1') {
                $this->errors[] = Tools::displayError('You do not have permission to edit anything here.');
            } else {
                if ((int) Tools::getValue('id_relais_colis_order')) {
                    $relais_colis_oder = new RelaisColisOrder((int) Tools::getValue('id_relais_colis_order'));

                    if ($relais_colis_oder->id_order && !$relais_colis_oder->pdf_number) {
                        $order = new Order((int) $relais_colis_oder->id_order);

                        RelaisColisApi::processSending((int) $relais_colis_oder->id_order);
                        $this->informations[] = $this->l('Task Done.');
                    }
                }
            }
        }
        // Bulk actions
        if (Tools::isSubmit('submitBulkaskprinting' . $this->table)) {
            $array_id = Tools::getValue($this->table . 'Box');
            foreach ($array_id as $element) {
                $relais_colis_oder = new RelaisColisOrder((int) $element);
                if ($relais_colis_oder->id_order) {
                    $order = new Order((int) $relais_colis_oder->id_order);

                    RelaisColisApi::processSending((int) $relais_colis_oder->id_order);
                }
            }
        }
        // Bulk actions
        if (Tools::isSubmit('submitBulkprinting' . $this->table)) {
            $array_id = Tools::getValue($this->table . 'Box');
            $list_pdf = '';
            $init = true;
            foreach ($array_id as $element) {
                $pdf_id = RelaisColisOrder::getPdfNumberByIdRelais((int) $element);
                if ($pdf_id) {
                    if ($init) {
                        $list_pdf = $pdf_id;
                        $init = false;
                    } else {
                        $list_pdf .= '-' . $pdf_id;
                    }
                }
            }
            if ($list_pdf) {
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminManageRelaisColis', 'true') . '&list_pdf=' . $list_pdf);
            }
        }

        // Bulk actions shipping letter
        if (Tools::isSubmit('submitBulkletter' . $this->table)) {
            $array_id = Tools::getValue($this->table . 'Box');
            $list_letter = '';
            $order_invalid = '';
            $init = true;
            $init_invalid = true;
            if ($array_id) {
                foreach ($array_id as $element) {
                    $relais_order = new RelaisColisOrder((int) $element);
                    $relais_order->letter_exported = 1;
                    $relais_order->letter_date = date('Y-m-d');
                    if ($relais_order->id_order) {
                        $order = new Order((int) $relais_order->id_order);
                        $order_state = new OrderState((int) $order->current_state);
                        if ($order_state->logable || $order_state->shipped || $order_state->paid) {
                            $relais_order->save();
                            if ($init) {
                                $list_letter = $order->reference;
                                $init = false;
                            } else {
                                $list_letter .= '-' . $order->reference;
                            }
                        } else {
                            if ($init_invalid) {
                                $order_invalid = $order->id;
                                $init_invalid = false;
                            } else {
                                $order_invalid .= ' - ' . $order->id;
                            }
                        }
                    }
                }
            }
            if ($order_invalid) {
                $this->errors[] = Tools::displayError($this->l('Order(s) is(are) not in valid state to generate letter :') . $order_invalid);
            }
            if ($list_letter) {
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminManageRelaisColis', 'true') . '&list_letter=' . $list_letter . '&message_letter=' . $order_invalid);
            } else {
                $this->errors[] = Tools::displayError($this->l('You do not have any command in valid state to generate letter.'));
            }
        }
        if (Tools::isSubmit('submitBulkexportCsv' . $this->table)) {
            $data = [];
            $array_id = Tools::getValue($this->table . 'Box');
            foreach ($array_id as $element) {
                $relais_colis_oder = new RelaisColisOrder((int) $element);
                $relais_colis_info = new RelaisColisInfo($relais_colis_oder->id_relais_colis_info);
                if ($relais_colis_info->id_relais_colis_info != 0) {
                    $data[] = $relais_colis_info->id_relais_colis_info;

                    /* SAVE is_exported */
                    $relais_colis_oder->is_exported = true;
                    $relais_colis_oder->save();
                }
            }

            $this->exportCSVC2c($data);
        }

        return parent::postProcess();
    }

    public function renderList()
    {
        return parent::renderList();
    }

    public function orderLink($id_order)
    {
        $smarty = $this->context->smarty;
        $link = new Link();
        $smarty->assign('id_order', $id_order);
        $smarty->assign('link', $link);

        return $smarty->fetch(_PS_MODULE_DIR_ . 'relaiscolis/views/templates/admin/admin_link.tpl');
    }

    public function exportCSVC2c($relais_c2c)
    {
        // Contenu du fichier
        $content = [];

        // Entete de tableau
        $content[] = $this->getListHeaderCSV();

        foreach ($relais_c2c as $id_relais) {
            $relais_colis_info = new RelaisColisInfo($id_relais);
            if (empty($relais_colis_info->id)) {
                continue;
            }

            $order = new Order(RelaisColisOrder::getRelaisColisOrderIdByIdRelaisColisInfo($relais_colis_info->id_relais_colis_info));
            $customer = new Customer($relais_colis_info->id_customer);
            $gender = new Gender($customer->id_gender);
            $shipping = $order->getShipping();
            if (count($shipping) <= 1) {
                list($shipping) = $order->getShipping();
            }

            $shipping_address = new Address($order->id_address_delivery);

            $address = $shipping_address->address2;
            $address_split = preg_split('/\d+\K/', $address);
            $street_number = $address_split[0];
            if (is_numeric($street_number)) {
                $street_name = str_replace($street_number, '', $address);
            } else {
                $street_name = $street_number;
                $street_number = null;
            }

            if ($relais_colis_info->sending_date != 0) {
                $date = new DateTime($relais_colis_info->sending_date);
            } else {
                $date = new DateTime();
            }

            $csv_phone = null;

            if ($shipping_address->phone_mobile) {
                $csv_phone = trim($shipping_address->phone_mobile);
            }
            if (!$csv_phone && $shipping_address->phone) {
                $csv_phone = trim($shipping_address->phone);
            }

            if (!$csv_phone) {
                $csv_phone = '0000000000';
            }

            // On insère les données
            $data = [
                RelaisColisInfo::getTotalProductsForOrderId($order->id),
                (float) $shipping['weight'],
                'OUI',
                'OUI',
                $gender->name[1],
                (string) utf8_decode($customer->lastname),
                (string) utf8_decode($customer->firstname),
                (string) $street_number,
                (string) utf8_decode($street_name),
                (string) $shipping_address->postcode,
                (string) utf8_decode($shipping_address->city),
                $relais_colis_info->fcod_pays,
                $customer->email,
                $csv_phone,
                $relais_colis_info->rel,
                utf8_decode($relais_colis_info->rel_name),
            ];
            array_push($content, $data);
        }

        $date = new DateTime();
        $this->convertToCSV([], $content, 'Export Relais Colis du ' . $date->format('d-m-Y') . '.csv', ';');
    }

    /**
     * Retourne l'entete de tableau
     *
     * @return type
     */
    public function getListHeaderCSV()
    {
        return [
            (string) utf8_decode($this->l('Quantity')),
            (string) utf8_decode($this->l('Weight')),
            (string) utf8_decode($this->l('Conformal dimensions')),
            (string) utf8_decode($this->l('Conforming merchandise')),
            (string) utf8_decode($this->l('Recipient\'s civility')),
            (string) utf8_decode($this->l('Recipient Name')),
            (string) utf8_decode($this->l('First name recipient')),
            (string) utf8_decode($this->l('Destination street number')),
            (string) utf8_decode($this->l('Recipient Street')),
            (string) utf8_decode($this->l('Recipient Postal Code')),
            (string) utf8_decode($this->l('Ricipient Town')),
            (string) utf8_decode($this->l('Recipient Country')),
            (string) utf8_decode($this->l('Recipient email')),
            (string) utf8_decode($this->l('Recipient mobile phone')),
            (string) utf8_decode($this->l('Destination delivery point ID')),
            (string) utf8_decode($this->l('Destination delivery point Name')),
        ];
    }

    /**
     * Génère le CSV
     *
     * @param array $header
     * @param array $content
     * @param string $output_file_name
     * @param string $delimiter
     */
    public function convertToCSV(array $header, array $content, $output_file_name, $delimiter)
    {
        // open raw memory as file
        $temp_memory = fopen('php://memory', 'w');
        // Headers
        foreach ($header as $header) {
            fputcsv($temp_memory, $header, $delimiter, '"');
        }

        // Content
        foreach ($content as $line) {
            fputcsv($temp_memory, $line, $delimiter);
        }

        /* rewrind the "file" with the csv lines * */
        fseek($temp_memory, 0);
        /* modify header to be downloadable csv file * */
        header('Content-Disposition: attachement; filename="' . $output_file_name . '";');
        header('Content-type: text/csv');
        header('Cache-Control: no-store, no-cache');

        /* Send file to browser for download */
        fpassthru($temp_memory);
        fclose($temp_memory);
        exit;
    }
}
