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
	case 'getInfoByProduct':
		getInfoByProduct($_REQUEST['productid'], $_REQUEST['record'], $_REQUEST['seq']);
		break;
	default:
		echo "Nothing to declare sir..";
		break;
}

function getInfoByProduct($product_id, $record_id, $seq) {
	global $adb
	;
	require_once 'modules/InventoryExtras/InventoryExtras.php';
	require_once 'include/fields/CurrencyField.php';

	$invext = new InventoryExtras();
	$invext_prefix = $invext->getPrefix();

	$r = $adb->pquery("SELECT {$invext_prefix}prod_qty_in_order AS qtyinorder, 
		                      {$invext_prefix}prod_stock_avail AS stockavail, 
		                      {$invext_prefix}prod_qty_to_order AS qtytoorder, 
		                      qtyindemand, 
		                      vendor_part_no 
		               FROM vtiger_products WHERE productid = ?", array($product_id));

	$invdet_info = $adb->fetch_array($adb->pquery("SELECT SUM(invextras_qty_invoiced) AS qty_invoiced 
		                                           FROM vtiger_inventorydetails 
		                                           WHERE related_to = ? 
		                                           AND sequence_no = ? 
		                                           AND productid = ?", array($record_id, $seq, $product_id)));

	if ($adb->num_rows($r) > 0) {
		$data = $adb->fetch_array($r);

		$qty_in_order = CurrencyField::convertToUserFormat($data['qtyinorder']);
		$stock_avail = CurrencyField::convertToUserFormat($data['stockavail']);
		$qty_to_order = CurrencyField::convertToUserFormat($data['qtytoorder']);
		$qty_in_demand = CurrencyField::convertToUserFormat($data['qtyindemand']);
		$qty_invoiced = CurrencyField::convertToUserFormat($invdet_info['qty_invoiced']);

		$qty_in_order_lab = getTranslatedString('LBL_TO_DELIVER_SO', 'InventoryExtras');
		$qty_in_demand_lab = getTranslatedString('LBL_TO_RECEIVE_PO', 'InventoryExtras');
		$qty_invoiced_lab = getTranslatedString('invextras_qty_invoiced', 'InventoryDetails');
		$stock_avail_lab = getTranslatedString($invext_prefix . 'prod_stock_avail', 'Products');
		$qty_to_order_lab = getTranslatedString($invext_prefix . 'prod_qty_to_order', 'Products');
		$ven_part_no_lab = getTranslatedString('Vendor PartNo', 'Products');
		
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
			'qtytoorder' => array(
				'label' => $qty_to_order_lab,
			    'value' => $qty_to_order),
			'venpartno' => array(
				'label' => $ven_part_no_lab,
			    'value' => $data['vendor_part_no']),
			'qtyinvoiced' => array(
				'label' => $qty_invoiced_lab,
			    'value' => $qty_invoiced),
			)
		);
	} else {
		echo "NOTHINGFOUND";
	}
}