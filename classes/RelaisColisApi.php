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

class RelaisColisApi
{
    const TRACKING_URL = 'https://service.relaiscolis.com/wssuivicoliscritere/PageSuivi.aspx?Ref=@';
    const TYPE_DELIVERY = 'rc_delivery';
    const TYPE_MAX = 'rc_max';
    const TYPE_HOME = 'home_delivery';
    const TYPE_RETURN = 'return';
    const TYPE_C2C = 'rc_c2c';
    const TYPE_DRIVE = 'drive';
    const TYPE_SCHEDULE = 'schedule';
    const TYPE_BELGIUM = 'belgium';
    const INSEE_DEFAUT = '0';
    const INSEE_HOME = '0';
    const PRODUCT_FAMILY = '08';
    const DELIVERY_TYPE_DEFAULT = '00';
    const DELIVERY_TYPE_MAX = '08';
    const ACTIVITY_CODE = '05';
    const ACTIVITY_CODE_HOME = '08';
    const ACTIVITY_CODE_DRIVE = '07';
    const DELIVERY_TYPE_PAYMENT_DEFAULT = '3';
    const ORDER_TYPE_DEFAULT = '1';
    const ORDER_TYPE_SUB_DEFAULT = '1';
    const SENSITIVE_DEFAULT = '0';
    const PICKING_DEFAULT = '0';
    const HOME_RETREIVE_TYPE = '2';
    const ENS_ID_C2C = 'CC';
    const MAX_WEIGHT_RC = 20000;

    protected static function is_multi($array)
    {
        return count(array_filter($array, 'is_array')) > 0;
    }

    /**
     * Call Relais Colis REST Webservice and get xml response
     *
     * @param string $entity_method : "entity/method" of  the remote WS
     * @param array $post_array :
     *
     * @return SimpleXMLElement
     *
     * @throws PrestaShopException
     */
    protected static function rcWsXmlCall($entity_method, $post_array, $is_zip = false)
    {
        if (!($activation_key = Configuration::get('RC_ACTIVATION_KEY'))) {
            throw new PrestaShopModuleException('No Activation Key');
        }

        $rc_token_hash = Configuration::get('RC_TOKEN_HASH');
        switch ($entity_method) {
            case 'package/getPackagesPrice':
                $post_array['hash_token'] = $rc_token_hash;
                break;

            default:
                if ($rc_token_hash && !empty($rc_token_hash)) {
                    if (self::is_multi($post_array)) {
                        foreach ($post_array as &$pa) {
                            $pa['hash_token'] = $rc_token_hash;
                        }
                    } else {
                        $post_array['hash_token'] = $rc_token_hash;
                    }
                }
                break;
        }

        $curl = curl_init(Configuration::get('RC_REST_URL') . 'api/' . $entity_method);

        curl_setopt_array(
            $curl,
            [
                CURLOPT_USERPWD => $activation_key . ':' . $activation_key,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($post_array),
                CURLOPT_SSL_VERIFYPEER => false,
            ]
        );

        $curl_response = curl_exec($curl);
        
        if ($is_zip) {
            return $curl_response;
        }
        $http_response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($curl_response === false) {
            $curlError = curl_error($curl);
            curl_close($curl);

            return false;
        }
        curl_close($curl);

        // Check HTTP response code
        switch ($http_response_code) {
            case 200:
                // OK, continue
                break;
            case 401:
                return false;
            case 404:
                return false;
            default:
                return false;
        }

        $xml = simplexml_load_string($curl_response, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS);

        if ($xml === false) {
            throw new PrestaShopModuleException('Incorrect data received for ' . $entity_method);
        }

        return $xml;
    }

    /**
     * Convert to PHP boolean from xml string :
     * "1", "true", "on", "yes" => true
     * other => false
     *
     * @param mixed $val
     *
     * @return bool
     */
    protected static function xmlBool($val)
    {
        return is_string($val) ? filter_var($val, FILTER_VALIDATE_BOOLEAN) : (bool) $val;
    }

