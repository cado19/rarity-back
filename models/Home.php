<?php
class Home
{
    private $con;

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
        // Active bookings
        $activeStmt = $this->con->prepare("SELECT COUNT(*) as cnt FROM bookings WHERE account_id = ? AND status = 'active'");
        $activeStmt->execute([$this->agent_id]);
        $active_bookings = $activeStmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;

        // Upcoming bookings
        $upcomingStmt = $this->con->prepare("SELECT COUNT(*) as cnt FROM bookings WHERE account_id = ? AND start_date > CURDATE()");
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
            LIMIT 3
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

}
