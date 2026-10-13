<?php
// models/SubjectLecture.php
class SubjectLecture {
    private $conn;
    private $table_name = "subject_lectures";

    public $lecture_id;
    public $semester_subject_id;
    public $lecture_date;
    public $description;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new lecture
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (semester_subject_id, lecture_date, description) 
                  VALUES (?, ?, ?)";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->semester_subject_id = htmlspecialchars(strip_tags($this->semester_subject_id));
        $this->lecture_date = htmlspecialchars(strip_tags($this->lecture_date));
        $this->description = htmlspecialchars(strip_tags($this->description));

        // Bind parameters
        $stmt->bindParam(1, $this->semester_subject_id);
        $stmt->bindParam(2, $this->lecture_date);
        $stmt->bindParam(3, $this->description);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    // Read a single lecture by ID
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE lecture_id = ? LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->lecture_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            return $row;
        }

        return false;
    }

    // Read lectures by semester subject ID
    public function readBySubject($semester_subject_id) {
        $query = "SELECT * FROM " . $this->table_name . "
                  WHERE semester_subject_id = ?
                  ORDER BY lecture_date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $semester_subject_id);
        $stmt->execute();

        return $stmt;
    }

    // Get total number of lectures for a subject
    public function getTotalLectures($semester_subject_id) {
        $query = "SELECT COUNT(*) as total_lectures FROM " . $this->table_name . "
                  WHERE semester_subject_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $semester_subject_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total_lectures'] ?? 0;
    }

    // Delete a lecture
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE lecture_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->lecture_id);

        return $stmt->execute();
    }

    // Get lecture count for a semester subject
    public function getLectureCount($semester_subject_id) {
        $query = "SELECT COUNT(*) as count 
              FROM " . $this->table_name . " 
              WHERE semester_subject_id = :semester_subject_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':semester_subject_id', $semester_subject_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] ?? 0;
    }
}
?>