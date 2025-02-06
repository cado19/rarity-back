<?php
class Contract
{
    // DB STUFF
    private $con;
    private $table = "contracts";

    // contract properties
    public $id;
    public $booking_id;
    public $status; //status of the contract (signed/unsigned)

    // Constructor with DB
    public function __construct($db)
    {
        // when we instantiate a new post we'll pass in the db as a parameter
        $this->con = $db;
    }

    public function create()
    {
        $sql  = "INSERT INTO contracts (booking_id) VALUES ?";
        $stmt = $this->con->prepare($sql);
        if ($stmt->execute[$this->booking_id]) {
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

}
