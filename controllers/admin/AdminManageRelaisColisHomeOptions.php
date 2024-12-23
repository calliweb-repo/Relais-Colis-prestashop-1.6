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
require_once _PS_MODULE_DIR_ . 'relaiscolis/classes/RelaisColisProduct.php';
require_once _PS_MODULE_DIR_ . 'relaiscolis/classes/RelaisColisApi.php';
require_once _PS_MODULE_DIR_ . 'relaiscolis/classes/RelaisColisHomeOptions.php';

if (!defined('_PS_VERSION_')) { exit; }

class AdminManageRelaisColisHomeOptionsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'relaiscolis_homeoptions';
        $this->identifier = 'id_relais_colis_home_option';
        $this->className = 'RelaisColisHomeOptions';
        $this->lang = false;
        $this->requiredDatabase = true;
        $this->bootstrap = true;
        $this->deleted = false;
        $this->context = Context::getContext();
        $this->_where = ' AND active = 1';
        parent::__construct();

        $this->bulk_actions = [];
        $this->fields_list = [
            'id_relais_colis_home_option' => [
                'title' => $this->l('Id'),
                'align' => 'center',
            ],
            'label' => [
                'title' => $this->l('option'),
                'align' => 'left',
            ],
            'cost' => [
                'title' => $this->l('Cost'),
                'align' => 'center',
            ],
            'customer_choice' => [
                'title' => $this->l('Customer choice'),
                'align' => 'center',
                'active' => 'customer_choice',
                'type' => 'bool',
                'class' => 'fixed-width-sm',
                'orderby' => false,
            ],
        ];
    }

    public function initToolbar()
    {
        parent::initToolbar();
        $this->toolbar_btn = [];
    }

    public function renderForm()
    {
        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Home option'),
                'icon' => 'icon-male',
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('Name'),
                    'name' => 'label',
                    'readonly' => false,
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Cost'),
                    'name' => 'cost',
                ],
                [
                    'type' => 'switch',
                    'label' => $this->l('Customer can choose option'),
                    'name' => 'customer_choice',
                    'required' => false,
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'choice_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ],
                        [
                            'id' => 'choice_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ],
                    ],
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];

        /* @var Gender $obj */
        if (!$this->loadObject(true)) {
            return;
        }

        return parent::renderForm();
    }
}
