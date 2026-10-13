<?php
// models/Subject.php
class Subject {
    private $conn;
    private $table_name = "subjects";

    public $subject_id;
    public $subject_code;
    public $subject_name;
    public $credit_hours;
    public $department_id;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 (subject_code, subject_name, credit_hours, department_id) 
                 VALUES (?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->subject_code);
        $stmt->bindParam(2, $this->subject_name);
        $stmt->bindParam(3, $this->credit_hours);
        $stmt->bindParam(4, $this->department_id);

        return $stmt->execute();
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                 SET subject_code = ?, subject_name = ?, credit_hours = ?, department_id = ? 
                 WHERE subject_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->subject_code);
        $stmt->bindParam(2, $this->subject_name);
        $stmt->bindParam(3, $this->credit_hours);
        $stmt->bindParam(4, $this->department_id);
        $stmt->bindParam(5, $this->subject_id);

        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE subject_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->subject_id);

        return $stmt->execute();
    }

    public function readAll() {
        $query = "SELECT s.*, d.department_code, d.department_name 
                 FROM " . $this->table_name . " s
                 JOIN departments d ON s.department_id = d.department_id
                 ORDER BY s.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE subject_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->subject_id);
        $stmt->execute();

        return $stmt;
    }
}
?>