<?php
// models/StudentAttendance.php
class StudentAttendance {
    private $conn;
    private $table_name = "student_attendance";

    public $attendance_id;
    public $student_id;
    public $lecture_id;
    public $is_present;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Record attendance for a student
    public function record() {
        // Check if attendance already exists
        $check_query = "SELECT attendance_id FROM " . $this->table_name . " 
                        WHERE student_id = ? AND lecture_id = ?";

        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->bindParam(1, $this->student_id);
        $check_stmt->bindParam(2, $this->lecture_id);
        $check_stmt->execute();

        if ($check_stmt->rowCount() > 0) {
            // Update existing record
            $row = $check_stmt->fetch(PDO::FETCH_ASSOC);
            $this->attendance_id = $row['attendance_id'];

            $query = "UPDATE " . $this->table_name . " 
                      SET is_present = ? 
                      WHERE attendance_id = ?";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->is_present);
            $stmt->bindParam(2, $this->attendance_id);
        } else {
            // Create new record
            $query = "INSERT INTO " . $this->table_name . " 
                      (student_id, lecture_id, is_present) 
                      VALUES (?, ?, ?)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->student_id);
            $stmt->bindParam(2, $this->lecture_id);
            $stmt->bindParam(3, $this->is_present);
        }

        return $stmt->execute();
    }

    // Get attended lectures count for a student in a subject
    public function getAttendedLecturesCount($student_id, $semester_subject_id) {
        $query = "SELECT COUNT(*) as attended_lectures 
                  FROM " . $this->table_name . " sa
                  JOIN subject_lectures sl ON sa.lecture_id = sl.lecture_id
                  WHERE sa.student_id = ? 
                  AND sl.semester_subject_id = ? 
                  AND sa.is_present = 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $student_id);
        $stmt->bindParam(2, $semester_subject_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['attended_lectures'] ?? 0;
    }

    // Get attendance percentage for a student in a subject
    public function getAttendancePercentage($student_id, $semester_subject_id) {
        // Get total lectures
        $total_query = "SELECT COUNT(*) as total_lectures 
                        FROM subject_lectures 
                        WHERE semester_subject_id = ?";

        $total_stmt = $this->conn->prepare($total_query);
        $total_stmt->bindParam(1, $semester_subject_id);
        $total_stmt->execute();

        $total_row = $total_stmt->fetch(PDO::FETCH_ASSOC);
        $total_lectures = $total_row['total_lectures'] ?? 0;

        if ($total_lectures == 0) {
            return 0;
        }

        // Get attended lectures
        $attended = $this->getAttendedLecturesCount($student_id, $semester_subject_id);

        // Calculate percentage
        return ($attended / $total_lectures) * 100;
    }

    // Get student attendance for a specific lecture
    public function getStudentAttendanceForLecture($student_id, $lecture_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE student_id = ? AND lecture_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $student_id);
        $stmt->bindParam(2, $lecture_id);
        $stmt->execute();

        return $stmt;
    }


    public function getByLecture($lecture_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
              WHERE lecture_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $lecture_id);
        $stmt->execute();

        return $stmt;
    }
}
?>