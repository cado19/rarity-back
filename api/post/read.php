<?php
// THIS FILE WILL DELIVER ALL POSTS TO EXTERNAL REQUESTS

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// include necessary files
include_once '../../config/Database.php';
include_once '../../models/Post.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

// print_r($db);

// instantiate blog post object
$post = new Post($db);

// blog post query
$result = $post->read();

// get row count
$num = $result->rowCount();

//check if any posts

if ($num > 0) {
    $posts_arr         = [];
    $posts_arr['data'] = []; //this is where the data will go
    $posts_arr['message'] = []; //this is where the data will go
    $posts_arr['status'] = []; //this is where the data will go

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        // single post item array
        $post_item = [
            'id'            => $id,
            'title'         => $title,
            'body'          => $body,
            'author'        => $author,
            'category_id'   => $category_id,
            'category_name' => $category_name,
        ];

        // push that post item to 'data' index of array
        array_push($posts_arr['data'], $post_item);
        $posts_arr['message'] = ['messsage' => "Successfully fetched vehicles"];
        $posts_arr['status'] = ['status' =>'200'];

        // convert the posts to json
        echo json_encode($posts_arr);
    }
} else {
    // No posts found in the database ($num = 0)
    $response = [
        'messsage' => 'No posts found',
    ];
}
