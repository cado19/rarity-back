
<?php
    // THIS FILE WILL UPDATE DELETED STATUS OF A VEHICLE TO 'true'

    // Headers
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    //header mods for customer request
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Access-Control-Allow-Origin, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        http_response_code(200);
        exit();
    }

    // include necessary files
    include_once '../../config/Database.php';
    include_once '../../models/Fleet.php';

    // Instantiate The DB and connect to it
    $database = new Database();
    $db       = $database->connect();

    $fleet = new Fleet($db);

    $data = json_decode(file_get_contents('php://input'));

    $fleet->id = $data->vehicle_id;

    $response = [];

    if ($fleet->delete_vehicle()) {
        $status  = "Success";
        $message = "Successfully deleted vehicle";

        $response['status']  = $status;
        $response['message'] = $message;
    } else {
        $status  = "Error";
        $message = "An error occured ";

        $response['status']  = $status;
        $response['message'] = $message;
    }

echo json_encode($response);
?>