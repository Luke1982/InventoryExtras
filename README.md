# InventoryExtras
coreBOS module that adds some additional functionality and widgets for inventory management

## What will this do?
### Salesorder information in InventoryDetails
This will create a block in the InventoryDetails module called 'SalesOrder information'. On that block, there will be three fields:
- Related Salesorder line (*Only applicable in invoice lines*). Each InventoryDetails line will, every time it is saved (or the related invoice is saved) search for a salesorder line that this product was sold on. Multiple invoicelines could thus be related to a single salesorder line. This is because you may invoice your salesorder in multiple invoices.
- Quantity in order. This field only makes sense when you're watching an inventorydetails record that is related to a salesorder. It tells you how many items are still in order for this salesorder line. That is: the quantity of the line minus the sum of all invoice lines that say they are related to this salesorder line.
- Quantity invoiced (*Only applicable on salesorder lines*): This will tell you how many of the quantity sold on this line are already invoiced. This could be spread out across multiple invoices.

### Quantity in order in products
There will be a new field in products called quantity in order. That field will represent the sum of all inventorydetails records that have this product ID *and* are related to a salesorder

### Available stock in Products
Products will receive a new field that represents the quantity you have available in reality. This no. will simply be the stock you have minus the quantity in order there is for this product (field above)

### Order recommendation field in Products
A field will be installed in Products that recommends you how many items you should order. The formula is:
- (Quantity in order + Reorder level) -/- (Qty in Stock + Qty in Demand)

This field wil **not** be manipulated by InventoryExtras, since there are multiple values/fields that can influence this field, that are not caught by InventoryExtras. You could for instance change your stock level manually, or change the Reorderlevel manually.

Doing **some** of the work hardcoded in InventoryExtras would mean you would **still** have to set the same calculation in other placed (WorkFlows). This would create unclarity and leaved a lot of room for errors. Therefor I've chosen to not do anything at all to this field and leave you in charge of how and when you want to do it. You should use [InventoryMutations](https://github.com/Luke1982/InventoryMutations) and create a workflow task that updates the field on every creation of an InventoryMutation record. Set that task to do its work **last**, since it needs all other values to be updated first.

### Maximum stock field in Products
No logic attached, but you could use this to set your maximum stock and send out an alert when there are too many in stock

### Pending orders widget in Products
Products will receive a new widget where there will be a list of all salesorders that have an inventorydetails line related to it where the product ID is this one. The widget will show a link to the salesorder and the quantity in order this salesorder represents. This quantity could be negative! If you for instance have a salesorder that sells a product once, but there are more than one sold on invoices related to this salesorder, the value will be negative.

### Pending backorder widget in Products
A second widget will show you all the PurchaseOrders that this product is still in order on. It collects the products and shows you the quantity of the line on the purchaseorder **minus** the units delivered/received. That should give you a good overview of the quantity's you have yet to receive.

### Update the Qty in demand field
When an inventorydetails line is saved, the module will look for all lines that have a similar product ID and are also related to a PurchaseOrder. It will then take **all** the quantity's from those purchaseorders, deduct the units delivered/received and update the field in the product with the result. So basically, the result will be the quantity you have in demand out at your suppliers minus the ones you have already received. This does **not** look at the status of the PurchaseOrder. In stead, we will do something explained below:

### Update all lines when a PurchaseOrder is marked as received
It will install a workflow that allows you to create a custom workflow task. This task will alter all the inventorydetails records related to this record (you could tie in to any module that has inventorydetails). The alteration will be that all the units delivered/received will be set equal to the quantity of the line. That way you could make a single purchaseorder as 'Goods Received' and have all the lines be set equal to their quantity's in the units delivered/received field, which will fire related events, like create inventorymutations or the aftersave events on this module.

## When will it do it?
You need to have a coreBOS installation that is more recent than mid-december 2018. This module takes use of the new aftersave.first event which was introduced then. The reason behind this is mainly that it will launch before workflows this way. This is because we want to set some calculated fields (like total quantity in order in products) and probably use a workflow directly after that, that uses that newly set value on the product.

When a line is deleted in a salesorder, the module will look for invoice lines that referenced that salesorder line and remove the reference. After that, it'll force another `aftersave.first` on that invoice line. That way, when you remove a line in a salesorder and replace it with a new line with the same product, the reference is updated.

## Enhance lines in SalesOrders and PurchaseOrders
Some additional information will be added to the 'Information' column in inventory lines when in SalesOrders and PurchaseOrders:
#### In SalesOrders
You will see, per line:
- The qty in stock available for this product. This takes the qty in order into account
- The qty in demand for this product. How many are there in backorder at your suppliers?
- The total no. in order for this product.
- The quantity to order for this product, see 'order recommendation' field above
- The quantity invoiced for this line

#### In PurchaseOrders
- The quantity to order for this product, see 'order recommendation' field above
- The vendor part no. for this product

### Leave stock alone checkbox in SalesOrders
A new checkbox will be installed on salesorders. When you check it, this order will not affect the quantity in order on any of its lines or on the related products.

## After installation
The module installs two cbupdates, which you should run immediately after installing the module
#### updateNosInOrder
This update will first set all the database values for the checkbox 'Leave stock alone' to 0 in favor of NULL. This is so the query that calculates the no's in order can work. When you create a new SalesOrder, that value will be set to 0 anyway (if you don't check the box). It will then start off by setting all the no's in order to the difference between the quantity of the SalesOrder line and the quantity delivered for that line. Lastly, and most demanding (this update will take a **long** time and need 2GB of RAM, especially when you have many lines) the update will find all invoice lines, find the matching salesorder lines and then update them accordingly. This will also trigger updating the quantity in order (total) on the product.
#### updateQtysInDemand
This will get all the purchaseorder lines that have a status of 'Received Shipment' or 'Delivered' and set all units received equal to the quantity of the line. It will then save the line, which in turn will update the Qty in Demand on the product.

## Known issues
There are still some issues regarding the installation in existing installations. Especially when you have many salesorders and inventorydetails lines it'll be hard for the module to fetch all the data correctly and set all the values right. I'm still working on pinpointing exactly where the error lies.
