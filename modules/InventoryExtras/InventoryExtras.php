<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
Class InventoryExtras {

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
	 */
	function vtlib_handler($modulename, $event_type) {
		if($event_type == 'module.postinstall') {
			$this->doPostInstall();
		} else if($event_type == 'module.disabled') {
			// TODO Handle actions when this module is disabled.
		} else if($event_type == 'module.enabled') {
			// TODO Handle actions when this module is enabled.
		} else if($event_type == 'module.preuninstall') {
			// TODO Handle actions when this module is about to be deleted.
			$this->removeThisModule();
		} else if($event_type == 'module.preupdate') {
			// TODO Handle actions before this module is updated.
		} else if($event_type == 'module.postupdate') {
			// TODO Handle actions after this module is updated.
		}
	}

	private function doPostInstall() {
		$this->doAddInvDetBlockAndFields();
	}

	private function doAddInvDetBlockAndFields() {
		require_once 'vtlib/Vtiger/Module.php';
		require_once 'vtlib/Vtiger/Block.php';
		require_once 'vtlib/Vtiger/Field.php';

		$mod = Vtiger_Module::getInstance('InventoryDetails');
		$blk = new Vtiger_Block();

		$blk->label = 'LBL_INVDET_SO_INFO';

		$fld = new Vtiger_Field();
		$fld->name  = 'inventoryextras_qty_in_demand';
		$fld->table = 'vtiger_inventorydetails';
		$fld->column = 'inventoryextras_qty_in_demand';
		$fld->helpinfo = 'LBL_HELP_ID_QTY_IN_DEMAND';
		$fld->uitype = 7;
		$fld->typeofdata = 'N~O';
		$fld->presence = 0;
		$fld->displaytype = 2;
		$fld->masseditable = 0;

		$blk->addField($fld);

		$fld = new Vtiger_Field();
		$fld->name  = 'inventoryextras_inv_sibling';
		$fld->table = 'vtiger_inventorydetails';
		$fld->column = 'inventoryextras_inv_sibling';
		$fld->helpinfo = 'LBL_HELP_ID_INV_SIBLING';
		$fld->uitype = 10;
		$fld->typeofdata = 'I~O';
		$fld->displaytype = 1;
		$fld->masseditable = 0;
		$fld->setRelatedModules('InventoryDetails');

		$blk->addField($fld);

		$blk->save($mod);		
	}

	private function removeThisModule() {
		global $adb;
		require_once 'vtlib/Vtiger/Block.php';

		$blk = Vtiger_Block::getInstance('LBL_INVDET_SO_INFO');
		$blk->delete(true);

		// Also remove the columns from InventoryDetails table
		$adb->query("ALTER TABLE vtiger_inventorydetails DROP COLUMN inventoryextras_inv_sibling, DROP COLUMN inventoryextras_qty_in_demand");
	}

}
