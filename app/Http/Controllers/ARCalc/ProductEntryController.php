<?php

namespace App\Http\Controllers\ARCalc;

use App\Http\Controllers\Controller;
use App\Models\ProductEntry;
use Illuminate\Http\Request;

class ProductEntryController extends Controller
{
    public function view() {
        $products = ProductEntry::all();
        return view('arcalc.index', compact('products'));
    }
    
    public function store(Request $request)
    {
        $tableData = $request->input('table_data');

        foreach ($tableData as $item) {
            // Calculate the missing fields exactly like frontend
            $tariffPercentage = $item['tariff_percentage'] ?? 0;
            $amazonFee = ($item['sell_price'] ?? 0) * 0.18;
            $return = ($item['goods_cost'] ?? 0) > 2 ? 0.5 : 0.2;
            $tariff = (($item['goods_cost'] ?? 0) * $tariffPercentage) / 100;
            $actualCost = 
                ($item['shipping_cost'] ?? 0) +
                ($item['labeling_charges'] ?? 0) +
                ($item['goods_cost'] ?? 0) +
                ($item['fulfillment'] ?? 0) +
                $amazonFee +
                ($item['amazon_storage_charges'] ?? 0) +
                $return +
                ($item['low_inventory_fee'] ?? 0) +
                $tariff;
            $profit = ($item['sell_price'] ?? 0) - $actualCost;
            $netProceeds = ($item['sell_price'] ?? 0) - ($amazonFee + ($item['fulfillment'] ?? 0));
            $rockBottomSellPrice = ($item['sell_price'] ?? 0) - $profit;
            $targetBuyPrice = 
                ($item['sell_price'] ?? 0) -
                (
                    ($item['shipping_cost'] ?? 0) +
                    ($item['fulfillment'] ?? 0) +
                    $amazonFee +
                    ($item['amazon_storage_charges'] ?? 0) +
                    $return +
                    ($item['low_inventory_fee'] ?? 0) +
                    $tariff +
                    ($item['labeling_charges'] ?? 0)
                ) - 1;

            ProductEntry::create([
                'customer_name' => $item['customer_name'] ?? '',
                'brand_name' => $item['brand_name'] ?? '',
                'asin' => $item['asin'] ?? '',
                'brief_description' => $item['brief_description'] ?? '',
                'shipping_cost' => $item['shipping_cost'] ?? 0,
                'labeling_charges' => $item['labeling_charges'] ?? 0,
                'goods_cost' => $item['goods_cost'] ?? 0,
                'fulfillment' => $item['fulfillment'] ?? 0,
                'sell_price' => $item['sell_price'] ?? 0,
                'amazon_storage_charges' => $item['amazon_storage_charges'] ?? 0,
                'tariff_percentage' => $tariffPercentage,
                'origin_purchase' => $item['origin_purchase'] ?? '',
                'units_sold' => $item['units_sold'] ?? 0,
                'low_inventory_fee' => $item['low_inventory_fee'] ?? 0,
                'description' => $item['description'] ?? '',
                'product_link' => $item['product_link'] ?? '',
                'image_url' => $item['image_url'] ?? '',

                // Insert calculated fields
                'min_sell_price' => $rockBottomSellPrice,
                'profit' => $profit,
                'target_buy_price' => $targetBuyPrice,
                'net_proceeds' => $netProceeds,
                'amazon_fees' => $amazonFee,
                'tariff' => $tariff,
                'return_value' => $return,
                'actual_cost' => $actualCost,
            ]);
        } 

        return response()->json(['message' => 'Success']);
    }

    public function getAllEntries()
    {
        $entries = ProductEntry::all();

        return response()->json($entries->map(function ($entry) {
            return [
                'id' => $entry->id,
                'serialNumber' => $entry->serial_number,
                'customerName' => $entry->customer_name,
                'brandName' => $entry->brand_name,
                'asin' => $entry->asin,
                'briefDescription' => $entry->brief_description,
                'goodsCost' => $entry->goods_cost,
                'sellPrice' => $entry->sell_price,
                'rockBottomSellPrice' => $entry->min_sell_price,
                'targetBuyPrice' => $entry->target_buy_price,
                'netProceeds' => $entry->net_proceeds,
                'amazonFees' => $entry->amazon_fees,
                'tariffPercentage' => $entry->tariff_percentage,
                'tariff' => $entry->tariff,
                'fulfillment' => $entry->fulfillment,
                'storageCost' => $entry->amazon_storage_charges,
                'originOfPurchase' => $entry->origin_purchase,
                'unitsSold' => $entry->units_sold,
                'shippingCost' => $entry->shipping_cost,
                'labelingCharges' => $entry->labeling_charges,
                'return' => $entry->return_value,
                'lowInventoryFee' => $entry->low_inventory_fee,
                'description' => $entry->description,
                'actualCost' => $entry->actual_cost,
                'profit' => $entry->profit,
                'productLink' => $entry->product_link,
                'image' => $entry->image_url,
                'createdAt' => $entry->created_at,
                'updatedAt' => $entry->updated_at
            ];
        }));
    }


