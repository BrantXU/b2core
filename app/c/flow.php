<?php
class flow extends base {
  protected $m;
  
  public function __construct() {
    parent::__construct();
    $this->m = load('m/flow_m');
  }
}
?>
