<?php
// this class is a model for bookings.
class Booking
{
    // DB STUFF
    private $con;
    private $table = "bookings";

    // Booking properties
    public $id;
    public $booking_no;
    public $c_id;    //customer_id
    public $c_fname; //customer_first_name
    public $c_lname; //customer_last_name
    public $d_id;
    public $d_fname; //driver_first_name
    public $d_lname; //driver_last_name
    public $start_date;
    public $end_date;
    public $start_time;
    public $end_time;
    public $status;
    public $ct_status; // contract signature status
    public $daily_rate;
    public $custom_rate;
    public $total;
    public $make;
    public $model;
    public $vehicle_id;
    public $number_plate;
    public $account_id; // user id of the person who created the booking
    public $group;      // this text is used for the gantt chart in the front end
    public $title;
    public $agent;
    public $in_capital;
    public $out_capital;
    public $driver_fee;
    public $fuel;
    public $created_at;

    // Constructor with DB
    public function __construct($db)
    {
        // when we instantiate a new post we'll pass in the db as a parameter
        $this->con = $db;
    }

    // get single booking
    public function read_single()
    {
        $sql  = "SELECT a.name AS agent, c.id AS customer_id, c.first_name AS customer_first_name, c.last_name AS customer_last_name, v.id AS vehicle_id, v.model, v.make, v.number_plate, v.drive_train, cat.name AS category, v.seats, vp.daily_rate, d.id AS d_id, d.first_name AS driver_first_name, d.last_name AS driver_last_name, b.start_date, b.end_date, b.start_time, b.end_time, b.total, b.driver_fee, b.in_capital, b.out_capital, b.status, b.fuel, b.booking_no, b.custom_rate, ct.status AS signature_status FROM customer_details c INNER JOIN bookings b ON c.id = b.customer_id INNER JOIN accounts a ON b.account_id = a.id INNER JOIN vehicle_basics v ON b.vehicle_id = v.id INNER JOIN vehicle_pricing vp ON b.vehicle_id = vp.vehicle_id INNER JOIN contracts ct ON b.id = ct.booking_id INNER JOIN vehicle_categories cat ON v.category_id = cat.id INNER JOIN drivers d ON b.driver_id = d.id WHERE b.id = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->id]);

        //fetch the array
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->booking_no   = $row['booking_no'];
        $this->c_id         = $row['customer_id'];
        $this->c_fname      = $row['customer_first_name'];
        $this->c_lname      = $row['customer_last_name'];
        $this->d_id         = $row['d_id'];
        $this->d_fname      = $row['driver_first_name'];
        $this->d_lname      = $row['driver_last_name'];
        $this->start_date   = $row['start_date'];
        $this->end_date     = $row['end_date'];
        $this->start_time   = $row['start_time'];
        $this->end_time     = $row['end_time'];
        $this->driver_fee   = $row['driver_fee'];
        $this->in_capital   = $row['in_capital'];
        $this->out_capital  = $row['out_capital'];
        $this->status       = $row['status'];
        $this->custom_rate  = $row['custom_rate'];
        $this->daily_rate   = $row['daily_rate'];
        $this->fuel         = $row['fuel'];
        $this->total        = $row['total'];
        $this->vehicle_id   = $row['vehicle_id'];
        $this->make         = $row['make'];
        $this->model        = $row['model'];
        $this->number_plate = $row['number_plate'];
        $this->ct_status    = $row['signature_status'];
        $this->agent        = $row['agent'];
    }

    // get all bookings
    public function read()
    {
        $status = "false";

        try {
            $this->con->beginTransaction();

            //create the query
            $query = "SELECT b.id, b.booking_no, c.first_name AS c_fname, c.last_name AS c_lname, v.model, v.make, v.number_plate, b.start_date, b.end_date, b.status FROM customer_details c INNER JOIN bookings b ON c.id = b.customer_id INNER JOIN vehicle_basics v ON b.vehicle_id = v.id WHERE b.deleted = ? ORDER BY b.created_at DESC";
            $stmt  = $this->con->prepare($query);
            $stmt->execute([$status]);
            $this->con->commit();
        } catch (Exception $e) {
            $this->con->rollback();
        }

        return $stmt;
    }

    // get upcoming bookings
    public function read_upcoming()
    {
        $status = "upcoming";

        try {
            $this->con->beginTransaction();

            //create the query
            $query = "SELECT b.id, b.booking_no, c.first_name AS c_fname, c.last_name AS c_lname, v.model, v.make, v.number_plate, b.start_date, b.end_date, b.status FROM customer_details c INNER JOIN bookings b ON c.id = b.customer_id INNER JOIN vehicle_basics v ON b.vehicle_id = v.id WHERE b.status = ? ORDER BY b.created_at DESC";
            $stmt  = $this->con->prepare($query);
            $stmt->execute([$status]);
            $this->con->commit();
        } catch (Exception $e) {
            $this->con->rollback();
        }

        return $stmt;
    }

