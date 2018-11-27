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

global $adb, $current_user;
$r = $adb->pquery("SELECT vtiger_inventorydetails.invextras_qty_in_order AS qty, 
						  vtiger_inventorydetails.related_to,
						  vtiger_salesorder.subject, 
						  vtiger_account.accountname FROM 
						  vtiger_inventorydetails INNER JOIN vtiger_crmentity ON 
						  vtiger_inventorydetails.inventorydetailsid = vtiger_crmentity.crmid 
						  INNER JOIN vtiger_salesorder ON 
						  vtiger_inventorydetails.related_to = vtiger_salesorder.salesorderid 
						  INNER JOIN vtiger_account ON 
						  vtiger_inventorydetails.account_id = vtiger_account.accountid 
						  WHERE vtiger_inventorydetails.productid = ? 
						  AND CAST(vtiger_inventorydetails.invextras_qty_in_order AS INT) != ?", array($_REQUEST['record'], 0));

$lines = array();
while ($line = $adb->fetch_array($r)) {
	$lines[] = $line;
}

$smarty->assign('lines', $lines);
$smarty->assign('user_decnum', $current_user->column_fields['no_of_currency_decimals']);
$smarty->assign('user_grpsep', $current_user->column_fields['currency_grouping_separator']);
$smarty->assign('user_cursep', $current_user->column_fields['currency_decimal_separator']);
$smarty->display('modules/InventoryExtras/ProductsInOrderOnWidget.tpl');