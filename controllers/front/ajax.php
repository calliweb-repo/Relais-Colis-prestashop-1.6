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
require_once 'classes/RelaisColisInfoHome.php';

if (!defined('_PS_VERSION_')) { exit; }

class RelaiscolisAjaxModuleFrontController extends ModuleFrontController
{
    public function __construct()
    {
        parent::__construct();
        $this->ssl = true;
    }

    public function displayAjax()
    {
        /* Verif CLI Call */
        if (php_sapi_name() === 'cli') {
            echo 'Forbidden call.';
            exit;
        }

        /* Verif RC Token */
        if (
            Tools::getValue('rc_token') == false
            || Configuration::get('RC_CRON_TOKEN') == false
            || Tools::getValue('rc_token') != Configuration::get('RC_CRON_TOKEN')
        ) {
            echo 'Invalid Token.';
            exit;
        }

        $result = [];

        if (!Tools::getValue('cart') || !Tools::getValue('customer') || !Tools::getValue('option')) {
            $result['answer'] = false;
        } else {
            if ((int) $result = RelaisColisInfoHome::alreadyExists((int) Tools::getValue('cart'), (int) Tools::getValue('customer'))) {
                $relais_info_home = new RelaisColisInfoHome((int) $result);
            } else {
                $relais_info_home = new RelaisColisInfoHome();
                $relais_info_home->id_cart = (int) Tools::getValue('cart');
                $relais_info_home->id_customer = (int) Tools::getValue('customer');
            }
            if (Tools::getValue('option') == 'top24') {
                $relais_info_home->top = (int) Tools::getValue('selected');
            } else {
                $relais_info_home->{Tools::getValue('option')} = (int) Tools::getValue('selected');
            }
            $relais_info_home->save();
        }

        echo json_encode($result);
        exit;
    }
}
