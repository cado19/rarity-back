<?php

class Conversation
{
    private $con;
    public $id;
    public $agent_id;
    public $customer_id;

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
        $sql = "SELECT c.id,
                   c.customer_id,
                   cd.first_name,
                   cd.last_name,
                   m.message AS last_message,
                   m.created_at AS last_time
            FROM conversations c
            JOIN customer_details cd ON cd.id = c.customer_id
            LEFT JOIN (
                SELECT m1.conversation_id, m1.message, m1.created_at
                FROM messages m1
                WHERE m1.id = (
                    SELECT m2.id
                    FROM messages m2
                    WHERE m2.conversation_id = m1.conversation_id
                    ORDER BY m2.created_at DESC
                    LIMIT 1
                )
            ) m ON m.conversation_id = c.id
            WHERE c.agent_id = ?
            ORDER BY COALESCE(m.created_at, c.created_at) DESC";

        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->agent_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get all conversations for a customer
    public function get_conversations_by_customer()
    {
        $sql = "SELECT c.id, c.agent_id, ag.name,
                       (SELECT message FROM messages m
                        WHERE m.conversation_id = c.id
                        ORDER BY m.created_at DESC LIMIT 1) as last_message,
                       (SELECT created_at FROM messages m
                        WHERE m.conversation_id = c.id
                        ORDER BY m.created_at DESC LIMIT 1) as last_time
                FROM conversations c
                JOIN accounts ag ON ag.id = c.agent_id
                WHERE c.customer_id = ?
                ORDER BY last_time DESC";

        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->customer_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_messages()
    {
        $sql  = "SELECT * FROM messages WHERE conversation_id = ? ORDER BY created_at ASC";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
