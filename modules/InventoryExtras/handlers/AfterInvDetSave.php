<?php

Class AfterInvDetSave extends VTEventHandler {
	public function handleEvent($eventName, $entityData){
		global $current_user, $adb;

		$moduleName = $entityData->getModuleName();
		if ($moduleName == 'InventoryDetails') {
			require_once 'modules/InventoryExtras/InventoryExtras.php';

			$invdet_id = $entityData->getId();
			$invdet_data = $entityData->getData();
			$invext = new InventoryExtras();
			$invext_prefix = $invext->getPrefix();

			$related_type = getSalesEntityType($invdet_data['related_to']);
			$related_item = getSalesEntityType($invdet_data['productid']);

			if ($related_item == 'Products') {
				$sibl = $invext->getSiblingFromInvoice($invdet_data['related_to'], $invdet_data['productid']);

				if ($related_type == 'Invoice' && !!$sibl && ($sibl[$invext_prefix . 'inv_sibling'] == '' || $sibl[$invext_prefix . 'inv_sibling'] == '0')) {
					// This line is related to an invoice and a sibling was found. The sibling does not have
					// a related inventorydetails line yet
					$invext->updateInvDetRec($sibl['id'], $sibl['quantity'], $invdet_id, $invdet_data['quantity']);			
				} else if ($related_type == 'Invoice' && !!$sibl && ($sibl[$invext_prefix . 'inv_sibling'] != '' || $sibl[$invext_prefix . 'inv_sibling'] != '0')) {
					// Sibling was found, but it already has a related line
					if ($sibl[$invext_prefix . 'inv_sibling'] == $invdet_id) {
						// Already related to this line, update to be sure
						$invext->updateInvDetRec($sibl['id'], $sibl['quantity'], $invdet_id, $invdet_data['quantity']);
					}
				}

				if ($related_type == 'SalesOrder') {
					// Update the related product field with the summ of all invoice lines
					$qty_in_order_tot = $invext->getQtyInOrderByProduct($invdet_data['productid']);
					$invext->updateProductQtyInOrder($invdet_data['productid'], $qty_in_order_tot);
				}
			}
		}
	}
}