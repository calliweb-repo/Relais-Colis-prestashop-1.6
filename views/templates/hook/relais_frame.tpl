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
{addJsDef is_selected_home=$is_selected_home|boolval}
{addJsDef is_selected_home_plus=$is_selected_home_plus|boolval}
{addJsDef have_selected_point=$have_selected_point|boolval}
{addJsDef have_selected_last=$have_selected_last|boolval}
{addJsDef url_img=$module_dir}
{addJsDef key_rc_build=$key_build}
{addJsDef relaisColisKey=$relais_colis_key}
{addJsDef msg_order_carrier_relais=$msg_order_carrier_relais}
{addJsDef redirect_link_rc=$redirect_link}
{addJsDef relais_carrier_id=$relais_carrier_id}
{addJsDef home_carrier_id=$home_carrier_id}
{addJsDef relais_carrier_max_id=$relais_carrier_max_id}
{addJsDef max_unit_weight=$max_unit_weight}
{addJsDef is_relais_max=$is_relais_max}
{addJsDef must_unselected=$must_unselected}
{addJsDef have_selected_point=$have_selected_point}
{addJsDef msg_order_carrier_relais=$msg_order_carrier_relais}
{addJsDef have_selected_last=$have_selected_last}
{addJsDef RELAISCOLIS_ID=$RELAISCOLIS_ID}
{addJsDef RELAISCOLIS_ID_MAX=$RELAISCOLIS_ID_MAX}
{addJsDef baseUrl=$baseUrl}
{addJsDef rc_token=$rc_token}
{addJsDef use_id_ens=$useidens}
{addJsDef id_cart_home=$id_cart_home}
{addJsDef id_customer_home=$id_customer_home}

{* Si relais colis est sélectionné *}
{if $is_selected}

    {* Si un relais colis est sauvegardé en base pour ce panier *}
    {if $rel_selected_rel}

        <p>{l s='You have selected that delivery point :' mod='relaiscolis'}</p>

        <ul>
            <li><strong>Id Point relais :</strong> {$rel_selected_rel|escape:'html':'UTF-8'}</li>
            <li><strong>Nom :</strong> {$rel_selected_name|escape:'html':'UTF-8'}</li>
            <li><strong>Adresse :</strong> {$rel_selected_adr|escape:'html':'UTF-8'}</li>
            <li><strong>Code postal :</strong> {$rel_selected_cp|escape:'html':'UTF-8'}</li>
            <li><strong>Ville :</strong> {$rel_selected_vil|escape:'html':'UTF-8'}</li>
        </ul>

        <a href="{$link->getModuleLink('relaiscolis', 'redirect')}?action=delete_relais" 
            class="btn btn-primary">
            Changer de point relais
        </a>

    {else}

    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1>Choisissez votre point relais</h1>
            </div>
            <!-- Conteneur de l'iframe -->
            <div class="col-12" id="relais-colis-iframe-container"></div>
        </div>
    </div>

    {/if}
{/if}

    {literal}
        <script>
            // URL de l'iframe
            var widgetLocationUrl = "https://qaservice.relaiscolis.com/WidgetOsmRC/";

            // Création et ajout de l'iframe dans le conteneur
            var iframeHtml = '<iframe id="relaisColisIframe" src="' + widgetLocationUrl + '" style="width: 100%; height: 580px; border: none;" allow="geolocation"></iframe>';

            document.getElementById('relais-colis-iframe-container').innerHTML = iframeHtml;

            // Ajout du listener pour recevoir les messages de l'iframe
            window.addEventListener("message", function(event) {
                        
                // Vérification de la source (ajustez l'URL au besoin)
                if (event.origin !== "https://qaservice.relaiscolis.com") {
                    console.error("Domaine non autorisé :", event.origin);
                    return;
                }

                // Vérification des données reçues
                if (event.data && event.data.id && event.data.name) {
                    var data = event.data;

                    // Construire l'URL pour redirect
                    var redirectUrl = redirect_link_rc +
                    "?id_cart=" + encodeURIComponent(id_cart_home) +
                    "&id_customer=" + encodeURIComponent(id_customer_home) +
                    "&rel=" + encodeURIComponent(data.id) +
                    "&rel_name=" + encodeURIComponent(data.Nomrelais) +
                    "&rel_adr=" + encodeURIComponent(data.Geocoadresse) +
                    "&rel_cp=" + encodeURIComponent(data.Postalcode) +
                    "&rel_vil=" + encodeURIComponent(data.Commune) +
                    "&pseudo_rvc=" + encodeURIComponent(data.Pseudorvc || "") +
                    "&frc_max=" + encodeURIComponent(data.frcMax || 0) +
                    "&floc_rel=" + encodeURIComponent(data.flocRel || "") +
                    "&fcod_pays=" + encodeURIComponent(data.countryISO || "FRA") +
                    "&type_liv=" + encodeURIComponent(data.typeLiv || "REL") +
                    "&age_code=" + encodeURIComponent(data.ageCode || "") +
                    "&age_nom=" + encodeURIComponent(data.ageNom || "") +
                    "&age_adr=" + encodeURIComponent(data.ageAdr || "") +
                    "&age_vil=" + encodeURIComponent(data.ageVil || "") +
                    "&age_cp=" + encodeURIComponent(data.ageCp || "") +
                    "&ouvlun=" + encodeURIComponent(data.openingHours?.lun || "") +
                    "&ouvmar=" + encodeURIComponent(data.openingHours?.mar || "") +
                    "&ouvmer=" + encodeURIComponent(data.openingHours?.mer || "") +
                    "&ouvjeu=" + encodeURIComponent(data.openingHours?.jeu || "") +
                    "&ouvven=" + encodeURIComponent(data.openingHours?.ven || "") +
                    "&ouvsam=" + encodeURIComponent(data.openingHours?.sam || "") +
                    "&ouvdim=" + encodeURIComponent(data.openingHours?.dim || "") +
                    "&smart=" + encodeURIComponent(data.Relaissmart || 0) +
                    "&selected_date=" + encodeURIComponent(new Date().toISOString().split('T')[0]);

                    // Rediriger vers la page redirect
                    window.location.href = redirectUrl;

                }


            }, false);
            
        </script>
    {/literal}
