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

if (!defined('_PS_VERSION_')) { exit; }

class RelaisColisResetCarrier
{
    /**
     * Delete RC Carrier
     *
     * @param Relaiscolis $module_instance
     *
     * @return bool
     */
    public static function deleteRelaiscolisCarrier(Relaiscolis $module_instance)
    {
        $relais_carrier = new Carrier(Configuration::getGlobalValue('RELAISCOLIS_ID'));
        if ((int) $relais_carrier->id) {
            // If relais colis carrier is default set other one as default
            if (Configuration::get('PS_CARRIER_DEFAULT') == (int) $relais_carrier->id) {
                $carriers_d = Carrier::getCarriers(Context::getContext()->language->id);
                foreach ($carriers_d as $carrier_d) {
                    if ($carrier_d['active'] && !$carrier_d['deleted'] && ($carrier_d['name'] != $module_instance->config['name'])) {
                        Configuration::updateValue('PS_CARRIER_DEFAULT', $carrier_d['id_carrier']);
                    }
                }
            }
            // Save old carrier id
            Configuration::updateGlobalValue('RC_CARRIER_ID_HIST', Configuration::getGlobalValue('RC_CARRIER_ID_HIST') . '|' . (int) $relais_carrier->id);
            $relais_carrier->deleted = 1;
            if (!$relais_carrier->update()) {
                return false;
            }
        }
    }

    /**
     * Delete RC MAX Carrier
     *
     * @param Relaiscolis $module_instance
     *
     * @return bool
     */
    public static function deleteRelaiscolismaxCarrier(Relaiscolis $module_instance)
    {
        $relais_carrier = new Carrier(Configuration::getGlobalValue('RELAISCOLIS_ID_MAX'));

        if ((int) $relais_carrier->id) {
            // If relais colis carrier is default set other one as default
            if (Configuration::get('PS_CARRIER_DEFAULT') == (int) $relais_carrier->id) {
                $carriers_d = Carrier::getCarriers(Context::getContext()->language->id);
                foreach ($carriers_d as $carrier_d) {
                    if ($carrier_d['active'] && !$carrier_d['deleted'] && ($carrier_d['name'] != $module_instance->config['name'])) {
                        Configuration::updateValue('PS_CARRIER_DEFAULT', $carrier_d['id_carrier']);
                    }
                }
            }
            // Save old carrier id
            Configuration::updateGlobalValue('RC_CARRIER_ID_HIST', Configuration::getGlobalValue('RC_CARRIER_ID_HIST') . '|' . (int) $relais_carrier->id);
            $relais_carrier->deleted = 1;
            if (!$relais_carrier->update()) {
                return false;
            }
        }
    }

    /**
     * Delete RC Home Carrier
     *
     * @param Relaiscolis $module_instance
     *
     * @return bool
     */
    public static function deleteRelaiscolishomeCarrier(Relaiscolis $module_instance)
    {
        $relais_carrier = new Carrier(Configuration::getGlobalValue('RELAISCOLIS_ID_HOME'));

        if ((int) $relais_carrier->id) {
            // If relais colis carrier is default set other one as default
            if (Configuration::get('PS_CARRIER_DEFAULT') == (int) $relais_carrier->id) {
                $carriers_d = Carrier::getCarriers(Context::getContext()->language->id);
                foreach ($carriers_d as $carrier_d) {
                    if ($carrier_d['active'] && !$carrier_d['deleted'] && ($carrier_d['name'] != $module_instance->config['name'])) {
                        Configuration::updateValue('PS_CARRIER_DEFAULT', $carrier_d['id_carrier']);
                    }
                }
            }
            // Save old carrier id
            Configuration::updateGlobalValue('RC_CARRIER_ID_HIST', Configuration::getGlobalValue('RC_CARRIER_ID_HIST') . '|' . (int) $relais_carrier->id);
            $relais_carrier->deleted = 1;
            if (!$relais_carrier->update()) {
                return false;
            }
        }
    }

    /**
     * Delete RC HOME + Carrier
     *
     * @param Relaiscolis $module_instance
     *
     * @return bool
     */
    public static function deleteRelaiscolishomeplusCarrier(Relaiscolis $module_instance)
    {
        $relais_carrier = new Carrier(Configuration::getGlobalValue('RELAISCOLIS_ID_HOME_PLUS'));
        if ((int) $relais_carrier->id) {
            // If relais colis carrier is default set other one as default
            if (Configuration::get('PS_CARRIER_DEFAULT') == (int) $relais_carrier->id) {
                $carriers_d = Carrier::getCarriers(Context::getContext()->language->id);
                foreach ($carriers_d as $carrier_d) {
                    if ($carrier_d['active'] && !$carrier_d['deleted'] && ($carrier_d['name'] != $module_instance->config['name'])) {
                        Configuration::updateValue('PS_CARRIER_DEFAULT', $carrier_d['id_carrier']);
                    }
                }
            }
            // Save old carrier id
            Configuration::updateGlobalValue('RC_CARRIER_ID_HIST', Configuration::getGlobalValue('RC_CARRIER_ID_HIST') . '|' . (int) $relais_carrier->id);
            $relais_carrier->deleted = 1;
            if (!$relais_carrier->update()) {
                return false;
            }
        }
    }

    /**
     * Reset either RC or RC MAX based on active options Carrier (Delete and recreate)
     *
     * @param Relaiscolis $module_instance
     *
     * @return bool
     */
    public static function resetRC(Relaiscolis $module_instance)
    {
        if (RelaisColisApi::isFeatureActivated(RelaisColisApi::TYPE_DELIVERY) && !RelaisColisApi::isFeatureActivated(RelaisColisApi::TYPE_MAX)) {
            RelaisColisResetCarrier::deleteRelaiscolisCarrier($module_instance);
            $carrier = $module_instance->addCarrier();
            if ($carrier !== false) {
                $module_instance->addZones($carrier);
                $module_instance->addGroups($carrier);
                $module_instance->addRanges($carrier);

                return true;
            } else {
                return false;
            }
        } elseif (RelaisColisApi::isFeatureActivated(RelaisColisApi::TYPE_MAX)) {
            RelaisColisResetCarrier::deleteRelaiscolismaxCarrier($module_instance);
            $carrier = $module_instance->addCarrierMax();
            if ($carrier !== false) {
                $module_instance->addZones($carrier);
                $module_instance->addGroups($carrier);
                $module_instance->addRangesMax($carrier);

                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Reset RC Carrier Home (Delete and recreate)
     *
     * @param Relaiscolis $module_instance
     *
     * @return bool
     */
    public static function resetRCHome(Relaiscolis $module_instance)
    {
        RelaisColisResetCarrier::deleteRelaiscolishomeCarrier($module_instance);
        $carrier = $module_instance->addCarrierHome();
        if ($carrier !== false) {
            $module_instance->addZones($carrier);
            $module_instance->addGroups($carrier);
            $module_instance->addRangesHome($carrier);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Reset RC Carrier Home+ (Delete and recreate)
     *
     * @param Relaiscolis $module_instance
     * @param Relaiscolisplus $module_instance_plus
     *
     * @return bool
     */
    public static function resetRCHomePlus(Relaiscolis $module_instance)
    {
        RelaisColisResetCarrier::deleteRelaiscolishomeplusCarrier($module_instance);
        $carrier = $module_instance->addCarrierHomePlus();
        if ($carrier !== false) {
            $module_instance->addZones($carrier);
            $module_instance->addGroups($carrier);
            $module_instance->addRangesHome($carrier);

            return true;
        } else {
            return false;
        }
    }
}
