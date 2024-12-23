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

<div class="relais-about"> 
    {l s='You may have problems setting the weight ranges of the different Relais Parcel carriers.' mod='relaiscolis'}<br/>
    {l s='This can usually happen if you try to edit existing slices.' mod='relaiscolis'}<br/>
    {l s='If necessary, you can reset the different carriers using the buttons below.' mod='relaiscolis'}<br/><br/>
    {l s='Please refer to the user documentation for more information.' mod='relaiscolis'}<br/>
    <br/>
    {if $rc_delivery_active}
        <label class="control-label col-lg-4">
            {l s='Reset Relais Colis carrier' mod='relaiscolis'}
        </label>
        <div class="col-lg-8">
            <input type="submit" class="btn btn-default" name="reset_rc_carrier" value="{l s='Reset' mod='relaiscolis'}" />
        </div>
        <br/><br/><br/>
    {/if}
    {if $rc_home_delivery_active}
        <label class="control-label col-lg-4">
            {l s='Reset Relais Colis Home carrier' mod='relaiscolis'}
        </label>
        <div class="col-lg-8">
            <input type="submit" class="btn btn-default" name="reset_rc_carrierhome" value="{l s='Reset' mod='relaiscolis'}" />
        </div>
        <br/><br/><br/>
    {/if}
    {if $rc_home_plus_delivery_active}
        <label class="control-label col-lg-4">
            {l s='Reset Relais Colis Home + carrier' mod='relaiscolis'}
        </label>
        <div class="col-lg-8">
            <input type="submit" class="btn btn-default" name="reset_rc_carrierhomeplus" value="{l s='Reset' mod='relaiscolis'}" />
        </div>
    {/if}
</div>