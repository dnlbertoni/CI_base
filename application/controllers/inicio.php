<?php
/**
 * Description of inicio
 *
 * @author dnl
 */
class Inicio extends MY_Controller{
  function __construct() {
    parent::__construct();
  }
  function index(){
    Template::render();
  }
}
