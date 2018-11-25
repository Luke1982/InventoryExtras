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
Class AfterInvDetSave extends VTEventHandler {
	public function handleEvent($eventName, $entityData){
		global $current_user, $adb;

		$moduleName = $entityData->getModuleName();
		if ($moduleName == 'InventoryDetails') {
			require_once 'modules/InventoryExtras/InventoryExtras.php';

			$invdet_id = $entityData->getId();
			$invdet_data = $entityData->getData();
			$invext = new InventoryExtras();
			$invext_prefix = $invext->getPrefix();

			$related_type = getSalesEntityType($invdet_data['related_to']);
			$related_item = getSalesEntityType($invdet_data['productid']);

			if ($related_item == 'Products') {
				$sibl = $invext->getSiblingFromInvoice($invdet_data['related_to'], $invdet_data['productid']);

				if ($related_type == 'Invoice' && !!$sibl && ($sibl[$invext_prefix . 'inv_sibling'] == '' || $sibl[$invext_prefix . 'inv_sibling'] == '0')) {
					// This line is related to an invoice and a sibling was found. The sibling does not have
					// a related inventorydetails line yet
					$invext->updateInvDetRec($sibl['id'], $sibl['quantity'], $invdet_id, $invdet_data['quantity']);			
				} else if ($related_type == 'Invoice' && !!$sibl && ($sibl[$invext_prefix . 'inv_sibling'] != '' || $sibl[$invext_prefix . 'inv_sibling'] != '0')) {
					// Sibling was found, but it already has a related line
					if ($sibl[$invext_prefix . 'inv_sibling'] == $invdet_id) {
						// Already related to this line, update to be sure
						$invext->updateInvDetRec($sibl['id'], $sibl['quantity'], $invdet_id, $invdet_data['quantity']);
					}
				}

				if ($related_type == 'SalesOrder') {
					if ($invdet_data[$invext_prefix . 'inv_sibling'] == '0' || $invdet_data[$invext_prefix . 'inv_sibling'] == '') {
						// There is no sibling set yet by an invoice
						$invext->updateInvDetRec($invdet_id, $invdet_data['quantity'], 0, 0, true); // true to 'saveentity' and avoid infinite loop
					}
					// Update the related product field with the summ of all invoice lines
					$qty_in_order_tot = $invext->getQtyInOrderByProduct($invdet_data['productid']);
					$invext->updateProductQtyInOrder($invdet_data['productid'], $qty_in_order_tot);
				}
			}
		}
	}
}