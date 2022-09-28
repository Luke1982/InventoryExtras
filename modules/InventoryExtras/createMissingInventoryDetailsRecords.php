<?php

require_once 'modules/InventoryExtras/workflowfunctions/CBXGenerator.php';
require_once 'modules/PurchaseOrder/PurchaseOrder.php';
require_once 'modules/Quotes/Quotes.php';
require_once 'modules/SalesOrder/SalesOrder.php';
require_once 'modules/Invoice/Invoice.php';
require_once 'modules/InventoryDetails/InventoryDetails.php';
ini_set('max_execution_time', 600);

const SETYPE_TO_TABLE = array(
	'PurchaseOrder' =>
		array(
			'tablename' => 'vtiger_purchaseorder',
			'idcol' => 'purchaseorderid',
		),
	'Quotes' =>
		array(
			'tablename' => 'vtiger_quotes',
			'idcol' => 'quoteid',
		),
	'SalesOrder' =>
		array(
			'tablename' => 'vtiger_salesorder',
			'idcol' => 'salesorderid',
		),
	'Invoice' =>
		array(
			'tablename' => 'vtiger_invoice',
			'idcol' => 'invoiceid',
		),
);

global $adb, $current_user;
$q = "SELECT GROUP_CONCAT(DISTINCT e.crmid SEPARATOR ',') AS productids
		FROM vtiger_crmentity AS e
		WHERE e.setype = 'Products'
		AND e.deleted = 1";
$r = $adb->query($q);
$deleted_products = $adb->query_result($r, 0, 'productids');
if (!file_exists('cache/deleted_products.txt')) {
	file_put_contents('cache/deleted_products.txt', $deleted_products);
}
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
	LIMIT 10";
$r = $adb->query($q);

foreach (CBX\rowGenerator($r) as $f) {
	$seinfo = SETYPE_TO_TABLE[$f['setype']];
	$r = $adb->query("SELECT {$seinfo['idcol']} FROM {$seinfo['tablename']} WHERE {$seinfo['idcol']} = {$f['crmid']}");
	if ($adb->num_rows($r) !== 0) {
		$focus = new $f['setype']();
		$focus->retrieve_entity_info($f['crmid'], $f['setype']);
		$focus->id = $f['crmid'];

		InventoryDetails::createInventoryDetails($focus, $f['setype']);
	}
}

$deleted_products = file_get_contents('cache/deleted_products.txt');
$adb->query("UPDATE vtiger_crmentity SET deleted = 1 WHERE crmid IN ({$deleted_products})");

if ($adb->num_rows($r) > 0) {
	$q2 = "SELECT COUNT(*) AS rows_left
	FROM vtiger_crmentity
	WHERE deleted = 0
	AND (
		SELECT COUNT(*) FROM vtiger_inventorydetails
		WHERE vtiger_inventorydetails.related_to = vtiger_crmentity.crmid
	) = 0
	AND setype IN('PurchaseOrder', 'SalesOrder', 'Invoice', 'Quotes')";
	$r2 = $adb->query($q2);
	// $protocol = $_SERVER['HTTPS'] ? 'https://' : 'http://';
	// $host = $protocol . $_SERVER['HTTP_HOST'];
	// header('Location: ' . $host . '/index.php?module=InventoryExtras&action=InventoryExtrasAjax&createMissingInventoryDetailsRecords=test&random=' . rand(10, 100));
	?>
		<div class="slds-box slds-m-around_medium">
			<div class="slds-text slds-text-align_center">
				Er worden voor alle oude verkooporders, inkooporders, offertes en facturen
				moderne regels aangemaakt ten behoeve van het voorraadbeheer. Even geduld.
				Er zijn nog <?php echo $adb->query_result($r2, 0, 'rows_left'); ?> records die bijgewerkt moeten worden.
			</div>
		</div>
		<script>
			setTimeout(() => {
				window.location = window.location
			}, 2000)
		</script>
	<?php
}