    /**
     * Get the configurations values of the account and update them in PS
     *
     * @param string $activation_key
     * @param string $version
     *
     * @return mixed false if there is no activation key, String message if the account is not active or true if success
     */
    public static function processConfigurationAccount($activation_key = '', $version = '')
    {
        if (!$activation_key) {
            return false;
        }

        if (!$version) {
            $version = Configuration::get('RC_MODULE_VERSION');
        }

        $rcModule = Module::getInstanceByName('relaiscolis');

        $xml = self::rcWsXmlCall(
            'enseigne/getConfiguration',
            [
                'activationKey' => Configuration::get('RC_ACTIVATION_KEY'),
                'moduleName' => 'relais colis',
                'moduleVersion' => $version,
                'cmsName' => 'Prestashop',
                'cmsVersion' => _PS_VERSION_,
            ]
        );

        if (!$xml) {
            return false;
        }

        if (!self::xmlBool($xml->active)) {
            Configuration::updateValue('RC_IS_ACTIVE', 0);

            return $rcModule->l('Your account is inactif', 'relaiscolisapi');
        }

        Configuration::updateValue('RC_TOKEN_ACTIVE', 0);
        if (!empty(Configuration::get('RC_TOKEN_HASH'))) {
            if (!self::processTokenBalance()) {
                return $rcModule->l('Token balance call impossible, please check your Token Hash available in your account https://relaiscolis.com/', 'relaiscolisapi');
            }
            Configuration::updateValue('RC_TOKEN_ACTIVE', 1);
        }

        $option_value = '';

        if (isset($xml->options->entry)) {
            foreach ($xml->options->entry as $option) {
                $option_value .= $option->value . ';';
            }
        }
        if (!self::xmlBool($xml->useidens)) {
            Configuration::updateValue('RC_USE_ID_ENS', 0);
        } else {
            if ($xml->useidens == 'true') {
                Configuration::updateValue('RC_USE_ID_ENS', 1);
            } else {
                Configuration::updateValue('RC_USE_ID_ENS', 0);
            }
        }

        Configuration::updateValue('RC_IS_ACTIVE', 1);
        Configuration::updateValue('RC_OPTIONS', $option_value);
        Configuration::updateValue('RC_ENS_ID', (string) $xml->ens_id);
        Configuration::updateValue('RC_ENS_ID_LIGHT', (string) $xml->ens_id_light);
        Configuration::updateValue('RC_LIVEMAP_API', (string) $xml->livemapping_api);
        Configuration::updateValue('RC_LIVEMAP_PID', (string) $xml->livemapping_pid);
        Configuration::updateValue('RC_LIVEMAP_KEY', (string) $xml->livemapping_key);

        // update carriers status in PS
        self::setCarrierState();

        return true;
    }

    /**
     * Call Relais Colis REST Webservice and get xml response as zip file
     *
     * @param string $activation_key
     *
     * @return bool false if there is no activation key, true if success
     */
    public static function processGetEvts($activation_key = '')
    {
        if (!$activation_key) {
            return false;
        }

        $xml = self::rcWsXmlCall(
            'enseigne/getEvenements',
            [
                'activationKey' => Configuration::get('RC_ACTIVATION_KEY'),
            ],
            true
        );

        $response = json_decode($xml);
        $file = base64_decode($response->file);

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename=' . $response->filename);
        header('Content-Length: ' . Tools::strlen($file));
        echo $file;
        exit;
    }

    /**
     * Call Relais Colis REST Webservice and update the Orders states
     *
     * @param string $activation_key
     *
     * @return bool false if there is no activation key, true if success
     */
    public static function processGetEvtsData($activation_key = '')
    {
        if (!$activation_key) {
            return false;
        }

        $xml = self::rcWsXmlCall(
            'package/getDataEvts',
            [
                'activationKey' => $activation_key,
            ]
        );

        if (isset($xml->entry)) {
            $array = self::xml2array($xml);

            // manage multiple entry
            if (is_array($array['entry'])) {
                foreach ($array['entry'] as $orders) {
                    self::updateOrderState($orders);
                }
            } else {
                // single entry
                self::updateOrderState($array['entry']);
            }
        }

        return true;
    }

