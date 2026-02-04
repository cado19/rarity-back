<?php
// this class is a model for vehicle details.
class Fleet
{
    // DB STUFF
    private $con;
    private $table            = "vehicle_basics";
    private $pricing_table    = "vehicle_pricing";
    private $categories_table = "vehicle_categories";

    // Vehicle properties
    public $id;
    public $make;
    public $model;
    public $number_plate;
    public $seats;
    public $fuel;
    public $transmission;
    public $category_id;
    public $colour;
    public $drive_train;
    public $capacity;
    public $cylinders;
    public $horsepower;
    public $economy_city;
    public $economy_highway;
    public $acceleration;
    public $aspiration;

    //pricing properties
    public $pricing_id;
    public $daily_rate;
    public $vehicle_excess;
    public $refundable_security_deposit;
    public $monthly_target;
    public $cdw_rate;

    // issue properties
    public $issue_id;
    public $issue_title;
    public $issue_description;
    public $resolution_cost;
    public $resolution_date;

    // extras properties
    public $extras_id;
    public $bluetooth;
    public $keyless_entry;
    public $gps;
    public $reverse_cam;
    public $audio_input;
    public $android_auto;
    public $apple_carplay;
    public $sunroof;

    public $title; // to be used in getting vehicle make, model, number plate for calendar
    public $url;   // for getting and saving an image url
    public $deleted;
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

    public function read_catalog()
    {
        $status = "false";

        //create the query
        $query = "SELECT
          vb.id,
          vb.make,
          vb.model,
          vb.number_plate,
          cat.name AS category_name,
          cat.id AS category_id,
          vb.drive_train,
          vb.seats,
          vb.fuel,
          vb.transmission,
          vb.image,
          vb.deleted,
          vp.daily_rate,
          vp.vehicle_excess,
          vp.refundable_security_deposit,
          vp.monthly_target,
          vi.url AS earliest_image_url
        FROM vehicle_basics vb
        INNER JOIN vehicle_pricing vp ON vb.id = vp.vehicle_id
        INNER JOIN vehicle_categories cat ON vb.category_id = cat.id
        LEFT JOIN (
          SELECT vehicle_id, url
          FROM vehicle_images
          WHERE (vehicle_id, created_at) IN (
            SELECT vehicle_id, MIN(created_at)
            FROM vehicle_images
            GROUP BY vehicle_id
          )
        ) vi ON vb.id = vi.vehicle_id WHERE vb.deleted = ?";

        $stmt = $this->con->prepare($query);

        //execuute the query
        $stmt->execute([$status]);

        return $stmt;
    }

