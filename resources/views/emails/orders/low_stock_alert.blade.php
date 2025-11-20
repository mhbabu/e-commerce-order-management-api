# ⚠ Low Stock Alert

Dear Vendor,

Your product **{{ $product->name }}** is running low on stock.

### Variant:
- Color: **{{ $variant->color }}**
- Storage: **{{ $variant->storage }}**

### Current Stock:
- **{{ $inventory->quantity }} units remaining**

### Low Stock Threshold:
- **{{ $inventory->low_stock_threshold }} units**

Please restock as soon as possible.

Thanks,  
Laravel
