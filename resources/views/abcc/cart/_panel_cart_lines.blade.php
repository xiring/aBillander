<div id="div_cart_lines">
          <div class="table-responsive">

@if ($cart->cartproductlines->count())
    <table id="cart_lines" class="table table-hover">
        <thead>
            <tr>
              <th>{{ l('Reference') }}</th>
              <th colspan="2">{{ l('Product Name') }}</th>
              <th>
@if( \App\Configuration::get( 'ABCC_STOCK_SHOW' ) != 'none')
                  {{ l('Stock') }}
@endif
               </th>
               <th class="text-center button-pad">{{ l('Quantity') }}
                   <a href="javascript:void(0);" data-toggle="popover" data-placement="top" data-container="body" 
                          data-content="{{ l('Change Quantity and press [Enter] or click button on the right.') }}">
                      <i class="fa fa-question-circle abi-help"></i>
                   </a></th>
               <th> </th>
               <th class="text-center">
                  <span class="button-pad">{{ l('Customer Price') }}
                   <a href="javascript:void(0);" data-toggle="popover" data-placement="top" data-container="body" 
                          data-content="{{ l('Prices are exclusive of Tax', 'abcc/catalogue') }}
@if( \App\Configuration::isTrue('ENABLE_ECOTAXES') )
    . 
    {!! l('Prices are inclusive of Ecotax', 'abcc/catalogue') !!}
@endif
                  ">
                      <i class="fa fa-question-circle abi-help"></i>
                   </a></span></th>
               <th class="text-right">{{ l('Total') }}</th>
              <th class="text-right"> </th>
            </tr>
        </thead>
        <tbody class="sortable ui-sortable">
  @foreach ($cart->cartproductlines as $line)
    <tr>
      <!-- td>{{ $line->id }}</td -->
      <td title="{{ $line->product->id }}">@if ($line->product->product_type == 'combinable') <span class="label label-info">{{ l('Combinations') }}</span>
                @else {{ $line->product->reference }}
                @endif</td>

      <td>
@php
  $img = $line->product->getFeaturedImage();
@endphp
@if ($img)
              <a class="view-image" data-html="false" data-toggle="modal" 
                     href="{{ URL::to( \App\Image::pathProducts() . $img->getImageFolder() . $img->id . '-large_default' . '.' . $img->extension ) }}"
                     data-content="{{ nl2p($line->product->description_short) }} <br /> {{ nl2p($line->product->description) }} " 
                     data-title="{{ l('Product Images') }} :: {{ $line->product->name }} " 
                     data-caption="({{$img->id}}) {{ $img->caption }} " 
                     onClick="return false;" title="{{l('View Image')}}">

                      <img src="{{ URL::to( \App\Image::pathProducts() . $img->getImageFolder() . $img->id . '-mini_default' . '.' . $img->extension ) . '?'. 'time='. time() }}" style="border: 1px solid #dddddd;">
              </a>
@endif
      </td>

      <td>{{ $line->product->name }}
          @if( \App\Configuration::isTrue('ENABLE_ECOTAXES') && $line->product->ecotax )
              <br />
              {{ l('Ecotax: ', 'abcc/catalogue') }} {{ $line->product->ecotax->name }} ({{ abi_money( $line->product->getEcotax() ) }})
          @endif
      </td>

      <td style="white-space:nowrap">
@if( \App\Configuration::get( 'ABCC_STOCK_SHOW' ) != 'none')
            @if( \App\Configuration::get( 'ABCC_STOCK_SHOW' ) == 'amount')
              <div class="progress progress-striped" style="margin-bottom: auto !important">
                <div class="progress-bar progress-bar-{{ $line->product->stock_badge }}" title="{{ l('stock.badge.'.$line->product->stock_badge, 'abcc/layouts') }}" style="width: 100%">
                        <span class="badge" style="color: #333333; background-color: #ffffff;">{{ $line->product->as_quantity('quantity_onhand') }}</span>
                </div>
              </div>
            @else
              <div class="progress progress-striped" style="width: 34px; margin-bottom: auto !important">
                <div class="progress-bar progress-bar-{{ $line->product->stock_badge }}" title="{{ l('stock.badge.'.$line->product->stock_badge, 'abcc/layouts') }}" style="width: 100%">
                </div>
              </div>
            @endif
@endif
      </td>

        <td style="width:1px; white-space: nowrap;xvertical-align: top;">

            <div xclass="form-group">
              <div class="input-group" style="width: 81px;">

                <input class="input-line-quantity form-control input-sm col-xs-2" data-id="{{$line->id}}" data-quantity="{{ (int) $line->quantity }}" type="text" size="5" maxlength="5" style="xwidth: auto;" value="{{ (int) $line->quantity / $line->pmu_conversion_rate }}" onclick="this.select()" id="{{$line->id}}_quantity">

                <span class="input-group-btn">
                  <button class="update-line-quantity btn btn-sm btn-lightblue" data-id="{{$line->id}}" data-quantity="{{ (int) $line->quantity }}" type="button" title="{{l('Apply', [], 'layouts')}}" xonclick="add_discount_to_order($('#order_id').val());">
                      <span class="fa fa-calculator"></span>
                  </button>
                </span>

              </div>
            </div>

      </td>
      <td class="button-pad">

