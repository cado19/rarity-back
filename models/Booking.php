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
    public $c_id;     //customer_id
    public $c_fname;  //customer_first_name
    public $c_lname;  //customer_last_name
    public $phone_no; //customer_last_name
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
    public $mileage; // mileage value either from booking table or from form in complete booking process
    public $total;
    public $subtotal;
    public $cdw_total;
    public $vat;
    public $courtesy;             // courtesy car
    public $claim_no;             // claim no
    public $accident_vehicle_reg; // registration of the vehicle that got an accident for courtesy booking
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
    public $url; // name of the bookings video
    public $override;
    public $duration;
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
        try {
            $sql = "
                SELECT
                    a.name AS agent,
                    c.id AS customer_id,
                    c.first_name AS customer_first_name,
                    c.last_name AS customer_last_name,
                    c.phone_no AS phone_no,
                    v.id AS vehicle_id,
                    v.model,
                    v.make,
                    v.number_plate,
                    v.drive_train,
                    cat.name AS category,
                    v.seats,
                    vp.daily_rate,
                    COALESCE(ad.id, d.id) AS d_id,
                    COALESCE(ad.name, CONCAT(d.first_name, ' ', d.last_name)) AS driver_name,
                    b.start_date,
                    b.end_date,
                    b.start_time,
                    b.end_time,
                    b.courtesy,
                    b.claim_no,
                    b.accident_vehicle_reg,
                    b.total,
                    b.vat,
                    b.subtotal,
                    b.cdw_total,
                    b.driver_fee,
                    b.in_capital,
                    b.out_capital,
                    b.status,
                    b.fuel,
                    b.booking_no,
                    b.custom_rate,
                    b.media_url,
                    b.override,
                    b.duration,
                    b.mileage,
                    ct.status AS signature_status
                FROM bookings b
                INNER JOIN customer_details c ON c.id = b.customer_id
                INNER JOIN accounts a ON b.account_id = a.id
                INNER JOIN vehicle_basics v ON b.vehicle_id = v.id
                INNER JOIN vehicle_pricing vp ON b.vehicle_id = vp.vehicle_id
                INNER JOIN contracts ct ON b.id = ct.booking_id
                INNER JOIN vehicle_categories cat ON v.category_id = cat.id
                LEFT JOIN drivers d ON b.driver_id = d.id
                LEFT JOIN accounts ad ON b.account_driver_id = ad.id
                WHERE b.id = ?
                ";

            $stmt = $this->con->prepare($sql);
            $stmt->execute([$this->id]);

            //fetch the array
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (! $row) {
                // Booking not found
                return null;
            }
            foreach ($row as $key => $value) {
                $this->{$key} = $value;
            }

            return $row ?: null;
        } catch (PDOException $e) {
            // Bubble up SQL error so the endpoint can handle it
            throw new Exception("SQL Error in get_booking: " . $e->getMessage());
        }

    }

    // get all bookings
    public function read()
    {
        $status = "false";

        try {
            $this->con->beginTransaction();

            //create the query
            $query = "SELECT b.id, b.booking_no, c.first_name AS c_fname, c.last_name AS c_lname, v.model, v.make, v.number_plate, b.start_date, b.end_date, b.status, a.id AS agent_id FROM customer_details c INNER JOIN bookings b ON c.id = b.customer_id INNER JOIN vehicle_basics v ON b.vehicle_id = v.id INNER JOIN accounts a ON b.account_id = a.id WHERE b.deleted = ? ORDER BY b.created_at DESC";
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
            $query = "SELECT b.id, b.booking_no, c.id AS customer_id, c.first_name AS c_fname, c.last_name AS c_lname, b.driver_id, v.model, v.make, v.number_plate, b.start_date, b.end_date, b.status, a.id AS agent_id FROM customer_details c INNER JOIN bookings b ON c.id = b.customer_id INNER JOIN vehicle_basics v ON b.vehicle_id = v.id INNER JOIN accounts a ON b.account_id = a.id WHERE b.status = ? ORDER BY b.created_at DESC";
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
            $query = "SELECT b.id, b.booking_no, c.id AS customer_id, c.first_name AS c_fname, c.last_name AS c_lname, b.driver_id, v.model, v.make, v.number_plate, b.start_date, b.end_date, b.status, a.id AS agent_id FROM customer_details c INNER JOIN bookings b ON c.id = b.customer_id INNER JOIN vehicle_basics v ON b.vehicle_id = v.id INNER JOIN accounts a ON b.account_id = a.id WHERE b.status = ? ORDER BY b.created_at DESC";
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
            $query = "SELECT b.id, b.booking_no, c.id AS customer_id, c.first_name AS c_fname, c.last_name AS c_lname, v.model, v.make, v.number_plate, b.start_date, b.end_date, b.status, a.id AS agent_id FROM customer_details c INNER JOIN bookings b ON c.id = b.customer_id INNER JOIN vehicle_basics v ON b.vehicle_id = v.id INNER JOIN accounts a ON b.account_id = a.id WHERE b.status = ? ORDER BY b.created_at DESC";
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
            $query = "SELECT b.id, b.booking_no, c.first_name AS c_fname, c.last_name AS c_lname, v.model, v.make, v.number_plate, b.start_date, b.end_date, b.status, a.id AS agent_id FROM customer_details c INNER JOIN bookings b ON c.id = b.customer_id INNER JOIN vehicle_basics v ON b.vehicle_id = v.id INNER JOIN accounts a ON b.account_id = a.id WHERE b.status = ? ORDER BY b.created_at DESC";
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
    // public function read_agent_complete()
    // {
    //     $status = "complete";

    //     try {
    //         $this->con->beginTransaction();

    //         //create the query
    //         $query = "SELECT b.id, b.booking_no, c.first_name AS c_fname, c.last_name AS c_lname, v.model, v.make, v.number_plate, v.category_id, b.start_date, b.end_date, b.status, b.total FROM customer_details c INNER JOIN bookings b ON c.id = b.customer_id INNER JOIN vehicle_basics v ON b.vehicle_id = v.id WHERE b.account_id = ? AND b.status = ? AND MONTH(b.start_date) = 11 AND YEAR(b.start_date) = 2025 ORDER BY b.created_at DESC";
    //         $stmt  = $this->con->prepare($query);
    //         $stmt->execute([$this->account_id, $status]);
    //         $this->con->commit();
    //     } catch (Exception $e) {
    //         $this->con->rollback();
    //     }

    //     return $stmt;
    // }

    // Get an agent's completed bookings
    public function read_agent_complete()
    {
        $status = "complete";

        try {
            $query = "SELECT b.id, b.booking_no, c.first_name AS c_fname, c.last_name AS c_lname,
                         v.model, v.make, v.number_plate, v.category_id,
                         b.start_date, b.end_date, b.status, b.total
                  FROM customer_details c
                  INNER JOIN bookings b ON c.id = b.customer_id
                  INNER JOIN vehicle_basics v ON b.vehicle_id = v.id
                  WHERE b.account_id = ?
                    AND b.status = ?
                    AND b.start_date BETWEEN ? AND ?
                  ORDER BY b.created_at DESC";

            $stmt = $this->con->prepare($query);
            $stmt->execute([$this->account_id, $status, $this->start_date, $this->end_date]);
            return $stmt;
        } catch (PDOException $e) {
            return $e;
        }
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

    // save booking
    public function create()
    {
        $status = "upcoming";
        $sql    = "INSERT INTO bookings
                    (customer_id, vehicle_id, account_driver_id, start_date, end_date, start_time, end_time,
                     custom_rate, total, account_id, status, in_capital, out_capital, driver_fee, vat, subtotal, duration,
                     courtesy, claim_no, accident_vehicle_reg)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

        $stmt = $this->con->prepare($sql);

        if ($stmt->execute([
            $this->c_id,
            $this->vehicle_id,
            $this->d_id,
            $this->start_date,
            $this->end_date,
            $this->start_time,
            $this->end_time,
            $this->custom_rate,
            $this->total,
            $this->account_id,
            $status,
            $this->in_capital,
            $this->out_capital,
            $this->driver_fee,
            $this->vat,
            $this->subtotal,
            $this->duration,
            $this->courtesy,
            $this->claim_no,
            $this->accident_vehicle_reg,
        ])) {
            $this->id = $this->con->lastInsertId();
            return true;
        }
        return false;
    }

    // save booking number of a booking
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

    // calculate and save cdw_total for this booking
    public function calculate_and_save_cdw($cdw_rate)
    {
        try {
            // Ensure we have start_date and end_date loaded
            if (empty($this->start_date) || empty($this->end_date)) {
                throw new Exception("Booking dates not set. Call get_cdw_calc_resources() first.");
            }

            // Calculate number of days (inclusive)
            $start = new DateTime($this->start_date);
            $end   = new DateTime($this->end_date);
            $days  = $start->diff($end)->days + 1;

            // Calculate cdw_total
            $this->cdw_total = $days * (float) $cdw_rate;

            // Save cdw_total back to bookings table
            $sql = "UPDATE {$this->table}
                   SET cdw_total = ?
                 WHERE id = ?";

            $stmt    = $this->con->prepare($sql);
            $success = $stmt->execute([$this->cdw_total, $this->id]);

            return $success;

        } catch (PDOException $e) {
            throw new Exception("SQL Error in calculate_and_save_cdw: " . $e->getMessage());
        }
    }
    // save booking number of a booking
    public function save_booking_video()
    {
        $sql  = "UPDATE bookings SET media_url = ? WHERE id = ?";
        $stmt = $this->con->prepare($sql);
        if ($stmt->execute([$this->url, $this->id])) {
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

    // get the booking details of a driver's bookings
    public function driver_booking_workplan()
    {
        $sql = "SELECT b.id, CONCAT(b.booking_no, ' ', c.first_name, ' ', c.last_name) AS title, b.start_date AS start_time, b.end_date AS end_time, b.driver_id AS 'group', b.status FROM bookings b INNER JOIN customer_details c ON b.customer_id = c.id";

        $stmt = $this->con->prepare($sql);
        $stmt->execute();

        return $stmt;
    }

    // activate a booking
    public function activate_booking()
    {
        try {
            $this->con->beginTransaction();

            // 1. Update booking status
            $status      = "active";
            $sqlBooking  = "UPDATE bookings SET status = ? WHERE id = ?";
            $stmtBooking = $this->con->prepare($sqlBooking);
            $stmtBooking->execute([$status, $this->id]);

            // 2. Get vehicle_id for this booking
            $sqlVehicle  = "SELECT vehicle_id FROM bookings WHERE id = ?";
            $stmtVehicle = $this->con->prepare($sqlVehicle);
            $stmtVehicle->execute([$this->id]);
            $vehicle_id = $stmtVehicle->fetchColumn();

            if (! $vehicle_id) {
                throw new Exception("Vehicle not found for booking {$this->id}");
            }

            // 3. Update vehicle availability
            $sqlUpdateVehicle  = "UPDATE vehicle_basics SET availability = 'unavailable' WHERE id = ?";
            $stmtUpdateVehicle = $this->con->prepare($sqlUpdateVehicle);
            $stmtUpdateVehicle->execute([$vehicle_id]);

            // 4. Log status change with user_id
            $sqlLog = "INSERT INTO vehicle_status_history (vehicle_id, status, changed_by, notes)
                   VALUES (?, ?, ?, ?)";
            $stmtLog = $this->con->prepare($sqlLog);
            $stmtLog->execute([$vehicle_id, "booked", $this->account_id, "Booking {$this->id} activated"]);

            $this->con->commit();
            return true;

        } catch (Exception $e) {
            $this->con->rollBack();
            error_log("Activation error: " . $e->getMessage());
            return false;
        }
    }

    // cancel a booking
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

    // complete a booking
    public function complete_booking_with_mileage()
    {
        try {
            $this->con->beginTransaction();

            // 1. Fetch vehicle basics (including current mileage + maintenance flag)
            $fleet     = new Fleet($this->con);
            $fleet->id = $this->vehicle_id;
            $vehicle   = $fleet->get_vehicle_base();

            if (! $vehicle) {
                throw new Exception("Vehicle not found for booking {$this->id}");
            }

            $vehicleMileage = intval($vehicle['mileage']);
            $maintenance    = $vehicle['maintenance'] ?? 'no';

            // 2. Validation: new mileage must be >= current mileage
            if ($this->mileage < $vehicleMileage) {
                throw new Exception("Mileage entered ({$this->mileage}) is less than current vehicle mileage ({$vehicleMileage})");
            }

            // 3. Calculate booking mileage (distance driven)
            $bookingMileage = 0;
            if ($vehicleMileage > 0) {
                $bookingMileage = $this->mileage - $vehicleMileage;
            }

            // 4. Update booking status + mileage
            $sqlBooking  = "UPDATE bookings SET status = ?, mileage = ? WHERE id = ?";
            $stmtBooking = $this->con->prepare($sqlBooking);
            $stmtBooking->execute(["complete", $bookingMileage, $this->id]);

            // 5. Update vehicle mileage + availability
            $availability = ($maintenance === 'yes') ? 'unavailable' : 'available';

            $sqlVehicle = "UPDATE vehicle_basics
                        SET mileage = ?, availability = ?
                        WHERE id = ?";
            $stmtVehicle = $this->con->prepare($sqlVehicle);
            $stmtVehicle->execute([$this->mileage, $availability, $this->vehicle_id]);

            $this->con->commit();

            // 6. Log status change with user_id
            $statusLabel = ($maintenance === 'yes') ? "maintenance" : "available";
            $sqlLog      = "INSERT INTO vehicle_status_history (vehicle_id, status, changed_by, notes)
           VALUES (?, ?, ?, ?)";
            $stmtLog = $this->con->prepare($sqlLog);
            $stmtLog->execute([$this->vehicle_id, $statusLabel, $this->account_id, "Booking {$this->id} completed"]);
            return true;

        } catch (Exception $e) {
            $this->con->rollBack();
            error_log("Error completing booking with mileage: " . $e->getMessage());
            return false;
        }
    }

    // extend a booking
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

    // update vehicle fuel details a booking
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

    // get a voucher details
    public function voucher_details($id)
    {
        try {
            $sql = "SELECT
                b.id, b.booking_no, b.custom_rate, b.driver_fee, b.fuel, b.vat, b.total,
                b.cdw_total, b.subtotal, b.start_date, b.start_time, b.end_date, b.end_time,
                b.created_at,
                vb.make, vb.model, vb.number_plate, vp.daily_rate,
                c.first_name AS customer_first_name, c.last_name AS customer_last_name,
                COALESCE(a.name, CONCAT(d.first_name, ' ', d.last_name)) AS driver_name
            FROM bookings b
            INNER JOIN vehicle_basics vb ON b.vehicle_id = vb.id
            INNER JOIN customer_details c ON b.customer_id = c.id
            INNER JOIN vehicle_pricing vp ON b.vehicle_id = vp.vehicle_id
            LEFT JOIN drivers d ON b.driver_id = d.id
            LEFT JOIN accounts a ON b.account_driver_id = a.id
            WHERE b.id = ?";

            $stmt = $this->con->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\Throwable $th) {
            return null;
        }
    }

    // get a invoice details
    public function invoice_details()
    {
        $query = "SELECT b.id, b.booking_no, b.start_date, b.end_date,
                     b.duration AS duration_days,
                     b.daily_rate, c.first_name AS customer_first_name,
                     c.last_name AS customer_last_name, c.email AS customer_email,
                     v.make, v.model, v.number_plate
              FROM bookings b
              JOIN customers c ON b.customer_id = c.id
              JOIN vehicles v ON b.vehicle_id = v.id
              WHERE b.id = ?";
        $stmt = $this->con->prepare($query);
        $stmt->execute([$this->id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // get cdw calculation resources: vehicle_id, start_date, end_date
    public function get_cdw_calc_resources()
    {
        try {
            $query = "SELECT vehicle_id, start_date, end_date
                  FROM {$this->table}
                  WHERE id = ?";

            $stmt = $this->con->prepare($query);
            $stmt->execute([$this->id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                // Assign directly to Booking object
                $this->vehicle_id = $row['vehicle_id'];
                $this->start_date = $row['start_date'];
                $this->end_date   = $row['end_date'];

                return $row; // return array for external use if needed
            }

            return false; // no booking found

        } catch (PDOException $e) {
            // Bubble up error so caller can handle it
            throw new Exception("SQL Error in get_cdw_calc_resources: " . $e->getMessage());
        }

    }

    // update a booking

    public function update()
    {
        $sql = "UPDATE bookings
            SET vehicle_id = ?,
                customer_id = ?,
                account_driver_id = ?,
                start_date = ?,
                end_date = ?,
                start_time = ?,
                end_time = ?,
                duration = ?,
                custom_rate = ?,
                subtotal = ?,
                vat = ?,
                total = ?,
                driver_fee = ?
            WHERE id = ?";

        $stmt = $this->con->prepare($sql);

        return $stmt->execute([
            $this->vehicle_id,
            $this->c_id,
            $this->d_id,
            $this->start_date,
            $this->end_date,
            $this->start_time,
            $this->end_time,
            $this->duration,
            $this->custom_rate,
            $this->subtotal,
            $this->vat,
            $this->total,
            $this->driver_fee,
            $this->id,
        ]);
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

    // when a vehicle has been changed in the calendar dashboard
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

    // function to calculate the duration of a booking
    public static function calculateDuration($start_date, $start_time, $end_date, $end_time, $override = false)
    {
        $startDateTime = strtotime("$start_date $start_time");
        $endDateTime   = strtotime("$end_date $end_time");

        // Base day difference
        $days = floor(($endDateTime - $startDateTime) / 86400);

        // Expected end datetime if exactly $days later at same start time
        $expectedEnd = strtotime("+{$days} days", $startDateTime);

        // Allowance window (+2 hours)
        $allowanceEnd = $expectedEnd + (2 * 3600);

        $extraDay = 0;
        if ($endDateTime > $allowanceEnd) {
            $extraDay = 1;
        }

        if ($override) {
            $extraDay = 0;
        }

        return $days + $extraDay;
    }

}
