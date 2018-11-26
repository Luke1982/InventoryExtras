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
		global $adb, $current_user;
		require_once 'modules/InventoryDetails/InventoryDetails.php';

		if ($this->hasError()) $this->sendError();
		if ($this->isApplied()) {
			$this->sendMsg('Changeset '.get_class($this).' already applied!');
		} else {

			$r = $adb->query("SELECT vtiger_inventorydetails.related_to, vtiger_inventorydetails.inventorydetailsid AS id 
				              FROM vtiger_inventorydetails 
			                  INNER JOIN vtiger_invoice ON 
			                  vtiger_invoice.invoiceid = vtiger_inventorydetails.related_to 
			                  INNER JOIN vtiger_salesorder ON 
			                  vtiger_invoice.salesorderid = vtiger_salesorder.salesorderid 
			                  INNER JOIN vtiger_crmentity ON 
			                  vtiger_inventorydetails.inventorydetailsid = vtiger_crmentity.crmid 
			                  WHERE vtiger_crmentity.deleted = 0");
			while($row = $adb->fetch_array($r)) {
				$id = new InventoryDetails();
				$id->retrieve_entity_info($row['id'], 'InventoryDetails');
				$id->id = $row['id'];
				$id->mode = 'edit';

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