    // get active bookings
    public function read_active()
    {
        $status = "active";

        try {
            $this->con->beginTransaction();

            //create the query
            $query = "SELECT b.id, b.booking_no, c.first_name AS c_fname, c.last_name AS c_lname, v.model, v.make, v.number_plate, b.start_date, b.end_date, b.status FROM customer_details c INNER JOIN bookings b ON c.id = b.customer_id INNER JOIN vehicle_basics v ON b.vehicle_id = v.id WHERE b.status = ? ORDER BY b.created_at DESC";
            $stmt  = $this->con->prepare($query);
            $stmt->execute([$status]);
            $this->con->commit();
        } catch (Exception $e) {
            $this->con->rollback();
        }

        return $stmt;
    }

    // get completed bookings
    public function read_completed()
    {
        $status = "complete";

        try {
            $this->con->beginTransaction();

            //create the query
            $query = "SELECT b.id, b.booking_no, c.first_name AS c_fname, c.last_name AS c_lname, v.model, v.make, v.number_plate, b.start_date, b.end_date, b.status FROM customer_details c INNER JOIN bookings b ON c.id = b.customer_id INNER JOIN vehicle_basics v ON b.vehicle_id = v.id WHERE b.status = ? ORDER BY b.created_at DESC";
            $stmt  = $this->con->prepare($query);
            $stmt->execute([$status]);
            $this->con->commit();
        } catch (Exception $e) {
            $this->con->rollback();
        }

        return $stmt;
    }

    // get cancelled bookings
    public function read_cancelled()
    {
        $status = "cancelled";

        try {
            $this->con->beginTransaction();

            //create the query
            $query = "SELECT b.id, b.booking_no, c.first_name AS c_fname, c.last_name AS c_lname, v.model, v.make, v.number_plate, b.start_date, b.end_date, b.status FROM customer_details c INNER JOIN bookings b ON c.id = b.customer_id INNER JOIN vehicle_basics v ON b.vehicle_id = v.id WHERE b.status = ? ORDER BY b.created_at DESC";
            $stmt  = $this->con->prepare($query);
            $stmt->execute([$status]);
            $this->con->commit();
        } catch (Exception $e) {
            $this->con->rollback();
        }

        return $stmt;
    }

    // get agent bookings for the current month
    public function read_agent()
    {
        $status = "cancelled";

        try {
            $this->con->beginTransaction();

            //create the query
            $query = "SELECT b.id, b.booking_no, c.first_name AS c_fname, c.last_name AS c_lname, v.model, v.make, v.number_plate, v.category_id, b.start_date, b.end_date, b.status FROM customer_details c INNER JOIN bookings b ON c.id = b.customer_id INNER JOIN vehicle_basics v ON b.vehicle_id = v.id WHERE b.account_id = ? AND MONTH(b.start_date) = MONTH(CURDATE()) AND YEAR(b.start_date) = YEAR(CURDATE()) ORDER BY b.created_at DESC";
            $stmt  = $this->con->prepare($query);
            $stmt->execute([$this->account_id]);
            $this->con->commit();
        } catch (Exception $e) {
            $this->con->rollback();
        }

        return $stmt;
    }

    // get completed agent bookings for the current month
    public function read_agent_complete()
    {
        $status = "complete";

        try {
            $this->con->beginTransaction();

            //create the query
            $query = "SELECT b.id, b.booking_no, c.first_name AS c_fname, c.last_name AS c_lname, v.model, v.make, v.number_plate, v.category_id, b.start_date, b.end_date, b.status, b.total FROM customer_details c INNER JOIN bookings b ON c.id = b.customer_id INNER JOIN vehicle_basics v ON b.vehicle_id = v.id WHERE b.account_id = ? AND b.status = ? AND MONTH(b.start_date) = MONTH(CURDATE()) AND YEAR(b.start_date) = YEAR(CURDATE()) ORDER BY b.created_at DESC";
            $stmt  = $this->con->prepare($query);
            $stmt->execute([$this->account_id, $status]);
            $this->con->commit();
        } catch (Exception $e) {
            $this->con->rollback();
        }

        return $stmt;
    }

    // get upcoming driver bookings
    public function upcoming_driver_bookings()
    {
        $status = "upcoming";

        $sql  = "SELECT b.booking_no, b.start_date, b.end_date, v.make, v.model, v.number_plate FROM bookings b INNER JOIN vehicle_basics v ON b.vehicle_id = v.id WHERE b.driver_id = ? AND b.status = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->d_id, $status]);
        return $stmt;
    }

