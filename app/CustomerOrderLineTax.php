<?php

namespace App;

class CustomerOrderLineTax extends BillableLineTax
{


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Needed (MayBe) by /WooConnect/src/WooOrderImporter.php
    public function customerorderline()
    {
       return $this->belongsTo('App\CustomerOrderLine', 'customer_order_line_id');
    }
    
}
