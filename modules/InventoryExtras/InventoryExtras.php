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
			'nl_nl' => 'Mutatie voorraad buiten beschouwing laten',
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
		'invextras_so_sibling' => array(
			'en_us' => 'Salesorder sibling line',
			'nl_nl' => 'Gekoppelde orderregel',
		),
		'invextras_qty_invoiced' => array(
			'en_us' => 'Qty invoiced',
			'nl_nl' => 'Aantal gefactureerd',
		),
		'LBL_INVDET_SO_INFO' => array(
			'en_us' => 'SalesOrder info (when related)',
			'nl_nl' => 'Verkooporder informatie (wanneer gekoppeld)',
		),
		'LBL_HELP_ID_QTY_IN_ORDER' => array(
			'en_us' => 'No. \'still in order\' that this line represents',
			'nl_nl' => 'Aantal \'nog in order\' voor deze regel',
		),
		'LBL_HELP_ID_SO_SIBLING' => array(
			'en_us' => 'The opposed salesorder line',
			'nl_nl' => 'De gekoppelde verkooporder regel',
		),
		'LBL_HELP_ID_QTY_INVOICED' => array(
			'en_us' => 'The quantity that has been invoiced opposed to this line (only applicable when this is a SalesOrder line',
			'nl_nl' => 'Het aantal dat van deze regel gefactureerd is (alleen van toepassing als dit een verkooporder regel is',
		),
	);
	private $i18n_prod = array(
		'langs' => array('en_us', 'nl_nl'),
		'invextras_prod_qty_in_order' => array(
			'en_us' => 'Qty in order',
			'nl_nl' => 'Aantal in order',
		),
		'invextras_prod_stock_avail' => array(
			'en_us' => 'Available stock',
			'nl_nl' => 'Beschikbare voorraad',
		),
		'invextras_prod_qty_to_order' => array(
			'en_us' => 'Quantity to order',
			'nl_nl' => 'Aantal te bestellen',
		),
		'invextras_prod_max_stock' => array(
			'en_us' => 'Maximum stock',
			'nl_nl' => 'Maximale voorraad',
		),
		'LBL_HELP_PROD_QTY_IN_ORDER' => array(
			'en_us' => 'The sum of all \'qty\'s in order\' for all lines related to this product.',
			'nl_nl' => 'Het totaal van alle regels waarbij dit product nog \'in order\' staat',
		),
		'LBL_HELP_PROD_QTY_TO_ORDER' => array(
			'en_us' => 'The quantity you should order to meet all orders in respect to the pending qty\'s in order',
			'nl_nl' => 'Het aantal dat besteld moet worden om aan alle orders te voldoen',
		),
		'LBL_HELP_PROD_MAX_STOCK' => array(
			'en_us' => 'The maximum amount you want to have in stock for this product',
			'nl_nl' => 'Het maximale aantal dat u voor dit product in voorraad wilt hebben',
		),
		'LBL_HELP_PROD_STOCK_AVAIL' => array(
			'en_us' => 'Stock realistically available, difference between stock and qty in order',
			'nl_nl' => 'Realistisch beschikbare voorraad, verschil tussen voorraad en aantal in order',
		),
		'LBL_PRODUCT_IN_ORDER_ON' => array(
			'en_us' => 'Product in order on',
			'nl_nl' => 'Product in order op',
		),
		'LBL_PRODUCT_IN_BACKORDER_ON' => array(
			'en_us' => 'Product in backorder on',
			'nl_nl' => 'Product in backorder op',
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
			$this->doInstallcbUpdates();
		}
	}

	private function doPostInstall() {
		$this->doAddInvDetBlockAndFields();
		$this->doAddProdFields();
		$this->doAddSoFields();
		$this->doCreateInvDetAfterSaveHandlers();
		$this->doUpdateLangFiles();
		$this->doAddProductInOrderOnWidget();
		$this->doCreateWorkflowFunction();
		$this->doAddProductInBackOrderOnWidget();
		$this->doAddInventoryExtrasHeaderScript();
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
		$fld->columntype = 'DECIMAL(28,3)';
		$fld->helpinfo = 'LBL_HELP_ID_QTY_IN_ORDER';
		$fld->uitype = 7;
		$fld->typeofdata = 'NN~O';
		$fld->presence = 0;
		$fld->displaytype = 1;
		$fld->masseditable = 0;

		$blk->addField($fld);

		$fld = new Vtiger_Field();
		$fld->name  = $this->prefix . 'qty_invoiced';
		$fld->table = 'vtiger_inventorydetails';
		$fld->column = $this->prefix . 'qty_invoiced';
		$fld->columntype = 'DECIMAL(28,3)';
		$fld->helpinfo = 'LBL_HELP_ID_QTY_INVOICED';
		$fld->uitype = 7;
		$fld->typeofdata = 'NN~O';
		$fld->presence = 0;
		$fld->displaytype = 1;
		$fld->masseditable = 0;

		$blk->addField($fld);

		$fld = new Vtiger_Field();
		$fld->name  = $this->prefix . 'so_sibling';
		$fld->table = 'vtiger_inventorydetails';
		$fld->column = $this->prefix . 'so_sibling';
		$fld->columntype = 'INT(11)';
		$fld->helpinfo = 'LBL_HELP_ID_SO_SIBLING';
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
		$fld->columntype = 'DECIMAL(28,3)';
		$fld->helpinfo = 'LBL_HELP_PROD_QTY_IN_ORDER';
		$fld->uitype = 7;
		$fld->typeofdata = 'NN~O';
		$fld->displaytype = 1;
		$fld->masseditable = 0;

		$blk->addField($fld);

		$fld = new Vtiger_Field();
		$fld->name  = $this->prefix . 'prod_stock_avail';
		$fld->table = 'vtiger_products';
		$fld->column = $this->prefix . 'prod_stock_avail';
		$fld->columntype = 'DECIMAL(28,3)';
		$fld->helpinfo = 'LBL_HELP_PROD_STOCK_AVAIL';
		$fld->uitype = 7;
		$fld->typeofdata = 'NN~O';
		$fld->displaytype = 1;
		$fld->masseditable = 0;

		$blk->addField($fld);

		$fld = new Vtiger_Field();
		$fld->name  = $this->prefix . 'prod_qty_to_order';
		$fld->table = 'vtiger_products';
		$fld->column = $this->prefix . 'prod_qty_to_order';
		$fld->columntype = 'DECIMAL(28,3)';
		$fld->helpinfo = 'LBL_HELP_PROD_QTY_TO_ORDER';
		$fld->uitype = 7;
		$fld->typeofdata = 'NN~O';
		$fld->displaytype = 1;
		$fld->masseditable = 0;

		$blk->addField($fld);

		$fld = new Vtiger_Field();
		$fld->name  = $this->prefix . 'prod_max_stock';
		$fld->table = 'vtiger_products';
		$fld->column = $this->prefix . 'prod_max_stock';
		$fld->columntype = 'DECIMAL(28,3)';
		$fld->helpinfo = 'LBL_HELP_PROD_MAX_STOCK';
		$fld->uitype = 7;
		$fld->typeofdata = 'NN~O';
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
		require 'include/events/include.inc';

		$mod = Vtiger_Module::getInstance('InventoryDetails');
		$blk = Vtiger_Block::getInstance('LBL_INVDET_SO_INFO', $mod);
		if ($blk !== false) $blk->delete(true);

		$mod = Vtiger_Module::getInstance('Products');
		$fld = Vtiger_Field::getInstance($this->prefix . 'prod_qty_in_order', $mod);
		if ($fld !== false) $fld->delete();
		$fld = Vtiger_Field::getInstance($this->prefix . 'prod_stock_avail', $mod);
		if ($fld !== false) $fld->delete();
		$fld = Vtiger_Field::getInstance($this->prefix . 'prod_qty_to_order', $mod);
		if ($fld !== false) $fld->delete();
		$fld = Vtiger_Field::getInstance($this->prefix . 'prod_max_stock', $mod);
		if ($fld !== false) $fld->delete();

		$mod = Vtiger_Module::getInstance('SalesOrder');
		$fld = Vtiger_Field::getInstance($this->prefix . 'so_no_stock_change', $mod);
		if ($fld !== false) $fld->delete();

		$this->doRemoveWorkflowFunction();

		// Also remove the columns from InventoryDetails table
		$adb->query("ALTER TABLE vtiger_inventorydetails DROP COLUMN " . $this->prefix . "so_sibling, DROP COLUMN " . $this->prefix . "qty_in_order");
		$adb->query("ALTER TABLE vtiger_inventorydetails DROP COLUMN " . $this->prefix . "qty_invoiced");
		$adb->query("ALTER TABLE vtiger_products DROP COLUMN " . $this->prefix . "prod_qty_in_order");
		$adb->query("ALTER TABLE vtiger_products DROP COLUMN " . $this->prefix . "prod_stock_avail");
		$adb->query("ALTER TABLE vtiger_products DROP COLUMN " . $this->prefix . "prod_qty_to_order");
		$adb->query("ALTER TABLE vtiger_products DROP COLUMN " . $this->prefix . "prod_max_stock");
		$adb->query("ALTER TABLE vtiger_salesorder DROP COLUMN " . $this->prefix . "so_no_stock_change");

		$moduleInstance = Vtiger_Module::getInstance('Products');
		$moduleInstance->deleteLink('DETAILVIEWWIDGET', 'LBL_PRODUCT_IN_ORDER_ON', 'module=InventoryExtras&action=InventoryExtrasAjax&file=ProductsInOrderOnWidget&return_module=$MODULE$&record=$RECORD$');
		$moduleInstance->deleteLink('DETAILVIEWWIDGET', 'LBL_PRODUCT_IN_BACKORDER_ON', 'module=InventoryExtras&action=InventoryExtrasAjax&file=ProductsInBackOrderOnWidget&return_module=$MODULE$&record=$RECORD$');
		$moduleInstance = Vtiger_Module::getInstance('InventoryExtras');
		$moduleInstance->deleteLink('HEADERSCRIPT', 'InventoryExtrasHeaderScript', 'modules/InventoryExtras/InventoryExtras.js');

		$em = new VTEventsManager($adb);
		$em->unregisterHandler('InvExtrasAfterSaveFirst');
		$em->unregisterHandler('InvExtrasAfterSave');
	}

	private function doCreateInvDetAfterSaveHandlers() {
		global $adb;
		require 'include/events/include.inc';

		$em = new VTEventsManager($adb);
		$eventName = 'vtiger.entity.aftersave.first';
		$filePath = 'modules/InventoryExtras/handlers/InvExtrasAfterSaveFirst.php';
		$className = 'InvExtrasAfterSaveFirst';
		$em->registerHandler($eventName, $filePath, $className);

		$em = new VTEventsManager($adb);
		$eventName = 'vtiger.entity.aftersave';
		$filePath = 'modules/InventoryExtras/handlers/InvExtrasAfterSave.php';
		$className = 'InvExtrasAfterSave';
		$em->registerHandler($eventName, $filePath, $className);
	}

	private function doAddProductInOrderOnWidget() {
		include_once('vtlib/Vtiger/Module.php');
		$mod_acc = Vtiger_Module::getInstance('Products');
		$mod_acc->addLink('DETAILVIEWWIDGET', 'LBL_PRODUCT_IN_ORDER_ON', 'module=InventoryExtras&action=InventoryExtrasAjax&file=ProductsInOrderOnWidget&return_module=$MODULE$&record=$RECORD$');		
	}

	private function doAddProductInBackOrderOnWidget() {
		include_once('vtlib/Vtiger/Module.php');
		$mod_acc = Vtiger_Module::getInstance('Products');
		$mod_acc->addLink('DETAILVIEWWIDGET', 'LBL_PRODUCT_IN_BACKORDER_ON', 'module=InventoryExtras&action=InventoryExtrasAjax&file=ProductsInBackOrderOnWidget&return_module=$MODULE$&record=$RECORD$');		
	}

	private function doAddInventoryExtrasHeaderScript() {
		include_once('vtlib/Vtiger/Module.php');
		$mod_acc = Vtiger_Module::getInstance('InventoryExtras');
		$mod_acc->addLink('HEADERSCRIPT', 'InventoryExtrasHeaderScript', 'modules/InventoryExtras/InventoryExtras.js');		
	}

	private function doUpdateLangFiles() {
		$this->updateLangFor('SalesOrder', $this->i18n_so);
		$this->updateLangFor('Products', $this->i18n_prod);
		$this->updateLangFor('InventoryDetails', $this->i18n_invdet);
	}

	private function doCreateWorkflowFunction() {
		require_once 'include/utils/utils.php';
		include_once('vtlib/Vtiger/Module.php');
		require 'modules/com_vtiger_workflow/VTEntityMethodManager.inc';
		global $adb;
		$emm = new VTEntityMethodManager($adb);
		$emm->addEntityMethod("PurchaseOrder", "Equalize related InventoryDetails records", "modules/InventoryExtras/workflowfunctions/EqualizeIDRecords.php", "EqualizeIDRecords");
		$emm->addEntityMethod("SalesOrder", "Equalize related InventoryDetails records", "modules/InventoryExtras/workflowfunctions/EqualizeIDRecords.php", "EqualizeIDRecords");
		$emm->addEntityMethod("Invoice", "Equalize related InventoryDetails records", "modules/InventoryExtras/workflowfunctions/EqualizeIDRecords.php", "EqualizeIDRecords");
	}

	private function doRemoveWorkflowFunction() {
		require_once 'include/utils/utils.php';
		include_once('vtlib/Vtiger/Module.php');
		require 'modules/com_vtiger_workflow/VTEntityMethodManager.inc';
		global $adb;
		$emm = new VTEntityMethodManager($adb);
		$emm->removeEntityMethod("PurchaseOrder", "Equalize related InventoryDetails records");
		$emm->removeEntityMethod("SalesOrder", "Equalize related InventoryDetails records");
		$emm->removeEntityMethod("Invoice", "Equalize related InventoryDetails records");
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

	private function createInventoryMutation($column_fields) {
		if ($column_fields['units_delrec_mutated'] != 0) {
			global $current_user;
			require_once 'modules/InventoryMutations/InventoryMutations.php';
			$im = new InventoryMutations();
			$im->mode = 'create';

			$im->column_fields['units_delrec_before'] = $column_fields['units_delrec_before'];
			$im->column_fields['units_delrec_mutated'] = $column_fields['units_delrec_mutated'];
			$im->column_fields['units_delrec_after'] = $column_fields['units_delrec_after'];
			$im->column_fields['invmut_inventorydetails_id'] = $column_fields['invmut_inventorydetails_id'];
			$im->column_fields['invmut_source_id'] = $column_fields['invmut_source_id'];
			$im->column_fields['invmut_product_id'] = $column_fields['invmut_product_id'];

			$handler = vtws_getModuleHandlerFromName('InventoryMutations', $current_user);
			$meta = $handler->getMeta();
			$im->column_fields = DataTransform::sanitizeRetrieveEntityInfo($im->column_fields, $meta);
			$im->save('InventoryMutations');
		}	
	}

	private function getSoNoStockChange($invdet_id) {
		global $adb;
		$r = $adb->pquery("SELECT vtiger_salesorder.{$this->prefix}so_no_stock_change AS flag 
			               FROM vtiger_salesorder INNER JOIN vtiger_inventorydetails ON 
                           vtiger_salesorder.salesorderid = vtiger_inventorydetails.related_to 
			               WHERE vtiger_inventorydetails.inventorydetailsid = ?", array($invdet_id));

		if ($adb->num_rows($r) > 0) {
			$flag = $adb->fetch_array($r)['flag'];
			if ($flag == 0 || $flag == '0') {
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}	

	public function getPrefix() {
		return $this->prefix;
	}

	public function getSiblingFromInvoice($invoiceid, $productid) {
		global $adb;

		$r = $adb->pquery("
			SELECT vtiger_inventorydetails.inventorydetailsid AS id, 
			vtiger_inventorydetails.quantity FROM 
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

	/*
	 * Method: update an inventorydetails record
	 *
	 * @param : record ID of the inventorydetails record you want to update
	 * @param : quantity you want the units delivered deducted from
	 * @param : The inventorydetails record ID that you want to set as this records' sibling
	 * @param : The units delivered
	 * @param : (bool) Use 'save' mothod or 'saveentity' (avoid other aftersave events and workflows)
	 * @param : (string) The type of delivery ('invoiced'/'delivered') decides which field the $units_del will set
	 */
	public function updateInvDetRec($invdet_id, $invdet_qty, $sibl_id, $units_del, $saveentity = false, $deliver_type = 'delivered') {
		global $current_user;
		require_once 'modules/InventoryDetails/InventoryDetails.php';
		require_once 'include/utils/VtlibUtils.php';

		$id = new InventoryDetails();
		$id->retrieve_entity_info($invdet_id, 'InventoryDetails');
		$id->id = $invdet_id;
		$id->mode = 'edit';

		if (vtlib_isModuleActive('InventoryMutations') && $saveentity && $deliver_type == 'delivered') {
			require_once 'modules/InventoryMutations/InventoryMutations.php';
			// create inventorymutations record since saveentity doesn't call the aftersave
			$this->createInventoryMutation(array(
				'units_delrec_before' => $id->column_fields['units_delivered_received'],
				'units_delrec_mutated' => $units_del,
				'units_delrec_after' => $id->column_fields['units_delivered_received'] - $units_del,
				'invmut_inventorydetails_id' => $id->id,
				'invmut_source_id' => $id->column_fields['related_to'],
				'invmut_product_id' => $id->column_fields['productid'],
			));
		}

		$id->column_fields[$this->prefix . 'so_sibling'] = $sibl_id;

		if ($deliver_type == 'invoiced') {
			$id->column_fields[$this->prefix . 'qty_invoiced'] = $units_del;
		} else {
			$id->column_fields['units_delivered_received'] = $units_del;
		}
		

		if (!$this->getSoNoStockChange($invdet_id)) {
			$id->column_fields[$this->prefix . 'qty_in_order'] = $invdet_qty - $units_del;
		} else {
			$id->column_fields[$this->prefix . 'qty_in_order'] = 0;
		}

		$handler = vtws_getModuleHandlerFromName('InventoryDetails', $current_user);
		$meta = $handler->getMeta();
		$id->column_fields = DataTransform::sanitizeRetrieveEntityInfo($id->column_fields, $meta);

		// Make sure no ajax action is set to prevent CRMEntity from NOT converting to DB format
		$hold_ajxaction = isset($_REQUEST['ajxaction']) ? $_REQUEST['ajxaction'] : '';
		unset($_REQUEST['ajxaction']);		

		if ($saveentity) {
			$id->saveentity('InventoryDetails');
		} else {
			$id->save('InventoryDetails');
		}

		$_REQUEST['ajxaction'] = $hold_ajxaction;
	}

	public function getInvDetQtyById($id) {
		global $adb;
		$r = $adb->pquery("SELECT quantity FROM vtiger_inventorydetails WHERE inventorydetailsid = ? LIMIT 1", array($id));
		return $adb->fetch_array($r)['quantity'];
	}

	public function getInvoiceQtysFromSoLine($so_line_id) {
		global $adb;
		$r = $adb->pquery("SELECT SUM(vtiger_inventorydetails.quantity) AS qty FROM vtiger_inventorydetails 
                           INNER JOIN vtiger_crmentity crment_inv ON 
                           vtiger_inventorydetails.related_to = crment_inv.crmid 
                           INNER JOIN vtiger_crmentity crment_invdet ON 
                           vtiger_inventorydetails.inventorydetailsid = crment_invdet.crmid 
                           WHERE vtiger_inventorydetails.{$this->prefix}so_sibling = ? 
                           AND (SELECT vtiger_salesorder.invextras_so_no_stock_change FROM 
                                vtiger_salesorder INNER JOIN vtiger_inventorydetails 
                                ON vtiger_salesorder.salesorderid = vtiger_inventorydetails.related_to 
                                WHERE vtiger_inventorydetails.inventorydetailsid = ? LIMIT 1) != ? 
                           AND (SELECT vtiger_inventorydetails.productid FROM vtiger_inventorydetails 
                                WHERE vtiger_inventorydetails.inventorydetailsid = ? LIMIT 1) = vtiger_inventorydetails.productid 
                           AND crment_inv.deleted = ? 
                           AND crment_invdet.deleted = ?", array($so_line_id, $so_line_id, 1, $so_line_id, 0, 0));
		return $adb->num_rows($r) > 0 ? $adb->fetch_array($r)['qty'] : 0;
	}
	
	public function getQtyInOrderByProduct($productid) {
		global $adb;
		$r = $adb->pquery("SELECT SUM(vtiger_inventorydetails.{$this->prefix}qty_in_order) AS qty FROM vtiger_inventorydetails 
			INNER JOIN vtiger_crmentity ON 
			vtiger_inventorydetails.inventorydetailsid = vtiger_crmentity.crmid 
			INNER JOIN vtiger_salesorder ON 
			vtiger_salesorder.salesorderid = vtiger_inventorydetails.related_to 
			INNER JOIN vtiger_crmentity crment_so ON 
			vtiger_salesorder.salesorderid = crment_so.crmid 
			WHERE vtiger_crmentity.deleted = ? 
			AND crment_so.deleted = ? 
			AND vtiger_inventorydetails.productid = ? 
			AND (vtiger_salesorder.{$this->prefix}so_no_stock_change != ? 
			OR vtiger_salesorder.{$this->prefix}so_no_stock_change IS NULL)", array(0, 0, $productid, 1));

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
			               INNER JOIN vtiger_crmentity crment_po 
			               ON vtiger_inventorydetails.related_to = crment_po.crmid 
			               WHERE crment_id.deleted = ? 
			               AND crment_prod.deleted = ? 
			               AND crment_po.deleted = ? 
			               AND vtiger_purchaseorder.postatus != 'Cancelled' 
			               AND vtiger_inventorydetails.productid = ?", array(0, 0, 0, $productid));

		return $adb->fetch_array($r)['qty_bo'];
	}

	public function updateProductQtyInOrder($productid, $qty_in_order, $fieldname, $source_mod = '') {
		global $current_user;
		require_once 'modules/Products/Products.php';

		$p = new Products();
		$p->retrieve_entity_info($productid, 'Products');
		$p->id = $productid;
		$p->mode = 'edit';

		$p->column_fields[$fieldname] = $qty_in_order;

		if ($source_mod == 'SalesOrder') {
			// Recalculate available stock
			$p->column_fields[$this->prefix . 'prod_stock_avail'] = (float)$p->column_fields['qtyinstock'] - (float)$qty_in_order;			
		}

		$handler = vtws_getModuleHandlerFromName('Products', $current_user);
		$meta = $handler->getMeta();
		unset($_REQUEST['ajxaction']);
		$p->column_fields = DataTransform::sanitizeRetrieveEntityInfo($p->column_fields, $meta);

		if (isset($_REQUEST['ajxaction'])) {
			$ajxaction_holder = $_REQUEST['ajxaction'];
			$_REQUEST['ajxaction'] = 'Workflow';
		}

		if (file_exists('modules/ExactOnline/ExactOnline.php')) {
			$p->saveentity('Products');
		} else {
			$p->save('Products');
		}

		if (isset($_REQUEST['ajxaction'])) {
			$_REQUEST['ajxaction'] = $ajxaction_holder;
		}
	}
}
