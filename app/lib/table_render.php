<?php
class TableRenderer{
    public $item = ['a'];
    public $data = ['a'];
    public $entity_type = 'company';
 
    public function render()
    {
        $param['entity_type'] = $this->entity_type;
        $param['item'] = $this->item;
        $param['entities'] = $this->data;
        return view('v/lib/list', $param, true);
    }
}
