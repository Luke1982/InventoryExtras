<?php
/*************************************************************************************************
 * Copyright 2018 MajorLabel -- This file is a part of MajorLabel coreBOS Customizations.
* Licensed under the vtiger CRM Public License Version 1.1 (the "License"); you may not use this
* file except in compliance with the License. You can redistribute it and/or modify it
* under the terms of the License. MajorLabel reserves all rights not expressly
* granted by the License. coreBOS distributed by MajorLabel is distributed in
* the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
* warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. Unless required by
* applicable law or agreed to in writing, software distributed under the License is
* distributed on an "AS IS" BASIS, WITHOUT ANY WARRANTIES OR CONDITIONS OF ANY KIND,
* either express or implied. See the License for the specific language governing
* permissions and limitations under the License. You may obtain a copy of the License
* at <http://corebos.org/documentation/doku.php?id=en:devel:vpl11>
*************************************************************************************************/
switch ($_REQUEST['function']) {
	case 'getInfoByProduct':
		getInfoByProduct($_REQUEST['productid'], $_REQUEST['record'], $_REQUEST['seq']);
		break;
	case 'getAccountabilityReportForProduct':
		getAccountabilityReportForProduct((int)($_REQUEST['productid']));
		break;
	default:
		echo "Nothing to declare sir..";
		break;
}

