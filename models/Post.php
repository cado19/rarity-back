<?php
class Post
{
    // DB STUFF
    private $con;
    private $table = "posts";

    // Post properties
    public $id;
    public $category_id;
    public $category_name;
    public $title;
    public $body;
    public $author;
    public $created_at;

    // Constructor with DB
    public function __construct($db)
    {
        // when we instantiate a new post we'll pass in the db as a parameter
        $this->con = $db;
    }

    // get posts
    public function read()
    {
        //create the query
        $query = "SELECT c.name AS category_name, p.id, p.category_id, p.title, p.body, p.author, p.created_at FROM posts p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC";

        // prepare statement
        $stmt = $this->con->prepare($query);

        //execuute the query
        $stmt->execute();

        return $stmt;

    }

    public function read_sigle()
    {
        //create the query
        $query = "SELECT c.name AS category_name, p.id, p.category_id, p.title, p.body, p.author, p.created_at FROM" . $this->table . "p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ? LIMIT 0,1";

        // prepare statement
        $stmt = $this->con->prepare($query);

        //execuute the query
        $stmt->execute([$this->id]);

        //fetch the array
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        //set the properties
        $this->title         = $row['title'];
        $this->body          = $row['body'];
        $this->author        = $row['author'];
        $this->category_id   = $row['category_id'];
        $this->category_name = $row['category_name'];

        return $stmt;
    }
    public function create(){
        // Create the query 
        $sql = "INSERT INTO posts (title, body, author, category_id) VALUES (?,?,?,?)";

        // prepare the statement
        $stmt = $this->con->prepare($sql);

        //clean the data
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->body = htmlspecialchars(strip_tags($this->body));
        $this->author = htmlspecialchars(strip_tags($this->author));
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));

        if($stmt->execute([$this->title, $this->body, $this->author, $this->category_id])){
            return true;
        }

        // print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);

        return false;
    }


}