@if ( $line->product->getPackagesWithPriceRules( \Auth::user()->customer ) )   {{-- Has extra measure units! --}}
                {{-- {!! Form::select('measure_unit_id', $line->product->measureunits->pluck('name', 'id')->toArray(), $line->product->measure_unit_id, array('class' => 'form-control form-control-sm')) !!} --}}

                {{-- $line->product->hasPackagePriceRules( \Auth::user()->customer ) --}}


                   <span class="pull-right" style="position: absolute; padding-right: 7px" data-toggle="popover" data-placement="top" data-container="body" 
                          data-content="{{ l('Prices per Units!', 'abcc/catalogue') }}">
                      <i class="fa fa-info-circle abi-help" style="color: #ff0084;"></i>
                   </span>


<ul class="nav nav-pills pull-left" style="border: 1px solid #dddddd;border-radius: 3px;">
  <li class="dropdown" xstyle="float: left;position: relative;

display: inline-block;">
    <a class="dropdown-toggle" data-toggle="dropdown" href="#" aria-expanded="false" style="padding: 5px 7px;">
      {{ $line->package_measureunit->sign }} <span class="caret"></span>
    </a>
    <ul class="dropdown-menu dropdown-menu-right">

      <li><a href="#" class="update-line-measureunit" data-id="{{$line->id}}" data-measureunit="{{ $line->product->measureunit->id }}">{{ $line->product->measureunit->name }}</a></li>

@foreach( $line->product->getPackagesWithPriceRules( \Auth::user()->customer ) as $unit )

      <li><a href="#" class="update-line-measureunit" data-id="{{$line->id}}" data-measureunit="{{ $unit->id }}">{{ $unit->name }} - {{ round($unit->conversion_rate, 0) }} {{ $line->product->measureunit->name }}</a></li>

@endforeach

    </ul>
  </li>
</ul>


@else

<!-- div class="btn-group">
  <a href="#" class="btn btn-warning">Default</a>
  <a href="#" data-toggle="dropdown" class="btn btn-warning dropdown-toggle"><span class="caret"></span></a>
  <ul class="dropdown-menu" style="background-color: white">
    <li><a href="#">Action</a></li>
    <li><a href="#">Another action</a></li>
    <li><a href="#">Something else here</a></li>
    <li class="divider"></li>
    <li><a href="#">Separated link</a></li>
  </ul>
</div -->
{{--


                   <span class="pull-right" style="position: absolute;" data-toggle="popover" data-placement="top" data-container="body" 
                          data-content="{{ l('Prices per Units!', 'abcc/catalogue') }}">
                      <i class="fa fa-question-circle abi-help"></i>
                   </span>


<ul class="nav nav-pills pull-left" style="border: 1px solid #dddddd;border-radius: 3px;">
  <li class="dropdown" xstyle="float: left;position: relative;

display: inline-block;">
    <a class="dropdown-toggle" data-toggle="dropdown" href="#" aria-expanded="false" style="padding: 5px 7px;">
      {{ $line->product->measureunit->sign }} <span class="caret"></span>
    </a>
    <ul class="dropdown-menu">
      <li><a href="#">Action</a></li>
      <li><a href="#">Another action</a></li>
      <li><a href="#">Something else here</a></li>
      <li class="divider"></li>
      <li><a href="#">Separated link</a></li>
    </ul>
  </li>
</ul>
--}}
<!-- p>Por unidad: 12,00€</p -->

@endif

      </td>
{{--
  https://stackoverflow.com/questions/25732320/bootstrap-grid-inside-a-td
  http://jsfiddle.net/9jeyyfdL/
--}}
      <td class="text-right" style="white-space:nowrap">
        <div style="display:inline-block; float: none; padding-left: 7px">
          {{ $line->as_priceable( $line->unit_customer_final_price * $line->pmu_conversion_rate ) }}

          <!-- p class="text-info">{{ $line->as_priceable($line->unit_customer_price + $line->product->getEcotax()) }}</p -->

@if ( $line->product->hasQuantityPriceRules( \Auth::user()->customer ) )

          <a class="btn btn-sm btn-custom show-pricerules" href="#" data-target='#myModalShowPriceRules' data-id="{{ $line->product->id }}" data-toggle="modal" onClick="return false;" title="{{ l('Show Special Prices', 'abcc/catalogue') }} {{-- $line->product->hasQuantityPriceRules( \Auth::user()->customer ) --}}"><i class="fa fa-thumbs-o-up"></i></a>

@endif
        </div>
      </td>

      <td class="text-right">{{ $line->as_priceable($line->quantity * $line->unit_customer_final_price) }}</td>

                <td class="text-right button-pad">
                    <!-- a class="btn btn-sm btn-info" title="XXXXXS" onClick="loadcustomerorderlines();"><i class="fa fa-pencil"></i></a -->
                    
                    <!-- a class="btn btn-sm btn-warning edit-order-line" data-id="18" title="Modificar" onclick="return false;"><i class="fa fa-pencil"></i></a -->
                    
                    <a class="btn btn-sm btn-danger delete-cart-line" data-id="{{$line->id}}" title="{{l('Delete', [], 'layouts')}}" data-content="{{l('You are going to delete a record. Are you sure?', [], 'layouts')}}" data-title="{{ '('.$line->id.') ['.$line->reference.'] '.$line->name }}" onclick="return false;"><i class="fa fa-trash-o"></i></a>

                </td>
            </tr>
  @endforeach


@include('abcc.cart._panel_cart_total')


        </tbody>
    </table>

@else
<div class="alert alert-warning alert-block">
    <i class="fa fa-warning"></i>
    {{l('No records found', [], 'layouts')}}
</div>
@endif


<input id="cart_nbr_items" name="cart_nbr_items" type="hidden" value="{{ $cart->nbrItems() }}">

       </div>

</div><!-- div id="div_cart_lines" -->
