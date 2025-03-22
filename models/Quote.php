<?php
class Quote {
    private $conn;
    private $table = 'quotes';

    // Properties
    public $id;
    public $quote;
    public $author_id;
    public $category_id;

    // ======================
    // Constructor
    // ======================
    public function __construct($db) {
        $this->conn = $db;
    }

    // ======================
    // READ ALL QUOTES
    // ======================
    public function read() {
        $query = 'SELECT * FROM ' . $this->table;
        $stmt = $this->conn->prepare($query);

        try {
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("SQL Error: " . $e->getMessage());
            echo json_encode(['message' => 'SQL Error: ' . $e->getMessage()]);
            return false;
        }
    }

    // ======================
    // READ SINGLE OR FILTERED QUOTES
    // ======================
    public function readFiltered($id = null, $author_id = null, $category_id = null) {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE 1=1';

        if (!empty($id)) {
            $query .= ' AND id = :id';
        }
        if (!empty($author_id)) {
            $query .= ' AND author_id = :author_id';
        }
        if (!empty($category_id)) {
            $query .= ' AND category_id = :category_id';
        }

        $stmt = $this->conn->prepare($query);

        if (!empty($id)) {
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        }
        if (!empty($author_id)) {
            $stmt->bindValue(':author_id', $author_id, PDO::PARAM_INT);
        }
        if (!empty($category_id)) {
            $stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);
        }

        try {
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            echo "SQL Error: " . $e->getMessage();
            return false;
        }
    }

    public function create() {
        if (
            empty($this->quote) || 
            empty($this->author_id) || 
            empty($this->category_id)
        ) {
            echo json_encode(['message' => 'Missing required fields']);
            return false;
        }
    
        $query = 'INSERT INTO ' . $this->table . ' 
                  SET quote = :quote, author_id = :author_id, category_id = :category_id';
    
        $stmt = $this->conn->prepare($query);
    
        // ✅ Clean numeric values but keep the quote string raw
        $this->author_id = intval($this->author_id);
        $this->category_id = intval($this->category_id);
    
        $stmt->bindValue(':quote', $this->quote, PDO::PARAM_STR);
        $stmt->bindValue(':author_id', $this->author_id, PDO::PARAM_INT);
        $stmt->bindValue(':category_id', $this->category_id, PDO::PARAM_INT);
    
        try {
            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            error_log("SQL Error: " . $e->getMessage());
            echo json_encode(['message' => 'SQL Error: ' . $e->getMessage()]);
            return false;
        }
    
        return false;
    }
    

// ======================
// UPDATE QUOTE
// ======================
public function update() {
    if (
        !isset($this->id) ||
        empty($this->quote) || 
        empty($this->author_id) || 
        empty($this->category_id)
    ) {
        echo json_encode(['message' => 'Missing required fields']);
        return false;
    }

    $query = 'UPDATE ' . $this->table . ' 
              SET quote = :quote, author_id = :author_id, category_id = :category_id 
              WHERE id = :id';

    $stmt = $this->conn->prepare($query);

    // ✅ Clean numeric values but keep the quote string raw
    $this->author_id = intval($this->author_id);
    $this->category_id = intval($this->category_id);
    $this->id = intval($this->id);

    $stmt->bindValue(':quote', $this->quote, PDO::PARAM_STR);
    $stmt->bindValue(':author_id', $this->author_id, PDO::PARAM_INT);
    $stmt->bindValue(':category_id', $this->category_id, PDO::PARAM_INT);
    $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

    try {
        if ($stmt->execute()) {
            if ($stmt->rowCount()) {
                return true;
            } else {
                echo json_encode(['message' => 'No changes made or ID not found']);
                return false;
            }
        }
    } catch (PDOException $e) {
        error_log("SQL Error: " . $e->getMessage());
        echo json_encode(['message' => 'SQL Error: ' . $e->getMessage()]);
        return false;
    }

    return false;
}

    // ======================
// DELETE QUOTE
// ======================
public function delete() {
    // ✅ Validate ID (no empty check)
    if (!isset($this->id) || intval($this->id) <= 0) {
        echo json_encode(['message' => 'Missing or invalid ID']);
        return false;
    }

    // ✅ Check if ID exists before trying to delete
    $query = 'SELECT id FROM ' . $this->table . ' WHERE id = :id';
    $stmt = $this->conn->prepare($query);
    $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
    $stmt->execute();

    if (!$stmt->rowCount()) {
        echo json_encode(['message' => 'No quote found with the specified ID']);
        return false;
    }

    // ✅ Proceed with deletion
    $query = 'DELETE FROM ' . $this->table . ' WHERE id = :id';
    $stmt = $this->conn->prepare($query);
    $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

    try {
        if ($stmt->execute()) {
            echo json_encode(['message' => 'Quote deleted']);
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
