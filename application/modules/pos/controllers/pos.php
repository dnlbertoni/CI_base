<?php

/**
 * Controlador de todo lo referente a la facturacion
 *
 * @author dnl
 * @category P.O.S. (point of sale)
 */
class Pos extends MY_Controller{
  /**
   * funcion constructor aca se inicializa lo siguiente:
   * @var integer $puesto puesto con el que sale el comprobante 
   * 
   */
  private $puesto;
  function __construct() {
    parent::__construct();

    $this->puesto = PUESTO;
    $this->load->model('Articulos_model');
    $this->load->model('Clientes_model');
    $this->load->model('Numerador_model');
    $this->load->model('Tmpmovim_model');
	$this->load->model('Facencab_model');
	$this->load->model('Facmovim_model');
  }
  /**
   * Pantalla de inicio de facturacion
   */
  function index(){
    /*
    $data['sucursales']=$this->Sucursales_model->getAll();
    Template::set_block('novedades', 'pos/_novedades');
    Template::set($data);
    Template::render();
    */
    Template::redirect('pos/pedido');
  }
  function pedido($clienteId=1){
    $fechoy= new DateTime();
    $data['fecha']=$fechoy->format(" d/m/Y");
    $cliente=$this->Clientes_model->getById($clienteId);
    $data['cliente']="$cliente->apellido $cliente->nombre";
    $data['clienteId']=$clienteId;
    $numero =$this->Numerador_model->getNextRemito($this->puesto);
    $data['comprobante']=sprintf("%04.0f-%08.0f", $this->puesto, $numero);
    $data['puesto']=$this->puesto;
    $data['numero']=$numero;
    $data['pagina']=base_url().'index.php/pos/addArticulo';
    $data['paginaDet']="'".base_url().'index.php/pos/muestroDetalle'."'";
    $data['paginaDel']="'".base_url().'index.php/pos/vacioTMP'."'";
    $data['ocultos']=array('fecha'=>$fechoy->format(" d/m/Y"));
    Template::set_block('novedades', 'pos/_novPedidos');
    Template::set($data);
    Template::render();
  }
  function addArticulo(){
    $articulo = $this->Articulos_model->getById($this->input->post('articulo'));
    $datos['puesto']  =$this->input->post('puesto');
    $datos['cliente_id']=$this->input->post('cliente');
    $datos['numero']  =$this->input->post('numero');
    $datos['cantidad']=$this->input->post('cantidad')*$articulo->kg;
    $datos['articulo']=$this->input->post('articulo');
    $datos['precio']=$articulo->precio;
    $id=$this->Tmpmovim_model->add($datos);
    $this->muestroDetalle($this->input->post('puesto'),$this->input->post('numero'));
  }
  function muestroDetalle($puesto=false, $numero=false){
    $puesto=($puesto)?$puesto:$this->input->post('puesto');
    $numero=($numero)?$numero:$this->input->post('numero');
    $this->load->model('Tmpmovim_model');
    $articulos=$this->Tmpmovim_model->getDetalle($puesto,$numero);
    $data['articulos']=$articulos;
    $this->load->view('pos/movimientos', $data);
  }
  function vacioTMP($puesto=false,$numero=false){
    $puesto=($puesto)?$puesto:$this->input->post('puesto');
    $numero=($numero)?$numero:$this->input->post('numero');

    $articulos=$this->Tmpmovim_model->vacioTemporal($puesto,$numero);
    $data['articulos']=array();
    $this->load->view('pos/movimientos', $data);
  }
  function imprimoComprobante($puesto=false, $numero=false, $imprimir=false){
    $articulos=$this->Tmpmovim_model->toSave($puesto, $numero);
	$importe = 0;
	foreach($articulos as $articulo){
		$importe += $articulo->importe;
	};
    $datosEncab = array('tipcom'=>1,
                        'puesto'=>$puesto,
                        'numero'=>$numero,
                        'letra'=>'X',
                        'cliente_id'=>$articulo->cliente_id,
                        'importe'=>$importe,
                        'estado'=>1
                        );
    $idencab = $this->Facencab_model->agregar($datosEncab);
    $datosCaj = array('faencab-id'=>$idencab,
                        'cajcon_id'=>1, // factura de ventas
                        'importe'=>$importe,
                        'fpago_id'=>1
                        );
	if ($idencab){
        $this->Numerador_model->nextRemito($this->puesto);
        $this->Cajmovim_model->agregar($datosCaj);        
    }
    foreach($articulos as $articulo){
    $datosMovim[]= array( 'facencab_id' => $idencab,
                          'articulo_id' => $articulo->articulo_id,
                          'cantidad'    => $articulo->cantidad,
                          'precio'      => $articulo->articulo_precio,
                          'publico'     => $articulo->articulo_publico,
                          'tasaiva'     => 1
                        );
    }
    if($this->Facmovim_model->add($datosMovim)){
      $this->Tmpmovim_model->vacioTemporal($puesto,$numero);
      redirect('pos/pdf/imprimoComprobante/'.$idencab);
    }else{
      echo 'Error al grabar el comprobante';
    };
  }
}

