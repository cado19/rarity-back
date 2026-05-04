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
        $sql = "INSERT INTO messages (conversation_id, sender_role, sender_id, message, created_at)
            VALUES (?, ?, ?, ?, NOW())";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([
            $this->conversation_id,
            $this->sender_role,
            $this->sender_id,
            $this->message,
        ]);

        if ($this->sender_role === 'customer') {
            $update = $this->con->prepare("
            UPDATE conversations
            SET last_message = ?, last_time = NOW(),
                agent_unread_count = agent_unread_count + 1
            WHERE id = ?
        ");
        } elseif ($this->sender_role === 'agent') {
            $update = $this->con->prepare("
            UPDATE conversations
            SET last_message = ?, last_time = NOW(),
                customer_unread_count = customer_unread_count + 1
            WHERE id = ?
        ");
            error_log("Branch: agent → increment customer_unread_count");
        } else {
            error_log("Unknown sender_role: " . $this->sender_role);
            return false;
        }

        $update->execute([$this->message, $this->conversation_id]);
        $rows = $update->rowCount();
        error_log("Rows updated in conversations: " . $rows);

        return $rows > 0;
    }

}
