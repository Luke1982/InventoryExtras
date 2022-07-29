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
$r = $adb->query("SELECT so.salesorderid AS related_to,
						 so.subject,
						 acc.accountname,
						 (soid.quantity - soid.units_delivered_received) AS qty
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
					INNER JOIN vtiger_account AS acc
						ON so.accountid = acc.accountid
					WHERE p.productid = {$_REQUEST['record']}");

$lines = array();
while ($line = $adb->fetch_array($r)) {
	$lines[] = $line;
}

$smarty->assign('lines', $lines);
$smarty->assign('user_decnum', $current_user->column_fields['no_of_currency_decimals']);
$smarty->assign('user_grpsep', $current_user->column_fields['currency_grouping_separator']);
$smarty->assign('user_cursep', $current_user->column_fields['currency_decimal_separator']);
$smarty->display('modules/InventoryExtras/ProductsInOrderOnWidget.tpl');