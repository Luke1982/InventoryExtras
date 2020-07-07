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
Class InvExtrasAfterSaveFirst extends VTEventHandler {
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

				if ($related_type == 'Invoice') {

					$sibl = $invext->getSiblingFromInvoice($invdet_data['related_to'], $invdet_data['productid']);
					if (!!$sibl) {
						if ($invdet_data[$invext_prefix . 'so_sibling'] == '0' || $invdet_data[$invext_prefix . 'so_sibling'] == '') {
							// This is an invoice line and no related salesorder line has been set yet
							$adb->pquery("UPDATE vtiger_inventorydetails SET {$invext_prefix}so_sibling = ? 
								          WHERE inventorydetailsid = ?", array((int)$sibl['id'], $invdet_id));
						}
						$qty_invoiced = $invext->getInvoiceQtysFromSoLine($sibl['id']);
						$invext->updateInvDetRec($sibl['id'], $sibl['quantity'], 0, $qty_invoiced, false, 'invoiced');
					}

				} else if ($related_type == 'SalesOrder') {

					$pot_inv_lines = $invext->getPotentialInvoiceLinesFor($invdet_id);
					if (!!$pot_inv_lines) {
						foreach ($pot_inv_lines as $invoicelineid) {
							$adb->pquery("UPDATE vtiger_inventorydetails SET invextras_so_sibling = ? WHERE inventorydetailsid = ?", array($invdet_id, $invoicelineid));
							$em = new VTEventsManager($adb);
							$invoicelineData = VTEntityData::fromEntityId($adb, $invoicelineid);
							$em->triggerEvent('vtiger.entity.aftersave.first', $invoicelineData);
						}
					}
					if ($invext->getInvoiceQtysFromSoLine($invdet_id) == 0) {
						// There were no invoice lines related to this salesorder line
						$invext->updateInvDetRec($invdet_id, $invdet_data['quantity'], 0, 0, true, 'invoiced');
					} else {
						// There were invoice lines found, update to be sure
						$qty_delivered = $invext->getInvoiceQtysFromSoLine($invdet_id);
						$invext->updateInvDetRec($invdet_id, $invdet_data['quantity'], 0, $qty_delivered, true, 'invoiced');
					}
					$qty_in_order_tot = $invext->getQtyInOrderByProduct($invdet_data['productid']);					
					$invext->updateProductQtyInOrder($invdet_data['productid'], $qty_in_order_tot, $invext_prefix . 'prod_qty_in_order', $related_type);

				} else if ($related_type == 'PurchaseOrder') {

					$qty_in_backord_tot = $invext->getTotalInBackOrder($invdet_data['productid']);
					$invext->updateProductQtyInOrder($invdet_data['productid'], $qty_in_backord_tot, 'qtyindemand');

				}

			}

		} else if ($moduleName == 'SalesOrder' && $_REQUEST['action'] == 'SalesOrderAjax' && $_REQUEST['file'] == 'DetailViewAjax') {

			// Inventorydetails lines don't get saved when a related record (SO, PO, etc) is edited inline. Therefor the code above
			// won't work. We need to catch some events here that we know could affect our calculations
			global $adb, $current_user;
			require_once 'modules/InventoryDetails/InventoryDetails.php';
			require_once 'data/VTEntityDelta.php';		

			$delta_manager = new VTEntityDelta();
			$so_id = $entityData->getId();
			$so_data = $entityData->getData();
			$delta = $delta_manager->getEntityDelta('SalesOrder', $entityData->getId());

			if ($delta && array_key_exists('invextras_so_no_stock_change', $delta)) {
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