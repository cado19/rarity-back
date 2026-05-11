<?php
include_once __DIR__ . '/Account.php';
class Home
{
    private $con;
    public $agent_id;

    public function __construct($db)
    {
        $this->con = $db;
    }

    // Get booking stats for a customer
    public function get_stats($customer_id)
    {
        try {
            // Active bookings
            $sqlActive = "SELECT COUNT(*) as active_count
                          FROM bookings
                          WHERE customer_id = ? AND status = 'active' AND deleted = 'false'";
            $stmt = $this->con->prepare($sqlActive);
            $stmt->execute([$customer_id]);
            $active = $stmt->fetch(PDO::FETCH_ASSOC)['active_count'];

            // Next upcoming booking
            $sqlUpcoming = "SELECT CONCAT(start_date, ' ', start_time) as upcoming
                            FROM bookings
                            WHERE customer_id = ? AND status = 'upcoming' AND deleted = 'false'
                            ORDER BY start_date ASC LIMIT 1";
            $stmt = $this->con->prepare($sqlUpcoming);
            $stmt->execute([$customer_id]);
            $upcoming        = $stmt->fetch(PDO::FETCH_ASSOC);
            $nextReservation = $upcoming ? $upcoming['upcoming'] : null;

            // Total bookings
            $sqlTotal = "SELECT COUNT(*) as total_count
                         FROM bookings
                         WHERE customer_id = ? AND deleted = 'false'";
            $stmt = $this->con->prepare($sqlTotal);
            $stmt->execute([$customer_id]);
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total_count'];

            return [
                "active"   => $active,
                "upcoming" => $nextReservation,
                "total"    => $total,
            ];
        } catch (Exception $e) {
            return [
                "error"   => true,
                "message" => "Error fetching stats: " . $e->getMessage(),
            ];
        }
    }

