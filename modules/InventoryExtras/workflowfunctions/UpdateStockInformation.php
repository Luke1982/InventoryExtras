<?php
/*************************************************************************************************
 * Copyright 2022 MajorLabel -- This file is a part of MajorLabel coreBOS Customizations.
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

/**
 * Update the inventory field of the given products.
 * Those are:
 * - Stock level
 * - Quantity in order
 * - Quantity in backorder
 * - Available stock (stock - qty in order)
 * - Qty to order ((qty in order + min stock) - (stock + qty in backorder))
 *
 * @param  array Array of product CRM ID's
 * @return void
 * @throws None
 */
function updateProductInventoryFieldsFor(array $products = array()) : void {
	require_once 'modules/InventoryExtras/InventoryExtras.php';
	$ie = new InventoryExtras();

	$stock_object = $ie->getCurrentProductStockLevelsObject($products);
	$inorder_object = $ie->getCurrentProductOrderLevelsObject($products);
	$backorder_object = $ie->getCurrentProductBackorderLevelsObject($products);

	$product_info = combineProductObjects(
		$products,
		$stock_object,
		$inorder_object,
		$backorder_object
	);
	foreach ($product_info as $product) {
		$ie->updateProduct($product);
	}
}

/**
 * Combines all three database object about products
 * (stock, in order and backorder) to a single products
 * array, keys based on product CRM ID's
 *
 * @param array  $product_ids      The product ID's in question
 * @param object $stock_object     The database object with the stock information
 * @param object $inorder_object   The database object with the information
 * 								   about the quantity in order
 * @param object $backorder_object The database object with the information
 * 								   about the quantity in backorder
 * @return array $products 		   The combined array with current product
 * 								   information
 * @throws None
 */
function combineProductObjects(
	array  $product_ids,
	object &$stock_object,
	object &$inorder_object,
	object &$backorder_object
) : array {

	require_once 'modules/InventoryExtras/workflowfunctions/CBXGenerator.php';

	$products = array_fill_keys(array_values($product_ids), array());
	$objects = array($stock_object, $inorder_object, $backorder_object);
	array_walk($objects, function (&$object) use (&$products) {
		foreach (CBX\rowGenerator($object) as $product) {
			foreach ($product as $key => $value) {
				if (is_string($key)) {
					$products[$product['productid']][$key] = $value;
				}
			}
		}
	});
	return $products;
}