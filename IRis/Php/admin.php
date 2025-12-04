<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "rocelenrj_db";
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$pdo = new PDO('mysql:host=localhost;dbname=rocelenrj_db', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ---------- ADD USER ---------- */
if (isset($_POST['add_user'])) {
    $full_name   = trim($_POST['full_name']);
    $email       = trim($_POST['email']);
    $position    = trim($_POST['position']);
    $username    = trim($_POST['username']);
    $password    = $_POST['password'];
    $employee_id = trim($_POST['employee_id']);

    if ($full_name && $email && $position && $username && $password && $employee_id) {
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO users (full_name, email, position, username, password, employee_id)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $full_name,
                $email,
                $position,
                $username,
                $password_hashed,
                $employee_id
            ]);
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } catch (PDOException $e) {
            echo "<script>alert('Error adding user: " . addslashes($e->getMessage()) . "');</script>";
        }
    } else {
        echo "<script>alert('Please fill all fields.');</script>";
    }
}

/* ---------- UPDATE USER ---------- */
if (isset($_POST['update_user'])) {
    $id          = (int) $_POST['edit_id'];
    $full_name   = trim($_POST['edit_full_name']);
    $email       = trim($_POST['edit_email']);
    $position    = trim($_POST['edit_position']);
    $username    = trim($_POST['edit_username']);
    $password    = $_POST['edit_password'];
    $employee_id = trim($_POST['edit_employee_id']);

    if ($full_name && $email && $position && $username && $employee_id) {
        try {
            if ($password !== '') {
                $password_hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users 
                    SET full_name = ?, email = ?, position = ?, username = ?, password = ?, employee_id = ?
                    WHERE id = ?");
                $stmt->execute([$full_name, $email, $position, $username, $password_hashed, $employee_id, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users 
                    SET full_name = ?, email = ?, position = ?, username = ?, employee_id = ?
                    WHERE id = ?");
                $stmt->execute([$full_name, $email, $position, $username, $employee_id, $id]);
            }
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } catch (PDOException $e) {
            echo "<script>alert('Error updating user: " . addslashes($e->getMessage()) . "');</script>";
        }
    } else {
        echo "<script>alert('Please fill all required fields.');</script>";
    }
}

/* ---------- DELETE USER ---------- */
if (isset($_POST['delete_user'])) {
    $id = (int) $_POST['delete_id'];

    if ($id > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } catch (PDOException $e) {
            echo "<script>alert('Error deleting user: " . addslashes($e->getMessage()) . "');</script>";
        }
    }
}

/* ---------- FETCH DATA ---------- */
$hrStmt   = $pdo->query("SELECT name, position FROM employees WHERE position IS NOT NULL");
$hrEmployees = $hrStmt->fetchAll(PDO::FETCH_ASSOC);
$userStmt = $pdo->query("SELECT * FROM users");
$users    = $userStmt->fetchAll(PDO::FETCH_ASSOC);

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard - Rocelyn RJ Building Trades Inc</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style>
    body {
  font-family: Arial, sans-serif;
  background: #f9fdf9;
  margin: 0;
}

header {
  background: #040f2a;
  color: #e5c392 ;
  padding: 18px 36px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.LOGO {
  display: flex;
  align-items: center;
  gap: 14px;
}

.LOGO img {
  width: 150px;
  height: 48px;
  object-fit: contain;
}

header h1 {
  margin: 0;
  font-size: 26px;
  font-weight: bold;
  white-space: nowrap;
}

.logout-btn {
  margin-left: 850px;
  background: #081e55ff;
  color: #e5c392;
  border: none;
  padding: 8px 15px;
  border-radius: 5px;
  cursor: pointer;
  font-weight: bold;
  font-size: 16px;
  transition: all 0.3s;
}

.logout-btn:hover {
  background: #254776;
  color: #fff;
}

/* MAIN LAYOUT */

.dashboard-container {
  max-width: 1300px;
  width: 100%;
  margin: 36px auto;
  padding: 0 20px;
}

/* Two cards side by side */
.cards-row {
  display: flex;
  flex-wrap: wrap;
  flex-direction: row;
  justify-content: space-between;
  align-items: flex-start;
  gap: 20px;
  width: 100%;
}

/* HR card and User Management card share base style */
.card-section,
.user-mgmt-container {
  background: #ffffff;
  border-radius: 14px;
  box-shadow: 0 4px 15px #0001;
  border: 1px solid #e0e0e0;
  padding: 20px;
  min-width: 400px;  /* slightly smaller so both fit */
}

/* HR smaller, User larger but still flexible */
.card-section {
  flex: 1;          /* 1 part */
}

.user-mgmt-container {
  flex: 2;          /* 2 parts */
}


/* HR section details */

.card-section h3 {
  margin: 0;                       /* remove extra bottom margin */
  font-size: 22px;
  color: #181818;
}

.card-section p {
  margin: 8px 0 16px 0;
  color: #888;
  font-size: 16px;
}

.card-section .topbar {
  display: flex;
  justify-content: space-between;  /* title left, actions right */
  align-items: flex-start;         /* align to top */
 
}
.card-section .topbar > div {
  display: flex;
  flex-direction: column;          /* 3 Employees above Export PDF */
  align-items: flex-end;           /* group right aligned */
  gap: 4px;
}

.topbar .count {
  background: transparent ;
  color: #ebb66dff ;
  border-radius: 8px;
  padding: 4px 16px;
  font-weight: bold;
}

.topbar button {
  margin-left: 16px;
}

/* Buttons */

.export-btn {
  background: #d32f2f;
  color: #fff;
  font-weight: bold;
  border: none;
  border-radius: 6px;
  padding: 8px 16px;
  cursor: pointer;
}

.export-btn:hover {
  background: #ab2222;
}

.btn.add {
  background: #1976d2;
  color: #fff;
  font-weight: bold;
  border: none;
  border-radius: 6px;
  padding: 8px 16px;
  cursor: pointer;
}

.btn.add:hover {
  background: #3d73a9ff;
}

.btn {
  background: #13aa52;
  color: #fff;
  border: none;
  border-radius: 7px;
  padding: 10px 28px;
  font-weight: bold;
  font-size: 17px;
  cursor: pointer;
}

.btn:hover {
  background: #4ba06eff;
}

.btn-top {
  padding: 8px 22px;
  font-size: 15px;
  border-radius: 6px;
}

/* Tables */

.table-section {
  margin-top: 16px;
}

.table-section table {
  width: 100%;
  border-collapse: collapse;
  background: #fff;
}

.table-section th,
.table-section td {
  padding: 10px 9px;
  text-align: left;
}

.table-section th {
  background: #e5c392 ;
}

.table-section tr:nth-child(even) {
  background: #f5fcf6;
}

/* Badges */

.badge {
  font-size: 13px;
  border-radius: 7px;
  padding: 2px 12px;
  font-weight: bold;
}

.badge.active {
  background: #b5f7ce;
  color: #218c5b;
}

.badge.onleave {
  background: #fffba3;
  color: #99913c;
}

.position-badge {
  padding: 2px 11px;
  border-radius: 6px;
  font-weight: bold;
}

/* User Management card */

/* User Management card */

.user-mgmt-container {
  display: flex;
  flex-direction: column;
}

/* Title row (User Management + buttons) */
.user-mgmt-title {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 4px;
}

.user-bar-btns {
  margin-left: auto;
  display: flex;
  gap: 10px;
}

/* Search and table inside user card */

.search {
  width: 280px;
  padding: 9px 14px;
  border-radius: 6px;
  border: 1px solid #d1dad2;
  font-size: 15px;
  margin-bottom: 10px;
}

.passwd-mask {
  font-family: monospace;
  letter-spacing: 4px;
}

.user-mgmt-container table {
  width: 100%;
  border-collapse: collapse;
  background: #fff;
  margin-top: 4px;
}

.user-mgmt-container th {
  background: #eaf9f1;
  font-size: 17px;
  text-align: left;
  padding: 10px 8px;
}

.user-mgmt-container td {
  font-size: 16px;
  border-bottom: 1px solid #e8e8e8;
  padding: 8px 8px;
}

.user-mgmt-container th.text-center,
.user-mgmt-container td.text-center {
  text-align: center;
}

/* Utility classes */

.text-center {
  text-align: center;
}

.no-underline {
  text-decoration: none !important;
}

.no-underline:hover,
.no-underline:focus {
  text-decoration: none !important;
}

/* Responsive: stack cards on small screens */

@media (max-width: 1000px) {
  .cards-row {
    flex-direction: column;
    gap: 20px;
  }
  .dashboard-container {
    padding: 6px;
  }
}
/* Keep table from stretching / overlapping */
#users-table {
  width: 100%;              /* was 110% */
  table-layout: fixed;
}

/* Data cells: truncate if too long */
#users-table td {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Header cells: allow wrapping so labels stay readable */
#users-table th {
  white-space: normal;      /* allow multiple lines */
    background: #e5c392 ;
}

