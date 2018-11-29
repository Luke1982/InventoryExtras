/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

function appendInvExtraInfo() {
	var invRows = document.getElementsByClassName("detailview_inventory_row");
	for (var i = 0; i < invRows.length; i++) {
		var recordId  = invRows[i].getElementsByTagName("SPAN")[0].getAttribute("vtrecordid");
		var stockCell = invRows[i].getElementsByClassName("detailview_inventory_stockcell")[0];
		appendStockCell(recordId, stockCell);
	}
}

function appendStockCell(recordId, stockCell) {
	var r = new XMLHttpRequest();
	r.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			if (r.response != "NOTHINGFOUND") {
				var stockInfo = JSON.parse(r.response);

				var stockAvailSpan = document.createElement("SPAN");
				stockAvailSpan.innerHTML = "<br /><b>" + stockInfo.stockavail.label + ":&nbsp;</b>" + stockInfo.stockavail.value;
				stockCell.appendChild(stockAvailSpan);

				var inDemandSpan = document.createElement("SPAN");
				inDemandSpan.innerHTML = "<br /><b>" + stockInfo.qtyindemand.label + ":&nbsp;</b>" + stockInfo.qtyindemand.value;
				stockCell.appendChild(inDemandSpan);

				var inOrderSpan = document.createElement("SPAN");
				inOrderSpan.innerHTML = "<br /><b>" + stockInfo.qtyinorder.label + ":&nbsp;</b>" + stockInfo.qtyinorder.value;
				stockCell.appendChild(inOrderSpan);
			}
		}
	}
	r.open("GET", "index.php?module=InventoryExtras&action=InventoryExtrasAjax&file=CommonAjaxActions&function=getStockInfoByProduct&productid=" + recordId);
	r.send();

}

window.addEventListener("load", appendInvExtraInfo);