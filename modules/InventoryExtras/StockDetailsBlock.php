<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class StockDetailsBlock {
	public static function getWidget($name) {
		return (new StockDetailsBlock_RenderBlock());
	}
}

class StockDetailsBlock_RenderBlock extends StockDetailsBlock {

	/**
	 * Interface implementation method that should return
	 * the HTML rendered on screen
	 *
	 * @param Array $context  Context array about the parent
	 *
	 * @throws None
	 * @author MajorLabel <info@majorlabel.nl>
	 * @return None
	 */
	public function process(array $context) : void {
		$smarty = new vtigerCRM_Smarty;
		$smarty->assign('fields', $context['FIELDS']);
		$smarty->display('modules/InventoryExtras/StockDetails.tpl');
	}
}