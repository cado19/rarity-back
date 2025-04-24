<?php
class Analytics
{
    // DB STUFF
    private $con;
    // private $table = "posts";

    // Analytics variables and stuff
    public $total;
    public $month;
    public $year;
    public $category;
    public $make;
    public $model;
    public $number_plate;

    // Constructor with DB
    public function __construct($db)
    {
        // when we instantiate a new post we'll pass in the db as a parameter
        $this->con = $db;
    }

    // LANDING PAGE STATS

    public function income_last_three_months()
    {
        $sql  = "SELECT monthname(created_at) AS Month, sum(total) AS Total from bookings WHERE datediff(now(), start_date) <= 90 group by Month";
        $stmt = $this->con->prepare($sql);
        $stmt->execute();

        return $stmt;
    }

    // earned revenue this month
    public function earned_revenue()
    {
        $sql  = "SELECT monthname(created_at) AS Month, sum(total) AS total FROM bookings WHERE datediff(now(), start_date) <= 30 GROUP BY Month";
        $stmt = $this->con->prepare($sql);
        $stmt->execute();

        $row         = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->total = $row['total'];
        $this->month = $row['Month'];

    }

    // get the number of bookings in the current month
    public function booking_count_this_month()
    {
        $sql  = "SELECT count(b.id) AS total FROM bookings b  WHERE datediff(now(), start_date) <= 30";
        $stmt = $this->con->prepare($sql);
        $stmt->execute();

        $row         = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->total = $row['total'];

    }

    public function most_popular_categories()
    {
        $status = "cancelled";
        $sql    = "SELECT count(vb.category_id) AS total, cat.name AS category FROM vehicle_basics vb INNER JOIN bookings b ON vb.id = b.vehicle_id INNER JOIN vehicle_categories cat ON vb.category_id = cat.id WHERE b.status != ? GROUP BY vb.category_id ORDER BY count(vb.category_id) DESC";

        $stmt = $this->con->prepare($sql);
        $stmt->execute([$status]);

        return $stmt;
    }

    public function revenue_ninety_days()
    {
        $sql = "SELECT monthname(start_date) AS Month, month(start_date) AS MonthNumber, sum(total) AS Total from bookings WHERE start_date >= DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-01'), INTERVAL 3 MONTH)
            -- AND start_date < DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-01'), INTERVAL 2 MONTH)
            group by Month, MonthNumber ORDER BY MonthNumber ASC";

        $stmt = $this->con->prepare($sql);
        $stmt->execute();

        return $stmt;
    }
    // VEHICLE ALL TIME STATS

    //most profitable all time vehicle
    public function profitable_vehicle()
    {
        $status = "cancelled";

        $sql  = "SELECT v.model, v.make, v.number_plate, sum(b.total) AS Income FROM vehicle_basics v INNER JOIN bookings b ON v.id = b.vehicle_id WHERE b.status != ? GROUP BY v.id ORDER BY Income DESC LIMIT 1";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$status]);
        $row = $stmt->fetch();

        $this->make         = $row['make'];
        $this->model        = $row['model'];
        $this->number_plate = $row['number_plate'];
        $this->total        = $row['Income'];
    }

    //most popular all time vehicle
    public function popular_vehicle()
    {
        $status = "cancelled";

        $sql  = "SELECT v.model, v.make, v.number_plate, count(v.id) AS total FROM vehicle_basics v INNER JOIN bookings b ON v.id = b.vehicle_id WHERE b.status != ? GROUP BY v.id ORDER BY total DESC LIMIT 1";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$status]);
        $row = $stmt->fetch();

        $this->make         = $row['make'];
        $this->model        = $row['model'];
        $this->number_plate = $row['number_plate'];
        $this->total        = $row['total'];
    }

    public function vehicle_totals()
    {
        $status = "cancelled";
        $sql    = "SELECT v.id, v.make, v.model, v.number_plate, sum(b.total) AS total, (SELECT daily_rate FROM vehicle_pricing vp WHERE vp.vehicle_id = v.id) AS daily_rate, sum(b.total) / 180 AS ADR FROM bookings b INNER JOIN vehicle_basics v ON b.vehicle_id = v.id WHERE b.status != ? group by v.id";
        $stmt   = $this->con->prepare($sql);
        $stmt->execute([$status]);

        return $stmt;
    }

    // VEHICLE MONTH STATS
    public function month_vehicle_totals()
    {
        $date   = $this->year . '-' . $this->month . '-' . '01';
        $status = "cancelled";
        $sql    = "SELECT
                        vb.make,
                        vb.model,
                        vb.number_plate,
                        COALESCE(SUM(b.total), 0) AS aggregated_total
                   FROM
                        vehicle_basics vb
                   LEFT JOIN
                        bookings b ON vb.id = b.vehicle_id
                                   AND b.start_date >= ?
                                   AND b.start_date < ? + INTERVAL 1 MONTH
                                   AND b.status != ?
                   GROUP BY
                        vb.make, vb.model, vb.number_plate;";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$date, $date, $status]);

        return $stmt;
    }
}
