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
<span class="btn-group-action">
    <span class="btn-group">{$id_order|escape:'html':'UTF-8'}
        <a class="btn btn-default _self" href ="{$link->getAdminLink('AdminOrders', true)|escape:'html':'UTF-8'}&vieworder&id_order={$id_order|escape:'html':'UTF-8'}">
            <i class="icon-file-text"></i></a>
    </span>
</span>