    public function create_booking()
    {
        $status = "upcoming";
        $sql    = "INSERT INTO bookings (customer_id, vehicle_id, driver_id, start_date, end_date, start_time, end_time, custom_rate, total, account_id, status, in_capital, out_capital, driver_fee) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $stmt   = $this->con->prepare($sql);
        if ($stmt->execute([$this->c_id, $this->vehicle_id, $this->d_id, $this->start_date, $this->end_date, $this->start_time, $this->end_time, $this->custom_rate, $this->total, $this->account_id, $status, $this->in_capital, $this->out_capital, $this->driver_fee])) {
            $this->id = $this->con->lastInsertId();
            return true;
        } else {
            // print error if something goes wrong
            printf("Error :  % s . \n ", $stmt->error);
            return false;
        }

    }

    public function create_custom_booking()
    {
        $status = "upcoming";
        $sql    = "INSERT INTO bookings (customer_id, vehicle_id, driver_id, start_date, end_date, start_time, end_time, custom_rate, total, account_id, status, in_capital, out_capital, driver_fee) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $stmt   = $this->con->prepare($sql);
        if ($stmt->execute([$this->c_id, $this->vehicle_id, $this->d_id, $this->start_date, $this->end_date, $this->start_time, $this->end_time, $this->custom_rate, $this->total, $this->account_id, $status, $this->in_capital, $this->out_capital, $this->driver_fee])) {
            $this->id = $this->con->lastInsertId();
            return true;
        } else {
            // print error if something goes wrong
            printf("Error :  % s . \n ", $stmt->error);
            return false;
        }

    }

    public function save_booking_number()
    {
        $sql  = "UPDATE bookings SET booking_no = ? WHERE id = ?";
        $stmt = $this->con->prepare($sql);
        if ($stmt->execute([$this->booking_no, $this->id])) {
            // $this->id = $this->con->lastInsertId();
            return true;
        } else {
            // print error if something goes wrong
            printf("Error :  % s . \n ", $stmt->error);
            return false;
        }
    }

    public function booking_workplan()
    {
        $sql = "SELECT b.id, CONCAT(b.booking_no, ' ', c.first_name, ' ', c.last_name) AS title, b.start_date AS start_time, b.end_date AS end_time, b.vehicle_id AS 'group', b.status FROM bookings b INNER JOIN customer_details c ON b.customer_id = c.id";

        $stmt = $this->con->prepare($sql);
        $stmt->execute();

        return $stmt;
    }

    public function driver_booking_workplan()
    {
        $sql = "SELECT b.id, CONCAT(b.booking_no, ' ', c.first_name, ' ', c.last_name) AS title, b.start_date AS start_time, b.end_date AS end_time, b.driver_id AS 'group', b.status FROM bookings b INNER JOIN customer_details c ON b.customer_id = c.id";

        $stmt = $this->con->prepare($sql);
        $stmt->execute();

        return $stmt;
    }

    public function activate_booking()
    {
        $status = "active";
        $sql    = "UPDATE bookings SET status = ? WHERE id = ?";
        $stmt   = $this->con->prepare($sql);

        if ($stmt->execute([$status, $this->id])) {
            // $this->id = $this->con->lastInsertId();
            return true;
        } else {
            // print error if something goes wrong
            printf("Error :  % s . \n ", $stmt->error);
            return false;
        }
    }

    public function cancel_booking()
    {
        $status = "cancelled";
        $sql    = "UPDATE bookings SET status = ? WHERE id = ?";
        $stmt   = $this->con->prepare($sql);

        if ($stmt->execute([$status, $this->id])) {
            // $this->id = $this->con->lastInsertId();
            return true;
        } else {
            // print error if something goes wrong
            printf("Error :  % s . \n ", $stmt->error);
            return false;
        }
    }

    public function complete_booking()
    {
        $status = "complete";
        $sql    = "UPDATE bookings SET status = ? WHERE id = ?";
        $stmt   = $this->con->prepare($sql);

        if ($stmt->execute([$status, $this->id])) {
            // $this->id = $this->con->lastInsertId();
            return true;
        } else {
            // print error if something goes wrong
            printf("Error :  % s . \n ", $stmt->error);
            return false;
        }
    }

    public function extend_booking()
    {
        $sql  = "UPDATE bookings SET end_date = ?, total = ? WHERE id = ?";
        $stmt = $this->con->prepare($sql);

        if ($stmt->execute([$this->end_date, $this->total, $this->id])) {
            // $this->id = $this->con->lastInsertId();
            return true;
        } else {
            // print error if something goes wrong
            printf("Error :  % s . \n ", $stmt->error);
            return false;
        }
    }

