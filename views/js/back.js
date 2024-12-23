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
    $('#submitPdfLabel').click(function () {
        $('#rc_form_pdf').submit();
    });
    $('#submitSendingLabel').click(function () {
        $(this).hide();
        $('.waiting_relais').show(1000);
    });
    $('#submitSendingLabelReturn').click(function (event) {
        if (confirm("Valider votre retour avec ces prestations ? Attention ceux ci ne pourront plus être modifiés par la suite.")) {
            $(this).hide();
            $('.waiting_relais').show(1000);
        } else {
            event.preventDefault();
        }
    });
    $("a[name='sendLabel']").click(function () {
        $.ajax({
            type: "POST",
            url: $("a[name='sendLabel']").attr("controller"),
            async: false,
            dataType: "html",
            data: {
                ajax: "1",
                action: "SendLabelByMail",
                label_url: $("a[name='sendLabel']").attr("url"),
                lastname: $("a[name='sendLabel']").attr("lastname"),
                firstname: $("a[name='sendLabel']").attr("firstname"),
                email: $("a[name='sendLabel']").attr("email"),
                order_ref: $("a[name='sendLabel']").attr("order_ref"),
            },
            success: function (res) {
                $("a[name='sendLabel']").hide();
                $("span[name='mailSent']").text(" L'étiquette a été envoyée par mail ");
                $("span[name='mailSent']").show();
            },
            error: function (res) {
                console.log(res);
            }

        });
    });
    $("a[name='sendSmartLabel']").click(function () {
        $.ajax({
            type: "POST",
            url: $("a[name='sendSmartLabel']").attr("controller"),
            async: false,
            dataType: "html",
            data: {
                ajax: "1",
                action: "SendSmartLabelByMail",
                smart_label_url: $("a[name='sendSmartLabel']").attr("url"),
                lastname: $("a[name='sendSmartLabel']").attr("lastname"),
                firstname: $("a[name='sendSmartLabel']").attr("firstname"),
                email: $("a[name='sendSmartLabel']").attr("email"),
                order_ref: $("a[name='sendSmartLabel']").attr("order_ref"),
            },
            success: function (res) {
                $("a[name='sendSmartLabel']").hide();
                $("span[name='mailSmartSent']").text(" Le bordereau Smart a été envoyé par mail ");
                $("span[name='mailSmartSent']").show();
            },
            error: function (res) {
                console.log(res);
            }

        });
    });
    $('#submitRegularOrder').click(function () {
        if (confirm("Attention !\n\nCeci va supprimer les enregistrements relatifs à RelaisColis en base de données pour cette commande.\n\nCette action est irréversible.")) {
            $('#correct_rc_address').submit();
        }
    });

    // MAJ WIDGET DEC 2024 - suppression gestion affichage numéro suivi
    /* var tracking_link = $('#tracking_link_rc').val();
    if (tracking_link !== null && tracking_link !== 'undefined') {
        $('.shipping_number_show a').html(tracking_link);
        $('#shipping_number_show a').html(tracking_link);
    }

    if (typeof (c2c_activated) !== 'undefined') {
        if (c2c_activated === true && $('#shipping_table .shipping_number_show').size()) {
            $('#shipping_table .shipping_number_show').html('Le tracking s\'effectue sur le site RelaisColis');
        }
    } */

    $('#wrp9').click(function () {
        smartManage();
    });
    smartManage();
});

function smartManage() {
    if ($('#wrp9').prop('checked') == true) {
        for (var i = 1; i < 9; i++) {
            $('#wrp' + i).prop('checked', false);
            $('#wrp' + i).prop('disabled', true);
        }
    } else {
        for (var i = 1; i < 9; i++) {
            $('#wrp' + i).prop('disabled', false);
        }
    }
}
