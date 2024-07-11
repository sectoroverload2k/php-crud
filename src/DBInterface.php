<?php
namespace PHP_Crud;

interface DBInterface {
  public function list();
  public function get($id);
  public function create($data);
  public function update($id, $data);
  public function delete($id);
}