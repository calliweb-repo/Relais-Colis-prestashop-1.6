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
if (!defined('_PS_VERSION_')) {
    exit;
}
require_once _PS_MODULE_DIR_ . 'relaiscolis/classes/RelaisColisInfo.php';
require_once _PS_MODULE_DIR_ . 'relaiscolis/classes/RelaisColisApi.php';
require_once _PS_MODULE_DIR_ . 'relaiscolis/classes/RelaisColisOrder.php';
require_once _PS_MODULE_DIR_ . 'relaiscolis/classes/RelaisColisOrderProduct.php';
require_once _PS_MODULE_DIR_ . 'relaiscolis/classes/RelaisColisOrderPdf.php';
require_once _PS_MODULE_DIR_ . 'relaiscolis/classes/RelaisColisProduct.php';
require_once _PS_MODULE_DIR_ . 'relaiscolis/classes/RelaisColisHomeOptions.php';
require_once _PS_MODULE_DIR_ . 'relaiscolis/classes/RelaisColisInfoHome.php';
require_once _PS_MODULE_DIR_ . 'relaiscolis/classes/RelaisColisResetCarrier.php';

class Relaiscolis extends CarrierModule
{
    const MAX_WEIGHT_BE = 15;
    const MAX_HEIGHT_BE = 170;

    public $id_carrier;
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'relaiscolis';
        $this->tab = 'shipping_logistics';
        $this->version = '1.1.4';
        $this->author = 'Calliweb';
        $this->need_instance = 1;
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

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Relais Colis Delivery Module for Prestashop');
        $this->description = $this->l('The Relais Colis module offer and display, on the e-merchants websites, the map of our 5200 Relais Colis everywhere in France.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall relais colis module ?');
        $this->ps_versions_compliancy = [
            'min' => '1.6',
            'max' => '1.7', ];

        $this->module_key = 'c04b43eb7e4e471a11e56dd5bddc43e6';
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        $this->l('Not enough money in balance');

        if (extension_loaded('curl') == false) {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');

            return false;
        }

        // adding relais colis simple carrier
        $carrier = $this->addCarrier();
        $this->addZones($carrier);
        $this->addGroups($carrier);
        $this->addRanges($carrier);

        // adding relais colis Max carrier
        $carrier = $this->addCarrierMax();
        $this->addZones($carrier);
        $this->addGroups($carrier);
        $this->addRangesMax($carrier);

        // adding home carrier
        $carrier = $this->addCarrierHome();
        $this->addZones($carrier);
        $this->addGroups($carrier);
        $this->addRangesHome($carrier);

        // adding home + carrier
        $carrier = $this->addCarrierHomePlus();
        $this->addZones($carrier);
        $this->addGroups($carrier);
        $this->addRangesHome($carrier);

        $token = tools::strtoupper(Tools::passwdGen(12));

        if (!Configuration::updateValue('RC_REST_URL', 'https://ws-modules.relaiscolis.com/') ||
            !Configuration::updateValue('RC_ACTIVATION_KEY', '') ||
            !Configuration::updateValue('RC_LIVEMAP_API', '') ||
            !Configuration::updateValue('RC_LIVEMAP_PID', '') ||
            !Configuration::updateValue('RC_LIVEMAP_KEY', '') ||
            !Configuration::updateValue('RC_CRON_ACTIVE', false) ||
            !Configuration::updateValue('RC_CRON_TOKEN', $token) ||
            !Configuration::updateValue('RC_HOME_RETRIEVE_TYPE', '') ||
            !Configuration::updateValue('RC_MODULE_VERSION', $this->version) ||
            !Configuration::updateValue('RC_TOKEN_HASH', '') ||
            !Configuration::updateValue('RC_TOKEN_ACTIVE', 0)
        ) {
            return false;
        }
        if (!$this->createOrderStates()) {
            return false;
        }
        include dirname(__FILE__) . '/sql/install.php';
        Configuration::updateGlobalValue('RC_MODULE_VERSION', '1.1.2');

        if (!parent::install()
            || !$this->installBackOffice()
            || !$this->registerHook('header')
            || !$this->registerHook('actionAdminControllerSetMedia')
            || !$this->registerHook('displayCustomerAccount')
            || !$this->registerHook('newOrder')
            || !$this->registerHook('actionCarrierUpdate')
            || !$this->registerHook('displayAdminOrder')
            || !$this->registerHook('displayBeforeCarrier')
            || !$this->registerHook('displayCarrierList')
            || !$this->registerHook('actionProductUpdate')
            || !$this->registerHook('displayAdminProductsExtra')
            || !$this->registerHook('displayOrderDetail')
            || !$this->registerHook('displayBackOfficeHeader')
        ) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        Configuration::deleteByName('RC_REST_URL');
        Configuration::deleteByName('RC_ACTIVATION_KEY');
        Configuration::deleteByName('RC_LIVEMAP_API');
        Configuration::deleteByName('RC_LIVEMAP_PID');
        Configuration::deleteByName('RC_LIVEMAP_KEY');
        Configuration::deleteByName('RC_CRON_ACTIVE');
        Configuration::deleteByName('RC_TOP');
        Configuration::deleteByName('RC_TOP_COST');
        Configuration::deleteByName('RC_TOP_HOUR');
        Configuration::deleteByName('RC_OPTIONS');
        Configuration::deleteByName('RC_HOME_RETRIEVE_TYPE');
        Configuration::deleteByName('RC_TOKEN_HASH');
        Configuration::deleteByName('RC_TOKEN_ACTIVE');
        Configuration::deleteByName('RC_ENS_ID');

        // Delete carrier Relais Colis
        RelaisColisResetCarrier::deleteRelaiscolisCarrier($this);

        // Delete carrier Relais Colis Max
        RelaisColisResetCarrier::deleteRelaiscolismaxCarrier($this);

        // delete carrier Home
        RelaisColisResetCarrier::deleteRelaiscolishomeCarrier($this);

        // delete carrier Home +
        RelaisColisResetCarrier::deleteRelaiscolishomeplusCarrier($this);

        include dirname(__FILE__) . '/sql/uninstall.php';

        // Delete Tab menu
        $this->uninstallModuleTab('AdminManageRelaisColis');
        $this->uninstallModuleTab('AdminManageRelaisColisMenu');
        $this->uninstallModuleTab('AdminManageRelaisColisReturn');
        $this->uninstallModuleTab('AdminManageRelaisColisConfig');
        $this->uninstallModuleTab('AdminManageRelaisColisHome');
        $this->uninstallModuleTab('AdminManageRelaisColisHomeOptions');
        $this->uninstallModuleTab('AdminManageRelaisColisOrderProduct');

