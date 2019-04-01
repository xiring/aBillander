<?php

namespace App\Http\Controllers\SalesRepCenter;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use Illuminate\Support\Facades\Auth;
use App\SalesRepUser;
use App\SalesRep;

use App\Configuration;
use App\Currency;

use App\Customer;
use App\CustomerShippingSlip;
use App\CustomerShippingSlipLine;

use Mail;

class AbsrcCustomerShippingSlipsController extends Controller
{
    protected $customer, $customerShippingSlip, $customerShippingSlipLine;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Customer $customer, CustomerShippingSlip $customerShippingSlip, CustomerShippingSlipLine $customerShippingSlipLine)
    {
        // $this->middleware('auth:customer')->except('pdf');

        $this->customer                 = $customer;
        $this->customerShippingSlip     = $customerShippingSlip;
        $this->customerShippingSlipLine = $customerShippingSlipLine;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $documents = $this->customerShippingSlip    //CustomerInvoice::ofCustomer()      // Of Logged in Customer (see scope on Customer Model)
                            ->ofSalesRep()
                            ->where( function ($q) {
                                    $q->where('status', 'closed');
                                    $q->orWhere('status', 'confirmed');
                                } )
                            ->withCount('lines')
//                            ->with('customer')
                            ->with('currency')
//                            ->with('paymentmethod')
                            ->orderBy('document_date', 'desc')
                            ->orderBy('id', 'desc');

        $documents = $documents->paginate( Configuration::get('ABCC_ITEMS_PERPAGE') );

        $documents->setPath('shippingslips');

        return view('absrc.shipping_slips.index', compact('documents'));
    }

    public function show($cinvoiceKey)
    {
/*        // Temporary
        return $this->pdf($cinvoiceKey, $request);

        $invoice = $this->invoiceRepository->findByUrlKey($cinvoiceKey);

        app()->setLocale($invoice->client->language);

        event(new InvoiceViewed($invoice));

        return view('client_center.invoices.public')
            ->with('invoice', $invoice)
            ->with('statuses', InvoiceStatuses::statuses())
            ->with('urlKey', $cinvoiceKey)
            ->with('merchants', MerchantProperties::setProperties(config('fi.merchant')))
            ->with('attachments', $invoice->clientAttachments);
*/

        $cinvoice = $this->customerInvoice
                            ->where('secure_key', $cinvoiceKey)
                            ->with('customer')
                            ->with('invoicingAddress')
                            ->with('customerInvoiceLines')
                            ->with('currency')
                            ->firstOrFail();

        // $company = \App\Context::getContext()->company;
        $company = \App\Company::with('currency')->findOrFail( intval(Configuration::get('DEF_COMPANY')) );

        return view('absrc.invoices.show', compact('cinvoice', 'company'));
    }

    public function showPdf($id, Request $request)
    {

        $customer      = Auth::user()->customer;

        $document = $this->customerShippingSlip->where('id', $id)->where('customer_id', $customer->id)->first();

        if (!$document) 
            return redirect()->route('absrc.shippingslips.index')
                    ->with('error', l('The record with id=:id does not exist', ['id' => $id], 'layouts').$customer->id);
        

        $company = \App\Context::getContext()->company;

        // Get Template
        $t = $document->template ?? 
             \App\Template::find( Configuration::getInt('DEF_CUSTOMER_SHIPPING_SLIP_TEMPLATE') );

        if ( !$t )
            return redirect()->route('absrc.shippingslips.index', $id)
                ->with('error', l('Unable to load PDF Document &#58&#58 (:id) ', ['id' => $document->id], 'layouts'));

        $template = $t->getPath( 'CustomerShippingSlip' );


//        $template = 'customer_invoices.templates.' . $cinvoice->template->file_name;  // . '_dist';
        $paper = $t->paper;    // A4, letter
        $orientation = $t->orientation;    // 'portrait' or 'landscape'.

        // PDF stuff
        try{
                $pdf        = \PDF::loadView( $template, compact('document', 'company') )
                            ->setPaper( $paper, $orientation );
        }
        catch(\Exception $e){

                return redirect()->route('absrc.invoices.index')
                    ->with('error', l('Unable to load PDF Document &#58&#58 (:id) ', ['id' => $document->id], 'layouts').$e->getMessage());
        }

        // PDF stuff ENDS

        $pdfName    = 'Shippingslip_' . $document->secure_key . '_' . $document->document_date;

        if ($request->has('screen')) return view($template, compact('document', 'company'));
        
        return  $pdf->stream();
        return  $pdf->download( $pdfName . '.pdf');
    }

    public function html($cinvoiceKey)
    {
        $invoice = $this->invoiceRepository->findByUrlKey($cinvoiceKey);

        return $invoice->html;
    }
}
