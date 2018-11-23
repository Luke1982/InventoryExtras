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

	private $prefix = 'invextras_';

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
		$this->doAddProdFields();
		$this->doAddSoFields();
		$this->doCreateInvDetAfterSaveHandler();
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
		$fld->displaytype = 1;
		$fld->masseditable = 0;

		$blk->addField($fld);
	}

	private function doAddSoFields() {
		require_once 'vtlib/Vtiger/Module.php';
		require_once 'vtlib/Vtiger/Block.php';
		require_once 'vtlib/Vtiger/Field.php';

		$mod = Vtiger_Module::getInstance('SalesOrder');
		$blk = Vtiger_Block::getInstance('LBL_SO_INFORMATION', $mod);

		$fld = new Vtiger_Field();
		$fld->name  = $this->prefix . 'so_no_stock_change';
		$fld->table = 'vtiger_salesorder';
		$fld->column = $this->prefix . 'so_no_stock_change';
		$fld->columntype = 'VARCHAR(3)';
		$fld->helpinfo = 'LBL_HELP_SO_LEAVE_STOCK_ALONE';
		$fld->uitype = 56;
		$fld->typeofdata = 'C~O';
		$fld->displaytype = 1;
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

	private function doCreateInvDetAfterSaveHandler() {
		global $adb;
		require 'include/events/include.inc';
		$em = new VTEventsManager($adb);
		$eventName = 'vtiger.entity.aftersave';
		$filePath = 'modules/InventoryExtras/handlers/AfterInvDetSave.php';
		$className = 'AfterInvDetSave';
		$em->registerHandler($eventName, $filePath, $className);		
	}

	public function getPrefix() {
		return $this->prefix;
	}

	public function getSiblingFromInvoice($invoiceid, $productid) {
		global $adb;

		$r = $adb->pquery("
			SELECT vtiger_inventorydetails.inventorydetailsid AS id, 
			vtiger_inventorydetails.quantity,
			vtiger_inventorydetails.{$this->prefix}inv_sibling FROM 
			vtiger_inventorydetails INNER JOIN vtiger_crmentity ON 
			vtiger_inventorydetails.inventorydetailsid = vtiger_crmentity.crmid WHERE 
			vtiger_inventorydetails.related_to IN (
				SELECT vtiger_invoice.salesorderid FROM vtiger_invoice INNER JOIN vtiger_crmentity ON 
				vtiger_invoice.invoiceid = vtiger_crmentity.crmid WHERE 
				vtiger_crmentity.deleted = ? AND 
				vtiger_invoice.invoiceid = ?
			) 
			AND vtiger_inventorydetails.productid = ? 
			AND vtiger_crmentity.deleted = ?", array(0, $invoiceid, $productid, 0));

		if ($adb->num_rows($r) > 0) {
			return $adb->fetch_array($r);
		} else {
			return false;
		}
	}

	public function updateInvDetRec($invdet_id, $invdet_qty, $sibl_id, $sibl_qty) {
		global $current_user;
		require_once 'modules/InventoryDetails/InventoryDetails.php';

		$id = new InventoryDetails();
		$id->retrieve_entity_info($invdet_id, 'InventoryDetails');
		$id->id = $invdet_id;
		$id->mode = 'edit';

		$id->column_fields[$this->prefix . 'inv_sibling'] = $sibl_id;
		$id->column_fields[$this->prefix . 'qty_in_order'] = (float)$invdet_qty - (float)$sibl_qty;
		$id->column_fields['units_delivered_received'] = $sibl_qty;

		$handler = vtws_getModuleHandlerFromName('InventoryDetails', $current_user);
		$meta = $handler->getMeta();
		$id->column_fields = DataTransform::sanitizeRetrieveEntityInfo($id->column_fields, $meta);

		$id->save('InventoryDetails');		
	}

	public function getInvDetQtyById($id) {
		global $adb;
		$r = $adb->pquery("SELECT quantity FROM vtiger_inventorydetails WHERE inventorydetailsid = ? LIMIT 1", array($id));
		return $adb->fetch_array($r)['quantity'];
	}

}
