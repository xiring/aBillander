<?php 

namespace Queridiam\FSxConnector\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

// use \aBillander\WooConnect\WooConnector;
// use \aBillander\WooConnect\WooOrderImporter;
use Queridiam\FSxConnector\FSxTools;

use \App\Configuration;

class FSxConfigurationKeysController extends Controller {

   public $conf_keys = [];

   public function __construct()
   {

        $this->conf_keys = [

                1 => [

//                        'FSX_IMPERSONATE_TIMEOUT' => '0',,
//						'FSX_TIME_OFFSET'               => '3',
//						'FSX_MAX_ROUNDCYCLES'           => '50',

                        'FSOL_WEB_CUSTOMER_CODE_BASE', //      => 50000,
//						'FSOL_WEB_GUEST_CODE_BASE'        => 60000,		// 'WOOC_ENABLE_GUEST_CHECKOUT'
                        'FSOL_ABI_CUSTOMER_CODE_BASE', //      => 80000,


						'FSOL_CBDCFG', //  => '/public_html/laextranatural.com/wp-content/plugins/FSx-Connector/fsweb/BBDD/',
						'FSOL_CIACFG', //  => 'imagenes/',
						'FSOL_CPVCFG', //  => 'npedidos/',
						'FSOL_CCLCFG', //  => 'nclientes/',
						'FSOL_CBRCFG', //  => 'factusolweb.sql',

                        'FSX_USE_LOCAL_DATABASE', //     => '1',


//    'WOO_ORDER_TO_DOWNLOAD_STATUS_ID'  => '-1',
//    'WOO_ORDER_DOWNLOADED_STATUS_ID'   => '-1',
                        'FSX_FORCE_CUSTOMERS_DOWNLOAD', //     => '0',
                        'FSX_DLOAD_CUSTOMER_SHIPPING_ADDRESS', // => '0',
                        'FSX_SHIPPING_ADDRESS_AS_LINE', // => '0',
//    'WOO_USE_WEB_DESC'                 => '0',
                          'FSX_ORDER_LINES_REFERENCE_CHECK', //  => '0',

                    ],

                2 => [

            'FSOL_IMPUESTO_DIRECTO_TIPO_1', //  => '',
            'FSOL_IMPUESTO_DIRECTO_TIPO_2', //  => '',
            'FSOL_IMPUESTO_DIRECTO_TIPO_3', //  => '',
            'FSOL_IMPUESTO_DIRECTO_TIPO_4', //  => '',

                    ],

                3 => [
/*
            'FSOL_TCACFG', //  => '',  // Tarifa
            'FSOL_AUSCFG', //  => '',  // Almacén
            'FSOL_SPCCFG', //  => '',  // Serie de Pedidos

            'FSOL_PIV1CFG', //  => '',
            'FSOL_PIV2CFG', //  => '',
            'FSOL_PIV3CFG', //  => '',

            'FSOL_PRE1CFG', //  => '',
            'FSOL_PRE2CFG', //  => '',
            'FSOL_PRE2CFG', //  => '',
*/
                    ],

                4 => [


                    ],

                5 => [

            'FSOL_TCACFG', //  => '',  // Tarifa
            'FSOL_AUSCFG', //  => '',  // Almacén
            'FSOL_SPCCFG', //  => '',  // Serie de Pedidos

            'FSOL_PIV1CFG', //  => '',
            'FSOL_PIV2CFG', //  => '',
            'FSOL_PIV3CFG', //  => '',

            'FSOL_PRE1CFG', //  => '',
            'FSOL_PRE2CFG', //  => '',
            'FSOL_PRE2CFG', //  => '',


                    ],

                6 => [


                    ],

                7 => [

//    'FSOL_LOAD_SECCIONES'           => '1',
//    'FSOL_LOAD_FAMILIAS'            => '1',
    'FSX_LOAD_FAMILIAS_TO_ROOT',
    'FSX_LOAD_ARTICULOS',
//    'FSX_ADD_TO_ALL_CATS'           => '0',  // Add Product to Section and Family
//    'FSOL_ARTICULOS_DESC_WEB'       => '2',  // 1=> la Descripción Corta, 2=> la Descripción Larga, 3=> Ambas
    'FSX_LOAD_ARTICULOS_ACTIVE',
//    'FSOL_LOAD_ARTICULOS_STATUS'     => 'draft',   // publish, pending, draft
 //   'FSOL_LOAD_ARTICULOS_VISIBILITY' => 'visible', // visible, catalog, search, hidden
    'FSX_LOAD_ARTICULOS_PRIZE_ALL',
    'FSX_LOAD_ARTICULOS_STOCK_ALL',
//    'FSOL_ARTICULOS_CAT_NOT_FOUND'  => '0',
//    'FSOL_PROD_WOO_ONLY_DEACTIVATE' => '0',
//    'FSX_RESUME_ON_TIMEOUT'         => '0',

            'FSX_FSOL_AUSCFG_PEER', //  => '',  // Almacén correspondiente en aBillander para cargar el stock

                    ],

        ];

   }

