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
    public $colour;
    public $category_name;
    public $vehicle_excess;
    public $issue_id;
    public $issue_title;
    public $resolution_cost;
    public $resolution_date;
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
        $query = "SELECT vb.make, vb.model, vb.number_plate, cat.name as category_name, cat.id AS category_id, vb.drive_train, vb.seats, vb.fuel, vb.transmission, vb.image, vp.daily_rate, vp.vehicle_excess, vp.refundable_security_deposit, vp.monthly_target FROM vehicle_basics vb INNER JOIN vehicle_pricing vp ON vb.id = vp.vehicle_id INNER JOIN vehicle_categories cat ON vb.category_id = cat.id WHERE vb.id = ? LIMIT 0,1";

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
        $sql = "INSERT INTO vehicle_basics (make, model, number_plate, category_id, fuel, seats, transmission, drive_train) VALUES (?,?,?,?,?,?,?,?)";

        // prepare the statement
        $stmt = $this->con->prepare($sql);

        //clean the data
        // $this->title       = htmlspecialchars(strip_tags($this->title));
        // $this->body        = htmlspecialchars(strip_tags($this->body));
        // $this->author      = htmlspecialchars(strip_tags($this->author));
        // $this->category_id = htmlspecialchars(strip_tags($this->category_id));

        if ($stmt->execute([$this->make, $this->model, $this->number_plate, $this->category_id, $this->fuel, $this->seats, $this->transmission, $this->drive_train])) {
            $this->id = $this->con->lastInsertId();
            return true;
        } else {

            // print error if something goes wrong
            printf("Error: %s.\n", $stmt->error);
            return false;
        }

    }

    public function create_pricing()
    {
        $sql = "INSERT INTO vehicle_pricing (vehicle_id, daily_rate, vehicle_excess) VALUES (?,?,?)";

        // prepare the statement
        $stmt = $this->con->prepare($sql);

        if ($stmt->execute([$this->id, $this->daily_rate, $this->vehicle_excess])) {
            return true;
        } else {
            // print error if something goes wrong
            printf("Error: %s.\n", $stmt->error);
            return false;
        }
    }

    public function check_unique_number_plate()
    {
        $sql = "SELECT id FROM vehicle_basics WHERE number_plate LIKE ?";
        // prepare the statement
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->number_plate]);
        return $stmt;

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
        $query = "SELECT v.id, CONCAT(make, ' ', model, ' ', number_plate) AS title, cat.name AS category FROM vehicle_basics v INNER JOIN vehicle_categories cat ON v.category_id = cat.id ORDER BY title DESC";
        $stmt  = $this->con->prepare($query);
        $stmt->execute();

        //fetch the array
        // $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // $this->category_id = row['category_id'];

        return $stmt;
    }

    // VEHICLE ISSUES FUNCTIONS
    public function read_issues()
    {
        $sql  = "SELECT vb.make, vb.model, vb.number_plate, vi.title, vi.resolution_cost, vi.status FROM vehicle_issues vi INNER JOIN vehicle_basics vb ON vi.vehicle_id = vb.id ORDER BY created_at DESC";
        $stmt = $this->con->prepare($sql);
        $stmt->execute();

        return $stmt;
    }

    public function read_issue()
    {
        $sql  = "SELECT vb.make, vb.model, vb.number_plate, vi.title, vi.resolution_cost, vi.status FROM vehicle_issues vi INNER JOIN vehicle_basics vb ON vi.vehicle_id = vb.id WHERE vi.id = ? ORDER BY created_at DESC";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->issue_id]);
    }

    public function update_rate()
    {
        $sql  = "UPDATE vehicle_pricing SET daily_rate = ? WHERE vehicle_id = ?";
        $stmt = $this->con->prepare($sql);
        if ($stmt->execute([$this->daily_rate, $this->id])) {
            return true;
        } else {
            // print error if something goes wrong
            printf("Error: %s.\n", $stmt->error);
            return false;
        }

    }
    // count the total number of vehicles
    public function vehicle_count()
    {
        $sql  = "SELECT id FROM vehicle_basics";
        $stmt = $this->con->prepare($sql);
        $stmt->execute();
        return $stmt;
    }

    //count the total number of vehicles actively out there (active booking)
    public function active_vehicles()
    {
        $status = "active";
        $sql    = "SELECT vehicle_id FROM bookings WHERE status = ?";
        $stmt   = $this->con->prepare($sql);
        $stmt->execute([$status]);
        return $stmt;

    }
    //count the total number of vehicles reserved  (upcoming booking)
    public function reserved_vehicles()
    {
        $status = "upcoming";
        $sql    = "SELECT vehicle_id FROM bookings WHERE status = ?";
        $stmt   = $this->con->prepare($sql);
        $stmt->execute([$status]);
        return $stmt;

    }

    //get bookings ending today
    public function due_out_count()
    {
        $sql  = "SELECT id FROM bookings WHERE DATE(end_date) = CURRENT_DATE";
        $stmt = $this->con->prepare($sql);
        $stmt->execute();
        return $stmt;
    }

}