    /**
     * Update the state of an Order
     *
     * @param array $data the order
     */
    public static function updateOrderState($data)
    {
        $extract_number = Tools::substr($data, 1, 14);

        // Only the first package of an order modify the state
        if (Tools::substr($extract_number, 13, 2) == '01') {
            $evts_code = Tools::substr($data, 15, 3);
            $evts_justif = Tools::substr($data, 26, 3);
            $evtsDate = Tools::substr($data, 46, 4) . '-' . Tools::substr($data, 50, 2) . '-' . Tools::substr($data, 52, 2) . ' ' . Tools::substr($data, 54, 2) . ':' . Tools::substr($data, 56, 2) . ':00';
            $id_order = RelaisColisOrder::getRelaisColisOrderIdWithExtractNumber($extract_number);

            if ((int) $id_order) {
                $order = new Order((int) $id_order);
                $id_order_state = RelaisColisEvts::getIdstate($evts_code, $evts_justif);
                if ((int) $id_order_state && ((int) $id_order_state != (int) $order->current_state)) {
                    $history = new OrderHistory();
                    $history->id_order = (int) $id_order;
                    $history->id_employee = 1;
                    $history->changeIdOrderState($id_order_state, (int) $id_order);
                    $history->date_add = $evtsDate; // date('Y-m-d H:i:s');
                    $history->addWithemail(false);
                }
            }
        }
    }

    /**
     * Check if the Carriers are still active and/or deleted and update them
     *
     * @return bool
     */
    public static function setCarrierState()
    {
        if ((int) Configuration::getGlobalValue('RELAISCOLIS_ID')) {
            $carrier = new Carrier((int) Configuration::getGlobalValue('RELAISCOLIS_ID'));
            $carrier->deleted = 0;
            if (!self::isFeatureActivated(self::TYPE_DELIVERY)) {
                $carrier->active = 0;
                $carrier->deleted = 1;
            }
            $carrier->save();
        }
        if ((int) Configuration::getGlobalValue('RELAISCOLIS_ID_MAX')) {
            $carrier_max = new Carrier((int) Configuration::getGlobalValue('RELAISCOLIS_ID_MAX'));
            $carrier_max->active = 0;
            $carrier_max->deleted = 1;
            if (self::isFeatureActivated(self::TYPE_MAX)) {
                $carrier_max->active = 1;
                $carrier_max->deleted = 0;

                // if relais max activated, we don't need relais anymore
                $carrier->active = 0;
                $carrier->deleted = 1;
                $carrier->save();
            }
            $carrier_max->save();
        }
        if ((int) Configuration::getGlobalValue('RELAISCOLIS_ID_HOME')) {
            $carrier = new Carrier((int) Configuration::getGlobalValue('RELAISCOLIS_ID_HOME'));
            if (!self::isFeatureActivated(self::TYPE_HOME)) {
                $carrier->active = 0;
            }
            $carrier->save();
        }
        if ((int) Configuration::getGlobalValue('RELAISCOLIS_ID_HOME_PLUS')) {
            $carrier = new Carrier((int) Configuration::getGlobalValue('RELAISCOLIS_ID_HOME_PLUS'));
            if (!self::isFeatureActivated(self::TYPE_HOME)) {
                $carrier->active = 0;
            }
            $carrier->save();
        }

        return true;
    }

