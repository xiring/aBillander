<?php 

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Payment;

use App\Traits\DateFormFormatterTrait;

use App\Events\CustomerPaymentReceived;

class CustomerVouchersController extends Controller
{
   
   use DateFormFormatterTrait;


   protected $payment;

   public function __construct(Payment $payment)
   {
        $this->payment = $payment;
   }

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
        // Dates (cuen)
        $this->mergeFormDates( ['date_from', 'date_to'], $request );

		$payments = $this->payment
        			->filter( $request->all() )
					->with('paymentable')
					->with('paymentable.customer')
					->where('payment_type', 'receivable')
					->orderBy('due_date', 'asc');		// ->get();

        $payments = $payments->paginate( \App\Configuration::get('DEF_ITEMS_PERPAGE') );

        $payments->setPath('customervouchers');

        return view('customer_vouchers.index', compact('payments'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		//
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id, Request $request)
	{
		$action = 'edit';
		$back_route = $request->has('back_route') ? urldecode($request->input('back_route')) : '' ;
		
		$payment = $this->payment->with('currency')->findOrFail($id);

		// abi_r($payment, true);

        // Dates (cuen)
        $this->addFormDates( ['due_date'], $payment );
		
		return view('customer_vouchers.edit', compact('payment', 'action', 'back_route'));
	}

	public function pay($id, Request $request)
	{
		$action = 'pay';
		$back_route = $request->has('back_route') ? urldecode($request->input('back_route')) : '' ;
		
		$payment = $this->payment->with('currency')->findOrFail($id);

        // Dates (cuen)
        $this->addFormDates( ['due_date'], $payment );
		
		return view('customer_vouchers.edit', compact('payment', 'action' , 'back_route'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id, Request $request)
	{
        // Dates (cuen)
        $this->mergeFormDates( ['due_date', 'payment_date', 'due_date_next'], $request );


		$back_route = ( $request->has('back_route') AND $request->input('back_route') ) ? urldecode($request->input('back_route')) : 'customervouchers' ;

		$action = $request->input('action', '');

		$payment = $this->payment->findOrFail($id);

		if ( $action == 'edit' ) {
			//

			$rules = Payment::$rules;
			if ( $request->input('amount_next', 0.0) ) $rules['due_date_next'] = 'required';
			$rules['amount'] .= $payment->amount;

			$this->validate($request, $rules);

			$diff = $payment->amount - $request->input('amount', $payment->amount);

			// If amount is not fully paid, a new payment will be created for the difference
			if ( $diff > 0 ) {
				$new_payment = $payment->replicate( ['id', 'due_date', 'payment_date', 'amount'] );

				$due_date = $request->input('due_date_next') ?: \Carbon\Carbon::now();

				$new_payment->name = $payment->name . ' * ';
				$new_payment->status = 'pending';
				$new_payment->due_date = $due_date;
				$new_payment->payment_date = NULL;
				$new_payment->amount = $diff;

				$new_payment->save();

			}

			$payment->name     = $request->input('name',     $payment->name);
			$payment->due_date = $request->input('due_date', $payment->due_date);
			$payment->amount   = $request->input('amount',   $payment->amount);
			$payment->notes    = $request->input('notes',    $payment->notes);

			$payment->save();


			return redirect($back_route)
					->with('success', l('This record has been successfully updated &#58&#58 (:id) ', ['id' => $id], 'layouts') . $request->input('name') . ' / ' . $request->input('due_date'));
		
		} else

		if ( $action == 'pay' ) {

			// if ( !$request->input('payment_date') ) $request->merge( array('payment_date' => abi_date_short( \Carbon\Carbon::now() ) ) );

			$rules = Payment::$rules;
			$rules['payment_date']  = 'required';
			if ( $request->input('amount_next', 0.0) ) $rules['due_date_next'] = 'required';
			$rules['amount'] .= $payment->amount;

			$this->validate($request, $rules);

			$diff = $payment->amount - $request->input('amount', $payment->amount);

			// If amount is not fully paid, a new payment will be created for the difference
			if ( $diff > 0 ) {
				$new_payment = $payment->replicate( ['id', 'due_date', 'payment_date', 'amount'] );

				$due_date = $request->input('due_date_next') ?: \Carbon\Carbon::now();

				$new_payment->name = $payment->name . ' * ';
				$new_payment->status = 'pending';
				$new_payment->due_date = $due_date;
				$new_payment->payment_date = NULL;
				$new_payment->amount = $diff;

				$new_payment->save();

			}

			$payment->name     = $request->input('name',     $payment->name);
//			$payment->due_date = $request->input('due_date', $payment->due_date);
			$payment->payment_date = $request->input('payment_date');
			$payment->amount   = $request->input('amount',   $payment->amount);
			$payment->notes    = $request->input('notes',    $payment->notes);

			$payment->status   = 'paid';
			$payment->save();

			// Update Customer Risk
			event(new CustomerPaymentReceived($payment));


			return redirect($back_route)
					->with('success', l('This record has been successfully updated &#58&#58 (:id) ', ['id' => $id], 'layouts') . $request->input('name') . ' / ' . $request->input('due_date'));
		}


		// No action to process
		return redirect($back_route)
				->with('info', l('No action is taken &#58&#58 (:id) ', ['id' => $id], 'layouts') . $request->input('name') . ' / ' . $request->input('due_date'));
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $payment = $this->payment->findOrFail($id);

        if ($payment->amount>0.0)
	        return redirect()->back()
	                ->with('error', l('This record cannot be deleted because its Quantity or Value &#58&#58 (:id) ', ['id' => $id], 'layouts'));

        $payment->delete();

        return redirect()->back()
                ->with('success', l('This record has been successfully deleted &#58&#58 (:id) ', ['id' => $id], 'layouts'));
	}

}