/* Last column for Edit/Delete buttons (no ellipsis here) */
#users-table th:last-child,
#users-table td:last-child {
  width: 140px;
  white-space: nowrap;
  overflow: visible;        /* override the td rule */
  text-overflow: clip;      /* remove "..." */
}

.action-buttons-vert {
  display: flex;
  flex-direction: column;  /* stack (magkapatong) */
  align-items: center;
  gap: 6px;
}

.action-buttons-vert form {
  margin: 0;
}

.delete-btn {
  background: #d32f2f;
}

.delete-btn:hover {
  background: #ab2222;
}

.hr-header {
  display: flex;
  justify-content: space-between;  /* title left, buttons right */
  align-items: center;
  margin-bottom: 8px;
}

.hr-title h3 {
  margin: 0;
}

/* reuse your existing .count and .export-btn styles */
.hr-actions {
  display: flex;
  align-items: center;
  gap: 8px;    /* space between badge and button */
}

.card-section .topbar {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 8px;
}

.hr-title-wrap {
  display: flex;
  flex-direction: column;   /* subtitle directly under title */
}

.hr-title-wrap h3 {
  margin: 0;
  font-size: 22px;
  color: #181818;
}

.hr-subtitle {
  margin-right: 8px;
}
</style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.7.0/jspdf.plugin.autotable.min.js"></script>
</head>

