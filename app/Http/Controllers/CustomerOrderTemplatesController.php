<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\CustomerOrderTemplate;
use App\CustomerOrderTemplateLine;

use App\Customer;
use App\Template;
use App\CustomerOrder;
use App\ShippingMethod;

class CustomerOrderTemplatesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $customerordertemplates = CustomerOrderTemplate::with('customer')->orderBy('id', 'asc')->get();

        return view('customer_order_templates.index', compact('customerordertemplates'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('customer_order_templates.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, CustomerOrderTemplate::$rules);

        $customerordertemplate = CustomerOrderTemplate::create($request->all());

        return redirect('customerordertemplates')
                ->with('info', l('This record has been successfully created &#58&#58 (:id) ', ['id' => $customerordertemplate->id], 'layouts') . $request->input('name'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\CustomerOrderTemplate  $customerordertemplate
     * @return \Illuminate\Http\Response
     */
    public function show(CustomerOrderTemplate $customerordertemplate)
    {
        return $this->edit($customerordertemplate);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\CustomerOrderTemplate  $customerordertemplate
     * @return \Illuminate\Http\Response
     */
    public function edit(CustomerOrderTemplate $customerordertemplate)
    {
        return view('customer_order_templates.edit', compact('customerordertemplate'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\CustomerOrderTemplate  $customerordertemplate
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CustomerOrderTemplate $customerordertemplate)
    {
        $this->validate($request, CustomerOrderTemplate::$rules);

        $customerordertemplate->update($request->all());

        return redirect('customerordertemplates')
                ->with('success', l('This record has been successfully updated &#58&#58 (:id) ', ['id' => $customerordertemplate->id], 'layouts') . $request->input('name'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\CustomerOrderTemplate  $customerordertemplate
     * @return \Illuminate\Http\Response
     */
    public function destroy(CustomerOrderTemplate $customerordertemplate)
    {
        $id = $customerordertemplate->id;

        $customerordertemplate->delete();

        return redirect('customerordertemplates')
                ->with('success', l('This record has been successfully deleted &#58&#58 (:id) ', ['id' => $id], 'layouts'));
    }


    /**
     * AJAX Stuff.
     *
     * 
     */
    public function sortLines(Request $request)
    {
        $positions = $request->input('positions', []);

        \DB::transaction(function () use ($positions) {
            foreach ($positions as $position) {
                CustomerOrderTemplateLine::where('id', '=', $position[0])->update(['line_sort_order' => $position[1]]);
            }
        });

        return response()->json($positions);
    }




    public function createCustomerOrder(CustomerOrderTemplate $customerordertemplate, Request $request)
    {
        $customerordertemplate->load(['customerordertemplatelines', 'customer', 'customer.currency', 'template']);
        $customer = $customerordertemplate->customer;
        $cotlines = $customerordertemplate->customerordertemplatelines;

        // Create Customer Order Header
        $shipping_method_id = $customer->getShippingMethodId();

        $shipping_method = ShippingMethod::find($shipping_method_id);
        $carrier_id = $shipping_method ? $shipping_method->carrier_id : null;

        // Common data
        $data = [
//            'company_id' => $this->company_id,
            'customer_id' => $customer->id,
//            'user_id' => $this->,

            'sequence_id' => Configuration::getInt('DEF_CUSTOMER_ORDER_SEQUENCE'),

            'created_via' => 'customer_order_template',

            'document_date' => \Carbon\Carbon::now(),

            'currency_conversion_rate' => $customer->currency->conversion_rate,
//            'down_payment' => $this->down_payment,

            'document_discount_percent' => $customerordertemplate->document_discount_percent > 0.0 ? 
                                                $customerordertemplate->document_discount_percent  : 
                                                $customer->discount_percent,
            
            'document_ppd_percent'      => $customerordertemplate->document_ppd_percent > 0.0      ? 
                                                $customerordertemplate->document_ppd_percent       : 
                                                $customer->discount_ppd_percent,

            'total_currency_tax_incl' => 0.0,
            'total_currency_tax_excl' => 0.0,
//            'total_currency_paid' => $this->total_currency_paid,

            'total_tax_incl' => 0.0,
            'total_tax_excl' => 0.0,

//            'commission_amount' => $this->commission_amount,

            // Skip notes

            'status' => 'draft',
            'onhold' => 0,
            'locked' => 0,

            'invoicing_address_id' => $customer->invoicing_address_id,
            'shipping_address_id' => $customerordertemplate->shipping_address_id,   // To do: check if exist in customer address book
            'warehouse_id' => Configuration::getInt('DEF_WAREHOUSE'),
            'shipping_method_id' => $shipping_method_id,
            'carrier_id' => $carrier_id,
            'sales_rep_id' => $customer->sales_rep_id,
            'currency_id' => $customer->currency->id,
            'payment_method_id' => $customer->getPaymentMethodId(),
            'template_id' => $customerordertemplate->template_id > 0 ? : Configuration::getInt('DEF_CUSTOMER_ORDER_TEMPLATE'),
        ];

        // Model specific data
        $extradata = [
//            'payment_status' => 'pending',
//            'stock_status' => 'completed',
        ];

        // Let's get dirty
        $order = CustomerOrder::create( $data + $extradata );



        // Create Customer Order Lines
        // Cowboys, lett's herd the cattle!
        foreach ($cotlines as $cotline) {
            # code...

            $line[] = $order->addProductLine( $cotline->product_id, null, $cotline->quantity, [] );

            // ^- Document totals are calculated when a line is added   
        }

        
        // Did I forget something? Maybe YES
        // Shipping Cost Stuff!
        $method = $order->shippingmethod ?: $order->shippingaddress->getShippingMethod();
        $free_shipping = (Configuration::getNumber('ABCC_FREE_SHIPPING_PRICE') >= 0.0) ? Configuration::getNumber('ABCC_FREE_SHIPPING_PRICE') : null;

        list($shipping_label, $cost, $tax) = array_values(ShippingMethod::costPriceCalculator( $method, $order, $free_shipping ));


        $params = [
            'line_type' => 'shipping',
//            'prices_entered_with_tax' => $pricetaxPolicy,
            'name' => $shipping_label.' :: '.$method->name,
            'cost_price' => $cost,
            'unit_price' => $cost,
            'discount_percent' => 0.0,
            'unit_customer_price' => $cost,
            'unit_customer_final_price' => $cost,
            'tax_id' => $tax->id,
            'sales_equalization' => $order->customer->sales_equalization,

//            'line_sort_order' => $request->input('line_sort_order'),
//            'notes' => $request->input('notes', ''),
        ];

        $line[] = $order->addServiceLine( null, null, 1.0, $params );



        // Good boy, so far
        if ( Configuration::isFalse('CUSTOMER_ORDERS_NEED_VALIDATION') )
            $order->confirm();
        
        $customerordertemplate->last_used_at =  $order->document_date;
        $customerordertemplate->save();

        return redirect('customerordertemplates')
                ->with('info', l('This record has been successfully created &#58&#58 (:id) ', ['id' => $order->id], 'layouts'));
    }
}