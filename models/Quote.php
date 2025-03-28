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
            http_response_code(400); // Bad Request
            echo json_encode(['message' => 'Missing Required Parameters']);
            return false;
        }
    
        // Check if author_id and category_id exist
        if (!$this->isValidAuthor($this->author_id)) {
            http_response_code(404);
            echo json_encode(['message' => 'author_id Not Found']);
            return false;
        }
    
        if (!$this->isValidCategory($this->category_id)) {
            http_response_code(404);
            echo json_encode(['message' => 'category_id Not Found']);
            return false;
        }
    
        $query = 'INSERT INTO ' . $this->table . ' (quote, author_id, category_id)
                  VALUES (:quote, :author_id, :category_id)';
    
        $stmt = $this->conn->prepare($query);
    
        $stmt->bindValue(':quote', $this->quote, PDO::PARAM_STR);
        $stmt->bindValue(':author_id', $this->author_id, PDO::PARAM_INT);
        $stmt->bindValue(':category_id', $this->category_id, PDO::PARAM_INT);
    
        try {
            if ($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
    
                http_response_code(201); // Created
                echo json_encode([
                    'id' => $this->id,
                    'quote' => $this->quote,
                    'author_id' => $this->author_id,
                    'category_id' => $this->category_id
                ]);
                return true;
            }
        } catch (PDOException $e) {
            http_response_code(500); // Internal Server Error
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
        // Validate input
        if (
            empty($this->id) ||
            empty($this->quote) ||
            empty($this->author_id) ||
            empty($this->category_id)
        ) {
            http_response_code(400);
            echo json_encode(['message' => 'Missing Required Parameters']);
            return false;
        }
    
        // Validate author_id exists
        if (!$this->isValidAuthor($this->author_id)) {
            http_response_code(404);
            echo json_encode(['message' => 'author_id Not Found']);
            return false;
        }
    
        // Validate category_id exists
        if (!$this->isValidCategory($this->category_id)) {
            http_response_code(404);
            echo json_encode(['message' => 'category_id Not Found']);
            return false;
        }
    
        // Check if quote ID exists before updating
        $checkQuery = 'SELECT id FROM ' . $this->table . ' WHERE id = :id';
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindValue(':id', $this->id, PDO::PARAM_INT);
        $checkStmt->execute();
    
        if (!$checkStmt->rowCount()) {
            http_response_code(404);
            echo json_encode(['message' => 'No Quotes Found']);
            return false;
        }
    
        // Clean input
        $this->quote = htmlspecialchars(strip_tags($this->quote));
        $this->id = intval($this->id);
        $this->author_id = intval($this->author_id);
        $this->category_id = intval($this->category_id);
    
        // Build update query
        $query = 'UPDATE ' . $this->table . '
                  SET quote = :quote,
                      author_id = :author_id,
                      category_id = :category_id
                  WHERE id = :id';
    
        $stmt = $this->conn->prepare($query);
    
        $stmt->bindValue(':quote', $this->quote, PDO::PARAM_STR);
        $stmt->bindValue(':author_id', $this->author_id, PDO::PARAM_INT);
        $stmt->bindValue(':category_id', $this->category_id, PDO::PARAM_INT);
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
    
        // Execute and respond
        try {
            if ($stmt->execute()) {
                http_response_code(200);
                echo json_encode([
                    'id' => $this->id,
                    'quote' => $this->quote,
                    'author_id' => $this->author_id,
                    'category_id' => $this->category_id
                ]);
                return true;
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['message' => 'SQL Error: ' . $e->getMessage()]);
            return false;
        }
    
        http_response_code(500);
        echo json_encode(['message' => 'Quote Not Updated']);
        return false;
    }
    
    

    // ======================
    // DELETE QUOTE
    // ======================
    public function delete() {
        if (!isset($this->id) || intval($this->id) <= 0) {
            return ['status' => 400, 'message' => 'Missing or invalid ID'];
        }
    
        $query = 'SELECT id FROM quotes WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
        $stmt->execute();
    
        if (!$stmt->rowCount()) {
            return ['status' => 404, 'message' => 'No quote found with the specified ID'];
        }
    
        $query = 'DELETE FROM quotes WHERE id = :id';
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
