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
Class InvExtrasAfterSave extends VTEventHandler {
	public function handleEvent($eventName, $entityData){
		global $current_user, $adb;

		$moduleName = $entityData->getModuleName();
		if ($moduleName == 'SalesOrder' && $_REQUEST['action'] == 'SalesOrderAjax' && $_REQUEST['file'] == 'DetailViewAjax') {

			global $adb, $current_user;
			require_once 'modules/InventoryDetails/InventoryDetails.php';
			require_once 'data/VTEntityDelta.php';		

			$delta_manager = new VTEntityDelta();
			$so_id = $entityData->getId();
			$so_data = $entityData->getData();
			$delta = $delta_manager->getEntityDelta('SalesOrder', $entityData->getId());

			if (array_key_exists('invextras_so_no_stock_change', $delta)) {
				// Only fire when so_no_stock_change checkbox was altered
				$r = $adb->pquery("SELECT vtiger_inventorydetails.inventorydetailsid AS id FROM vtiger_inventorydetails 
					               INNER JOIN vtiger_crmentity ON 
					               vtiger_inventorydetails.inventorydetailsid = vtiger_crmentity.crmid 
					               WHERE vtiger_inventorydetails.related_to = ? 
					               AND vtiger_crmentity.deleted = ?", array($so_id, 0));

				while ($invdet = $adb->fetch_array($r)) {
					$invdet_id = $invdet['id'];

					$id = new InventoryDetails();
					$id->retrieve_entity_info($invdet_id, 'InventoryDetails');
					$id->id = $invdet_id;
					$id->mode = 'edit';

					$handler = vtws_getModuleHandlerFromName('InventoryDetails', $current_user);
					$meta = $handler->getMeta();
					$id->column_fields = DataTransform::sanitizeRetrieveEntityInfo($id->column_fields, $meta);

					$id->save('InventoryDetails');				
				}
			}

		} else if ($moduleName == 'PurchaseOrder' && $_REQUEST['action'] == 'PurchaseOrderAjax' && $_REQUEST['file'] == 'DetailViewAjax') {

			// Be sure to update the product in demand field when a purchaseorder status changes through inline edit
			global $adb, $current_user;
			require_once 'modules/InventoryExtras/InventoryExtras.php';
			require_once 'data/VTEntityDelta.php';

			$invext = new InventoryExtras();
			$delta_manager = new VTEntityDelta();
			$po_id = $entityData->getId();
			$po_data = $entityData->getData();
			$delta = $delta_manager->getEntityDelta('PurchaseOrder', $entityData->getId());

			if ($delta && array_key_exists('postatus', $delta)) {
				// Only fire when postatus was altered
				$r = $adb->pquery("SELECT vtiger_inventorydetails.productid FROM vtiger_inventorydetails 
					               INNER JOIN vtiger_crmentity ON 
					               vtiger_inventorydetails.inventorydetailsid = vtiger_crmentity.crmid 
								   INNER JOIN vtiger_crmentity crment_pr ON
								   vtiger_inventorydetails.productid = crment_pr.crmid
								   INNER JOIN vtiger_products ON
								   vtiger_inventorydetails.productid = vtiger_products.productid
					               WHERE vtiger_inventorydetails.related_to = ? 
								   AND vtiger_crmentity.deleted = ?
								   AND crment_pr.deleted = ?", array($po_id, 0, 0));

				while ($prod = $adb->fetch_array($r)) {
					$qty_in_backord_tot = $invext->getTotalInBackOrder($prod['productid']);
					$invext->updateProductQtyInOrder($prod['productid'], $qty_in_backord_tot, 'qtyindemand');
				}
			}
		}
	}
}