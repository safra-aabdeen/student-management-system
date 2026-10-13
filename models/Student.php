<?php
// models/Student.php

class Student {
    private $conn;
    private $table_name = "students";

    // student properties
    public $student_id;
    public $student_number;
    public $first_name;
    public $last_name;
    public $email;
    public $phone;
    public $address;
    public $dob;
    public $gender;
    public $department_id;
    public $enrollment_year;
    public $enrollment_type; // F for full-time, P for part-time
    public $current_semester_id;
    public $status; // Active, Inactive, Graduated, etc.
    public $created_at;
    public $updated_at;

    // constructor
    public function __construct($db) {
        $this->conn = $db;
    }

    // generate student number
    public function generateStudentNumber($department_code, $enrollment_year, $enrollment_type) {
        // Format: KUR/IT/2324/F/0001
        // KUR is constant
        // IT is department code
        // 2324 is academic year (e.g., 2023-2024)
        // F is enrollment type (F for full-time, P for part-time)
        // 0001 is a unique sequential number

        // Get the next sequential number
        $query = "SELECT MAX(SUBSTRING_INDEX(student_number, '/', -1)) as max_num 
                  FROM " . $this->table_name . " 
                  WHERE student_number LIKE 'KUR/{$department_code}/{$enrollment_year}/{$enrollment_type}/%'";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $next_num = 1;
        if ($row && $row['max_num']) {
            $next_num = intval($row['max_num']) + 1;
        }

        // Format the sequential number with leading zeros
        $seq_num = str_pad($next_num, 4, '0', STR_PAD_LEFT);

        // Construct the student number
        return "KUR/{$department_code}/{$enrollment_year}/{$enrollment_type}/{$seq_num}";
    }

    // create new student
    public function create() {
        // Query to insert a new student
        $query = "INSERT INTO " . $this->table_name . "
                  (student_number, first_name, last_name, email, phone, address, dob, gender, 
                   department_id, enrollment_year, enrollment_type, status) 
                  VALUES
                  (:student_number, :first_name, :last_name, :email, :phone, :address, :dob, :gender,
                   :department_id, :enrollment_year, :enrollment_type, :status)";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Sanitize data
        $this->student_number = htmlspecialchars(strip_tags($this->student_number));
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->dob = htmlspecialchars(strip_tags($this->dob));
        $this->gender = htmlspecialchars(strip_tags($this->gender));
        $this->enrollment_year = htmlspecialchars(strip_tags($this->enrollment_year));
        $this->enrollment_type = htmlspecialchars(strip_tags($this->enrollment_type));
        $this->status = htmlspecialchars(strip_tags($this->status));

        // Bind data
        $stmt->bindParam(":student_number", $this->student_number);
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":dob", $this->dob);
        $stmt->bindParam(":gender", $this->gender);
        $stmt->bindParam(":department_id", $this->department_id);
        $stmt->bindParam(":enrollment_year", $this->enrollment_year);
        $stmt->bindParam(":enrollment_type", $this->enrollment_type);
        $stmt->bindParam(":status", $this->status);

        // Execute query
        if ($stmt->execute()) {
            $this->student_id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    // read all students
    public function readAll() {
        $query = "SELECT s.*, d.department_code, d.department_name 
                  FROM " . $this->table_name . " s
                  LEFT JOIN departments d ON s.department_id = d.department_id
                  ORDER BY s.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // read students by department
    public function readByDepartment($department_id) {
        $query = "SELECT s.*, d.department_code, d.department_name 
                  FROM " . $this->table_name . " s
                  LEFT JOIN departments d ON s.department_id = d.department_id
                  WHERE s.department_id = :department_id
                  ORDER BY s.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':department_id', $department_id);
        $stmt->execute();

        return $stmt;
    }

    // Read a single student by ID
    public function readOneStudent() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE student_id = ? LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->student_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            return $row;
        }

        return false;
    }

    // get students by semester
    public function getStudentsBySemester($semester_id) {
        // Get students currently enrolled in this semester
        $query = "SELECT s.* 
              FROM " . $this->table_name . " s
              WHERE s.current_semester_id = :semester_id
              AND s.status = 'Active'
              ORDER BY s.last_name, s.first_name";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':semester_id', $semester_id);
        $stmt->execute();

        return $stmt;
    }

    // read one student
    public function readOne() {
        $query = "SELECT s.*, d.department_code, d.department_name 
                  FROM " . $this->table_name . " s
                  LEFT JOIN departments d ON s.department_id = d.department_id
                  WHERE s.student_id = :student_id
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_id', $this->student_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->student_number = $row['student_number'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->email = $row['email'];
            $this->phone = $row['phone'];
            $this->address = $row['address'];
            $this->dob = $row['dob'];
            $this->gender = $row['gender'];
            $this->department_id = $row['department_id'];
            $this->enrollment_year = $row['enrollment_year'];
            $this->enrollment_type = $row['enrollment_type'];
            $this->current_semester_id = $row['current_semester_id'];
            $this->status = $row['status'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];

            return true;
        }

        return false;
    }

    // update student
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET first_name = :first_name,
                      last_name = :last_name,
                      email = :email,
                      phone = :phone,
                      address = :address,
                      dob = :dob,
                      gender = :gender,
                      status = :status
                  WHERE student_id = :student_id";

        $stmt = $this->conn->prepare($query);

        // Sanitize data
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->dob = htmlspecialchars(strip_tags($this->dob));
        $this->gender = htmlspecialchars(strip_tags($this->gender));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->student_id = htmlspecialchars(strip_tags($this->student_id));

        // Bind data
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":dob", $this->dob);
        $stmt->bindParam(":gender", $this->gender);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":student_id", $this->student_id);

        // Execute query
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // delete student
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE student_id = :student_id";

        $stmt = $this->conn->prepare($query);
        $this->student_id = htmlspecialchars(strip_tags($this->student_id));
        $stmt->bindParam(':student_id', $this->student_id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // search students
    public function search($keywords) {
        $query = "SELECT s.*, d.department_code, d.department_name 
                  FROM " . $this->table_name . " s
                  LEFT JOIN departments d ON s.department_id = d.department_id
                  WHERE s.student_number LIKE :keywords
                  OR s.first_name LIKE :keywords
                  OR s.last_name LIKE :keywords
                  OR s.email LIKE :keywords
                  ORDER BY s.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $keywords = htmlspecialchars(strip_tags($keywords));
        $keywords = "%{$keywords}%";
        $stmt->bindParam(':keywords', $keywords);
        $stmt->execute();

        return $stmt;
    }
}
?>