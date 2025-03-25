<?php
class Quote {
    private $conn;
    private $table = 'quotes';

    // Properties
    public $id;
    public $quote;
    public $author;
    public $category;

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
        $query = 'SELECT 
                    q.id, 
                    q.quote, 
                    a.author AS author, 
                    c.category AS category
                  FROM 
                    ' . $this->table . ' q
                  LEFT JOIN 
                    authors a ON q.author_id = a.id
                  LEFT JOIN 
                    categories c ON q.category_id = c.id
                  LIMIT 25';

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
        $query = 'SELECT 
                    q.id, 
                    q.quote, 
                    a.author AS author, 
                    c.category AS category
                  FROM 
                    ' . $this->table . ' q
                  LEFT JOIN 
                    authors a ON q.author_id = a.id
                  LEFT JOIN 
                    categories c ON q.category_id = c.id
                  WHERE 1=1';

        if (!empty($id)) {
            $query .= ' AND q.id = :id';
        }
        if (!empty($author_id)) {
            $query .= ' AND q.author_id = :author_id';
        }
        if (!empty($category_id)) {
            $query .= ' AND q.category_id = :category_id';
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

    //create quote
    public function create() {
        if (
            empty($this->quote) || 
            empty($this->author_id) || 
            empty($this->category_id)
        ) {
            echo json_encode(['message' => 'Missing Required Parameters']);
            return false;
        }
    
        // ✅ Check if author_id and category_id exist
        if (!$this->isValidAuthor($this->author_id)) {
            echo json_encode(['message' => 'author_id Not Found']);
            return false;
        }
    
        if (!$this->isValidCategory($this->category_id)) {
            echo json_encode(['message' => 'category_id Not Found']);
            return false;
        }
    
        $query = 'INSERT INTO ' . $this->table . ' 
                  SET quote = :quote, author_id = :author_id, category_id = :category_id';
    
        $stmt = $this->conn->prepare($query);
    
        $stmt->bindValue(':quote', $this->quote, PDO::PARAM_STR);
        $stmt->bindValue(':author_id', $this->author_id, PDO::PARAM_INT);
        $stmt->bindValue(':category_id', $this->category_id, PDO::PARAM_INT);
    
        try {
            if ($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
    
                echo json_encode([
                    'id' => $this->id,
                    'quote' => $this->quote,
                    'author_id' => $this->author_id,
                    'category_id' => $this->category_id
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
    
    private function isValidAuthor($author_id) {
        $query = 'SELECT id FROM authors WHERE id = :author_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':author_id', $author_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
    
    private function isValidCategory($category_id) {
        $query = 'SELECT id FROM categories WHERE id = :category_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
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
                  SET quote = :quote, 
                      author_id = :author_id, 
                      category_id = :category_id 
                  WHERE id = :id';

        $stmt = $this->conn->prepare($query);

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
        // Validate ID
        if (!isset($this->id) || intval($this->id) <= 0) {
            return ['status' => 400, 'message' => 'Missing or invalid ID'];
        }
    
        // Check if ID exists before deleting
        $query = 'SELECT id FROM ' . $this->table . ' WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
        $stmt->execute();
    
        if (!$stmt->rowCount()) {
            return ['status' => 404, 'message' => 'No quote found with the specified ID'];
        }
    
        // Proceed with deletion
        $query = 'DELETE FROM ' . $this->table . ' WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
    
        try {
            if ($stmt->execute()) {
                return [
                    'status' => 200,
                    'id' => $this->id,
                    'message' => 'Quote deleted'
                ];
            }
        } catch (PDOException $e) {
            error_log("SQL Error: " . $e->getMessage());
            return ['status' => 500, 'message' => 'SQL Error: ' . $e->getMessage()];
        }
    
        return false;
    }
    

    // ======================
    // READ SINGLE QUOTE
    // ======================
    
    public function readSingle() {
        $query = 'SELECT 
                    q.id, 
                    q.quote, 
                    a.author AS author, 
                    c.category AS category
                  FROM ' . $this->table . ' q
                  LEFT JOIN authors a ON q.author_id = a.id
                  LEFT JOIN categories c ON q.category_id = c.id
                  WHERE q.id = :id
                  LIMIT 1';
    
        $stmt = $this->conn->prepare($query);
    
        // Bind the ID
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
    
        try {
            $stmt->execute();
    
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($row) {
                $this->quote = $row['quote'];
                $this->author = $row['author'];   // ✅ Return author name instead of ID
                $this->category = $row['category']; // ✅ Return category name instead of ID
                return true;
            }
    
            return false; // No result found
    
        } catch (PDOException $e) {
            error_log("SQL Error: " . $e->getMessage());
            echo json_encode(['message' => 'SQL Error: ' . $e->getMessage()]);
            return false;
        }
    }
    
}
