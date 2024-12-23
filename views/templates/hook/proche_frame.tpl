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
{addJsDef is_selected=$is_selected|boolval}
{addJsDef have_selected_point=$have_selected_point|boolval}
{addJsDef have_selected_last=$have_selected_last|boolval}
{addJsDef url_img=$url_img}
{addJsDef key_rc_build=$key_build}
{addJsDef msg_order_carrier_relais=$msg_order_carrier_relais}
{addJsDef redirect_link_rc=$redirect_link}
{addJsDef relais_carrier_id=$relais_carrier_id}
{addJsDef baseUrl=$baseUrl}
<div id="frame_relais">
    {if $have_selected_point || $have_selected_last}
        <div class="col-sm-12 col-xs-12 clearfix choice-info">
            <div class="col-sm-6 col-xs-12">
                <span class="relay-info-intro">
                    {if $have_selected_point}
                        {l s='You have selected that delivery point :' mod='relaiscolis'}
                    {/if}
                    {if $have_selected_last}
                        {l s='In previous order you have selected that delivery point :' mod='relaiscolis'}
                    {/if}
                </span>
                <br/>
                <span class="relay-info-title">{if isset($relay_info.name)}{$relay_info.name|escape:'htmlall':'UTF-8'}{/if}</span>
                <br/>
                <span>{if isset($relay_info.street)}{$relay_info.street|escape:'htmlall':'UTF-8'}{/if}</span>
                <br/>
                <span>{if isset($relay_info.postcode)}{$relay_info.postcode|escape:'htmlall':'UTF-8'}{/if} {if isset($relay_info.city)}{$relay_info.city|escape:'htmlall':'UTF-8'}{/if}</span>
                <br/>
                {if $have_selected_last}
                    <a href="{$redirect_link_last_point|escape:'htmlall':'UTF-8'}" class="red-link button-relais" id="set-last-relay">{l s='Choose that point' mod='relaiscolis'}</a>
                {/if}
                {if $have_selected_point || $have_selected_last}
                    <button class="red-link button-relais" id="set-another-relay" type="button">{l s='Find another point' mod='relaiscolis'}</button>
                {/if}
            </div>
            <div class="col-sm-6 col-xs-12">
                <span class="relay-info-intro">{l s='Opening Hours :' mod='relaiscolis'}</span>
                <br/>
                <table>
                    <tr>
                        <td>{l s='Monday : ' mod='relaiscolis'}</td><td>{if isset($relay_info.ouvlun)}{$relay_info.ouvlun|escape:'htmlall':'UTF-8'}{/if}</td>
                    </tr>
                    <tr>
                        <td>{l s='thuesday : ' mod='relaiscolis'}</td><td>{if isset($relay_info.ouvmar)}{$relay_info.ouvmar|escape:'htmlall':'UTF-8'}{/if}</td>
                    </tr>
                    <tr>
                        <td>{l s='Wednesday : ' mod='relaiscolis'}</td><td>{if isset($relay_info.ouvmer)}{$relay_info.ouvmer|escape:'htmlall':'UTF-8'}{/if}</td>
                    </tr>
                    <tr>
                        <td>{l s='Thursday : ' mod='relaiscolis'}</td><td>{if isset($relay_info.ouvjeu)}{$relay_info.ouvjeu|escape:'htmlall':'UTF-8'}{/if}</td>
                    </tr>
                    <tr>
                        <td>{l s='Friday : ' mod='relaiscolis'}</td><td>{if isset($relay_info.ouvven)}{$relay_info.ouvven|escape:'htmlall':'UTF-8'}{/if}</td>
                    </tr>
                    <tr>
                        <td>{l s='Saturday : ' mod='relaiscolis'}</td><td>{if isset($relay_info.ouvsam)}{$relay_info.ouvsam|escape:'htmlall':'UTF-8'}{/if}</td>
                    </tr>
                    <tr>
                        <td>{l s='Sunday : ' mod='relaiscolis'}</td><td>{if isset($relay_info.ouvdim)}{$relay_info.ouvdim|escape:'htmlall':'UTF-8'}{/if}</td>
                    </tr>
                </table>
            </div>
        </div>
    {/if}


    <div class="col-sm-12 col-xs-12 clearfix" id="divGlobal">
        <div class="relais-title txt-center">
            <span>{l s='Find a delivery point everywhere in France' mod='relaiscolis'}</span>
        </div>
        <div class="col-sm-12 col-xs-12 clearfix">
            <div class="col-md-6 col-xs-6">
                <label class="grey" for="adresse">{l s='Address :' mod='relaiscolis'}</label>
                <input class="small" value="{$street|escape:'htmlall':'UTF-8'}" name="form_address" id="form_address" type="text">
            </div>
            <div class="col-md-2 col-xs-6">
                <label class="grey" for="codePostal">{l s='Postcode :' mod='relaiscolis'}</label>
                <input class="small"value="{$postcode|escape:'htmlall':'UTF-8'}" name="form_CP" id="form_CP" type="text">
            </div>
            <div class="col-md-2 col-xs-6">
                <label class="grey" for="ville">{l s='City :' mod='relaiscolis'}</label>
                <input class="small" value="{$city|escape:'htmlall':'UTF-8'}" name="form_city" id="form_city" type="text">
            </div>
            <div class="col-md-2 col-xs-6">
                <label class="grey" for="ville">{l s='Country :' mod='relaiscolis'}</label>
                <select class="small" name="list_country_rc" id="list_country_rc">
                    {foreach from=$limited_countries item=country}
                        <option value="{$country.iso3|escape:'html':'UTF-8'}" {if $country_selected == $country.iso3}selected{/if}>{$country.name}</option>
                    {/foreach}
                </select>
            </div>
        </div>

        <div class="col-md-12 col-xs-12 txt-center">
            <button class="red-link button-relais" type="button" onclick="launchResearch();
                        return false;">{l s='Ok' mod='relaiscolis'}</button>
            <div class="flag-img">
                <img class="logo-relais" src="modules/relaiscolis/views/img/rc_long_logo.png"/>
            </div>
        </div>
        <div id="divEmplacement" style="display: none">
            Choisissez un emplacement :
            <select name="selListeAdresses" id="selListeAdresses">
            </select>
            <input type="button" value="Afficher" name="makeReco" onclick="resolveAmbiguity();" />
        </div>
        <div class="col-sm-12 col-xs-12 clearfix" id="map_rc_wrapper">
            <div class="col-sm-4 col-xs-12" id="divInfohtml">
            </div>
            <div class="col-sm-8 col-xs-12" id="divMapContainer">
            </div>
            <input type="hidden" id="refresh" value ="0"/>
        </div>
    </div>
</div>

<script type="text/javascript">
    {literal}

        $(document).ready(function () {
            var is_selected = '{/literal}{$is_selected|escape:'htmlall':'UTF-8'}{literal}';
            var have_selected_point = '{/literal}{$have_selected_point|escape:'htmlall':'UTF-8'}{literal}';
            var have_selected_last = '{/literal}{$have_selected_last|escape:'htmlall':'UTF-8'}{literal}';
            if (is_selected) {
                $('#frame_relais').show('1000');
            }
            else {
                $('#frame_relais').hide('1000');
            }
            if (!have_selected_point && !have_selected_last) {
                $('#divGlobal').show('1000');
            }
            else {
                $('#divGlobal').hide('1000');
            }
            $('#set-another-relay').click(function () {
                $('#divGlobal').show('1000');
            });
        });

    {/literal}
</script>
