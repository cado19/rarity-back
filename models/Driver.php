<?php

class Driver
{

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
    public function read()
    {
        $status = "false";
        $sql    = "SELECT id, first_name, last_name, email, id_no, phone_no FROM drivers WHERE deleted = ?";
        // prepare the statement
        $stmt = $this->con->prepare($sql);

        $stmt->execute([$status]);

        return $stmt;
    }

    // get booking drivers
    public function booking_drivers()
    {
        $status = "false";

        $sql = "SELECT id, first_name, last_name FROM drivers WHERE deleted =  ? ORDER BY id DESC";

        // prepare the statement
        $stmt = $this->con->prepare($sql);

        $stmt->execute([$status]);

        return $stmt;
    }

    public function read_single()
    {
        $sql  = "SELECT * FROM drivers WHERE id = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->first_name    = $row['first_name'];
        $this->last_name     = $row['last_name'];
        $this->email         = $row['email'];
        $this->dl_no         = $row['dl_no'];
        $this->id_no         = $row['id_no'];
        $this->phone_no      = $row['phone_no'];
        $this->dl_expiry     = $row['dl_expiration_date'];
        $this->date_of_birth = $row['date_of_birth'];
    }

    public function check_unique_email()
    {
        $sql = "SELECT id FROM drivers WHERE email LIKE ?";

        // prepare the statement
        $stmt = $this->con->prepare($sql);

        $stmt->execute([$this->email]);

        return $stmt;
    }

    public function create()
    {
        $sql = "INSERT INTO drivers
                (first_name, last_name, email, id_no, dl_no, phone_no, date_of_birth, password)
                VALUES (?,?,?,?,?,?,?)";

        $stmt = $this->con->prepare($sql);
        if ($stmt->execute([this->first_name, this->last_name, this->email, this->id_no, this->dl_no, this->phone_no, this->date_of_birth])) {
            $this->id = $this->con->lastInsertId();
            return true;
        } else {
            // print error if something goes wrong
            printf("Error :  % s . \n ", $stmt->error);
            return false;
        }

    }
}
