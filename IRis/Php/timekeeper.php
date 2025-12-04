<?php
session_start();
date_default_timezone_set("Asia/Manila");

// ✅ Database Connection
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "rocelenrj_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Get today's date
$today = date("Y-m-d");

// Handle search
$search = "";
if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
}

// Handle actions (checkin, checkout, absent, logout)
if (isset($_POST['action'])) {
    $id = intval($_POST['id']);
    $time = date("H:i:s");

    switch($_POST['action']) {
        case "checkin":
            $sql = "SELECT * FROM attendance WHERE employee_id=$id AND date='$today'";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                $conn->query("UPDATE attendance SET checkin_time='$time', status='Present' WHERE employee_id=$id AND date='$today'");
            } else {
                $conn->query("INSERT INTO attendance (employee_id, date, checkin_time, status) VALUES ($id, '$today', '$time', 'Present')");
            }
            break;

        case "checkout":
            $conn->query("UPDATE attendance SET checkout_time='$time', status='Checked Out' WHERE employee_id=$id AND date='$today'");
            break;

        case "absent":
            $sql = "SELECT * FROM attendance WHERE employee_id=$id AND date='$today'";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                $conn->query("UPDATE attendance SET status='Absent', checkin_time=NULL, checkout_time=NULL WHERE employee_id=$id AND date='$today'");
            } else {
                $conn->query("INSERT INTO attendance (employee_id, date, status) VALUES ($id, '$today', 'Absent')");
            }
            break;

        case "logout":
            session_destroy();
            header("Location: login.php");
            exit;
            case "leave_request":
            $days = max(1, intval($_POST['days']));   // at least 1 day
            $reason = $conn->real_escape_string($_POST['reason'] ?? 'Leave Request');
            // Simple request using today as start date; adjust as needed
            $start_date = $today;
            $end_date   = date('Y-m-d', strtotime("+".($days-1)." day", strtotime($start_date)));

            // Insert into leave_requests table (make sure columns match your schema)
            $conn->query("
                INSERT INTO leave_requests (employee_id, type, start_date, end_date, days, status)
                VALUES ($id, '$reason', '$start_date', '$end_date', $days, 'Pending')
            ");
            break;
    }
}

// Fetch employees + today's attendance
$sql = "SELECT e.id, e.name, e.position, e.department, e.site,
               IFNULL(a.checkin_time, '-') AS checkin_time,
               IFNULL(a.checkout_time, '-') AS checkout_time,
               IFNULL(a.status, 'Not Checked In') AS status
        FROM employees e
        LEFT JOIN attendance a ON e.id=a.employee_id AND a.date='$today'";

if (!empty($search)) {
    $sql .= " WHERE e.name LIKE '%$search%'";
}

$employees = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Time Keeper Dashboard</title>

