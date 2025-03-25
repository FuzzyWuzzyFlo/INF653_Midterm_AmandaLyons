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
      // Create query
      $query = 'UPDATE ' . $this->table . ' SET author = :author WHERE id = :id';

      // Prepare statement
      $stmt = $this->conn->prepare($query);

      // Clean data
      $this->author = htmlspecialchars(strip_tags($this->author));
      $this->id = htmlspecialchars(strip_tags($this->id));

      // Bind data
      $stmt->bindParam(':author', $this->author);
      $stmt->bindParam(':id', $this->id);

      // Execute query
      if ($stmt->execute()) {
        return true;
      }

      // Print error if something goes wrong
      printf("Error: %s.\n", $stmt->error);

      return false;
    }
// Update Author ID
public function updateID() {
    // Create query
    $query = 'UPDATE ' . $this->table . ' SET id = :id WHERE author = :author';

    // Prepare statement
    $stmt = $this->conn->prepare($query);

    // Clean data
    $this->author = htmlspecialchars(strip_tags($this->author));
    $this->id = htmlspecialchars(strip_tags($this->id));

    // Bind data
    $stmt->bindParam(':author', $this->author);
    $stmt->bindParam(':id', $this->id);

    // Execute query
    if ($stmt->execute()) {
      return true;
    }

    // Print error if something goes wrong
    printf("Error: %s.\n", $stmt->error);

    return false;
  }

    // Delete Author
    public function delete() {
      if (!isset($this->id) || intval($this->id) <= 0) {
          echo json_encode(['message' => 'Missing or invalid ID']);
          return false;
      }
  
      // Check if ID exists before trying to delete
      $query = 'SELECT id FROM authors WHERE id = :id';
      $stmt = $this->conn->prepare($query);
      $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
      $stmt->execute();
  
      if (!$stmt->rowCount()) {
          echo json_encode(['message' => 'No author found with the specified ID']);
          return false;
      }
  
      $query = 'DELETE FROM authors WHERE id = :id';
      $stmt = $this->conn->prepare($query);
      $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
  
      try {
          if ($stmt->execute()) {
              echo json_encode([
                  'id' => $this->id,
                  'message' => 'Author deleted'
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
  }