    // get single vehicle
    public function read_single()
    {
        //create the query
        $query = "SELECT
          vb.make,
          vb.model,
          vb.number_plate,
          cat.name AS category_name,
          cat.id AS category_id,
          vb.drive_train,
          vb.seats,
          vb.fuel,
          vb.transmission,
          vb.image,
          vb.deleted,
          vp.daily_rate,
          vp.vehicle_excess,
          vp.refundable_security_deposit,
          vp.monthly_target,
          vi.url AS earliest_image_url
        FROM vehicle_basics vb
        INNER JOIN vehicle_pricing vp ON vb.id = vp.vehicle_id
        INNER JOIN vehicle_categories cat ON vb.category_id = cat.id
        LEFT JOIN (
          SELECT vehicle_id, url
          FROM vehicle_images
          WHERE (vehicle_id, created_at) IN (
            SELECT vehicle_id, MIN(created_at)
            FROM vehicle_images
            GROUP BY vehicle_id
          )
        ) vi ON vb.id = vi.vehicle_id
        WHERE vb.id = ?
        LIMIT 1";

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
        $this->url            = $row['earliest_image_url'];
        $this->deleted        = $row['deleted'];

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

    // function to save the pricing details of a vehicle
    public function create_pricing()
    {
        try {
            $sql = "INSERT INTO vehicle_pricing (vehicle_id, daily_rate, vehicle_excess, refundable_security_deposit, cdw_rate, monthly_target) VALUES (?,?,?,?,?,?)";
            // prepare the statement
            $stmt = $this->con->prepare($sql);

            $success = $stmt->execute([
                $this->id,
                $this->daily_rate,
                $this->vehicle_excess,
                $this->refundable_security_deposit,
                $this->cdw_rate,
                $this->monthly_target,
            ]);

            return $success;
        } catch (PDOException $e) {
            // Log or bubble up the SQL error
            throw new Exception("SQL Error in update_pricing: " . $e->getMessage());
        }
    }

    // function to update pricing details of a vehicle
    public function update_pricing()
    {
        try {
            $sql = "UPDATE vehicle_pricing
                   SET daily_rate = ?,
                       vehicle_excess = ?,
                       refundable_security_deposit = ?,
                       cdw_rate = ?,
                       monthly_target = ?
                 WHERE vehicle_id = ?";

            $stmt = $this->con->prepare($sql);

            $success = $stmt->execute([
                $this->daily_rate,
                $this->vehicle_excess,
                $this->refundable_security_deposit,
                $this->cdw_rate,
                $this->monthly_target,
                $this->id,
            ]);

            return $success;

        } catch (PDOException $e) {
            // Log or bubble up the SQL error
            throw new Exception("SQL Error in update_pricing: " . $e->getMessage());
        }
    }
    // function to get save the details of a vehicle
    public function create_extras()
    {
        $sql = "INSERT INTO vehicle_pricing (vehicle_id) VALUES (?)";

        // prepare the statement
        $stmt = $this->con->prepare($sql);

        if ($stmt->execute([$this->id])) {
            return true;
        } else {
            // print error if something goes wrong
            printf("Error: %s.\n", $stmt->error);
            return false;
        }
    }

    // function to get save extras of a vehicle
    public function save_extras()
    {
        $sql = "UPDATE vehicle_extras SET bluetooth = ?, keyless_entry = ?, reverse_cam = ?, audio_input = ?, gps = ?, android_auto = ?, apple_carplay = ?, sunroof = ? WHERE vehicle_id = ?";

        // prepare the statement
        $stmt = $this->con->prepare($sql);

        if ($stmt->execute([$this->bluetooth, $this->keyless_entry, $this->reverse_cam, $this->audio_input, $this->gps, $this->android_auto, $this->apple_carplay, $this->sunroof, $this->id])) {
            return true;
        } else {
            // print error if something goes wrong
            printf("Error: %s.\n", $stmt->error);
            return false;
        }

    }

    // function to get save an image of a vehicle
    public function save_image()
    {
        $sql = "INSERT INTO vehicle_images (url, vehicle_id) VALUES (?,?)";

        // prepare the statement
        $stmt = $this->con->prepare($sql);

        if ($stmt->execute([$this->url, $this->id])) {
            return true;
        } else {
            // print error if something goes wrong
            printf("Error: %s.\n", $stmt->error);
            return false;
        }

    }

    // function to update the basic details of vehicle
    public function update_base()
    {
        $query = "UPDATE vehicle_basics SET make = ?, model = ?, number_plate = ?, seats = ?, fuel = ?, transmission = ?, category_id = ?, colour = ?, drive_train = ?, capacity = ?, cylinders = ?, economy_city = ?, economy_highway = ?, acceleration = ?, aspiration = ?, horsepower = ? WHERE id = ?";

        // prepare the statement
        $stmt = $this->con->prepare($query);

        if ($stmt->execute([$this->make, $this->model, $this->number_plate, $this->seats, $this->fuel, $this->transmission, $this->category_id, $this->colour, $this->drive_train, $this->capacity, $this->cylinders, $this->economy_city, $this->economy_highway, $this->acceleration, $this->aspiration, $this->horsepower, $this->id])) {
            return true;
        } else {
            // print error if something goes wrong
            printf("Error: %s.\n", $stmt->error);
            return false;
        }
    }

    // function to get the ids of a vehicle with a numbler plate like the onw given.
    public function check_unique_number_plate()
    {
        $sql = "SELECT id FROM vehicle_basics WHERE number_plate LIKE ?";
        // prepare the statement
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->number_plate]);
        return $stmt;

    }

    // function to get the images of a vehicle
    public function get_vehicle_images()
    {
        $query = "SELECT id, url FROM vehicle_images WHERE vehicle_id = ? ORDER BY created_at DESC";
        $stmt  = $this->con->prepare($query);
        $stmt->execute([$this->id]);
        return $stmt;
    }
    // function to get the basics of a vehicle based on its id
    public function get_vehicle_base()
    {
        try {
            $query = "SELECT * FROM vehicle_basics WHERE id = ?";
            $stmt  = $this->con->prepare($query);
            $stmt->execute([$this->id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (! $row) {
                // No extras found for this vehicle
                return null;
            }

            // Dynamically assign extras
            $fields = [
                'make',
                'model',
                'number_plate',
                'seats',
                'fuel',
                'transmission',
                'category_id',
                'colour',
                'drive_train',
                'capacity',
                'cylinders',
                'economy_city',
                'economy_highway',
                'acceleration',
                'aspiration',
            ];

            foreach ($fields as $field) {
                $this->$field = $row[$field];
            }

            return $row;
        } catch (PDOException $e) {
            // Bubble up SQL error so the endpoint can handle it
            throw new Exception("SQL Error in get_vehicle_extras: " . $e->getMessage());
        }

    }

    // function to get pricing details of a vehicle
    public function get_vehicle_pricing()
    {
        try {
            $query = "SELECT id, daily_rate, vehicle_excess, refundable_security_deposit, cdw_rate, monthly_target FROM vehicle_pricing WHERE vehicle_id = ?";
            $stmt  = $this->con->prepare($query);
            $stmt->execute([$this->id]);

            //fetch the array
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (! $row) {
                // No extras found for this vehicle
                return null;
            }

            // Keep vehicle id intact, store extras id separately
            $this->pricing_id = $row['id'];

            // Dynamically assign extras
            $fields = [
                'daily_rate',
                'vehicle_excess',
                'refundable_security_deposit',
                'cdw_rate',
                'monthly_target',
            ];

            foreach ($fields as $field) {
                $this->$field = $row[$field];
            }

            return $row;

        } catch (PDOException $e) {
            // Bubble up SQL error so the endpoint can handle it
            throw new Exception("SQL Error in get_vehicle_extras: " . $e->getMessage());
        }
    }

    // function to get the extra details of a vehicle
    public function get_vehicle_extras()
    {
        try {
            $query = "SELECT id, bluetooth, keyless_entry, reverse_cam, audio_input, gps, apple_carplay, android_auto, sunroof FROM vehicle_extras WHERE vehicle_id = ?";
            $stmt  = $this->con->prepare($query);
            $stmt->execute([$this->id]);

            //fetch the array
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (! $row) {
                // No extras found for this vehicle
                return null;
            }

            // Keep vehicle id intact, store extras id separately
            $this->extras_id = $row['id'];

            // Dynamically assign extras
            $fields = [
                'bluetooth',
                'keyless_entry',
                'reverse_cam',
                'audio_input',
                'gps',
                'apple_carplay',
                'android_auto',
                'sunroof',
            ];

            foreach ($fields as $field) {
                $this->$field = $row[$field];
            }

            return $row;

        } catch (PDOException $e) {
            // Bubble up SQL error so the endpoint can handle it
            throw new Exception("SQL Error in get_vehicle_extras: " . $e->getMessage());

        }

    }

    // function to retrieve all categories for saving a vehicle
    public function categories()
    {
        $sql  = "SELECT id, name FROM " . $this->categories_table . " ORDER BY id ASC";
        $stmt = $this->con->prepare($sql);
        $stmt->execute();
        return $stmt;
    }

    // function to get a vehicle's category id based on the vehicle's id
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

    // function to get the daily rate of a vehicle based on its id.
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
        $sql  = "SELECT vb.make, vb.model, vb.number_plate, vi.id, vi.title, vi.resolution_cost, vi.status, vi.created_at FROM vehicle_issues vi INNER JOIN vehicle_basics vb ON vi.vehicle_id = vb.id ORDER BY created_at DESC";
        $stmt = $this->con->prepare($sql);
        $stmt->execute();

        return $stmt;
    }

    public function read_issue()
    {
        $sql  = "SELECT vb.make, vb.model, vb.number_plate, vi.id, vi.title, vi.description, vi.resolution_cost, vi.status, vi.created_at FROM vehicle_issues vi INNER JOIN vehicle_basics vb ON vi.vehicle_id = vb.id WHERE vi.id = ? ORDER BY created_at DESC";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->issue_id]);

        //fetch the array
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        //set the properties
        $this->make              = $row['make'];
        $this->model             = $row['model'];
        $this->number_plate      = $row['number_plate'];
        $this->issue_title       = $row['title'];
        $this->issue_description = $row['description'];
        $this->resolution_cost   = $row['resolution_cost'];
        $this->resolution_date   = $row['created_at'];
    }

    public function create_issue()
    {
        $query = "INSERT INTO vehicle_issues (vehicle_id, title, description, resolution_cost) VALUES (?,?,?,?)";
        $stmt  = $this->con->prepare($query);
        if ($stmt->execute([$this->id, $this->title, $this->description, $this->resolution_cost])) {
            $this->issue_id = $this->con->lastInsertId();
            return true;
        } else {
            // print error if something goes wrong
            printf("Error: %s.\n", $stmt->error);
            return false;
        }
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

    // function to delete a vehicle
    public function delete_vehicle()
    {
        $deleted = "true";
        $sql     = "UPDATE vehicle_basics SET deleted = ? WHERE id = ?";
        $stmt    = $this->con->prepare($sql);
        if ($stmt->execute([$deleted, $this->id])) {
            return true;
        } else {
            // print error if something goes wrong
            printf("Error: %s.\n", $stmt->error);
            return false;
        }
    }

    // function to delete a vehicle
    public function restore_vehicle()
    {
        $deleted = "false";
        $sql     = "UPDATE vehicle_basics SET deleted = ? WHERE id = ?";
        $stmt    = $this->con->prepare($sql);
        if ($stmt->execute([$deleted, $this->id])) {
            return true;
        } else {
            // print error if something goes wrong
            printf("Error: %s.\n", $stmt->error);
            return false;
        }
    }

    public function deleted_vehicles()
    {
        $deleted = "true";
        $sql     = "SELECT c.name AS category_name, v.id, v.category_id, v.make, v.model, v.number_plate, v.created_at, vp.daily_rate FROM vehicle_basics v INNER JOIN vehicle_categories c ON v.category_id = c.id INNER JOIN vehicle_pricing vp ON v.id = vp.vehicle_id WHERE v.deleted = ?";
        $stmt    = $this->con->prepare($sql);
        $stmt->execute([$deleted]);
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

    // get all vehicle ids in a given category
    public function vehicles_in_category()
    {
        $sql  = "SELECT id FROM vehicle_basics WHERE category_id = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->category_id]);
        return $stmt;
    }

}
