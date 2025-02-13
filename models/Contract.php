<?php
class Contract
{
    // DB STUFF
    private $con;
    private $table = "contracts";

    // contract properties
    public $id;
    public $booking_id;
    public $booking_no;
    public $status; //status of the contract (signed/unsigned)
    public $c_id;    //customer_id
    public $c_fname; //customer_first_name
    public $c_lname; //customer_last_name
    public $c_phone_no;
    public $c_email;
    public $c_residential_address;
    public $d_id;
    public $d_fname; //driver_first_name
    public $d_lname; //driver_last_name
    public $d_phone_no; //driver_last_name
    public $start_date;
    public $end_date;
    public $start_time;
    public $end_time;
    public $ct_status; // contract signature status
    public $vehicle_id;
    public $make;
    public $model;
    public $number_plate;
    public $vehicle_excess;
    public $daily_rate;
    public $custom_rate;
    public $total;
    public $group; // this text is used for the gantt chart in the front end
    public $signature;
    public $created_at;

    // Constructor with DB
    public function __construct($db)
    {
        // when we instantiate a new post we'll pass in the db as a parameter
        $this->con = $db;
    }

    public function create()
    {
        $sql  = "INSERT INTO contracts (booking_id) VALUES (?)";
        $stmt = $this->con->prepare($sql);
        if ($stmt->execute([$this->booking_id])) {
            return true;
        } else {
            // print error if something goes wrong
            printf("Error :  % s . \n ", $stmt->error);
            return false; 
        }

    }

    public function sign_contract()
    {
        $sql  = "UPDATE contracts SET signature = ? WHERE id = ?";
        $stmt = $this->con->prepare($sql);
        if ($stmt->execute([$this->signature, $this->id])) {
            return true;
        } else {
            // print error if something goes wrong
            printf("Error :  % s . \n ", $stmt->error);
            return false; 
        }
    }

    // get id of the contract that is to be signed
    public function contract_to_sign()
    {
        $sql  = "SELECT id from contracts WHERE booking_id = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->booking_id]);
        $row      = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->id = $row['id'];

    }

    public function read_single(){
        $sql = "SELECT c.first_name AS c_fname, c.last_name AS c_lname, c.id_no AS c_id_no, c.phone_no AS c_phone_no, c.email AS c_email, c.residential_address, d.first_name AS d_fname, d.last_name AS d_lname, d.id_no AS d_id, d.phone_no AS d_phone_no, vb.make, vb.model, vb.number_plate, vp.daily_rate, vp.vehicle_excess,bk.start_date, bk.end_date, bk.start_time, bk.end_time, bk.custom_rate, bk.total, ct.signature, ct.created_at FROM bookings bk INNER JOIN customer_details c ON bk.customer_id = c.id INNER JOIN drivers d ON bk.driver_id = d.id INNER JOIN vehicle_basics vb ON bk.vehicle_id = vb.id INNER JOIN contracts ct ON ct.booking_id = bk.id INNER JOIN vehicle_pricing vp ON bk.vehicle_id = vp.vehicle_id WHERE bk.id = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->booking_id]);

        //fetch the array
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // $this->booking_no   = $row['booking_no'];
        $this->c_id         = $row['c_id_no'];
        $this->c_fname      = $row['c_fname'];
        $this->c_lname      = $row['c_lname'];
        $this->d_id         = $row['d_id'];
        $this->d_fname      = $row['d_fname'];
        $this->d_lname      = $row['d_lname'];
        $this->d_phone_no   = $row['d_phone_no'];
        $this->start_date   = $row['start_date'];
        $this->end_date     = $row['end_date'];
        $this->start_time   = $row['start_time'];
        $this->end_time     = $row['end_time'];
        $this->c_residential_address       = $row['residential_address'];
        $this->make         = $row['make'];
        $this->model        = $row['model'];
        $this->number_plate = $row['number_plate'];
        $this->vehicle_excess = $row['vehicle_excess'];
        $this->custom_rate  = $row['custom_rate'];
        $this->daily_rate   = $row['daily_rate'];
        $this->total        = $row['total'];
        $this->signature    = $row['signature'];
        $this->created_at    = $row['created_at'];
    }

}
