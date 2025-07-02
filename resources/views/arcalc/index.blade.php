<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amazon Revenue Calculator</title>
    <link rel="Icon" href="{{ asset('images/gtslogo.png') }}">
    <link rel="stylesheet" href="{{ asset('css/arcalc.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Sarabun:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800&display=swap"
        rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>

    <div class="container mt-5">

        <div class="header-wrapper">
            <img src="{{ asset('images/logo.png') }}" alt="Company Logo" class="logo img-fluid">
            <h1 class="main-heading">Amazon Revenue Calculator</h1>
        </div>

        <!-- Windows -->
        <div id="material-purchase-cost" class="window">
            <h3 style="color: #00569f;">Material Purchase Cost</h3>
            <p>for Calculate <strong>Material Purchase Cost</strong> enter the required data</p>
        </div>

        <form id="user-form" enctype="multipart/form-data">
        <input type="hidden" id="serialNumber" name="serial_number" value="{{ $product->serial_number ?? '' }}">

            <div class="row">
                <!-- Customer Name -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="customerName">Customer Name</label>
                        <input type="text" class="form-control" id="customerName" name="customerName" placeholder="Enter customer name" value="{{ old('customerName', $entry->customer_name ?? '') }}">
                    </div>
                </div>
        
                <!-- Brand Name -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="brand-name">Brand Name</label>
                        <input type="text" class="form-control" id="brand-name" name="brandName" placeholder="Enter brand name" value="{{ old('brandName', $entry->brand_name ?? '') }}">
                    </div>
                </div>
        
                <!-- ASIN -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="asin">ASIN</label>
                        <input type="text" class="form-control" id="asin" name="asin" placeholder="Enter ASIN" value="{{ old('asin', $entry->asin ?? '') }}">
                    </div>
                </div>
        
                <!-- Brief Description -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="brief-description">Brief Description</label>
                        <input type="text" id="brief-description" name="briefDescription" class="form-control" placeholder="Enter brief description" value="{{ old('briefDescription', $entry->brief_description ?? '') }}">
                    </div>
                </div>
        
                <!-- Shipping Cost -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="shipping-cost">Shipping Cost</label>
                        <input type="number" step="any" class="form-control" id="shipping-cost" name="shippingCost" placeholder="Enter shipping cost" value="{{ old('shipping-cost', $entry->shipping_cost ?? '') }}">
                    </div>
                </div>
        
                <!-- Labeling Charges -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="labeling-charges">Labeling Charges</label>
                        <input type="number" step="any" class="form-control" name="labelingCharges" id="labeling-charges" placeholder="Enter labeling charges" value="{{ old('labelingCharges', $entry->labeling_charges ?? '') }}">
                    </div>
                </div>
        
                <!-- Goods Cost -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="goods-cost">Goods Cost</label>
                        <input type="number" step="any" class="form-control" name="goodsCost" id="goods-cost" placeholder="Enter goods cost" value="{{ old('goodsCost', $entry->goods_cost ?? '') }}">
                    </div>
                </div>
        
                <!-- Fulfillment -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="fulfillment">Fulfillment</label>
                        <input type="number" step="any" class="form-control" name="fulfillmentFees" id="fulfillment" placeholder="Enter fulfillment fee" value="{{ old('fulfillmentFees', $entry->fulfillment ?? '') }}">
                    </div>
                </div>
        
                <!-- Amazon Storage Charges -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="amazon-storage-charges">Amazon Storage Charges</label>
                        <input type="number" step="any" class="form-control" name="amazonStorageCharges" id="amazon-storage-charges" placeholder="Enter Amazon storage charges" value="{{ old('amazonStorageCharges', $entry->amazon_storage_charges ?? '') }}">
                    </div>
                </div>
        
                <!-- Sell Price -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="sell-price">Sell Price</label>
                        <input type="number" step="any" class="form-control" name="sellPrice" id="sell-price" placeholder="Enter sell price" value="{{ old('sellPrice', $entry->sell_price ?? '') }}">
                    </div>
                </div>
        
                <!-- Origin of Purchase -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="origin-purchase">Origin of Purchase</label>
                        <input type="text" class="form-control" name="originPurchase" id="origin-purchase" placeholder="Enter origin of purchase" value="{{ old('originPurchase', $entry->origin_purchase ?? '') }}">
                    </div>
                </div>
        
                <!-- Units Sold -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="units-sold">Units Sold</label>
                        <input type="number" step="any" class="form-control" name="unitsSold" id="units-sold" placeholder="Enter units sold" value="{{ old('unitsSold', $entry->units_sold ?? '') }}">
                    </div>
                </div>
        
                <!-- Tariff -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="tariff-percentage">Tariff</label>
                        <input type="number" id="tariff-percentage" name="tariffPercentage" class="form-control" placeholder="Enter tariff percentage" value="{{ old('tariffPercentage', $entry->tariff_percentage ?? '') }}">
                    </div>
                </div>
                
                <!-- Low Inventory Fee -->
                <div class="col-md-4">
                    <div class="form-check mt-4">
                        <input type="checkbox" class="form-check-input" name="lowInventoryFee" id="low-inventory-fee" {{ old('lowInventoryFee', $entry->low_inventory_fee ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="low-inventory-fee">Low Inventory Fee</label>
                    </div>
                </div>

                <!-- Description -->
                <div class="col-md-12 mt-2">
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" placeholder="Enter detailed description">{{ old('description', $entry->description ?? '') }}</textarea>
                    </div>
                </div>
        
                <!-- Product Link -->
                <div class="col-md-12 mt-2">
                    <div class="form-group">
                        <label for="product-link">Product Link</label>
                        <input type="text" id="product-link" name="productLink" class="form-control" placeholder="Enter product link" value="{{ old('tariffPercentage', $entry->product_link ?? '') }}">
                    </div>
                </div>
        
                <!-- Image Upload -->
                <div class="col-md-12 mt-2">
                    <div class="form-group">
                        <label for="image-upload">Upload Image</label>
                        <input type="file" class="form-control" name="uploadImage" id="image-upload" accept="image/*">

                        <!-- Image preview -->
                        @if(isset($entry) && $entry->image_url)
                            <img id="previewImage" src="{{ asset('storage/' . $entry->image_url) }}" alt="Current Image" width="150" class="mt-3">
                        @else
                            <img id="previewImage" src="#" alt="Preview Image" width="150" style="display: none;" class="mt-3">
                        @endif
                    </div>
                </div>
        
                <!-- Submit Button -->
                <div class="col-md-12 mt-4">
                    <button type="submit" class="btn submit-btn form-control" style="font-weight: bold;">-- SUBMIT --</button>
                </div>

                <!-- Save Changes Button -->
                <div class="col-md-12 mt-4">
                    <!-- <button type="button" id="save-changes-btn" class="btn saveChanges-btn mb-2 form-control" style="display:none; font-weight: bold;">-- Save Changes --</button> -->
                    <button type="button" id="save-changes-btn" class="btn saveChanges-btn mb-2 form-control" style="display:none; font-weight: bold;">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true" id="save-spinner"></span>
                        <span id="save-btn-text">-- Save Changes --</span>
                    </button>
                </div>
            </div>
        </form>        

        <div class="mb-3 mt-3">
            <label for="brandFilter" class="form-label" style="font-weight: bold;">Filter by Brand Name:</label>
            <select id="brandFilter" class="form-select" style="max-width: 300px; display: inline-block;">
                <option value="all">All Brands</option>
            </select>
        </div>

        <div class="mb-3 mt-3">
            <label for="customerFilter" class="form-label" style="font-weight: bold;">Filter by Customer Name:</label>
            <select id="customerFilter" class="form-select" style="max-width: 300px; display: inline-block;">
                <option value="all">All Customers</option>
            </select>
        </div>       

        <!-- Table -->
        <div class="mt-5">
            <h3>Details</h3>
            <table class="table table-bordered" id="data-table">
                <thead style="background-color: #00569f !important; color: white !important;">
                    <tr>
                        <th>SN</th>
                        <th>Customer Name</th>
                        <th>Brand Name</th>
                        <th>ASIN</th>
                        <th>Brief Description</th>
                        <th>Buy Price</th>
                        <th>Sell Price</th>
                        <th>Min Sell Price</th>
                        <th>Profit</th>
                        <th>Target Buy Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <button id="downloadExcel" class="btn d-flex align-items-center gap-2 mb-3 custom-excel-button">
            <img src="https://cdn-icons-png.flaticon.com/512/732/732220.png" alt="Excel Icon"
                style="width: 24px; height: 24px;">
            <span>Download Excel</span>
        </button>

        <table id="combined-data-table" style="display: none;">
            <thead>
                <tr>
                    <th>Serial Number</th>
                    <th>Customer Name</th>
                    <th>Brand Name</th>
                    <th>ASIN</th>
                    <th>Brief Description</th>
                    <th>Shipping Cost</th>
                    <th>Labeling Charges</th>
                    <th>Goods Cost</th>
                    <th>Fulfillment</th>
                    <th>Sell Price</th>
                    <th>Amazon Storage Charges</th>
                    <th>Origin Purchase</th>
                    <th>Units Sold</th>
                    <th>Low Inventory Fee</th>
                    <th>Description</th>
                    <th>Product Link</th>
                    <th>Image URL</th>
                    <th>Min Sell Price</th>
                    <th>Profit</th>
                    <th>Target Buy Price</th>
                    <th>Net Proceeds</th>
                    <th>Amazon Fees</th>
                    <th>Tariff</th>
                    <th>Return</th>
                    <th>Actual Cost</th>
                </tr>
            </thead>
            <tbody id="combined-data-body">
            </tbody>
        </table>

        <div id="customTooltip" class="custom-tooltip"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/arcalc.js') }}"></script>

</body>

</html>