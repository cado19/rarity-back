<?php
/**
 *
 */
class Account
{
    // DB STUFF
    private $con;
    private $table = "accounts";

    // Account properties
    public $id;
    public $name;
    public $email;
    public $password;        // password that comes from the user
    public $hashed_password; // password that comes from the db
    public $phone_no;
    public $country;
    public $role_id;
    public $category_id; //category_id that is used to get agent rates
    public $agent_rate;
    public $deleted;

    public function __construct($db)
    {
        /// when we instantiate a new post we'll pass in the db as a parameter
        $this->con = $db;
    }

    // this function fetches the account for login purposes. It ensures that the account exists with the given email
    public function fetch_account()
    {
        $sql  = "SELECT id, name, email, password, role_id FROM accounts WHERE email = ? LIMIT 0,1";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->email]);

        return $stmt;
    }

    public function fetch_agent_rate()
    {
        $sql  = "SELECT rate FROM agent_rates WHERE agent_id = ? AND category_id = ? LIMIT 0,1";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->id, $this->category_id]);
        $row              = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->agent_rate = $row['rate'];
    }

    public function fetch_role_id()
    {
        $sql  = "SELECT role_id FROM accounts WHERE id = ? LIMIT 0,1";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->id]);
        $row           = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->role_id = $row['role_id'];
    }
}