function getInfoByProduct($product_id, $record_id, $seq) {
	global $adb;
	
	require_once 'modules/InventoryExtras/InventoryExtras.php';
	require_once 'include/fields/CurrencyField.php';

	$invext = new InventoryExtras();
	$invext_prefix = $invext->getPrefix();

	$r = $adb->pquery("SELECT {$invext_prefix}prod_qty_in_order AS qtyinorder, 
		                      {$invext_prefix}prod_stock_avail AS stockavail, 
		                      {$invext_prefix}prod_qty_to_order AS qtytoorder, 
		                      qtyindemand, 
		                      vendor_part_no,
							  generalledgers,
							  'Products' AS setype
					   FROM vtiger_products WHERE productid = ?
					UNION SELECT '' AS qtyinorder,
						'' AS stockavail,
						'' AS qtytoorder,
						'' AS qtyindemand,
						'' AS vendor_part_no,
						generalledgers,
						'Services' AS setype
					FROM vtiger_service WHERE serviceid = ?", array($product_id, $product_id));

	if ($adb->num_rows($r) > 0) {
		$data = $adb->fetch_array($r);

		$qty_in_order = CurrencyField::convertToUserFormat($data['qtyinorder']);
		$stock_avail = CurrencyField::convertToUserFormat($data['stockavail']);
		$qty_to_order = CurrencyField::convertToUserFormat($data['qtytoorder']);
		$qty_in_demand = CurrencyField::convertToUserFormat($data['qtyindemand']);

		$qty_in_order_lab = getTranslatedString('LBL_TO_DELIVER_SO', 'InventoryExtras');
		$qty_in_demand_lab = getTranslatedString('LBL_TO_RECEIVE_PO', 'InventoryExtras');
		$stock_avail_lab = getTranslatedString($invext_prefix . 'prod_stock_avail', 'Products');
		$qty_to_order_lab = getTranslatedString($invext_prefix . 'prod_qty_to_order', 'Products');
		$ven_part_no_lab = getTranslatedString('Vendor PartNo', 'Products');
		$gl_account_lab = getTranslatedString('GL Account', 'Products');
		
		echo json_encode(array(
			'qtyinorder' => array(
				'label' => $qty_in_order_lab,
				'value' => $qty_in_order),
			'qtyindemand' => array(
				'label' => $qty_in_demand_lab,
			    'value' => $qty_in_demand),
			'stockavail' => array(
				'label' => $stock_avail_lab,
			    'value' => $stock_avail),
			'qtytoorder' => array(
				'label' => $qty_to_order_lab,
			    'value' => $qty_to_order),
			'venpartno' => array(
				'label' => $ven_part_no_lab,
			    'value' => $data['vendor_part_no']),
			'glaccount' => array(
				'label' => $gl_account_lab,
				'value' => $data['generalledgers']),
			'setype' => $data['setype']
			)
		);
	} else {
		echo "NOTHINGFOUND";
	}
}

/**
 * Get the accountability for the current product
 * stock details. That means getting the source
 * of the data. The actual inventorylines and
 * the quantities on those lines. So how many are
 * invoiced, on which invoices, how many are
 * received on which purchaseorders and how many
 * are in order on which salesorder?
 *
 * @param int  @productid The product ID you want to
 * 						  get the accountability for
 * @return None
 * @throws None
 */
function getAccountabilityReportForProduct(int $productid) : void {
	ini_set('display_errors', 0);

	require_once 'modules/InventoryExtras/InventoryExtras.php';
	require_once 'include/PhpSpreadsheet/autoload.php';
	require_once 'include/PhpSpreadsheet/phpoffice/phpspreadsheet/src/PhpSpreadsheet/Style/NumberFormat.php';
	require_once 'modules/InventoryExtras/workflowfunctions/CBXGenerator.php';

	$ie = new InventoryExtras();
	$workbook = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
	$protocol = $_SERVER['HTTPS'] ? 'https://' : 'http://';
	$host = $protocol . $_SERVER['HTTP_HOST'];

	$worksheet = $workbook->removeSheetByIndex(0);
	$dbobject = $ie->getStockAccountabilityObject($productid);
	$sheets = array(
		'PurchaseOrder' => array(
			'index'     => 0,
			'title'     => 'Inkoop orders',
			'cur_row'   => 2,
			'title_row' => array(
				array(
					'column' => 'A',
					'label'  => 'Inkooporder nummer'
				),
				array(
					'column' => 'B',
					'label'  => 'Aantal besteld'
				),
				array(
					'column' => 'C',
					'label'  => 'Aantal ontvangen'
				),
			),
		),
		'SalesOrder' => array(
			'index'     => 1,
			'title'     => 'Verkoop orders',
			'cur_row'   => 2,
			'title_row' => array(
				array(
					'column' => 'A',
					'label'  => 'Verkooporder nummer'
				),
				array(
					'column' => 'B',
					'label'  => 'Aantal verkocht'
				),
				array(
					'column' => 'C',
					'label'  => 'Aantal uitgeleverd'
				),
				array(
					'column' => 'D',
					'label'  => 'Aantal resterend in order'
				),
			),
		),
		'Invoice' => array(
			'index'     => 2,
			'title'     => 'Facturen',
			'cur_row'   => 2,
			'title_row' => array(
				array(
					'column' => 'A',
					'label'  => 'Factuurnummer'
				),
				array(
					'column' => 'B',
					'label'  => 'Aantal gefactureerd'
				),
			),
		),
	);

	array_walk($sheets, function ($sheet) use ($workbook) {
		$workbook->addSheet(new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet(), $sheet['index']);
		$worksheet = $workbook->setActiveSheetIndex($sheet['index']);
		$worksheet->setTitle($sheet['title']);
		foreach ($sheet['title_row'] as $title_column) {
			$worksheet->setCellValue($title_column['column'] . '1', $title_column['label']);
			$worksheet->getStyle('A1:E1')->getFont()->applyFromArray(
				[
					'name' => 'Arial',
					'bold' => true,
					'italic' => false,
					'underline' => false,
					'strikethrough' => false,
					'color' => [ 'rgb' => '000000' ],
					'size' => '12pt'
				]
			);
			$worksheet->getStyle($title_column['column'] . '1')
			->getFill()
			->setFillType('solid')
			->getStartColor()
			->setARGB('FF9cb5b7');
		}
		$worksheet->getColumnDimension($title_column['column'])->setAutoSize(true);
	});

	foreach (CBX\rowGenerator($dbobject) as $row) {
		$filename = 'cache/voorraad-verantwoording-' . $row['product_no'] . '-' . date('d-m-Y') . '.xlsx';
		switch ($row['entitytype']) {
			case 'Invoice':
				$worksheet = $workbook->setActiveSheetIndex($sheets['Invoice']['index']);
				$worksheet->setCellValueExplicit(
					'A' . $sheets['Invoice']['cur_row'],
					$row['entity_no'],
					\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
				);
				$worksheet->getCell('A' . $sheets['Invoice']['cur_row'])->getHyperlink()->setUrl(
					$host . '/index.php?module=' . $row['entitytype'] . '&action=DetailView&record=' . $row['entityid']
				);
				$worksheet->setCellValue('B' . $sheets['Invoice']['cur_row'], $row['invoiced']);
				$sheets['Invoice']['cur_row']++;
				break;
			case 'PurchaseOrder':
				$worksheet = $workbook->setActiveSheetIndex($sheets['PurchaseOrder']['index']);
				$worksheet->setCellValue('A' . $sheets['PurchaseOrder']['cur_row'], $row['entity_no']);
				$worksheet->getCell('A' . $sheets['PurchaseOrder']['cur_row'])->getHyperlink()->setUrl(
					$host . '/index.php?module=' . $row['entitytype'] . '&action=DetailView&record=' . $row['entityid']
				);
				$worksheet->setCellValue('B' . $sheets['PurchaseOrder']['cur_row'], $row['purchased']);
				$worksheet->setCellValue('C' . $sheets['PurchaseOrder']['cur_row'], $row['received']);
				$sheets['PurchaseOrder']['cur_row']++;
				break;
			case 'SalesOrder':
				$worksheet = $workbook->setActiveSheetIndex($sheets['SalesOrder']['index']);
				$worksheet->setCellValue('A' . $sheets['SalesOrder']['cur_row'], $row['entity_no']);
				$worksheet->getCell('A' . $sheets['SalesOrder']['cur_row'])->getHyperlink()->setUrl(
					$host . '/index.php?module=' . $row['entitytype'] . '&action=DetailView&record=' . $row['entityid']
				);
				$worksheet->setCellValue('B' . $sheets['SalesOrder']['cur_row'], $row['sold']);
				$worksheet->setCellValue('C' . $sheets['SalesOrder']['cur_row'], $row['delivered']);
				$worksheet->setCellValue('D' . $sheets['SalesOrder']['cur_row'], $row['inorder']);
				$sheets['SalesOrder']['cur_row']++;
				break;
		}
	}
	$workbookWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($workbook);
	$workbookWriter->save($filename);

	$quoted = sprintf('"%s"', addcslashes(basename($filename), '"\\'));
	$size   = filesize($filename);

	header('Content-Description: File Transfer');
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment; filename=' . $quoted);
	header('Content-Transfer-Encoding: binary');
	header('Connection: Keep-Alive');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	header('Content-Length: ' . $size);

	readfile($filename);
	unlink($filename); // Delete file after presented for download
}