    public function destroy($id)
    {
        $product = ProductEntry::find($id);

        if ($product) {
            $product->delete();
            return response()->json(['success' => true, 'message' => 'Entry deleted successfully.']);
        }

        return response()->json(['success' => false, 'message' => 'Entry not found.'], 404);
    }

    public function update(Request $request, $id)
    {
        $product = ProductEntry::findOrFail($id); // use DB primary key

        $validated = $request->validate([
            'customerName' => 'nullable|string|max:255',
            'brandName' => 'nullable|string|max:255',
            'asin' => 'nullable|string|max:255',
            'briefDescription' => 'nullable|string',
            'shippingCost' => 'nullable|numeric',
            'labelingCharges' => 'nullable|numeric',
            'goodsCost' => 'nullable|numeric',
            'fulfillment' => 'nullable|numeric',
            'sellPrice' => 'nullable|numeric',
            'amazonStorageCharges' => 'nullable|numeric',
            'originPurchase' => 'nullable|string|max:255',
            'unitsSold' => 'nullable|numeric',
            'tariffPercentage' => 'nullable|numeric',
            'lowInventoryFee' => 'numeric',
            'description' => 'nullable|string',
            'productLink' => 'nullable|url',
            'imageUrl' => 'nullable|string',
        ]);

        // Calculate exactly like our JS calculateFields(data)
        $tariffPercentage = $validated['tariffPercentage'] ?? 0;
        $amazonFee = ($validated['sellPrice'] ?? 0) * 0.18;
        $return = ($validated['goodsCost'] ?? 0) > 2 ? 0.5 : 0.2;
        $tariff = (($validated['goodsCost'] ?? 0) * $tariffPercentage) / 100;
        $actualCost = 
            ($validated['shippingCost'] ?? 0) +
            ($validated['labelingCharges'] ?? 0) +
            ($validated['goodsCost'] ?? 0) +
            ($validated['fulfillment'] ?? 0) +
            $amazonFee +
            ($validated['amazonStorageCharges'] ?? 0) +
            $return +
            ($validated['lowInventoryFee'] ?? 0) +
            $tariff;
        $profit = ($validated['sellPrice'] ?? 0) - $actualCost;
        $netProceeds = ($validated['sellPrice'] ?? 0) - ($amazonFee + ($validated['fulfillment'] ?? 0));
        $rockBottomSellPrice = ($validated['sellPrice'] ?? 0) - $profit;
        $targetBuyPrice = 
            ($validated['sellPrice'] ?? 0) -
            (
                ($validated['shippingCost'] ?? 0) +
                ($validated['fulfillment'] ?? 0) +
                $amazonFee +
                ($validated['amazonStorageCharges'] ?? 0) +
                $return +
                ($validated['lowInventoryFee'] ?? 0) +
                $tariff +
                ($validated['labelingCharges'] ?? 0)
            ) - 1;

        $product->update([
            'customer_name' => $validated['customerName'],
            'brand_name' => $validated['brandName'],
            'asin' => $validated['asin'],
            'brief_description' => $validated['briefDescription'],
            'shipping_cost' => $validated['shippingCost'],
            'labeling_charges' => $validated['labelingCharges'],
            'goods_cost' => $validated['goodsCost'],
            'fulfillment' => $validated['fulfillment'],
            'sell_price' => $validated['sellPrice'],
            'amazon_storage_charges' => $validated['amazonStorageCharges'],
            'origin_purchase' => $validated['originPurchase'],
            'units_sold' => $validated['unitsSold'],
            'tariff_percentage' => $tariffPercentage,
            'low_inventory_fee' => $validated['lowInventoryFee'],
            'description' => $validated['description'],
            'product_link' => $validated['productLink'],
            'image_url' => $validated['imageUrl'],

            // Save new calculations
            'amazon_fees' => $amazonFee,
            'tariff' => $tariff,
            'min_sell_price' => $rockBottomSellPrice,
            'profit' => $profit,
            'target_buy_price' => $targetBuyPrice,
            'net_proceeds' => $netProceeds,
            'return_value' => $return,
            'actual_cost' => $actualCost,
        ]);

        return response()->json(['message' => 'Updated successfully!']);
    }
}