    public function update_fuel()
    {
        $sql  = "UPDATE bookings SET fuel = ? WHERE id = ?";
        $stmt = $this->con->prepare($sql);

        if ($stmt->execute([$this->fuel, $this->id])) {
            // $this->id = $this->con->lastInsertId();
            return true;
        } else {
            // print error if something goes wrong
            printf("Error :  % s . \n ", $stmt->error);
            return false;
        }
    }

    // get start date from db to be used in calculating total when extending a booking
    public function get_start_date()
    {
        $sql  = "SELECT start_date FROM bookings WHERE id = ? LIMIT 0,1";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->id]);

        $row              = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->start_date = $row['start_date'];
    }

    // get custom_rate from db to be used in calculating total when extending a booking
    public function get_custom_rate()
    {
        $sql  = "SELECT custom_rate FROM bookings WHERE id = ? LIMIT 0,1";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->id]);

        $row               = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->custom_rate = $row['custom_rate'];
    }

    // get vehicle_id from db to be used in getting daily rate and calculating total when extending a booking
    public function get_vehicle_id()
    {
        $sql  = "SELECT vehicle_id FROM bookings WHERE id = ? LIMIT 0,1";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->id]);

        $row              = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->vehicle_id = $row['vehicle_id'];
    }

    public function get_voucher_details()
    {
        $sql = "SELECT b.id, b.booking_no, b.custom_rate, b.total, b.start_date, b.start_time, b.end_date, b.end_time, b.created_at, vb.make, vb.model, vb.number_plate, vp.daily_rate, c.first_name, c.last_name FROM bookings b INNER JOIN vehicle_basics vb ON b.vehicle_id = vb.id INNER JOIN customer_details c ON b.customer_id = c.id INNER JOIN vehicle_pricing vp ON b.vehicle_id = vp.vehicle_id WHERE b.id = ?";

        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->id]);
        $row                = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->id           = $row['id'];
        $this->booking_no   = $row['booking_no'];
        $this->custom_rate  = $row['custom_rate'];
        $this->total        = $row['total'];
        $this->start_date   = $row['start_date'];
        $this->start_time   = $row['start_time'];
        $this->end_date     = $row['end_date'];
        $this->end_time     = $row['end_time'];
        $this->created_at   = $row['created_at'];
        $this->make         = $row['make'];
        $this->model        = $row['model'];
        $this->number_plate = $row['number_plate'];
        $this->daily_rate   = $row['daily_rate'];
        $this->c_fname      = $row['first_name'];
        $this->c_lname      = $row['last_name'];
    }

    public function update_booking_details()
    {

        $sql  = "UPDATE bookings SET vehicle_id = ?, customer_id = ?, driver_id = ?, start_date = ?, end_date = ?, start_time = ?, end_time = ?, custom_rate = ?, total = ? WHERE id = ?";
        $stmt = $this->con->prepare($sql);
        if ($stmt->execute([$this->vehicle_id, $this->c_id, $this->d_id, $this->start_date, $this->end_date, $this->start_time, $this->end_time, $this->custom_rate, $this->total, $this->id])) {
            $this->id = $this->con->lastInsertId();
            return true;
        } else {
            // print error if something goes wrong
            printf("Error :  % s . \n ", $stmt->error);
            return false;
        }

    }

    public function custom_booking_update()
    {

        $sql  = "UPDATE bookings SET vehicle_id = ?, customer_id = ?, driver_id = ?, start_date = ?, end_date = ?, start_time = ?, end_time = ?, custom_rate = ?, total = ? WHERE id = ?";
        $stmt = $this->con->prepare($sql);
        if ($stmt->execute([$this->vehicle_id, $this->c_id, $this->d_id, $this->start_date, $this->end_date, $this->start_time, $this->end_time, $this->custom_rate, $this->total, $this->id])) {
            $this->id = $this->con->lastInsertId();
            return true;
        } else {
            // print error if something goes wrong
            printf("Error :  % s . \n ", $stmt->error);
            return false;
        }
    }

    // when an event in the dashboard has been dragged
    public function update_dash_dates()
    {
        $sql  = "UPDATE bookings SET start_date = ?, end_date = ? WHERE id = ?";
        $stmt = $this->con->prepare($sql);
        if ($stmt->execute([$this->start_date, $this->end_date, $this->id])) {
            return true;
        } else {
            return false;
        }

    }

    public function update_dash_vehicle()
    {
        $sql  = "UPDATE bookings SET vehicle_id = ?, start_date = ?, end_date = ?, total = ? WHERE id = ?";
        $stmt = $this->con->prepare($sql);
        if ($stmt->execute([$this->vehicle_id, $this->start_date, $this->end_date, $this->total, $this->id])) {
            return true;
        } else {
            return false;
        }
    }

}
