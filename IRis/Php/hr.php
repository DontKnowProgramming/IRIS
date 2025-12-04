<?php
// hr_dashboard.php
// --- DB Connection ---
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "rocelenrj_db";
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// ----------------------
// POST HANDLERS
// ----------------------

// 1) Add Employee (and create initial attendance + leave request)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_employee'])) {
    $name = $_POST['name'];
    $position = $_POST['position'];
    $department = $_POST['department'];
    $site = $_POST['site'];
    $salary = $_POST['salary'];
    $status = $_POST['status'];
    $hire_date = $_POST['hire_date'];

    $stmt = $conn->prepare("INSERT INTO employees (name, position, department, site, salary, status, hire_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $name, $position, $department, $site, $salary, $status, $hire_date);
    if ($stmt->execute()) {
        $new_employee_id = $conn->insert_id;

        // Insert a default attendance row (so attendance table has an entry if desired)
        // NOTE: timekeeper.php should be the authoritative source of attendance records.
       $check_time = date('H:i:s');

            $att_stmt = $conn->prepare("
                INSERT INTO attendance (employee_id, date, checkin_time, checkout_time, status)
                VALUES (?, CURDATE(), ?, NULL, ?)
            ");

            $default_status = "Present";
            $att_stmt->bind_param("iss", $new_employee_id, $check_time, $default_status);
            $att_stmt->execute();


        // Insert a default pending leave request for the new employee (optional)
        $leave_type = "Initial Pending Request";
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d');
        $days = 1;
        $leave_status = "Pending";
        $lstmt = $conn->prepare("INSERT INTO leave_requests (employee_id, type, start_date, end_date, days, status) VALUES (?, ?, ?, ?, ?, ?)");
        $lstmt->bind_param("isssis", $new_employee_id, $leave_type, $start_date, $end_date, $days, $leave_status);
        $lstmt->execute();
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// 2) Update (Edit) Employee
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_employee_id'])) {
    $id = (int)$_POST['update_employee_id'];
    $name = $_POST['edit_name'];
    $position = $_POST['edit_position'];
    $department = $_POST['edit_department'];
    $site = $_POST['edit_site'];
    $salary = $_POST['edit_salary'];
    $status = $_POST['edit_status'];
    $hire_date = $_POST['edit_hire_date'];

    $stmt = $conn->prepare("UPDATE employees SET name=?, position=?, department=?, site=?, salary=?, status=?, hire_date=? WHERE id=?");
    $stmt->bind_param("sssssssi", $name, $position, $department, $site, $salary, $status, $hire_date, $id);
    $stmt->execute();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// 3) Delete Employee (and related attendance & leave entries)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_employee'])) {
    $id = (int)$_POST['delete_employee'];

    // Delete related attendance rows
    $d1 = $conn->prepare("DELETE FROM attendance WHERE employee_id = ?");
    $d1->bind_param("i", $id);
    $d1->execute();

    // Delete related leave requests
    $d2 = $conn->prepare("DELETE FROM leave_requests WHERE employee_id = ?");
    $d2->bind_param("i", $id);
    $d2->execute();

    // Delete employee
    $d3 = $conn->prepare("DELETE FROM employees WHERE id = ?");
    $d3->bind_param("i", $id);
    $d3->execute();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// 4) Handle leave approve/reject (kept from your original)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['leave_action'], $_POST['leave_id'])) {
    $new_status = $_POST['leave_action'] === 'approve' ? 'Approved' : 'Rejected';
    $stmt = $conn->prepare("UPDATE leave_requests SET status=? WHERE id=?");
    $stmt->bind_param("si", $new_status, $_POST['leave_id']);
    $stmt->execute();
}

// ----------------------
// Fetch data for display
// ----------------------

// Employees
$employees = [];
$res = $conn->query("SELECT * FROM employees ORDER BY id DESC");
while ($row = $res->fetch_assoc()) $employees[] = $row;

// Attendance: show today's attendance (so timekeeper.php inserts will show here)
$attendance = [];
$sql = "SELECT a.id, a.employee_id, e.name, e.department, e.site,
               a.checkin_time, a.checkout_time, a.status
        FROM attendance a 
        JOIN employees e ON e.id = a.employee_id
        WHERE a.date = CURDATE()
        ORDER BY a.checkin_time ASC";

$res2 = $conn->query($sql);
while ($row = $res2->fetch_assoc()) $attendance[] = $row;

// Leave requests
$leave_reqs = [];
$sql = "SELECT lr.*, e.name FROM leave_requests lr JOIN employees e ON lr.employee_id = e.id ORDER BY lr.id DESC";
$res3 = $conn->query($sql);
while ($row = $res3->fetch_assoc()) $leave_reqs[] = $row;

// Dashboard summary
$total_emp = count($employees);
$active_emp = 0;
foreach ($employees as $emp) if ($emp['status'] === 'Active') $active_emp++;
$pending_req = 0;
foreach ($leave_reqs as $leave) if ($leave['status'] === 'Pending') $pending_req++;
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HR Dashboard</title>
  
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background: #fff; }
        .topbar { background: #040f2a; color: #e5c392; padding: 15px 16px; display:flex; align-items:center; justify-content:space-between;}
        .topbar img, .topbar .icon-img { height:40px; }
        .summary-cards { display:flex; gap:24px; justify-content:center; margin:30px 0 16px; }
        .summary-card { flex:1; background:#fff; border-radius:15px; box-shadow: 0 2px 8px rgba(7,163,60,0.10); padding:22px 0; text-align:center;}
        .tabs{ display:flex; align-items:center; background:#ebeaea; border-radius:14px; margin-bottom:14px;}
        .tab{ flex:1; padding:12px 10px; text-align:center; border:none; background:none; font-size:1.06rem; font-weight:600; color:#555; border-radius:14px;}
        .tab.active{ background:#fff; color:#d7a662ff; }
        .section-card{ background:#fff; color: #c69046ff; border-radius:18px; padding:24px; margin-top:22px; box-shadow:0 1px 6px rgba(7,163,60,0.08);}
        .badge-site{ background:#2d5996ff; color:#fff; font-size:.98rem; padding:5px 14px; border-radius:9px; }
        .badge-status{ padding:6px 18px; font-size:1.02rem; border-radius:12px; background:green; color:#fff; }
        .btn-blue{ background:#2d5996ff; color:#fff; font-weight:600; border-radius:22px; }
        .btn-outline{ color:#fff; background:#2d5996ff; border-radius:22px; }
        .btn-success{ color:black; background:#e5c392; }
        .btn-success2{ color:white; background:green; }
        .btn-success:hover { background: #c6b6a1ff; }
             .status-present {
            background-color: #28a745; /* same as Check In */    }

            .status-leave {
            background-color: #0d6efd; /* same as Request Leave */
            }

            .status-absent {
            background-color: #dc3545; /* same as Mark Absent OR your green if you prefer */
            }
            
           .status-checkout{
            background-color: #17a2b8; /* same as Mark Checkout OR your green if you prefer */
            }
    </style>
</head>
<body>
    
    <div class="topbar mb-4">
        <div class="d-flex align-items-center gap-3">
            <a href="login.php" class="btn btn-success">&larr; </a>
            <img src="../Capstone pics/LOGO3.jpg" class="icon-img" />
            
            <div>
                <span style="font-size:2rem;font-weight:700;line-height:1;">HR Dashboard</span>
                <div class="label">Human Resources Management</div>
            </div>
       <div id="datetime-indicator" style="font-size:1.1rem;font-weight:500;margin-left:750px;"></div>
        </div>
        
    </div>  
     

    <div class="container">
        <div class="summary-cards">
            <div class="summary-card"><h1><?= $total_emp ?></h1><span>Total Employees</span></div>
            <div class="summary-card"><h1><?= $active_emp ?></h1><span>Active Employees</span></div>
            <div class="summary-card"><h1 style='color:#ff7000'><?= $pending_req ?></h1><span>Pending Requests</span></div>
        </div>

            <div class="tabs">
                <button class="tab active" onclick="showTab('employees')">Employees</button>
                <button class="tab" onclick="showTab('attendance')">Attendance</button>
                <button class="tab" onclick="showTab('leave')">Leave Requests</button>
            </div>

        <!-- EMPLOYEES -->
        <div id="employees" class="section-card tab-content active">
            <div class="d-flex justify-content-between mb-3">
                <div>
                    <h5 class="mb-0">Employee Management</h5>
                    <div style="color:#888;">View and manage employee records</div>
                </div>
                
                <div>
                    <button class="btn btn-blue me-2" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">Add New Employee</button>
                    <button class="btn btn-outline" onclick="exportEmployeesPDF()">Export Data</button>
                </div>
            </div>

            <input id="searchInput" class="form-control mb-3" placeholder="Search by name, position, department, or site..." />

            <table id="employeesTable" class="table table-bordered align-middle mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Position</th>
                        <th>Department</th>
                        <th>Site</th>
                        <th>Salary</th>
                        <th>Status</th>
                        <th>Hire Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($employees as $emp): ?>
                    <tr>
                        <td><?= htmlspecialchars($emp['name']) ?></td>
                        <td><?= htmlspecialchars($emp['position']) ?></td>
                        <td><?= htmlspecialchars($emp['department']) ?></td>
                        <td><span class="badge-site"><?= htmlspecialchars($emp['site']) ?></span></td>
                        <td>₱<?= number_format($emp['salary'], 0) ?></td>
                        <td>
                            <?php if ($emp['status'] == 'Active'): ?>
                                <span class="badge-status">Active</span>
                            <?php else: ?>
                                <span class="badge-status" style="background:#ededf0;color:#222;"><?= htmlspecialchars($emp['status']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($emp['hire_date']) ?></td>
                        <td>
                            <!-- Edit button triggers modal and fills values via JS -->
                            <button
                                class="btn btn-sm btn-primary"
                                onclick='openEditModal(
                                    <?= (int)$emp["id"] ?>,
                                    <?= json_encode($emp["name"]) ?>,
                                    <?= json_encode($emp["position"]) ?>,
                                    <?= json_encode($emp["department"]) ?>,
                                    <?= json_encode($emp["site"]) ?>,
                                    <?= json_encode($emp["salary"]) ?>,
                                    <?= json_encode($emp["status"]) ?>,
                                    <?= json_encode($emp["hire_date"]) ?>
                                )'
                            > Edit
                            </button>

                            <form method="POST" style="display:inline-block;margin-left:6px;" onsubmit="return confirm('Delete this employee and all related records?');">
                                <input type="hidden" name="delete_employee" value="<?= $emp['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>

        <!-- ATTENDANCE -->
        <div id="attendance" class="section-card tab-content" style="display:none;">
            <div class="d-flex justify-content-between mb-3">
                <div><h5 class="mb-0">Attendance Management</h5></div>
                <div><button class="btn btn-outline" onclick="exportAttendancePDF()">Export Data</button></div>
            </div>

            <input id="searchAttendanceInput" class="form-control mb-3" placeholder="Search by employee, department, site, status, or time..." />

            <table id="attendanceTable" class="table align-middle">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Site</th>
                        <th>Check-in Time</th>
                        <th>Check-out Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($attendance as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['department']) ?></td>
                        <td><span class="badge-site"><?= htmlspecialchars($row['site']) ?></span></td>
                        <td><?= htmlspecialchars(date("h:i:s A", strtotime($row['checkin_time']))) ?></td>

                                <td>
                                    <?= $row['checkout_time']
                                        ? htmlspecialchars(date("h:i:s A", strtotime($row['checkout_time'])))
                                        : '—' ?>
                                </td>

                                <td>
                                    <span class="badge-status
                                        <?= $row['status'] === 'Present' ? ' status-present' : '' ?>
                                        <?= $row['status'] === 'Absent' ? ' status-absent' : '' ?>
                                        <?= $row['status'] === 'On Leave' ? ' status-leave' : '' ?>
                                        <?= $row['status'] === 'Checked Out' ? ' status-checkout' : '' ?>">
                                        <?= htmlspecialchars($row['status']) ?>
                                    </span>
                                </td>


                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>

        <!-- LEAVE REQUESTS -->
        <div id="leave" class="section-card tab-content" style="display:none;">
            <h5>Leave Request Management</h5>
            <div style="color:#888;">Review and approve leave requests</div>
            <button class="btn btn-outline mt-2 mb-3" onclick="exportLeavePDF()">Export Leave Report</button>

            <table id="leaveTable" class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Leave Type</th>
                        <th>Dates</th>
                        <th>Days</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($leave_reqs as $req): ?>
                    <tr>
                        <td><b><?= htmlspecialchars($req['name']) ?></b></td>
                        <td><?= htmlspecialchars($req['type']) ?></td>
                        <td>
                            <?= date('M j', strtotime($req['start_date'])) ?>
                            <?= $req['start_date'] != $req['end_date'] ? '-' . date('j, Y', strtotime($req['end_date'])) : ', ' . date('Y', strtotime($req['start_date'])) ?>
                        </td>
                        <td><?= (int) $req['days'] ?></td>
                        <td>
                            <?php if ($req['status'] == 'Approved'): ?>
                                <span class="badge-status">Approved</span>
                            <?php elseif ($req['status'] == 'Rejected'): ?>
                                <span class="badge-status" style="background:#ed3240;">Rejected</span>
                            <?php else: ?>
                                <span class="badge-status" style="background:#e7e9f1;color:#222;"><?= htmlspecialchars($req['status']) ?></span>
                            <?php endif ?>
                        </td>
                        <td>
                            <?php if ($req['status'] == 'Pending'): ?>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="leave_id" value="<?= $req['id'] ?>">
                                    <button name="leave_action" value="approve" class="btn btn-success2 btn-sm">Approve</button>
                                    <button name="leave_action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                                </form>
                            <?php else: ?>
                                <button class="btn btn-outline btn-sm" disabled>View Details</button>
                            <?php endif ?>
                        </td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>

    </div>

    <!-- Add Employee Modal -->
    <div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3"><label>Full Name</label><input type="text" name="name" class="form-control" required></div>
                <div class="mb-3"><label>Position</label><input type="text" name="position" class="form-control" required></div>
                <div class="mb-3"><label>Department</label>
                    <select name="department" class="form-select" required>
                        <option value="">Select department</option>
                        <option>Construction</option>
                        <option>Management</option>
                        <option>Electrical</option>
                        <option>Design</option>
                        <option>Operations</option>
                    </select>
                </div>
                <div class="mb-3"><label>Monthly Salary</label><input type="number" name="salary" class="form-control" required></div>
                <div class="mb-3"><label>Status</label>
                    <select name="status" class="form-select" required>
                        <option>Active</option>
                        <option>On Leave</option>
                    </select>
                </div>
                <div class="mb-3"><label>Site Location</label>
                    <select name="site" class="form-select" required>
                        <option value="">Select site location</option>
                        <option>Taguig</option>
                        <option>Manila</option>
                        <option>Palawan</option>
                        <option>Cebu</option>
                    </select>
                </div>
                <div class="mb-3"><label>Hire Date</label><input type="date" name="hire_date" class="form-control" required></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="add_employee" class="btn btn-blue">Add Employee</button>
            </div>
        </form>
      </div>
    </div>

    <!-- Edit Employee Modal -->
    <div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <form method="POST" class="modal-content" id="editEmployeeForm">
            <input type="hidden" name="update_employee_id" id="update_employee_id">
            <div class="modal-header">
                <h5 class="modal-title">Edit Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3"><label>Full Name</label><input type="text" name="edit_name" id="edit_name" class="form-control" required></div>
                <div class="mb-3"><label>Position</label><input type="text" name="edit_position" id="edit_position" class="form-control" required></div>
                <div class="mb-3"><label>Department</label>
                    <select name="edit_department" id="edit_department" class="form-select" required>
                        <option>Construction</option>
                        <option>Management</option>
                        <option>Electrical</option>
                        <option>Design</option>
                        <option>Operations</option>
                    </select>
                </div>
                <div class="mb-3"><label>Monthly Salary</label><input type="number" name="edit_salary" id="edit_salary" class="form-control" required></div>
                <div class="mb-3"><label>Status</label>
                    <select name="edit_status" id="edit_status" class="form-select" required>
                        <option>Active</option>
                        <option>On Leave</option>
                    </select>
                </div>
                <div class="mb-3"><label>Site Location</label>
                    <select name="edit_site" id="edit_site" class="form-select" required>
                        <option>Taguig</option>
                        <option>Manila</option>
                        <option>Palawan</option>
                        <option>Cebu</option>
                    </select>
                </div>
                <div class="mb-3"><label>Hire Date</label><input type="date" name="edit_hire_date" id="edit_hire_date" class="form-control" required></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-blue">Save Changes</button>
            </div>
        </form>
      </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.7.0/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Tabs
        function showTab(tab) {
            document.querySelectorAll('.tab').forEach(button => button.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(div => div.style.display = 'none');
            if (tab === 'employees') {
                document.querySelectorAll('.tab')[0].classList.add('active');
                document.getElementById('employees').style.display = 'block';
            }
            if (tab === 'attendance') {
                document.querySelectorAll('.tab')[1].classList.add('active');
                document.getElementById('attendance').style.display = 'block';
            }
            if (tab === 'leave') {
                document.querySelectorAll('.tab')[2].classList.add('active');
                document.getElementById('leave').style.display = 'block';
            }
        }

        // Date/time indicator
        function updateDateTime() {
            const box = document.getElementById('datetime-indicator');
            if (!box) return;
            const now = new Date();
            const opts = { year: 'numeric', month: 'short', day: 'numeric', weekday: 'short', hour: '2-digit', minute: '2-digit', second: '2-digit' };
            box.textContent = now.toLocaleString('en-US', opts);
        }
        setInterval(updateDateTime, 1000);
        updateDateTime();

        // Search filters
        document.addEventListener('DOMContentLoaded', function () {
            var searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    var filter = this.value.toLowerCase();
                    document.querySelectorAll('#employeesTable tbody tr').forEach(function (row) {
                        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
                    });
                });
            }

            var attendanceSearch = document.getElementById('searchAttendanceInput');
            if (attendanceSearch) {
                attendanceSearch.addEventListener('input', function () {
                    var filter = this.value.toLowerCase();
                    document.querySelectorAll('#attendanceTable tbody tr').forEach(function (row) {
                        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
                    });
                });
            }
        });

        // Export functions
        function exportEmployeesPDF() {
            var doc = new jspdf.jsPDF();
            doc.text("Employee Data", 14, 14);
            doc.autoTable({ html: '#employeesTable', startY: 20, theme: 'grid' });
            doc.save('employees.pdf');
        }
        function exportAttendancePDF() {
            var doc = new jspdf.jsPDF();
            doc.text("Attendance Data", 14, 14);
            doc.autoTable({ html: '#attendanceTable', startY: 20, theme: 'grid' });
            doc.save('attendance.pdf');
        }
        function exportLeavePDF() {
            var doc = new jspdf.jsPDF();
            doc.text("Leave Request Report", 14, 14);
            doc.autoTable({ html: '#leaveTable', startY: 20, theme: 'grid' });
            doc.save('leave_requests.pdf');
        }

        // Open Edit Modal and populate fields
        function openEditModal(id, name, position, department, site, salary, status, hire_date) {
            // decode if needed
            document.getElementById('update_employee_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_position').value = position;
            document.getElementById('edit_department').value = department;
            document.getElementById('edit_site').value = site;
            document.getElementById('edit_salary').value = salary;
            document.getElementById('edit_status').value = status;
            document.getElementById('edit_hire_date').value = hire_date;
            var modal = new bootstrap.Modal(document.getElementById('editEmployeeModal'), {});
            modal.show();
        }
    </script>
</body>
</html>


    