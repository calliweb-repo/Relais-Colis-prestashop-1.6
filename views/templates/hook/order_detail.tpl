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

{if !$c2c_activated}
    <input type="hidden" id="tracking_link_rc" value="{if isset($link_tracking_rc)}{$link_tracking_rc|escape:'htmlall':'UTF-8'}{/if}" />
{/if}
<script type="text/javascript">
{literal}
    $(document).ready(function () {
        var tracking_link = $('#tracking_link_rc').val();
        if (tracking_link !== null && tracking_link !== 'undefined') {
            $('.shipping_number_show a').html(tracking_link);
            var temp = '';
            $('#block-order-detail a').each(function() {
                temp = $(this).attr('href');
                var find = temp.indexOf("style=RC");
                if (find > 0) {
                    if(typeof c2c_activated !== 'undefined') {
                        if(c2c_activated === true) {
                            $(this).html('{/literal}{$link_tracking_rc|escape:'htmlall':'UTF-8'}{literal}');
                        }
                    }
                    else {
                        $(this).html('{/literal}Le tracking s\'effectue sur le site RelaisColis{literal}');
                    }
                }
            });
        }
    });
{/literal}
</script>