	/**
	 * Display a listing of the resource.
	 * GET /something
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
        $conf_keys = array();
        $tab_index =   $request->has('tab_index')
                        ? intval($request->input('tab_index'))
                        : 1;

        // Check tab_index
        $tab_view = 'fsx_connector::fsx_configuration_keys.'.'key_group_'.intval($tab_index);
        if (!\View::exists($tab_view)) 
            return \Redirect::to('404');

        $key_group = [];

        foreach ($this->conf_keys[$tab_index] as $key)
            $key_group[$key]= Configuration::get($key);

        $errors = $this->checkFolderErrors();
        session()->put('error', $errors);       //  Session::set()  use Illuminate\Support\Facades\Session;

// abi_r($key_group, true);
        return view( $tab_view, compact('tab_index', 'key_group') );

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
    public function store(Request $request)
    {
        // die($request->input('tab_index'));

        $tab_index =   $request->has('tab_index')
                        ? intval($request->input('tab_index'))
                        : -1;
        
        $key_group = isset( $this->conf_keys[$tab_index] ) ?
                            $this->conf_keys[$tab_index]   :
                            null ;

        // Check tab_index
        if (!$key_group) 
            return redirect('404');

        foreach ($key_group as $key) 
        {
            if ($request->has($key)) {

                // abi_r("-$key-");
                // abi_r($request->input($key));

                // Prevent NULL values
                $value = is_null( $request->input($key) ) ? '' : $request->input($key);

                \App\Configuration::updateValue($key, $value);
            }
        }


        return redirect('fsx/fsxconfigurationkeys?tab_index='.$tab_index)
                ->with('error', $this->checkFolderErrors() )
                ->with('success', l('This record has been successfully updated &#58&#58 (:id) ', ['id' => $tab_index], 'layouts') );
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
    public function edit($id)
    {
        // 
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        // 
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        // 
    }


/* ********************************************************************************************* */    


    /**
     * Show the form for editing the specified resource.
     * GET 
     *
     * @param  
     * @return Response
     */
    public function configurationsEdit()
    {
        $tab_index = 5;

        $key_group = [];

        foreach ($this->conf_keys[$tab_index] as $key)
            $key_group[$key]= Configuration::get($key);
        
        $fsxconfs = [];

        $fsxconfs[] = [ 'id' => 'FSOL_TCACFG', 'value' => Configuration::get('FSOL_TCACFG') ];
        $fsxconfs[] = [ 'id' => 'FSOL_AUSCFG', 'value' => Configuration::get('FSOL_AUSCFG') ];
        $fsxconfs[] = [ 'id' => 'FSOL_SPCCFG', 'value' => Configuration::get('FSOL_SPCCFG') ];
        
        $fsxconfs[] = [ 'id' => 'FSOL_PIV1CFG', 'value' => Configuration::get('FSOL_PIV1CFG') ];
        $fsxconfs[] = [ 'id' => 'FSOL_PIV2CFG', 'value' => Configuration::get('FSOL_PIV2CFG') ];
        $fsxconfs[] = [ 'id' => 'FSOL_PIV3CFG', 'value' => Configuration::get('FSOL_PIV3CFG') ];
        
        $fsxconfs[] = [ 'id' => 'FSOL_PRE1CFG', 'value' => Configuration::get('FSOL_PRE1CFG') ];
        $fsxconfs[] = [ 'id' => 'FSOL_PRE2CFG', 'value' => Configuration::get('FSOL_PRE2CFG') ];
        $fsxconfs[] = [ 'id' => 'FSOL_PRE3CFG', 'value' => Configuration::get('FSOL_PRE3CFG') ];


        return view('fsx_connector::fsx_configuration_keys.'.'key_group_'.$tab_index, compact('tab_index', 'key_group', 'fsxconfs'));
    }

