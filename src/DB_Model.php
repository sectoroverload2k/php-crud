<?php
namespace PhpCrud;

use PhpCrud\Model;

abstract class DB_Model extends Model {
  public function __construct(){
  }

	public function __buildSelectAll(){
		$class = get_called_class();
		$sql = sprintf('SELECT %s, %s FROM %s', join(',',$class::keys), join(',',$class::attrs), $class::table);
		if(in_array('location_id', $class::attrs )){
			$sql .= sprintf(' WHERE (location_id=%d OR location_id IS NULL)', Token::getDefaultLocationId());
		}
		return $sql;
	}
	public function __buildSelect($id){
		$class = get_called_class();
		$sql = sprintf('SELECT %s, %s FROM %s WHERE _id=%d', join(',',$class::keys), join(',',$class::attrs), $class::table , $id);
		if(in_array('location_id', $class::attrs )){
			$sql .= sprintf(' AND (location_id=%d OR location_id IS NULL)', Token::getDefaultLocationId());
		}
		return $sql;
	}

	public function __buildSelectWhere($col, $val){
		$class = get_called_class();
		$sql = sprintf('SELECT %s, %s FROM %s WHERE %s=%s', join(',',$class::keys), join(',',$class::attrs), $class::table, $col, $val);
		return $sql;
	}

	public function __buildInsert($data){
		$class = get_called_class();
		$sql = sprintf('INSERT INTO %s SET ', $class::table);

    $schema = (defined("$class::schema")) ? $class::schema : [];
		foreach($class::attrs as $i => $key){
      if(isset($data[$key])){
        if(in_array($key, array_keys($schema))){
          switch($schema[$key]){
            case 'int':
              if(!empty($data[$key])){ $sql .= sprintf("%s=%d, ", $key, $data[$key]); }
              break;
            default:
              $sql .= sprintf("%s='%s', ", $key, addslashes($data[$key]));
          }

        } else {
          $sql .= sprintf('%s="%s", ', $key, addslashes($data[$key]));
        }
      }
    }
    $sql = rtrim($sql,', ');
		return $sql;
	}

	public function __buildUpdate($id, $data){
		$class = get_called_class();
    $updates = 0;
    $sql = sprintf('UPDATE %s SET ', $class::table);

		$restricted = (defined("$class::restricted")) ? $class::restricted : [];
    $schema = (defined("$class::schema")) ? $class::schema : [];
    foreach($class::attrs as $i => $key){
      if(isset($data[$key]) && !in_array($key, $restricted)){
        if(in_array($key, array_keys($schema))){
          switch($schema[$key]){
            case 'int':
              if(!empty($data[$key])){ $sql .= sprintf("%s=%d, ", $key, $data[$key]); }
              break;
            default:
              $sql .= sprintf("%s='%s', ", $key, addslashes($data[$key]));
          }
        } else {
          $sql .= sprintf("%s='%s', ", $key, addslashes($data[$key]));
        }

        $updates++;
      }
    }
    $sql = rtrim($sql, ', ');
		if(in_array('last_updated',$class::attrs) || in_array('last_updated',$class::restricted)){
			$sql .= ", last_updated=NOW()";
		}
    $sql .= sprintf(' WHERE _id=%d', $id);

		return ($updates > 0) ? $sql : false;
	}


	public function __buildDelete($id){
		$class = get_called_class();
		$sql = sprintf('DELETE FROM %s WHERE _id=%d', $class::table, $id);
		return $sql;
	}
}



