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
        $sql  = "SELECT a.id AS id, a.name, a.email, a.phone_no, a.country, r.name AS role FROM accounts a INNER JOIN roles r ON a.role_id = r.id WHERE a.id = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->id]);

        //fetch the array
        $row            = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->id       = $row['id'];
        $this->name     = $row['name'];
        $this->email    = $row['email'];
        $this->phone_no = $row['phone_no'];
        $this->country  = $row['country'];
        $this->role     = $row['role'];
    }

    public function create()
    {
        $sql  = "INSERT INTO accounts (name, email, country, phone_no, password, role_id) VALUES (?,?,?,?,?,?)";
        $stmt = $this->con->prepare();

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
}
