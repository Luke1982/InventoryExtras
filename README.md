# InventoryExtras
coreBOS module that adds some additional functionality and widgets for inventory management

## What will this do?
### Salesorder information in InventoryDetails
This will create a block in the InventoryDetails module called 'SalesOrder information'. On that block, there will be two fields:
- Related invoiceline. This field will only be filled when this inventorydetails line is related to a salesorder with a link to the related inventorydetails line that matches this line. For this to happen, there needs to be at least one inventorydetails line on an invoice related to the related SalesOrder where the product ID is similar
- Quantity in order: This field will represent the difference between the qty in a related invoiceline and the line itself. If there is no invoiceline related to this line, the qty in order will be the qty of the line. When the related entity is not a salesorder, the qty in order will be zero.

### Quantity in order in products
There will be a new field in products called quantity in order. That field will represent the sum of all inventorydetails records that have this product ID *and* are related to a salesorder

### Pending orders widget in Products
Products will receive a new widget where there will be a list of all salesorders that have an inventorydetails line related to it where the product ID is this one, and the 'quantity in order' is greater than zero. The widget will show a link to the salesorder and the quantity in order this salesorder represents.

## Known limitations
The biggest limitation is that getting the 'sibling' inventorydetails line from an invoice for a salesorder line is not easy. The module will collect the invoices related to the salesorder that this line belongs to and select the first line that has a similar product. That means that when you create multiple invoices for a salesorder and list a product that lives on that salesorder in both invoices (if, for instance you have two invoices) **only** the first one will be selected, since the module has no way of knowing which one you want to regard as the 'opposite' line.

Also, when you list a product more than once on a salesorder, the module will select the first one it encounters in invoices, meaning multiple lines on a salesorder could be matched to the same line on an invoice. I will try to perfect this mechanism, but be aware that in edge cases, the module won't be able to match the lines correctly.
