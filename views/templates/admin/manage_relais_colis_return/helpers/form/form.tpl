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

{extends file='helpers/form/form.tpl'}

{block name="input"}
    {if $input.type == 'text_customer'}
        <span>{$customer->firstname|escape:'html':'UTF-8'} {$customer->lastname|escape:'html':'UTF-8'}</span>
        <p>
            <a class="text-muted" href="{$url_customer|escape:'html':'UTF-8'}">{l s='View details on the customer page' mod='relaiscolis'}</a>
        </p>
    {elseif $input.type == 'text_order'}
        <span>{$text_order|escape:'html':'UTF-8'}</span>
        <p>
            <a class="text-muted" href="{$url_order|escape:'html':'UTF-8'}">{l s='View details on the order page' mod='relaiscolis'}</a>
        </p>
    {elseif $input.type == 'pdf_order_return'}
        <p>
            {if $state_order_return == 2}
                {if isset($pdf_number)}
                    <div class="rc-information">
                        {if count($registred_services) }
                            <p>{l s='Choosen Return Services' mod='relaiscolis'} : </p>
                        {/if}
                        <ul>
                            {foreach $registred_services as $service}
                                <li>{$service|escape:'html':'UTF-8'}</li>
                            {/foreach}
                        </ul>
                        {if $image_url}
                            <a class="btn btn-primary" target="_blank" href="{$image_url|escape:'html':'UTF-8'}">
                                <i class="icon-download"></i>
                                {l s='Download label' mod='relaiscolis'}
                            </a>
                            {if $bordereau_smart_url}
                                <a class="btn btn-primary" target="_blank" href="{$bordereau_smart_url|escape:'html':'UTF-8'}">
                                    <i class="icon-download"></i>
                                    {l s='Smart label' mod='relaiscolis'}
                                </a> 
                            {/if}
                            <a class="btn btn-primary" name="sendLabel" firstname="{$customer->firstname|escape:'html':'UTF-8'}"  lastname="{$customer->lastname|escape:'html':'UTF-8'}" email="{$customer->email}" url="{$image_url|escape:'html':'UTF-8'}" order_ref="{$order_ref|escape:'html':'UTF-8'}">
                                <i class="icon-send"></i>
                                {l s='Send label by mail' mod='relaiscolis'}
                            </a>
                            {if $bordereau_smart_url}
                                <a class="btn btn-primary" name="sendSmartLabel" firstname="{$customer->firstname|escape:'html':'UTF-8'}"  lastname="{$customer->lastname|escape:'html':'UTF-8'}" email="{$customer->email}" url="{$bordereau_smart_url|escape:'html':'UTF-8'}" order_ref="{$order_ref|escape:'html':'UTF-8'}">
                                    <i class="icon-send"></i>
                                    {l s='Send smart label by mail' mod='relaiscolis'}
                                </a>
                            {/if}
                            <span class="success" name="mailSent" style="display:none"></span>
                            <span class="success" name="mailSmartSent" style="display:none"></span>
                        {elseif $pdf_number}
                            <button class="btn btn-primary" type="button" id="submitPdfLabel" name="submitPdfLabel">{l s='Download label' mod='relaiscolis'}</button>
                        {else}
                            <form action="{$currentIndex|escape:'html':'UTF-8'}&id_order_return={$id_order_return|escape:'html':'UTF-8'}&updateorder_return&token={$smarty.get.token|escape:'html':'UTF-8'}" method="post" autocomplete="off">
                                {if count($prestations) }
                                    <p>{l s='Choosen Return Services available' mod='relaiscolis'} : </p>
                                {/if}
                                {foreach $prestations as $key => $prestation}
                                    <div class="checkbox">
                                        <label for="wrp{$key|escape:'html':'UTF-8'}">
                                            <input type="checkbox" name="wrp{$key|escape:'html':'UTF-8'}" id="wrp{$key|escape:'html':'UTF-8'}" value="1"{if $prestation == "Smart"}checked{/if}>
                                            {$prestation|escape:'html':'UTF-8'}
                                        </label>
                                    </div>
                                {/foreach}
                                {if count($prestations) }
                                    <br/><br/>
                                {/if}

                                <button class="btn btn-primary" type="submit" id="submitSendingLabelReturn" name="submitSendingLabel">{l s='Asking for label' mod='relaiscolis'}</button> <span class="waiting_relais">{l s='Processing...' mod='relaiscolis'}</span>
                            </form>
                        {/if}
                    </div>
                {/if}   
            {else}
                --
            {/if}
        </p>
    {elseif $input.type == 'list_products'}
        <table class="table">
            <thead>
                <tr>
                    <th>{l s='Reference' mod='relaiscolis'}</th>
                    <th>{l s='Product name' mod='relaiscolis'}</th>
                    <th class="text-center">{l s='Quantity' mod='relaiscolis'}</th>
                    <th class="text-center">{l s='Action' mod='relaiscolis'}</th>
                </tr>
            </thead>
            <tbody>
                {foreach $returnedCustomizations as $returnedCustomization}
                    <tr>
                        <td>{$returnedCustomization['reference']|escape:'html':'UTF-8'}</td>
                        <td>{$returnedCustomization['name']|escape:'html':'UTF-8'}</td>
                        <td class="text-center">{$returnedCustomization['product_quantity']|intval|escape:'html':'UTF-8'}</td>
                        <td class="text-center">
                            <a class="btn btn-default" href="{$current|escape:'html':'UTF-8'}&amp;deleteorder_return_detail&amp;id_order_detail={$returnedCustomization['id_order_detail']}&amp;id_order_return={$id_order_return}&amp;id_customization={$returnedCustomization['id_customization']}&amp;token={$token|escape:'html':'UTF-8'}">
                                <i class="icon-remove"></i>
                                {l s='Delete' mod='relaiscolis'}
                            </a>
                        </td>
                    </tr>
                    {assign var='productId' value=$returnedCustomization.product_id}
                    {assign var='productAttributeId' value=$returnedCustomization.product_attribute_id}
                    {assign var='customizationId' value=$returnedCustomization.id_customization}
                    {assign var='addressDeliveryId' value=$returnedCustomization.id_address_delivery}
                    {foreach $customizedDatas.$productId.$productAttributeId.$addressDeliveryId.$customizationId.datas as $type => $datas}
                        <tr>
                            <td colspan="4">
                                <div class="form-horizontal">
                                {if $type == Product::CUSTOMIZE_FILE}
                                    {foreach from=$datas item='data'}
                                        <div class="form-group">
                                            <span class="col-lg-3 control-label"><strong>{l s='Attachment' mod='relaiscolis'}</strong></span>
                                            <div class="col-lg-9">
                                                <a href="displayImage.php?img={$data['value']|escape:'html':'UTF-8'}&amp;name={$returnedCustomization['id_order_detail']|intval|escape:'html':'UTF-8'}-file{$smarty.foreach.data.iteration.iteration}" class="_blank"><img class="img-thumbnail" src="{$picture_folder}{$data['value']}_small" alt="" /></a>
                                            </div>
                                        </div>
                                    {/foreach}
                                {elseif $type == Product::CUSTOMIZE_TEXTFIELD}
                                    {foreach from=$datas item='data'}
                                        <div class="form-group">
                                            <span class="control-label col-lg-3"><strong>{if $data['name']}{$data['name']|escape:'html':'UTF-8'}{else}{l s='Text #%d' sprintf=$smarty.foreach.data.iteration mod='relaiscolis'}{/if}</strong></span>
                                            <div class="col-lg-9">
                                                <p class="form-control-static">
                                                    {$data['value']|escape:'html':'UTF-8'}
                                                </p>
                                            </div>
                                        </div>
                                    {/foreach}
                                {/if}
                                </div>
                            </td>
                        </tr>
                    {/foreach}
                {/foreach}

                {* Classic products *}
                {foreach $products as $k => $product}
                    {if !isset($quantityDisplayed[$product['id_order_detail']]) || $product['product_quantity']|intval > $quantityDisplayed[$product['id_order_detail']]|intval}
                        <tr>
                            <td>{$product['product_reference']|escape:'html':'UTF-8'}</td>
                            <td class="text-center">{$product['product_name']|escape:'html':'UTF-8'}</td>
                            <td class="text-center">{$product['product_quantity']|escape:'html':'UTF-8'}</td>
                            <td class="text-center">
                                <a class="btn btn-default"  href="{$current|escape:'html':'UTF-8'}&amp;deleteorder_return_detail&amp;id_order_detail={$product['id_order_detail']}&amp;id_order_return={$id_order_return}&amp;token={$token|escape:'html':'UTF-8'}">
                                    <i class="icon-remove"></i>
                                    {l s='Delete' mod='relaiscolis'}
                                </a>
                            </td>
                        </tr>
                    {/if}
                {/foreach}
            </tbody>
        </table>
    {else}
        {$smarty.block.parent}
    {/if}

    {if $pdf_number}
        <form action="{$print_pdf_url|escape:'html':'UTF-8'}" id="rc_form_pdf" method="post" target="_blank">
            <input type="hidden" name="number1" value="{$pdf_number|escape:'html':'UTF-8'}" />
            <input type="hidden" name="token1" value="{$token_number|escape:'html':'UTF-8'}" />
        </form>
    {/if}
{/block}
