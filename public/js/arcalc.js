let originalId = null;
let rowCount = 0;

$(document).ready(function () {

    function calculateFields(data) {
        data.tariffPercentage = data.tariffPercentage || 0;
        data.amazonFee = data.sellPrice * 0.18
        data.return = data.goodsCost > 2 ? 0.5 : 0.2;
        data.tariff = (data.goodsCost * data.tariffPercentage) / 100;
        data.actualCost = data.shippingCost + data.labelingCharges + data.goodsCost + data.fulfillment + data.amazonFee + data.amazonStorageCharges + data.return + data.lowInventoryFee + data.tariff;
        data.profit = data.sellPrice - data.actualCost;
        data.netProceeds = data.sellPrice - (data.amazonFee + data.fulfillment);
        data.rockBottomSellPrice = data.sellPrice - data.profit;
        data.targetBuyPrice = data.sellPrice - (data.shippingCost + data.fulfillment + data.amazonFee + data.amazonStorageCharges + data.return + data.lowInventoryFee + data.tariff + data.labelingCharges) - 1;
        return data;
    }

    // Function to reset the form after submit
    function resetForm() {
        $('#user-form')[0].reset();  // Reset all form fields
        $('#user-form').data('existing-image', '');  // Clear the existing image data if any
        editingRow = null;  // Reset the editingRow to null
    }

    async function readImage(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result);
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });
    }

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('#user-form').on('submit', async function (e) {
        e.preventDefault();

        const imageFile = $('#image-upload')[0].files[0];
        let imageUrl = '';
        if (imageFile) {
            imageUrl = await readImage(imageFile);
        } else {
            imageUrl = $('#user-form').data('existing-image') || '';
        }

        // Get the tariff percentage value
        const tariffPercentage = parseFloat($('#tariff-percentage').val()) || 0;

        let serialNumberToUse;

        if (originalId) {
            // If editing an existing row ➔ Keep the existing serial number
            serialNumberToUse = $('#data-table tbody tr').filter(function () {
                return $(this).data('id') == originalId;
            }).find('td:eq(0)').text().trim();
        } else {
            // If adding a new row ➔ Find the next available serial number
            const existingSerials = [];
            $('#data-table tbody tr').each(function () {
                if (!$(this).hasClass('detail-row')) {
                    const serial = parseInt($(this).find('td:eq(0)').text().trim(), 10);
                    if (!isNaN(serial)) {
                        existingSerials.push(serial);
                    }
                }
            });

            existingSerials.sort((a, b) => a - b);

            serialNumberToUse = 1;
            for (let i = 0; i < existingSerials.length; i++) {
                if (serialNumberToUse < existingSerials[i]) {
                    break;
                }
                serialNumberToUse++;
            }
        }

        const formData = {
            serialNumber: serialNumberToUse,
            customerName: $('#customerName').val(),
            brandName: $('#brand-name').val(),
            asin: $('#asin').val(),
            briefDescription: $('#brief-description').val(),
            shippingCost: parseFloat($('#shipping-cost').val()) || 0,
            labelingCharges: parseFloat($('#labeling-charges').val()) || 0,
            goodsCost: parseFloat($('#goods-cost').val()) || 0,
            fulfillment: parseFloat($('#fulfillment').val()) || 0,
            sellPrice: parseFloat($('#sell-price').val()) || 0,
            amazonStorageCharges: parseFloat($('#amazon-storage-charges').val()) || 0,
            originPurchase: $('#origin-purchase').val(),
            unitsSold: $('#units-sold').val(),
            tariffPercentage: tariffPercentage,
            lowInventoryFee: $('#low-inventory-fee').is(':checked') ? 0.97 : 0,
            description: $('#description').val().trim(),
            productLink: $('#product-link').val(),
            imageUrl: imageUrl
        };

        const calculatedData = calculateFields(formData);
        // Add the new or updated row to the table
        addRow(calculatedData);

        addCombinedDataRow(serialNumberToUse, formData, calculatedData);

        // Reset the form
        resetForm();

        originalId = null;

        $('#previewImage').hide();

        const rowDataArray = [];
        const tableRows = $('#combined-data-table tbody tr');

        // Loop through each row
        tableRows.each(function () {
            const row = $(this).find('td');
            const rowData = {
                serial_number: row.eq(0).text().trim(),
                customer_name: row.eq(1).text().trim(),
                brand_name: row.eq(2).text().trim(),
                asin: row.eq(3).text().trim(),
                brief_description: row.eq(4).text().trim(),
                shipping_cost: parseFloat(row.eq(5).text().trim()) || 0,
                labeling_charges: parseFloat(row.eq(6).text().trim()) || 0,
                goods_cost: parseFloat(row.eq(7).text().trim()) || 0,
                fulfillment: parseFloat(row.eq(8).text().trim()) || 0,
                sell_price: parseFloat(row.eq(9).text().trim()) || 0,
                amazon_storage_charges: parseFloat(row.eq(10).text().trim()) || 0,
                tariff_percentage: parseFloat(row.eq(11).text().trim()) || 0,
                origin_purchase: row.eq(12).text().trim(),
                units_sold: parseFloat(row.eq(13).text().trim()) || 0,
                low_inventory_fee: row.eq(14).text().trim().toLowerCase() === 'yes' ? 0.97 : 0,
                description: row.eq(15).text().trim(),
                product_link: row.eq(16).text().trim(),
                image_url: row.eq(17).text().trim(),
                min_sell_price: parseFloat(row.eq(18).text().trim()) || 0,
                profit: parseFloat(row.eq(19).text().trim()) || 0,
                target_buy_price: parseFloat(row.eq(20).text().trim()) || 0,
                net_proceeds: parseFloat(row.eq(21).text().trim()) || 0,
                amazon_fees: parseFloat(row.eq(22).text().trim()) || 0,
                tariff: parseFloat(row.eq(23).text().trim()) || 0,
                return_value: parseFloat(row.eq(24).text().trim()) || 0,
                actual_cost: parseFloat(row.eq(25).text().trim()) || 0
            };

            rowDataArray.push(rowData);
        });

        // AJAX POST to your server route (adjust URL as needed)
        $.ajax({
            url: '/handle-data', // Replace with your route
            type: 'POST',
            data: {
                table_data: rowDataArray
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')  // Include CSRF token
            },
            success: function (response) {
                alert('Data submitted successfully!');
                console.log(response);
            },
            error: function (xhr, status, error) {
                console.error('Error:', error);
                alert('Error submitting data.');
            }
        });

        localStorage.setItem('scrollToBottom', 'true');
        location.reload();
    });

    if (localStorage.getItem('scrollToBottom') === 'true') {
        setTimeout(function () {
            $('html, body').animate({
                scrollTop: $('#data-table').offset().top + $('#data-table').height()
            }, 'slow');
            localStorage.removeItem('scrollToBottom');
        }, 1000);
    }

    function addRow(data) {
        // const description = $('#description').val();
        const description = data.description || '-';

        // Helper function to safely format numbers
        const formatNumber = (value) => {
            return (value !== undefined && value !== null && !isNaN(value)) ? parseFloat(value).toFixed(2) : '-';
        };

        const rowHtml = `
        <tr data-id="${data.id}"
            data-row="${rowCount}" 
            data-brand="${data.brandName}" 
            data-customer="${data.customerName}" 
            data-tariff-percentage="${data.tariffPercentage || 0}">
            <td class="serial-number" data-bs-toggle="tooltip" title="SN">--</td>
            <td class="customerName" data-bs-toggle="tooltip" title="Customer Name">${data.customerName}</td>
            <td class="productBrand" data-bs-toggle="tooltip" title="Brand Name">${data.brandName}</td>
            <td data-bs-toggle="tooltip" title="ASIN">${data.asin}</td>
            <td data-bs-toggle="tooltip" title="Brief Description">${data.briefDescription}</td>
            <td data-bs-toggle="tooltip" title="Buy Price (Goods Cost)">${data.goodsCost || '-'}</td>
            <td data-bs-toggle="tooltip" title="Sell Price">${data.sellPrice || '-'}</td>
            <td data-bs-toggle="tooltip" title="Min Sell Price">${formatNumber(data.rockBottomSellPrice)}</td>
            <td data-bs-toggle="tooltip" title="Profit">${data.profit !== undefined ? formatNumber(data.profit) : '-'}</td>
            <td data-bs-toggle="tooltip" title="Target Buy Price">${formatNumber(data.targetBuyPrice)}</td>
            <td>
                <button class="btn btn-success btn-sm edit-btn mb-2">Edit</button>
                <button class="btn btn-danger btn-sm delete-btn mb-2">Delete</button>
            </td>
        </tr>
        <tr class="detail-row" style="display: none;">
            <td colspan="11">
                <div class="row">
                    <!-- Image adjusted to cover the left side and centered -->
                    <div class="col-md-3 d-flex justify-content-center align-items-center position-relative">
                        <img id="image-detail" src="${data.imageUrl}" class="img-fluid detail-box-img">
                        <!-- Units Sold moved to bottom-left of the image -->
                        <div class="units-sold" style="position: absolute; bottom: 10px; left: 10px; background-color: rgba(0, 0, 0, 0.5); color: white; padding: 5px;">
                            Units Sold: ${data.unitsSold}
                        </div>
                    </div>
    
                    <!-- Description at the top-left (changed to left alignment) -->
                    <div class="col-md-9">
                        <div class="row">
                            <div class="col-md-12" style="text-align: left;">
                                <h4>Description:</h4>
                                <p class="description">${description}</p>
    
                                ${data.productLink ? `<a id="productLink" href="${data.productLink}" target="_blank" style="color:black; text-decoration:none; word-break: break-word;">View Product &#8599</a>` : ''}
                            </div>
                        </div>
    
                        <!-- 2x2 Grid for the other data -->
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-group">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Profit:
                                        <div class="value-box"><span class="dollar-sign">$</span><span class="profit">${formatNumber(data.profit)}</span></div>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Net Proceeds:
                                        <div class="value-box"><span class="dollar-sign">$</span><span class="net-proceeds"><span class="net-proceeds">${formatNumber(data.netProceeds)}</span></span></div>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Amazon fees:
                                        <div class="value-box"><span class="dollar-sign">$</span><span class="amazon-fees"><span class="amazon-fees">${formatNumber(data.amazonFee)}</span></span></div>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Tariff:
                                        <div class="value-box"><span class="dollar-sign">$</span><span class="tariff"><span class="tariff">${formatNumber(data.tariff)}</span></span></div>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-group">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Shipping Cost:
                                        <div class="value-box"><span class="dollar-sign">$</span><span class="shipping-cost">${data.shippingCost}</span></div>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Labeling Charges:
                                        <div class="value-box"><span class="dollar-sign">$</span><span class="labeling-charges">${data.labelingCharges}</span></div>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Return:
                                        <div class="value-box"><span class="dollar-sign">$</span><span class="return">${data.return}</span></div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-group">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Fulfillment fees:
                                        <div class="value-box"><span class="dollar-sign">$</span><span class="fulfillment-fees">${data.fulfillment}</span></div>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Storage Cost:
                                        <div class="value-box"><span class="dollar-sign">$</span><span class="storage-cost">${data.amazonStorageCharges}</span></div>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-group">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Low Inventory Fee:
                                        <div class="value-box"><span class="dollar-sign">$</span><span class="low-inventory-fee">${formatNumber(data.lowInventoryFee)}</span></div>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Actual Cost:
                                        <div class="value-box"><span class="dollar-sign">$</span><span class="actual-cost">${formatNumber(data.actualCost)}</span></div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-group">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Origin of Purchase:
                                        <div class="value-box"><span class="origin-of-purchase">${data.originPurchase || '-'}</span></div> 
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Min Sell Price:
                                        <div class="value-box"><span class="dollar-sign">$</span><span class="min-sell-price">${formatNumber(data.rockBottomSellPrice)}</span></div>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-group">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Target Buy Price:
                                        <div class="value-box"><span class="dollar-sign">$</span><span class="target-buy-price">${formatNumber(data.targetBuyPrice)}</span></div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        `;

        $('#data-table tbody').append(rowHtml);
        rowCount++;
        $('[data-bs-toggle="tooltip"]').tooltip();

        updateFilters(data);
        resetSerialNumbers();
    }

    let originalId = null;

    $('#data-table').on('click', '.edit-btn', function () {
        const row = $(this).closest('tr');

        // Pre-filling the form inputs with the values from the table
        $('#customerName').val(row.find('td:eq(1)').text());
        $('#brand-name').val(row.find('td:eq(2)').text());
        $('#asin').val(row.find('td:eq(3)').text());
        $('#brief-description').val(row.find('td:eq(4)').text());
        $('#goods-cost').val(row.find('td:eq(5)').text());
        $('#sell-price').val(row.find('td:eq(6)').text());

        // Find the corresponding detail row and extract the shipping cost
        const detailRow = row.next('.detail-row'); // Get the next row, assuming it's the detail row

        const extractValue = (label) => detailRow.find(`.list-group-item:contains("${label}") .value-box`).text().replace('$', '').trim();
        $('#shipping-cost').val(extractValue("Shipping Cost"));
        $('#labeling-charges').val(extractValue("Labeling Charges"));
        $('#fulfillment').val(extractValue("Fulfillment fees"));
        $('#amazon-storage-charges').val(extractValue("Storage Cost"));
        $('#origin-purchase').val(extractValue("Origin of Purchase"));

        const unitsSold = detailRow.find('.units-sold').text().replace('Units Sold:', '').trim();
        $('#units-sold').val(unitsSold);

        const matchedTariffPercentage = parseFloat(row.data('tariff-percentage')) || 0;
        $('#tariff-percentage').val(matchedTariffPercentage);

        const lowInventoryFee = parseFloat(extractValue("Low Inventory Fee")) || 0;
        $('#low-inventory-fee').prop('checked', lowInventoryFee > 0);

        const description = detailRow.find('h4:contains("Description:")').next('p').text().trim();
        $('#description').val(description);

        const productLink = detailRow.find('#productLink').attr('href') || '';
        $('#product-link').val(productLink);

        // Retrieve the existing image URL
        const imageUrl = detailRow.find('#image-detail').attr('src');
        // Store the imageUrl for later use
        $('#user-form').data('existing-image', imageUrl);
        if (imageUrl) {
            $('#previewImage').attr('src', imageUrl).show();
        } else {
            $('#previewImage').hide();
        }

        // Clear the file input
        $('#image-upload').val('');

        originalId = row.data('id');

        // Show the "Save Changes" button and hide other buttons
        $('#save-changes-btn').fadeIn();
        $('.submit-btn').fadeOut();

        $('html, body').animate({ scrollTop: 0 }, 'fast');
    });

    // Add event listener for the new "Save Changes" button
    $('#save-changes-btn').on('click', async function (e) {
        e.preventDefault();

        const imageFile = $('#image-upload')[0].files[0];
        let imageUrl = '';
        if (imageFile) {
            imageUrl = await readImage(imageFile);
        } else {
            imageUrl = $('#user-form').data('existing-image') || '';
        }

        const formData = {
            customerName: $('#customerName').val(),
            brandName: $('#brand-name').val(),
            asin: $('#asin').val(),
            briefDescription: $('#brief-description').val(),
            shippingCost: parseFloat($('#shipping-cost').val()) || 0,
            labelingCharges: parseFloat($('#labeling-charges').val()) || 0,
            goodsCost: parseFloat($('#goods-cost').val()) || 0,
            fulfillment: parseFloat($('#fulfillment').val()) || 0,
            sellPrice: parseFloat($('#sell-price').val()) || 0,
            amazonStorageCharges: parseFloat($('#amazon-storage-charges').val()) || 0,
            originPurchase: $('#origin-purchase').val(),
            unitsSold: $('#units-sold').val(),
            tariffPercentage: parseFloat($('#tariff-percentage').val()) || 0,
            lowInventoryFee: $('#low-inventory-fee').is(':checked') ? 0.97 : 0,
            description: $('#description').val().trim(),
            productLink: $('#product-link').val(),
            imageUrl: imageUrl
        };

        // Calculate extra fields (profit, targetBuyPrice, etc)
        const calculatedData = calculateFields({
            ...formData,
            tariffPercentage: formData.tariffPercentage
        });

        // Combine both form + calculated fields
        const updatedFormData = {
            ...formData,
            profit: calculatedData.profit,
            netProceeds: calculatedData.netProceeds,
            amazonFee: calculatedData.amazonFee,
            tariff: calculatedData.tariff,
            return: calculatedData.return,
            actualCost: calculatedData.actualCost,
            rockBottomSellPrice: calculatedData.rockBottomSellPrice,
            targetBuyPrice: calculatedData.targetBuyPrice,
        };

        $('#save-spinner').removeClass('d-none');
        $('#save-btn-text').text('Saving...');
        $('#save-changes-btn').prop('disabled', true);

        ///arcalc
        $.ajax({
            url: `/update-entry/${originalId}`,
            type: 'POST',
            data: updatedFormData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                alert('Entry updated successfully!');

                const row = $(`#data-table tbody tr`).filter(function () {
                    return $(this).data('id') == originalId;
                });

                if (row.length) {
                    row.find('td:eq(1)').text(formData.customerName);
                    row.find('td:eq(2)').text(formData.brandName);
                    row.find('td:eq(3)').text(formData.asin);
                    row.find('td:eq(4)').text(formData.briefDescription);
                    row.find('td:eq(5)').text(formData.goodsCost);
                    row.find('td:eq(6)').text(formData.sellPrice);
                    row.find('td:eq(7)').text(calculatedData.rockBottomSellPrice.toFixed(2));
                    row.find('td:eq(8)').text(calculatedData.profit.toFixed(2));
                    row.find('td:eq(9)').text(calculatedData.targetBuyPrice.toFixed(2));

                    row.css('background-color', '#e0ffe0');
                    setTimeout(() => row.css('background-color', ''), 1500);
                }

                const detailRow = row.next('.detail-row');
                detailRow.find('.description').text(formData.description);
                detailRow.find('.units-sold').text(`Units Sold: ${formData.unitsSold}`);
                detailRow.find('#image-detail').attr('src', imageUrl);
                detailRow.find('#productLink').attr('href', formData.productLink);

                const updateVal = (selector, value) => {
                    detailRow.find(selector).text(parseFloat(value).toFixed(2));
                };

                updateVal('.profit', calculatedData.profit);
                updateVal('.net-proceeds', calculatedData.netProceeds);
                updateVal('.amazon-fees', calculatedData.amazonFee);
                updateVal('.tariff', calculatedData.tariff);
                updateVal('.shipping-cost', formData.shippingCost);
                updateVal('.labeling-charges', formData.labelingCharges);
                updateVal('.return', calculatedData.return);
                updateVal('.fulfillment-fees', formData.fulfillment);
                updateVal('.storage-cost', formData.amazonStorageCharges);
                updateVal('.low-inventory-fee', calculatedData.lowInventoryFee);
                updateVal('.actual-cost', calculatedData.actualCost);
                detailRow.find('.origin-of-purchase').text(formData.originPurchase);
                updateVal('.min-sell-price', calculatedData.rockBottomSellPrice);
                updateVal('.target-buy-price', calculatedData.targetBuyPrice);

                $('html, body').animate({
                    scrollTop: row.offset().top - 100 // Adjust the offset as needed
                }, 'slow');

                $('#user-form')[0].reset();
                $('#previewImage').hide();
                $('#save-changes-btn').fadeOut();
                $('.submit-btn').fadeIn()
                $('#save-spinner').addClass('d-none');
                $('#save-btn-text').text('Save Changes');
                $('#save-changes-btn').prop('disabled', false);
                originalId = null;
            },
            error: function (xhr) {
                console.error("XHR Response:", xhr.responseText);
                alert("Error updating entry.");
                $('#save-spinner').addClass('d-none');
                $('#save-btn-text').text('Save Changes');
                $('#save-changes-btn').prop('disabled', false);
            }
        });
    });

    $('#data-table').on('click', 'tr[data-row]', function () {
        const detail = $(this).next('.detail-row');
        $('.detail-row').not(detail).slideUp();
        detail.slideToggle();
    });

    // Filter the rows based on the selected brand
    $('#brandFilter').on('change', function () {
        const selectedBrand = $(this).val();
        const $rows = $("#data-table tbody tr");

        if (selectedBrand === "all") {
            // Show all rows if "All Brands" is selected
            $rows.show();
            $('#data-table tbody tr.detail-row').hide(); // Hide detail rows
        } else {
            $rows.each(function () {
                const brand = $(this).find('.productBrand').text().trim(); // Correct class name here
                if (brand === selectedBrand) {
                    $(this).show();
                    $(this).next('tr.detail-row').show(); // Show the associated detail row
                } else {
                    $(this).hide();
                    $(this).next('tr.detail-row').hide(); // Hide the associated detail row
                }
            });
        }
    });

    // Filter the rows based on the selected customer
    $('#customerFilter').on('change', function () {
        const selectedCustomer = $(this).val();
        const $rows = $("#data-table tbody tr");

        if (selectedCustomer === "all") {
            $rows.show();
            $('#data-table tbody tr.detail-row').hide(); // Hide detail rows
        } else {
            $rows.each(function () {
                const customer = $(this).find('.customerName').text().trim();
                if (customer === selectedCustomer) {
                    $(this).show();
                    $(this).next('tr.detail-row').show(); // Show the associated detail row
                } else {
                    $(this).hide();
                    $(this).next('tr.detail-row').hide(); // Hide the associated detail row
                }
            });
        }
    });

    function updateFilters(data) {
        if (data.brandName && data.brandName.trim() !== '') {
            if (!$("#brandFilter option[value='" + data.brandName + "']").length) {
                $("#brandFilter").append(`<option value="${data.brandName}">${data.brandName}</option>`);
            }
        }

        if (data.customerName && data.customerName.trim() !== '') {
            if (!$("#customerFilter option[value='" + data.customerName + "']").length) {
                $("#customerFilter").append(`<option value="${data.customerName}">${data.customerName}</option>`);
            }
        }

        // Remove empty options (if any)
        $("#brandFilter option, #customerFilter option").filter(function () {
            return !this.value || $.trim(this.value) === '';
        }).remove();
    }


    //var rockBottomSellPrice = $row.find('td:eq(7)').text();

    $('#downloadExcel').click(function () {
        // Create a new workbook and worksheet
        var wb = XLSX.utils.book_new();
        var ws_data = [];

        // Define the header row
        ws_data.push([
            "Serial Number",
            "Customer Name",
            "Product Brand",
            "ASIN",
            "Brief Description",
            "Goods Cost",
            "Sell Price",
            "Min Sell Price",
            "Profit",
            "Target Buy Price",
            "Units Sold",
            "Description",
            "Net Proceeds",
            "Amazon fees",
            "Tariff",
            "Fulfillment fees",
            "Storage Cost",
            "Origin of Purchase",
            "Shipping Cost",
            "Labeling Charges",
            "Return",
            "Low Inventory Fee",
            "Actual Cost"
        ]);

        // Iterate over each data row in the table
        $('#data-table tbody tr').each(function () {
            var $row = $(this);

            // Skip detail rows
            if ($row.hasClass('detail-row')) return;

            // Get the first row's data
            var serialNumber = $row.find('td:eq(0)').text();
            var customerName = $row.find('td:eq(1)').text();
            var productBrand = $row.find('td:eq(2)').text();
            var asin = $row.find('td:eq(3)').text();
            var briefDescription = $row.find('td:eq(4)').text();
            var goodsCost = $row.find('td:eq(5)').text();
            var sellPrice = $row.find('td:eq(6)').text();
            var rockBottomSellPrice = $row.find('td:eq(7)').text();
            var profit = $row.find('td:eq(8)').text();
            var targetBuyPrice = $row.find('td:eq(9)').text();

            // Manually get the next row for details (skip if it's a detail row itself)
            var $detailRow = $row.next('tr.detail-row');
            var unitsSoldText = $detailRow.find('.units-sold').text().trim();
            var unitsSoldMatch = unitsSoldText.match(/\d+/);
            unitsSold = unitsSoldMatch ? unitsSoldMatch[0] : '';
            var description = $detailRow.find('.description').text().trim() || '';
            var netProceeds = $detailRow.find('.net-proceeds').text().trim() || '';
            var amazonFees = $detailRow.find('.amazon-fees').text().trim() || '';
            var tariff = $detailRow.find('.tariff').text().trim() || '';
            var fulfillmentFees = $detailRow.find('.fulfillment-fees').text().trim() || '';
            var storageCost = $detailRow.find('.storage-cost').text().trim() || '';
            var originOfPurchase = $detailRow.find('.origin-of-purchase').text().trim() || '';
            var shippingCost = $detailRow.find('.shipping-cost').text().trim() || '';
            var labelingCharges = $detailRow.find('.labeling-charges').text().trim() || '';
            var returnVal = $detailRow.find('.return').text().trim() || '';
            var lowInventoryFee = $detailRow.find('.low-inventory-fee').text().trim() || '';
            var actualCost = $detailRow.find('.actual-cost').text().trim() || '';

            // Add the row data into the worksheet data array
            ws_data.push([
                serialNumber,
                customerName,
                productBrand,
                asin,
                briefDescription,
                goodsCost,
                sellPrice,
                rockBottomSellPrice,
                profit,
                targetBuyPrice,
                unitsSold,
                description,
                netProceeds,
                amazonFees,
                tariff,
                fulfillmentFees,
                storageCost,
                originOfPurchase,
                shippingCost,
                labelingCharges,
                returnVal,
                lowInventoryFee,
                actualCost
            ]);
        });

        // Convert the data array to a worksheet
        var ws = XLSX.utils.aoa_to_sheet(ws_data);

        // Append the worksheet to the workbook
        XLSX.utils.book_append_sheet(wb, ws, "Sheet1");

        // Write the workbook to a file
        XLSX.writeFile(wb, "Purchase-Data.xlsx");
    });

    function readImage(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = e => resolve(e.target.result);
            reader.onerror = e => reject(e);
            reader.readAsDataURL(file);
        });
    }

    // Function to read the selected image and display the preview
    function previewSelectedImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#previewImage').attr('src', e.target.result).show();
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Event listener for image input change
    $('#image-upload').change(function () {
        previewSelectedImage(this);
    });


    $.ajax({
        url: '/get-all-entries',
        method: 'GET',
        success: function (dataArray) {
            dataArray.forEach((data, index) => {
                // Assign serialNumber and rowCount (you can customize this logic)
                data.serialNumber = index + 1;
                rowCount = index + 1; // Update global rowCount if needed

                renderRow(data); // Use your existing function
            });
        },
        error: function (err) {
            console.error("Error fetching data:", err);
        }
    });

    function renderRow(data) {
        const description = data.description || '-';

        const formatText = (value) => value !== undefined && value !== null && value !== '' ? value : '-';
        const formatNumber = (value) => (!isNaN(value) && value !== null) ? parseFloat(value).toFixed(2) : '-';

        const imageUrl = data.image && data.image.startsWith('data:image')
            ? data.image
            : '';

        const customerName = formatText(data.customerName);
        const brandName = formatText(data.brandName);
        const amazonFees = formatText(data.amazonFees);
        const fulfillment = formatText(data.fulfillment);
        const storageCost = formatText(data.storageCost);
        const originOfPurchase = data.originOfPurchase || '-';
        const returnValue = data.return || '-';
        const actualCost = formatText(data.actualCost);
        const unitsSold = formatText(data.unitsSold);
        const tariffPercentage = data.tariffPercentage;
        const tariffAmount = data.tariff;

        const rowHtml = `
        <tr data-id="${data.id}"
            data-row="${rowCount}" 
            data-brand="${brandName}" 
            data-customer="${customerName}" 
            data-tariff-percentage="${tariffPercentage || 0}">
            
            <td data-bs-toggle="tooltip" title="SN">${data.serialNumber}</td>
            <td class="customerName" data-bs-toggle="tooltip" title="Customer Name">${formatText(data.customerName)}</td>
            <td class="productBrand" data-bs-toggle="tooltip" title="Brand Name">${formatText(data.brandName)}</td>
            <td data-bs-toggle="tooltip" title="ASIN">${formatText(data.asin)}</td>
            <td data-bs-toggle="tooltip" title="Brief Description">${formatText(data.briefDescription)}</td>
            <td data-bs-toggle="tooltip" title="Buy Price (Goods Cost)">${formatText(data.goodsCost)}</td>
            <td data-bs-toggle="tooltip" title="Sell Price">${formatText(data.sellPrice)}</td>
            <td data-bs-toggle="tooltip" title="Min Sell Price">${formatNumber(data.rockBottomSellPrice)}</td>
            <td data-bs-toggle="tooltip" title="Profit">${formatNumber(data.profit)}</td>
            <td data-bs-toggle="tooltip" title="Target Buy Price">${formatNumber(data.targetBuyPrice)}</td>
            <td>
                <button class="btn btn-success btn-sm edit-btn mb-2">Edit</button>
                <button class="btn btn-danger btn-sm delete-btn mb-2">Delete</button>
            </td>
        </tr>
        <tr class="detail-row" style="display: none;">
            <td colspan="11">
                <div class="row">
                    <div class="col-md-3 d-flex justify-content-center align-items-center position-relative">
                        <img id="image-detail" src="${imageUrl}" class="img-fluid detail-box-img">
                        <div class="units-sold" style="position: absolute; bottom: 10px; left: 10px; background-color: rgba(0, 0, 0, 0.5); color: white; padding: 5px;">
                            Units Sold: ${unitsSold}
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="row">
                            <div class="col-md-12" style="text-align: left;">
                                <h4>Description:</h4>
                                <p class="description">${description}</p>
                                ${data.productLink ? `<a id="productLink" href="${data.productLink}" target="_blank" style="color:black; text-decoration:none; word-break: break-word;">View Product &#8599</a>` : ''}
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-group">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Profit:
                                        <div class="value-box"><span class="dollar-sign">$</span><span class="profit">${formatNumber(data.profit)}</span></div>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Net Proceeds:
                                        <div class="value-box"><span class="dollar-sign">$</span><span class="net-proceeds">${formatNumber(data.netProceeds)}</span></div>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Amazon fees:
                                        <div class="value-box"><span class="dollar-sign">$</span><span class="amazon-fees">${amazonFees}</span></div>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Tariff:
                                        <div class="value-box"><span class="dollar-sign">$</span><span class="tariff">${formatNumber(tariffAmount)}</span></div>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-group">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Shipping Cost:
                                        <div class="value-box"><span class="dollar-sign">$</span><span class="shipping-cost">${formatNumber(data.shippingCost)}</span></div>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Labeling Charges:
                                        <div class="value-box"><span class="dollar-sign">$</span><span class="labeling-charges">${formatNumber(data.labelingCharges)}</span></div>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Return:
                                        <div class="value-box"><span class="dollar-sign">$</span><span class="return-value">${returnValue}</span></div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-group">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Fulfillment fees:
                                        <div class="value-box"><span class="dollar-sign">$</span><span class="fulfillment-fees">${fulfillment}</span></div>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Storage Cost:
                                        <div class="value-box"><span class="dollar-sign">$</span><span class="storage-cost">${storageCost}</span></div>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-group">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Low Inventory Fee:
                                        <div class="value-box"><span class="dollar-sign">$</span><span class="low-inventory-fee">${formatNumber(data.lowInventoryFee)}</span></div>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Actual Cost:
                                        <div class="value-box"><span class="dollar-sign">$</span><span class="actual-cost">${actualCost}</span></div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-group">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Origin of Purchase:
                                        <div class="value-box"><span class="origin-of-purchase">${originOfPurchase}</span></div> 
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Min Sell Price:
                                        <div class="value-box"><span class="dollar-sign">$</span><span class="min-sell-price">${formatNumber(data.rockBottomSellPrice)}</span></div>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-group">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Target Buy Price:
                                        <div class="value-box"><span class="dollar-sign">$</span><span class="target-buy-price">${formatNumber(data.targetBuyPrice)}</span></div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        `;

        const $tbody = $('#data-table tbody');
        const $rows = $tbody.find('tr');
        let inserted = false;

        $rows.each(function () {
            const currentSerial = parseInt($(this).find('td:eq(0)').text(), 10);
            if (data.serialNumber < currentSerial) {
                $(this).before(rowHtml);
                inserted = true;
                return false;
            }
        });

        if (!inserted) {
            $tbody.append(rowHtml);
        }

        rowCount++;
        updateFilters(data);
        resetSerialNumbers();

        $('[data-bs-toggle="tooltip"]').tooltip();
    }

    $(document).on('click', '.delete-btn', function () {
        let id = $(this).closest('tr').data('id');

        if (!id) {
            alert("ID not found for deletion.");
            return;
        }

        if (confirm("Are you sure you want to delete this entry?")) {
            $.ajax({
                url: `/delete-entry/${id}`,
                type: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    alert("Entry deleted successfully!");
                    const row = $(`tr[data-id="${id}"]`);
                    const detailRow = row.next('.detail-row');
                    row.remove();
                    detailRow.remove();
                    resetSerialNumbers();
                },
                error: function (xhr) {
                    alert("Error deleting entry.");
                }
            });
        }
    });

    function resetSerialNumbers() {
        let count = 1;
        $('#data-table tbody tr').each(function () {
            if (!$(this).hasClass('detail-row')) {
                $(this).find('td:eq(0)').text(count++);
            }
        });

        $('#combined-data-table tbody tr').each(function (index) {
            $(this).find('td:eq(0)').text(index + 1);
        });
    }

    $(function () {
        $('[data-bs-toggle="tooltip"]').tooltip();
    });

});

function addCombinedDataRow(serialNumber, formData, data) {
    const row = $('<tr></tr>');

    // Form Data Columns
    row.append(`<td>${serialNumber}</td>`);
    row.append(`<td>${formData.customerName}</td>`);
    row.append(`<td>${formData.brandName}</td>`);
    row.append(`<td>${formData.asin}</td>`);
    row.append(`<td>${formData.briefDescription}</td>`);
    row.append(`<td>${formData.shippingCost}</td>`);
    row.append(`<td>${formData.labelingCharges}</td>`);
    row.append(`<td>${formData.goodsCost}</td>`);
    row.append(`<td>${formData.fulfillment}</td>`);
    row.append(`<td>${formData.sellPrice}</td>`);
    row.append(`<td>${formData.amazonStorageCharges}</td>`);
    row.append(`<td>${formData.tariffPercentage}</td>`);
    row.append(`<td>${formData.originPurchase}</td>`);
    row.append(`<td>${formData.unitsSold}</td>`);
    row.append(`<td>${formData.lowInventoryFee ? 'Yes' : 'No'}</td>`);
    row.append(`<td>${formData.description}</td>`);
    row.append(`<td>${formData.productLink}</td>`);
    row.append(`<td>${formData.imageUrl}</td>`);

    // Calculated Data Columns
    row.append(`<td>${data.rockBottomSellPrice.toFixed(2)}</td>`);
    row.append(`<td>${data.profit.toFixed(2)}</td>`);
    row.append(`<td>${data.targetBuyPrice.toFixed(2)}</td>`);
    row.append(`<td>${data.netProceeds.toFixed(2)}</td>`);
    row.append(`<td>${data.amazonFee.toFixed(2)}</td>`);
    row.append(`<td>${data.tariff.toFixed(2)}</td>`);
    row.append(`<td>${data.return}</td>`);
    row.append(`<td>${data.actualCost.toFixed(2)}</td>`);

    if (originalId) {
        $('#combined-data-table tbody tr').each(function () {
            const existingRow = $(this);
            if (existingRow.find('td:eq(0)').text() === serialNumber.toString()) {
                existingRow.replaceWith(row);
                return false;
            }
        });
    } else {
        $('#combined-data-body').append(row);
    }
}