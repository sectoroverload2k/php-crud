<?php
namespace PhpCrud;

abstract class LoadableObject {
  public function __construct($data){
		$model = get_called_class().'_model';
    foreach($data as $k => $v){
      if(property_exists(get_called_class(), $k)){
        $this->$k = $v;
      }
    }
		if(property_exists(get_called_class(), '_id')){
			$this->links['self'] = sprintf('%s/%s/%d', BASE_URL, $model::table, $this->_id);
		}
  }
  public function __toString(){
    return json_encode($this);
  }

	public function expand($list){
		if(gettype($list)=='string'){
			$list = explode(',',$list);
		}
		foreach($list as $item){
			$model = ucwords($item).'_model';
			if(isset($this->{$item.'_id'})){
				$id = $this->{$item.'_id'};
				$this->$item = $model::get($id);
			}
		}
	}
}
