<?php
class TableRenderer{
    public $item = ['a'];
    public $data = ['a'];
    public $entity_type = 'company';
    public $opt;
 
    public function render()
    {
        // todo: 如果 filter 存在, 需要过滤掉 表头中对应的字段。再新建对象时需要作为默认属性
        if(isset($this->opt['filter'])){
            foreach($this->opt['filter'] as $key=>$value){
                $this->opt['filter'][$key] = $value == 'eid'?$this->opt['eid']:$value; 
                unset($this->item[$key]);// $key;
            }
        }
        //print_r($this->item);
        $param['entity_type'] = $this->entity_type;
        $param['item'] = $this->item;
        $param['opt'] = isset($this->opt['filter'])? base64_encode(json_encode($this->opt['filter'])):'';
        $param['entities'] = $this->data;
        return view('v/lib/list', $param, true);
    }
}
