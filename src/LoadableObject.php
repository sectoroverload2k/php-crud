<?php

namespace PhpCrud;

abstract class LoadableObject
{
    public function __construct($data)
    {
        $model = get_called_class().'_model';
        foreach($data as $k => $v) {
            if(property_exists(get_called_class(), $k)) {
                $this->$k = $v;
            }
        }
        if(defined(sprintf("%s::%s", get_called_class(), 'primary_key'))) {
            $key = $this::primary_key;
            if(property_exists(get_called_class(), $key)) {
                $this->links['self'] = sprintf('%s/%s/%d', BASE_URL, $model::table, $this->$key);
            }
        }
    }
    public function __toString()
    {
        return json_encode($this);
    }

    public function expand($list)
    {
        if(gettype($list) == 'string') {
            $list = explode(',', $list);
        }
        foreach($list as $item) {
            $model = ucwords($item).'_model';
            if(isset($this->{$item.'_id'})) {
                $id = $this->{$item.'_id'};
                $this->$item = $model::get($id);
            }
        }
    }
}