    /**
     * Update an Order to be ready for being sent
     *
     * @param int $id_order
     *
     * @return bool
     */
    public static function processSending($id_order = 0)
    {
        if (!$id_order) {
            return false;
        }

        $rcModule = Module::getInstanceByName('relaiscolis');

        // load order data
        $order = new Order((int) $id_order);
        $customer = new Customer((int) $order->id_customer);
        $address = new Address((int) $order->id_address_delivery);

        $id_relais_colis_order = RelaisColisOrder::getRelaisColisOrderId((int) $order->id);
        if ($id_relais_colis_order !== false) {
            $relais_colis_order = new RelaisColisOrder((int) $id_relais_colis_order);
            if ($relais_colis_order->order_weight == 0) {
                Context::getContext()->controller->errors[] = $rcModule->l('You must specify a weight in the order', 'relaiscolisapi') . ': ' . $order->id;
                return false;
            }

            $datas = [];
            $packages = RelaisColisOrderProduct::getPackagesByRcOrderIdDispatchByPackageNumber($relais_colis_order->id);
            if (!empty($packages)) {
                foreach ($packages as $package) {
                    $datas[] = self::getPackagesData($order, $customer, $address, $relais_colis_order, $package, $package['package_number']);
                }
            } else {
                $datas[] = self::getPackagesData($order, $customer, $address, $relais_colis_order);
            }
            $data_reprise = [];
            foreach ($datas as $data) {
                if ($data['orderTypeSub'] == 2) {
                    $dta = $data;
                    $dta['orderType'] = 2;
                    $data_reprise[] = $dta;
                }
            }

            $id_shop = (int) Context::getContext()->shop->id;
            $datas[0] = array_merge($datas[0], [
                'nameExpediteur' => Configuration::get('PS_SHOP_NAME', null, null, $id_shop),
                'address1Expediteur' => Configuration::get('PS_SHOP_ADDR1', null, null, $id_shop),
                'address2Expediteur' => Configuration::get('PS_SHOP_ADDR2', null, null, $id_shop),
                'postcodeExpediteur' => Configuration::get('PS_SHOP_CODE', null, null, $id_shop),
                'cityExpediteur' => Configuration::get('PS_SHOP_CITY', null, null, $id_shop),
                'phoneExpediteur' => Configuration::get('PS_SHOP_PHONE', null, null, $id_shop),
                'emailExpediteur' => Configuration::get('PS_SHOP_EMAIL', null, null, $id_shop),
            ]);

            // Call Relais Colis WS
            $xml = self::rcWsXmlCall('package/placeAdvertisement', $datas);
            if (!$xml) {
                Context::getContext()->controller->errors[] = $rcModule->l('No reservation number received, , please contact the customer support service : ctocpro@relaiscolis.com', 'relaiscolisapi');
                return false;
            }

            if (Configuration::get('RC_ENS_ID') == self::ENS_ID_C2C) {                
                if ((string) $xml->entry) {                    
                    if (Tools::substr((string) $xml->entry, 0, 6) == 'error:') {
                        $errors = explode("<br>", Tools::substr((string) $xml->entry, 6));                        
                        foreach($errors as $err) {
                            Context::getContext()->controller->errors[] = $rcModule->l($err);
                        }
                        return false;
                    }
                }
            }
            
            $pdf_numbers = (array) $xml->entry;
            if (empty($pdf_numbers)) {
                Context::getContext()->controller->errors[] = $rcModule->l('No reservation number received, , please contact the customer support service : ctocpro@relaiscolis.com', 'relaiscolisapi');
                return false;
            }
            
            $return_reference = '';
            $parcel_tracking = true;
            foreach ($pdf_numbers as $package_number => $pdf_number) {
                if ((string) $pdf_number) {
                    $return_reference = Tools::substr($pdf_number, 2, 10);
                    if ($parcel_tracking == true) {
                        // fill data
                        $relais_colis_order->pdf_number = (string) $pdf_number;
                        $tracking_number = (string) $pdf_number;
                        $relais_colis_order->is_send = true;
                    }

                    $relais_colis_pdf = new RelaisColisOrderPdf();
                    $relais_colis_pdf->pdf_number = (string) $pdf_number;
                    $relais_colis_pdf->package_number = (string) $datas[$package_number]['package_number'];
                    $relais_colis_pdf->id_relais_colis_order = $relais_colis_order->id;
                    $relais_colis_pdf->add();
                    $parcel_tracking = false;
                } else {
                    Context::getContext()->controller->errors[] = $rcModule->l('An error occured during the sending', 'relaiscolisapi');

                    return false;
                }
            }

            // save data
            if (!$relais_colis_order->save()) {
                Context::getContext()->controller->errors[] = $rcModule->l('Reservation number not updated', 'relaiscolisapi');

                return false;
            }
            $id_order_carrier = $order->getIdOrderCarrier();
            $customer_name = str_replace(' ', '', $customer->lastname);
            $customer_name = Tools::strtoupper(Tools::substr($customer_name, 0, 4));
            if ((int) $id_order_carrier) {
                $order_carrier = new OrderCarrier((int) $id_order_carrier);
                $order_carrier->tracking_number = $pdf_numbers[0];
                $order_carrier->save();
            }

            $order->shipping_number = $pdf_numbers[0];
            $order->save();
            Context::getContext()->controller->confirmations[] = $rcModule->l('Reservation number received', 'relaiscolisapi');

            if ($data_reprise) {
                foreach ($data_reprise as $key => $reprise) {
                    $data_reprise[$key]['returnReference'] = $return_reference;
                    $data_reprise[$key]['productType'] = '02';
                }
                $xml = self::rcWsXmlCall('package/placeAdvertisement', $data_reprise);
            }
            return true;
        }

        return false;
    }

