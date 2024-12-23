/**
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
 */

$(document).ready(function () {
    $('form[name=carrier_area]').submit(function (e) {
        if (($('#id_carrier' + relais_carrier_id).is(':checked')) || ($('.delivery_option_radio:checked').val() == relais_carrier_id + ',') || ($('#id_carrier' + relais_carrier_max_id).is(':checked')) || ($('.delivery_option_radio:checked').val() == relais_carrier_max_id + ','))
        {

            if (!have_selected_point) {
                if (!!$.prototype.fancybox)
                    $.fancybox.open([
                        {
                            type: 'inline',
                            autoScale: true,
                            minHeight: 30,
                            content: '<p class="fancybox-error">' + msg_order_carrier_relais + '</p>'
                        }],
                            {
                                padding: 0
                            });
                else
                    alert(msg_order_carrier);
                e.preventDefault();
            }
        }
        return true;
    });

    // if no relais point selected, we prevent all form of payment method to be submited
	if($('#order-opc').size() && !(have_selected_point || have_selected_last) && idCarrierRcOrRcmaxSelected()) {
		$("#opc_payment_methods-content form").submit(function(evt) {           
			evt.preventDefault();
		});
        
        if($('#cgv').attr('checked')) {
            $('#HOOK_TOP_PAYMENT').html('<img src="'+baseUrl+'modules/relaiscolis/views/img/rc_long_logo.png" alt="Relais Colis">');
            $('#opc_payment_methods-content #HOOK_PAYMENT').html(msg_order_carrier_relais);
        }
	}

    // On One Page Checkout, we need to prevent the selection of a means of payment if there is no selected relais point.
    $('#order-opc').on('click', '.payment_module a', function(e) {
		if(!(have_selected_point || have_selected_last) && idCarrierRcOrRcmaxSelected()) {
			e.preventDefault();

			if (!!$.prototype.fancybox) {
				$.fancybox.open([
				{
					type: 'inline',
					autoScale: true,
					minHeight: 30,
					content: '<p class="fancybox-error">' + msg_order_carrier_relais + '</p>'
				}],
				{
					padding: 0
				});
			}
			else {
				alert(msg_order_carrier_relais);
			}

			return false;
		}
	});
});

function idCarrierRcOrRcmaxSelected() {
     if (($('#id_carrier' + RELAISCOLIS_ID).is(':checked')) || ($('.delivery_option_radio:checked').val() == RELAISCOLIS_ID + ',')) {
         return true;
     }
     if (($('#id_carrier' + RELAISCOLIS_ID_MAX).is(':checked')) || ($('.delivery_option_radio:checked').val() == RELAISCOLIS_ID_MAX + ',')) {
         return true;
     }
	return false;
}

function updatePaymentMethods(json)
{
    if($('#order-opc').size() && !(have_selected_point || have_selected_last) && idCarrierRcOrRcmaxSelected() && $('#cgv').attr('checked')) {
        $('#HOOK_TOP_PAYMENT').html('<img src="'+baseUrl+'modules/relaiscolis/views/img/rc_long_logo.png" alt="Relais Colis">');
        $('#opc_payment_methods-content #HOOK_PAYMENT').html(msg_order_carrier_relais);
    }
    else {
        $('#HOOK_TOP_PAYMENT').html(json.HOOK_TOP_PAYMENT);
        $('#opc_payment_methods-content #HOOK_PAYMENT').html(json.HOOK_PAYMENT);
    }
}

function submitUpdateOptionHome(option)
{
    var cost = $('#' + option + '_cost').val();
    var selected = $('#'+ option).val();
    var cart = $('#id_cart_home').val();
    var customer = $('#id_customer_home').val();
    $.ajax({
        type: 'POST',
        url: baseUri + 'modules/relaiscolis/ajax.php?rand=' + new Date().getTime() + '&rc_token=' + rc_token,
        async: true,
        cache: false,
        dataType: "json",
        headers: {"cache-control": "no-cache"},
        data:
                {
                    option: option,
                    selected: selected,
                    cart: cart,
                    customer: customer,
                    ajax: true,
                    token: token
                },
        success: function (jsonData)
        {
            if (jsonData.hasError)
            {
                var errors = '';
                for (error in jsonData.errors)
                    //IE6 bug fix
                    if (error != 'indexOf')
                        errors += '<li>' + jsonData.errors[error] + '</li>';
            }
        },
        error: function (XMLHttpRequest, textStatus, errorThrown)
        {
            error = "TECHNICAL ERROR: unable to load form.\n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus;
            if (!!$.prototype.fancybox)
            {
                $.fancybox.open([
                    {
                        type: 'inline',
                        autoScale: true,
                        minHeight: 30,
                        content: "<p class='fancybox-error'>" + error + '</p>'
                    }],
                        {
                            padding: 0
                        });
            }
            else
                alert(error);
        }
    });
}
