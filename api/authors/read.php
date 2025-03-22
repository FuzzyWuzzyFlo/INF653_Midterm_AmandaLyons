<?php 
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../config/Database.php';
include_once '../../models/Author.php'; // Updated to Author

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate author object
$author = new Author($db); // Updated to Author

// Author query
$result = $author->read(); // Updated to Author read()
$num = $result->rowCount();

// Check if any authors
if($num > 0) {
    $authors_arr = array();

    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $author_item = array(
            'id' => $id,
            'author' => $author
        );

        array_push($authors_arr, $author_item);
    }

    // Output JSON
    echo json_encode($authors_arr);

} else {
    // No authors found
    echo json_encode(
        array('message' => 'No Authors Found')
    );
}