    public static function getPackagesData(Order $order, Customer $customer, Address $address, RelaisColisOrder $relais_colis_order, $package = [], $package_number = null)
    {
        // prepare Data for Relais Colis WS
        $pseudo_rvc = '';
        $agency_code = '';
        $language_order = Language::getIsoById(Context::getContext()->language->id);
        if ($language_order != 'FR' && $language_order != 'NL') {
            $language_order = 'FR';
        }
        $iso_code = Country::getIsoById($address->id_country);
        $delivery_type = self::DELIVERY_TYPE_DEFAULT;
        $activity_code = self::ACTIVITY_CODE;
        $delivery_type_payment = self::DELIVERY_TYPE_PAYMENT_DEFAULT;

        $datas = [
                // Required fields
                'activationKey' => Configuration::get('RC_ACTIVATION_KEY'),
                'orderReference' => $order->reference,
                'customerId' => $order->id_customer,
                'customerFullname' => $address->lastname . ' ' . $address->firstname,
                'customerEmail' => $customer->email,
                'shippingAddress1' => Tools::substr($address->address1, 0, 32),
                'shippingAddress2' => Tools::substr($address->address2, 0, 32),
                'shippingPostcode' => $address->postcode,
                'shippingCity' => $address->city,
                'shippmentWeight' => (float) $relais_colis_order->order_weight * 1000,
                'weight' => (isset($package['weight'])) ? (float) ($package['weight'] * 1000) : (float) $relais_colis_order->order_weight * 1000,
                'deliveryPaymentMethod' => $delivery_type_payment,
                'pseudoRvc' => $pseudo_rvc,
                'agencyCode' => $agency_code,
                'deliveryType' => $delivery_type,
                'activityCode' => $activity_code,
                'language' => $language_order,
                'shippingCountryCode' => $iso_code, // todo check implement
                'customerPhone' => $address->phone,
                'customerMobile' => $address->phone_mobile,
                'pickingSite' => self::PICKING_DEFAULT,
                'productFamily' => self::PRODUCT_FAMILY,
                'orderType' => self::ORDER_TYPE_DEFAULT,
                'orderTypeSub' => self::ORDER_TYPE_SUB_DEFAULT,
                'sensitiveProduct' => self::SENSITIVE_DEFAULT,
        ];
        if ((int) $relais_colis_order->id_relais_colis_info) {
            // Relais order
            $id_relais_colis_info = RelaisColisInfo::alreadyExists((int) $order->id_cart, (int) $order->id_customer);
            if ((int) $id_relais_colis_info) {
                $relais_colis_info = new RelaisColisInfo($id_relais_colis_info);
                $datas['customerFullname'] = Tools::substr($address->address1, 0, 32);
                $datas['shippingAddress1'] = Tools::substr($address->address2, 0, 32);
                $datas['shippingAddress2'] = '';
                $datas['pseudoRvc'] = $relais_colis_info->pseudo_rvc;
                $datas['xeett'] = $relais_colis_info->rel;
                $datas['agencyCode'] = $relais_colis_info->age_code;

                if ((int) $relais_colis_info->frc_max == 1) {
                    // Relais max
                    $datas['deliveryType'] = self::DELIVERY_TYPE_MAX;
                }
            }
        } else {
            // Home order
            $datas['activityCode'] = self::ACTIVITY_CODE_HOME;

            // Home+ order
            if ((int) $id_relais_colis_info_home = RelaisColisInfoHome::getInfoHomeByIdOrder((int) $order->id)) {
                $relais_colis_info_home = new RelaisColisInfoHome($id_relais_colis_info_home);
                $datas['digicode'] = Tools::substr($relais_colis_info_home->digicode, 0, 8);
                $datas['floor'] = $relais_colis_info_home->floor_delivery;
                $datas['housingType'] = $relais_colis_info_home->type_home;
                $datas['lift'] = $relais_colis_info_home->elevator;
                $datas['urgent'] = $relais_colis_info_home->top;
                $datas['sensitiveProduct'] = $relais_colis_info_home->sensible;

                // options
                $datas['cpSchedule'] = $relais_colis_info_home->schedule;
                $datas['cpRetrieveOldEquipment'] = $relais_colis_info_home->retrieve_old_equipment;
                $datas['cpDeliveryOnTheFloor'] = $relais_colis_info_home->delivery_on_floor;
                $datas['cpDeliveryAtTwo'] = $relais_colis_info_home->delivery_at_two;
                $datas['cpTurnOnHomeAppliance'] = $relais_colis_info_home->turn_on_home_appliance;
                $datas['cpMountFurniture'] = $relais_colis_info_home->mount_furniture;
                $datas['cpNonStandard'] = $relais_colis_info_home->non_standard;
                $datas['cpUnpacking'] = $relais_colis_info_home->unpacking;
                $datas['cpEvacuationPackaging'] = $relais_colis_info_home->evacuation_packaging;
                $datas['cpRecovery'] = Configuration::get('RC_HOME_RETRIEVE_TYPE', null);
                $datas['cpDeliveryDesiredRoom'] = $relais_colis_info_home->delivery_desired_room;
                $datas['cpRecoverOldBedding'] = $relais_colis_info_home->recover_old_bedding;
                $datas['cpDeliveryEco'] = $relais_colis_info_home->delivery_eco;
                $datas['cpAssembly'] = $relais_colis_info_home->assembly;

                // recovery 3DE
                if ($relais_colis_info_home->recovery) {
                    $datas['orderType'] = 2;
                    $datas['orderTypeSub'] = 3;
                }
                // recovery bed delivery
                if ($relais_colis_info_home->recover_old_bedding) {
                    $datas['orderType'] = 1;
                    $datas['orderTypeSub'] = 2;
                }

                $datas['homePlus'] = 1;
            }
        }
        $datas['package_number'] = 0;
        if ($package_number) {
            $datas['package_number'] = $package_number;
        }

        return $datas;
    }

