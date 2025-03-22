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
  public function read_single(){
    // Create query
    $query = 'SELECT
          id,
          category
        FROM
          ' . $this->table . '
      WHERE id = ?
      LIMIT 0,1';

      //Prepare statement
      $stmt = $this->conn->prepare($query);

      // Bind ID
      $stmt->bindParam(1, $this->id);

      // Execute query
      $stmt->execute();

      $row = $stmt->fetch(PDO::FETCH_ASSOC);

      // set properties
      $this->id = $row['id'];
      $this->category = $row['category'];
  }

  public function create() {
    // Validate input
    if (empty($this->category)) {
        echo "Category value is empty or invalid.";
        return false;
    }

    // Create Query
    $query = 'INSERT INTO ' . $this->table . ' 
              SET category = :category';

    // Prepare Statement
    $stmt = $this->conn->prepare($query);

    // Clean data
    $this->category = htmlspecialchars(strip_tags($this->category));

    // Bind data (fixed binding name)
    $stmt->bindParam(':category', $this->category);

    // Execute query
    try {
        if ($stmt->execute()) {
            return true;
        }
    } catch (PDOException $e) {
        echo "Insert failed: " . $e->getMessage();
        return false;
    }

    // Output detailed error if execute fails
    $error = $stmt->errorInfo();
    echo "SQL Error: " . $error[2];
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
    // Create query
    $query = 'DELETE FROM ' . $this->table . ' WHERE id = :id';

    // Prepare Statement
    $stmt = $this->conn->prepare($query);

    // clean data
    $this->id = htmlspecialchars(strip_tags($this->id));

    // Bind Data
    $stmt-> bindParam(':id', $this->id);

    // Execute query
    if($stmt->execute()) {
      return true;
    }

    // Print error if something goes wrong
    printf("Error: $s.\n", $stmt->error);

    return false;
    }
  }
