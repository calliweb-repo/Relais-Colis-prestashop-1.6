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
<form id="myform" action="{$print_pdf_url|escape:'htmlall':'UTF-8'}" method="post" target="_blank">
    {foreach $list_pdf as $key=>$pdf}
        <input type="hidden" name="pdf{$key|escape:'htmlall':'UTF-8'}" value="{$pdf|escape:'htmlall':'UTF-8'}" />
    {/foreach}
    <input type="hidden" name="activationKey" value="{$activationKey|escape:'htmlall':'UTF-8'}" />

    <div id="select_format">
        <span>{l s='Format :' mod='relaiscolis'}</span>
        <select name="format">
            <option value="A4" selected>{l s='10 x 15 : 4 par page' mod='relaiscolis'}
            <option value="A5">{l s='21 x 15 : une par page' mod='relaiscolis'}
            <option value="ZEBRA">{l s='10 x 15 x 4 / ZEBRA' mod='relaiscolis'}
        </select>
    </div>

    <input class="btn btn-primary" type="submit" />
</form>
<br/>
<a id="return" class="btn btn-primary" href="{$url_back|escape:'htmlall':'UTF-8'}">{l s='Back to list' mod='relaiscolis'}</a>
<script type="text/javascript">
    var url_back = "{$url_back|escape:'htmlall':'UTF-8'}";
    {literal}

        $(document).ready(function ()
        {
            $('#myform').submit();
        });
    {/literal}
</script>
