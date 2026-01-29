<?php
class Notification
{
    private $con;
    private $table = "device_tokens";

    // Properties
    public $id;
    public $user_id;
    public $user_type; // 'customer' or 'agent'
    public $expo_token;
    public $created_at;

    public function __construct($db)
    {
        $this->con = $db;
    }

    // Register or update token
    public function register()
    {
        $sql = "INSERT INTO {$this->table} (user_id, user_type, expo_token)
                VALUES (:user_id, :user_type, :expo_token)
                ON DUPLICATE KEY UPDATE expo_token = :expo_token";

        $stmt = $this->con->prepare($sql);

        return $stmt->execute([
            ':user_id'    => $this->user_id,
            ':user_type'  => $this->user_type,
            ':expo_token' => $this->expo_token,
        ]);
    }

    // Get all tokens for a given user
    public function getTokens()
    {
        $sql  = "SELECT expo_token FROM {$this->table} WHERE user_id = :user_id AND user_type = :user_type";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([
            ':user_id'   => $this->user_id,
            ':user_type' => $this->user_type,
        ]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Send notification via Expo Push API
    public function send($title, $body, $data = [])
    {
        $url = "https://exp.host/--/api/v2/push/send";

        $fields = [
            "to"    => $this->expo_token,
            "sound" => "default",
            "title" => $title,
            "body"  => $body,
            "data"  => $data,
        ];

        $headers = ["Content-Type: application/json"];

        // 🔎 Debug: log outgoing payload
        error_log("Expo Push Payload: " . json_encode($fields));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);

        // 🔎 Debug: log cURL errors
        if ($result === false) {
            error_log("cURL error: " . curl_error($ch));
        } else {
            // 🔎 Debug: log Expo response
            error_log("Expo Push Response: " . $result);
        }

        curl_close($ch);

        return $result;
    }
}
