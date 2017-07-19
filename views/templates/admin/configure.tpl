{*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2017 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div class="panel">
	<h3><i class="icon icon-credit-card"></i> {l s='2Performant tracking code' mod='tperformant'}</h3>
	<p>
		{l s='You have to be registered with 2Performant.com in order to use this module.' mod='tperformant'}
	</p>
</div>

{if (isset($form_successes)) && (count($form_successes) > 0)}
	<div class="alert alert-success">
		<h4>{l s='Success!' mod='tperformant'}</h4>
		<ul class="list-unstyled">
		{foreach from=$form_successes item='message'}
			<li>{$message|escape:'htmlall':'UTF-8'}</li>
		{/foreach}
		</ul>
	</div>
{/if}
