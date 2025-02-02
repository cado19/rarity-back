<?php
// this class is a model for vehicle details.
class Fleet
{
    // DB STUFF
    private $con;
    private $table = "vehicle_basics";

    // Vehicle properties
    public $id;
    public $make;
    public $model;
    public $number_plate;
    public $seats;
    public $fuel;
    public $transmission;
    public $daily_rate;
    public $category_id;
    public $category_name;
    public $vehicle_excess;
    public $title; // to be used in getting vehicle make, model, number plate for calendar
    public $created_at;

    // Constructor with DB
    public function __construct($db)
    {
        // when we instantiate a new post we'll pass in the db as a parameter
        $this->con = $db;
    }

    // get vehicles
    public function read()
    {
        $status = "false";
        //create the query
        $query = "SELECT c.name AS category_name, v.id, v.category_id, v.make, v.model, v.number_plate, v.created_at, vp.daily_rate FROM vehicle_basics v INNER JOIN vehicle_categories c ON v.category_id = c.id INNER JOIN vehicle_pricing vp ON v.id = vp.vehicle_id WHERE v.deleted = ?";

        // prepare statement
        $stmt = $this->con->prepare($query);

        //execuute the query
        $stmt->execute([$status]);

        return $stmt;

    }

    // get single vehicle
    public function read_single()
    {
        //create the query
        $query = "SELECT vb.make, vb.model, vb.number_plate, cat.name as category_name, cat.id AS category_id, vb.drive_train, vb.seats, vb.fuel, vb.transmission, vb.image, vp.daily_rate, vp.vehicle_excess, vp.refundable_security_deposit, vp.monthly_target, ve.bluetooth, ve.keyless_entry, ve.reverse_cam, ve.audio_input, ve.gps, ve.android_auto, ve.apple_carplay FROM vehicle_basics vb INNER JOIN vehicle_pricing vp ON vb.id = vp.vehicle_id INNER JOIN vehicle_extras ve ON vb.id = ve.vehicle_id INNER JOIN vehicle_categories cat ON vb.category_id = cat.id WHERE vb.id = ? LIMIT 0,1";

        // prepare statement
        $stmt = $this->con->prepare($query);

        //execuute the query
        $stmt->execute([$this->id]);

        //fetch the array
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        //set the properties
        $this->make           = $row['make'];
        $this->model          = $row['model'];
        $this->number_plate   = $row['number_plate'];
        $this->category_id    = $row['category_id'];
        $this->category_name  = $row['category_name'];
        $this->transmission   = $row['transmission'];
        $this->seats          = $row['seats'];
        $this->drive_train    = $row['drive_train'];
        $this->vehicle_excess = $row['vehicle_excess'];
        $this->daily_rate     = $row['daily_rate'];
        $this->fuel           = $row['fuel'];

        // return $stmt;
    }

    // create vehicle
    public function create()
    {
        // Create the query
        $sql = "INSERT INTO posts (title, body, author, category_id) VALUES (?,?,?,?)";

        // prepare the statement
        $stmt = $this->con->prepare($sql);

        //clean the data
        $this->title       = htmlspecialchars(strip_tags($this->title));
        $this->body        = htmlspecialchars(strip_tags($this->body));
        $this->author      = htmlspecialchars(strip_tags($this->author));
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));

        if ($stmt->execute([$this->title, $this->body, $this->author, $this->category_id])) {
            return true;
        }

        // print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);

        return false;
    }

    // function to retrieve all categories for saving a vehicle
    public function categories()
    {

        try {
            $con->beginTransaction();

            $sql  = "SELECT id, name FROM vehicle_categories ORDER BY id ASC";
            $stmt = $con->prepare($sql);
            $stmt->execute();

            $con->commit();
        } catch (Exception $e) {
            $con->rollback();
        }

        return $stmt;
    }

// function to get a vehicle's category id
    public function category()
    {

        $sql  = "SELECT category_id FROM vehicle_basics WHERE id = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->id]);
        $res = $stmt->fetch();

        // set category_id property of the class
        $this->category_id = $res['category_id'];

        // return $stmt;
    }

    public function get_daily_rate()
    {
        $sql  = "SELECT daily_rate FROM vehicle_pricing WHERE vehicle_id = ? LIMIT 0,1";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->id]);
        $res = $stmt->fetch();

        // set vehicle pricing property of the class
        $this->daily_rate = $res['daily_rate'];
    }

    // get vehicles for new booking
    public function booking_vehicles()
    {
        $status = "false";

        try {
            $this->con->beginTransaction();
            $query = "SELECT id, make, model, number_plate FROM vehicle_basics WHERE deleted = ? AND partner_id IS NULL ORDER BY id DESC";
            $stmt  = $this->con->prepare($query);
            $stmt->execute([$status]);
            $this->con->commit();
        } catch (Exception $e) {
            $this->con->rollback();
        }

        return $stmt;
    }

    public function workplan_vehicles()
    {
        $query = "SELECT id, CONCAT(make, ' ', model, ' ', number_plate) AS title FROM vehicle_basics ORDER BY title DESC";
        $stmt  = $this->con->prepare($query);
        $stmt->execute();

        //fetch the array
        // $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // $this->category_id = row['category_id'];

        return $stmt;
    }

}