        return parent::uninstall();
    }

    public function createOrderStates()
    {
        // state 'livraison en cours'

        if (!(int) Configuration::get('RC_STATE_LEC')) {
            $order_state = new OrderState();
            $order_state->unremovable = true;
            $order_state->name = [];

            foreach (Language::getLanguages() as $language) {
                if (Tools::strtolower($language['iso_code']) == 'fr') {
                    $order_state->name[$language['id_lang']] = 'RC - Livraison en cours';
                } else {
                    $order_state->name[$language['id_lang']] = 'RC - Shipping in progress';
                }
            }

            $order_state->module_name = $this->name;
            $order_state->color = '#eddfdc';
            $order_state->hidden = false;
            $order_state->delivery = false;
            $order_state->logable = false;
            $order_state->invoice = true;
            $order_state->send_email = false;
            $order_state->template = '';

            if ($order_state->save()) {
                copy(
                    _PS_MODULE_DIR_ . $this->name . '/views/img/rc.gif',
                    _PS_IMG_DIR_ . 'os/' . (int) $order_state->id . '.gif'
                );
                Configuration::updateValue('RC_STATE_LEC', (int) $order_state->id);
            } else {
                return false;
            }
        }
        // state 'Déposé en relais'
        if (!(int) Configuration::get('RC_STATE_DER')) {
            $order_state = new OrderState();
            $order_state->unremovable = true;
            $order_state->name = [];

            foreach (Language::getLanguages() as $language) {
                if (Tools::strtolower($language['iso_code']) == 'fr') {
                    $order_state->name[$language['id_lang']] = 'RC - Déposé en relais';
                } else {
                    $order_state->name[$language['id_lang']] = 'RC - Filed relay';
                }
            }

            $order_state->module_name = $this->name;
            $order_state->color = '#ff7f70';
            $order_state->hidden = false;
            $order_state->delivery = false;
            $order_state->logable = false;
            $order_state->invoice = true;
            $order_state->send_email = false;
            $order_state->template = '';

            if ($order_state->save()) {
                copy(
                    _PS_MODULE_DIR_ . $this->name . '/views/img/rc.gif',
                    _PS_IMG_DIR_ . 'os/' . (int) $order_state->id . '.gif'
                );
                Configuration::updateValue('RC_STATE_DER', (int) $order_state->id);
            } else {
                return false;
            }
        }

        // state 'Non livré'
        if (!(int) Configuration::get('RC_STATE_NL')) {
            $order_state = new OrderState();
            $order_state->unremovable = true;
            $order_state->name = [];

            foreach (Language::getLanguages() as $language) {
                if (Tools::strtolower($language['iso_code']) == 'fr') {
                    $order_state->name[$language['id_lang']] = 'RC - Non livré';
                } else {
                    $order_state->name[$language['id_lang']] = 'RC - Undelivered';
                }
            }

            $order_state->module_name = $this->name;
            $order_state->color = '#bf9ce0';
            $order_state->hidden = false;
            $order_state->delivery = false;
            $order_state->logable = false;
            $order_state->invoice = true;
            $order_state->send_email = false;
            $order_state->template = '';

            if ($order_state->save()) {
                copy(
                    _PS_MODULE_DIR_ . $this->name . '/views/img/rc.gif',
                    _PS_IMG_DIR_ . 'os/' . (int) $order_state->id . '.gif'
                );
                Configuration::updateValue('RC_STATE_NL', (int) $order_state->id);
            } else {
                return false;
            }
        }

        // state 'Refus Client'
        if (!(int) Configuration::get('RC_STATE_RC')) {
            $order_state = new OrderState();
            $order_state->unremovable = true;
            $order_state->name = [];

            foreach (Language::getLanguages() as $language) {
                if (Tools::strtolower($language['iso_code']) == 'fr') {
                    $order_state->name[$language['id_lang']] = 'RC - Refus Client';
                } else {
                    $order_state->name[$language['id_lang']] = 'RC - Customer refuse';
                }
            }

            $order_state->module_name = $this->name;
            $order_state->color = '#90d153';
            $order_state->hidden = false;
            $order_state->delivery = false;
            $order_state->logable = false;
            $order_state->invoice = true;
            $order_state->send_email = false;
            $order_state->template = '';

            if ($order_state->save()) {
                copy(
                    _PS_MODULE_DIR_ . $this->name . '/views/img/rc.gif',
                    _PS_IMG_DIR_ . 'os/' . (int) $order_state->id . '.gif'
                );
                Configuration::updateValue('RC_STATE_RC', (int) $order_state->id);
            } else {
                return false;
            }
        }

        // state 'En cours de retour'
        if (!(int) Configuration::get('RC_STATE_ECR')) {
            $order_state = new OrderState();
            $order_state->unremovable = true;
            $order_state->name = [];

            foreach (Language::getLanguages() as $language) {
                if (Tools::strtolower($language['iso_code']) == 'fr') {
                    $order_state->name[$language['id_lang']] = 'RC - En cours de retour';
                } else {
                    $order_state->name[$language['id_lang']] = 'RC - Return in progress';
                }
            }

            $order_state->module_name = $this->name;
            $order_state->color = '#01b0f1';
            $order_state->hidden = false;
            $order_state->delivery = false;
            $order_state->logable = false;
            $order_state->invoice = true;
            $order_state->send_email = false;
            $order_state->template = '';

            if ($order_state->save()) {
                copy(
                    _PS_MODULE_DIR_ . $this->name . '/views/img/rc.gif',
                    _PS_IMG_DIR_ . 'os/' . (int) $order_state->id . '.gif'
                );
                Configuration::updateValue('RC_STATE_ECR', (int) $order_state->id);
            } else {
                return false;
            }
        }

        // state 'Retourné'
        if (!(int) Configuration::get('RC_STATE_RETURNED')) {
            $order_state = new OrderState();
            $order_state->unremovable = true;
            $order_state->name = [];

            foreach (Language::getLanguages() as $language) {
                if (Tools::strtolower($language['iso_code']) == 'fr') {
                    $order_state->name[$language['id_lang']] = 'RC - Retourné';
                } else {
                    $order_state->name[$language['id_lang']] = 'RC - Returned';
                }
            }

            $order_state->module_name = $this->name;
            $order_state->color = '#f8c600';
            $order_state->hidden = false;
            $order_state->delivery = false;
            $order_state->logable = false;
            $order_state->invoice = true;
            $order_state->send_email = false;
            $order_state->template = '';

            if ($order_state->save()) {
                copy(
                    _PS_MODULE_DIR_ . $this->name . '/views/img/rc.gif',
                    _PS_IMG_DIR_ . 'os/' . (int) $order_state->id . '.gif'
                );
                Configuration::updateValue('RC_STATE_RETURNED', (int) $order_state->id);
            } else {
                return false;
            }
        }

        // state 'Reprise en cours'
        if (!(int) Configuration::get('RC_STATE_REPENC')) {
            $order_state = new OrderState();
            $order_state->unremovable = true;
            $order_state->name = [];

            foreach (Language::getLanguages() as $language) {
                if (Tools::strtolower($language['iso_code']) == 'fr') {
                    $order_state->name[$language['id_lang']] = 'RC - Reprise en cours';
                } else {
                    $order_state->name[$language['id_lang']] = 'RC - Recovery in progress';
                }
            }

            $order_state->module_name = $this->name;
            $order_state->color = '#a8c23a';
            $order_state->hidden = false;
            $order_state->delivery = false;
            $order_state->logable = false;
            $order_state->invoice = true;
            $order_state->send_email = false;
            $order_state->template = '';

            if ($order_state->save()) {
                copy(
                    _PS_MODULE_DIR_ . $this->name . '/views/img/rc.gif',
                    _PS_IMG_DIR_ . 'os/' . (int) $order_state->id . '.gif'
                );
                Configuration::updateValue('RC_STATE_REPENC', (int) $order_state->id);
            } else {
                return false;
            }
        }

        // state 'Litige'
        if (!(int) Configuration::get('RC_STATE_LITIGE')) {
            $order_state = new OrderState();
            $order_state->unremovable = true;
            $order_state->name = [];

            foreach (Language::getLanguages() as $language) {
                if (Tools::strtolower($language['iso_code']) == 'fr') {
                    $order_state->name[$language['id_lang']] = 'RC - Litiges';
                } else {
                    $order_state->name[$language['id_lang']] = 'RC - Litigations';
                }
            }

            $order_state->module_name = $this->name;
            $order_state->color = '#fad7af';
            $order_state->hidden = false;
            $order_state->delivery = false;
            $order_state->logable = false;
            $order_state->invoice = true;
            $order_state->send_email = false;
            $order_state->template = '';

            if ($order_state->save()) {
                copy(
                    _PS_MODULE_DIR_ . $this->name . '/views/img/rc.gif',
                    _PS_IMG_DIR_ . 'os/' . (int) $order_state->id . '.gif'
                );
                Configuration::updateValue('RC_STATE_LITIGE', (int) $order_state->id);
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /*
        * If values have been submitted in the form, process.
        */
        if (((bool) Tools::isSubmit('submitRelaiscolisModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);
        $this->context->smarty->assign('relais_version', $this->version);
        $this->context->smarty->assign('user_manual_url', __PS_BASE_URI__ . '/modules/relaiscolis/pdf/Manuel_Utilisateur.pdf');
        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output . $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();
        
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitRelaiscolisModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([
            [
                'form' => $this->getConfigForm(),
            ],
        ]);
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        $form = [
            'legend' => [
                'title' => $this->l('Relais Colis') . ' V' . $this->version,
                'icon' => 'icon-cogs',
            ],
            'submit' => [
                'title' => $this->l('Save my account informations'),
            ],
        ];

        // INFO TAB
        $form['tabs']['about'] = $this->l('About Relais colis Informations');

        $defaut_tpl = $this->local_path . 'views/templates/admin/about.tpl';
        $form['input'][] = [
            'tab' => 'about',
            'type' => 'html',
            'name' => 'about',
            'html_content' => $this->context->smarty->fetch($defaut_tpl),
        ];

        // GENERAL TAB
        $form['tabs']['general'] = $this->l('Your Relais Colis Account');

        $form['input'][] = [
            'tab' => 'general',
            'col' => 3,
            'type' => 'text',
            'required' => true,
            'label' => $this->l('Relais Colis Activation Key'),
            'name' => 'RC_ACTIVATION_KEY',
            'desc' => $this->l('Available in your relais colis account contract'),
        ];
        $form['input'][] = [
            'tab' => 'general',
            'col' => 3,
            'type' => 'text',
            'required' => false,
            'readonly' => true,
            'label' => $this->l('Your Account Options'),
            'name' => 'RC_OPTIONS',
            'desc' => $this->l('List all your account options'),
        ];

        // TOKEN TAB
        if (RelaisColisApi::isFeatureActivated(RelaisColisApi::TYPE_C2C)) {
            $form['tabs']['token'] = $this->l('Tokens');

            $form['input'][] = [
                'tab' => 'token',
                'col' => 3,
                'type' => 'text',
                'label' => $this->l('Relais Colis Hash'),
                'name' => 'RC_TOKEN_HASH',
                'desc' => $this->l('Available in your relais colis account contract'),
            ];

            if (Configuration::get('RC_TOKEN_HASH')) {
                // processTokenBalance
                $result = RelaisColisApi::processTokenBalance();

                if ($result) {
                    $return = ['infos' => $result];
                } else {
                    $return = ['error' => $this->l('An error occured, check your configuration')];
                }
                $this->context->smarty->assign($return);
                $default_tpl = $this->local_path . 'views/templates/admin/token.tpl';
                $form['input'][] = [
                    'tab' => 'token',
                    'type' => 'html',
                    'name' => 'token',
                    'html_content' => $this->context->smarty->fetch($default_tpl),
                ];
            }
        }

        // SYSTEM TAB
        $form['tabs']['system'] = $this->l('Relais Colis advanced parameters');

        $form['input'][] = [
            'tab' => 'system',
            'col' => 3,
            'type' => 'text',
            'required' => true,
            'readonly' => false,
            'label' => $this->l('Webservice URL'),
            'name' => 'RC_REST_URL',
        ];

        if (!RelaisColisApi::isFeatureActivated(RelaisColisApi::TYPE_C2C)) {
            $form['input'][] = [
                'tab' => 'system',
                'col' => 3,
                'type' => 'text',
                'required' => false,
                'readonly' => true,
                'label' => $this->l('Id livemap api'),
                'name' => 'RC_LIVEMAP_API',
                'desc' => $this->l('Id livemap api is available after registring activation key'),
            ];
            $form['input'][] = [
                'tab' => 'system',
                'col' => 3,
                'type' => 'text',
                'readonly' => true,
                'required' => false,
                'label' => $this->l('PID livemapping'),
                'name' => 'RC_LIVEMAP_PID',
                'desc' => $this->l('PID livemapping is available after registring activation key.'),
            ];
            $form['input'][] = [
                'tab' => 'system',
                'type' => 'text',
                'label' => $this->l('Key livemapping'),
                'name' => 'RC_LIVEMAP_KEY',
                'readonly' => true,
                'required' => false,
                'desc' => $this->l('Key livemapping is available after registring activation key.'),
            ];
        }
        // options for home delivery
        $form['input'][] = [
            'tab' => 'system',
            'type' => 'switch',
            'label' => $this->l('Active update order state by cron'),
            'name' => 'RC_CRON_ACTIVE',
            'is_bool' => true,
            'values' => [
                [
                    'id' => 'top_on',
                    'value' => 1,
                    'label' => $this->l('Enabled'),
                ],
                [
                    'id' => 'top_off',
                    'value' => 0,
                    'label' => $this->l('Disabled'),
                ],
            ],
            'desc' => $this->l('You can set a cron to automaticaly update order state. The script cron.php is in your module repository.'),
            'hint' => $this->l('You can set a cron to automaticaly update order state. The script cron.php is in your module repository.'),
        ];

        $form['input'][] = [
            'tab' => 'system',
            'col' => 3,
            'type' => 'text',
            'required' => false,
            'readonly' => true,
            'label' => $this->l('Your CRON token'),
            'name' => 'RC_CRON_TOKEN',
            'desc' => $this->l('If you choose to install automatic CRON you must set your cron task with this token in parameters like this : YOUR_DOMAIN/modules/relaiscolis/cron?php?token=YOUR_TOKEN_VALUE'),
        ];

        $form['input'][] = [
            'tab' => 'system',
            'type' => 'switch',
            'label' => $this->l('Customer can choose TOP option'),
            'name' => 'RC_TOP',
            'required' => false,
            'is_bool' => true,
            'values' => [
                [
                    'id' => 'top_on',
                    'value' => 1,
                    'label' => $this->l('Enabled'),
                ],
                [
                    'id' => 'top_off',
                    'value' => 0,
                    'label' => $this->l('Disabled'),
                ],
            ],
        ];

        $form['input'][] = [
            'tab' => 'system',
            'type' => 'text',
            'label' => $this->l('Top cost'),
            'name' => 'RC_TOP_COST',
            'required' => false,
        ];
        $form['input'][] = [
            'tab' => 'system',
            'type' => 'text',
            'label' => $this->l('Top max hour'),
            'name' => 'RC_TOP_HOUR',
            'required' => false,
            'hint' => $this->l('Hour maximal for delivery top. ex: 16:00'),
        ];

        $form['input'][] = [
            'tab' => 'system',
            'type' => 'select',
            'label' => $this->l('Choose home retreive'),
            'name' => 'RC_HOME_RETRIEVE_TYPE',
            'required' => true,
            'hint' => $this->l('Choose home retreive'),
            'options' => [
                'query' => [
                    ['value' => 2, 'label' => $this->l('Synchro')],
                    ['value' => 3, 'label' => $this->l('Recovery D3E')],
                ],
                'id' => 'value',
                'name' => 'label',
            ],
        ];

        if (!Configuration::get('RC_CRON_ACTIVE')) {
            // options for home delivery
            $form['tabs']['events'] = $this->l('Events');

            $form['input'][] = [
                'tab' => 'events',
                'type' => 'html',
                'name' => 'events',
                'html_content' => $this->context->smarty->fetch($this->local_path . 'views/templates/admin/events.tpl'),
            ];
        }

        // Reset weight range
        $form['tabs']['reset_weight_ranges'] = $this->l('Reset carriers');

        $home_carrier_plus = new Carrier(Configuration::getGlobalValue('RELAISCOLIS_ID_HOME_PLUS'));
        $rc_home_plus_delivery_active = $home_carrier_plus->active == 1;

        $this->context->smarty->assign([
            'rc_home_delivery_active' => RelaisColisApi::isFeatureActivated(RelaisColisApi::TYPE_HOME),
            'rc_home_plus_delivery_active' => $rc_home_plus_delivery_active,
            'rc_delivery_active' => RelaisColisApi::isFeatureActivated(RelaisColisApi::TYPE_DELIVERY),
        ]);
        $form['input'][] = [
            'tab' => 'reset_weight_ranges',
            'type' => 'html',
            'name' => 'reset_weight_ranges',
            'html_content' => $this->context->smarty->fetch($this->local_path . 'views/templates/admin/reset_carriers.tpl'),
        ];

        return $form;
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return [
            'RC_REST_URL' => Configuration::get('RC_REST_URL', null),
            'RC_OPTIONS' => Configuration::get('RC_OPTIONS', null),
            'RC_ACTIVATION_KEY' => Configuration::get('RC_ACTIVATION_KEY', null),
            'RC_LIVEMAP_API' => Configuration::get('RC_LIVEMAP_API', null),
            'RC_LIVEMAP_PID' => Configuration::get('RC_LIVEMAP_PID', null),
            'RC_LIVEMAP_KEY' => Configuration::get('RC_LIVEMAP_KEY', null),
            'RC_CRON_ACTIVE' => Configuration::get('RC_CRON_ACTIVE', false),
            'RC_CRON_TOKEN' => Configuration::get('RC_CRON_TOKEN'),
            'RC_SENSIBLE_COST' => Configuration::get('RC_SENSIBLE_COST', 0),
            'RC_TOP' => Configuration::get('RC_TOP', 0),
            'RC_TOP_COST' => Configuration::get('RC_TOP_COST', 0),
            'RC_TOP_HOUR' => Configuration::get('RC_TOP_HOUR', null),
            'RC_HOME_RETRIEVE_TYPE' => Configuration::get('RC_HOME_RETRIEVE_TYPE', null),
            'RC_TOKEN_HASH' => Configuration::get('RC_TOKEN_HASH', null),
        ];
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        if (Tools::getValue('test_return')) {
            RelaisColisApi::processTestReturn();
        }
        if (Tools::getValue('get_events')) {
            if (!Configuration::get('RC_ACTIVATION_KEY')) {
                $this->context->controller->errors[] = $this->l('Your Account is must be setted to do that.');
            } else {
                $evts = RelaisColisApi::processGetEvts(Configuration::get('RC_ACTIVATION_KEY'));
                if ($evts && $evts !== true) {
                    $this->context->controller->errors[] = $evts;
                }
                if (true === $evts) {
                    $this->context->controller->informations[] = $this->l('Download complete');
                }
            }
        } elseif (Tools::getValue('reset_rc_carrier')) {
            if (RelaisColisResetCarrier::resetRC($this) === true) {
                $this->context->controller->confirmations[] = $this->l('The carrier Relais Colis has been correctly reset');
            }
        } elseif (Tools::getValue('reset_rc_carrierhome')) {
            if (RelaisColisResetCarrier::resetRCHome($this) === true) {
                $this->context->controller->confirmations[] = $this->l('The carrier Relais Colis Home has been correctly reset');
            }
        } elseif (Tools::getValue('reset_rc_carrierhomeplus')) {
            if (RelaisColisResetCarrier::resetRCHomePlus($this) === true) {
                $this->context->controller->confirmations[] = $this->l('The carrier Relais Colis Home + has been correctly reset');
            }
        } else {
            $form_values = $this->getConfigFormValues();

            foreach (array_keys($form_values) as $key) {
                if ($key != 'RC_OPTIONS') {
                    $value = Tools::getValue($key);
                    switch ($key) {
                        case 'RC_TOP_COST':
                            $value = (float) str_replace(',', '.', $value);
                            break;
                        case 'RC_REST_URL':
                            if (substr($value, -1) != '/') {
                                $value = $value . '/';
                            }
                            break;
                    }
                    Configuration::updateValue($key, $value);
                }
            }
            $registration = RelaisColisApi::processConfigurationAccount(Configuration::get('RC_ACTIVATION_KEY'), $this->version);

            if ($registration !== true || count($this->context->controller->errors)) {
                if ($registration !== false) {
                    $this->context->controller->errors[] = $this->l($registration);
                } else {
                    $this->context->controller->errors[] = $this->l('Your Account is not setting correctly, please check your account information and/or webservice url.');
                }
            }
            if (true === $registration && !count($this->context->controller->errors)) {
                $this->context->controller->confirmations[] = $this->l('Your Account is now setting');
                // Remove MultiColis Settings if C2C
                if (RelaisColisApi::isFeatureActivated(RelaisColisApi::TYPE_C2C)) {
                    RelaisColisProduct::removePackageQuantity();
                }
            }
        }
    }

    public function getOrderShippingCost($params, $shipping_cost)
    {
        $cart = $this->context->cart;

        $home_plus_carrier = new Carrier((int) Configuration::getGlobalValue('RELAISCOLIS_ID_HOME_PLUS'));

        if ($this->id_carrier == $home_plus_carrier->id) {
            $options_home_available = $this->getOptionsProductCart($cart->getProducts());
            $options_top = $this->hasTopOptions($cart->getProducts());
            $options_sensible = $this->hasSensibleOptions($cart->getProducts());

            // add customer choic price
            if ((int) $result = RelaisColisInfoHome::alreadyExists((int) $cart->id, (int) $cart->id_customer)) {
                $relais_info_home = new RelaisColisInfoHome((int) $result);
            } else {
                $relais_info_home = new RelaisColisInfoHome();
                $relais_info_home->id_cart = $cart->id;
                $relais_info_home->id_customer = $cart->id_customer;
            }

            if (empty($relais_info_home->id_customer)) {
                $relais_info_home->id_customer = 0;
            }

            if ($options_sensible) {
                $relais_info_home->sensible = 1;
            }
            if ($options_top) {
                if (Configuration::get('RC_TOP') && $relais_info_home->top) {
                    $shipping_cost += (float) Configuration::get('RC_TOP_COST');
                }
                if (!Configuration::get('RC_TOP')) {
                    $shipping_cost += (float) Configuration::get('RC_TOP_COST');
                    $relais_info_home->top = 1;
                }
            }
            $options_home_list = RelaisColisHomeOptions::getRelaisColisHomeOptionsActive();
            if (is_array($options_home_list)) {
                foreach ($options_home_list as $row) {
                    if (in_array($row['option'], $options_home_available)) {
                        if ($row['customer_choice'] && $relais_info_home->{$row['option']}) {
                            $shipping_cost += (float) $row['cost'];
                            $relais_info_home->{$row['option']} = 1;
                        }
                        if (!$row['customer_choice']) {
                            $shipping_cost += (float) $row['cost'];
                            $relais_info_home->{$row['option']} = 1;
                        }
                    } else {
                        $relais_info_home->{$row['option']} = 0;
                    }
                }
            }
            $relais_info_home->save();
        }

        return $shipping_cost;
    }

    public function getOrderShippingCostExternal($params)
    {
        return true;
    }

    public function addCarrier()
    {
        $carrier = new Carrier();

        $carrier->name = $this->l('Relais Colis');
        $carrier->is_module = true;
        $carrier->active = 0;
        $carrier->range_behavior = 1;
        $carrier->need_range = 1;
        $carrier->shipping_external = true;
        $carrier->max_height = 170;
        $carrier->max_width = 170;
        $carrier->max_depth = 170;
        $carrier->max_weight = 19.999999;
        $carrier->url = RelaisColisApi::TRACKING_URL;
        $carrier->external_module_name = $this->name;
        $carrier->shipping_method = 1;

        foreach (Language::getLanguages() as $lang) {
            $carrier->delay[$lang['id_lang']] = $this->l('Fast delivery');
        }

        if ($carrier->add() == true) {
            @copy(dirname(__FILE__) . '/views/img/carrier_image.jpg', _PS_SHIP_IMG_DIR_ . '/' . (int) $carrier->id . '.jpg');
            Configuration::updateGlobalValue('RELAISCOLIS_ID', (int) $carrier->id);

            return $carrier;
        }

        return false;
    }

    public function addCarrierMax()
    {
        $carrier = new Carrier();

        $carrier->name = $this->l('Relais Colis');
        $carrier->is_module = true;
        $carrier->active = 0;
        $carrier->range_behavior = 1;
        $carrier->need_range = 1;
        $carrier->shipping_external = true;
        $carrier->max_height = 250;
        $carrier->max_width = 250;
        $carrier->max_depth = 250;
        $carrier->max_weight = 40;
        $carrier->url = RelaisColisApi::TRACKING_URL;
        $carrier->external_module_name = $this->name;
        $carrier->shipping_method = 1;

        foreach (Language::getLanguages() as $lang) {
            $carrier->delay[$lang['id_lang']] = $this->l('Fast delivery');
        }

        if ($carrier->add() == true) {
            @copy(dirname(__FILE__) . '/views/img/carrier_image.jpg', _PS_SHIP_IMG_DIR_ . '/' . (int) $carrier->id . '.jpg');
            Configuration::updateGlobalValue('RELAISCOLIS_ID_MAX', (int) $carrier->id);

            return $carrier;
        }

        return false;
    }

    public function addCarrierHome()
    {
        $carrier = new Carrier();

        $carrier->name = $this->l('Relais Colis Home');
        $carrier->is_module = true;
        $carrier->active = 0;
        $carrier->range_behavior = 1;
        $carrier->need_range = 1;
        $carrier->shipping_external = true;
        $carrier->max_height = 0;
        $carrier->max_weight = 0;
        $carrier->url = RelaisColisApi::TRACKING_URL;
        $carrier->external_module_name = $this->name;
        $carrier->shipping_method = 2;

        foreach (Language::getLanguages() as $lang) {
            $carrier->delay[$lang['id_lang']] = $this->l('Fast delivery');
        }

        if ($carrier->add() == true) {
            @copy(dirname(__FILE__) . '/views/img/carrier_image.jpg', _PS_SHIP_IMG_DIR_ . '/' . (int) $carrier->id . '.jpg');
            Configuration::updateGlobalValue('RELAISCOLIS_ID_HOME', (int) $carrier->id);

            return $carrier;
        }

        return false;
    }

    public function addCarrierHomePlus()
    {
        $carrier = new Carrier();

        $carrier->name = $this->l('Relais Colis Home +');
        $carrier->is_module = true;
        $carrier->active = 0;
        $carrier->range_behavior = 1;
        $carrier->need_range = 1;
        $carrier->shipping_external = true;
        $carrier->max_height = 0;
        $carrier->max_weight = 0;
        $carrier->url = RelaisColisApi::TRACKING_URL;
        $carrier->external_module_name = $this->name;
        $carrier->shipping_method = 2;

        foreach (Language::getLanguages() as $lang) {
            $carrier->delay[$lang['id_lang']] = $this->l('Fast delivery');
        }

        if ($carrier->add() == true) {
            @copy(dirname(__FILE__) . '/views/img/carrier_image.jpg', _PS_SHIP_IMG_DIR_ . '/' . (int) $carrier->id . '.jpg');
            Configuration::updateGlobalValue('RELAISCOLIS_ID_HOME_PLUS', (int) $carrier->id);

            return $carrier;
        }

        return false;
    }

    public function addGroups($carrier)
    {
        $groups_ids = [];
        $groups = Group::getGroups(Context::getContext()->language->id);
        foreach ($groups as $group) {
            $groups_ids[] = $group['id_group'];
        }

        $carrier->setGroups($groups_ids);
    }

    public function addRanges($carrier)
    {
        $range_weight = new RangeWeight();
        $range_weight->id_carrier = $carrier->id;
        $range_weight->delimiter1 = '0.000001';
        $range_weight->delimiter2 = '20';
        $range_weight->add();
    }

    public function addRangesMax($carrier)
    {
        $range_weight = new RangeWeight();
        $range_weight->id_carrier = $carrier->id;
        $range_weight->delimiter1 = '0.000001';
        $range_weight->delimiter2 = '40';
        $range_weight->add();
    }

    public function addRangesHome($carrier)
    {
        $range_price = new RangePrice();
        $range_price->id_carrier = $carrier->id;
        $range_price->delimiter1 = '0';
        $range_price->delimiter2 = '10000';
        $range_price->add();

        $range_weight = new RangeWeight();
        $range_weight->id_carrier = $carrier->id;
        $range_weight->delimiter1 = '0';
        $range_weight->delimiter2 = '10000';
        $range_weight->add();
    }

    public function addZones($carrier)
    {
        return true;
    }

    public function hookActionAdminControllerSetMedia($params)
    {
        $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        if (Tools::getValue('controller') == 'AdminOrders' || Tools::getValue('controller') == 'AdminManageRelaisColisReturn') {
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
        }
    }

    /**
     * Save Relais Colis Product
     */
    public function hookActionProductUpdate($params)
    {
        if (!RelaisColisApi::isFeatureActivated(RelaisColisApi::TYPE_C2C)) {
            if (Tools::getValue('key_tab') == 'ModuleRelaiscolis' && (Tools::isSubmit('submitAddproductAndStay') || Tools::isSubmit('submitAddproduct'))) {
                $id_product = Tools::getValue('id_product');
                $id_relais_colis_product = RelaisColisProduct::getRelaisColisProductId((int) $id_product);
                $relais_colis_product = new RelaisColisProduct((int) $id_relais_colis_product);
                $relais_colis_product->id_product_home = (int) $id_product;
                $relais_colis_product->package_quantity = (int) Tools::getValue('packageRC');
                $relais_colis_product->save();
            }
        }
    }

    /**
     * Hook for Product BO
     */
    public function hookDisplayAdminProductsExtra($params)
    {
        if (!RelaisColisApi::isFeatureActivated(RelaisColisApi::TYPE_C2C)) {
            $id_product = Tools::getValue('id_product');
            $product = new Product((int) $id_product);
            $id_relais_colis_product = RelaisColisProduct::getRelaisColisProductId((int) $id_product);
            $relais_colis_product = new RelaisColisProduct((int) $id_relais_colis_product);
            $link = new Link();

            $this->context->smarty->assign([
                'admin_product_link' => $link->getAdminLink('AdminProducts'),
                'product' => $product,
                'relais_colis_product' => $relais_colis_product,
            ]);

            return $this->display(__FILE__, 'views/templates/hook/admin_product_extra.tpl');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        // TODO
        $context = Context::getContext();
        if ($context->controller->php_self == 'order-opc') {
            $this->hookDisplayCarrierList(null);
        }
    }

    public function hookNewOrder($params)
    {
        // Return if not Relais Colis
        if (
            !in_array($params['order']->id_carrier, [
                Configuration::getGlobalValue('RELAISCOLIS_ID'),
                Configuration::getGlobalValue('RELAISCOLIS_ID_HOME'),
                Configuration::getGlobalValue('RELAISCOLIS_ID_MAX'),
                Configuration::getGlobalValue('RELAISCOLIS_ID_HOME_PLUS'),
            ])
        ) {
            return;
        }

        // Relais Colis MAX
        $order = $params['order'];
        if (
            $order->id_carrier == Configuration::getGlobalValue('RELAISCOLIS_ID')
            || $order->id_carrier == Configuration::getGlobalValue('RELAISCOLIS_ID_MAX')
        ) {
            $order->id_address_delivery = $this->isSameAddress((int) $order->id_address_delivery, (int) $order->id_cart, (int) $order->id_customer, $order->id_address_invoice);
            $order->update();
        }

        // Relais Colis HOME+
        if ($order->id_carrier == Configuration::getGlobalValue('RELAISCOLIS_ID_HOME_PLUS')) {
            // add order id to info home
            if ((int) $result = RelaisColisInfoHome::alreadyExists((int) $order->id_cart, (int) $order->id_customer)) {
                $relais_info_home = new RelaisColisInfoHome((int) $result);
                $relais_info_home->id_order = (int) $order->id;
                $relais_info_home->save();
            }
        }

        // Relais Colis Order
        $relais_order = new RelaisColisOrder();
        $relais_order->id_order = (int) $order->id;
        $relais_order->id_relais_colis_info = 0;
        $relais_order->id_customer = (int) $order->id_customer;
        $relais_order->order_weight = (float) $order->getTotalWeight();
        $relais_order->is_send = false;
        $relais_order->pdf_number = null;

        $id_relais_colis_info = RelaisColisInfo::alreadyExists((int) $order->id_cart, (int) $order->id_customer);
        if ((int) $id_relais_colis_info) {
            // Delete an eventually RelaisColisInfo reccord if carrier is Home or Home+
            if ($params['order']->id_carrier == Configuration::getGlobalValue('RELAISCOLIS_ID_HOME_PLUS') || $params['order']->id_carrier == Configuration::getGlobalValue('RELAISCOLIS_ID_HOME')) {
                $relaisColisInfo = new RelaisColisInfo($id_relais_colis_info);
                $relaisColisInfo->delete();
            } else {
                $relais_order->id_relais_colis_info = $id_relais_colis_info;
            }
        }
        $relais_order->save();

        // Relais Colis Order Product
        $package_number = 1;
        foreach ($order->getProducts() as $product) {
            // Get the package quantity for each product

            $relais_colis_product = new RelaisColisProduct((int) RelaisColisProduct::getRelaisColisProductId((int) $product['product_id']));
            if (RelaisColisApi::isFeatureActivated(RelaisColisApi::TYPE_C2C) && Configuration::get('RC_TOKEN_ACTIVE') === 1) {
                $relais_colis_product->package_quantity = 1;
            }

            // TODO MOndial relay
            if (empty($relais_colis_product->package_quantity) && $relais_colis_product->package_quantity == 0) {
                continue;
            } else {
                $package_quantity = (int) $relais_colis_product->package_quantity;
            }

            if ($package_quantity <= 0) {
                continue;
            }

            // Generate an object for each package quantity
            for ($j = 1; $j <= $product['product_quantity']; ++$j) {
                $total_weight = 0;
                for ($i = 1; $i <= $package_quantity; ++$i) {
                    $relais_order_product = new RelaisColisOrderProduct();
                    $relais_order_product->id_relais_colis_order = (int) $relais_order->id;
                    $relais_order_product->id_product = (int) $product['product_id'];
                    $relais_order_product->package_number = (int) $package_number;
                    if ($i == $package_quantity) {
                        $relais_order_product->weight = (float) Tools::ps_round($product['product_weight'], 2) - (float) $total_weight;
                    } else {
                        $relais_order_product->weight = (float) Tools::ps_round($product['product_weight'] / $package_quantity, 2);
                        $total_weight += (float) $relais_order_product->weight;
                    }

                    $relais_order_product->save();
                    ++$package_number;
                }
            }
        }
    }

    public function hookActionCarrierUpdate($params)
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        $globalVar = null;
        $oldCarrierId = $params['id_carrier'];
        $newCarrierId = (int) $params['carrier']->id;

        switch ($oldCarrierId) {
            case (int) Configuration::getGlobalValue('RELAISCOLIS_ID'):
                // Update new carrier RC
                $globalVar = 'RELAISCOLIS_ID';

                // Set Default extra attribute to the new Carrier
                $carrier = new Carrier($newCarrierId);
                $carrier->max_height = 170;
                $carrier->max_width = 170;
                $carrier->max_depth = 170;
                $carrier->max_weight = 19.999999;
                $carrier->range_behavior = 1;
                $carrier->need_range = 1;
                if (!RelaisColisApi::isFeatureActivated(RelaisColisApi::TYPE_DELIVERY) || RelaisColisApi::isFeatureActivated(RelaisColisApi::TYPE_MAX)) {
                    $carrier->active = 0;
                }
                $carrier->save();
                break;

            case (int) Configuration::getGlobalValue('RELAISCOLIS_ID_MAX'):
                // Update new carrier RC_ID_MAX
                $globalVar = 'RELAISCOLIS_ID_MAX';

                // Set Default extra attribute to the new Carrier
                $carrier = new Carrier($newCarrierId);
                $carrier->max_height = 250;
                $carrier->max_width = 250;
                $carrier->max_depth = 250;
                $carrier->max_weight = 40;
                $carrier->range_behavior = 1;
                $carrier->need_range = 1;
                if (!RelaisColisApi::isFeatureActivated(RelaisColisApi::TYPE_MAX)) {
                    $carrier->active = 0;
                }
                $carrier->save();
                break;

            case (int) Configuration::getGlobalValue('RELAISCOLIS_ID_HOME'):
                // Update new carrier RC_ID_HOME
                $globalVar = 'RELAISCOLIS_ID_HOME';

                if (!RelaisColisApi::isFeatureActivated(RelaisColisApi::TYPE_HOME)) {
                    $carrier = new Carrier($newCarrierId);
                    $carrier->active = 0;
                    $carrier->save();
                }
                break;

            case (int) Configuration::getGlobalValue('RELAISCOLIS_ID_HOME_PLUS'):
                // Update new carrier RC_ID_HOME_PLUS
                $globalVar = 'RELAISCOLIS_ID_HOME_PLUS';

                if (!RelaisColisApi::isFeatureActivated(RelaisColisApi::TYPE_HOME)) {
                    $carrier = new Carrier($newCarrierId);
                    $carrier->active = 0;
                    $carrier->save();
                }
                break;
        }

        if ($globalVar !== null) {
            Configuration::updateGlobalValue($globalVar, $newCarrierId);
            Configuration::updateGlobalValue('RC_CARRIER_ID_HIST', Configuration::getGlobalValue('RC_CARRIER_ID_HIST') . '|' . $oldCarrierId);
        }
    }

    public function hookDisplayCustomerAccount($params)
    {
        if (Configuration::get('PS_ORDER_RETURN') && RelaisColisApi::isFeatureActivated(RelaisColisApi::TYPE_RETURN)) {
            return $this->display(dirname(__FILE__), 'my_account.tpl');
        }
    }

    public function hookDisplayOrderDetail($params)
    {
        $order = new Order($params['order']->id);
        if (Configuration::getGlobalValue('RELAISCOLIS_ID')) {
            if ($id_relais_colis_order = RelaisColisOrder::getRelaisColisOrderId((int) $order->id)) {
                $relais_colis_order = new RelaisColisOrder((int) $id_relais_colis_order);
                $link_tracking_rc = Tools::substr($relais_colis_order->pdf_number, 2, 10);
                $this->context->smarty->assign([
                    'link_tracking_rc' => $link_tracking_rc,
                    'c2c_activated' => RelaisColisApi::isFeatureActivated(RelaisColisApi::TYPE_C2C) && Configuration::get('RC_TOKEN_ACTIVE') == 1,
                ]);

                return $this->display(__FILE__, 'order_detail.tpl');
            }
        }
    }

    public function hookdisplayBackOfficeHeader()
    {
        $this->context->controller->addCss($this->_path . 'views/css/tab.css');
    }

    public function hookDisplayAdminOrder($params)
    {
        $errors = [];
        $show_tab = false;
        $order = new Order($params['id_order']);
        $order_carrier = new Carrier((int) $order->id_carrier);

        // Check if Order is ours
        if (!$order_carrier || $order_carrier->external_module_name != $this->name){
            return false;
        }  
        
        if (Tools::isSubmit('submitSendingLabel')) {
            $relais_colis_order_product = RelaisColisOrderProduct::getPackagesByRcOrderId((int) Tools::getValue('id_relais_colis_order'));
            // IF: MONO ELSE: MULTI
            if (empty($relais_colis_order_product)) {
                if (!(int) Tools::getValue('weight')) {
                    $errors[] = $this->l('Your must have weight set in your order');
                } else {
                    if (!empty(RelaisColisOrderPdf::getPdfsNumber((int) Tools::getValue('id_relais_colis_order')))) {
                        $errors[] = $this->l('This order has already been processed');
                    } elseif ((int) Tools::getValue('id_relais_colis_order')) {
                        $relais_colis_order = new RelaisColisOrder((int) Tools::getValue('id_relais_colis_order'));
                        $relais_colis_order->order_weight = (int) Tools::getValue('weight') / 1000;
                        $relais_colis_order->save();
                        RelaisColisApi::processSending($order->id);

                        // MAJ WIDGET DEC 2024 - Sauvegarde numéro suivi de la commande
                        $tracking_number = RelaisColisOrder::getPdfNumber($order->id);
                        self::updateOrderCarrierTrackingNumber($tracking_number, $order->id);

                        // MAJ WIDGET DEC 2024 - Changement etat commande en statut expédiée et envoi mail client
                        if ($order->getCurrentState() != _PS_OS_SHIPPING_) {                                      
                            try {
                                $history = new OrderHistory();
                                $history->id_order = (int)($order->id);
                                $history->id_order_state = _PS_OS_SHIPPING_;
                                $history->changeIdOrderState(_PS_OS_SHIPPING_, $order->id, true);
                                $history->addWithemail();
                            } catch (\Exception $e) {
                                //Tools::dieObject($e->getMessage());
                            }
                        }

                        // MAJ WIDGET DEC 2024 - Forcer le rechargement de la page
                        Tools::redirectAdmin(
                            $this->context->link->getAdminLink('AdminOrders', true) . '&vieworder&id_order=' . $order->id
                        );
                    }
                }
            } else {
                $relais_colis_order = new RelaisColisOrder((int) Tools::getValue('id_relais_colis_order'));
                $relais_colis_op_details = RelaisColisOrderProduct::getOrderWeightDetail((int) $relais_colis_order->id, $order);

                if ($relais_colis_op_details['same_total'] == true) {
                    if (!empty(RelaisColisOrderPdf::getPdfsNumber((int) Tools::getValue('id_relais_colis_order')))) {
                        $errors[] = $this->l('This order has already been processed');
                    } else {
                        RelaisColisApi::processSending($order->id);
                    }
                } else {
                    $errors[] = $this->l('Your weight are incorrect');
                }
            }
        }

        if (Tools::isSubmit('submitNewRcAddress')) {
            $order->id_address_delivery = Tools::getValue('rc_id_new_address');
            $order->save();
            // To recharge the page for correct delivery address display
            $url = $this->context->link->getAdminLink('AdminOrders', true) . '&vieworder&id_order=' . $params['id_order'];
            Tools::redirectAdmin($url);
        }

        if (Configuration::getGlobalValue('RELAISCOLIS_ID')) {
            $link_tracking_rc = '';
            $relais_carrier = new Carrier(Configuration::getGlobalValue('RELAISCOLIS_ID'));
            $relais_max_carrier = new Carrier(Configuration::getGlobalValue('RELAISCOLIS_ID_MAX'));
            $home_carrier = new Carrier(Configuration::getGlobalValue('RELAISCOLIS_ID_HOME'));
            $home_carrier_plus = new Carrier(Configuration::getGlobalValue('RELAISCOLIS_ID_HOME_PLUS'));
            $order_carrier = new Carrier($order->id_carrier);
            if (
                in_array($order_carrier->id_reference, [$relais_carrier->id_reference, $home_carrier->id_reference, $relais_max_carrier->id_reference, $home_carrier_plus->id_reference])
                || in_array((int) $order->id_carrier, explode('|', Configuration::getGlobalValue('RC_CARRIER_ID_HIST')))
            ) {
                $id_relais_colis_info = RelaisColisInfo::alreadyExists($order->id_cart, $order->id_customer);
                $relay_info = [];
                if ((int) $id_relais_colis_info) {
                    $relais_colis_info = new RelaisColisInfo((int) $id_relais_colis_info);
                    $relay_info = [
                        'rel' => $relais_colis_info->rel,
                        'name' => $relais_colis_info->rel_name,
                        'street' => $relais_colis_info->rel_adr,
                        'postcode' => $relais_colis_info->rel_cp,
                        'city' => $relais_colis_info->rel_vil,
                    ];
                }
                $this->context->smarty->assign([
                    'order' => $order,
                    'relay_info' => $relay_info,
                ]);

                $has_etiquette = RelaisColisOrder::getPdfNumber($params['id_order']);

                $weight = 0;
                $relais_colis_order_product = [];
                $etiquettes = [];
                $relais_colis_op_details = [];
                if ($id_relais_colis_order = RelaisColisOrder::getRelaisColisOrderId((int) $order->id)) {
                    $relais_colis_order = new RelaisColisOrder((int) $id_relais_colis_order);
                    $weight = $relais_colis_order->order_weight;
                    $link_tracking_rc = Tools::substr($relais_colis_order->pdf_number, 2, 10);

                    $etiquettes = RelaisColisOrderPdf::getPdfsNumber((int) $relais_colis_order->id);
                    $relais_colis_order_product = RelaisColisOrderProduct::getPackagesByRcOrderId((int) $relais_colis_order->id);
                    $relais_colis_op_details = RelaisColisOrderProduct::getOrderWeightDetail((int) $relais_colis_order->id, $order);

                    if (count($etiquettes) == 1) {
                        $etiquettes[0]['weight'] = (float) $weight;
                    }
                }

                $valid_delivery_address = true;
                
                // MAJ WIDGET DEC 2024 - Désactivation vérification adresse
                // Valide le point relais si les noms sont identiques, peu pertinent, pour les adresses longues, ça beug.
                /*
                // If it's not a Home or Home+
                if (!empty($relay_info)) {
                    // Test if delivery address is valid
                    $valid_delivery_address = false;

                    // Get the deleviry address
                    $delivery_address = new Address($order->id_address_delivery);

                    $formatted_name_relay = trim(preg_replace('/[0-9!<>,;?=+()@#"°{}_$%:]/', '', $relais_colis_info->rel_name));
                    // Compare if lastname field of address is equal to rel_name field of relaisColisInfo
                    if ($delivery_address->lastname == $formatted_name_relay) {
                        $valid_delivery_address = true;
                    }
                } */

                $rc_addresses = RelaisColisOrder::getRCDeliveryAddresses($params['id_order']);
                $print_pdf_url = Configuration::get('RC_REST_URL') . 'etiquette/generate';

                $this->context->smarty->assign([
                    'rc_addresses' => $rc_addresses,
                    'valid_delivery_address' => $valid_delivery_address,
                    'has_etiquette' => $has_etiquette,
                    'etiquettes' => $etiquettes,
                    'admin_token' => Tools::getAdminTokenLite('AdminManageRelaisColisOrderProduct'),
                    'c2c_activated' => RelaisColisApi::isFeatureActivated(RelaisColisApi::TYPE_C2C),
                    'print_pdf_url' => $print_pdf_url,
                    'weight' => $weight * 1000,
                    'relais_colis_packages' => $relais_colis_order_product,
                    'relais_colis_op_details' => $relais_colis_op_details,
                    'id_relais_colis_order' => $id_relais_colis_order,
                    'link_tracking_rc' => $link_tracking_rc,
                    'products' => $order->getProducts(),
                    'activationKey' => Configuration::get('RC_ACTIVATION_KEY'),
                ]);

                $show_tab = true;
            }
        }

        /* C2C */
        if (RelaisColisApi::isFeatureActivated(RelaisColisApi::TYPE_C2C)) {
            if (Configuration::getGlobalValue('RC_TOKEN_ACTIVE') == 1) {
                $link = new Link();
                $redirect_link = $link->getAdminLink('AdminOrders');
                $redirect_link .= '&id_order=' . $order->id . '&vieworder';

                $id_relais_colis_info = RelaisColisInfo::alreadyExists($order->id_cart, $order->id_customer);
                $relais_colis_info = new RelaisColisInfo($id_relais_colis_info);
                $relay_c2c_id = 0;
                if ($relais_colis_info->id_relais_colis_info != 0) {
                    $relay_c2c_id = $id_relais_colis_info;
                }

                $this->context->controller->addCSS($this->_path . '/views/css/front.css');

                $relais_token_info = false;
                $token_balance = false;
                $token_cost = false;

                //if (Configuration::get('RC_TOKEN_HASH')) {
                // MAJ WIDGET DEC 2024 - modif condition pour n'utiliser l'API que si le numéro de suivi est vide
                $tracking_number = RelaisColisOrder::getPdfNumber($order->id);
                if (Configuration::get('RC_TOKEN_HASH') && empty($tracking_number)) {
                    $relais_token_info = RelaisColisApi::processTokenBalance();
                    $token_balance = (float) $relais_token_info->balance;
                    $token_cost = RelaisColisApi::getTokenPrice([$relais_colis_order->order_weight * 1000]);
                }
                
                $id_relais_colis_order = RelaisColisOrder::getRelaisColisOrderId((int) $order->id);
                $relais_colis_order = new RelaisColisOrder((int) $id_relais_colis_order);

                $this->context->smarty->assign([
                    'redirect_link' => $redirect_link,
                    'redirect_link_csv' => $link->getAdminLink('AdminManageRelaisColis'),
                    'relay_c2c_id' => $relay_c2c_id,
                    'order' => $order,
                    'url_img' => '../modules/relaiscolis/views/img/',
                    'token_balance' => $token_balance,
                    'token_cost' => $token_cost !== false ? $token_cost[0] : $token_cost,
                ]);
            } else {
                $errors[] = $this->l('Your C2C account is invalid, please verify your Hash Token.');
            }
        }

        $this->context->smarty->assign([
            'has_c2c' => RelaisColisApi::isFeatureActivated(RelaisColisApi::TYPE_C2C) && Configuration::get('RC_TOKEN_ACTIVE') == 1,
            'ens_id_c2c' => Configuration::get('RC_ENS_ID') == RelaisColisApi::ENS_ID_C2C,
            'rcErrors' => json_encode($errors)
        ]);

        if ($show_tab) {
            return $this->display(__FILE__, 'order_tab.tpl');
        }

        return false;
    }

    public function hookDisplayCarrierList($params)
    {
        $cart = $this->context->cart;
        $tab_id_rc = explode('|', Configuration::getGlobalValue('RC_CARRIER_ID_HIST'));
        if (!Configuration::get('RC_ACTIVATION_KEY') || !Configuration::get('RC_IS_ACTIVE') || !$this->active || !$cart->id) {
            $tab_id_rc[] = (int) Configuration::getGlobalValue('RELAISCOLIS_ID');
            $tab_id_rc[] = (int) Configuration::getGlobalValue('RELAISCOLIS_ID_MAX');
            $tab_id_rc[] = (int) Configuration::getGlobalValue('RELAISCOLIS_ID_HOME');
            $tab_id_rc[] = (int) Configuration::getGlobalValue('RELAISCOLIS_ID_HOME_PLUS');
            $this->context->smarty->assign('ids_relais_exclude', $tab_id_rc);

            return $this->display(__FILE__, 'relais_colis_error.tpl');
        }
        $relais_carrier = new Carrier((int) Configuration::getGlobalValue('RELAISCOLIS_ID'));
        if (!isset($relais_carrier) || !$relais_carrier->active || !RelaisColisApi::isFeatureActivated(RelaisColisApi::TYPE_DELIVERY)) {
            $tab_id_rc[] = (int) Configuration::getGlobalValue('RELAISCOLIS_ID');
        }
        $relais_max_carrier = new Carrier((int) Configuration::getGlobalValue('RELAISCOLIS_ID_MAX'));
        if (!isset($relais_max_carrier) || !$relais_max_carrier->active || !RelaisColisApi::isFeatureActivated(RelaisColisApi::TYPE_MAX)) {
            $tab_id_rc[] = (int) Configuration::getGlobalValue('RELAISCOLIS_ID_MAX');
        }
        $home_carrier = new Carrier((int) Configuration::getGlobalValue('RELAISCOLIS_ID_HOME'));
        if (!isset($home_carrier) || !$home_carrier->active || !RelaisColisApi::isFeatureActivated(RelaisColisApi::TYPE_HOME)) {
            $tab_id_rc[] = (int) Configuration::getGlobalValue('RELAISCOLIS_ID_HOME');
        }
        $home_plus_carrier = new Carrier((int) Configuration::getGlobalValue('RELAISCOLIS_ID_HOME_PLUS'));
        if (!isset($home_plus_carrier) || !$home_plus_carrier->active || !RelaisColisApi::isFeatureActivated(RelaisColisApi::TYPE_HOME)) {
            $tab_id_rc[] = (int) Configuration::getGlobalValue('RELAISCOLIS_ID_HOME_PLUS');
        }
        // are all product with weight ?
        $products_list = $cart->getProducts();
        $cart_available = true;
        $is_too_high_for_BE = false;
        $total_weight = 0;
        $max_unit_weight = 0;
        $is_relais_max = 0;
        $options_top = $this->hasTopOptions($products_list);
        $options_out = $this->hasOutStandardOptions($products_list);
        $options_home_available = $this->getOptionsProductCart($products_list);

        foreach ($products_list as $product) {
            if (!(float) $product['weight']) {
                $cart_available = false;
            } else {
                if ((float) $product['weight'] > $max_unit_weight) {
                    $max_unit_weight = (float) $product['weight'];
                }
                $total_weight += (float) $product['weight'] * $product['quantity'];
            }

            if ($product['height'] > self::MAX_HEIGHT_BE) {
                $is_too_high_for_BE = true;
            }
        }

        if ((float) $total_weight >= 20) {
            $is_relais_max = 1;
        }

        $this->context->smarty->assign('is_relais_max', $is_relais_max);
        $is_selected = false;
        $is_selected_home = false;
        $is_selected_home_plus = false;
        if ($cart->id_carrier == $relais_carrier->id || $cart->id_carrier == $relais_max_carrier->id) {
            $is_selected = true;
        }
        if ($cart->id_carrier == $home_carrier->id) {
            $is_selected_home = true;
        }
        if ($cart->id_carrier == $home_plus_carrier->id) {
            $is_selected_home_plus = true;
        }
        $street = '';
        $city = '';
        $postcode = '';
        $country_selected = 'FRA';
        if ((int) $cart->id_address_delivery) {
            $address = new Address((int) $cart->id_address_delivery);
            $firstname = $address->firstname;
            $lastname = $address->lastname;
            $street = $address->address1;
            $street2 = $address->address2;
            $city = $address->city;
            $postcode = $address->postcode;
            $country_address = new Country($address->id_country);
            foreach ($this->limited_countries as $country) {
                if ($country['iso2'] == $country_address->iso_code) {
                    $country_selected = $country['iso3'];
                }
            }

            // *RelaisColis Home* and *RelaisColis Home +* only active in France or Belgium....
            if ($country_address->iso_code != 'FR' && $country_address->iso_code != 'BE') {
                $tab_id_rc[] = (int) Configuration::getGlobalValue('RELAISCOLIS_ID_HOME');
                $tab_id_rc[] = (int) Configuration::getGlobalValue('RELAISCOLIS_ID_HOME_PLUS');
                $is_selected_home = false;
                $is_selected_home_plus = false;
            }
            // ....But in Belgium only if weight > 15kg and height of all products < 170cm
            if ($country_address->iso_code == 'BE' && (!RelaisColisApi::isFeatureActivated(RelaisColisApi::TYPE_BELGIUM) || $total_weight <= self::MAX_WEIGHT_BE || $is_too_high_for_BE)) {
                $tab_id_rc[] = (int) Configuration::getGlobalValue('RELAISCOLIS_ID_HOME');
                $tab_id_rc[] = (int) Configuration::getGlobalValue('RELAISCOLIS_ID_HOME_PLUS');
                $is_selected_home = false;
                $is_selected_home_plus = false;
            }
            if ($country_address->iso_code == 'BE' && (!RelaisColisApi::isFeatureActivated(RelaisColisApi::TYPE_BELGIUM) || $is_too_high_for_BE || $total_weight > self::MAX_WEIGHT_BE)) {
                // not allowed to deliver in belgium
                $tab_id_rc[] = (int) Configuration::getGlobalValue('RELAISCOLIS_ID');
                $tab_id_rc[] = (int) Configuration::getGlobalValue('RELAISCOLIS_ID_MAX');
                $is_selected = false;
            }
            if ($is_selected_home || $is_selected_home_plus) {
                $this->context->smarty->assign([
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'street' => $street,
                    'street2' => $street2,
                    'city' => $city,
                    'postcode' => $postcode,
                ]);
            } else { // We still need to initialize street2
                $this->context->smarty->assign([
                    'street2' => $street2,
                ]);
            }
        }
        $url_img = 'modules/relaiscolis/views/img/';
        $link = new Link();
        $redirect_link = $link->getModuleLink('relaiscolis', 'redirect', [], true);

        // searching if delivery point is already selected
        $id_relais_colis_info = RelaisColisInfo::alreadyExists($this->context->cart->id, $this->context->customer->id);
        $relay_info = [];
        $have_selected_point = false;
        $have_selected_last = false;
        $redirect_link_last_point = false;
        if ((int) $id_relais_colis_info) {
            $relais_colis_info = new RelaisColisInfo((int) $id_relais_colis_info);
            if ($is_relais_max && $relais_colis_info->frc_max != 1) {
                // range weight has changed cant delivery in standard delivery point
                $relais_colis_info->delete();
            } else {
                $relay_info = [
                    'name' => $relais_colis_info->rel_name,
                    'street' => $relais_colis_info->rel_adr,
                    'postcode' => $relais_colis_info->rel_cp,
                    'city' => $relais_colis_info->rel_vil,
                    'ouvlun' => RelaisColisInfo::formatOpeningTime($relais_colis_info->ouvlun),
                    'ouvmar' => RelaisColisInfo::formatOpeningTime($relais_colis_info->ouvmar),
                    'ouvmer' => RelaisColisInfo::formatOpeningTime($relais_colis_info->ouvmer),
                    'ouvjeu' => RelaisColisInfo::formatOpeningTime($relais_colis_info->ouvjeu),
                    'ouvven' => RelaisColisInfo::formatOpeningTime($relais_colis_info->ouvven),
                    'ouvsam' => RelaisColisInfo::formatOpeningTime($relais_colis_info->ouvsam),
                    'ouvdim' => RelaisColisInfo::formatOpeningTime($relais_colis_info->ouvdim),
                ];
                $have_selected_point = true;
            }
        } else {
            // if no delivery point selected looking for last delivery point selected in last order
            $id_relais_colis_info = RelaisColisInfo::lastSelectedPoint($this->context->cart->id, $this->context->customer->id);
            $relay_info = [];
            if ((int) $id_relais_colis_info) {
                $relais_colis_info = new RelaisColisInfo((int) $id_relais_colis_info);
                if (!$is_relais_max || ($cart->id_carrier == $relais_max_carrier->id && $relais_colis_info->frc_max == 1)) {
                    $relay_info = [
                        'name' => $relais_colis_info->rel_name,
                        'street' => $relais_colis_info->rel_adr,
                        'postcode' => $relais_colis_info->rel_cp,
                        'city' => $relais_colis_info->rel_vil,
                        'ouvlun' => RelaisColisInfo::formatOpeningTime($relais_colis_info->ouvlun),
                        'ouvmar' => RelaisColisInfo::formatOpeningTime($relais_colis_info->ouvmar),
                        'ouvmer' => RelaisColisInfo::formatOpeningTime($relais_colis_info->ouvmer),
                        'ouvjeu' => RelaisColisInfo::formatOpeningTime($relais_colis_info->ouvjeu),
                        'ouvven' => RelaisColisInfo::formatOpeningTime($relais_colis_info->ouvven),
                        'ouvsam' => RelaisColisInfo::formatOpeningTime($relais_colis_info->ouvsam),
                        'ouvdim' => RelaisColisInfo::formatOpeningTime($relais_colis_info->ouvdim),
                    ];
                    $have_selected_last = true;
                    $redirect_link_last_point = $link->getModuleLink('relaiscolis', 'redirect', [
                        'id_last_point' => (int) $id_relais_colis_info, ], true);
                }
            }
        }
        if ($options_out) {
            $tab_id_rc[] = (int) Configuration::getGlobalValue('RELAISCOLIS_ID');
            $tab_id_rc[] = (int) Configuration::getGlobalValue('RELAISCOLIS_ID_MAX');
            $is_selected = false;
        }
        $list_type_home = [
            $this->l('House'),
            $this->l('Flat'), ];

        $list_type_floor = [
            $this->l('ground floor'),
            $this->l('1st floor'),
            $this->l('2th floor'),
            $this->l('3th floor'),
            $this->l('4th floor'),
            $this->l('5th floor'),
            $this->l('+5th floor'),
        ];
        if (Configuration::get('RC_LIVEMAP_API') && Configuration::get('RC_LIVEMAP_PID') && Configuration::get('RC_LIVEMAP_KEY')) {
            $key_build = 'JSBS' . Configuration::get('RC_LIVEMAP_API') . '$' . Configuration::get('RC_LIVEMAP_PID');
        } else {
            $key_build = false;
        }

        // building options
        if ((int) $result = RelaisColisInfoHome::alreadyExists((int) $cart->id, (int) $cart->id_customer)) {
            $relais_info_home = new RelaisColisInfoHome((int) $result);
        } else {
            $relais_info_home = new RelaisColisInfoHome();
        }
        $customer_option_choice = [];
        $mandatory_option = [];
        $options_home_list = RelaisColisHomeOptions::getRelaisColisHomeOptionsActive();
        if (is_array($options_home_list)) {
            foreach ($options_home_list as $row) {
                if (in_array($row['option'], $options_home_available) && $row['customer_choice']) {
                    $selected_choice = false;
                    if ($relais_info_home->{$row['option']}) {
                        $selected_choice = true;
                    }
                    $customer_option_choice[] = [
                        'option' => $row['option'],
                        'label' => $row['label'],
                        'cost' => $row['cost'],
                        'selected' => $selected_choice, ];
                } else {
                    if (in_array($row['option'], $options_home_available) && !$row['customer_choice']) {
                        $mandatory_option[] = [
                            'option' => $row['option'],
                            'label' => $row['label'],
                            'cost' => $row['cost'], ];
                    }
                }
            }
        }

        // top 24 h ?
        $top24 = 0;
        $top24_unselectable = false;
        $top_cost = 0;
        $top_selected = false;
        if ($options_top) {
            if (Configuration::get('RC_TOP')) {
                $top24 = true;
            } else {
                $top24_unselectable = true;
            }
            $top_cost = (float) Configuration::get('RC_TOP_COST');
            if ($relais_info_home->top) {
                $top_selected = true;
            }
        }

        $order_link = $link->getPageLink('order', true) . '?step=2';

        $protocol = 'http://';
        if (Configuration::get('PS_SSL_ENABLED')) {
            $protocol = 'https://';
        }
        $baseUrl = $protocol . Tools::getShopDomainSsl() . __PS_BASE_URI__;
        $digicode = $relais_info_home->digicode;
        $floor_delivery = $relais_info_home->floor_delivery;
        $type_home_selected = $relais_info_home->type_home;
        $elevator = $relais_info_home->elevator;
        $basic_home_plus_cost = $this->getHomePlusBasicCost();
        $useidens = '';
        if (Configuration::get('RC_USE_ID_ENS')) {
            $useidens = Configuration::get('RC_ENS_ID');
        }
        
        // MAJ WIDGET DEC 2024 - Pour affichage informations relai
        $id_relais_selected = RelaisColisInfo::alreadyExists((int) $cart->id, (int) $cart->id_customer);
        $relais_selected = new RelaisColisInfo((int) $id_relais_selected);
        // MAJ WIDGET DEC 2024 - Fin ajout
        
        $this->context->smarty->assign([

            // MAJ WIDGET DEC 2024 - informations relai
            'rel_selected_rel' => $relais_selected->rel,
            'rel_selected_name' => $relais_selected->rel_name,
            'rel_selected_adr' => $relais_selected->rel_adr,
            'rel_selected_cp' => $relais_selected->rel_cp,
            'rel_selected_vil' => $relais_selected->rel_vil,
            // MAJ WIDGET DEC 2024 - fin ajout

            'msg_order_carrier_relais' => $this->l('You must select relay point first.'),
            'relais_carrier_id' => $relais_carrier->id,
            'relais_carrier_max_id' => $relais_max_carrier->id,
            'home_carrier_id' => $home_carrier->id,
            'is_selected' => $is_selected,
            'is_selected_home' => $is_selected_home,
            'is_selected_home_plus' => $is_selected_home_plus,
            'street' => $street,
            'city' => $city,
            'postcode' => $postcode,
            'url_img' => $url_img,
            'redirect_link' => $redirect_link,
            'redirect_link_last_point' => $redirect_link_last_point,
            'have_selected_point' => $have_selected_point,
            'have_selected_last' => $have_selected_last,
            'relay_info' => $relay_info,
            'country_selected' => $country_selected,
            'key_build' => $key_build,
            'relais_colis_key' => Configuration::get('RC_LIVEMAP_KEY'), // The Livemap key is encrypted
            'must_unselected' => false,
            'max_unit_weight' => $max_unit_weight,
            'list_type_home' => $list_type_home,
            'list_type_floor' => $list_type_floor,
            'digicode' => $digicode,
            'floor_delivery' => $floor_delivery,
            'type_home_selected' => $type_home_selected,
            'elevator' => $elevator,
            'customer_option_choice' => $customer_option_choice,
            'mandatory_option' => $mandatory_option,
            'top24' => $top24,
            'baseUrl' => $baseUrl,
            'top_cost' => $top_cost,
            'top_selected' => $top_selected,
            'id_cart_home' => $this->context->cart->id,
            'id_customer_home' => $this->context->customer->id,
            'basic_home_plus_cost' => $basic_home_plus_cost,
            'top24_unselectable' => $top24_unselectable,
            'order_link' => $order_link,
            'useidens' => $useidens,
        ]);

        $limited_countries = $this->limited_countries;
        if (!RelaisColisApi::isFeatureActivated(RelaisColisApi::TYPE_BELGIUM)) {
            unset($limited_countries['belgique']);
        }
        $this->context->smarty->assign('limited_countries', $limited_countries);
        $this->context->smarty->assign('ids_relais_exclude', $tab_id_rc);
        if (!$cart_available) {
            $tab_id_rc[] = (int) Configuration::getGlobalValue('RELAISCOLIS_ID');
            $tab_id_rc[] = (int) Configuration::getGlobalValue('RELAISCOLIS_ID_MAX');
            $tab_id_rc[] = (int) Configuration::getGlobalValue('RELAISCOLIS_ID_HOME');
            $tab_id_rc[] = (int) Configuration::getGlobalValue('RELAISCOLIS_ID_HOME_PLUS');
            $this->context->smarty->assign('ids_relais_exclude', $tab_id_rc);

            return $this->display(__FILE__, 'relais_colis_error.tpl');
        }
        if (Configuration::get('RC_ACTIVATION_KEY') && Configuration::get('RC_IS_ACTIVE') && $this->active) {
            $url_img = 'modules/relaiscolis/views/img/';
            $this->context->smarty->assign('url_img', $url_img);
            $this->context->controller->addJS($this->_path . '/views/js/front.js');
            
            /* MAJ WIDGET DEC 2024 - Suppression appel carte Michelin
            if (Configuration::get('RC_LIVEMAP_API') && Configuration::get('RC_LIVEMAP_PID') && Configuration::get('RC_LIVEMAP_KEY')) {
                $key_build = 'JSBS' . Configuration::get('RC_LIVEMAP_API') . '$' . Configuration::get('RC_LIVEMAP_PID');
                $this->context->controller->addJS($this->_path . '/views/js/scripts_listerelais_Enseigne.js');
                $this->context->controller->addJS('https://secure-apijs.viamichelin.com/apijsv2/api/js?key=' . $key_build . '&lang=fra&protocol=https');
            }
            */

            $this->context->controller->addCSS($this->_path . '/views/css/front.css');
        }

        // We need to show an error message only if we are in Belgium and the TYPE_BELGIUM option is active (and if there is an error, of course).
        $need_to_show_message_if_error = $country_address->iso_code == 'BE' && RelaisColisApi::isFeatureActivated(RelaisColisApi::TYPE_BELGIUM);
        // We need to show an error message for weight issue in Belgium only if TYPE_HOME option is not active.
        $weight_issue_for_BE = $total_weight > self::MAX_WEIGHT_BE && !RelaisColisApi::isFeatureActivated(RelaisColisApi::TYPE_HOME);

        if ($need_to_show_message_if_error && ($is_too_high_for_BE || $weight_issue_for_BE)) {
            $message1 = $message2 = '';

            if ($is_too_high_for_BE) {
                $message1 .= $this->l('A least one of your product\'s height is larger than ') . self::MAX_HEIGHT_BE . 'cm.';
            }

            if ($weight_issue_for_BE) {
                $message2 .= $this->l('The weight of your products is superior than ') . self::MAX_WEIGHT_BE . 'kg.';
            }

            $this->context->smarty->assign('message1', $message1);
            $this->context->smarty->assign('message2', $message2);

            return $this->display(__FILE__, 'relais_colis_error_be.tpl');
        }

        $this->context->smarty->assign('RELAISCOLIS_ID', (int) Configuration::getGlobalValue('RELAISCOLIS_ID'));
        $this->context->smarty->assign('RELAISCOLIS_ID_MAX', (int) Configuration::getGlobalValue('RELAISCOLIS_ID_MAX'));
        $this->context->smarty->assign('rc_token', Configuration::get('RC_CRON_TOKEN'));

        return $this->display(__FILE__, 'relais_frame.tpl');
    }

    public function hookDisplayBeforeCarrier($params)
    {
        if ((int) Configuration::getGlobalValue('RELAISCOLIS_ID')) {
            $carrier = new Carrier((int) Configuration::getGlobalValue('RELAISCOLIS_ID'));
            if ($carrier->id) {
                if (RelaisColisApi::isFeatureActivated(RelaisColisApi::TYPE_MAX) || !RelaisColisApi::isFeatureActivated(RelaisColisApi::TYPE_DELIVERY)) {
                    // relais max activated dont need relais anymore
                    if ($carrier->active) {
                        $carrier->active = 0;
                        $carrier->save();
                    }
                }
            }
        }
        if ((int) Configuration::getGlobalValue('RELAISCOLIS_ID_MAX')) {
            $carrier = new Carrier((int) Configuration::getGlobalValue('RELAISCOLIS_ID_MAX'));
            if ($carrier->id) {
                if (!RelaisColisApi::isFeatureActivated(RelaisColisApi::TYPE_MAX)) {
                    // relais max activated dont need relais anymore
                    if ($carrier->active) {
                        $carrier->active = 0;
                        $carrier->save();
                    }
                }
            }
        }
        if ((int) Configuration::getGlobalValue('RELAISCOLIS_ID_HOME')) {
            $carrier = new Carrier((int) Configuration::getGlobalValue('RELAISCOLIS_ID_HOME'));
            if ($carrier->id) {
                if (!RelaisColisApi::isFeatureActivated(RelaisColisApi::TYPE_HOME)) {
                    if ($carrier->active) {
                        $carrier->active = 0;
                        $carrier->save();
                    }
                }
            }
        }
        if ((int) Configuration::getGlobalValue('RELAISCOLIS_ID_HOME_PLUS')) {
            $carrier = new Carrier((int) Configuration::getGlobalValue('RELAISCOLIS_ID_HOME_PLUS'));
            if ($carrier->id) {
                if (!RelaisColisApi::isFeatureActivated(RelaisColisApi::TYPE_HOME)) {
                    if ($carrier->active) {
                        $carrier->active = 0;
                        $carrier->save();
                    }
                }
            }
        }
    }

    public function isSameAddress($id_address, $id_cart, $id_customer, $id_order_invoice)
    {
        if (!$id_delivery_point = (int) RelaisColisInfo::alreadyExists($id_cart, $id_customer)) {
            return $id_address;
        }

        $delivery_point = new RelaisColisInfo((int) $id_delivery_point);
        // retrieve isocode 2
        $country_iso = 'FR';
        $delivery_country = $delivery_point->fcod_pays;
        // belgium RP for french customer
        if ($delivery_point->age_code == 'BE' && $delivery_point->fcod_pays == 'FRA') {
            $delivery_country = 'BEL';
        }
        foreach ($this->limited_countries as $country) {
            if ($country['iso3'] == $delivery_country) {
                $country_iso = $country['iso2'];
            }
        }
        // retrieving customer addresse invoice for at least a phone number
        $mobile = '0606060606';
        $phone = '';
        $address_invoice = new Address((int) $id_order_invoice);

        if ($address_invoice->phone_mobile) {
            $mobile = $address_invoice->phone_mobile;
        }
        if ($address_invoice->phone) {
            $phone = $address_invoice->phone;
        }

        $ps_address = new Address((int) $id_address);
        $new_address = new Address();
        $id_country = Country::getByIso($country_iso);
        $firstname = 'Relais Colis';
        $address1 = $ps_address->lastname . ' ' . $ps_address->firstname;

        if (
            $this->upper($ps_address->lastname) != $this->upper($this->formatName($delivery_point->rel_name))
            || $ps_address->id_country != $id_country
            || $this->upper($ps_address->firstname) != $this->upper($firstname)
            || $this->upper($ps_address->address1) != $this->upper($address1)
            || $this->upper($ps_address->address2) != $this->upper($delivery_point->rel_adr)
            || $this->upper($ps_address->postcode) != $this->upper($delivery_point->rel_cp)
            || $this->upper($ps_address->city) != $this->upper($delivery_point->rel_vil)
        ) {
            $new_address->id_customer = (int) $id_customer;
            $new_address->lastname = trim(Tools::substr($this->formatName($delivery_point->rel_name), 0, 32));
            $new_address->firstname = $firstname;
            $new_address->postcode = $delivery_point->rel_cp;
            $new_address->city = $delivery_point->rel_vil;
            $new_address->id_country = $id_country;
            $new_address->alias = 'Relais colis - ' . date('d-m-Y');
            $new_address->phone_mobile = $mobile;
            $new_address->phone = $phone;
            $new_address->active = 1;
            $new_address->deleted = 1;
            $new_address->address1 = $address1;
            $new_address->address2 = $delivery_point->rel_adr;
            $new_address->add();

            return (int) $new_address->id;
        }

        return (int) $ps_address->id;
    }

    public function upper($str_in)
    {
        return Tools::strtoupper(str_replace('-', ' ', Tools::link_rewrite($str_in)));
    }

    public function formatName($name)
    {
        return preg_replace('/[0-9!<>,;?=+()@#"°{}_$%:]/', '', Tools::stripslashes($name));
    }

    public function installBackOffice()
    {
        $id_lang_en = LanguageCore::getIdByIso('en');
        $id_lang_fr = LanguageCore::getIdByIso('fr');

        if (!Tab::getIdFromClassName('AdminManageRelaisColisMenu')) {
            $this->installModuleTab(
                'AdminManageRelaisColisMenu',
                [
                $id_lang_fr => 'Relais Colis',
                $id_lang_en => 'Relais Colis',
                ],
                0,
                true,
                1,
                'local_shipping'
            );
        }
        $id_rc_tab = Tab::getIdFromClassName('AdminManageRelaisColisMenu');

        if (!Tab::getIdFromClassName('AdminManageRelaisColisConfig')) {
            $this->installModuleTab(
                'AdminManageRelaisColisConfig',
                [
                    $id_lang_fr => 'Configuration',
                    $id_lang_en => 'Configuration',
                ],
                $id_rc_tab,
                true,
                0,
                false
            );
        }
        if (!Tab::getIdFromClassName('AdminManageRelaisColisHome')) {
            $this->installModuleTab(
                'AdminManageRelaisColisHome',
                [
                    $id_lang_fr => 'Configuration produits Relais Colis',
                    $id_lang_en => 'Relais Colis Products Configuration',
                ],
                $id_rc_tab,
                true,
                1,
                false
            );
        }
        if (!Tab::getIdFromClassName('AdminManageRelaisColisHomeOptions')) {
            $this->installModuleTab(
                'AdminManageRelaisColisHomeOptions',
                [
                    $id_lang_fr => 'Configuration prix options Relais Colis',
                    $id_lang_en => 'Relais Colis Options price Configuration',
                ],
                $id_rc_tab,
                true,
                2,
                false
            );
        }
        if (!Tab::getIdFromClassName('AdminManageRelaisColisOrderProduct')) {
            $this->installModuleTab(
                'AdminManageRelaisColisOrderProduct',
                [
                    $id_lang_fr => 'Liste des multicolis Relais Colis',
                    $id_lang_en => 'Relais Colis list multiple packages',
                ],
                $id_rc_tab,
                true,
                3,
                false
            );
        }
        if (!Tab::getIdFromClassName('AdminManageRelaisColis')) {
            $this->installModuleTab(
                'AdminManageRelaisColis',
                [
                    $id_lang_fr => 'Commandes Relais Colis',
                    $id_lang_en => 'Relais Colis Orders',
                ],
                $id_rc_tab,
                true,
                4,
                false
            );
        }
        if (!Tab::getIdFromClassName('AdminManageRelaisColisReturn')) {
            $this->installModuleTab(
                'AdminManageRelaisColisReturn',
                [
                    $id_lang_fr => 'Retour Relais Colis',
                    $id_lang_en => 'Relais Colis Returns',
                ],
                $id_rc_tab,
                true,
                5,
                false
            );
        }

        return true;
    }

    public function uninstallModuleTab($tabClass)
    {
        $idTab = Tab::getIdFromClassName($tabClass);
        if ($idTab != 0) {
            $tab = new Tab($idTab);
            $tab->delete();

            return true;
        }

        return false;
    }

    public function installModuleTab($tabClass, $tabName, $idTabParent, $active = true, $position)
    {
        $tab = new Tab();
        $tab->name = $tabName;
        $tab->class_name = $tabClass;
        $tab->module = $this->name;
        $tab->id_parent = (int) $idTabParent;
        $tab->active = (bool) $active;
        if ($position) {
            $tab->position = (int) $position;
        }

        if (!$tab->save()) {
            return false;
        }

        return true;
    }

    public function hasTopOptions($products_list)
    {
        $options_top = false;
        if (is_array($products_list)) {
            foreach ($products_list as $product) {
                $options_home_product = RelaisColisProduct::getProductOptions((int) $product['id_product']);
                if (is_array($options_home_product)) {
                    foreach ($options_home_product as $option) {
                        if ($option['top']) {
                            if ($this->isAvailableTop()) {
                                $options_top = true;
                            }
                        }
                    }
                }
            }
        }

        return $options_top;
    }

    public function hasOutStandardOptions($products_list)
    {
        $options_out = false;
        if (is_array($products_list)) {
            foreach ($products_list as $product) {
                $options_home_product = RelaisColisProduct::getProductOptions((int) $product['id_product']);
                if (is_array($options_home_product)) {
                    foreach ($options_home_product as $option) {
                        if ($option['non_standard']) {
                            $options_out = true;
                        }
                    }
                }
            }
        }

        return $options_out;
    }

    public function getHomePlusBasicCost()
    {
        if (Configuration::getGlobalValue('RELAISCOLIS_ID_HOME_PLUS')) {
            $carrier = new Carrier((int) Configuration::getGlobalValue('RELAISCOLIS_ID_HOME_PLUS'));
            $address = new Address((int) $this->context->cart->id_address_delivery);
            $id_zone = Address::getZoneById((int) $address->id);
            $products = $this->context->cart->getProducts();
            $additional_shipping_cost = 0;
            // Additional shipping cost on product
            foreach ($products as $product) {
                if (version_compare(_PS_VERSION_, '1.5', '<')) {
                    $additional_shipping_cost += (float) $product['additional_shipping_cost'] * $product['quantity'];
                } elseif (!$product['is_virtual']) {
                    $additional_shipping_cost += (float) $product['additional_shipping_cost'] * $product['quantity'];
                }
            }
            if ($carrier->shipping_handling) {
                return $this->getCostByShippingMethod($carrier, $id_zone) + (float) $additional_shipping_cost + (float) Configuration::get('PS_SHIPPING_HANDLING');
            } else {
                return $this->getCostByShippingMethod($carrier, $id_zone) + (float) $additional_shipping_cost;
            }
        }

        return false;
    }

    public function getCostByShippingMethod($carrier, $id_zone)
    {
        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            if (!is_object($this->context->cart)) {
                $this->context->cart = new Cart();
            }
        }

        if ($carrier->shipping_method) {
            if ($carrier->shipping_method == 1) {
                if ($carrier->getDeliveryPriceByWeight($this->context->cart->getTotalWeight(), $id_zone)) {
                    return $carrier->getDeliveryPriceByWeight($this->context->cart->getTotalWeight(), $id_zone);
                }
            }

            if ($carrier->shipping_method == 2) {
                if ($carrier->getDeliveryPriceByPrice($this->context->cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING), $id_zone, $this->context->cart->id_currency)) {
                    return $carrier->getDeliveryPriceByPrice($this->context->cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING), $id_zone, $this->context->cart->id_currency);
                }
            }
        } else {
            if (Configuration::get('PS_SHIPPING_METHOD')) {
                if ($carrier->getDeliveryPriceByWeight($this->context->cart->getTotalWeight(), $id_zone)) {
                    return $carrier->getDeliveryPriceByWeight($this->context->cart->getTotalWeight(), $id_zone);
                }
            }

            if (!Configuration::get('PS_SHIPPING_METHOD')) {
                if ($carrier->getDeliveryPriceByPrice($this->context->cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING), $id_zone, $this->context->cart->id_currency)) {
                    return $carrier->getDeliveryPriceByPrice($this->context->cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING), $id_zone, $this->context->cart->id_currency);
                }
            }
        }

        return false;
    }

    public function getOptionsProductCart($products_list)
    {
        $options_home_available = [];
        if (is_array($products_list)) {
            foreach ($products_list as $product) {
                $options_home_product = RelaisColisProduct::getProductOptions((int) $product['id_product']);
                if (is_array($options_home_product)) {
                    foreach ($options_home_product as $option) {
                        if (!in_array('schedule', $options_home_available) && $option['schedule']) {
                            $options_home_available[] = 'schedule';
                        }
                        if (!in_array('retrieve_old_equipment', $options_home_available) && $option['retrieve_old_equipment']) {
                            $options_home_available[] = 'retrieve_old_equipment';
                        }
                        if (!in_array('delivery_on_floor', $options_home_available) && $option['delivery_on_floor']) {
                            $options_home_available[] = 'delivery_on_floor';
                        }
                        if (!in_array('delivery_at_two', $options_home_available) && $option['delivery_at_two']) {
                            $options_home_available[] = 'delivery_at_two';
                        }
                        if (!in_array('turn_on_home_appliance', $options_home_available) && $option['turn_on_home_appliance']) {
                            $options_home_available[] = 'turn_on_home_appliance';
                        }
                        if (!in_array('mount_furniture', $options_home_available) && $option['mount_furniture']) {
                            $options_home_available[] = 'mount_furniture';
                        }
                        if (!in_array('non_standard', $options_home_available) && $option['non_standard']) {
                            $options_home_available[] = 'non_standard';
                        }
                        if (!in_array('unpacking', $options_home_available) && $option['unpacking']) {
                            $options_home_available[] = 'unpacking';
                        }
                        if (!in_array('evacuation_packaging', $options_home_available) && $option['evacuation_packaging']) {
                            $options_home_available[] = 'evacuation_packaging';
                        }
                        if (!in_array('recovery', $options_home_available) && $option['recovery']) {
                            $options_home_available[] = 'recovery';
                        }
                        if (!in_array('delivery_desired_room', $options_home_available) && $option['delivery_desired_room']) {
                            $options_home_available[] = 'delivery_desired_room';
                        }
                        if (!in_array('recover_old_bedding', $options_home_available) && $option['recover_old_bedding']) {
                            $options_home_available[] = 'recover_old_bedding';
                        }
                        if (!in_array('assembly', $options_home_available) && $option['assembly']) {
                            $options_home_available[] = 'assembly';
                        }
                    }
                }
            }
        }

        return $options_home_available;
    }

    public function isAvailableTop()
    {
        $hour = date('H:i');
        $time = explode(':', $hour);
        $current_hour = $time[0];

        if ($available = Configuration::get('RC_TOP_HOUR')) {
            $available = explode(':', $available);
            if (isset($available[0])) {
                $available_hour = (int) $available[0];
            }
            if (isset($available[0])) {
                $available_min = (int) $available[0];
            }
            if ($available_hour && $available_min) {
                if ((int) $available_hour > (int) $current_hour) {
                    return true;
                }
                if ((int) $available_hour == (int) $current_hour && (int) $available_min >= (int) $current_hour) {
                    return true;
                }
            }
        }

        return false;
    }

    public function hasSensibleOptions($products_list)
    {
        $options_sensible = false;
        if (is_array($products_list)) {
            foreach ($products_list as $product) {
                $options_home_product = RelaisColisProduct::getProductOptions((int) $product['id_product']);
                if (is_array($options_home_product)) {
                    foreach ($options_home_product as $option) {
                        if ($option['sensible']) {
                            $options_sensible = true;
                        }
                    }
                }
            }
        }

        return $options_sensible;
    }

    // MAJ WIDGET DEC 2024 - Function pour enregistrer numéro suivi dans table order_carrier
    public function updateOrderCarrierTrackingNumber($tracking_number, $id_order)
    {
        $sql = 'UPDATE ' . _DB_PREFIX_ . 'order_carrier
                SET tracking_number = "' . pSQL($tracking_number) . '"
                WHERE id_order = ' . (int)$id_order;

        return Db::getInstance()->execute($sql);
    }
}