    /**
     * Update an Order to be ready for being returned (Create or update an OrderReturn bound on the Order)
     *
     * @param int $id_order_return
     *
     * @return bool
     */
    public static function processSendingReturn($id_order_return = 0, $prestations = '')
    {
        if (!(int) $id_order_return) {
            return false;
        }
        // load order data
        $rcModule = Module::getInstanceByName('relaiscolisapi');
        $order_return = new OrderReturn((int) $id_order_return);
        $order = new Order((int) $order_return->id_order);
        $customer = new Customer((int) $order->id_customer);
        $address = new Address((int) $order->id_address_invoice);
        $country = Country::getNameById((int) Context::getContext()->language->id, (int) $address->id_country);
        $xeett = '';
        $xeett_name = '';

        $id_relais_colis_order = RelaisColisOrder::getRelaisColisOrderId((int) $order->id);
        if ($id_relais_colis_order !== false) {
            $relais_colis_order = new RelaisColisOrder((int) $id_relais_colis_order);
            $relais_colis_info = new RelaisColisInfo((int) $relais_colis_order->id_relais_colis_info);
            $xeett = $relais_colis_info->rel;
            $xeett_name = $relais_colis_info->rel_name;
        } else {
            Context::getContext()->controller->errors[] = $rcModule->l('This order is not an order with Relais Colis', 'relaiscolisapi');

            return false;
        }

        // max 15 days to return with Relais Colis
        $date_day = date('Y-m-d');
        $date_order = date($order->date_add);
        $date_order_max = date('Y-m-d', strtotime($date_order . ' +15 days'));

        if ($date_day > $date_order_max) {
            Context::getContext()->controller->errors[] = $rcModule->l('This order has been made more then 15 days ago, it can not be returned anymore by relais colis', 'relaiscolisapi');

            return false;
        }
        if ($order_return->id) {
            // Call Relais COlis WS
            $xml = self::rcWsXmlCall(
                'return/placeReturnV3',
                [
                    'activationKey' => Configuration::get('RC_ACTIVATION_KEY'),
                    'requests' => [
                        [
                            'orderId' => $order->id,
                            'customerId' => $order->id_customer,
                            'customerFullname' => $customer->lastname . ' ' . $customer->firstname,
                            'xeett' => $xeett,
                            'xeettName' => $xeett_name,
                            'customerPhone' => $address->phone,
                            'customerMobile' => $address->phone_mobile,
                            'reference' => $order->reference,
                            'customerCompany' => $address->company,
                            'customerAddress1' => $address->address1,
                            'customerAddress2' => $address->address2,
                            'customerPostcode' => $address->postcode,
                            'customerCity' => $address->city,
                            'customerCountry' => $country,
                            'prestations' => $prestations,
                        ],
                    ],
                ]
            );
            if ((string) $xml->entry->response_status != 'Available') {
                Context::getContext()->controller->errors[] = $rcModule->l('No return number received', 'relaiscolisapi');

                return false;
            } else {
                if (!$pdf_number = (string) $xml->entry->return_number) {
                    Context::getContext()->controller->errors[] = $rcModule->l('No return number received', 'relaiscolisapi');

                    return false;
                }
                if (!$token = (string) $xml->entry->token) {
                    Context::getContext()->controller->errors[] = $rcModule->l('No token received', 'relaiscolisapi');

                    return false;
                }
                if (!$image_url = (string) $xml->entry->image_url) {
                    Context::getContext()->controller->errors[] = $rcModule->l('No image Url received', 'relaiscolisapi');

                    return false;
                }
            }
            // Load existing Relais Colis Return
            if ($id_relais_colis_return = RelaisColisReturn::getRelaisColisReturnId((int) $id_order_return)) {
                $relais_colis_return = new RelaisColisReturn((int) $id_relais_colis_return);
            } else {
                $relais_colis_return = new RelaisColisReturn();
                $relais_colis_return->id_order_return = (int) $id_order_return;
                $relais_colis_return->id_order = (int) $order->id;
                $relais_colis_return->id_customer = (int) $order->id_customer;
            }
            // fill data
            $relais_colis_return->pdf_number = $pdf_number;
            $relais_colis_return->token = $token;
            $relais_colis_return->image_url = $image_url;
            $relais_colis_return->bordereau_smart_url = (string) $xml->entry->bordereau_smart_url;
            $relais_colis_return->is_send = true;
            $relais_colis_return->services = $prestations;

            // save data
            if (!$relais_colis_return->save()) {
                Context::getContext()->controller->errors[] = $rcModule->l('Return number not updated', 'relaiscolisapi');

                return false;
            }

            Context::getContext()->controller->confirmations[] = $rcModule->l('Return number updated', 'relaiscolisapi');

            return true;
        }

        return false;
    }

