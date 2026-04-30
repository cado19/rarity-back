<?php

class Message
{
    private $con;
    public $conversation_id;
    public $sender_role;
    public $sender_id;
    public $message;

    public function __construct($db)
    {$this->con = $db;}

    public function send()
    {
        $sql  = "INSERT INTO messages (conversation_id, sender_role, sender_id, message) VALUES (?, ?, ?, ?)";
        $stmt = $this->con->prepare($sql);
        return $stmt->execute([$this->conversation_id, $this->sender_role, $this->sender_id, $this->message]);
    }
}
