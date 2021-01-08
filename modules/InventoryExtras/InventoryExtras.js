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
	var sourceRecId = document.getElementsByName("record")[0].value;
	for (var i = 0; i < invRows.length; i++) {
		var recordId  = invRows[i].getElementsByTagName("SPAN")[0].getAttribute("vtrecordid");
		var stockCell = invRows[i].getElementsByClassName("detailview_inventory_stockcell")[0];
		var qtyCell = invRows[i].getElementsByClassName("detailview_inventory_qtycell")[0];
		appendCells(sourceRecId, recordId, stockCell, qtyCell, i+1);
	}
}

function appendCells(sourceRecId, recordId, stockCell, qtyCell, seq) {
	var r = new XMLHttpRequest();
	r.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			if (r.response != "NOTHINGFOUND") {
				var stockInfo = JSON.parse(r.response);
				switch (gVTModule) {
					case "SalesOrder":
						setSoStockCell(stockCell, stockInfo);
						break;
					case "PurchaseOrder":
						setPoCells(stockCell, stockInfo, qtyCell);
				}
			}
		}
	}
	r.open("GET", "index.php?module=InventoryExtras&action=InventoryExtrasAjax&file=CommonAjaxActions&function=getInfoByProduct&productid=" 
		           + recordId + "&record=" + sourceRecId + "&seq=" + seq);
	r.send();

}

function setSoStockCell(stockCell, stockInfo) {
	var qtyInvoicedSpan = document.createElement("SPAN");
	qtyInvoicedSpan.innerHTML = "<br /><b>" + stockInfo.qtyinvoiced.label + ":&nbsp;</b>" + stockInfo.qtyinvoiced.value;
	stockCell.appendChild(qtyInvoicedSpan);
		
	var stockAvailSpan = document.createElement("SPAN");
	stockAvailSpan.innerHTML = "<br /><b>" + stockInfo.stockavail.label + ":&nbsp;</b>" + stockInfo.stockavail.value;
	stockCell.appendChild(stockAvailSpan);

	var inDemandSpan = document.createElement("SPAN");
	inDemandSpan.innerHTML = "<br /><b>" + stockInfo.qtyindemand.label + ":&nbsp;</b>" + stockInfo.qtyindemand.value;
	stockCell.appendChild(inDemandSpan);

	var inOrderSpan = document.createElement("SPAN");
	inOrderSpan.innerHTML = "<br /><b>" + stockInfo.qtyinorder.label + ":&nbsp;</b>" + stockInfo.qtyinorder.value;
	stockCell.appendChild(inOrderSpan);

	var qtyToOrderSpan = document.createElement("SPAN");
	qtyToOrderSpan.innerHTML = "<br /><b>" + stockInfo.qtytoorder.label + ":&nbsp;</b>" + stockInfo.qtytoorder.value;
	stockCell.appendChild(qtyToOrderSpan);	
}

function setPoCells(stockCell, stockInfo, qtyCell) {
	console.log(stockInfo);
	
	var venPartNoSpan = document.createElement("SPAN");
	venPartNoSpan.innerHTML = "<br /><b>" + stockInfo.venpartno.label + ":&nbsp;</b>" + stockInfo.venpartno.value;
	stockCell.appendChild(venPartNoSpan);

	var qtyToOrderSpan = document.createElement("SPAN");
	qtyToOrderSpan.innerHTML = "<br /><b>" + stockInfo.qtytoorder.label + ":&nbsp;</b>" + stockInfo.qtytoorder.value;
	stockCell.appendChild(qtyToOrderSpan);

	stockCell.removeChild(stockCell.childNodes[2])
	stockCell.removeChild(stockCell.childNodes[1])
}

window.addEventListener("load", appendInvExtraInfo);