<?php 

	class Driver {
		
		    // DB STUFF
	    private $con;
	    private $table = "drivers";

        // Customer properties
	    public $id;
	    public $first_name;
	    public $last_name;
	    public $email;
	    public $id_type;
	    public $id_no;
	    public $phone_no;
	    public $dl_no;
	    public $dl_expiry;
        public $date_of_birth;
	    public $id_image;
	    public $id_back_image;
	    public $profile_image;
	    public $license_image;
	    public $created_at;

		    // Constructor with DB
	    public function __construct($db)
	    {
	        // when we instantiate a new post we'll pass in the db as a parameter
	        $this->con = $db;
	    }

	    // get drivers
	    public function read(){}

	    // get booking drivers 
	    public function booking_drivers(){
	    	$status = "false";

            $sql = "SELECT id, first_name, last_name FROM drivers WHERE deleted =  ? ORDER BY id DESC";

            // prepare the statement
            $stmt = $this->con->prepare($sql);

            $stmt->execute([$status]);

	        return $stmt;
	    }
	}

 ?>