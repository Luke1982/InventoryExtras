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

class updateQtysInDemand extends cbupdaterWorker {

	function applyChange() {
		global $adb, $current_user;
		require_once 'modules/InventoryExtras/InventoryExtras.php';
		require_once 'modules/InventoryDetails/InventoryDetails.php';

		if ($this->hasError()) $this->sendError();
		if ($this->isApplied()) {
			$this->sendMsg('Changeset '.get_class($this).' already applied!');
		} else {
			$r = $adb->pquery("SELECT vtiger_products.productid FROM vtiger_products 
				               INNER JOIN vtiger_crmentity ON 
				               vtiger_products.productid = vtiger_crmentity.crmid 
				               WHERE vtiger_crmentity.deleted = ? 
				               AND vtiger_products.discontinued = ?", array(0, 1));

			$ie = new InventoryExtras();
			while ($prod = $adb->fetch_array($r)) {
				$qty_in_backord_tot = $ie->getTotalInBackOrder($prod['productid']);
				$ie->updateProductQtyInOrder($prod['productid'], $qty_in_backord_tot, 'qtyindemand');
			}

			// Update all Purchase
			$r = $adb->pquery("SELECT vtiger_inventorydetails.inventorydetailsid FROM 
				               vtiger_inventorydetails INNER JOIN vtiger_purchaseorder 
				               ON vtiger_inventorydetails.related_to = vtiger_purchaseorder.purchaseorderid 
				               INNER JOIN vtiger_crmentity crment_po 
				               ON vtiger_inventorydetails.related_to = crment_po.crmid 
				               INNER JOIN vtiger_crmentity crment_id 
				               ON vtiger_inventorydetails.inventorydetailsid = crment_id.crmid 
				               INNER JOIN vtiger_crmentity crment_prod 
				               ON vtiger_inventorydetails.productid = crment_prod.crmid 
				               WHERE vtiger_purchaseorder.postatus = ? 
				               AND crment_po.deleted = ? 
				               AND crment_id.deleted = ?
				               AND crment_prod.deleted = ?", array('Received Shipment', 0, 0, 0));

			while ($id_record = $adb->fetch_array($r)) {
				$id = new InventoryDetails();
				$id->retrieve_entity_info($id_record['inventorydetailsid'], 'InventoryDetails');
				$id->id = $id_record['inventorydetailsid'];
				$id->mode = 'edit';

				$id->column_fields['units_delivered_received'] = $id->column_fields['quantity'];

				$handler = vtws_getModuleHandlerFromName('InventoryDetails', $current_user);
				$meta = $handler->getMeta();
				$id->column_fields = DataTransform::sanitizeRetrieveEntityInfo($id->column_fields, $meta);

				$id->save('InventoryDetails');
			}

			$this->sendMsg('Changeset '.get_class($this).' applied!');
			$this->markApplied();
		}
		$this->finishExecution();
	}

}