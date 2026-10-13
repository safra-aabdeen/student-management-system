<?php
// models/Semester.php
class Semester {
    private $conn;
    private $table_name = "semesters";

    public $semester_id;
    public $department_id;
    public $year_number;
    public $semester_number;
    public $start_date;
    public $end_date;
    public $is_active;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Read all semesters
    public function readAll() {
        $query = "SELECT s.*, d.department_name, d.department_code 
                 FROM " . $this->table_name . " s
                 LEFT JOIN departments d ON s.department_id = d.department_id
                 ORDER BY s.department_id, s.year_number, s.semester_number";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // Create new semester
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                (department_id, year_number, semester_number, start_date, end_date, is_active)
                VALUES (:department_id, :year_number, :semester_number, :start_date, :end_date, :is_active)";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->department_id = htmlspecialchars(strip_tags($this->department_id));
        $this->year_number = htmlspecialchars(strip_tags($this->year_number));
        $this->semester_number = htmlspecialchars(strip_tags($this->semester_number));
        $this->start_date = htmlspecialchars(strip_tags($this->start_date));
        $this->end_date = htmlspecialchars(strip_tags($this->end_date));
        $this->is_active = $this->is_active ? 1 : 0;

        // Bind parameters
        $stmt->bindParam(':department_id', $this->department_id);
        $stmt->bindParam(':year_number', $this->year_number);
        $stmt->bindParam(':semester_number', $this->semester_number);
        $stmt->bindParam(':start_date', $this->start_date);
        $stmt->bindParam(':end_date', $this->end_date);
        $stmt->bindParam(':is_active', $this->is_active);

        // Execute query
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Update semester
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET department_id = :department_id,
                    year_number = :year_number,
                    semester_number = :semester_number,
                    start_date = :start_date,
                    end_date = :end_date,
                    is_active = :is_active
                WHERE semester_id = :semester_id";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->semester_id = htmlspecialchars(strip_tags($this->semester_id));
        $this->department_id = htmlspecialchars(strip_tags($this->department_id));
        $this->year_number = htmlspecialchars(strip_tags($this->year_number));
        $this->semester_number = htmlspecialchars(strip_tags($this->semester_number));
        $this->start_date = htmlspecialchars(strip_tags($this->start_date));
        $this->end_date = htmlspecialchars(strip_tags($this->end_date));
        $this->is_active = $this->is_active ? 1 : 0;

        // Bind parameters
        $stmt->bindParam(':semester_id', $this->semester_id);
        $stmt->bindParam(':department_id', $this->department_id);
        $stmt->bindParam(':year_number', $this->year_number);
        $stmt->bindParam(':semester_number', $this->semester_number);
        $stmt->bindParam(':start_date', $this->start_date);
        $stmt->bindParam(':end_date', $this->end_date);
        $stmt->bindParam(':is_active', $this->is_active);

        // Execute query
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Delete semester
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE semester_id = ?";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->semester_id = htmlspecialchars(strip_tags($this->semester_id));

        // Bind parameter
        $stmt->bindParam(1, $this->semester_id);

        // Execute query
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Get semester by ID
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE semester_id = ?";

        $stmt = $this->conn->prepare($query);

        // Bind parameter
        $stmt->bindParam(1, $this->semester_id);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    // Toggle semester active status
    public function toggleActive() {
        $query = "UPDATE " . $this->table_name . " SET is_active = :is_active WHERE semester_id = :semester_id";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->semester_id = htmlspecialchars(strip_tags($this->semester_id));
        $this->is_active = $this->is_active ? 1 : 0;

        // Bind parameters
        $stmt->bindParam(':semester_id', $this->semester_id);
        $stmt->bindParam(':is_active', $this->is_active);

        // Execute query
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }
}
?>