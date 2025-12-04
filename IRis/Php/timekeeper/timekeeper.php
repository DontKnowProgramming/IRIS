<?php
session_start();
date_default_timezone_set("Asia/Manila");
include "config.php"; // DB connection file

// Get today's date
$today = date("Y-m-d");

// Handle actions
if (isset($_POST['action'])) {
    $id = intval($_POST['id']);
    $time = date("H:i:s");

    if ($_POST['action'] == "checkin") {
        // Insert or update attendance record
        $sql = "SELECT * FROM attendance WHERE employee_id=$id AND date='$today'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $conn->query("UPDATE attendance SET checkin_time='$time', status='Present' WHERE employee_id=$id AND date='$today'");
        } else {
            $conn->query("INSERT INTO attendance (employee_id, date, checkin_time, status) VALUES ($id, '$today', '$time', 'Present')");
        }
    }

    if ($_POST['action'] == "checkout") {
        $conn->query("UPDATE attendance SET checkout_time='$time', status='Checked Out' WHERE employee_id=$id AND date='$today'");
    }

    if ($_POST['action'] == "absent") {
        $sql = "SELECT * FROM attendance WHERE employee_id=$id AND date='$today'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $conn->query("UPDATE attendance SET status='Absent', checkin_time=NULL, checkout_time=NULL WHERE employee_id=$id AND date='$today'");
        } else {
            $conn->query("INSERT INTO attendance (employee_id, date, status) VALUES ($id, '$today', 'Absent')");
        }
    }

    if ($_POST['action'] == "logout") {
        session_destroy();
        header("Location: timekeeper.php");
        exit;
    }

    if ($_POST['action'] == "export") {
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment;filename=attendance_report.csv");
        $output = fopen("php://output", "w");
        fputcsv($output, ["Employee", "Position", "Department", "Site", "Check-In", "Check-Out", "Status"]);

        $sql = "SELECT e.name, e.position, e.department, e.site, a.checkin_time, a.checkout_time, a.status 
                FROM employees e 
                LEFT JOIN attendance a ON e.id=a.employee_id AND a.date='$today'";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }
}

// Fetch employees + today's attendance
$sql = "SELECT e.id, e.name, e.position, e.department, e.site,
               IFNULL(a.checkin_time, '-') AS checkin_time,
               IFNULL(a.checkout_time, '-') AS checkout_time,
               IFNULL(a.status, 'Not Checked In') AS status
        FROM employees e
        LEFT JOIN attendance a ON e.id=a.employee_id AND a.date='$today'";
$employees = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Time Keeper Dashboard</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f7fdf9; margin: 0; padding: 0; }
    header { background: #e5c392; color: #e5c392; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
    header h1 { margin: 0; font-size: 20px; color: #e5c392;}
    header .buttons form { display: inline; }
    header .buttons button {
  background-color: #e5c392 !important;
  color: #ffffff !important;
  border: none;
  padding: 8px 15px;
  border-radius: 5px;
  cursor: pointer;
}

    .container { padding: 20px 40px; }
    .card { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    table th, table td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
    .status { padding: 5px 10px; border-radius: 5px; font-size: 12px; color: white; }
    .not-checked { background: #d9534f; }
    .checked-in { background: #28a745; }
    .checked-out { background: #17a2b8; }
    .absent { background: #6c757d; }
    .btn { border: none; padding: 6px 12px; color: white; border-radius: 5px; cursor: pointer; margin-right: 5px; }
    .btn-checkin { background: #28a745; }
    .btn-checkout { background: #17a2b8; }
    .btn-absent { background: #dc3545; }
    .top-boxes { display: flex; justify-content: space-between; margin-bottom: 20px; }
    .top-box { background: white; padding: 15px 20px; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); flex: 1; margin-right: 15px; }
    .top-box:last-child { margin-right: 0; }
    
  </style>
</head>
<body>
  <header>
    <h1>Time Keeper Dashboard <br><small>Rocelyn RJ Building Trades Inc</small></h1>
    <div class="buttons">
      <form method="post"><button name="action" value="logout">Logout</button></form>

    </div>
  </header>

  <div class="container">
    <div class="top-boxes">
      <div class="top-box">
        <strong>Today's Date</strong><br>
        <span id="liveDate"></span>
      </div>
      <div class="top-box">
        <strong>Current Time</strong><br>
        <span id="liveClock"></span>
      </div>
    </div>

    <div class="card">
      <h2>Employee Attendance Tracking</h2>
      <p>Monitor check-ins, check-outs, and attendance status for all employees</p>
      <form method="post" style="margin-bottom:10px;">
        <button name="action" value="export">Export Report</button>
      </form>

      <table>
        <thead>
          <tr>
            <th>Employee</th>
            <th>Position</th>
            <th>Department</th>
            <th>Site</th>
            <th>Check-In Time</th>
            <th>Check-Out Time</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php while($emp = $employees->fetch_assoc()): ?>
          <tr>
            <td><strong><?= $emp['name'] ?></strong></td>
            <td><?= $emp['position'] ?></td>
            <td><?= $emp['department'] ?></td>
            <td><?= $emp['site'] ?></td>
            <td><?= $emp['checkin_time'] ?></td>
            <td><?= $emp['checkout_time'] ?></td>
            <td>
              <span class="status 
                <?= $emp['status']=="Not Checked In"?'not-checked':'' ?>
                <?= $emp['status']=="Present"?'checked-in':'' ?>
                <?= $emp['status']=="Checked Out"?'checked-out':'' ?>
                <?= $emp['status']=="Absent"?'absent':'' ?>
              "><?= $emp['status'] ?></span>
            </td>
            <td>
              <form method="post" style="display:inline;">
                <input type="hidden" name="id" value="<?= $emp['id'] ?>">
                <button class="btn btn-checkin" name="action" value="checkin">Check In</button>
              </form>
              <form method="post" style="display:inline;">
                <input type="hidden" name="id" value="<?= $emp['id'] ?>">
                <button class="btn btn-checkout" name="action" value="checkout">Check Out</button>
              </form>
              <form method="post" style="display:inline;">
                <input type="hidden" name="id" value="<?= $emp['id'] ?>">
                <button class="btn btn-absent" name="action" value="absent">Mark Absent</button>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    function updateClock() {
      const now = new Date();
      const optionsDate = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
      document.getElementById("liveDate").innerText = now.toLocaleDateString("en-US", optionsDate);
      document.getElementById("liveClock").innerText = now.toLocaleTimeString("en-US");
    }
    setInterval(updateClock, 1000);
    updateClock();
  </script>
</body>
</html>
