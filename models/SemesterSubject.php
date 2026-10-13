<?php
// models/SemesterSubject.php
class SemesterSubject {
    private $conn;
    private $table_name = "semester_subjects";

    public $semester_subject_id;
    public $semester_id;
    public $subject_id;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Read all semester subjects
    public function readAll() {
        $query = "SELECT ss.*, s.subject_code, s.subject_name, s.credit_hours, 
              sem.year_number, sem.semester_number, d.department_code 
              FROM " . $this->table_name . " ss
              LEFT JOIN subjects s ON ss.subject_id = s.subject_id
              LEFT JOIN semesters sem ON ss.semester_id = sem.semester_id
              LEFT JOIN departments d ON sem.department_id = d.department_id
              ORDER BY sem.year_number, sem.semester_number, s.subject_code";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Read semester subjects by semester ID
    public function readBySemester($semester_id) {
        $query = "SELECT ss.*, s.subject_code, s.subject_name, s.credit_hours
                  FROM " . $this->table_name . " ss
                  LEFT JOIN subjects s ON ss.subject_id = s.subject_id
                  WHERE ss.semester_id = ?
                  ORDER BY s.subject_code";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $semester_id);
        $stmt->execute();
        return $stmt;
    }

    // Create a new semester subject
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (semester_id, subject_id)
                  VALUES (?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->semester_id);
        $stmt->bindParam(2, $this->subject_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete a semester subject
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE semester_subject_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->semester_subject_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Read one semester subject by ID
    public function readOne($semester_subject_id) {
        $query = "SELECT ss.*, s.subject_code, s.subject_name, s.credit_hours,
              sem.year_number, sem.semester_number, sem.semester_id
              FROM " . $this->table_name . " ss
              LEFT JOIN subjects s ON ss.subject_id = s.subject_id
              LEFT JOIN semesters sem ON ss.semester_id = sem.semester_id
              WHERE ss.semester_subject_id = ?
              LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $semester_subject_id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>