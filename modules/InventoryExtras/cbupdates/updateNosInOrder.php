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

class updateNosInOrder extends cbupdaterWorker {

	function applyChange() {
		ini_set('memory_limit', '2048M');
		global $adb, $current_user;
		require_once 'modules/InventoryDetails/InventoryDetails.php';
		require_once 'modules/InventoryExtras/InventoryExtras.php';		

		if ($this->hasError()) $this->sendError();
		if ($this->isApplied()) {
			$this->sendMsg('Changeset '.get_class($this).' already applied!');
		} else {

			$invext = new InventoryExtras();
			$invext_prefix = $invext->getPrefix();			

			$adb->query("UPDATE vtiger_salesorder SET {$invext_prefix}so_no_stock_change = 0");
			$adb->query("UPDATE vtiger_inventorydetails INNER JOIN vtiger_salesorder ON vtiger_inventorydetails.related_to = vtiger_salesorder.salesorderid SET vtiger_inventorydetails.invextras_qty_in_order = (vtiger_inventorydetails.quantity - vtiger_inventorydetails.units_delivered_received)");

			$r = $adb->query("SELECT vtiger_inventorydetails.related_to, 
				                     vtiger_inventorydetails.inventorydetailsid AS id, 
				                     vtiger_inventorydetails.productid AS id, 
				                     vtiger_inventorydetails.{$invext_prefix}so_sibling AS sibl_id 
				              FROM vtiger_inventorydetails 
			                  INNER JOIN vtiger_invoice ON 
			                  vtiger_inventorydetails.related_to = vtiger_invoice.invoiceid 
			                  INNER JOIN vtiger_salesorder ON 
			                  vtiger_invoice.salesorderid = vtiger_salesorder.salesorderid 
			                  INNER JOIN vtiger_crmentity ON 
			                  vtiger_inventorydetails.inventorydetailsid = vtiger_crmentity.crmid 
			                  INNER JOIN vtiger_crmentity crment_inv ON 
			                  vtiger_inventorydetails.related_to = crment_inv.crmid 
			                  INNER JOIN vtiger_crmentity crment_so ON 
			                  vtiger_salesorder.salesorderid = crment_so.crmid 
			                  WHERE vtiger_crmentity.deleted = 0 
			                  AND crment_inv.deleted = 0 
			                  AND crment_so.deleted = 0");

			while($row = $adb->fetch_array($r)) {

				$sibl = $invext->getSiblingFromInvoice($row['related_to'], $row['productid']);
				if (!!$sibl) {
					if ($row['sibl_id'] == '0' || $row['sibl_id'] == '' || $row['sibl_id'] == 0) {
						// This is an invoice line and no related salesorder line has been set yet
						$adb->pquery("UPDATE vtiger_inventorydetails SET {$invext_prefix}so_sibling = ? 
							          WHERE inventorydetailsid = ?", array((int)$sibl['id'], $row['id']));
					}
					$qty_delivered = $invext->getInvoiceQtysFromSoLine($sibl['id']);
					$invext->updateInvDetRec($sibl['id'], $sibl['quantity'], 0, $qty_delivered);
				}	
			}		

			$this->sendMsg('Changeset '.get_class($this).' applied!');
			$this->markApplied();
		}
		$this->finishExecution();
	}

}