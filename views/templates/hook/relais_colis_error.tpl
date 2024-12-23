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
<script type="text/javascript">
    {literal}
        $(document).ready(function () {
    {/literal}
    {foreach from=$ids_relais_exclude item=id}
        {literal}
                $('.delivery_option').each(function ( ) {
                    if ($(this).children('.delivery_option_radio').val() == '{/literal}{$id|escape:'htmlall':'UTF-8'}{literal},') {
                        $(this).remove();
                    }
                    if ($(this).find('input.delivery_option_radio').val() == '{/literal}{$id|escape:'htmlall':'UTF-8'}{literal},') {
                        $(this).remove();
                    }
                });
        {/literal}
        {literal}
                    $('#id_carrier{/literal}{$id|escape:'htmlall':'UTF-8'}{literal}').parent().parent().remove();
        {/literal}
    {/foreach}
    {literal}
       if ($('.delivery_option').length == 0) {
            $('button[name=processCarrier]').prop( "disabled", true );
            $('#cgv').prop( "checked", false );
            $('#cgv').prop( "disabled", true );
        }
        });
    {/literal}
</script>
