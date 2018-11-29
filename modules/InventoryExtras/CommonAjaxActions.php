<?php
/*************************************************************************************************
 * Copyright 2018 MajorLabel -- This file is a part of MajorLabel coreBOS Customizations.
* Licensed under the vtiger CRM Public License Version 1.1 (the "License"); you may not use this
* file except in compliance with the License. You can redistribute it and/or modify it
* under the terms of the License. MajorLabel reserves all rights not expressly
* granted by the License. coreBOS distributed by MajorLabel is distributed in
* the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
* warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. Unless required by
* applicable law or agreed to in writing, software distributed under the License is
* distributed on an "AS IS" BASIS, WITHOUT ANY WARRANTIES OR CONDITIONS OF ANY KIND,
* either express or implied. See the License for the specific language governing
* permissions and limitations under the License. You may obtain a copy of the License
* at <http://corebos.org/documentation/doku.php?id=en:devel:vpl11>
*************************************************************************************************/
switch ($_REQUEST['function']) {
	case 'getStockInfoByProduct':
		getStockInfoByProduct($_REQUEST['productid']);
		break;
	default:
		echo "Nothing to declare sir..";
		break;
}

function getStockInfoByProduct($product_id) {
	global $adb
	;
	require_once 'modules/InventoryExtras/InventoryExtras.php';
	require_once 'include/fields/CurrencyField.php';

	$invext = new InventoryExtras();
	$invext_prefix = $invext->getPrefix();

	$r = $adb->pquery("SELECT {$invext_prefix}prod_qty_in_order AS qtyinorder, 
		                      {$invext_prefix}prod_stock_avail AS stockavail, 
		                      qtyindemand FROM vtiger_products WHERE productid = ?", array($product_id));
	if ($adb->num_rows($r) > 0) {
		$data = $adb->fetch_array($r);

		$qty_in_order = CurrencyField::convertToUserFormat($data['qtyinorder']);
		$stock_avail = CurrencyField::convertToUserFormat($data['stockavail']);
		$qty_in_demand = CurrencyField::convertToUserFormat($data['qtyindemand']);

		$qty_in_order_lab = getTranslatedString('LBL_TO_DELIVER_SO', 'InventoryExtras');
		$qty_in_demand_lab = getTranslatedString('LBL_TO_RECEIVE_PO', 'InventoryExtras');
		$stock_avail_lab = getTranslatedString($invext_prefix . 'prod_stock_avail', 'Products');
		
		echo json_encode(array(
			'qtyinorder' => array(
				'label' => $qty_in_order_lab,
				'value' => $qty_in_order),
			'qtyindemand' => array(
				'label' => $qty_in_demand_lab,
			    'value' => $qty_in_demand),
			'stockavail' => array(
				'label' => $stock_avail_lab,
			    'value' => $stock_avail),
			)
		);
	} else {
		echo "NOTHINGFOUND";
	}
}