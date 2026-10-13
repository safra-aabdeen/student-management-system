<?php
// models/ExamEligibility.php
class ExamEligibility {
    private $conn;
    private $table_name = "exam_eligibility";

    public $eligibility_id;
    public $student_id;
    public $semester_subject_id;
    public $is_eligible;
    public $attendance_percentage;
    public $remarks;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Update or create eligibility record
    public function save() {
        // Check if record exists
        $check_query = "SELECT eligibility_id FROM " . $this->table_name . " 
                        WHERE student_id = ? AND semester_subject_id = ?";

        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->bindParam(1, $this->student_id);
        $check_stmt->bindParam(2, $this->semester_subject_id);
        $check_stmt->execute();

        if ($check_stmt->rowCount() > 0) {
            // Update existing record
            $row = $check_stmt->fetch(PDO::FETCH_ASSOC);
            $this->eligibility_id = $row['eligibility_id'];

            $query = "UPDATE " . $this->table_name . " 
                      SET is_eligible = ?, attendance_percentage = ?, remarks = ?
                      WHERE eligibility_id = ?";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->is_eligible);
            $stmt->bindParam(2, $this->attendance_percentage);
            $stmt->bindParam(3, $this->remarks);
            $stmt->bindParam(4, $this->eligibility_id);
        } else {
            // Create new record
            $query = "INSERT INTO " . $this->table_name . " 
                      (student_id, semester_subject_id, is_eligible, attendance_percentage, remarks) 
                      VALUES (?, ?, ?, ?, ?)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->student_id);
            $stmt->bindParam(2, $this->semester_subject_id);
            $stmt->bindParam(3, $this->is_eligible);
            $stmt->bindParam(4, $this->attendance_percentage);
            $stmt->bindParam(5, $this->remarks);
        }

        return $stmt->execute();
    }

    // Get eligibility for a student in a subject
    public function getEligibility($student_id, $semester_subject_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE student_id = ? AND semester_subject_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $student_id);
        $stmt->bindParam(2, $semester_subject_id);
        $stmt->execute();

        return $stmt;
    }

    // Get all eligibility records for a student in a semester
    public function getStudentSemesterEligibility($student_id, $semester_id) {
        $query = "SELECT e.*, ss.semester_id, s.subject_code, s.subject_name 
                  FROM " . $this->table_name . " e
                  JOIN semester_subjects ss ON e.semester_subject_id = ss.semester_subject_id
                  JOIN subjects s ON ss.subject_id = s.subject_id
                  WHERE e.student_id = ? AND ss.semester_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $student_id);
        $stmt->bindParam(2, $semester_id);
        $stmt->execute();

        return $stmt;
    }

    // Check if a student is eligible for all subjects in a semester
    public function isEligibleForAllSubjects($student_id, $semester_id) {
        $query = "SELECT COUNT(*) as total_subjects, 
                  SUM(CASE WHEN e.is_eligible = 1 THEN 1 ELSE 0 END) as eligible_subjects
                  FROM " . $this->table_name . " e
                  JOIN semester_subjects ss ON e.semester_subject_id = ss.semester_subject_id
                  WHERE e.student_id = ? AND ss.semester_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $student_id);
        $stmt->bindParam(2, $semester_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row['total_subjects'] == 0) {
            return false;
        }

        return $row['total_subjects'] == $row['eligible_subjects'];
    }
}
?>