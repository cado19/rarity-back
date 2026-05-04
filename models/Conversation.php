<?php

class Conversation
{
    private $con;
    public $id;
    public $agent_id;
    public $customer_id;
    public $viewer_role;

    public function __construct($db)
    {$this->con = $db;}

    public function create()
    {
        $sql  = "INSERT INTO conversations (agent_id, customer_id) VALUES (?, ?)";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->agent_id, $this->customer_id]);
        $this->id = $this->con->lastInsertId();
        return $this->id;
    }

    // Get all conversations for an agent
    public function get_conversations_by_agent()
    {
        $sql = "SELECT c.id, c.customer_id, cd.first_name, cd.last_name,
                   c.last_message, c.last_time, c.agent_unread_count
            FROM conversations c
            JOIN customer_details cd ON cd.id = c.customer_id
            WHERE c.agent_id = ?
            ORDER BY c.last_time DESC";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->agent_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get all conversations for a customer
    public function get_conversations_by_customer()
    {
        $sql = "SELECT c.id, c.agent_id, ag.name AS agent_name,
                   c.last_message, c.last_time, c.customer_unread_count
            FROM conversations c
            JOIN accounts ag ON ag.id = c.agent_id
            WHERE c.customer_id = ?
            ORDER BY c.last_time DESC";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->customer_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get all messages in a conversation
    public function get_messages()
    {
        $sql = "SELECT m.id, m.message, m.sender_role, m.sender_id, m.created_at
            FROM messages m
            WHERE m.conversation_id = ?
            ORDER BY m.created_at ASC";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->id]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($this->viewer_role === 'customer') {
            $reset = $this->con->prepare("UPDATE conversations SET customer_unread_count = 0 WHERE id = ?");
        } else {
            $reset = $this->con->prepare("UPDATE conversations SET agent_unread_count = 0 WHERE id = ?");
        }
        $reset->execute([$this->id]);

        return $messages;
    }

    // Get total number of a customer's unread messages from all conversations
    public function get_total_unread_for_customer()
    {
        $sql = "SELECT SUM(customer_unread_count) AS total_unread
            FROM conversations
            WHERE customer_id = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->customer_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int) $row['total_unread'] : 0;
    }

    // Get total number of a customer's unread messages from all conversations
    public function get_total_unread_for_agent()
    {
        $sql = "SELECT SUM(agent_unread_count) AS total_unread
            FROM conversations
            WHERE agent_id = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->agent_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int) $row['total_unread'] : 0;
    }

    // Get existing conversation
    public function find_existing($agent_id, $customer_id)
    {
        $sql  = "SELECT id FROM conversations WHERE agent_id = ? AND customer_id = ? LIMIT 1";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$agent_id, $customer_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['id'] : null;
    }

}
