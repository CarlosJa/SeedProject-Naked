<?php namespace App\Components;

use Model as Model;



class Role extends Model {
	
	public function __construct() {
		 parent::__construct(); 
	}


    public static function deniedAction($permission, $controller) {
		$controller = strtolower($controller);
        foreach($permission as $permContrller => $PerMethod) {
			if($permContrller == $controller) {
				$AllowAccess = 1;
				break;
			}
		}
		
		if($AllowAccess == 1) {
			return true;
		} else {
			print_array('Deny Access ' . $controller);
		}
        
    }


    public static function AccessGranted($permission, $method) {
		 //print_array($permission);

		if($permission[0] == "*") { return true; } else {
			if (in_array($method, $permission)) {
				return true;
			} else {
				die('Access Denied - ' . $method);
			}
		}
    }


//////////////////////// Manage Role Application ////////////////////////////
	
	public function Group() {
		$Return = $this->db->select("SELECT COUNT(*) AS accounts, a.role_id, b.role_name AS RoleName, b.createdate AS CreateDate FROM usergen a JOIN roles b ON a.role_id=b.role_id GROUP BY a.role_id");
		return $Return; 
	}
	
	public function getPerms($data=""){
		if($data){
			$Search .= "WHERE 1";
			foreach($data as $key => $val) :
			if(!$val) continue;
			$string = strtolower($val);
				$Search .= " AND {$key} LIKE '%{$string}%'";
			endforeach;
		}
		$Return = $this->db->select("SELECT * FROM permissions {$Search} ORDER BY perm_controller ASC");
		// sendlog("SELECT * FROM permissions {$Search} ORDER BY perm_controller ASC");
		return $Return;
	}
	
	public function insertPerm($data){
		$data['perm_controller'] = strtolower($data['perm_controller']);
		$data['perm_action'] = strtolower($data['perm_action']);
		
		$Return = $this->db->insert('permissions', $data);
//		sendlog($Return);
		return $Return;
	}
	
	
	public function deletePerm($id) {
		$Return = $this->db->query("DELETE FROM permissions WHERE perm_id='{$id}'");
		return $Return;
	}
	
	
	public function updatePerm($data, $pid){
		$Return = $this->db->update('permissions', $data, "perm_id='{$pid}'");
		return $Return;
	}
	
	
	
		// insert a new role
	public function insertRole($data) {
		$rdata['role_name'] = $data['groupname'];
		$rdata['controller'] = $data['grouprole'];
		$Return = $this->db->insert('roles', $rdata);
		$getID = json_decode($Return);
		
		foreach($data['permission'] as $permid) :
			$permData['role_id']=$getID->ID ;
			$permData['perm_id']=$permid;
			$RolePerms = $this->db->insert('role_perm', $permData);
		endforeach; 
		
		return $Return;
	}

	// insert array of roles for specified user id
	public function insertUserRoles($userid, $roles) {
		$sql = "INSERT INTO user_role (userid, role_id) VALUES (:userid, :role_id)";
		$sth = $GLOBALS["DB"]->prepare($sql);
		$sth->bindParam(":userid", $userid, PDO::PARAM_STR);
		$sth->bindParam(":role_id", $role_id, PDO::PARAM_INT);
		foreach ($roles as $role_id) {
			$sth->execute();
		}
		return true;
	}

	// delete array of roles, and all associations
	public function deleteRoles($roles) {
		$sql = "DELETE t1, t2, t3 FROM roles as t1
				JOIN user_role as t2 on t1.role_id = t2.role_id
				JOIN role_perm as t3 on t1.role_id = t3.role_id
				WHERE t1.role_id = :role_id";
		$sth = $GLOBALS["DB"]->prepare($sql);
		$sth->bindParam(":role_id", $role_id, PDO::PARAM_INT);
		foreach ($roles as $role_id) {
			$sth->execute();
		}
		return true;
	}

	
	// delete ALL roles for specified user id
	public function deleteUserRoles($userid) {
		$sql = "DELETE FROM user_role WHERE userid = :userid";
		$sth = $GLOBALS["DB"]->prepare($sql);
		return $sth->execute(array(":userid" => $userid));
	}

		// check if a user has a specific role
	public function hasRole($role_name) {
		return isset($this->roles[$role_name]);
	}


	
	public static function createRole(){
		die('testing');
	}
	
	public function roleType(){
        $SQL = "SELECT role_id, role_name FROM roles";
        $Return = $this->db->select($SQL);
		
		return $Return;
	}
	
	
} // End Class 