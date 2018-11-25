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

	private $i18n_so = array(
		'langs' => array('en_us', 'nl_nl'),
		'invextras_so_no_stock_change' => array(
			'en_us' => 'Don\'t affect the stock',
			'nl_nl' => 'Geen invloed op voorraad',
		),
		'LBL_HELP_SO_LEAVE_STOCK_ALONE' => array(
			'en_us' => 'This will avoid lines on related invoices from being linked to lines on this salesorder. It will also avoid the lines on this salesorder to affect the total no. in order on the product.',
			'nl_nl' => 'Als dit aan staat worden er geen factuurregels gezocht die tegenover de regels van de order moeten komen te staan. De regels van deze order hebben dan ook geen invloed op het product (bijvoorbeeld het aantal \'in order\'',
		),
	);
	private $i18n_invdet = array(
		'langs' => array('en_us', 'nl_nl'),
		'invextras_qty_in_order' => array(
			'en_us' => 'Quantity still in order',
			'nl_nl' => 'Aantal nog in order',
		),
		'invextras_inv_sibling' => array(
			'en_us' => 'Invoice sibling line',
			'nl_nl' => 'Gekoppelde factuurregel',
		),
		'LBL_INVDET_SO_INFO' => array(
			'en_us' => 'SalesOrder info (when this line is related to a SalesOrder)',
			'nl_nl' => 'Verkooporder informatie (wanneer deze regel aan een order is gekoppeld)',
		),
		'LBL_HELP_ID_QTY_IN_ORDER' => array(
			'en_us' => 'No. \'still in order\' that this line represents',
			'nl_nl' => 'Aantal \'nog in order\' voor deze regel',
		),
		'LBL_HELP_ID_INV_SIBLING' => array(
			'en_us' => 'This is the line on an invoice that is this line\'s sibling, or \'opposed\'',
			'nl_nl' => 'De regel op een aan deze order gerelateerde factuur die tegenover deze regel staat',
		),
	);
	private $i18n_prod = array(
		'langs' => array('en_us', 'nl_nl'),
		'invextras_prod_qty_in_order' => array(
			'en_us' => 'Qty',
			'nl_nl' => 'Aantal',
		),
		'LBL_HELP_PROD_QTY_IN_ORDER' => array(
			'en_us' => 'The sum of all \'qty\'s in order\' for all lines related to this product.',
			'nl_nl' => 'Het totaal van alle regels waarbij dit product nog \'in order\' staat',
		),
		'LBL_PRODUCT_IN_ORDER_ON' => array(
			'en_us' => 'Product in order on',
			'nl_nl' => 'Product in order op',
		),
		'LBL_NO_ORDERED_PRODS_FOUND' => array(
			'en_us' => 'No orders found that this product is in order on',
			'nl_nl' => 'Geen orders gevonden waarop dit product in order staat',
		),
	);

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
		$this->doUpdateLangFiles();
		$this->doAddProductInOrderOnWidget();
		$this->doInstallcbUpdates();
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
		$fld->displaytype = 1;
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

	private function doInstallcbUpdates() {
		copy('modules/InventoryExtras/cbupdates/InventoryExtras.xml', 'modules/cbupdater/cbupdates/InventoryExtras.xml');
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

		$mod = Vtiger_Module::getInstance('SalesOrder');
		$fld = Vtiger_Field::getInstance($this->prefix . 'so_no_stock_change', $mod);
		if ($fld !== false) $fld->delete();

		// Also remove the columns from InventoryDetails table
		$adb->query("ALTER TABLE vtiger_inventorydetails DROP COLUMN " . $this->prefix . "inv_sibling, DROP COLUMN " . $this->prefix . "qty_in_order");
		$adb->query("ALTER TABLE vtiger_products DROP COLUMN " . $this->prefix . "prod_qty_in_order");
		$adb->query("ALTER TABLE vtiger_salesorder DROP COLUMN " . $this->prefix . "so_no_stock_change");

		$moduleInstance = Vtiger_Module::getInstance('Products');
		$moduleInstance->deleteLink('DETAILVIEWWIDGET', 'LBL_PRODUCT_IN_ORDER_ON', 'module=InventoryExtras&action=InventoryExtrasAjax&file=ProductsInOrderOnWidget&return_module=$MODULE$&record=$RECORD$');		
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

	private function doAddProductInOrderOnWidget() {
		include_once('vtlib/Vtiger/Module.php');
		$mod_acc = Vtiger_Module::getInstance('Products');
		$mod_acc->addLink('DETAILVIEWWIDGET', 'LBL_PRODUCT_IN_ORDER_ON', 'module=InventoryExtras&action=InventoryExtrasAjax&file=ProductsInOrderOnWidget&return_module=$MODULE$&record=$RECORD$');		
	}

	private function doUpdateLangFiles() {
		$this->updateLangFor('SalesOrder', $this->i18n_so);
		$this->updateLangFor('Products', $this->i18n_prod);
		$this->updateLangFor('InventoryDetails', $this->i18n_invdet);
	}

	private function updateLangFor($modulename, $i18n) {
		$langs = $i18n['langs'];
		unset($i18n['langs']);
		foreach ($langs as $lang) {
			$lang_file = 'modules/' . $modulename . '/language/' . $lang . '.custom.php';
			if (file_exists($lang_file)) {
				include $lang_file;
			} else {
				$custom_strings = array();
			}
			foreach ($i18n as $label => $langs) {
				foreach ($langs as $lang => $value) {
					if (strpos($lang_file, $lang) !== false) {
						// Lang exists and we have a translation for it
						if (!array_key_exists($label, $custom_strings)) {
							// We don't have this label yet
							$custom_strings[$label] = $value;
						}
						file_put_contents($lang_file, "<?php\n\$custom_strings = " . var_export($custom_strings, true) . ";");
					}
				}
			}
		}
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
			vtiger_inventorydetails INNER JOIN vtiger_crmentity crment_inv ON 
			vtiger_inventorydetails.inventorydetailsid = crment_inv.crmid 
			INNER JOIN vtiger_crmentity crment_prod ON 
			vtiger_inventorydetails.productid = crment_prod.crmid WHERE 
			vtiger_inventorydetails.related_to IN (
				SELECT vtiger_invoice.salesorderid FROM vtiger_invoice INNER JOIN vtiger_crmentity ON 
				vtiger_invoice.invoiceid = vtiger_crmentity.crmid INNER JOIN vtiger_salesorder ON 
				vtiger_invoice.salesorderid = vtiger_salesorder.salesorderid 
				WHERE 
				vtiger_crmentity.deleted = ? AND 
				vtiger_invoice.invoiceid = ? AND 
				(vtiger_salesorder.{$this->prefix}so_no_stock_change != ? OR 
				vtiger_salesorder.{$this->prefix}so_no_stock_change IS NULL)
			) 
			AND vtiger_inventorydetails.productid = ? 
			AND crment_inv.deleted = ? 
			AND crment_prod.deleted = ?", array(0, $invoiceid, 1, $productid, 0, 0));

		if ($adb->num_rows($r) > 0) {
			return $adb->fetch_array($r);
		} else {
			return false;
		}
	}

	public function updateInvDetRec($invdet_id, $invdet_qty, $sibl_id, $sibl_qty, $saveentity = false) {
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

		if ($saveentity) {
			$id->saveentity('InventoryDetails');
		} else {
			$id->save('InventoryDetails');
		}
				
	}

	public function getInvDetQtyById($id) {
		global $adb;
		$r = $adb->pquery("SELECT quantity FROM vtiger_inventorydetails WHERE inventorydetailsid = ? LIMIT 1", array($id));
		return $adb->fetch_array($r)['quantity'];
	}

	public function getQtyInOrderByProduct($productid) {
		global $adb;
		$r = $adb->pquery("SELECT SUM(vtiger_inventorydetails.{$this->prefix}qty_in_order) AS qty FROM vtiger_inventorydetails 
			INNER JOIN vtiger_crmentity ON 
			vtiger_inventorydetails.inventorydetailsid = vtiger_crmentity.crmid 
			INNER JOIN vtiger_salesorder ON 
			vtiger_salesorder.salesorderid = vtiger_inventorydetails.related_to 
			WHERE vtiger_crmentity.deleted = ? 
			AND vtiger_inventorydetails.productid = ? 
			AND (vtiger_salesorder.{$this->prefix}so_no_stock_change != ? 
			OR vtiger_salesorder.{$this->prefix}so_no_stock_change IS NULL)", array(0, $productid, 1));

		return $adb->fetch_array($r)['qty'];
	}

	public function getTotalInBackOrder($productid) {
		global $adb;
		$r = $adb->pquery("SELECT SUM(vtiger_inventorydetails.quantity - vtiger_inventorydetails.units_delivered_received) AS qty_bo 
			               FROM vtiger_inventorydetails 
			               INNER JOIN vtiger_crmentity crment_id 
			               ON vtiger_inventorydetails.inventorydetailsid = crment_id.crmid 
			               INNER JOIN vtiger_crmentity crment_prod 
			               ON vtiger_inventorydetails.productid = crment_prod.crmid 
			               INNER JOIN vtiger_purchaseorder 
			               ON vtiger_inventorydetails.related_to = vtiger_purchaseorder.purchaseorderid 
			               WHERE crment_id.deleted = ? 
			               AND crment_prod.deleted = ? 
			               AND vtiger_inventorydetails.productid = ?", array(0, 0, $productid));

		return $adb->fetch_array($r)['qty_bo'];
	}

	public function updateProductQtyInOrder($productid, $qty_in_order, $fieldname) {
		global $current_user;
		require_once 'modules/Products/Products.php';

		$p = new Products();
		$p->retrieve_entity_info($productid, 'Products');
		$p->id = $productid;
		$p->mode = 'edit';

		$p->column_fields[$fieldname] = $qty_in_order;

		$handler = vtws_getModuleHandlerFromName('Products', $current_user);
		$meta = $handler->getMeta();
		$p->column_fields = DataTransform::sanitizeRetrieveEntityInfo($p->column_fields, $meta);

		if (file_exists('modules/ExactOnline/ExactOnline.php')) {
			$p->saveentity('Products');
		} else {
			$p->save('Products');
		}
	}


}
