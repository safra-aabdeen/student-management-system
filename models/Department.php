<?php
// models/Department.php
require_once __DIR__ . '/../config/database.php';

class Department {
    // Database connection and table name
    private $conn;
    private $table_name = "departments";

    // Object properties
    public $department_id;
    public $department_code;
    public $department_name;
    public $total_years;
    public $semesters_per_year;
    public $created_at;
    public $updated_at;

    // Constructor with DB connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Read all departments
    public function readAll() {
        // Query to select all departments
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY department_id";

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    // Create new department
    public function create() {
        // Query to insert a new department
        $query = "INSERT INTO " . $this->table_name . "
                (department_code, department_name, total_years, semesters_per_year)
                VALUES
                (:department_code, :department_name, :total_years, :semesters_per_year)";

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->department_code = htmlspecialchars(strip_tags($this->department_code));
        $this->department_name = htmlspecialchars(strip_tags($this->department_name));
        $this->total_years = htmlspecialchars(strip_tags($this->total_years));
        $this->semesters_per_year = htmlspecialchars(strip_tags($this->semesters_per_year));

        // Bind parameters
        $stmt->bindParam(":department_code", $this->department_code);
        $stmt->bindParam(":department_name", $this->department_name);
        $stmt->bindParam(":total_years", $this->total_years);
        $stmt->bindParam(":semesters_per_year", $this->semesters_per_year);

        // Execute query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Check if department code already exists
    public function codeExists() {
        // Query to check if department code exists
        $query = "SELECT department_id FROM " . $this->table_name . " 
                  WHERE department_code = :department_code LIMIT 1";

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->department_code = htmlspecialchars(strip_tags($this->department_code));

        // Bind parameter
        $stmt->bindParam(":department_code", $this->department_code);

        // Execute query
        $stmt->execute();

        // Get number of rows
        $num = $stmt->rowCount();

        // Return true if department code exists
        return $num > 0;
    }

    // Update department
    public function update() {
        // Query to update a department
        $query = "UPDATE " . $this->table_name . "
                SET
                    department_code = :department_code,
                    department_name = :department_name,
                    total_years = :total_years,
                    semesters_per_year = :semesters_per_year
                WHERE
                    department_id = :department_id";

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->department_id = htmlspecialchars(strip_tags($this->department_id));
        $this->department_code = htmlspecialchars(strip_tags($this->department_code));
        $this->department_name = htmlspecialchars(strip_tags($this->department_name));
        $this->total_years = htmlspecialchars(strip_tags($this->total_years));
        $this->semesters_per_year = htmlspecialchars(strip_tags($this->semesters_per_year));

        // Bind parameters
        $stmt->bindParam(":department_id", $this->department_id);
        $stmt->bindParam(":department_code", $this->department_code);
        $stmt->bindParam(":department_name", $this->department_name);
        $stmt->bindParam(":total_years", $this->total_years);
        $stmt->bindParam(":semesters_per_year", $this->semesters_per_year);

        // Execute query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Delete department
    public function delete() {
        // Query to delete a department
        $query = "DELETE FROM " . $this->table_name . " WHERE department_id = :department_id";

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->department_id = htmlspecialchars(strip_tags($this->department_id));

        // Bind parameter
        $stmt->bindParam(":department_id", $this->department_id);

        // Execute query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Read one department
    public function readOne() {
        // Query to read single department
        $query = "SELECT * FROM " . $this->table_name . " 
              WHERE department_id = :department_id 
              LIMIT 0,1";

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->department_id = htmlspecialchars(strip_tags($this->department_id));

        // Bind parameter
        $stmt->bindParam(":department_id", $this->department_id);

        // Execute query
        $stmt->execute();

        // Fetch row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // If department exists, populate object properties
        if($row) {
            $this->department_code = $row['department_code'];
            $this->department_name = $row['department_name'];
            $this->total_years = $row['total_years'];
            $this->semesters_per_year = $row['semesters_per_year'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }

        return false;
    }
}
?>