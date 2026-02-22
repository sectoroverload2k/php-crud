<?php

namespace PhpCrud;

abstract class LoadableList extends \ArrayObject implements \ArrayAccess, \JsonSerializable
{
    protected array $items = [];

    public function __construct($input = array(), $flags = 0, $iterator_class = 'ArrayIterator')
    {
        if (isset($input) && is_array($input)) {
            $this->items = $input;
        }
        parent::__construct($input, $flags, $iterator_class);
    }
    public function expand($list)
    {
        if(gettype($list) == 'string') {
            $list = explode(',', $list);
        }

        $this->items = array_map(function ($i) use ($list) {
            $i->expand($list);
            return $i;
        }, $this->items);
    }
    public function jsonSerialize(): mixed
    {
        return $this->items;
    }
    public function __toString()
    {
        return json_encode($this->items);
    }
}