<style>
body { font-family: Arial, sans-serif; background: #f7fdf9; margin:0; padding:0; }
header { background:#040f2a; color:white; padding:15px 30px; display:flex; justify-content:space-between; align-items:center; }
.LOGO { display: flex; align-items: center; gap: 14px; }
      .LOGO img { width: 150px; height: 48px; object-fit: contain; }
header h1 { margin:0; font-size:30px; color:#e5c392;}
header .buttons form { display:inline; }
header .buttons button { margin-left:850px; background:#e5c392; color:black; border:none; padding:8px 15px; border-radius:5px; cursor:pointer; }
.container { padding:20px 40px; }
.card { background:white;  padding:20px; border-radius:10px; margin-bottom:20px; box-shadow:0 2px 6px rgba(0,0,0,0.1); }

h2{color:#c69046ff;}

table { width:100%; border-collapse:collapse; margin-top:15px; }
table th, table td { padding:10px; text-align:left; border-bottom:1px solid #ddd; }
.status { padding:5px 10px; border-radius:5px; font-size:12px; color:white; }
.not-checked { background:#d9534f; }
.checked-in { background:#28a745; }
.checked-out { background:#17a2b8; }
.absent { background:#6c757d; }
.btn { border:none; padding:6px 12px; color:white; border-radius:5px; cursor:pointer; margin-right:5px; }
.btn-checkin { background:#28a745; }
.btn-checkin:hover{background: #61c478ff}
.btn-checkout { background:#17a2b8; }
.btn-checkout:hover{background: #4ca2afff;}
.btn-absent:hover{background: #c2525dff}
.btn-absent { background:#dc3545; }
.btn-outline { border:1px solid #41639dff; background:#2d5996ff; color:#fff; padding:6px 12px; border-radius:5px; cursor:pointer; }
.btn-outline:hover { background: #5273a1ff; }
.top-boxes { display:flex; justify-content:space-between; margin-bottom:20px; }
.top-box { background:white; padding:15px 20px; border-radius:10px; box-shadow:0 2px 6px rgba(0,0,0,0.1); flex:1; margin-right:15px; }
.top-box:last-child { margin-right:0; }
.search-bar { margin-bottom:15px; }
.search-bar input { padding:6px; width:250px; border-radius:5px; border:1px solid #ccc; }
.search-bar button { padding:6px 12px; border:none; border-radius:5px; background:#00a651; color:white; cursor:pointer; }
.submit { border:1px solid #003da6ff; background:#2d5996ff; color:#fff; padding:6px 12px; border-radius:5px; cursor:pointer; }
.submit:hover{ background: #5273a1ff; }

.dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-btn {
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
    padding: 5px;
}

.dropdown-content {
    display: none;
    position: absolute;
    background-color: white;
    min-width: 140px;
    box-shadow: 0px 4px 10px rgba(0,0,0,0.2);
    z-index: 10;
    border-radius: 6px;
}

.dropdown-content a {
    padding: 10px;
    display: block;
    text-decoration: none;
    color: black;
    font-size: 14px;
}

.dropdown-content a:hover {
    background-color: #f1f1f1;
}

.dropdown:hover .dropdown-content {
    display: block;
}

</style>
<!-- ✅ Include jsPDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>
<body>
<header>
  <div class="LOGO">
  <img src="../Capstone pics/LOGO3.jpg" alt="Company Logo">
<h1>Time Keeper Dashboard </h1>

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

    <!-- Search bar -->
    <form method="get" class="search-bar">
      <input type="text" name="search" placeholder="Search employee..." value="<?= htmlspecialchars($search) ?>">
      <button type="submit">Search</button>
    </form>

    <!-- Export PDF button -->
    <button class="btn btn-outline" onclick="exportEmployeesPDF()">Export Data</button>

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

  <!-- New Request Leave button goes here -->
  <button class="btn btn-outline"
          onclick="openLeavePrompt(<?= $emp['id'] ?>, '<?= htmlspecialchars($emp['name'], ENT_QUOTES) ?>')">
    Request Leave
  </button>

  <div class="dropdown">
    <button class="dropdown-btn">⋮</button>
    <div class="dropdown-content">
      <a href="edit.php?id=<?= $emp['id'] ?>">Edit</a>
      <a href="#" onclick="confirmDelete(<?= $emp['id'] ?>)">Delete</a>
      <a href="view.php?id=<?= $emp['id'] ?>">View Profile</a>
    </div>
  </div>
</td>

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
  const optionsDate = { weekday:'long', year:'numeric', month:'long', day:'numeric' };
  document.getElementById("liveDate").innerText = now.toLocaleDateString("en-US", optionsDate);
  document.getElementById("liveClock").innerText = now.toLocaleTimeString("en-US");
}
setInterval(updateClock, 1000);
updateClock();

// ✅ Browser-based PDF export using jsPDF
async function exportEmployeesPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    doc.setFontSize(16);
    doc.text("Employee Attendance Report", 105, 15, { align: "center" });
    doc.setFontSize(12);

    const headers = [["Employee","Position","Department","Site","Check-In","Check-Out","Status"]];
    const rows = [];

    document.querySelectorAll("table tbody tr").forEach(tr => {
        const row = [];
        tr.querySelectorAll("td").forEach((td, index) => {
            if (index < 7) row.push(td.innerText);
        });
        rows.push(row);
    });

    doc.autoTable({
        head: headers,
        body: rows,
        startY: 25,
        styles: { fontSize: 10 },
        headStyles: { fillColor: [0, 166, 81] },
    });

    doc.save("attendance_report.pdf");
}

function confirmDelete(id) {
    if (confirm("Are you sure you want to delete this employee?")) {
        window.location.href = "delete.php?id=" + id;
    }
}

function openLeavePrompt(empId, empName) {
    const days = prompt("How many days of leave for " + empName + "?", "1");
    if (!days) return;

    const parsed = parseInt(days, 10);
    if (isNaN(parsed) || parsed < 1) {
        alert("Please enter a valid number of days (1 or more).");
        return;
    }

    const reason = prompt("Optional: Leave type or reason (e.g., Sick Leave, Vacation)", "Vacation Leave") || "Leave Request";

    const form = document.createElement("form");
    form.method = "post";
    form.style.display = "none";

    const idInput = document.createElement("input");
    idInput.name = "id";
    idInput.value = empId;
    form.appendChild(idInput);

    const actionInput = document.createElement("input");
    actionInput.name = "action";
    actionInput.value = "leave_request";
    form.appendChild(actionInput);

    const daysInput = document.createElement("input");
    daysInput.name = "days";
    daysInput.value = parsed;
    form.appendChild(daysInput);

    const reasonInput = document.createElement("textarea");
    reasonInput.name = "reason";
    reasonInput.textContent = reason;
    form.appendChild(reasonInput);

    document.body.appendChild(form);
    form.submit();
}
</script>

<!-- jsPDF AutoTable plugin -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
</body>
</html>

