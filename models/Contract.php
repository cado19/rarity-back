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
    public $status;  //status of the contract (signed/unsigned)
    public $c_id;    //customer_id
    public $c_fname; //customer_first_name
    public $c_lname; //customer_last_name
    public $c_phone_no;
    public $c_email;
    public $c_residential_address;
    public $d_id;
    public $d_fname;    //driver_first_name
    public $d_lname;    //driver_last_name
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
        $status = "signed";
        $sql    = "UPDATE contracts SET signature = ?, status = ? WHERE id = ?";
        $stmt   = $this->con->prepare($sql);
        if ($stmt->execute([$this->signature, $status, $this->id])) {
            return true;
        } else {
            // print error if something goes wrong
            printf("Error :  % s . \n ", $stmt->error);
            return false;
        }
    }

    public function set_cdw_status()
    {
        $status = "true";
        $sql    = "UPDATE contracts SET cdw = ? WHERE id = ?";
        $stmt   = $this->con->prepare($sql);
        if ($stmt->execute([$status, $this->id])) {
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

    public function read_single()
    {
        $sql = "SELECT
            c.first_name AS c_fname,
            c.last_name AS c_lname,
            c.id_no AS c_id_no,
            c.phone_no AS c_phone_no,
            c.email AS c_email,
            c.residential_address,
            COALESCE(a.name, CONCAT(d.first_name, ' ', d.last_name)) AS driver_name,
            COALESCE(a.phone_no, d.phone_no) AS driver_phone,
            vb.make,
            vb.model,
            vb.number_plate,
            vp.daily_rate,
            vp.vehicle_excess,
            vp.cdw_vehicle_excess,
            bk.start_date,
            bk.end_date,
            bk.start_time,
            bk.end_time,
            bk.custom_rate,
            bk.total,
            ct.cdw,
            ct.signature,
            ct.created_at
        FROM bookings bk
        JOIN customer_details c ON bk.customer_id = c.id
        LEFT JOIN drivers d ON bk.driver_id = d.id
        LEFT JOIN accounts a ON bk.account_driver_id = a.id
        JOIN vehicle_basics vb ON bk.vehicle_id = vb.id
        JOIN contracts ct ON ct.booking_id = bk.id
        JOIN vehicle_pricing vp ON bk.vehicle_id = vp.vehicle_id
        WHERE bk.id = ?;
        ";

        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->booking_id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

}
