
<div class="row">
    <div class="col-md-12">
        <div class="page-header">
            <div class="pull-right">
                <a href="{{ URL::to('customerinvoices') }}" class="btn btn-default"><i class="fa fa-mail-reply"></i> {{l('Back to Customer Invoices')}}</a>
            </div>
            
              <h2><a href="{{ URL::to('customerinvoices') }}">{{l('Customer Invoices')}}</a> <span style="color: #cccccc;">/</span> 
                  {{l('Invoice to')}} <span class="lead well well-sm">

                  <a href="{{ URL::to('customers/' . $customer->id . '/edit') }}" title=" {{l('View Customer')}} " target="_blank">{{ $customer->name_fiscal }}</a>

                 <a title=" {{l('View Invoicing Address')}} " href="javascript:void(0);">
                    <button type="button" class="btn btn-xs btn-success" data-toggle="popover" data-placement="right" 
                            title="{{l('Invoicing Address')}}" data-content="
                                  {{$customer->name_fiscal}}<br />
                                  {{l('VAT ID')}}: {{$customer->identification}}<br />
                                  {{ $invoicing_address->address1 }} {{ $invoicing_address->address2 }}<br />
                                  {{ $invoicing_address->postcode }} {{ $invoicing_address->city }}, {{ $invoicing_address->state->name }}<br />
                                  {{ $invoicing_address->country->name }}
                                  <br />
                            ">
                        <i class="fa fa-info-circle"></i>
                    </button>
                 </a></span>
               @if ( $invoice->document_reference )
                 {{ $invoice->document_reference }}
               @else
                 <a href="javascript:void(0);" class="btn btn-primary btn-sm"><strong>{{ \App\CustomerInvoice::getStatusList()[ $invoice->status ] }}</strong></a>
               @endif
             </h2>

        </div>
    </div>
</div> 

   @include('customer_invoices.modal_product_search')

<!-- Invoice Menu -->   
   <ul class="nav nav-tabs" role="tablist">
      <li class="lead" id="tab_header"   ><a href="javascript:void(0);" id="b_header"   >{{l('Header')}}</a></li>
      <li class="lead" id="tab_lines"    ><a href="javascript:void(0);" id="b_lines"    >{{l('Lines')}}</a></li>
      <li class="lead" id="tab_profit"   ><a href="javascript:void(0);" id="b_profit"   >{{l('Profitability')}}</a></li>
      <!-- li class="lead" id="tab_payments" ><a href="javascript:void(0);" id="b_payments" >{{l('Payments')}}</a></li -->
      
      @if ( $customer->einvoice )
        <li class="pull-right" id="tab_tlights" ><a href="javascript:void(0);" id="b_tlights" ><span class="label label-success">{{l('Accepts eInvoice')}}</span></a></li>
      @else
        <li class="pull-right" id="tab_tlights" ><a href="javascript:void(0);" id="b_tlights" ><span class="label label-warning">{{l('Does NOT accept eInvoice')}}</span></a></li>
      @endif
      @if ( !$invoice->id )
      <li class="pull-right" id="tab_tlights" ><a href="javascript:void(0);" id="b_tlights" ><span class="label label-danger">{{l('NOT Saved', [], 'layouts')}}</span></a></li>
      @endif
      <li class="pull-right" id="tab_tlights" ><a href="javascript:void(0);" id="b_tlights" ><span class="label label-info"> {{ \App\CustomerInvoice::getStatusList()[ $invoice->status ] }} </span></a></li>
      @if ( $customer->sales_equalization )
        <li class="pull-right" id="tab_tlights" ><a href="javascript:void(0);" id="b_tlights" ><span class="label label-primary"> {{l('Equalization Tax')}} </span></a></li>
      @endif
   </ul>
 

@if ( isset($invoice->id) && ($invoice->id>0) )
  {!! Form::model($invoice, array('method' => 'PATCH', 'route' => array('customerinvoices.update', $invoice->id), 'id' => 'f_new_order', 'name' => 'f_new_order', 'class' => 'form')) !!}
@else
  {!! Form::model($invoice, array('method' => 'POST', 'route' => array('customerinvoices.store'), 'id' => 'f_new_order', 'name' => 'f_new_order', 'class' => 'form')) !!}
@endif

   <input type="hidden" id="nbrlines" name="nbrlines" value="{{ count($invoice->customerInvoiceLines) }}"/>
   <input type="hidden" id="customer_id" name="customer_id" value="{{$customer->id}}"/>
   <input type="hidden" id="status" name="status" value="{{$invoice->status}}"/>
   <input type="hidden" id="invoicing_address_id" name="invoicing_address_id" value="{{$customer->invoicing_address_id}}"/>

   <input type="hidden" name="prices_entered_with_tax" value="{{$invoice->prices_entered_with_tax}}"/>
   <input type="hidden" name="round_prices_with_tax"   value="{{$invoice->round_prices_with_tax}}"/>

<!-- id="div_header" -->  
   <div class="container-fluid">
      <div class="row" id="div_header" style="padding-top: 20px;">

      @include('customer_invoices._invoice_header')

      </div>

   </div>


<!-- id="div_lines" -->
   <div class="table-responsive" id="div_lines" style="padding-top: 20px;">

   @include('customer_invoices._invoice_body')

   </div>


<!-- div id="div_footer" -->
  <div id="div_footer">

  @include('customer_invoices._invoice_footer')

  </div>

{!! Form::close() !!}


<!-- id="div_profit" -->
   <div class="table-responsive" id="div_profit" style="padding-top: 20px;">

      @include('customer_invoices._tab_profit')

   </div>


<!-- id="div_payments" -->
   <div class="table-responsive" id="div_payments" style="padding-top: 20px;">

      @include('customer_invoices._tab_payments')

   </div>

@include('customer_invoices.modal_save_invoice')
