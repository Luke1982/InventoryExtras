<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
class InventoryExtras {

	private $prefix = 'invextras_';

	private $i18n_so = array(
		'langs' => array('en_us', 'nl_nl'),
		'invextras_so_no_stock_change' => array(
			'en_us' => 'Don\'t affect the stock',
			'nl_nl' => 'Mutatie voorraad buiten beschouwing laten',
		),
		'LBL_HELP_SO_LEAVE_STOCK_ALONE' => array(
			'en_us' => 'This will avoid lines on related invoices from being linked to lines on this salesorder.
			It will also avoid the lines on this salesorder to affect the total no. in order on the product.',
			'nl_nl' => 'Als dit aan staat worden er geen factuurregels gezocht die tegenover de regels van de order
			moeten komen te staan. De regels van deze order hebben dan ook geen invloed op het product (bijvoorbeeld het aantal \'in order\'',
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
	 * Link array keys from product collection
	 * from database to fieldnames of products
	 * Array format: 'corebos columnn name' => 'inventoryextras key'
	 */
	private const FIELD_MAPPING = array(
		'qtyinstock'				  => 'instock',
		'invextras_prod_qty_in_order' => 'inorder',
		'qtyindemand'				  => 'inbackorder',
	);

	/**
	 * All the numerical array keys that could
	 * exist on a product collected from the
	 * database by InventoryExtras
	 */
	public const NUMERICAL_KEYS = array(
		'inbackorder',
		'received',
		'invoiced',
		'instock',
		'sold',
		'delivered',
		'inorder',
	);

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
	 */
	public function vtlib_handler($modulename, $event_type) { // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
		if ($event_type == 'module.postinstall') {
			$this->doPostInstall();
		} elseif ($event_type == 'module.disabled') {
			// TODO Handle actions when this module is disabled.
		} elseif ($event_type == 'module.enabled') {
			// TODO Handle actions when this module is enabled.
		} elseif ($event_type == 'module.preuninstall') {
			// TODO Handle actions when this module is about to be deleted.
			$this->removeThisModule();
		} elseif ($event_type == 'module.preupdate') {
			// TODO Handle actions before this module is updated.
		} elseif ($event_type == 'module.postupdate') {
			// TODO Handle actions after this module is updated.
			$this->doInstallcbUpdates();
			$this->doRemoveInvExtrasBlockInInvendet();
			$this->removeInvenExtrasEventHandlers();
			$this->doCreateWorkflowFunction();
			self::installStockDetailsWidget();
		}
	}

	private function doPostInstall() {
		$this->doAddProdFields();
		$this->doAddSoFields();
		$this->doUpdateLangFiles();
		$this->doAddProductInOrderOnWidget();
		$this->doCreateWorkflowFunction();
		$this->doAddProductInBackOrderOnWidget();
		$this->doAddInventoryExtrasHeaderScript();
		$this->doInstallcbUpdates();
		self::installStockDetailsWidget();
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
		if ($blk !== false) {
			$blk->delete(true);
		}

		$mod = Vtiger_Module::getInstance('Products');
		$fld = Vtiger_Field::getInstance($this->prefix . 'prod_qty_in_order', $mod);
		if ($fld !== false) {
			$fld->delete();
		}
		$fld = Vtiger_Field::getInstance($this->prefix . 'prod_stock_avail', $mod);
		if ($fld !== false) {
			$fld->delete();
		}
		$fld = Vtiger_Field::getInstance($this->prefix . 'prod_qty_to_order', $mod);
		if ($fld !== false) {
			$fld->delete();
		}
		$fld = Vtiger_Field::getInstance($this->prefix . 'prod_max_stock', $mod);
		if ($fld !== false) {
			$fld->delete();
		}

		$mod = Vtiger_Module::getInstance('SalesOrder');
		$fld = Vtiger_Field::getInstance($this->prefix . 'so_no_stock_change', $mod);
		if ($fld !== false) {
			$fld->delete();
		}

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
		$moduleInstance->deleteLink(
			'DETAILVIEWWIDGET',
			'LBL_PRODUCT_IN_ORDER_ON',
			'module=InventoryExtras&action=InventoryExtrasAjax&file=ProductsInOrderOnWidget&return_module=$MODULE$&record=$RECORD$'
		);
		$moduleInstance->deleteLink(
			'DETAILVIEWWIDGET',
			'LBL_PRODUCT_IN_BACKORDER_ON',
			'module=InventoryExtras&action=InventoryExtrasAjax&file=ProductsInBackOrderOnWidget&return_module=$MODULE$&record=$RECORD$'
		);
		$moduleInstance = Vtiger_Module::getInstance('InventoryExtras');
		$moduleInstance->deleteLink('HEADERSCRIPT', 'InventoryExtrasHeaderScript', 'modules/InventoryExtras/InventoryExtras.js');

		$em = new VTEventsManager($adb);
		$em->unregisterHandler('InvExtrasAfterSaveFirst');
		$em->unregisterHandler('InvExtrasAfterSave');
	}

	/**
	 * Remove special InventoryExtras block in InventoryDetails
	 * together with its fields
	 *
	 * @param  Null
	 * @return void
	 */
	private function doRemoveInvExtrasBlockInInvendet() : void {
		global $adb;
		$mod = Vtiger_Module::getInstance('InventoryDetails');
		$blk = Vtiger_Block::getInstance('LBL_INVDET_SO_INFO', $mod);
		if ($blk !== false) {
			$blk->delete(true);
			$adb->query("ALTER TABLE vtiger_inventorydetails DROP COLUMN " . $this->prefix . "so_sibling, DROP COLUMN " . $this->prefix . "qty_in_order");
			$adb->query("ALTER TABLE vtiger_inventorydetails DROP COLUMN " . $this->prefix . "qty_invoiced");
		}
	}

	/**
	 * Remove handler references in database
	 *
	 * @param  Null
	 * @return void
	 */
	private function removeInvenExtrasEventHandlers() : void {
		global $adb;
		require 'include/events/include.inc';

		$em = new VTEventsManager($adb);
		$em->unregisterHandler('InvExtrasAfterSaveFirst');
		$em->unregisterHandler('InvExtrasAfterSave');
		$em->unregisterHandler('InvExtrasAfterDelete');
	}

	private function doAddProductInOrderOnWidget() {
		include_once 'vtlib/Vtiger/Module.php';
		$mod_acc = Vtiger_Module::getInstance('Products');
		$mod_acc->addLink(
			'DETAILVIEWWIDGET',
			'LBL_PRODUCT_IN_ORDER_ON',
			'module=InventoryExtras&action=InventoryExtrasAjax&file=ProductsInOrderOnWidget&return_module=$MODULE$&record=$RECORD$'
		);
	}

	private function doAddProductInBackOrderOnWidget() {
		include_once 'vtlib/Vtiger/Module.php';
		$mod_acc = Vtiger_Module::getInstance('Products');
		$mod_acc->addLink(
			'DETAILVIEWWIDGET',
			'LBL_PRODUCT_IN_BACKORDER_ON',
			'module=InventoryExtras&action=InventoryExtrasAjax&file=ProductsInBackOrderOnWidget&return_module=$MODULE$&record=$RECORD$'
		);
	}

	private function doAddInventoryExtrasHeaderScript() {
		include_once 'vtlib/Vtiger/Module.php';
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
		include_once 'vtlib/Vtiger/Module.php';
		require 'modules/com_vtiger_workflow/VTEntityMethodManager.inc';
		global $adb;
		$emm = new VTEntityMethodManager($adb);
		$emm->addEntityMethod(
			"PurchaseOrder",
			"Equalize related InventoryDetails records",
			"modules/InventoryExtras/workflowfunctions/EqualizeIDRecords.php",
			"EqualizeIDRecords"
		);
		$emm->addEntityMethod(
			"SalesOrder",
			"Equalize related InventoryDetails records",
			"modules/InventoryExtras/workflowfunctions/EqualizeIDRecords.php",
			"EqualizeIDRecords"
		);
		$emm->addEntityMethod(
			"Invoice",
			"Equalize related InventoryDetails records",
			"modules/InventoryExtras/workflowfunctions/EqualizeIDRecords.php",
			"EqualizeIDRecords"
		);
		$mods = array('Invoice', 'PurchaseOrder', 'SalesOrder');
		array_walk($mods, function ($mod) use ($emm) {
			$emm->addEntityMethod(
				$mod,
				'Werk voorraadinformatie bij voor alle producten die hierop genoemd zijn',
				'modules/InventoryExtras/workflowfunctions/UpdateStockInformation.php',
				'updateStockForInventoryRecord'
			);
		});
	}

	private function doRemoveWorkflowFunction() {
		require_once 'include/utils/utils.php';
		include_once 'vtlib/Vtiger/Module.php';
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

	public function getPrefix() {
		return $this->prefix;
	}

	/**
	 * Install the StockDetails widget
	 *
	 * @param  Null
	 * @return Null
	 * @throws Null
	 */
	private static function installStockDetailsWidget() {
		include_once 'vtlib/Vtiger/Module.php';
		$module = Vtiger_Module::getInstance('Products');
		$linktypes = array('DETAILVIEWWIDGET', 'EDITVIEWWIDGET');
		foreach ($linktypes as $linktype) {
			$module->addLink(
				$linktype,
				'StockDetails',
				'block://StockDetailsBlock:modules/InventoryExtras/StockDetailsBlock.php',
				'',
				3
			);
		}
	}

	/**
	 * Get the inventorylines that are relevant to
	 * the stock calculation and stock-related fields
	 * calculation (like quantity in demand, quantity
	 * in order, etc.).
	 *
	 * @param  int    $productid   The product CRM ID of the product
	 * @return object $productinfo The database object representing
	 * 							   the inventorydetails lines that
	 * 							   are relevant to the stock accountability
	 * 							   information
	 * @throws None
	 */
	public function getStockAccountabilityObject(int $productid) : object {
		global $adb;

		$q = "SELECT p.productid,
					 p.product_no,
					 p.productname,
					 inv.invoice_no AS entity_no,
					 'Invoice' AS entitytype,
					 inv.invoiceid AS entityid,
					 invid.quantity AS invoiced,
					 0 AS purchased,
					 0 AS received,
					 0 AS sold,
					 0 AS delivered,
					 0 AS inorder
				FROM vtiger_products AS p
				INNER JOIN vtiger_crmentity AS p_ent
					ON p.productid = p_ent.crmid
					AND p_ent.deleted = 0
				INNER JOIN vtiger_inventorydetails AS invid
					ON invid.productid = p.productid
				INNER JOIN vtiger_crmentity AS invid_ent
					ON invid.inventorydetailsid = invid_ent.crmid
					AND invid_ent.deleted = 0
				INNER JOIN vtiger_invoice AS inv
					ON inv.invoiceid = invid.related_to
				INNER JOIN vtiger_crmentity AS inv_ent
					ON inv.invoiceid = inv_ent.crmid
					AND inv_ent.deleted = 0
				WHERE p.productid = {$productid}
			UNION
			SELECT 	 p.productid,
					 p.product_no,
					 p.productname,
					 po.purchaseorder_no AS entity_no,
					 'PurchaseOrder' AS entitytype,
					 po.purchaseorderid AS entityid,
					 0 AS invoiced,
					 poid.quantity AS purchased,
					 poid.units_delivered_received AS received,
					 0 AS sold,
					 0 AS delivered,
					 0 AS inorder
				FROM vtiger_products AS p
				INNER JOIN vtiger_crmentity AS p_ent
					ON p.productid = p_ent.crmid
					AND p_ent.deleted = 0
				INNER JOIN vtiger_inventorydetails AS poid
					ON poid.productid = p.productid
				INNER JOIN vtiger_crmentity AS poid_ent
					ON poid.inventorydetailsid = poid_ent.crmid
					AND poid_ent.deleted = 0
				INNER JOIN vtiger_purchaseorder AS po
					ON po.purchaseorderid = poid.related_to
				INNER JOIN vtiger_crmentity AS po_ent
					ON po.purchaseorderid = po_ent.crmid
					AND po_ent.deleted = 0
				WHERE p.productid = {$productid}
			UNION
			SELECT 	 p.productid,
					 p.product_no,
					 p.productname,
					 so.salesorder_no AS entity_no,
					 'SalesOrder' AS entitytype,
					 so.salesorderid AS entityid,
					 0 AS invoiced,
					 0 AS purchased,
					 0 AS received,
					 soid.quantity AS sold,
					 soid.units_delivered_received AS delivered,
					 soid.quantity - soid.units_delivered_received AS inorder
				FROM vtiger_products AS p
				INNER JOIN vtiger_crmentity AS p_ent
					ON p.productid = p_ent.crmid
					AND p_ent.deleted = 0
				INNER JOIN vtiger_inventorydetails AS soid
					ON p.productid = soid.productid
				INNER JOIN vtiger_salesorder AS so
					ON soid.related_to = so.salesorderid
					AND so.sostatus != 'Delivered'
					AND so.sostatus != 'Cancelled'
					AND so.sostatus != 'Niet geleverd'
					AND so.invextras_so_no_stock_change != 1
				INNER JOIN vtiger_crmentity AS so_ent
					ON so.salesorderid = so_ent.crmid
					AND so_ent.deleted = 0
				INNER JOIN vtiger_crmentity AS soid_ent
					ON soid.inventorydetailsid = soid_ent.crmid
					AND soid_ent.deleted = 0
				WHERE p.productid = {$productid}
			";
		return $adb->query($q);
	}

	/**
	 * Get the current quantity in backorder for
	 * each product by taking the PurchaseOrders
	 * that are pending, getting the quantity ordered
	 * for each line and deducting the possible
	 * units received for that line.
	 *
	 * @param  array  $products
	 * 				  An optional array of product ID's
	 * 				  that will be used to filter the results.
	 * @return Object database result object
	 * @throws None
	 */
	public function getCurrentProductBackorderLevelsObject(array $products = array()) : object {
		global $adb;
		$filter = count($products) > 0 ? 'AND p.productid IN(' . implode(',', $products) . ')' : '';
		$q = "SELECT p.productid,
					 p.product_no,
					 p.productname,
					 SUM(poid.quantity - poid.units_delivered_received) AS inbackorder 
				FROM vtiger_inventorydetails AS poid
				INNER JOIN vtiger_crmentity crment_id 
					ON poid.inventorydetailsid = crment_id.crmid 
				INNER JOIN vtiger_crmentity crment_prod 
					ON poid.productid = crment_prod.crmid 
				INNER JOIN vtiger_purchaseorder AS po
					ON poid.related_to = po.purchaseorderid 
				INNER JOIN vtiger_crmentity crment_po 
					ON poid.related_to = crment_po.crmid
				INNER JOIN vtiger_products AS p
					ON poid.productid = p.productid
				WHERE crment_id.deleted = 0
				AND crment_prod.deleted = 0 
				AND crment_po.deleted = 0
				AND po.postatus != 'Cancelled'
				AND po.postatus != 'Delivered'
				AND po.postatus != 'Received Shipment'
				{$filter}
				GROUP BY poid.productid";
		return $adb->query($q);
	}

	/**
	 * Get the current stock level for each product by
	 * taking the sum of all purchaseorderlines (InventoryDetails)
	 * quantities 'received' and deducting all the quantities
	 * invoices
	 *
	 * @param  array  $products
	 * 				  An optional array of product ID's
	 * 				  that will be used to filter the results.
	 * @return Object database result object
	 * @throws None
	 */
	public function getCurrentProductStockLevelsObject(array $products = array()) : object {
		global $adb;
		$filter = count($products) > 0 ? 'WHERE p.productid IN(' . implode(',', $products) . ')' : '';
		$q = "SELECT
				T1.product_no,
				T1.productname,
				T1.productid,
				T1.invoiced,
				T2.received,
				T2.received - T1.invoiced AS instock
				FROM (
				SELECT p.productid,
					p.product_no,
					p.productname,
					SUM(invid.quantity) AS invoiced
						FROM vtiger_products AS p
						INNER JOIN vtiger_crmentity AS p_ent
							ON p.productid = p_ent.crmid
							AND p_ent.deleted = 0
						INNER JOIN vtiger_inventorydetails AS invid
							ON invid.productid = p.productid
							AND (
								SELECT `setype`
								FROM vtiger_crmentity
								WHERE vtiger_crmentity.crmid = invid.related_to
								AND vtiger_crmentity.deleted = 0
							) = 'Invoice'
						INNER JOIN vtiger_crmentity AS invid_ent
							ON invid.inventorydetailsid = invid_ent.crmid
							AND invid_ent.deleted = 0
						{$filter}
						GROUP BY p.productid
				) AS T1 JOIN (
				SELECT p.productid,
					p.productname,
					SUM(poid.units_delivered_received) AS received
						FROM vtiger_products AS p
						INNER JOIN vtiger_crmentity AS p_ent
							ON p.productid = p_ent.crmid
							AND p_ent.deleted = 0
						INNER JOIN vtiger_inventorydetails AS poid
							ON poid.productid = p.productid
							AND (
								SELECT `setype`
								FROM vtiger_crmentity
								WHERE vtiger_crmentity.crmid = poid.related_to
								AND vtiger_crmentity.deleted = 0
							) = 'PurchaseOrder'
						INNER JOIN vtiger_crmentity AS poid_ent
							ON poid.inventorydetailsid = poid_ent.crmid
							AND poid_ent.deleted = 0
						{$filter}
						GROUP BY p.productid
				) AS T2
					ON T1.productid = T2.productid";
		$r = $adb->query($q);
		return $r;
	}

	/**
	 * Get the current quantity in order for all products
	 * by taking all InventoryLines that belong to orders
	 * that are NOT 'Delivered', 'Cancelled', 'Niet geleverd'
	 * AND do not have the checkbox 'so_no_stock_change'
	 * selected. The units_delivered_received will be
	 * deducted from the quantity of each line.
	 *
	 * @param  array  $products
	 * 				  An optional array of product ID's that
	 * 				  will be used to filter the results.
	 * @return object Database result object
	 * @throws None
	 */
	public function getCurrentProductOrderLevelsObject(array $products = array()) : object {
		global $adb;
		$filter = count($products) > 0 ? 'WHERE p.productid IN(' . implode(',', $products) . ')' : '';
		$q = "SELECT p.productid,
					 p.product_no,
					 SUM(soid.quantity) AS sold,
					 SUM(soid.units_delivered_received) AS delivered,
					 SUM(soid.quantity - soid.units_delivered_received) AS inorder
				FROM vtiger_products AS p
				INNER JOIN vtiger_crmentity AS p_ent
					ON p.productid = p_ent.crmid
					AND p_ent.deleted = 0
				INNER JOIN vtiger_inventorydetails AS soid
					ON p.productid = soid.productid
					AND (
						SELECT `setype`
						FROM vtiger_crmentity
						WHERE vtiger_crmentity.crmid = soid.related_to
						AND vtiger_crmentity.deleted = 0
					) = 'SalesOrder'
				INNER JOIN vtiger_salesorder AS so
					ON soid.related_to = so.salesorderid
					AND so.sostatus != 'Delivered'
					AND so.sostatus != 'Cancelled'
					AND so.sostatus != 'Niet geleverd'
					AND so.invextras_so_no_stock_change != 1
				INNER JOIN vtiger_crmentity AS soid_ent
					ON soid.inventorydetailsid = soid_ent.crmid
					AND soid_ent.deleted = 0
				{$filter}
				GROUP BY p.productid";
		$r = $adb->query($q);
		return $r;
	}

	/**
	 * Make sure the numerical properties of a product
	 * that we want to update exist so we don't run into
	 * errors when trying to do math with them
	 *
	 * @param  array $product The product, passed by reference
	 * @return None
	 * @throws None
	 */
	private function sanitizeProductArray(array &$product) : void {
		foreach (self::NUMERICAL_KEYS as $key) {
			if (!array_key_exists($key, $product)) {
				$product[$key] = (float)0;
			}
		}
	}

	/**
	 * Update a single product
	 *
	 * @param int	  $productid The product CRM ID
	 * @param array   $product   An array that contains the product information
	 * 							 that **can**, but does not have to include the
	 * 							 following keys:
	 * 							 - productid: The product CRM ID
	 * 							 - product_no
	 * 							 - sold: The qty sold on salesorders
	 * 							 - delivered: The qty delivered on invoices
	 * 							 - inorder: The delta between sold and delivered
	 * 							 - productname
	 * 							 - received: The qty received from purchaseorders
	 * 							 - invoiced: The qty invoiced in total
	 * 							 - instock: The delta between received and invoiced
	 * 							 - inbackorder: The quantity not received on open purchaseorders
	 * @return object $focus 	 The productobject after saving
	 * @throws None
	 */
	public function updateProduct(int $productid, array $product) : object {
		global $current_user;
		require_once 'modules/Products/Products.php';

		$p = new Products();
		$p->retrieve_entity_info($productid, 'Products');
		$p->id = $productid;
		$p->mode = 'edit';

		$this->sanitizeProductArray($product);

		foreach (self::FIELD_MAPPING as $cbcolumnname => $iekey) {
			$p->column_fields[$cbcolumnname] = (string)number_format($product[$iekey], 6, '.', '');
		}

		$p->column_fields['invextras_prod_stock_avail'] = ($product['instock'] - $product['inorder']);
		$p->column_fields['invextras_prod_qty_to_order'] =
			($product['inorder'] + (float)$p->column_fields['reorderlevel']) -
			($product['instock'] + $product['inbackorder']);

		$p->column_fields['invextras_prod_stock_avail'] = (string)number_format($p->column_fields['invextras_prod_stock_avail'], 6, '.', '');
		$p->column_fields['invextras_prod_qty_to_order'] = (string)number_format($p->column_fields['invextras_prod_qty_to_order'], 6, '.', '');

		$handler = vtws_getModuleHandlerFromName('Products', $current_user);
		$meta = $handler->getMeta();
		$p->column_fields = DataTransform::sanitizeRetrieveEntityInfo($p->column_fields, $meta);

		if (isset($_REQUEST['ajxaction'])) {
			$ajxaction_holder = $_REQUEST['ajxaction'];
			$_REQUEST['ajxaction'] = 'Workflow';
		}

		$p->saveentity('Products');

		if (isset($_REQUEST['ajxaction'])) {
			$_REQUEST['ajxaction'] = $ajxaction_holder;
		}

		return $p;
	}
}
