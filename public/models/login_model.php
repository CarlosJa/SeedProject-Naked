<?php
class Login_Model extends Model
{
    public function __construct()
    {
        parent::__construct();
    }


	/*
		This is basic authentication level for log in. However we need to enhance this a bit. 
		If User Logged 		then redirect to User screen
		if Admin Logged 	then redirect to Admin Screen 
		If Agency Logged 	then redirect to Agency screen. 
	*/
    public function run()
    {
        $sth = $this->db->prepare("SELECT user_id, username, role_id FROM users WHERE 
                						username = :username AND password = :password");
        $sth->execute(array(
            ':username' => $_POST['username'],
            ':password' => Hash::create('sha256', $_POST['password'], HASH_PASSWORD_KEY)
        ));
        
        $data = $sth->fetch();


        $count =  $sth->rowCount();
        if ($count > 0) {
            // login
            Session::init();
            Session::pSet('UserProfile', 'roleId', $data['role_id']);
            Session::pSet('UserProfile', 'loggedIn', true);
            Session::pSet('UserProfile', 'userId', $data['user_id']);
            
            $sql = "SELECT t2.perm_controller,t2.perm_action FROM role_perm as t1
					JOIN permissions as t2 ON t1.perm_id = t2.perm_id
					WHERE t1.role_id = :role_id";
           
            $permission = array();
            $permission = $this->db->select($sql, array(':role_id' => $data['role_id']));  
            
            Session::set('permission', $permission);



			// This is a bit annoying to have it hard coded but its good enough for now. 
			// This will set the specific redirects for the access levels Business, Client & User  there's only 3 screens with sub levels of permissions. 
			switch($data['role_id']) { 
				# Business 
				case '1' :  // Administrator
				case '2' :  // Marketing
					 header('location: /admin');
				break; 
				
				# Client
				case '4' : // Agency
					 header('location: /dashboard');
				break;
				
				#End User
				case '6' :   // Users
					 header('location: /dashboard');
				break; 
			}
			         
           
        } else {
            header('location: ../login');
        }
        
    }
    
}