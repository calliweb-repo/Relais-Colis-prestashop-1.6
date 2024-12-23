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
require_once _PS_MODULE_DIR_ . 'relaiscolis/classes/RelaisColisInfo.php';

if (!defined('_PS_VERSION_')) { exit; }

class RelaiscolisRedirectModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public $display_header = false;
    public $display_footer = false;

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        $id_cart = $this->context->cart->id;
        $id_customer = $this->context->customer->id;

        // MAJ WIDGET DEC 2024 - Suppression relaiscolis quand client souhaite changer
        if (Tools::getValue('action') === 'delete_relais') {
            $id_relais_colis_info = RelaisColisInfo::alreadyExists($this->context->cart->id, $this->context->customer->id);
            if ($id_relais_colis_info) {
                $relais_colis_info = new RelaisColisInfo($id_relais_colis_info);
                $relais_colis_info->delete();
            }

            // redirection
            $this->context->cart->id_carrier = (int) Configuration::getGlobalValue('RELAISCOLIS_ID');
            $link = new Link();
            $redirect = $link->getPageLink(Configuration::get('PS_ORDER_PROCESS_TYPE') ? 'order-opc' : 'order', true);
            Tools::redirect($redirect . '?step=2');
        }
        // MAJ WIDGET DEC 2024 - fin suppression

        if (Tools::getValue('id_last_point')) {
            $id_last_point = (int) Tools::getValue('id_last_point');
            $relais_colis_info_used = new RelaisColisInfo((int) $id_last_point);
            if ($relais_colis_info_used) {
                $id_relais_colis_info = RelaisColisInfo::alreadyExists($id_cart, $id_customer);
                if ((int) $id_relais_colis_info) {
                    $relais_colis_info = new RelaisColisInfo((int) $id_relais_colis_info);
                } else {
                    $relais_colis_info = new RelaisColisInfo();
                }
                $relais_colis_info->id_cart = $id_cart;
                $relais_colis_info->id_customer = $id_customer;
                $relais_colis_info->rel = $relais_colis_info_used->rel;
                $relais_colis_info->rel_name = $relais_colis_info_used->rel_name;
                $relais_colis_info->rel_adr = $relais_colis_info_used->rel_adr;
                $relais_colis_info->rel_cp = $relais_colis_info_used->rel_cp;
                $relais_colis_info->rel_vil = $relais_colis_info_used->rel_vil;
                $relais_colis_info->pseudo_rvc = $relais_colis_info_used->pseudo_rvc;
                $relais_colis_info->frc_max = $relais_colis_info_used->frc_max;
                $relais_colis_info->floc_rel = $relais_colis_info_used->floc_rel;
                $relais_colis_info->fcod_pays = $relais_colis_info_used->fcod_pays;
                $relais_colis_info->type_liv = $relais_colis_info_used->type_liv;
                $relais_colis_info->age_code = $relais_colis_info_used->age_code;
                $relais_colis_info->age_nom = $relais_colis_info_used->age_nom;
                $relais_colis_info->age_adr = $relais_colis_info_used->age_adr;
                $relais_colis_info->age_vil = $relais_colis_info_used->age_vil;
                $relais_colis_info->age_cp = $relais_colis_info_used->age_cp;
                $relais_colis_info->ouvlun = $relais_colis_info_used->ouvlun;
                $relais_colis_info->ouvmar = $relais_colis_info_used->ouvmar;
                $relais_colis_info->ouvmer = $relais_colis_info_used->ouvmer;
                $relais_colis_info->ouvjeu = $relais_colis_info_used->ouvjeu;
                $relais_colis_info->ouvven = $relais_colis_info_used->ouvven;
                $relais_colis_info->ouvsam = $relais_colis_info_used->ouvsam;
                $relais_colis_info->ouvdim = $relais_colis_info_used->ouvdim;
                $relais_colis_info->selected_date = date('Y-m-d');
                $relais_colis_info->save();
            }
        } else {
            $id_relais_colis_info = RelaisColisInfo::alreadyExists($id_cart, $id_customer);
            if ((int) $id_relais_colis_info) {
                $relais_colis_info = new RelaisColisInfo((int) $id_relais_colis_info);
            } else {
                $relais_colis_info = new RelaisColisInfo();
            }
            $relais_colis_info->id_cart = $id_cart;
            $relais_colis_info->id_customer = $id_customer;
            if (Tools::getValue('rel')) {
                $relais_colis_info->rel = trim(Tools::getValue('rel'));
            }
            /* if (Tools::getValue('nom')) {
                $relais_colis_info->rel_name = trim(utf8_encode(Tools::getValue('nom')));
            } */
            if (Tools::getValue('rel_name')) {
                $relais_colis_info->rel_name = trim(utf8_encode(Tools::getValue('rel_name')));
            }
            /* if (Tools::getValue('reladr')) {
                $relais_colis_info->rel_adr = trim(utf8_encode(Tools::getValue('reladr')));
            } */
            if (Tools::getValue('rel_adr')) {
                $relais_colis_info->rel_adr = trim(utf8_encode(Tools::getValue('rel_adr')));
            }
            /* if (Tools::getValue('relcp')) {
                $relais_colis_info->rel_cp = trim(Tools::getValue('relcp'));
            } */
            if (Tools::getValue('rel_cp')) {
                $relais_colis_info->rel_cp = trim(Tools::getValue('rel_cp'));
            }
            /* if (Tools::getValue('relvil')) {
                $relais_colis_info->rel_vil = trim(utf8_encode(Tools::getValue('relvil')));
            } */
            if (Tools::getValue('rel_vil')) {
                $relais_colis_info->rel_vil = trim(utf8_encode(Tools::getValue('rel_vil')));
            }
            /* if (Tools::getValue('PseudoRvc')) {
                $relais_colis_info->pseudo_rvc = trim(Tools::getValue('PseudoRvc'));
            } */
            if (Tools::getValue('pseudo_rvc')) {
                $relais_colis_info->pseudo_rvc = trim(Tools::getValue('pseudo_rvc'));
            }
            /* if (Tools::getValue('frcmax')) {
                $relais_colis_info->frc_max = trim(Tools::getValue('frcmax'));
            } */
            if (Tools::getValue('frc_max')) {
                $relais_colis_info->frc_max = trim(Tools::getValue('frc_max'));
            }
            /* if (Tools::getValue('flocrel')) {
                $relais_colis_info->floc_rel = trim(Tools::getValue('flocrel'));
            } */
            if (Tools::getValue('floc_rel')) {
                $relais_colis_info->floc_rel = trim(Tools::getValue('floc_rel'));
            }
            /* if (Tools::getValue('fcodpays')) {
                $relais_colis_info->fcod_pays = trim(utf8_encode(Tools::getValue('fcodpays')));
            } */
            if (Tools::getValue('fcod_pays')) {
                $relais_colis_info->fcod_pays = trim(utf8_encode(Tools::getValue('fcod_pays')));
            }
            /* if (Tools::getValue('TypeLiv')) {
                $relais_colis_info->type_liv = trim(Tools::getValue('TypeLiv'));
            } */
            if (Tools::getValue('type_liv')) {
                $relais_colis_info->type_liv = trim(Tools::getValue('type_liv'));
            }
            if (Tools::getValue('age_code')) {
                $relais_colis_info->age_code = trim(Tools::getValue('age_code'));
            }
            if (Tools::getValue('age_nom')) {
                $relais_colis_info->age_nom = trim(Tools::getValue('age_nom'));
            }
            if (Tools::getValue('age_adr')) {
                $relais_colis_info->age_adr = trim(Tools::getValue('age_adr'));
            }
            if (Tools::getValue('age_vil')) {
                $relais_colis_info->age_vil = trim(Tools::getValue('age_vil'));
            }
            if (Tools::getValue('age_cp')) {
                $relais_colis_info->age_cp = trim(Tools::getValue('age_cp'));
            }
            /* if (Tools::getValue('OuvLun')) {
                $relais_colis_info->ouvlun = trim(Tools::getValue('OuvLun'));
            } */
            if (Tools::getValue('ouvlun')) {
                $relais_colis_info->ouvlun = trim(Tools::getValue('ouvlun'));
            }
            /* if (Tools::getValue('OuvMar')) {
                $relais_colis_info->ouvmar = trim(Tools::getValue('OuvMar'));
            } */
            if (Tools::getValue('ouvmar')) {
                $relais_colis_info->ouvmar = trim(Tools::getValue('ouvmar'));
            }
            /* if (Tools::getValue('OuvMer')) {
                $relais_colis_info->ouvmer = trim(Tools::getValue('OuvMer'));
            } */
            if (Tools::getValue('ouvmer')) {
                $relais_colis_info->ouvmer = trim(Tools::getValue('ouvmer'));
            }
            /* if (Tools::getValue('OuvJeu')) {
                $relais_colis_info->ouvjeu = trim(Tools::getValue('OuvJeu'));
            } */
            if (Tools::getValue('ouvjeu')) {
                $relais_colis_info->ouvjeu = trim(Tools::getValue('ouvjeu'));
            }
            /* if (Tools::getValue('OuvVen')) {
                $relais_colis_info->ouvven = trim(Tools::getValue('OuvVen'));
            } */
            if (Tools::getValue('ouvven')) {
                $relais_colis_info->ouvven = trim(Tools::getValue('ouvven'));
            }
            /* if (Tools::getValue('OuvSam')) {
                $relais_colis_info->ouvsam = trim(Tools::getValue('OuvSam'));
            } */
            if (Tools::getValue('ouvsam')) {
                $relais_colis_info->ouvsam = trim(Tools::getValue('ouvsam'));
            }
            /* if (Tools::getValue('OuvDim')) {
                $relais_colis_info->ouvdim = trim(Tools::getValue('OuvDim'));
            } */
            if (Tools::getValue('ouvdim')) {
                $relais_colis_info->ouvdim = trim(Tools::getValue('ouvdim'));
            }
            $relais_colis_info->selected_date = date('Y-m-d');
            $relais_colis_info->save();
        }
        $this->context->cart->id_carrier = (int) Configuration::getGlobalValue('RELAISCOLIS_ID');
        $link = new Link();
        $redirect = $link->getPageLink(Configuration::get('PS_ORDER_PROCESS_TYPE') ? 'order-opc' : 'order', true);
        Tools::redirect($redirect . '?step=2');
    }
}
