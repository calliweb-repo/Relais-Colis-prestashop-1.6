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

if (!defined('_PS_VERSION_')) { exit; }

class AdminManageRelaisColisHomeController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'product';
        $this->className = 'Product';
        $this->lang = true;
        $this->requiredDatabase = true;
        $this->bootstrap = true;
        $this->list_no_link = true;
        $this->deleted = false;
        $this->addRowAction('edit');
        $this->_select = 'rcp.*';
        $this->_join = ' LEFT JOIN `' . _DB_PREFIX_ . 'relaiscolis_product` rcp ON (a.`id_product` = rcp.`id_product_home`)';
        $this->_group = ' GROUP BY a.id_product';
        $this->context = Context::getContext();

        parent::__construct();
        $this->bulk_actions = [];
        $this->fields_list = [
            'id_product' => [
                'title' => $this->l('Id product'),
                'align' => 'center',
            ],
            'name' => [
                'title' => $this->l('name'),
                'align' => 'left',
                'filter_key' => 'b!name',
            ],
            'retrieve_old_equipment' => [
                'title' => $this->l('Ret'),
                'align' => 'center',
                'active' => 'is_retrieve_old_equipment',
                'type' => 'bool',
                'class' => 'fixed-width-sm',
                'orderby' => false,
                'hint' => $this->l('Retrieve old equipment'),
            ],
            'delivery_on_floor' => [
                'title' => $this->l('Floor'),
                'align' => 'center',
                'active' => 'is_delivery_on_floor',
                'type' => 'bool',
                'class' => 'fixed-width-sm',
                'orderby' => false,
                'hint' => $this->l('Floor Delivery'),
            ],
            'delivery_at_two' => [
                'title' => $this->l('two'),
                'align' => 'center',
                'active' => 'is_delivery_at_two',
                'type' => 'bool',
                'class' => 'fixed-width-sm',
                'orderby' => false,
                'hint' => $this->l('Delivery in twos'),
            ],
            'turn_on_home_appliance' => [
                'title' => $this->l('Impl.'),
                'align' => 'center',
                'active' => 'is_turn_on_home_appliance',
                'type' => 'bool',
                'class' => 'fixed-width-sm',
                'orderby' => false,
                'hint' => $this->l('Implementation large electrical appliances'),
            ],
            'mount_furniture' => [
                'title' => $this->l('Assemb.'),
                'align' => 'center',
                'active' => 'is_mount_furniture',
                'type' => 'bool',
                'class' => 'fixed-width-sm',
                'orderby' => false,
                'hint' => $this->l('Assembly little piece of fourniture'),
            ],
            'non_standard' => [
                'title' => $this->l('stand.'),
                'align' => 'center',
                'active' => 'is_non_standard',
                'type' => 'bool',
                'class' => 'fixed-width-sm',
                'orderby' => false,
                'hint' => $this->l('Out of standard'),
            ],
            'unpacking' => [
                'title' => $this->l('Unwrap'),
                'align' => 'center',
                'active' => 'is_unpacking',
                'type' => 'bool',
                'class' => 'fixed-width-sm',
                'orderby' => false,
                'hint' => $this->l('Unwrapping'),
            ],
            'evacuation_packaging' => [
                'title' => $this->l('Eva.'),
                'align' => 'center',
                'active' => 'is_evacuation_packaging',
                'type' => 'bool',
                'class' => 'fixed-width-sm',
                'orderby' => false,
                'hint' => $this->l('Evacuation'),
            ],
            'recovery' => [
                'title' => $this->l('Rec.'),
                'align' => 'center',
                'active' => 'is_recovery',
                'type' => 'bool',
                'class' => 'fixed-width-sm',
                'orderby' => false,
                'hint' => $this->l('Recovery'),
            ],
            'delivery_desired_room' => [
                'title' => $this->l('Room'),
                'align' => 'center',
                'active' => 'is_delivery_desired_room',
                'type' => 'bool',
                'class' => 'fixed-width-sm',
                'orderby' => false,
                'hint' => $this->l('Room delivery'),
            ],
            'recover_old_bedding' => [
                'title' => $this->l('Old'),
                'align' => 'center',
                'active' => 'is_recover_old_bedding',
                'type' => 'bool',
                'class' => 'fixed-width-sm',
                'orderby' => false,
                'hint' => $this->l('Recover old bedding'),
            ],
            'assembly' => [
                'title' => $this->l('As.'),
                'align' => 'center',
                'active' => 'is_assembly',
                'type' => 'bool',
                'class' => 'fixed-width-sm',
                'orderby' => false,
                'hint' => $this->l('Assemblage'),
            ],
            'top' => [
                'title' => $this->l('Top'),
                'align' => 'center',
                'active' => 'is_top',
                'type' => 'bool',
                'class' => 'fixed-width-sm',
                'orderby' => false,
                'hint' => $this->l('Top 24 hours'),
            ],
            'sensible' => [
                'title' => $this->l('Caut.'),
                'align' => 'center',
                'active' => 'is_sensible',
                'type' => 'bool',
                'class' => 'fixed-width-sm',
                'orderby' => false,
                'hint' => $this->l('Fragile product'),
            ],
            'package_quantity' => [
                'title' => $this->l('Qty'),
                'align' => 'center',
                'type' => 'int',
                'class' => 'fixed-width-sm',
                'orderby' => false,
                'hint' => $this->l('Package quantity'),
            ],
        ];
    }

    public function initToolbar()
    {
        parent::initToolbar();
        $this->toolbar_btn = [];
    }

    public function renderList()
    {
        $this->informations[] = $this->l('Fly over head columns for more informations.');

        return parent::renderList();
    }

    /**
     * Call the right method for creating or updating object
     *
     * @return ObjectModel|false|void
     */
    public function processSave()
    {
        $id_relais_colis_product = RelaisColisProduct::getRelaisColisProductId((int) Tools::getValue('id_relais_colis_product'));
        $relais_colis_product = new RelaisColisProduct((int) $id_relais_colis_product);
        $this->copyFromPost($relais_colis_product, 'relaiscolis_product');
        $this->validateRules('RelaisColisProduct');
        $relais_colis_product->id = $id_relais_colis_product;
        $relais_colis_product->save();

        return null;
    }

    public function validateRules($class_name = false)
    {
        parent::validateRules('RelaisColisProduct');
    }

    /**
     * Load class object using identifier in $_GET (if possible)
     * otherwise return an empty object, or die
     *
     * @param bool $opt Return an empty object if load fail
     *
     * @return ObjectModel|false
     */
    protected function loadObject($opt = false, $original = false)
    {
        if ($original == true) {
            if (!isset($this->className) || empty($this->className)) {
                return true;
            }
            $id_product = (int) Tools::getValue('id_product');
            if ($id_product != 0) {
                $this->identifier = 'id_relais_colis_product';
                $this->className = 'RelaisColisProduct';
                $id_relais_colis_product = RelaisColisProduct::getRelaisColisProductId((int) $id_product);
                $id = (int) $id_relais_colis_product;
            } else {
                $this->identifier = 'id_product';
                $this->className = 'Product';

                return true;
            }

            if ($id && Validate::isUnsignedId($id)) {
                if (!$this->object) {
                    $this->object = new $this->className($id);
                }
                if (Validate::isLoadedObject($this->object)) {
                    return $this->object;
                }
                // throw exception
                $this->errors[] = Tools::displayError('The object cannot be loaded (or found)');

                return false;
            } elseif ($opt) {
                if (!$this->object) {
                    $this->object = new $this->className();
                }

                return $this->object;
            } else {
                $this->errors[] = Tools::displayError('The object cannot be loaded (the identifier is missing or invalide)');

                return false;
            }
        } else {
            $this->identifier = 'id_product';
            $this->className = 'Product';

            return parent::loadObject($opt);
        }
    }

    public function renderForm()
    {
        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Product Configuration Relais Colis'),
                'image' => __PS_BASE_URI__ . 'modules/relaiscolis/views/img/rc.gif',
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('Package Quantity:'),
                    'name' => 'package_quantity',
                    'required' => false,
                    'desc' => $this->l('Number of package that the order will generate for this product'),
                ],
                [
                    'type' => 'hidden',
                    'label' => 'id',
                    'name' => 'id_product_home',
                ],
                [
                    'name' => 'retrieve_old_equipment',
                    'type' => 'switch',
                    'required' => false,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => '<img src="../img/admin/enabled.gif" alt="' . $this->l('Enabled') . '" title="' . $this->l('Enabled') . '" />',
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => '<img src="../img/admin/disabled.gif" alt="' . $this->l('Disabled') . '" title="' . $this->l('Disabled') . '" />',
                        ],
                    ],
                    'title' => $this->l('Ret'),
                    'desc' => $this->l('Retrieve old equipment'),
                ],
                [
                    'name' => 'delivery_on_floor',
                    'type' => 'switch',
                    'required' => false,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => '<img src="../img/admin/enabled.gif" alt="' . $this->l('Enabled') . '" title="' . $this->l('Enabled') . '" />',
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => '<img src="../img/admin/disabled.gif" alt="' . $this->l('Disabled') . '" title="' . $this->l('Disabled') . '" />',
                        ],
                    ],
                    'title' => $this->l('Floor'),
                    'desc' => $this->l('Floor Delivery'),
                ],
                [
                    'name' => 'delivery_at_two',
                    'type' => 'switch',
                    'required' => false,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => '<img src="../img/admin/enabled.gif" alt="' . $this->l('Enabled') . '" title="' . $this->l('Enabled') . '" />',
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => '<img src="../img/admin/disabled.gif" alt="' . $this->l('Disabled') . '" title="' . $this->l('Disabled') . '" />',
                        ],
                    ],
                    'title' => $this->l('two'),
                    'desc' => $this->l('Delivery in twos'),
                ],
                [
                    'name' => 'turn_on_home_appliance',
                    'type' => 'switch',
                    'required' => false,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => '<img src="../img/admin/enabled.gif" alt="' . $this->l('Enabled') . '" title="' . $this->l('Enabled') . '" />',
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => '<img src="../img/admin/disabled.gif" alt="' . $this->l('Disabled') . '" title="' . $this->l('Disabled') . '" />',
                        ],
                    ],
                    'title' => $this->l('Impl.'),
                    'desc' => $this->l('Implementation large electrical appliances'),
                ],
                [
                    'name' => 'mount_furniture',
                    'type' => 'switch',
                    'required' => false,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => '<img src="../img/admin/enabled.gif" alt="' . $this->l('Enabled') . '" title="' . $this->l('Enabled') . '" />',
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => '<img src="../img/admin/disabled.gif" alt="' . $this->l('Disabled') . '" title="' . $this->l('Disabled') . '" />',
                        ],
                    ],
                    'title' => $this->l('Assemb.'),
                    'desc' => $this->l('Assembly little piece of fourniture'),
                ],
                [
                    'name' => 'non_standard',
                    'type' => 'switch',
                    'required' => false,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => '<img src="../img/admin/enabled.gif" alt="' . $this->l('Enabled') . '" title="' . $this->l('Enabled') . '" />',
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => '<img src="../img/admin/disabled.gif" alt="' . $this->l('Disabled') . '" title="' . $this->l('Disabled') . '" />',
                        ],
                    ],
                    'title' => $this->l('stand.'),
                    'desc' => $this->l('Out of standard'),
                ],
                [
                    'name' => 'unpacking',
                    'type' => 'switch',
                    'required' => false,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => '<img src="../img/admin/enabled.gif" alt="' . $this->l('Enabled') . '" title="' . $this->l('Enabled') . '" />',
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => '<img src="../img/admin/disabled.gif" alt="' . $this->l('Disabled') . '" title="' . $this->l('Disabled') . '" />',
                        ],
                    ],
                    'title' => $this->l('Unwrap'),
                    'desc' => $this->l('Unwrapping'),
                ],
                [
                    'name' => 'evacuation_packaging',
                    'type' => 'switch',
                    'required' => false,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => '<img src="../img/admin/enabled.gif" alt="' . $this->l('Enabled') . '" title="' . $this->l('Enabled') . '" />',
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => '<img src="../img/admin/disabled.gif" alt="' . $this->l('Disabled') . '" title="' . $this->l('Disabled') . '" />',
                        ],
                    ],
                    'title' => $this->l('Eva.'),
                    'desc' => $this->l('Evacuation'),
                ],
                [
                    'name' => 'recovery',
                    'type' => 'switch',
                    'required' => false,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => '<img src="../img/admin/enabled.gif" alt="' . $this->l('Enabled') . '" title="' . $this->l('Enabled') . '" />',
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => '<img src="../img/admin/disabled.gif" alt="' . $this->l('Disabled') . '" title="' . $this->l('Disabled') . '" />',
                        ],
                    ],
                    'title' => $this->l('Rec.'),
                    'desc' => $this->l('Recovery'),
                ],
                [
                    'name' => 'delivery_desired_room',
                    'type' => 'switch',
                    'required' => false,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => '<img src="../img/admin/enabled.gif" alt="' . $this->l('Enabled') . '" title="' . $this->l('Enabled') . '" />',
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => '<img src="../img/admin/disabled.gif" alt="' . $this->l('Disabled') . '" title="' . $this->l('Disabled') . '" />',
                        ],
                    ],
                    'title' => $this->l('Room'),
                    'desc' => $this->l('Room delivery'),
                ],
                [
                    'name' => 'recover_old_bedding',
                    'type' => 'switch',
                    'required' => false,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => '<img src="../img/admin/enabled.gif" alt="' . $this->l('Enabled') . '" title="' . $this->l('Enabled') . '" />',
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => '<img src="../img/admin/disabled.gif" alt="' . $this->l('Disabled') . '" title="' . $this->l('Disabled') . '" />',
                        ],
                    ],
                    'title' => $this->l('Old'),
                    'desc' => $this->l('Recover old bedding'),
                ],
                [
                    'name' => 'assembly',
                    'type' => 'switch',
                    'required' => false,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => '<img src="../img/admin/enabled.gif" alt="' . $this->l('Enabled') . '" title="' . $this->l('Enabled') . '" />',
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => '<img src="../img/admin/disabled.gif" alt="' . $this->l('Disabled') . '" title="' . $this->l('Disabled') . '" />',
                        ],
                    ],
                    'title' => $this->l('As.'),
                    'desc' => $this->l('Assemblage'),
                ],
                [
                    'name' => 'top',
                    'type' => 'switch',
                    'required' => false,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => '<img src="../img/admin/enabled.gif" alt="' . $this->l('Enabled') . '" title="' . $this->l('Enabled') . '" />',
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => '<img src="../img/admin/disabled.gif" alt="' . $this->l('Disabled') . '" title="' . $this->l('Disabled') . '" />',
                        ],
                    ],
                    'title' => $this->l('Top'),
                    'desc' => $this->l('Top 24 hours'),
                ],
                [
                    'name' => 'sensible',
                    'type' => 'switch',
                    'required' => false,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => '<img src="../img/admin/enabled.gif" alt="' . $this->l('Enabled') . '" title="' . $this->l('Enabled') . '" />',
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => '<img src="../img/admin/disabled.gif" alt="' . $this->l('Disabled') . '" title="' . $this->l('Disabled') . '" />',
                        ],
                    ],
                    'title' => $this->l('Caut.'),
                    'desc' => $this->l('Fragile product'),
                ],
            ],
            'submit' => [
                'title' => $this->l('   Save   '),
                'class' => 'btn btn-default',
            ],
        ];
        $id_product = (int) Tools::getValue('id_product');
        $this->fields_value['id_product_home'] = $id_product;
        $id_relais_colis_product = RelaisColisProduct::getRelaisColisProductId((int) $id_product);
        $id = (int) $id_relais_colis_product;

        if ((int) $id) {
            $relais_colis_product = new RelaisColisProduct($id);
            $this->fields_value['package_quantity'] = $relais_colis_product->package_quantity;
            $this->fields_value['id_product_home'] = $relais_colis_product->id_product_home;
            $this->fields_value['retrieve_old_equipment'] = $relais_colis_product->retrieve_old_equipment;
            $this->fields_value['delivery_on_floor'] = $relais_colis_product->delivery_on_floor;
            $this->fields_value['delivery_at_two'] = $relais_colis_product->delivery_at_two;
            $this->fields_value['turn_on_home_appliance'] = $relais_colis_product->turn_on_home_appliance;
            $this->fields_value['mount_furniture'] = $relais_colis_product->mount_furniture;
            $this->fields_value['non_standard'] = $relais_colis_product->non_standard;
            $this->fields_value['unpacking'] = $relais_colis_product->unpacking;
            $this->fields_value['evacuation_packaging'] = $relais_colis_product->evacuation_packaging;
            $this->fields_value['recovery'] = $relais_colis_product->recovery;
            $this->fields_value['delivery_desired_room'] = $relais_colis_product->delivery_desired_room;
            $this->fields_value['recover_old_bedding'] = $relais_colis_product->recover_old_bedding;
            $this->fields_value['assembly'] = $relais_colis_product->assembly;
            $this->fields_value['top'] = $relais_colis_product->top;
            $this->fields_value['sensible'] = $relais_colis_product->sensible;
        }

        return parent::renderForm();
    }

    public function postProcess()
    {
        if (!Tools::getValue('submitAddproduct')) {
            parent::postProcess();
        }

        if ($this->tabAccess['edit'] !== '1') {
            $this->_errors[] = Tools::displayError('You do not have permission to edit anything here.');
        } else {
            if ((int) Tools::getValue('id_product')) {
                if ($id_relais_colis_product = RelaisColisProduct::getRelaisColisProductId((int) Tools::getValue('id_product'))) {
                    $relais_colis_product = new RelaisColisProduct($id_relais_colis_product);
                } else {
                    $relais_colis_product = new RelaisColisProduct();
                    $relais_colis_product->id_product_home = (int) Tools::getValue('id_product');
                }
                if (Tools::isSubmit('is_retrieve_old_equipment' . $this->table)) {
                    $relais_colis_product->retrieve_old_equipment = !$relais_colis_product->retrieve_old_equipment;
                }
                if (Tools::isSubmit('is_delivery_on_floor' . $this->table)) {
                    $relais_colis_product->delivery_on_floor = !$relais_colis_product->delivery_on_floor;
                }
                if (Tools::isSubmit('is_delivery_at_two' . $this->table)) {
                    $relais_colis_product->delivery_at_two = !$relais_colis_product->delivery_at_two;
                }
                if (Tools::isSubmit('is_turn_on_home_appliance' . $this->table)) {
                    $relais_colis_product->turn_on_home_appliance = !$relais_colis_product->turn_on_home_appliance;
                }
                if (Tools::isSubmit('is_mount_furniture' . $this->table)) {
                    $relais_colis_product->mount_furniture = !$relais_colis_product->mount_furniture;
                }
                if (Tools::isSubmit('is_non_standard' . $this->table)) {
                    $relais_colis_product->non_standard = !$relais_colis_product->non_standard;
                }
                if (Tools::isSubmit('is_unpacking' . $this->table)) {
                    $relais_colis_product->unpacking = !$relais_colis_product->unpacking;
                }
                if (Tools::isSubmit('is_evacuation_packaging' . $this->table)) {
                    $relais_colis_product->evacuation_packaging = !$relais_colis_product->evacuation_packaging;
                }
                if (Tools::isSubmit('is_recovery' . $this->table)) {
                    $relais_colis_product->recovery = !$relais_colis_product->recovery;
                }
                if (Tools::isSubmit('is_recover_old_bedding' . $this->table)) {
                    $relais_colis_product->recover_old_bedding = !$relais_colis_product->recover_old_bedding;
                }
                if (Tools::isSubmit('is_delivery_desired_room' . $this->table)) {
                    $relais_colis_product->delivery_desired_room = !$relais_colis_product->delivery_desired_room;
                }
                if (Tools::isSubmit('is_delivery_eco' . $this->table)) {
                    $relais_colis_product->delivery_eco = !$relais_colis_product->delivery_eco;
                }
                if (Tools::isSubmit('is_assembly' . $this->table)) {
                    $relais_colis_product->assembly = !$relais_colis_product->assembly;
                }
                if (Tools::isSubmit('is_top' . $this->table)) {
                    $relais_colis_product->top = !$relais_colis_product->top;
                }
                if (Tools::isSubmit('is_sensible' . $this->table)) {
                    $relais_colis_product->sensible = !$relais_colis_product->sensible;
                }
                if (Tools::isSubmit('retrieve_old_equipment')) {
                    $relais_colis_product->retrieve_old_equipment = Tools::getValue('retrieve_old_equipment');
                }
                if (Tools::isSubmit('delivery_on_floor')) {
                    $relais_colis_product->delivery_on_floor = Tools::getValue('delivery_on_floor');
                }
                if (Tools::isSubmit('delivery_at_two')) {
                    $relais_colis_product->delivery_at_two = Tools::getValue('delivery_at_two');
                }
                if (Tools::isSubmit('turn_on_home_appliance')) {
                    $relais_colis_product->turn_on_home_appliance = Tools::getValue('turn_on_home_appliance');
                }
                if (Tools::isSubmit('mount_furniture')) {
                    $relais_colis_product->mount_furniture = Tools::getValue('mount_furniture');
                }
                if (Tools::isSubmit('non_standard')) {
                    $relais_colis_product->non_standard = Tools::getValue('non_standard');
                }
                if (Tools::isSubmit('unpacking')) {
                    $relais_colis_product->unpacking = Tools::getValue('unpacking');
                }
                if (Tools::isSubmit('evacuation_packaging')) {
                    $relais_colis_product->evacuation_packaging = Tools::getValue('evacuation_packaging');
                }
                if (Tools::isSubmit('recovery')) {
                    $relais_colis_product->recovery = Tools::getValue('recovery');
                }
                if (Tools::isSubmit('recover_old_bedding')) {
                    $relais_colis_product->recover_old_bedding = Tools::getValue('recover_old_bedding');
                }
                if (Tools::isSubmit('delivery_desired_room')) {
                    $relais_colis_product->delivery_desired_room = Tools::getValue('delivery_desired_room');
                }
                if (Tools::isSubmit('delivery_eco')) {
                    $relais_colis_product->delivery_eco = Tools::getValue('delivery_eco');
                }
                if (Tools::isSubmit('assembly')) {
                    $relais_colis_product->assembly = Tools::getValue('assembly');
                }
                if (Tools::isSubmit('top')) {
                    $relais_colis_product->top = Tools::getValue('top');
                }
                if (Tools::isSubmit('sensible')) {
                    $relais_colis_product->sensible = Tools::getValue('sensible');
                }
                if (Tools::isSubmit('package_quantity')) {
                    $relais_colis_product->package_quantity = Tools::getValue('package_quantity');
                }
                $relais_colis_product->save();
            }
        }
    }
}
