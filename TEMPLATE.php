<?php

class Template extends LoadableObject {
  public $_id, $name, $description, $date_created;
  public function __construct($data){
    parent::__construct($data);
		//load_model('Something');
  }
  
}

class Templates_model extends DB_Model implements DBInterface {
  const keys = ['_id'];
  const attrs = ['name','description'];
  const table = 'tablename';
	const restricted = ['date_created'];

  function list($data=[]){
    Authenticator::requirePermission('templates:list');
		global $db;
		$user_id = Token::getUserId();
		$location_id = Token::getDefaultLocationId();

		$sql = self::__buildSelectAll();
		$res = $db->query($sql);
		while($dtl = $res->fetchAssoc()){
			$item = new Template($dtl);
			if(!empty($expand)){ $item->expand($expand); }
			$retval[] = $item;
		}
		return new TemplateList( $retval );
  }
  function get($id){
    Authenticator::requirePermission('templates:get');
		global $db;
		$sql = self::__buildSelect($id);
		$res = $db->query($sql);
		if($res->numRows() == 0){ die(new NotFoundException()); }
		return new Template($res->fetchAssoc());
  }
  function create($data){
    Authenticator::requirePermission('templates:create');
		global $db;
		if(isset($data['location_id']) && !in_array($data['location_id'], Token::getLocationIds())){
			die(new UnauthorizedAccessException());
		}

		if(empty($data['location_id'])){ $data['location_id'] = Token::getDefaultLocationId(); }
		$sql = self::__buildInsert($data);
		$res = $db->query($sql);
		$item_id = $db->insert_id();
		return Templates_model::get($item_id);
  }
  function update($id,$data){
    Authenticator::requirePermission('templates:update');
		global $db;
		$item = Templates_model::get($id);
		if(isset($data['location_id']) && !in_array($data['location_id'], Token::getLocationIds())){
			die(new UnauthorizedAccessException());
		}
		$sql = self::__buildUpdate($id, $data);
		if($sql){
			$res = $db->query($sql);
		}
		return Templates_model::get($id);
  }
  function delete($id){
    Authenticator::requirePermission('templates:delete');
		global $db;
		$item = Templates_model::get($id);
		if(!empty($item->location_id) && !in_array($item->location_id, Token::getLocationIds())){
			die(new UnauthorizedAccessException());
		}
		$sql = self::__buildDelete($id);
		$db->query($sql);
		return true;
  }
}
class Template_model extends Templates_model {};
class TemplateList extends LoadableList {};