    /**
     * Update the specified resource in storage.
     * PUT 
     *
     * @param  
     * @return Response
     */
    public function configurationsUpdate(Request $request)
    {
        $settings = [];

        // Get Configurations from FactuSOL Web
        FSxTools::setFSxConnection();

        // Start Logic Probe, now!
        try {

            // $groups = \DB::connection('fsx-bbdd')->select( "select * from ".TABLA_CONFIGURACION." WHERE `CODCFG` = ?", [1] );

            $settings = \DB::connection('fsx-bbdd')->table( TABLA_CONFIGURACION )->where('CODCFG', '1')->first();
        }

        catch( \Exception $e ) {

            /*
            $e->getMessage(); // Error message.

            $e->getRequest(); // Last request data.

            $e->getResponse(); // Last response data.
            */

            return redirect()->route('fsxconfigurationkeys.index')
                    ->with('error', $e->getMessage());

        }

        // Save Settings Cache

        Configuration::updateValue('FSOL_CONFIGURATIONS_CACHE', json_encode($settings));

        // Set some handy values

        Configuration::updateValue('FSOL_TCACFG', $settings->TCACFG);
        Configuration::updateValue('FSOL_AUSCFG', $settings->AUSCFG);
        Configuration::updateValue('FSOL_SPCCFG', $settings->SPCCFG);

        Configuration::updateValue('FSOL_PIV1CFG', $settings->PIV1CFG);
        Configuration::updateValue('FSOL_PIV2CFG', $settings->PIV2CFG);
        Configuration::updateValue('FSOL_PIV3CFG', $settings->PIV3CFG);

        Configuration::updateValue('FSOL_PRE1CFG', $settings->PRE1CFG);
        Configuration::updateValue('FSOL_PRE2CFG', $settings->PRE2CFG);
        Configuration::updateValue('FSOL_PRE3CFG', $settings->PRE3CFG);


        return redirect('fsx/fsxconfiguration')
                ->with('success', l('This configuration has been successfully updated', [], 'layouts'));
    }


/* ********************************************************************************************* */    


    /**
     * Show the form for editing the specified resource.
     * GET 
     *
     * @param  
     * @return Response
     */
    public function configurationTaxesEdit()
    {
        $tab_index = 2;

// abi_r($tab_index, true);

        $key_group = [];
        $key_percent = [];

        foreach ($this->conf_keys[$tab_index] as $key)
        {
              $key_group[$key]= Configuration::get($key);

              $i = substr($key, -1);

              $key_percent[$key] = $i == '4' ? '0.00' : number_format(floatval( Configuration::get('FSOL_PIV'.$i.'CFG') ), 2);
        }
        $taxList = \App\Tax::orderby('name', 'desc')->pluck('name', 'id')->toArray();

        return view('fsx_connector::fsx_configuration_keys.'.'key_group_'.$tab_index, compact('tab_index', 'key_group', 'key_percent', 'taxList'));
    }

    /**
     * Update the specified resource in storage.
     * PUT 
     *
     * @param  
     * @return Response
     */
    public function configurationTaxesUpdate(Request $request)
    {
        $tab_index = 2;

        $key_group = $this->conf_keys[$tab_index];

        // Validation rules
        $rules = [];
        
        foreach ($this->conf_keys[$tab_index] as $key)
        {
            $rules[ $key ] = 'sometimes|nullable|exists:taxes,id';
        }

        $this->validate($request, $rules);

        foreach ($key_group as $key) 
        {
            if ($request->has($key)) {

                \App\Configuration::updateValue($key, $request->input($key));
            }
        }

        return redirect()->route('fsx.configuration.taxes')
                ->with('success', l('This configuration has been successfully updated', [], 'layouts'));
    }


/* ********************************************************************************************* */


    /**
     * Show the form for editing the specified resource.
     * GET 
     *
     * @param  
     * @return Response
     */
    public function configurationPaymentMethodsEdit()
    {
        $tab_index = 3;

        // return $tab_index ;

        $fsolpaymethods = FSxTools::getFormasDePagoList();

//        abi_r($fsolpaymethods, true);

        // Save Payment Methods Cache
        Configuration::updateValue('FSX_FORMAS_DE_PAGO_CACHE', json_encode($fsolpaymethods));

        // Beautifier:
        $fsolpaymethods_full=[];
        foreach ($fsolpaymethods as $key => $value) {
            # code...
            $fsolpaymethods_full[$key] = "[$key] &nbsp; $value";
        }

        // aBillander Methods
        $pgatesList = \App\PaymentMethod::select('id', 'name')->orderby('name', 'asc')->get()->toArray();

        // abi_r($pgatesList, true);

        // Payment Methods Dictionary
        $dic = [];
        $dic_val = [];
        foreach ($pgatesList as $paymethod) {
            $dic[$paymethod['id']] = FSxTools::getPaymentMethodKey( $paymethod['id'] );
            $dic_val[$paymethod['id']] = Configuration::get($dic[$paymethod['id']]);
        }

        return view('fsx_connector::fsx_configuration_keys.'.'key_group_'.$tab_index, compact('tab_index', 'fsolpaymethods', 'fsolpaymethods_full', 'dic', 'dic_val', 'pgatesList'));
    }

