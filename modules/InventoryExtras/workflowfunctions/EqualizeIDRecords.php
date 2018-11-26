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

function EqualizeIDRecords($entity) {
	global $adb, $current_user;
	require_once 'modules/InventoryDetails/InventoryDetails.php';

	list($wsid, $id) = explode('x', $entity->id);
	$r = $adb->pquery("SELECT vtiger_inventorydetails.inventorydetailsid, 
		                      vtiger_inventorydetails.quantity, 
		                      vtiger_inventorydetails.units_delivered_received 
		               FROM vtiger_inventorydetails INNER JOIN vtiger_crmentity 
		               ON vtiger_inventorydetails.inventorydetailsid = vtiger_crmentity.crmid 
		               WHERE vtiger_inventorydetails.related_to = ? 
		               AND vtiger_crmentity.deleted = ?", array($id, 0));

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
}