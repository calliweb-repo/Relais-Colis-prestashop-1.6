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
<div id="product-relaiscolis" class="panel product-tab">
	<input type="hidden" name="submitted_tabs[]" value="Suppliers">
	<h3>
        <i class="icon-cogs"></i>
        {l s='Relais Colis' mod='relaiscolis'}
    </h3>
	<div class="alert alert-info">
        {l s='The number of package will create the default number of package for an order' mod='relaiscolis'}
	</div>
        <div class="form-group">
		    <label class="control-label col-lg-3" for="packageRC">{l s='Number of package' mod='relaiscolis'} :</label>
    		<div class="input-group col-lg-2">
    			<input id="packageRC" name="packageRC" type="text" value="{$relais_colis_product->package_quantity|escape:'html':'UTF-8'}">
    		</div>
		</div>


	<div class="panel-footer">
		<a href="{$admin_product_link|escape:'htmlall':'UTF-8'}" class="btn btn-default"><i class="process-icon-cancel"></i>{l s='Cancel' mod='relaiscolis'}</a>
		<button type="submit" name="submitAddproduct" class="btn btn-default pull-right"><i class="process-icon-save"></i>{l s='Save' mod='relaiscolis'}</button>
		<button type="submit" name="submitAddproductAndStay" class="btn btn-default pull-right"><i class="process-icon-save"></i>{l s='Save and stay' mod='relaiscolis'}</button>
	</div>
</div>
