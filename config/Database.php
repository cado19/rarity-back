<?php
class Database
{
    //DB PARAMS
    private $host     = 'localhost';
    private $dbname   = 'rarity-rental';
    private $username = 'root';
    private $password = 'cado';
    private $con;

    public function connect()
    {
        // $this->con = null;

        try {
            $this->con = new PDO("mysql:host=$this->host;dbname=$this->dbname",$this->username,$this->password);
            $this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // echo "Connected successfully";
        } catch (PDOException $e) {
            echo "connection error: " . $e->getMessage();
        }

        return $this->con;
    }
}
?>

