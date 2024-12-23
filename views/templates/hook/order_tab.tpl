{*
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
*}
{addJsDef c2c_activated=$c2c_activated|boolval}
{addJsDef has_c2c=$has_c2c|boolval}

<div class="panel clearfix" id="informations_relais_colis">
    <div class="panel-heading">
        <i class="icon-truck"></i>
        {l s='Relais Colis informations' mod='relaiscolis'}
    </div>

    <div hidden id="rc_error_data" data-data="{$rcErrors|escape:'htmlall':'UTF-8'}"></div>
    <div id="rc_label_alert"></div>

    <div class="rc-information col-xs-12 col-sm-6 col-md-3">
        <div class="well">
            {if isset($relay_info.rel)}
                <span>{l s='Delivery point Id : ' mod='relaiscolis'}
                    {if isset($relay_info.rel)}{$relay_info.rel|escape:'htmlall':'UTF-8'}{/if}</span>
                <br />
                <br />
                <span
                    class="relay-info-title">{if isset($relay_info.name)}{$relay_info.name|escape:'htmlall':'UTF-8'}{/if}</span>
                <br />
                <br />
                <span>{if isset($relay_info.street)}{$relay_info.street|escape:'htmlall':'UTF-8'}{/if}</span>
                <br />
                <span>{if isset($relay_info.postcode)}{$relay_info.postcode|escape:'htmlall':'UTF-8'}{/if}
                    {if isset($relay_info.city)}{$relay_info.city|escape:'htmlall':'UTF-8'}{/if}</span>
            {else}
                <span>{l s='Home delivery' mod='relaiscolis'}</span>
            {/if}
        </div>
    </div>
    <input type="hidden" value="{if empty($relais_colis_packages)}0{else}1{/if}" id="compact_rc" />

    {if !$has_etiquette && $relais_colis_packages}
        <div class="rc-information col-xs-12 col-sm-5 well">
            <div class="panel-heading">
                <i class="icon-truck"></i>
                {l s='Packages' mod='relaiscolis'}
            </div>

            <table class="table" id="rc-package">
                <thead>
                    <th colspan=2>
                        {l s='Package Number' mod='relaiscolis'}
                    </th>
                    <th>
                        {l s='Product' mod='relaiscolis'}
                    </th>
                    <th>
                        {l s='Weight (in kg)' mod='relaiscolis'}
                    </th>
                    <th>
                    </th>
                </thead>
                {foreach $relais_colis_packages as $nb => $packages}
                    <tbody id="tbody-{$nb|escape:'htmlall':'UTF-8'}">
                        {foreach $packages.package as $key => $package}
                            {if $key == 0}
                                <tr>
                                    <td id="td-rowspan-{$nb|escape:'htmlall':'UTF-8'}" class="col-xs-1"
                                        rowspan="{$packages.count|escape:'htmlall':'UTF-8'}" colspan="2">
                                        {$package.package_number|escape:'htmlall':'UTF-8'}
                                    </td>
                                </tr>
                            {/if}
                            <tr id="rc-package-tr-{$package.id_relais_colis_order_product|escape:'htmlall':'UTF-8'}">
                                <td>
                                    {$package.product_name|escape:'htmlall':'UTF-8'}
                                </td>
                                <td>
                                    {$package.weight|escape:'htmlall':'UTF-8'|number_format:3:".":" "}
                                </td>
                                <td>
                                    <button id="rc-package-delete" class="rc-package-delete"
                                        data-key='{$package.id_relais_colis_order_product|escape:'htmlall':'UTF-8'}'
                                        data-package-number='{$nb|escape:'htmlall':'UTF-8'}'>
                                        <i class="icon-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        {/foreach}
                    </tbody>
                {/foreach}
                <tbody id="last-tbody">
                    <tr>
                        <td>
                        </td>
                        <td>
                            <input type="text" id="rc_package_number" name="rc_package_number"
                                placeholder="{l s='Nb' mod='relaiscolis'}">
                        </td>
                        <td>
                            <select id="rc_id_product" name="rc_id_product">
                                {foreach $products as $product}
                                    <option value="{$product.product_id|escape:'htmlall':'UTF-8'}"
                                        name="{$product.product_name|escape:'htmlall':'UTF-8'}">
                                        {$product.product_name|escape:'htmlall':'UTF-8'}</option>
                                {/foreach}
                            </select>
                        </td>
                        <td>
                            <input type="text" id="rc_weight" name="rc_weight"
                                placeholder="{l s='5, 15, 20' mod='relaiscolis'}">
                        </td>
                    </tr>
                </tbody>
            </table>
            <button id="rc_op_add_row" class="btn btn-primary">{l s='Add' mod='relaiscolis'}</button>
        </div>
    {/if}

    {if isset($has_etiquette) && $c2c_activated == false}
        {if $has_etiquette}
            {if $etiquettes}
                <div class="rc-information col-xs-12 col-sm-6 well">
                    <div class="panel-heading">
                        <i class="icon-truck"></i>
                        {l s='Etiquettes' mod='relaiscolis'}
                    </div>
                    <div class="row" id="select_format">
                        <table class="table">
                            <thead>
                                <th>
                                    {l s='Package Number' mod='relaiscolis'}
                                </th>
                                <th>
                                    {l s='Shipping Number' mod='relaiscolis'}
                                </th>
                                <th>
                                    {l s='Weight' mod='relaiscolis'}
                                </th>
                                <th>
                                    {l s='Format' mod='relaiscolis'}
                                </th>
                                <th>
                                </th>
                            </thead>
                            {foreach $etiquettes as $etiquette}
                                <form action="{$print_pdf_url|escape:'htmlall':'UTF-8'}" method="post" target="_blank">
                                    <tr>
                                        <td>
                                            {$etiquette.package_number|escape:'htmlall':'UTF-8'}
                                        </td>
                                        <td>
                                            {$etiquette.pdf_number14|escape:'htmlall':'UTF-8'}
                                        </td>
                                        <td>
                                            {$etiquette.weight|escape:'htmlall':'UTF-8'|number_format:3:".":" "}
                                        </td>
                                        <td>
                                            <select name="format">
                                                <option value="A4" selected>10 x 15 : 4 par page</option>
                                                <option value="A5">21 x 15 : une par page</option>
                                                <option value="ZEBRA">10 x 15 x 4 / ZEBRA</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="hidden" name="pdf" value="{$etiquette.pdf_number|escape:'htmlall':'UTF-8'}" />
                                            <input type="hidden" name="activationKey"
                                                value="{$activationKey|escape:'htmlall':'UTF-8'}" />
                                            <button class="btn btn-primary "
                                                type="submit">{l s='Download Label' mod='relaiscolis'}</button>
                                        </td>
                                    </tr>
                                </form>
                            {/foreach}
                        </table>
                    {else}
                        <form action="{$print_pdf_url|escape:'htmlall':'UTF-8'}" id="rc_form_pdf" method="post" target="_blank">
                            <div class="col-xs-6 col-sm-4">
                                Format :
                            </div>
                            <div class="col-xs-6 col-sm-6">
                                <select name="format">
                                    <option value="A4" selected>10 x 15 : 4 par page</option>
                                    <option value="A5">21 x 15 : une par page</option>
                                    <option value="ZEBRA">10 x 15 x 4 / ZEBRA</option>
                                </select>
                            </div>
                            <input type="hidden" name="pdf" value="{$has_etiquette|escape:'htmlall':'UTF-8'}" />
                            <input type="hidden" name="activationKey" value="{$activationKey|escape:'htmlall':'UTF-8'}" />
                        </form>

                        <div class="row">
                            <div class="col-xs-12 col-sm-4">
                                <button class="btn btn-primary " type="button" id="submitPdfLabel"
                                    name="submitPdfLabel">{l s='Download Label' mod='relaiscolis'}</button>
                            </div>
                        </div>
                    {/if}
                </div>
            </div>
        {else}
            {if isset($valid_delivery_address) && $valid_delivery_address == true}
                <div class="rc-information col-xs-6 col-sm-3 well">
                    <div class="row" id="select_format">
                        <form
                            action="{$currentIndex|escape:'htmlall':'UTF-8'}&id_order={$order->id|escape:'htmlall':'UTF-8'}&vieworder&token={$smarty.get.token|escape:'htmlall':'UTF-8'}"
                            method="post" autocomplete="off">
                            <div class="panel-heading">
                                <i class="icon-truck"></i>
                                {l s='Etiquette' mod='relaiscolis'}
                            </div>
                            <div style="margin-bottom:5px;">
                                {if empty($relais_colis_packages)}
                                    <span>{l s='Weight in grams' mod='relaiscolis'}</span><input type="text" name="weight"
                                        value="{$weight|escape:'htmlall':'UTF-8'}" class="fixed-width-sm" /><br />
                                {/if}
                                <input type="hidden" name="id_relais_colis_order"
                                    value="{$id_relais_colis_order|escape:'htmlall':'UTF-8'}" />
                                <p>
                                    <button class="btn btn-primary" type="submit" id="submitSendingLabel"
                                        name="submitSendingLabel">{l s='Asking for label' mod='relaiscolis'}</button>
                                    <span class="waiting_relais">{l s='Processing...' mod='relaiscolis'}</span>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            {else}
                <div class="rc-information col-xs-6 col-sm-3 well">
                    <form action="#" method="post" autocomplete="off" id="correct_rc_address">
                        <div class="panel-heading">
                            <i class="icon-truck"></i>
                            {l s='Etiquette' mod='relaiscolis'}
                        </div>
                        <div class="alert alert-danger">
                            <button type="button" class="close" data-dismiss="alert">×</button>
                            <span>
                                {l s='It seems there is an error with the selected address' mod='relaiscolis'}
                            </span>
                        </div>
                        <span>{l s='You can manually select a valid address : ' mod='relaiscolis'}</span>
                        <select id="rc_id_new_address" name="rc_id_new_address">
                            {foreach $rc_addresses as $rc_address}
                                <option value="{$rc_address.id_address|escape:'htmlall':'UTF-8'}"
                                    name="{$rc_address.alias|escape:'htmlall':'UTF-8'}">
                                    {$rc_address.alias|substr:15|escape:'htmlall':'UTF-8'} -
                                    {$rc_address.lastname|escape:'htmlall':'UTF-8'}</option>
                            {/foreach}
                        </select><br />
                        <button class="btn btn-primary" type="submit" id="submitNewRcAddress"
                            name="submitNewRcAddress">{l s='Validate' mod='relaiscolis'}</button><br /><br />
                    </form>
                </div>
            {/if}
        {/if}
    {/if}
    <input type="hidden" id="tracking_link_rc"
        value="{if isset($link_tracking_rc)}{$link_tracking_rc|escape:'htmlall':'UTF-8'}{/if}" />

    <!-- C2C -->
    {if isset($has_c2c) && $has_c2c == true}
        {if 
                                                                                                                            Configuration::get('RC_CRON_TOKEN') 
                                                                                                                            && Configuration::get('RC_CRON_TOKEN') != "" 
                                                                                                                          && isset($ens_id_c2c)
                                                                                                                            && $ens_id_c2c
                                                                                                                        }
        {if isset($valid_delivery_address) && $valid_delivery_address == true}
            {if $etiquettes}
                <form action="{$print_pdf_url|escape:'htmlall':'UTF-8'}" id="rc_form_pdf" method="post" target="_blank">
                    <input type="hidden" name="pdf" value="{$has_etiquette|escape:'htmlall':'UTF-8'}" />
                    <input type="hidden" name="activationKey" value="{$activationKey|escape:'htmlall':'UTF-8'}" />
                </form>

                <div class="row">
                    <div class="col-xs-12 col-sm-4">
                        <button class="btn btn-primary " type="button" id="submitPdfLabel"
                            name="submitPdfLabel">{l s='Download Label' mod='relaiscolis'}</button>
                    </div>
                </div>
            {else}
                <div class="rc-information col-xs-12 col-sm-6 col-md-4">
                    <div class="row well" id="select_format">
                        <form
                            action="{$currentIndex|escape:'htmlall':'UTF-8'}&id_order={$order->id|escape:'htmlall':'UTF-8'}&vieworder&token={$smarty.get.token|escape:'htmlall':'UTF-8'}"
                            method="post" autocomplete="off">
                            <div class="panel-heading">
                                <i class="icon-truck"></i>
                                {l s='Etiquette' mod='relaiscolis'}
                            </div>
                            <div class="col-sm-12 col-md-6" style="margin-bottom:5px;">
                                {if empty($relais_colis_packages)}
                                    <span>{l s='Weight in grams' mod='relaiscolis'}</span>
                                    <input type="text" name="weight" value="{$weight|escape:'htmlall':'UTF-8'}" class="fixed-width-sm"
                                        style="display:inline-block" />
                                    <button type="button" class="btn btn-primary" id="update_price_btn"><i class="icon-refresh"
                                            title="{l s='Refresh price' mod='relaiscolis'}"></i></button>
                                    <img id="c2c_loader" hidden src="{$img_dir|escape:'html':'UTF-8'}loader.gif" />
                                    <br />
                                {/if}
                                <input type="hidden" name="id_relais_colis_order"
                                    value="{$id_relais_colis_order|escape:'htmlall':'UTF-8'}" />

                            </div>
                            {if Configuration::get('RC_TOKEN_ACTIVE')}
                                <div class="col-sm-12 col-md-6">
                                    <table class="table table-striped rc-summary-table">
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>{l s='Available Balance' mod='relaiscolis'}</td>
                                                <td id="account_balance" data-balance="{$token_balance|escape:'htmlall':'UTF-8'}">
                                                    {$token_balance|number_format:2:".":" "}€</td>
                                            </tr>
                                            <tr>
                                                <td>{l s='Cost' mod='relaiscolis'}</td>
                                                <td>- <span id="estimated_price"
                                                        style="margin-right:0">{$token_cost|number_format:2:".":" "|escape:'htmlall':'UTF-8'}€</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>{l s='Leftover' mod='relaiscolis'}</td>
                                                <td id="leftover">
                                                    {($token_balance - $token_cost)|number_format:2:".":" "|escape:'htmlall':'UTF-8'}€
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            {/if}
                            <p>
                                <button class="btn btn-primary" type="submit" id="submitSendingLabel"
                                    name="submitSendingLabel">{l s='Asking for label' mod='relaiscolis'}</button>
                                <span class="waiting_relais">{l s='Processing...' mod='relaiscolis'}</span>
                            </p>
                        </form>
                    </div>
                </div>
            {/if}
        {else}
            <div class="rc-information col-xs-6 col-sm-3 well">
                <form action="#" method="post" autocomplete="off" id="correct_rc_address">
                    <div class="panel-heading">
                        <i class="icon-truck"></i>
                        {l s='Etiquette' mod='relaiscolis'}
                    </div>
                    <div class="alert alert-danger">
                        <button type="button" class="close" data-dismiss="alert">×</button>
                        <span>
                            {l s='It seems there is an error with the selected address' mod='relaiscolis'}
                        </span>
                    </div>
                    <span>{l s='You can manually select a valid address : ' mod='relaiscolis'}</span>
                    <select id="rc_id_new_address" name="rc_id_new_address">
                        {foreach $rc_addresses as $rc_address}
                            <option value="{$rc_address.id_address|escape:'htmlall':'UTF-8'}"
                                name="{$rc_address.alias|escape:'htmlall':'UTF-8'}">
                                {$rc_address.alias|substr:15|escape:'htmlall':'UTF-8'} -
                                {$rc_address.lastname|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select><br />
                    <button class="btn btn-primary" type="submit" id="submitNewRcAddress"
                        name="submitNewRcAddress">{l s='Validate' mod='relaiscolis'}</button><br /><br />
                </form>
            </div>
        {/if}

    {else}
        <div class="rc-information col-xs-12 col-sm-3 well">
            <div class="row">
                <div>
                    <form action="{$redirect_link_csv|escape:'htmlall':'UTF-8'}" target="_blank" name="form_export_csv"
                        id="form_export_csv" method="POST">
                        <input type="hidden" name="relay_c2c_id" value="{$relay_c2c_id|escape:'htmlall':'UTF-8'}" />
                        <button class="btn btn-primary" type="submit" id="export_csv"
                            name="export_csv">{l s='Export CSV' mod='relaiscolis'}</button>
                        <br>
                    </form>
                </div>
            </div>
        </div>
    {/if}
    {/if}
</div>

<script>
    const errorHandler = class {
        constructor(id) {
            this.id = id;
            this.errors = [];
            this.success = [];
        }

        display() {
            if (this.errors.length == 0 && this.success.length == 0) {
                return;
            }

            let html = '';

            $.each(this.errors, function(index, value) {
                html += '<div class="alert alert-danger"><span>' + value + '</span>' +
                    '<button type="button" class="close" data-dismiss="alert">×</button>' +
                    '</div>';
            });

            $.each(this.success, function(index, value) {
                html += '<div class="alert alert-success"><span>' + value + '</span>' +
                    '<button type="button" class="close" data-dismiss="alert">×</button>' +
                    '</div>';
            });

            $(this.id).html(html);
            $(this.id).show();
        }

        reset() {
            $(this.id).hide();
            $(this.id).html('');
            this.errors = [];
            this.success = [];
        }
    }

    $(document).ready(function() {
        // INIT
        $("#c2c_map").hide();
        $('#rc-errors-form').hide();
        var rc_label_alert = new errorHandler('#rc_label_alert');

        if ($('#rc_error_data').length > 0) {
            rc_label_alert.errors = $('#rc_error_data').data('data');
            rc_label_alert.display();
        }

        // Choose C2C
        $("#choose_c2c").click(function() {
            $("#c2c_map").toggle();
        });

        $("#rechoose_c2c").click(function() {
            $("#c2c_map").toggle();
        });

        $('input#date').datepicker({
            prevText: '',
            nextText: '',
            dateFormat: 'yy-mm-dd'
        });

        if ($('#rc-package').length > 0) {
            calculTotalWeight();
        }

        // CALCUL WEIGHT LEFT
        function calculTotalWeight() {
            rc_label_alert.reset();

            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: 'ajax-tab.php',
                data: {
                    ajax: true,
                    controller: 'AdminManageRelaisColisOrderProduct',
                    action: 'getDetailWeightPackage',
                    token: '{$admin_token|escape:'htmlall':'UTF-8'}',
                    id_relais_colis_order: {$id_relais_colis_order|escape:'htmlall':'UTF-8'},
                    id_order: {$order->id|escape:'htmlall':'UTF-8'}
                },
                success: function(result) {
                    if (result.same_total == true) {
                        $("#submitSendingLabel").removeAttr('disabled');

                        let msg = "{l s='The package is ready to be sent.' mod='relaiscolis'}";
                        rc_label_alert.success.push(msg);
                        rc_label_alert.display();
                    } else {
                        var compat = $('#compact_rc').val();
                        if (compat != '0') {
                            $("#submitSendingLabel").attr('disabled', 'disabled');
                        }
                        var html =
                            '<button type="button" class="close" data-dismiss="alert">×</button>';
                        $.each(result.detail, function(key, detail) {
                            if (detail.total_weight > detail.saved_weight) {
                                rc_label_alert.errors.push('-' + detail.product_name + " {l s='missing weight:' mod='relaiscolis'} <b>" + (detail.total_weight - detail.saved_weight).toFixed(2) + '</b><br>');
                            }

                            if (detail.total_weight < detail.saved_weight) {
                                rc_label_alert.errors.push('-' + detail.product_name + " {l s='excess weight:' mod='relaiscolis'} <b>" + (detail.saved_weight - detail.total_weight).toFixed(2) + '</b><br>');
                            }
                        });

                        rc_label_alert.display();
                    }
                }
            });
        }

        // ADD ROW
        $('#rc_op_add_row').live('click', function() {
            // INIT
            var package_number = $('#rc_package_number').val();
            var id_product = $('#rc_id_product').val();
            var product_name = $('#rc_id_product').find('option:selected').attr("name");
            var weight = $('#rc_weight').val();
            rc_label_alert.reset();
            // CONTROL
            if (!package_number) {
                rc_label_alert.errors.push("{l s='Choose a package number.' mod='relaiscolis'}");
            }

            if (!id_product) {
                rc_label_alert.errors.push("{l s='Choose a product.' mod='relaiscolis'}");
            }

            if (!weight) {
                rc_label_alert.errors.push("{l s='Choose a weight.' mod='relaiscolis'}");
            }

            // ACTION
            if (rc_label_alert.errors.length === 0) {
                // Errors hide
                rc_label_alert.reset();

                // Check if exist
                $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    url: 'ajax-tab.php',
                    data: {
                        ajax: true,
                        controller: 'AdminManageRelaisColisOrderProduct',
                        action: 'addPackage',
                        token: '{$admin_token|escape:'htmlall':'UTF-8'}',
                        package_number: package_number,
                        id_product: id_product,
                        weight: weight,
                        id_relais_colis_order: {$id_relais_colis_order|escape:'htmlall':'UTF-8'}
                    },
                    success: function(data) {
                        var tbody = $('#rc-package #tbody-' + package_number);
                        var trContent = '<tr id="rc-package-tr-' + data.id + '"><td>' +
                            product_name + '</td><td>' + weight +
                            '</td><td><button id="rc-package-delete" class="rc-package-delete" data-key="' +
                            data.id + '"><i class="icon-trash"></i></button></td></tr>';

                        if (tbody.length === 0) {
                            var table = $('#rc-package #last-tbody');
                            var trContent = '<tr><td id="td-rowspan-' + package_number +
                                '" colspan="2">' + package_number + '</td></tr>' +
                                trContent;
                            var tbodyContent = '<tbody id="tbody-' + package_number + '">' +
                                trContent + '</tbody>';
                            table.prev().after(tbodyContent);
                            $('#td-rowspan-' + package_number).attr('rowspan', 2);
                        } else {
                            tbody.append(trContent);
                            var tdRowspan = $('#td-rowspan-' + package_number);
                            var rowspan_nb = tdRowspan.attr('rowspan');
                            tdRowspan.attr('rowspan', rowspan_nb + 1);
                        }
                        calculTotalWeight();
                    }
                });
            } else {
                rc_label_alert.display();
            }

        });

        // DELETE ROW
        $('.rc-package-delete').live('click', function() {
            var key = $(this).data('key');
            var packageNumber = $(this).data('package-number');
            var tr = $('#rc-package-tr-' + key);
            rc_label_alert.reset();

            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: 'ajax-tab.php',
                data: {
                    ajax: true,
                    controller: 'AdminManageRelaisColisOrderProduct',
                    action: 'deletePackage',
                    token: '{$admin_token|escape:'htmlall':'UTF-8'}',
                    id_relais_colis_order_product: key,
                },
                success: function(data) {
                    if (data.result == 'success') {
                        tr.remove();
                        var rowCount = $('#tbody-' + packageNumber + ' tr').length;
                        if (rowCount == 1) {
                            $('#tbody-' + packageNumber).remove();
                        }
                        calculTotalWeight();
                    } else {
                        rc_label_alert.errors.push(data.message);
                        rc_label_alert.display();
                    }
                }
            });
        });

        var input_tokens = $('#account_balance');
        var input_weight = $('input[name=weight]');
        var price = $('#estimated_price');
        var leftover = $('#leftover');
        var tokens = 0;
        if (input_tokens.length > 0) {
            tokens = input_tokens.data('balance');
        }

        $('#update_price_btn').on('click', function() {
            let btn = $(this);
            btn.hide();
            $('#c2c_loader').show();
            rc_label_alert.reset();

            $.ajax({
                type: "POST",
                url: 'ajax-tab.php',
                dataType: "JSON",
                data: {
                    weight: input_weight.val(),
                    controller: 'AdminManageRelaisColisOrderProduct',
                    action: "getPackagesPrice",
                    token: '{$admin_token|escape:'htmlall':'UTF-8'}'
                },
                success: function(res) {
                    btn.show();
                    $('#c2c_loader').hide();

                    if (typeof(res['error']) !== 'undefined') {
                        rc_label_alert.errors.push(res['error']);
                    } else if (typeof(res['prices']) !== 'undefined') {
                        if (parseFloat(res['prices'][0]) != res['prices'][0]) {
                            rc_label_alert.errors.push(res['prices'][0]);
                        } else {
                            price.html(formatPrice(res['prices'][0]));
                            leftover.html(formatPrice(parseFloat(tokens) - parseFloat(res[
                                'prices'][0])));
                        }
                    }
                    rc_label_alert.display();
                },
                error: function(res) {
                    btn.show();
                    $('#c2c_loader').hide();

                    rc_label_alert.errors.push('Ajax error');
                    rc_label_alert.display();
                }
            });
        });

        function formatPrice(price) {
            return Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(price);
        }
    });
</script>