    public static function processTestReturn()
    {
        self::rcWsXmlCall(
            'return/testReturn',
            [
                'activationKey' => Configuration::get('RC_ACTIVATION_KEY'),
                'requests' => [
                    [
                        'test' => 'test',
                    ],
                ],
            ]
        );
    }

    /**
     * Check if all the features in the list are activated
     *
     * @param type $feature_list
     *
     * @return bool
     */
    public static function isFeatureActivated($feature_list)
    {
        if (!is_array($feature_list)) {
            $feature_list = [$feature_list];
        }
        // Get only trigramme
        $keys = explode(';', Configuration::get('RC_OPTIONS'));
        array_pop($keys);
        $found = true;
        foreach ($feature_list as $feature) {
            if (!in_array($feature, $keys)) {
                $found = false;
            }
        }

        return $found;
    }

    /**
     * Transform an XML Object into an array
     *
     * @param type $xmlObject
     * @param array $out
     *
     * @return type
     */
    public static function xml2array($xmlObject, $out = [])
    {
        foreach ((array) $xmlObject as $index => $node) {
            $out[$index] = (is_object($node)) ? xml2array($node) : $node;
        }

        return $out;
    }

    public static function processTokenBalance()
    {
        if (!Configuration::get('RC_ACTIVATION_KEY')) {
            return false;
        }

        try {
            $xml = self::rcWsXmlCall(
                'customer/getinfos',
                [
                    'hash' => Configuration::get('RC_TOKEN_HASH'),
                    'moduleName' => 'relais colis',
                    'cmsName' => 'Prestashop',
                    'cmsVersion' => _PS_VERSION_,
                ]
            );

            if (!$xml) {
                throw new Exception('XML is false');
            }

            if (isset($xml->code)) {
                switch ($xml->code) {
                    case '400':
                    case '404':
                        return false;
                }
            }

            return $xml;
        } catch (Exception $e) {
            // log
            return false;
        }
    }

    /**
     * @param array [$weights] in grams
     *
     * @return array|bool [prices] in euro
     */
    public static function getTokenPrice(array $weights)
    {
        $xml = self::rcWsXmlCall(
            'package/getPackagesPrice',
            [
                'packagesWeight' => $weights,
            ]
        );

        if ($xml === false) {
            return false;
        }

        return (array) $xml->entry;
    }
}