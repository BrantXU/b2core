<?php
class page extends base{
  function __construct(){
    parent::__construct();
  }
  function view($url = 'home' , $action = 'view'){
  	$this->display('v/'.$url.'/'.$action);
  }
}
