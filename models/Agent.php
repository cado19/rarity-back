<?php
class Agent
{
    // DB STUFF
    private $con;
    private $table = "accounts"; // this account deals with agents who are located in accounts table with role id 1,2

    // Account properties
    public $id;
    public $name;
    public $email;
    public $password;        // password that comes from the user
    public $hashed_password; // password that comes from the db
    public $phone_no;
    public $country;
    public $role_id;
    public $role;
    public $category_id; //category_id that is used to get agent rates
    public $agent_rate;
    public $deleted;
    public $commission_type;
    public $commission_amount;

    public function __construct($db)
    {
        /// when we instantiate a new post we'll pass in the db as a parameter
        $this->con = $db;
    }

    public function read()
    {
        $agent_id       = 2;
        $super_agent_id = 1;
        $sql            = "SELECT a.id, a.name, a.email, a.phone_no, a.country, r.name AS role FROM accounts a INNER JOIN roles r ON a.role_id = r.id WHERE role_id = ? || role_id = ?";
        $stmt           = $this->con->prepare($sql);
        $stmt->execute([$agent_id, $super_agent_id]);

        return $stmt;
    }

    public function read_single()
    {
        $sql = "SELECT a.id AS id, a.name, a.email, a.phone_no, a.country, r.name AS role
             FROM accounts a
             INNER JOIN roles r ON a.role_id = r.id
             WHERE a.id = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (! $row) {
            // No agent found
            return false;
        }

        $this->id       = $row['id'];
        $this->name     = $row['name'];
        $this->email    = $row['email'];
        $this->phone_no = $row['phone_no'];
        $this->country  = $row['country'];
        $this->role     = $row['role'];

        return true;
    }

    public function create()
    {
        $sql  = "INSERT INTO accounts (name, email, country, phone_no, password, role_id) VALUES (?,?,?,?,?,?)";
        $stmt = $this->con->prepare($sql);

        if ($stmt->execute([$this->name, $this->email, $this->country, $this->phone_no, $this->password, $this->role_id])) {
            $this->id = $this->con->lastInsertId();
            return true;
        } else {
            // print error if something goes wrong
            printf("Error :  % s . \n ", $stmt->error);
            return false;
        }
    }

    // check whether  agent data exists in the system
    public function check_unique_email()
    {
        $sql = "SELECT id FROM accounts WHERE email LIKE ?";

        // prepare the statement
        $stmt = $this->con->prepare($sql);

        $stmt->execute([$this->email]);

        return $stmt;
    }

    public function get_agent_commissions()
    {
        $sql = "SELECT cat.name, com.commission_type, com.commission_amount FROM agent_commissions com INNER JOIN vehicle_categories cat ON com.category_id = cat.id WHERE com.agent_id = ?";
        // prepare the statement
        $stmt = $this->con->prepare($sql);

        $stmt->execute([$this->id]);

        return $stmt;
    }

    public function get_commission_type_and_amount()
    {
        $sql  = "SELECT commission_type, commission_amount FROM agent_commissions WHERE agent_id = ? AND category_id = ? LIMIT 0,1";
        $stmt = $this->con->prepare($sql);

        $stmt->execute([$this->id, $this->category_id]);

        //fetch the array
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        //set the properties
        $this->commission_type   = $row['commission_type'];
        $this->commission_amount = $row['commission_amount'];

    }

    public function update_commission()
    {
        $sql = "UPDATE agent_commissions SET commission_type = ?, commission_amount = ? WHERE agent_id = ? AND category_id = ?";
        // prepare the statement
        $stmt = $this->con->prepare($sql);

        if ($stmt->execute([$this->commission_type, $this->commission_amount, $this->id, $this->category_id])) {
            return true;
        } else {
            // print error if something goes wrong
            printf("Error :  % s . \n ", $stmt->error);
            return false;
        }
    }

    public function update_rate()
    {
        $sql = "UPDATE agent_rates SET rate = ? WHERE agent_id = ? AND category_id = ?";
        // prepare the statement
        $stmt = $this->con->prepare($sql);

        if ($stmt->execute([$this->agent_rate, $this->id, $this->category_id])) {
            return true;
        } else {
            // print error if something goes wrong
            printf("Error :  % s . \n ", $stmt->error);
            return false;
        }
    }

