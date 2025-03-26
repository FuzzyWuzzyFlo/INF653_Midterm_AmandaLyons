<?php
  class Category {
    // DB Stuff
    private $conn;
    private $table = 'categories';

    // Properties
    public $id;
    public $category;

    // Constructor with DB
    public function __construct($db) {
      $this->conn = $db;
    }

    // Get categories
    public function read() {
      // Create query
      $query = 'SELECT
        id,
        category
      FROM
        ' . $this->table . '
      ORDER BY
        id DESC';

      // Prepare statement
      $stmt = $this->conn->prepare($query);

      // Execute query
      $stmt->execute();

      return $stmt;
    }

    // Get Single Category
    public function readSingle() {
      $query = 'SELECT 
                  id, 
                  category 
                FROM 
                  categories 
                WHERE 
                  id = :id
                LIMIT 1';
  
      $stmt = $this->conn->prepare($query);
      $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
  
      try {
          $stmt->execute();
          $row = $stmt->fetch(PDO::FETCH_ASSOC);
  
          if ($row) {
              $this->id = $row['id'];
              $this->category = $row['category'];
              return true;
          }
  
          return false; // No category found
  
      } catch (PDOException $e) {
          error_log("SQL Error: " . $e->getMessage());
          echo json_encode(['message' => 'SQL Error: ' . $e->getMessage()]);
          return false;
      }
  }
  

  public function create() {
    if (empty($this->category)) {
        echo json_encode(['message' => 'Missing Required Parameters']);
        return false;
    }

    $query = 'INSERT INTO categories (category) 
              VALUES (:category)';

    $stmt = $this->conn->prepare($query);
    $stmt->bindValue(':category', $this->category, PDO::PARAM_STR);

    try {
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();

            echo json_encode([
                'id' => $this->id,
                'category' => $this->category
            ]);
            return true;
        }
    } catch (PDOException $e) {
        error_log("SQL Error: " . $e->getMessage());
        echo json_encode(['message' => 'SQL Error: ' . $e->getMessage()]);
        return false;
    }

    return false;
}


  public function update() {
    $query = 'UPDATE ' . $this->table . ' 
              SET category = :category 
              WHERE id = :id';

    $stmt = $this->conn->prepare($query);

    // Clean data
    $this->category = htmlspecialchars(strip_tags($this->category));
    $this->id = htmlspecialchars(strip_tags($this->id));

    // Bind data
    $stmt->bindParam(':category', $this->category);
    $stmt->bindParam(':id', $this->id);

    if ($stmt->execute()) {
        if ($stmt->rowCount()) {
            return true;
        } else {
            echo "No rows updated. ID might not exist.";
            return false;
        }
    }

    // Output detailed error
    $error = $stmt->errorInfo();
    echo "SQL Error: " . $error[2];
    return false;
}

  // Delete Category
  public function delete() {
    if (!isset($this->id) || intval($this->id) <= 0) {
        return ['status' => 400, 'message' => 'Missing or invalid ID'];
    }

    // Check if ID exists
    $query = 'SELECT id FROM categories WHERE id = :id';
    $stmt = $this->conn->prepare($query);
    $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
    $stmt->execute();

    if (!$stmt->rowCount()) {
        return ['status' => 404, 'message' => 'No category found with the specified ID'];
    }

    // Proceed to delete
    $query = 'DELETE FROM categories WHERE id = :id';
    $stmt = $this->conn->prepare($query);
    $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

    try {
        if ($stmt->execute()) {
            return [
                'status' => 200,
                'id' => $this->id,
                'message' => 'Category deleted'
            ];
        }
    } catch (PDOException $e) {
        error_log("SQL Error: " . $e->getMessage());
        return ['status' => 500, 'message' => 'SQL Error: ' . $e->getMessage()];
    }

    return false;
}

}
