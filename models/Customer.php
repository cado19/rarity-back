<?php
// this class is a model for vehicle details.
class Customer
{
    // DB STUFF
    private $con;
    private $table = "customer_details";

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
    public $residential_address;
    public $work_address;
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

    // get customers
    public function read()
    {
        $status = "false";

            //create the query
            $query = "SELECT id, first_name, last_name, email, id_no, phone_no FROM customer_details WHERE deleted = ? ORDER BY first_name DESC";

            // prepare statement
            $stmt = $this->con->prepare($query);

            //execute the query
            $stmt->execute([$status]);

            return $stmt;

    }

    // get single vehicle
    public function read_single()
    {
        $status = "false";

        //create the query
        $query = "SELECT id, first_name, last_name, email, id_no, phone_no, dl_expiration, residential_address, work_address, date_of_birth, id_image, id_back_image, profile_image, license_image FROM customer_details WHERE deleted =  ? and id = ? LIMIT 0, 1";

        // prepare statement
        $stmt = $this->con->prepare($query);

        //execuute the query
        $stmt->execute([$status, $this->id]);

        //fetch the array
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        //set the properties
        $this->first_name          = $row['first_name'];
        $this->last_name           = $row['last_name'];
        $this->email               = $row['email'];
        $this->id_no               = $row['id_no'];
        $this->phone_no            = $row['phone_no'];
        $this->dl_expiry           = $row['dl_expiration'];
        $this->residential_address = $row['residential_address'];
        $this->work_address        = $row['work_address'];
        $this->date_of_birth       = $row['date_of_birth'];
        $this->id_image            = $row['id_image'];
        $this->id_back_image       = $row['id_back_image'];
        $this->profile_image       = $row['profile_image'];
        $this->license_image       = $row['license_image'];

        return $stmt;
    }

    public function read_recent()
    {
        $status = "false";

        try {
            $this->con->beginTransaction();
        } catch (Exception $e) {

        }

        //create the query
        $query = "SELECT id, first_name, last_name, email, id_no, phone_no FROM customer_details WHERE deleted =  ? ORDER BY created_at DESC LIMIT 10";

        // prepare statement
        $stmt = $this->con->prepare($query);

        //execuute the query
        $stmt->execute();

        return $stmt;
    }

    // create customer
    public function create()
    {

        // Create the query
        $sql = "INSERT INTO customer_details (first_name, last_name, email, id_type, id_no, dl_no, dl_expiration, phone_no, residential_address, work_address, date_of_birth) VALUES ( ? ,  ? ,  ? ,  ? ,  ? ,  ? ,  ? ,  ? ,  ? ,  ? ,  ? )";

        // prepare the statement
        $stmt = $this->con->prepare($sql);

        //clean the data

        $this->first_name          = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name           = htmlspecialchars(strip_tags($this->last_name));
        $this->email               = htmlspecialchars(strip_tags($this->email));
        $this->id_type             = htmlspecialchars(strip_tags($this->id_type));
        $this->id_no               = htmlspecialchars(strip_tags($this->id_no));
        $this->phone_no            = htmlspecialchars(strip_tags($this->phone_no));
        $this->dl_no               = htmlspecialchars(strip_tags($this->dl_no));
        $this->dl_expiry           = htmlspecialchars(strip_tags($this->dl_expiry));
        $this->residential_address = htmlspecialchars(strip_tags($this->residential_address));
        $this->work_address        = htmlspecialchars(strip_tags($this->work_address));
        $this->date_of_birth       = htmlspecialchars(strip_tags($this->date_of_birth));

        if ($stmt->execute([$this->first_name, $this->last_name, $this->email, $this->id_type, $this->id_no, $this->dl_no, $this->dl_expiry, $this->phone_no, $this->residential_address, $this->work_address, $this->date_of_birth])) {
            $this->id = $this->con->lastInsertId();
            return true;
        } else {
            // print error if something goes wrong
            printf("Error :  % s . \n ", $stmt->error);
            return false;
        }

    }

    public function booking_customers()
    {
        $status = "false";

        try {
            $this->con->beginTransaction();
            // Create the query
            $sql = "SELECT id, first_name, last_name FROM customer_details WHERE deleted =  ? ORDER BY id DESC";

            // prepare the statement
            $stmt = $this->con->prepare($sql);

            $stmt->execute([$status]);

            $this->con->commit();
        } catch (Exception $e) {
            $this->con->rollback();
        }

        return $stmt;
    }

    public function check_unique_email(){
        $sql = "SELECT id FROM customer_details WHERE email LIKE ?";

        // prepare the statement
        $stmt = $this->con->prepare($sql);

        $stmt->execute([$this->email]);

        return $stmt;
    }

    public function save_license(){
        $sql = "UPDATE customer_details SET license_image = ? WHERE id = ?";

        // prepare the statement
        $stmt = $this->con->prepare($sql);

        if ($stmt->execute([$this->license_image, $this->id])) {
            return true;
        } else {
                      // print error if something goes wrong
            printf("Error :  % s . \n ", $stmt->error);
            return false;
        }
    }

    public function save_id(){
        $sql = "UPDATE customer_details SET id_image = ?, id_back_image = ? WHERE id = ?";

        // prepare the statement
        $stmt = $this->con->prepare($sql);

        if ($stmt->execute([$this->id_image, $this->id_back_image, $this->id])) {
            return true;
        } else {
                      // print error if something goes wrong
            printf("Error :  % s . \n ", $stmt->error);
            return false;
        }
    }   

    public function save_profile_image(){
        $sql = "UPDATE customer_details SET profile_image = ? WHERE id = ?";

        // prepare the statement
        $stmt = $this->con->prepare($sql);

        if ($stmt->execute([$this->profile_image, $this->id])) {
            return true;
        } else {
                      // print error if something goes wrong
            printf("Error :  % s . \n ", $stmt->error);
            return false;
        }
    }

    

}
