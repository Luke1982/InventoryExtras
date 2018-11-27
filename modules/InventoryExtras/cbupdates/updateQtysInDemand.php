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

			$this->sendMsg('Changeset '.get_class($this).' applied!');
			$this->markApplied();
		}
		$this->finishExecution();
	}

}