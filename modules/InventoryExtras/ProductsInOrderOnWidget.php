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
$r = $adb->pquery("SELECT vtiger_inventorydetails.invextras_qty_in_order AS qty, 
						  vtiger_inventorydetails.related_to,
						  vtiger_salesorder.subject FROM 
						  vtiger_inventorydetails INNER JOIN vtiger_crmentity ON 
						  vtiger_inventorydetails.inventorydetailsid = vtiger_crmentity.crmid 
						  INNER JOIN vtiger_salesorder ON 
						  vtiger_inventorydetails.related_to = vtiger_salesorder.salesorderid 
						  WHERE vtiger_inventorydetails.productid = ? AND 
						  CAST(vtiger_inventorydetails.invextras_qty_in_order AS UNSIGNED) > ?", array($_REQUEST['record'], 0));

$lines = array();
while ($line = $adb->fetch_array($r)) {
	$lines[] = $line;
}

$smarty->assign('lines', $lines);
$smarty->display('modules/InventoryExtras/ProductsInOrderOnWidget.tpl');