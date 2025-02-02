<?php
// THIS FILE WILL DELIVER A SINGLE POSTS TO EXTERNAL REQUESTS

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// include necessary files
include_once '../../config/Database.php';
include_once '../../models/Post.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

// instantiate blog post object
$post = new Post($db);

// get the id from the url

if (isset($_GET['id'])) {
    $post->id = $_GET['id'];
} else {
    die();
}

// get single post
$post->read_single();

// create the array
$post_arr = array(
    'id' => $post->id,
    'title' => $post->title,
    'body' => $post->body,
    'author' => $post->author,
    'category_id' => $post->category_id,
    'category_name' => $post->category_name
);

echo json_encode($post_arr);
