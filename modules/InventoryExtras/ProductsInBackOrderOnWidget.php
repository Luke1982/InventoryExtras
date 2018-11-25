<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
require_once('Smarty_setup.php');
$smarty = new vtigerCRM_Smarty();

global $adb;
$r = $adb->pquery("SELECT (vtiger_inventorydetails.quantity - vtiger_inventorydetails.units_delivered_received) AS qty_bo, 
                   vtiger_purchaseorder.purchaseorderid AS po_id, 
                   vtiger_purchaseorder.subject, 
                   vtiger_vendor.vendorname 
	               FROM vtiger_inventorydetails 
	               INNER JOIN vtiger_crmentity crment_id ON 
	               vtiger_inventorydetails.inventorydetailsid = crment_id.crmid 
	               INNER JOIN vtiger_crmentity crment_prod ON 
	               vtiger_inventorydetails.productid = crment_prod.crmid 
	               INNER JOIN vtiger_purchaseorder ON 
	               vtiger_inventorydetails.related_to = vtiger_purchaseorder.purchaseorderid 
	               INNER JOIN vtiger_vendor ON 
	               vtiger_purchaseorder.vendorid = vtiger_vendor.vendorid 
	               WHERE crment_id.deleted = ? 
	               AND crment_prod.deleted = ? 
	               AND vtiger_inventorydetails.productid = ? 
	               AND (vtiger_inventorydetails.quantity - vtiger_inventorydetails.units_delivered_received) > ?", array(0, 0, $_REQUEST['record'], 0));

$lines = array();
while ($line = $adb->fetch_array($r)) {
	$lines[] = $line;
}

$smarty->assign('lines', $lines);
$smarty->display('modules/InventoryExtras/ProductsInBackOrderOnWidget.tpl');