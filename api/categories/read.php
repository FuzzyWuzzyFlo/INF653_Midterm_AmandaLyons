<?php 
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../config/Database.php';
include_once '../../models/Category.php'; 

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate category object
$category = new Category($db); 

// Category query
$result = $category->read(); 
$num = $result->rowCount();

// Check if any categories
if($num > 0) {
    $categories_arr = array();

    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $category_item = array(
            'id' => $id,
            'category' => $category
        );

        array_push($categories_arr, $category_item);
    }

    // Output JSON
    echo json_encode($categories_arr);

} else {
    // No category found
    echo json_encode(
        array('message' => 'No category Found')
    );
}
