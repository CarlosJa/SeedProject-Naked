<? namespace App\Components;

use Model as Model;



class Template extends Model 
{
	public function __construct() {
		 parent::__construct();

	}


	public function updateUser() { 
		echo "UpdateUser";
	}
	
	
	public function deleteUser() {
		echo "delete User";
	}
	
	public function getUsers() { 
		echo "Retrieve Users"; 
	}

	


}