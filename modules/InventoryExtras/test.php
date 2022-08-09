<?php

require_once 'modules/InventoryExtras/workflowfunctions/CBXGenerator.php';
require_once 'modules/PurchaseOrder/PurchaseOrder.php';
require_once 'modules/Quotes/Quotes.php';
require_once 'modules/SalesOrder/SalesOrder.php';
require_once 'modules/Invoice/Invoice.php';
require_once 'modules/InventoryDetails/InventoryDetails.php';
ini_set('max_execution_time', 600);

global $adb, $current_user;
$q = "SELECT GROUP_CONCAT(DISTINCT e.crmid SEPARATOR ',') AS productids
		FROM vtiger_crmentity AS e
		WHERE e.setype = 'Products'
		AND e.deleted = 1";
$r = $adb->query($q);
$deleted_products = $adb->query_result($r, 0, 'productids');
$adb->query("UPDATE vtiger_crmentity SET deleted = 0 WHERE crmid IN ({$deleted_products})");

$q = "SELECT
		crmid, setype
	FROM vtiger_crmentity
	WHERE deleted = 0
	AND (
		SELECT COUNT(*) FROM vtiger_inventorydetails
		WHERE vtiger_inventorydetails.related_to = vtiger_crmentity.crmid
	) = 0
	AND setype IN('PurchaseOrder', 'SalesOrder', 'Invoice', 'Quotes')
	LIMIT 1000";
$r = $adb->query($q);

foreach (CBX\rowGenerator($r) as $f) {
	$focus = new $f['setype']();
	$focus->retrieve_entity_info($f['crmid'], $f['setype']);
	$focus->id = $f['crmid'];

	InventoryDetails::createInventoryDetails($focus, $f['setype']);
}

$adb->query("UPDATE vtiger_crmentity SET deleted = 1 WHERE crmid IN ({$deleted_products})");

if ($adb->num_rows($r) > 0) {
	$protocol = $_SERVER['HTTPS'] ? 'https://' : 'http://';
	$host = $protocol . $_SERVER['HTTP_HOST'];
	header('Location: ' . $host . '/index.php?module=InventoryExtras&action=InventoryExtrasAjax&file=test');
}
