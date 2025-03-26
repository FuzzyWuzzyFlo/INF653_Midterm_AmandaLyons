<?php 
  class Author {
    // DB connection
    private $conn;
    private $table = 'authors';

    // Author properties
    public $id;
    public $author;

    // Constructor with DB connection
    public function __construct($db) {
      $this->conn = $db;
    }

    // Get Authors
    public function read() {
      // Create query
      $query = 'SELECT id, author FROM ' . $this->table . ' ORDER BY id DESC';

      // Prepare statement
      $stmt = $this->conn->prepare($query);

      // Execute query
      $stmt->execute();

      return $stmt;
    }

    // Get Single Author
    public function readSingle() {
      $query = 'SELECT 
                  id, 
                  author 
                FROM 
                  authors 
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
              $this->author = $row['author'];
              return true;
          }
  
          return false; // No result found
  
      } catch (PDOException $e) {
          error_log("SQL Error: " . $e->getMessage());
          echo json_encode(['message' => 'SQL Error: ' . $e->getMessage()]);
          return false;
      }
  }
  
//create author
  public function create() {
    if (empty($this->author)) {
        echo json_encode(['message' => 'Missing Required Parameters']);
        return false;
    }

    $query = 'INSERT INTO authors (author) 
              VALUES (:author)';

    $stmt = $this->conn->prepare($query);
    $stmt->bindValue(':author', $this->author, PDO::PARAM_STR);

    try {
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();

            echo json_encode([
                'id' => $this->id,
                'author' => $this->author
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

   // Update Author
public function update() {
  // Validate input
  if (!isset($this->id) || empty($this->author)) {
      echo json_encode(['message' => 'Missing Required Parameters']);
      return false;
  }

  // Check if the author exists
  $checkQuery = 'SELECT id FROM ' . $this->table . ' WHERE id = :id';
  $checkStmt = $this->conn->prepare($checkQuery);
  $checkStmt->bindParam(':id', $this->id, PDO::PARAM_INT);
  $checkStmt->execute();

  if ($checkStmt->rowCount() === 0) {
      echo json_encode(['message' => 'author_id Not Found']);
      return false;
  }

  // Create update query
  $query = 'UPDATE ' . $this->table . ' SET author = :author WHERE id = :id';

  // Prepare and bind
  $stmt = $this->conn->prepare($query);
  $stmt->bindParam(':author', $this->author);
  $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);

  // Execute and respond
  try {
      if ($stmt->execute()) {
          echo json_encode([
              'id' => $this->id,
              'author' => $this->author
          ]);
          return true;
      }
  } catch (PDOException $e) {
      echo json_encode(['message' => 'SQL Error: ' . $e->getMessage()]);
      return false;
  }

  echo json_encode(['message' => 'Author Not Updated']);
  return false;
}


  //delete author
  public function delete() {
    if (!isset($this->id) || intval($this->id) <= 0) {
        return ['status' => 400, 'message' => 'Missing or invalid ID'];
    }

    $query = 'SELECT id FROM authors WHERE id = :id';
    $stmt = $this->conn->prepare($query);
    $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
    $stmt->execute();

    if (!$stmt->rowCount()) {
        return ['status' => 404, 'message' => 'No author found with the specified ID'];
    }

    $query = 'DELETE FROM authors WHERE id = :id';
    $stmt = $this->conn->prepare($query);
    $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

    try {
        if ($stmt->execute()) {
            return [
                'status' => 200,
                'id' => $this->id,
                'message' => 'Author deleted'
            ];
        }
    } catch (PDOException $e) {
        error_log("SQL Error: " . $e->getMessage());
        return ['status' => 500, 'message' => 'SQL Error: ' . $e->getMessage()];
    }

    return false;
}


}
