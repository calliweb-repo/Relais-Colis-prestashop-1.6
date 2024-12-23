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
{capture name=path}<a href="{$link->getPageLink('my-account.php', true)|escape:'htmlall':'UTF-8'}">{l s='My account' mod='relaiscolis'}</a><span class="navigation-pipe">{$navigationPipe|escape:'htmlall':'UTF-8'}</span>{l s='My relais colis returns' mod='relaiscolis'}{/capture}
    {include file="$tpl_dir./errors.tpl"}

<h1 class="page-heading bottom-indent">{l s='My relais colis returns' mod='relaiscolis'}</h1>
<p>{l s='Here are the returns managed by relais colis.' mod='relaiscolis'}</p>
<div class="block-center" id="block-history">
    {if $orders && count($orders)}
        <table id="order-list" class="table table-bordered footab">
            <thead>
                <tr>
                    <th class="first_item">{l s='Return' mod='relaiscolis'}</th>
                    <th class="item">{l s='Order' mod='relaiscolis'}</th>
                    <th class="item">{l s='Statut' mod='relaiscolis'}</th>
                    <th class="item">{l s='Date' mod='relaiscolis'}</th>
                    <th class="last_item">{l s='PDF' mod='relaiscolis'}</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$orders item=order name=myLoop}
                    <tr class="{if $smarty.foreach.myLoop.first}first_item{elseif $smarty.foreach.myLoop.last}last_item{else}item{/if} {if $smarty.foreach.myLoop.index % 2}alternate_item{/if}">
                        <td>{$order.id_order_return|string_format:"%06d"|escape:'htmlall':'UTF-8'}</td>
                        <td>{$order.reference|escape:'htmlall':'UTF-8'}</td>
                        <td>{$order.state_name|escape:'htmlall':'UTF-8'}</td>
                        <td>{dateFormat date=$order.date_add full=0}</td>
                        <td>
                            {if $order.state == '2'}
                                {if $order.image_url}
                                    <a class="btn btn-primary" target="_blank" href="{$order.image_url|escape:'html':'UTF-8'}">
                                    {l s='Print' mod='relaiscolis'}
                                    </a>  
                                    {if $order.bordereau_smart_url}
                                        <a class="btn btn-primary" target="_blank" href="{$order.bordereau_smart_url|escape:'html':'UTF-8'}">
                                        {l s='Smart Delivery Slip' mod='relaiscolis'}
                                        </a> 
                                    {/if}
                                {else}
                                <form id="myform{$order.id_order_return|escape:'htmlall':'UTF-8'}" action="{$print_pdf_url|escape:'htmlall':'UTF-8'}" method="post" target="_blank">
                                    <input type="hidden" name="number1" value="{$order.pdf_number|escape:'htmlall':'UTF-8'}" />
                                    <input type="hidden" name="token1" value="{$order.token|escape:'htmlall':'UTF-8'}" />
                                    <input class="btn btn-primary" type="submit" value="{l s='Print' mod='relaiscolis'}" />
                                </form>
                                {/if}
                            {else}
                                {l s='not available yet' mod='relaiscolis'}
                            {/if}
                        </td>

                    </tr>
                {/foreach}
            </tbody>
        </table>
    {else}
        <p class="warning">{l s='You don\'t have any return to print.' mod='relaiscolis'}</p>
    {/if}
</div>

<ul class="footer_links clearfix">
    <li>
        <a class="btn btn-default button button-small" href="{$link->getPageLink('my-account', true)|escape:'html':'UTF-8'}">
            <span>
                <i class="icon-chevron-left"></i>{l s='Back to Your Account' mod='relaiscolis'}
            </span>
        </a>
    </li>
    <li>
        <a class="btn btn-default button button-small" href="{$base_dir|escape:'htmlall':'UTF-8'}">
            <span><i class="icon-chevron-left"></i>{l s='Home' mod='relaiscolis'}</span>
        </a>
    </li>
</ul>