<body>
<header>
    <div class="LOGO">
        <img src="../Capstone pics/LOGO3.jpg" alt="Company Logo">
        <h1>Admin Dashboard </h1>
    </div>
    <form action="login.php" method="POST">
        <button type="submit" class="logout-btn">Logout</button>
    </form>
</header>

<div class="dashboard-container">

    <!-- Cards Row -->
    <div class="cards-row">

        <!-- HR Section -->
        <div class="card-section">
            <div class="topbar">
                  <div class="hr-title-wrap">
                      <h3>Human Resources</h3>
                      <p class="hr-subtitle">Employee Management</p>
                  </div>
                  <div>
                      <span class="count"><?= count($hrEmployees) ?> Employees</span>
                      <button class="export-btn" type="button" onclick="exportHRPDF()">Export PDF</button>
                  </div>
              </div>


            

            <div class="table-section">
                <table id="hr-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Position</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($hrEmployees as $emp): ?>
                            <tr>
                                <td><?= htmlspecialchars($emp['name']) ?></td>
                                <td><?= htmlspecialchars($emp['position']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- User Management Section -->
        <div class="user-mgmt-container">
            <div class="user-mgmt-title">
                <div>
                    <h2 style="margin: 0 0 6px 0;">User Management</h2>
                    <div style="margin-bottom:10px; color:#888; font-size:17px;">
                        Create and manage user accounts, roles, and permissions
                    </div>
                </div>
                <div class="user-bar-btns">
                    <button class="export-btn btn-top" type="button" onclick="exportUserPDF()">Export PDF</button>
                    <button class="btn add btn-top" type="button"
                            onclick="document.getElementById('addUserModal').style.display='flex';">
                        Add User
                    </button>
                </div>
            </div>

            <input class="search" id="searchInput" onkeyup="searchUserTable()" type="text" placeholder="Search users..."/>

            <table id="users-table">
                <thead>
                    <tr>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Position</th>
                        <th>Username</th>
                        <th>Password</th>
                        <th>Employee ID</th>
                        <th><center>Actions:</center></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['full_name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td>
                            <?php
                            $position  = trim($user['position']);
                            $posLower  = strtolower($position);

                            $badgeColor = "#23282d";
                            $badgeBg    = "#f2f2f2";

                            if (strpos($posLower,'admin') !== false) {
                                $badgeColor="#218c5b"; $badgeBg="#eaf9f1";
                            } elseif (strpos($posLower,'hr') !== false) {
                                $badgeColor="#1b5e95"; $badgeBg="#d9eaff";
                            } elseif (strpos($posLower,'finance') !== false) {
                                $badgeColor="#954ca7"; $badgeBg="#e4c0ff";
                            } elseif (strpos($posLower,'manager') !== false) {
                                $badgeColor="#a46815"; $badgeBg="#ffe3b6";
                            } elseif (
                                strpos($posLower,'time keeper') !== false ||
                                strpos($posLower,'timekeeper') !== false
                            ) {
                                $badgeColor="#99913c"; $badgeBg="#fffba3";
                            }
                            ?>
                            <span class="position-badge" style="background:<?= $badgeBg ?>; color:<?= $badgeColor ?>">
                                <?= htmlspecialchars($position) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><span class="passwd-mask">•••••••</span></td>
                        <td><?= htmlspecialchars($user['employee_id']) ?></td>

                        <td class="text-center">
                          <div class="action-buttons-vert">
                              <button type="button"
                                      class="btn btn-top no-underline edit-btn"
                                      data-id="<?= $user['id'] ?>"
                                      data-full_name="<?= htmlspecialchars($user['full_name'], ENT_QUOTES) ?>"
                                      data-email="<?= htmlspecialchars($user['email'], ENT_QUOTES) ?>"
                                      data-position="<?= htmlspecialchars($user['position'], ENT_QUOTES) ?>"
                                      data-username="<?= htmlspecialchars($user['username'], ENT_QUOTES) ?>"
                                      data-employee_id="<?= htmlspecialchars($user['employee_id'], ENT_QUOTES) ?>">
                                  Edit
                              </button>

                              <form method="POST" action=""
                                    onsubmit="return confirm('Delete this user?');">
                                  <input type="hidden" name="delete_id" value="<?= $user['id'] ?>">
                                  <button type="submit" name="delete_user"
                                          class="btn btn-top delete-btn">
                                      Delete
                                  </button>
                              </form>
                          </div>
                      </td>

                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div> <!-- end .cards-row -->
</div> <!-- end .dashboard-container -->


<!-- Add User Modal -->
<div id="addUserModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
  <div style="background:#fff; border-radius:12px; padding:24px; width:420px; box-shadow:0 6px 18px #0005; position:relative;">
    <h3 style="margin-top:0;">Add New User</h3>
    <form method="POST" action="">
      <label>Full Name</label>
      <input type="text" name="full_name" required style="width:100%; margin-bottom:12px; padding:8px; border-radius:6px; border:1px solid #ccc;" />
      <label>Email</label>
      <input type="email" name="email" required style="width:100%; margin-bottom:12px; padding:8px; border-radius:6px; border:1px solid #ccc;" />
      <label>Position</label>
      <input type="text" name="position" required style="width:100%; margin-bottom:12px; padding:8px; border-radius:6px; border:1px solid #ccc;" />
      <label>Username</label>
      <input type="text" name="username" required style="width:100%; margin-bottom:12px; padding:8px; border-radius:6px; border:1px solid #ccc;" />
      <label>Password</label>
      <input type="password" name="password" required style="width:100%; margin-bottom:12px; padding:8px; border-radius:6px; border:1px solid #ccc;" />
      <label>Employee ID</label>
      <input type="text" name="employee_id" required style="width:100%; margin-bottom:18px; padding:8px; border-radius:6px; border:1px solid #ccc;" />
      <button type="submit" name="add_user" style="background:#0a8f3c; color:white; padding:12px 22px; border:none; border-radius:8px; font-weight:bold; cursor:pointer;">Add User</button>
      <button type="button" onclick="document.getElementById('addUserModal').style.display='none';" style="position:absolute; top:10px; right:16px; font-size:20px; border:none; background:none; cursor:pointer;">&times;</button>
    </form>
  </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
  <div style="background:#fff; border-radius:12px; padding:24px; width:420px; box-shadow:0 6px 18px #0005; position:relative;">
    <h3 style="margin-top:0;">Edit User</h3>
    <form method="POST" action="">
      <input type="hidden" name="edit_id" id="edit_id">

      <label>Full Name</label>
      <input type="text" name="edit_full_name" id="edit_full_name" required style="width:100%; margin-bottom:12px; padding:8px; border-radius:6px; border:1px solid #ccc;" />

      <label>Email</label>
      <input type="email" name="edit_email" id="edit_email" required style="width:100%; margin-bottom:12px; padding:8px; border-radius:6px; border:1px solid #ccc;" />

      <label>Position</label>
      <input type="text" name="edit_position" id="edit_position" required style="width:100%; margin-bottom:12px; padding:8px; border-radius:6px; border:1px solid #ccc;" />

      <label>Username</label>
      <input type="text" name="edit_username" id="edit_username" required style="width:100%; margin-bottom:12px; padding:8px; border-radius:6px; border:1px solid #ccc;" />

      <label>New Password (leave blank to keep current)</label>
      <input type="password" name="edit_password" id="edit_password" style="width:100%; margin-bottom:12px; padding:8px; border-radius:6px; border:1px solid #ccc;" />

      <label>Employee ID</label>
      <input type="text" name="edit_employee_id" id="edit_employee_id" required style="width:100%; margin-bottom:18px; padding:8px; border-radius:6px; border:1px solid #ccc;" />

      <button type="submit" name="update_user" style="background:#0a8f3c; color:white; padding:12px 22px; border:none; border-radius:8px; font-weight:bold; cursor:pointer;">Update User</button>
      <button type="button" onclick="document.getElementById('editUserModal').style.display='none';" style="position:absolute; top:10px; right:16px; font-size:20px; border:none; background:none; cursor:pointer;">&times;</button>
    </form>
  </div>
</div>

<script>
function exportUserPDF() { 
  const { jsPDF } = window.jspdf; 
  const doc = new jsPDF('l'); 
  doc.text("User Management List", 14, 12); 
  doc.autoTable({ 
    html: '#users-table', 
    startY: 20, 
    headStyles: { fillColor: [41, 143, 60] }, 
    styles: { fontSize: 10 }, 
    columnStyles: { 6: { cellWidth: 32 } } 
  }); 
  doc.save("users.pdf"); 
}
function exportHRPDF() { 
  const { jsPDF } = window.jspdf; 
  const doc = new jsPDF(); 
  doc.text("HR Employees", 14, 12); 
  doc.autoTable({ 
    html: '#hr-table', 
    startY: 20, 
    headStyles: { fillColor: [41, 143, 60] }, 
    styles: { fontSize: 10 } 
  }); 
  doc.save("hr_employees.pdf"); 
}
function exportFinancePDF() { 
  const { jsPDF } = window.jspdf; 
  const doc = new jsPDF('l'); 
  doc.text("Finance Records", 14, 12); 
  doc.autoTable({ 
    html: '#finance-table', 
    startY: 20, 
    headStyles: { fillColor: [41, 143, 60] }, 
    styles: { fontSize: 10 } 
  }); 
  doc.save("finance.pdf"); 
}
function searchUserTable() {
  let input = document.getElementById('searchInput').value.toLowerCase();
  let rows = document.querySelectorAll('.user-mgmt-container tbody tr');
  rows.forEach(row => {
    row.style.display = Array.from(row.children).some(td =>
      td.textContent.toLowerCase().includes(input)
    ) ? '' : 'none';
  });
}
document.querySelector('.btn.add').addEventListener('click', function () {
  document.getElementById('addUserModal').style.display = 'flex';
});
document.getElementById('addUserModal').querySelector('button[type="button"]').addEventListener('click', function(){
   document.getElementById('addUserModal').style.display = 'none';
});
// Close modal on outside click
window.onclick = function(event) {
  if (event.target === document.getElementById('addUserModal')) {
    document.getElementById('addUserModal').style.display = 'none';
  }
}
// open Add User modal
document.querySelector('.btn.add').addEventListener('click', function () {
  document.getElementById('addUserModal').style.display = 'flex';
});

// close Add modal (already present) ...

// handle Edit buttons
document.querySelectorAll('.edit-btn').forEach(function(btn) {
  btn.addEventListener('click', function() {
    document.getElementById('edit_id').value = this.dataset.id;
    document.getElementById('edit_full_name').value = this.dataset.full_name;
    document.getElementById('edit_email').value = this.dataset.email;
    document.getElementById('edit_position').value = this.dataset.position;
    document.getElementById('edit_username').value = this.dataset.username;
    document.getElementById('edit_employee_id').value = this.dataset.employee_id;
    document.getElementById('edit_password').value = '';
    document.getElementById('editUserModal').style.display = 'flex';
  });
});

// close modals on outside click
window.onclick = function(event) {
  if (event.target === document.getElementById('addUserModal')) {
    document.getElementById('addUserModal').style.display = 'none';
  }
  if (event.target === document.getElementById('editUserModal')) {
    document.getElementById('editUserModal').style.display = 'none';
  }
};

</script>
