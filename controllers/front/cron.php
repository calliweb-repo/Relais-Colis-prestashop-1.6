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
require_once 'classes/RelaisColisApi.php';

if (!defined('_PS_VERSION_')) { exit; }

class RelaiscolisCronModuleFrontController extends ModuleFrontController
{
    public function __construct()
    {
        parent::__construct();
        $this->ssl = true;
    }

    public function display()
    {
        /* Verif CLI Call */
        if (php_sapi_name() !== 'cli') {
            echo 'Forbidden call.';
            exit;
        }

        /* Verif RC Token */
        if (Configuration::get('RC_CRON_TOKEN') == false || Tools::getValue('token') != Configuration::get('RC_CRON_TOKEN')) {
            echo 'Invalid Token.';
            exit;
        }

        if (Configuration::get('RC_CRON_ACTIVE')) {
            $context = Context::getContext();
            $shop = $context->shop;

            if (Shop::isFeatureActive()) {
                $shop_list = Shop::getShops();
                if (is_array($shop_list)) {
                    foreach ($shop_list as $shop) {
                        $activation_key = Configuration::get('RC_ACTIVATION_KEY', null, null, (int) $shop['id_shop']);
                        if ($activation_key) {
                            RelaisColisApi::processGetEvtsData($activation_key);
                        }
                    }
                } else {
                    RelaisColisApi::processGetEvtsData(Configuration::get('RC_ACTIVATION_KEY'));
                }
            } else {
                RelaisColisApi::processGetEvtsData(Configuration::get('RC_ACTIVATION_KEY'));
            }
        }
    }
}
