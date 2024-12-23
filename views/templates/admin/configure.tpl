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
    <div class="row relais-header">
        <div class="col-md-1 text-center logo">
            <img src="{$module_dir|escape:'html':'UTF-8'}views/img/relaiscolis.png" id="relais-logo" />
        </div>
        <div class="col-md-2 text-center subscribe">
        </div>
        <div class="col-md-6 about">
            <h4>{l s='About Relais Colis' mod='relaiscolis'} {$relais_version|escape:'htmlall':'UTF-8'}</h4>
            {l s='Inventor of the concept of delivery in local relay since 1983, Relais Colis is the first network of delivery in local points and at home.' mod='relaiscolis'}
            <div>{l s='Today Relais Colis is :' mod='relaiscolis'}</div>
            <ul>
                <li>{l s='The number 1 partner of the top 10 e-commerce. (Amazon, Vinted, La Redoute, Bouygues...)' mod='relaiscolis'}
                </li>
                <li>{l s='More than 40 million parcels delivered per year.' mod='relaiscolis'}
                </li>
                <li>{l s='An integrated network of 5 national hubs and 22 regional agencies.' mod='relaiscolis'}</li>
                <li>{l s='7,000 relays in France.' mod='relaiscolis'}</li>
            </ul>
            <div class="col-md-12">
                <a href="{$user_manual_url|escape:'html':'UTF-8'}" download>{l s='Download the user manual' mod='relaiscolis'} <i
                        class="icon-file-text"></i></a>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $('#RC_TOP_HOUR').timepicker({
            timeFormat: 'h:mm'
        });
    });
</script>
