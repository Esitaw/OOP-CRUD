<?php
class Database {
    private $host = "localhost";
    private $db_name = "oop_crud";
    private $username = "root";
    private $password = "";
    protected $conn;

    public function __construct() {
        try {
            $this->conn = new PDO("mysql:host={$this->host};dbname={$this->db_name}", 
                                  $this->username, 
                                  $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database Connection Failed: " . $e->getMessage());
        }
    }

    public function create($table, $data) {
        $fields = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));
        $sql = "INSERT INTO $table ($fields) VALUES ($placeholders)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($data);
    }

    public function read($table) {
        $stmt = $this->conn->prepare("SELECT * FROM $table");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($table, $data, $id) {
        $fields = "";
        foreach ($data as $key => $value) {
            $fields .= "$key = :$key, ";
        }
        $fields = rtrim($fields, ", ");
        $sql = "UPDATE $table SET $fields WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $data['id'] = $id;
        return $stmt->execute($data);
    }

    public function delete($table, $id) {
        $sql = "DELETE FROM $table WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}

class Student extends Database {
    protected $table = "students";
    
    public function addStudent($data) {
        return $this->create($this->table, $data);
    }

    public function getStudents() {
        return $this->read($this->table);
    }

    public function updateStudent($data, $id) {
        return $this->update($this->table, $data, $id);
    }

    public function deleteStudent($id) {
        return $this->delete($this->table, $id);
    }
}

class Attendance extends Database {
    protected $table = "attendance";

    public function addAttendance($data) {
        return $this->create($this->table, $data);
    }

    public function getAttendance() {
        return $this->read($this->table);
    }

    public function updateAttendance($data, $id) {
        return $this->update($this->table, $data, $id);
    }

    public function deleteAttendance($id) {
        return $this->delete($this->table, $id);
    }
}

$student = new Student();
$attendance = new Attendance();

if (isset($_POST['add_student'])) {
    $student->addStudent(['name' => $_POST['name'], 'email' => $_POST['email']]);
}
if (isset($_POST['update_student'])) {
    $student->updateStudent(['name' => $_POST['name'], 'email' => $_POST['email']], $_POST['id']);
}
if (isset($_GET['delete_student'])) {
    $student->deleteStudent($_GET['delete_student']);
}

if (isset($_POST['add_attendance'])) {
    $attendance->addAttendance(['student_id' => $_POST['student_id'], 'status' => $_POST['status']]);
}
if (isset($_GET['delete_attendance'])) {
    $attendance->deleteAttendance($_GET['delete_attendance']);
}

$students = $student->getStudents();
$attendances = $attendance->getAttendance();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student & Attendance CRUD</title>
</head>
<body>
    <h2>Student Management</h2>
    <form method="post">
        <input type="hidden" name="id" value="">
        <input type="text" name="name" placeholder="Student Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <button type="submit" name="add_student">Add Student</button>
    </form>

    <h3>Student List</h3>
    <table border="1" cellpadding="5">
        <tr><th>ID</th><th>Name</th><th>Email</th><th>Action</th></tr>
        <?php foreach ($students as $s): ?>
        <tr>
            <td><?= $s['id'] ?></td>
            <td><?= $s['name'] ?></td>
            <td><?= $s['email'] ?></td>
            <td>
                <a href="?delete_student=<?= $s['id'] ?>">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h2>Attendance Management</h2>
    <form method="post">
        <select name="student_id" required>
            <option value="">Select Student</option>
            <?php foreach ($students as $s): ?>
                <option value="<?= $s['id'] ?>"><?= $s['name'] ?></option>
            <?php endforeach; ?>
        </select>
        <select name="status" required>
            <option value="Present">Present</option>
            <option value="Absent">Absent</option>
        </select>
        <button type="submit" name="add_attendance">Add Attendance</button>
    </form>

    <h3>Attendance Records</h3>
    <table border="1" cellpadding="5">
        <tr><th>ID</th><th>Student ID</th><th>Status</th><th>Action</th></tr>
        <?php foreach ($attendances as $a): ?>
        <tr>
            <td><?= $a['id'] ?></td>
            <td><?= $a['student_id'] ?></td>
            <td><?= $a['status'] ?></td>
            <td><a href="?delete_attendance=<?= $a['id'] ?>">Delete</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