    // Get available vehicles
    public function get_available_vehicles($limit = 5)
    {
        try {
            $sqlVehicles = "SELECT id, make, model, image
                            FROM vehicle_basics
                            WHERE availability = 'Free' AND deleted = 'false'
                            ORDER BY created_at DESC LIMIT ?";
            $stmt = $this->con->prepare($sqlVehicles);
            $stmt->bindValue(1, (int) $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [
                "error"   => true,
                "message" => "Error fetching vehicles: " . $e->getMessage(),
            ];
        }
    }

    public function get_agent_stats()
    {
        // $account     = new Account($this->con);
        // $account->id = $this->agent_id;
        // $roles       = $account->get_roles();

        // Active bookings
        $activeStmt = $this->con->prepare("SELECT COUNT(*) as cnt FROM bookings WHERE account_id = ? AND status = 'active'");
        $activeStmt->execute([$this->agent_id]);
        $active_bookings = $activeStmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;

        // Upcoming bookings
        $upcomingStmt = $this->con->prepare("SELECT COUNT(*) as cnt FROM bookings WHERE account_id = ? AND status = 'upcoming'");
        $upcomingStmt->execute([$this->agent_id]);
        $upcoming_bookings = $upcomingStmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;

        // Revenue total
        $revenueStmt = $this->con->prepare("SELECT SUM(total) as revenue FROM bookings WHERE account_id = ?");
        $revenueStmt->execute([$this->agent_id]);
        $revenue_total = $revenueStmt->fetch(PDO::FETCH_ASSOC)['revenue'] ?? 0;

        // Recent bookings
        $recentStmt = $this->con->prepare("
            SELECT b.id, b.booking_no, cd.first_name, cd.last_name, b.start_date
            FROM bookings b
            JOIN customer_details cd ON cd.id = b.customer_id
            WHERE b.account_id = ?
            ORDER BY b.created_at DESC
            LIMIT 3
        ");
        $recentStmt->execute([$this->agent_id]);
        $recent_bookings = $recentStmt->fetchAll(PDO::FETCH_ASSOC);

        // Available vehicles
        $vehicleStmt = $this->con->prepare("
            SELECT v.id, v.make, v.model, v.transmission, v.fuel
            FROM vehicle_basics v
            WHERE v.id NOT IN (SELECT vehicle_id FROM bookings WHERE status = 'active')
            LIMIT 10
        ");
        $vehicleStmt->execute();
        $available_vehicles = $vehicleStmt->fetchAll(PDO::FETCH_ASSOC);

        // Recent messages
        $messageStmt = $this->con->prepare("
            SELECT m.id, m.message, m.created_at, cd.first_name, cd.last_name
            FROM messages m
            JOIN conversations c ON c.id = m.conversation_id
            JOIN customer_details cd ON cd.id = c.customer_id
            WHERE c.agent_id = ?
            ORDER BY m.created_at DESC
            LIMIT 3
        ");
        $messageStmt->execute([$this->agent_id]);
        $recent_messages = $messageStmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            "active_bookings"    => $active_bookings,
            "upcoming_bookings"  => $upcoming_bookings,
            "revenue_total"      => $revenue_total,
            "recent_bookings"    => $recent_bookings,
            "available_vehicles" => $available_vehicles,
            "recent_messages"    => $recent_messages,
        ];
    }

    public function get_web_stats()
    {
        $account     = new Account($this->con);
        $account->id = $this->agent_id;
        $account->fetch_role_ids();
        $roles = $account->role_ids;

        // Active bookings
        $activeStmt = $this->con->prepare("SELECT COUNT(*) as cnt FROM bookings WHERE status = 'active' AND account_id = ?");
        $activeStmt->execute([$this->agent_id]);
        $active_bookings = $activeStmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;

        // Upcoming bookings
        $upcomingStmt = $this->con->prepare("SELECT COUNT(*) as cnt FROM bookings WHERE status = 'upcoming' AND account_id = ?");
        $upcomingStmt->execute([$this->agent_id]);
        $upcoming_bookings = $upcomingStmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;

        // Revenue total
        $revenueStmt = $this->con->prepare("SELECT SUM(CAST(total AS DECIMAL(10,2))) as revenue FROM bookings WHERE deleted = 'false' AND account_id = ?");
        $revenueStmt->execute([$this->agent_id]);
        $revenue_total = $revenueStmt->fetch(PDO::FETCH_ASSOC)['revenue'] ?? 0;

        // Recent bookings
        $recentStmt = $this->con->prepare("
            SELECT b.id, b.booking_no, cd.first_name, cd.last_name, b.start_date
            FROM bookings b
            JOIN customer_details cd ON cd.id = b.customer_id
            WHERE b.deleted = 'false' AND b.account_id = ?
            ORDER BY b.created_at DESC
            LIMIT 5
        ");
        $recentStmt->execute([$this->agent_id]);
        $recent_bookings = $recentStmt->fetchAll(PDO::FETCH_ASSOC);

        // Available vehicles
        $vehicleStmt = $this->con->prepare("
            SELECT v.id, v.make, v.model, v.transmission, v.fuel
            FROM vehicle_basics v
            WHERE v.id NOT IN (SELECT vehicle_id FROM bookings WHERE status = 'active')
            LIMIT 3
        ");
        $vehicleStmt->execute();
        $available_vehicles = $vehicleStmt->fetchAll(PDO::FETCH_ASSOC);

        // Top customers (most bookings)
        $topCustomersStmt = $this->con->prepare("
            SELECT cd.id, cd.first_name, cd.last_name, COUNT(b.id) AS booking_count
            FROM bookings b
            JOIN customer_details cd ON cd.id = b.customer_id
            WHERE b.deleted = 'false' AND b.account_id = ?
            GROUP BY cd.id, cd.first_name, cd.last_name
            ORDER BY booking_count DESC
            LIMIT 5
        ");
        $topCustomersStmt->execute([$this->agent_id]);
        $top_customers = $topCustomersStmt->fetchAll(PDO::FETCH_ASSOC);

        // Top vehicles (most bookings)
        $topVehiclesStmt = $this->con->prepare("
            SELECT v.id, v.make, v.model, v.number_plate, COUNT(b.id) AS booking_count
            FROM bookings b
            JOIN vehicle_basics v ON v.id = b.vehicle_id
            WHERE b.deleted = 'false' AND b.account_id = ?
            GROUP BY v.id, v.make, v.model, v.number_plate
            ORDER BY booking_count DESC
            LIMIT 5
        ");
        $topVehiclesStmt->execute([$this->agent_id]);
        $top_vehicles = $topVehiclesStmt->fetchAll(PDO::FETCH_ASSOC);

        // Revenue by customer
        $revenueByCustomerStmt = $this->con->prepare("
            SELECT cd.id, cd.first_name, cd.last_name, SUM(CAST(b.total AS DECIMAL(10,2))) AS total_spent
            FROM bookings b
            JOIN customer_details cd ON cd.id = b.customer_id
            WHERE b.deleted = 'false' AND b.account_id = ?
            GROUP BY cd.id, cd.first_name, cd.last_name
            ORDER BY total_spent DESC
            LIMIT 5
        ");
        $revenueByCustomerStmt->execute([$this->agent_id]);
        $revenue_by_customer = $revenueByCustomerStmt->fetchAll(PDO::FETCH_ASSOC);

        // Revenue by vehicle
        $revenueByVehicleStmt = $this->con->prepare("
            SELECT v.id, v.make, v.model, SUM(CAST(b.total AS DECIMAL(10,2))) AS total_revenue
            FROM bookings b
            JOIN vehicle_basics v ON v.id = b.vehicle_id
            WHERE b.deleted = 'false' AND b.account_id = ?
            GROUP BY v.id, v.make, v.model
            ORDER BY total_revenue DESC
            LIMIT 5
        ");
        $revenueByVehicleStmt->execute([$this->agent_id]);
        $revenue_by_vehicle = $revenueByVehicleStmt->fetchAll(PDO::FETCH_ASSOC);

        $stats = [
            "active_bookings"     => $active_bookings,
            "upcoming_bookings"   => $upcoming_bookings,
            "revenue_total"       => $revenue_total,
            "recent_bookings"     => $recent_bookings,
            "available_vehicles"  => $available_vehicles,
            "top_customers"       => $top_customers,
            "top_vehicles"        => $top_vehicles,
            "revenue_by_customer" => $revenue_by_customer,
            "revenue_by_vehicle"  => $revenue_by_vehicle,
        ];

        // Role-specific stats
        if (in_array('driver', $roles)) {
            $stats["deliveries"]         = $this->get_driver_deliveries($this->agent_id);
            $stats["chauffeur_bookings"] = $this->get_chauffeur_bookings($this->agent_id);
        }

        if (in_array('salesperson', $roles)) {
            $stats["sales_bookings"] = $this->get_sales_bookings($this->agent_id);
        }

        if (in_array('fleet_manager', $roles)) {
            $stats["work_orders"]          = $this->get_work_orders();
            $stats["maintenance_vehicles"] = $this->get_vehicles_under_maintenance();
            $stats["service_alerts"]       = $this->get_service_alerts();
        }

        return $stats;
    }

    private function get_driver_deliveries($driverId)
    {
        $stmt = $this->con->prepare("
            SELECT d.id, b.booking_id, b.booking_no, b.start_date
            FROM deliveries d JOIN bookings b ON d.booking_id = b.id
            WHERE (d.driver_id = ? OR d.account_driver_id = ?)
            AND d.delivered = 'false'
            ORDER BY b.start_date DESC
            LIMIT 5
        ");
        $stmt->execute([$driverId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function get_chauffeur_bookings($driverId)
    {
        $stmt = $this->con->prepare("
            SELECT b.id, b.booking_no, b.start_date, b.end_date, c.first_name, c.last_name
            FROM bookings b
            JOIN customer_details c ON c.id = b.customer_id
            WHERE (b.driver_id = ? OR b.account_driver_id = ?)
            ORDER BY b.start_date DESC
            LIMIT 5
        ");
        $stmt->execute([$driverId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function get_sales_bookings($accountId)
    {
        $stmt = $this->con->prepare("
            SELECT b.id, b.booking_no, b.start_date, b.end_date, c.first_name, c.last_name
            FROM bookings b
            JOIN customer_details c ON c.id = b.customer_id
            WHERE b.account_id = ?
            ORDER BY b.created_at DESC
            LIMIT 5
        ");
        $stmt->execute([$accountId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function get_work_orders()
    {
        $stmt = $this->con->prepare("
            SELECT w.id, w.work_order_no, w.status, w.created_at, v.make, v.model, v.number_plate
            FROM work_orders w
            JOIN vehicle_basics v ON v.id = w.vehicle_id
            WHERE w.status = 'open'
            ORDER BY w.created_at DESC
            LIMIT 5
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function get_vehicles_under_maintenance()
    {
        $stmt = $this->con->prepare("
            SELECT v.id, v.make, v.model, v.number_plate
            FROM vehicle_basics v
            WHERE v.maintenance = 'yes'
            LIMIT 5
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function get_service_alerts()
    {
        $stmt = $this->con->prepare("
            SELECT v.id, v.make, v.model, v.number_plate, v.mileage, v.service_interval
            FROM vehicle_basics v
            WHERE (v.service - v.mileage) <= 500
            ORDER BY v.mileage DESC
            LIMIT 5
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
