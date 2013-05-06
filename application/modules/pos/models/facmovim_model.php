<?php

class Facmovim_model extends MY_Model{
	function __construct(){
		parent::__construct();
		$this->setTable('facmovim');
	}
	function add($datos){
		foreach($datos as $d){
			$this->db->insert($this->getTable(),$d);
		}
		return true;
	}
	function getComprobante($idencab){
		$this->db->select('facencab.fecha as fecha');
		$this->db->select('facencab.puesto as puesto');
		$this->db->select('facencab.numero as numero');
		$this->db->select('facencab.cliente_id as cliente_id');
		$this->db->select('clientes.apellido as cliente_apellido');
        $this->db->select('clientes.nombre as cliente_nombre');
		$this->db->select('facmovim.articulo_id as articulo_id');
		$this->db->select('articulos.nombre as articulo_nombre');
		$this->db->select('facmovim.cantidad as cantidad');
        $this->db->select('facmovim.publico as publico');
		$this->db->select('facmovim.precio as precio');
		$this->db->select('facmovim.precio * facmovim.cantidad as importe');
		$this->db->select('facencab.importe as total');
		$this->db->from($this->getTable());
		$this->db->join('facencab','facencab.id=facmovim.facencab_id','inner');
		$this->db->join('articulos','facmovim.articulo_id=articulos.id','inner');
		$this->db->join('clientes', 'facencab.cliente_id=clientes.id','inner');
		$this->db->where('facmovim.facencab_id',$idencab);
		return $this->db->get()->result();
	}
}