{*/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/*}
{if count($lines) > 0}
<table class="slds-table slds-table_cell-buffer slds-table_bordered" style="text-align: left;">
	<thead>
		<tr class="slds-line-height_reset">
			<th class="slds-text-title_caps" scope="col">
				<div style="font-weight: bold;" class="slds-truncate" title="{'invextras_prod_qty_in_order'|@getTranslatedString:'Products'}">{'invextras_prod_qty_in_order'|@getTranslatedString:'Products'}</div>
			</th>
			<th class="slds-text-title_caps" scope="col">
				<div style="font-weight: bold;" class="slds-truncate" title="{'Subject'|@getTranslatedString:'PurchaseOrder'}">{'Subject'|@getTranslatedString:'PurchaseOrder'}</div>
			</th>
 			<th class="slds-text-title_caps" scope="col">
				<div style="font-weight: bold;" class="slds-truncate" title="{'Vendor Name'|@getTranslatedString:'Vendors'}">{'Vendor Name'|@getTranslatedString:'Vendors'}</div>
			</th>
		</tr>
	</thead>
	<tbody>
		{foreach from=$lines item=line}
		<tr class="slds-hint-parent">
			<th data-label="{'LBL_QTY'|@getTranslatedString}" scope="row">
				<div class="slds-truncate" title="">{$line.qty_bo}</div>
			</th>
			<td data-label="{'Subject'|@getTranslatedString:'PurchaseOrder'}">
				<div class="slds-truncate" title=""><a href="index.php?module=PurchaseOrder&action=DetailView&record={$line.po_id}" target="_blank">{$line.subject}</a></div>
			</td>
 			<td data-label="{'Vendor Name'|@getTranslatedString:'Vendors'}">
				<div class="slds-truncate" title="">{$line.vendorname}</div>
			</td> 
		</tr>
		{/foreach}
	</tbody>
</table>
{else}
<span class="slds-text-title_caps">{'LBL_NO_ORDERED_PRODS_FOUND'|@getTranslatedString:'Products'}</span>
{/if}
