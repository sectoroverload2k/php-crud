<?php

namespace PhpCrud;

interface DBInterface
{
    public static function list();
    public static function get($id);
    public static function create($data);
    public static function update($id, $data);
    public static function delete($id);
}
