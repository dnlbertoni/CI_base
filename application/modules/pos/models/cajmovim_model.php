<?php
/**
 * Description of cajmovim
 *
 * @author dnl
 */
class Cajmovim_model extends MY_Model{
    function __construct() {
        parent::__construct();
        $this->setTable('cajmovim');
    }
    function agregar($datos){
        $this->db->set('fecha','NOW()', false);
        return $this->add($datos);
    }
}
