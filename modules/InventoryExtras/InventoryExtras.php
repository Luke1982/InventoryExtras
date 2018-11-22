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

	$prefix = 'invextras_';

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
		ini_set('display_errors', 1);
		error_reporting(E_ALL);

		$this->doAddInvDetBlockAndFields();
		// $this->doAddProdFields();
	}

	private function doAddInvDetBlockAndFields() {
		require_once 'vtlib/Vtiger/Module.php';
		require_once 'vtlib/Vtiger/Block.php';
		require_once 'vtlib/Vtiger/Field.php';

		$mod = Vtiger_Module::getInstance('InventoryDetails');
		$blk = new Vtiger_Block();

		$blk->label = 'LBL_INVDET_SO_INFO';
		$blk->save($mod);

		$fld = new Vtiger_Field();
		$fld->name  = $this->prefix . 'qty_in_order';
		$fld->table = 'vtiger_inventorydetails';
		$fld->column = $this->prefix . 'qty_in_order';
		$fld->columntype = 'INT(11)';
		$fld->helpinfo = 'LBL_HELP_ID_QTY_IN_ORDER';
		$fld->uitype = 7;
		$fld->typeofdata = 'N~O';
		$fld->presence = 0;
		$fld->displaytype = 2;
		$fld->masseditable = 0;

		$blk->addField($fld);

		$fld = new Vtiger_Field();
		$fld->name  = $this->prefix . 'inv_sibling';
		$fld->table = 'vtiger_inventorydetails';
		$fld->column = $this->prefix . 'inv_sibling';
		$fld->columntype = 'INT(11)';
		$fld->helpinfo = 'LBL_HELP_ID_INV_SIBLING';
		$fld->uitype = 10;
		$fld->typeofdata = 'I~O';
		$fld->displaytype = 1;
		$fld->masseditable = 0;

		$blk->addField($fld);
		$fld->setRelatedModules('InventoryDetails');
	}

	private function doAddProdFields() {
		require_once 'vtlib/Vtiger/Module.php';
		require_once 'vtlib/Vtiger/Block.php';
		require_once 'vtlib/Vtiger/Field.php';

		$mod = Vtiger_Module::getInstance('Products');
		$blk = Vtiger_Block::getInstance('LBL_STOCK_INFORMATION', $mod);

		$fld = new Vtiger_Field();
		$fld->name  = $this->prefix . 'prod_qty_in_order';
		$fld->table = 'vtiger_products';
		$fld->column = $this->prefix . 'prod_qty_in_order';
		$fld->columntype = 'INT(11)';
		$fld->helpinfo = 'LBL_HELP_PROD_QTY_IN_ORDER';
		$fld->uitype = 7;
		$fld->typeofdata = 'N~O';
		$fld->displaytype = 2;
		$fld->masseditable = 0;

		$blk->addField($fld);
	}

	private function removeThisModule() {
		global $adb;
		require_once 'vtlib/Vtiger/Module.php';
		require_once 'vtlib/Vtiger/Block.php';
		require_once 'vtlib/Vtiger/Field.php';

		$mod = Vtiger_Module::getInstance('InventoryDetails');
		$blk = Vtiger_Block::getInstance('LBL_INVDET_SO_INFO', $mod);
		if ($blk !== false) $blk->delete(true);

		$mod = Vtiger_Module::getInstance('Products');
		$fld = Vtiger_Field::getInstance($this->prefix . 'prod_qty_in_order', $mod);
		if ($fld !== false) $fld->delete();

		// Also remove the columns from InventoryDetails table
		$adb->query("ALTER TABLE vtiger_inventorydetails DROP COLUMN " . $this->prefix . "inv_sibling, DROP COLUMN " . $this->prefix . "qty_in_order");
		$adb->query("ALTER TABLE vtiger_products DROP COLUMN " . $this->prefix . "prod_qty_in_order");
	}

}