    /**
     * Update the specified resource in storage.
     * PUT 
     *
     * @param  
     * @return Response
     */
    public function configurationPaymentMethodsUpdate(Request $request)
    {
        // Validation rules
        $rules = [];
/* Poor man...        
        foreach ( $request->input('dic') as $key => $val) 
        {
            $rules[ 'dic.'.$key ] = 'sometimes|nullable|exists:payment_methods,id';
        }

        $this->validate($request, $rules);
*/        
        foreach ( $request->input('dic') as $key => $val) 
        {
            Configuration::updateValue($key, $val);
        }
        // Save Payment Gateways Dictionary Cache
        $dic_val = [];
        // aBillander Methods
        $pgatesList = \App\PaymentMethod::select('id', 'name')->orderby('name', 'desc')->get()->toArray();
        foreach ($pgatesList as $paymethod) {
            $dic_val[$paymethod['id']] = Configuration::get(FSxTools::getPaymentMethodKey($paymethod['id']));
        }

        Configuration::updateValue('FSX_FORMAS_DE_PAGO_DICTIONARY_CACHE', json_encode($dic_val));

//      abi_r($request->input('dic'));
//      abi_r($dic_val, true);

        return redirect()->route('fsx.configuration.paymentmethods')
                ->with('success', l('This configuration has been successfully updated', [], 'layouts'));
    }


/* ********************************************************************************************* */

    
    public function checkFolder( $folder = '' )
    {
        return $folder && is_dir($folder) && is_writable ($folder);
    }

    
    public function checkFolderErrors()
    {
        // Check folder permissions
        $dest_clientes = Configuration::get('FSOL_CBDCFG').Configuration::get('FSOL_CCLCFG'); // Destination folders
        $dest_pedidos  = Configuration::get('FSOL_CBDCFG').Configuration::get('FSOL_CPVCFG'); // 

        $errors = [];

        if ( !$this->checkFolder( $dest_clientes ) ) $errors[] = l('La carpeta <b style="font-weight: bold">'.$dest_clientes.'</b> (descarga de Clientes) no existe, o no tiene permisos de escritura.');

        if ( !$this->checkFolder( $dest_pedidos ) ) $errors[] = l('La carpeta <b style="font-weight: bold">'.$dest_pedidos.'</b> (descarga de Pedidos) no existe, o no tiene permisos de escritura');
        
        return $errors;
    }


/* ********************************************************************************************* */


}


/* ********************************************************************************************* */   





// Define the database table names used in the project
  define('TABLA_AGENTES'        , 'F_AGE');
  define('TABLA_ALMACENES'      , 'F_ALM');
  define('TABLA_ARTICULOS'      , 'F_ART');
  define('TABLA_AUT'            , 'F_AUT');
  define('TABLA_CONFIGURACION'  , 'F_CFG');
  define('TABLA_CLIENTES'       , 'F_CLI');
  define('TABLA_DESCUENTOS'     , 'F_DES');
  define('TABLA_DIRECCIONES'    , 'F_DIR');
  define('TABLA_EMPRESAS'       , 'F_EMP');
  define('TABLA_FACTURAS'       , 'F_FAC');
  define('TABLA_FAMILIAS'       , 'F_FAM');
  define('TABLA_FORMAS_PAGO'    , 'F_FPA');
  define('TABLA_LINEAS_FACTURAS', 'F_LFA');
  define('TABLA_LINEAS_PEDIDOS' , 'F_LPC');
  define('TABLA_LINEAS_TARIFAS' , 'F_LTA');
  define('TABLA_PEDIDOS'        , 'F_PCL');
  define('TABLA_SECCIONES'      , 'F_SEC');
  define('TABLA_STOCK'          , 'F_STO');
  define('TABLA_TARIFAS'        , 'F_TAR');
  
  define('TABLA_DICCIONARIO'    , 'fsx_dic');
  define('TABLE_FSX_LOG'        , 'fsx_log');
  define('TABLE_FSX_ORDERS'     , 'fsx_orders');