    // CREATE COMMISSIONS AFTER CREATING AN AGENT
    public function create_suv_commission()
    {

        $category_id     = 1;
        $commission_type = "percentage";
        $rate            = 10;

        $sql  = "INSERT INTO agent_commissions (agent_id, category_id, commission_type, commission_amount) VALUES (?,?,?,?)";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->id, $category_id, $commission_type, $rate]);

    }

    public function create_mid_size_suv_commission()
    {

        $category_id     = 2;
        $commission_type = "percentage";
        $rate            = 10;

        $sql  = "INSERT INTO agent_commissions (agent_id, category_id, commission_type, commission_amount) VALUES (?,?,?,?)";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->id, $category_id, $commission_type, $rate]);

    }

    public function create_medium_car_commission()
    {

        $category_id     = 3;
        $commission_type = "percentage";
        $rate            = 10;

        $sql  = "INSERT INTO agent_commissions (agent_id, category_id, commission_type, commission_amount) VALUES (?,?,?,?)";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->id, $category_id, $commission_type, $rate]);

    }

    public function create_small_car_commission()
    {

        $category_id     = 4;
        $commission_type = "percentage";
        $rate            = 10;

        $sql  = "INSERT INTO agent_commissions (agent_id, category_id, commission_type, commission_amount) VALUES (?,?,?,?)";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->id, $category_id, $commission_type, $rate]);

    }
    public function create_safari_commission()
    {

        $category_id     = 5;
        $commission_type = "percentage";
        $rate            = 10;

        $sql  = "INSERT INTO agent_commissions (agent_id, category_id, commission_type, commission_amount) VALUES (?,?,?,?)";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->id, $category_id, $commission_type, $rate]);

    }
    public function create_luxury_commission()
    {

        $category_id     = 6;
        $commission_type = "percentage";
        $rate            = 10;

        $sql  = "INSERT INTO agent_commissions (agent_id, category_id, commission_type, commission_amount) VALUES (?,?,?,?)";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->id, $category_id, $commission_type, $rate]);

    }
    public function create_commercial_commission()
    {

        $category_id     = 7;
        $commission_type = "percentage";
        $rate            = 10;

        $sql  = "INSERT INTO agent_commissions (agent_id, category_id, commission_type, commission_amount) VALUES (?,?,?,?)";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->id, $category_id, $commission_type, $rate]);

    }

    // CREATE RATES AFTER CREATING AN AGENT
    public function create_suv_rate()
    {

        $category_id = 1;
        $rate        = 0;

        $sql  = "INSERT INTO agent_rates (agent_id, category_id, rate) VALUES (?,?,?)";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->id, $category_id, $rate]);

    }

    public function create_mid_size_suv_rate()
    {

        $category_id = 2;
        $rate        = 0;

        $sql  = "INSERT INTO agent_rates (agent_id, category_id, rate) VALUES (?,?,?)";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->id, $category_id, $rate]);

    }

    public function create_medium_car_rate()
    {
        $category_id = 3;
        $rate        = 0;

        $sql  = "INSERT INTO agent_rates (agent_id, category_id, rate) VALUES (?,?,?)";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->id, $category_id, $rate]);

    }

    public function create_small_car_rate()
    {
        $category_id = 4;
        $rate        = 0;

        $sql  = "INSERT INTO agent_rates (agent_id, category_id, rate) VALUES (?,?,?)";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->id, $category_id, $rate]);

    }

    public function create_safari_rate()
    {
        global $con;
        $category_id = 5;
        $rate        = 0;

        $sql  = "INSERT INTO agent_rates (agent_id, category_id, rate) VALUES (?,?,?)";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->id, $category_id, $rate]);

    }

    public function create_luxury_rate()
    {
        $category_id = 6;
        $rate        = 0;

        $sql  = "INSERT INTO agent_rates (agent_id, category_id, rate) VALUES (?,?,?)";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->id, $category_id, $rate]);

    }

    public function create_commercial_rate()
    {
        $category_id = 7;
        $rate        = 0;

        $sql  = "INSERT INTO agent_rates (agent_id, category_id, rate) VALUES (?,?,?)";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->id, $category_id, $rate]);

    }
}
