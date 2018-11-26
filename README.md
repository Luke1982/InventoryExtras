# InventoryExtras
coreBOS module that adds some additional functionality and widgets for inventory management

## What will this do?
### Salesorder information in InventoryDetails
This will create a block in the InventoryDetails module called 'SalesOrder information'. On that block, there will be two fields:
- Related invoiceline. This field will only be filled when this inventorydetails line is related to a salesorder with a link to the related inventorydetails line that matches this line. For this to happen, there needs to be at least one inventorydetails line on an invoice related to the related SalesOrder where the product ID is similar
- Quantity in order: This field will represent the difference between the qty in a related invoiceline and the line itself. If there is no invoiceline related to this line, the qty in order will be the qty of the line. When the related entity is not a salesorder, the qty in order will be zero.

### Quantity in order in products
There will be a new field in products called quantity in order. That field will represent the sum of all inventorydetails records that have this product ID *and* are related to a salesorder

### Available stock in Products
Products will receive a new field that represents the quantity you have available in reality. This no. will simply be the stock you have minus the quantity in order there is for this product (field above)

### Pending orders widget in Products
Products will receive a new widget where there will be a list of all salesorders that have an inventorydetails line related to it where the product ID is this one, and the 'quantity in order' is greater than zero. The widget will show a link to the salesorder and the quantity in order this salesorder represents.

### Pending backorder widget in Products
A second widget will show you all the PurchaseOrders that this product is still in order on. It collects the products and shows you the quantity of the line on the purchaseorder **minus** the units delivered/received. That should give you a good overview of the quantity's you have yet to receive.

### Update the Qty in demand field
When an inventorydetails line is saved, the module will look for all lines that have a similar product ID and are also related to a PurchaseOrder. It will then take **all** the quantity's from those purchaseorders, deduct the units delivered/received and update the field in the product with the result. So basically, the result will be the quantity you have in demand out at your suppliers minus the ones you have already received. This does **not** look at the status of the PurchaseOrder. In stead, we will do something explained below:

### Update all lines when a PurchaseOrder is marked as received (TO-DO!!)
It will install a workflow that allows you to create a custom workflow task. This task will alter all the inventorydetails records related to this record (you could tie in to any module that has inventorydetails). The alteration will be that all the units delivered/received will be set equal to the quantity of the line. That way you could makr a single purchaseorder as 'Goods Received' and have all the lines be set equal to their quantity's in the units delivered/received field, which will fire related events, like create inventorymutations or the aftersave events on this module.

## Known limitations
The biggest limitation is that getting the 'sibling' inventorydetails line from an invoice for a salesorder line is not easy. The module will collect the inventorydetails lines from salesorders when a line that belongs to an invoice is edited or created, but stop when it finds one. That means that when you create multiple invoices for a salesorder and list a product that lives on that salesorder in both invoices (if, for instance you have two invoices) **only** the first one will be selected, since the module has no way of knowing which one you want to regard as the 'opposite' line.

Also, when you list a product more than once on a salesorder, the module will select the first one it encounters in invoices, meaning multiple lines on a salesorder could be matched to the same line on an invoice. I will try to perfect this mechanism, but be aware that in edge cases, the module won't be able to match the lines correctly.
