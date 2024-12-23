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

<div class="panel" id="fieldset_0">
    <div class="panel-heading">
		<i class="icon-user"></i>
        {l s='Relais Point CtoC' mod='relaiscolis'}
	</div>

    <div class="row">
        <div class="col-xs-12">
            <table class="table ">
                <tr>
                    <th><span class="title_box">{l s='Id Order' mod='relaiscolis'}</span></th>
                    <th><span class="title_box">{l s='reference' mod='relaiscolis'}</span></th>
                    <th><span class="title_box">{l s='firstname' mod='relaiscolis'}</span></th>
                    <th><span class="title_box">{l s='lastname' mod='relaiscolis'}</span></th>
                    <th><span class="title_box">{l s='Weight(gram)' mod='relaiscolis'}</span></th>
                    <th><span class="title_box"></span></th>
                </tr>
                {foreach $orders as $row}
                    <tr>

                    <td>{$row['id_order']|escape:'htmlall':'UTF-8'}</td>
                    <td>{$row['reference']|escape:'htmlall':'UTF-8'}</td>
                    <td>{$row['firstname']|escape:'htmlall':'UTF-8'}</td>
                    <td>{$row['lastname']|escape:'htmlall':'UTF-8'}</td>
                    <td>{$row['weight']|escape:'htmlall':'UTF-8'}g</td>

                    {if $row['id_relais_colis_info'] == 0 }
                        <td>    <span class="alert-warning">{l s='This order doesn\'t have a delivery point ' mod='relaiscolis'}</span> </td>
                    {else}
                        <td></td>
                    {/if}

                    </tr>
                {/foreach}
            </table>
        </div>
    </div>
    <br/>
    <div class="form-wrapper">
        <div class="form-group">
            <form action="{$redirect_link|escape:'htmlall':'UTF-8'}" name="saveC2CForm" id="saveC2CForm" method="POST">
                <div class="row">
                    <input type="hidden" name="c2c_ids" value="{if isset($c2c_ids)}{$c2c_ids|escape:'htmlall':'UTF-8'}{/if}" />
                    <div class="col-lg-1 text-left">
                        <label for="c2c_date">{l s='Date' mod='relaiscolis'}</label>
                    </div>
                    <div class="col-lg-1">
                        <div class="input-group">
                            <input type="text" id="date" name="date" value="{if isset($default_date)}{$default_date|escape:'htmlall':'UTF-8'}{/if}"/>
                            <span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-1 text-left">
                        <label for="c2c_smart">{l s='Smart Option' mod='relaiscolis'}</label>
                    </div>
                    <div class="col-lg-1">
                        <span class="switch prestashop-switch fixed-width-lg">
                            <input type="radio" name="c2c_smart" id="c2c_smart_on" value="1" {if isset($default_smart) && $default_smart == true}checked="checked"{/if}>
                            <label for="c2c_smart_on" class="radioCheck">{l s='Yes' mod='relaiscolis'}</label>
                            <input type="radio" name="c2c_smart" id="c2c_smart_off" value="0" {if isset($default_smart) && $default_smart == false}checked="checked"{/if}>
                            <label for="c2c_smart_off" class="radioCheck">{l s='No' mod='relaiscolis'}</label>
                            <a class="slide-button btn"></a>
                        </span>
                    </div>
                </div>
                <br/>
                <button class="btn btn-primary" type="submit" id="save_c2c_submit" name="save_c2c_submit">{l s='Save' mod='relaiscolis'}</button>
            </form>
        </div>
    </div>
</div>

<script>
    $( document ).ready(function() {
        $('input#date').datepicker({
            prevText: '',
            nextText: '',
            dateFormat: 'yy-mm-dd'
        });
    });
</script>
