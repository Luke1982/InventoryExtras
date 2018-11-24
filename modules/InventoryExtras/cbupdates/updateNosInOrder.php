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
		require_once 'modules/Invoice/Invoice.php';

		if ($this->hasError()) $this->sendError();
		if ($this->isApplied()) {
			$this->sendMsg('Changeset '.get_class($this).' already applied!');
		} else {

			$r = $adb->pquery("SELECT related_to FROM vtiger_inventorydetails WHERE inventorydetailsid = ?", array());
			while($row = $adb->fetch_array($r)) {
				if (getSalesEntityType($row['related_to']) == 'Invoice') {
					$inv = new Invoice();
					$inv->retrieve_entity_info($row['related_to'], 'Invoice');
					$inv->id = $row['related_to'];
					$inv->mode = 'edit';

					$handler = vtws_getModuleHandlerFromName('Invoice', $current_user);
					$meta = $handler->getMeta();
					$inv->column_fields = DataTransform::sanitizeRetrieveEntityInfo($inv->column_fields, $meta);

					$inv->save('Invoice');	
				}
			}			

			$this->sendMsg('Changeset '.get_class($this).' applied!');
			$this->markApplied();
		}
		$this->finishExecution();
	}

}