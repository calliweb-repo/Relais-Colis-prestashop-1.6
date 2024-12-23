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
<div class="panel">
    <div class="panel-heading">{l s='Shipping letter editing' mod='relaiscolis'}</div>
    <p>{l s='Your Shipping letter is ready to be printed, please click the editing button.' mod='relaiscolis'}</p>
<form id="myform" action="{$print_letter_url|escape:'htmlall':'UTF-8'}" method="post" target="_blank">
    <input type="hidden" name="search_by" value="order" />
    {foreach $list_letter as $key=>$colis}
        <input type="hidden" name="colis{$key|escape:'htmlall':'UTF-8'}" value="{$colis|escape:'htmlall':'UTF-8'}" />
    {/foreach}
    <input type="hidden" name="letter_number" value="3" />
    <input type="hidden" name="activationKey" value="{$activationKey|escape:'htmlall':'UTF-8'}" />
    <input class="btn btn-primary" type="submit" value="{l s='Launch edition' mod='relaiscolis'}"/>
</form>

<br/>

<a id="return" class="btn btn-primary" href="{$url_back|escape:'htmlall':'UTF-8'}">Retour à la liste</a>
</div>
<script type="text/javascript">
    var url_back = "{$url_back|escape:'htmlall':'UTF-8'}";
    {literal}

        $(document).ready(function ()
        {
            function return_to_list() {
                $('#return').get(0).click();
            }
        });

    {/literal}
</script>
