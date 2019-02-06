<?php

namespace App;

use Auth;

use App\Traits\BillableStockMovementsTrait;

use \App\CustomerShippingSlipLine;

class CustomerShippingSlip extends Billable
{
    
    use BillableStockMovementsTrait;

    public static $badges = [
            'a_class' => 'alert-info',
            'i_class' => 'fa-truck',
        ];


    /**
     * The fillable properties for this model.
     *
     * @var array
     *
     * https://gist.github.com/JordanDalton/f952b053ef188e8750177bf0260ce166
     */
    protected $document_fillable = [
                            'prices_entered_with_tax', 'round_prices_with_tax',
    ];

    public static $rules = [
                            'document_date' => 'required|date',
//                            'payment_date'  => 'date',
                            'delivery_date' => 'nullable|date|after_or_equal:document_date',
                            'customer_id' => 'exists:customers,id',
                            'shipping_address_id' => 'exists:addresses,id,addressable_id,{customer_id},addressable_type,App\Customer',
                            'sequence_id' => 'exists:sequences,id',
//                            'warehouse_id' => 'exists:warehouses,id',
//                            'carrier_id'   => 'exists:carriers,id',
                            'currency_id' => 'exists:currencies,id',
                            'payment_method_id' => 'nullable|exists:payment_methods,id',
               ];


    public function getDeletableAttribute()
    {
        return $this->status == 'draft';
    }
    

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */


    
    
    public function customerCard()
    {
        $address = $this->customer->address;

        $card = $customer->name .'<br />'.
                $address->address_1 .'<br />'.
                $address->city . ' - ' . $address->state->name.' <a href="javascript:void(0)" class="btn btn-grey btn-xs disabled">'. $$address->phone .'</a>';

        return $card;
    }
    
    public function customerCardFull()
    {
        $address = $this->customer->address;

        $card = ($address->name_commercial ? $address->name_commercial .'<br />' : '').
                ($address->firstname  ? $address->firstname . ' '.$address->lastname .'<br />' : '').
                $address->address1 . ($address->address2 ? ' - ' : '') . $address->address2 .'<br />'.
                $address->city . ' - ' . $address->state->name.' <a href="javascript:void(0)" class="btn btn-grey btn-xs disabled">'. $address->phone .'</a>';

        return $card;
    }
    
    public function customerCardMini()
    {
        $customer = unserialize( $this->customer );

        $card = $customer["city"].' - '.($customer["state_name"] ?? '').' <a href="#" class="btn btn-grey btn-xs disabled">'. $customer["phone"] .'</a>';

        return $card;
    }
    
    public function customerInfo()
    {
        $customer = $this->customer;

        $name = $customer->name_fiscal ?: $customer->name_commercial;

        if ( !$name ) 
            $name = $customer->name;

        return $name;
    }
    

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

    public function confirm()
    {
        if ( ! parent::confirm() ) return false;

        // Dispatch event
        event( new \App\Events\CustomerShippingSlipConfirmed($this) );

        return true;
    }

    public function close()
    {
        if ( ! parent::close() ) return false;

        // Dispatch event
        event( new \App\Events\CustomerShippingSlipClosed($this) );

        return true;
    }

    public function unclose( $status = null )
    {
        if ( ! parent::unclose() ) return false;

        // Dispatch event
        event( new \App\Events\CustomerShippingSlipUnclosed($this) );

        return true;
    }


    public function shouldPerformStockMovements()
    {
        if ( $this->created_via == 'manual' && $this->stock_status == 'pending' ) return true;
/*
        if ($this->stock_status == 'pending') return true;

        if ($this->stock_status == 'completed') return false;

        if ($this->created_via == 'aggregate_shipping_slips') return false;
*/
        return false;
    }


    public function canRevertStockMovements()
    {
        if ($this->created_via == 'manual' && $this->stock_status == 'completed' ) return true;

        return false;
    }



    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */



    public function something()
    {
        return true;
    }



    /*
    |--------------------------------------------------------------------------
    | Data Factory :: Scopes
    |--------------------------------------------------------------------------
    */

}
