<?php
/*************************************************************************************************
 * Copyright 2020 MajorLabel -- This file is a part of MajorLabel coreBOS Customizations.
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
class InvExtrasAfterDelete extends VTEventHandler {
	public function handleEvent($eventName, $entityData) {
		global $current_user, $adb;

		$moduleName = $entityData->getModuleName();
		if ($moduleName == 'InventoryDetails') {
			$invdet_data = $entityData->getData();
			$related_type = getSalesEntityType($invdet_data['related_to']);
			if ($related_type == 'SalesOrder') {
				require 'include/events/include.inc';
				$em = new VTEventsManager($adb);
				$invdet_id = $entityData->getId();
				$r = $adb->pquery("SELECT inventorydetailsid FROM vtiger_inventorydetails WHERE invextras_so_sibling = ?", array($invdet_id));
				if ($adb->num_rows($r) > 0) {
					$invoice_sibling_id = $adb->query_result($r, 0, 'inventorydetailsid');
					$adb->query("UPDATE vtiger_inventorydetails SET invextras_so_sibling=0 WHERE inventorydetailsid = {$invoice_sibling_id}");
				}
			}
		}
	}
}