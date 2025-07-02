<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductEntry extends Model
{
    use HasFactory;
    
    protected $table = 'arcalc_details';

    protected $fillable = [
        'serial_number',
        'customer_name',
        'brand_name',
        'asin',
        'brief_description',
        'shipping_cost',
        'goods_cost',
        'fulfillment',
        'amazon_storage_charges',
        'origin_purchase',
        'units_sold',
        'low_inventory_fee',
        'labeling_charges',
        'sell_price',
        'description',
        'product_link',
        'image_url',
        'min_sell_price',
        'profit',
        'target_buy_price',
        'net_proceeds',
        'amazon_fees',
        'tariff',
        'return_value',
        'actual_cost',
        'image_path', // for uploaded images
        'fulfillment_fees', // used in the update function
        'tariff_percentage', // used in update
    ];
}
