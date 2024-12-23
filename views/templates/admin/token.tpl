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
<div class="relais-token">
    {if isset($error)}
        <p style="color: red"><b>{$error|escape:'html':'UTF-8'}</b></p>
        <p>{l s='Your account is inactive' mod='relaiscolis'}</p>
    {else}
        <h2>Bonjour {$infos->firstname|escape:'html':'UTF-8'} {$infos->lastname|escape:'html':'UTF-8'}</h2>
        {if $infos->accountStatus == 'active'}
            <p>{l s='Your account is active' mod='relaiscolis'}</p>
            <p>{l s='Your account balance' mod='relaiscolis'} : {$infos->balance|floatval|number_format:2:".":" "}€</p>
        {else}
            <p>{l s='Your account is inactive' mod='relaiscolis'}</p>
        {/if}
    {/if}
</div>
