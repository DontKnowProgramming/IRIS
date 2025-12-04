<?php
// Start session and check if logged in
session_start();

// If user is not logged in, redirect to login.php
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Example variables (you can fetch these from your DB)
$activeProjects = 15;
$totalEmployees = 28;
$monthlyRevenue = "₱2.5M";

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Rocelyn RJ Building Trades Inc</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f9fdf9;
      margin: 0;
      padding: 0;
    }

    header {
      background: #0a8f3c;
      color: white;
      padding: 15px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    header h1 {
      margin: 0;
      font-size: 20px;
    }

    .logout-btn {
      background: white;
      color: #0a8f3c;
      padding: 8px 15px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-weight: bold;
    }

    .stats {
      display: flex;
      justify-content: center;
      margin: 30px auto;
      gap: 20px;
    }

    .stat-box {
      background: white;
      padding: 20px;
      border: 1px solid #b6e8c9;
      border-radius: 12px;
      width: 250px;
      text-align: center;
      box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    }

    .stat-box h2 {
      font-size: 28px;
      margin: 0;
    }

    .stat-box p {
      margin: 5px 0 0;
      font-size: 14px;
      color: #333;
    }

    .departments {
      display: flex;
      justify-content: center;
      margin: 40px auto;
      gap: 20px;
      flex-wrap: wrap;
    }

    .card {
      background: white;
      border-radius: 12px;
      padding: 20px;
      width: 280px;
      border: 1px solid #e0e0e0;
      box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    }

    .card h3 {
      margin-top: 10px;
      font-size: 20px;
      color: #222;
    }

    .card ul {
      list-style: none;
      padding: 0;
      margin: 15px 0;
    }

    .card ul li {
      margin: 6px 0;
      color: #333;
    }

    .card button {
      width: 100%;
      padding: 10px;
      border: none;
      border-radius: 8px;
      font-weight: bold;
      color: white;
      cursor: pointer;
    }

    .hr-btn { background: #007bff; }
    .finance-btn { background: #198754; }
    .user-btn { background: #6f42c1; }
  </style>
</head>
<body>

<header>
  <h1>Admin Dashboard - Rocelyn RJ Building Trades Inc</h1>
  <form action="logout.php" method="POST">
    <button type="submit" class="logout-btn">Logout</button>
  </form>
</header>

<main>
  <!-- Stats Section -->
  <div class="stats">
    <div class="stat-box">
      <h2 style="color: green;"><?php echo $activeProjects; ?></h2>
      <p>Active Projects</p>
    </div>
    <div class="stat-box">
      <h2 style="color: blue;"><?php echo $totalEmployees; ?></h2>
      <p>Total Employees</p>
    </div>
    <div class="stat-box">
      <h2 style="color: darkgreen;"><?php echo $monthlyRevenue; ?></h2>
      <p>Monthly Revenue</p>
    </div>
  </div>

  <!-- Department Access Section -->
  <h2 style="text-align:center;">Welcome to Admin Dashboard</h2>
  <p style="text-align:center;">Select your department to access your data and management tools</p>

  <div class="departments">
    <div class="card">
      <h3>Human Resources</h3>
      <ul>
        <li>✔ Employee Records Management</li>
        <li>✔ Payroll & Benefits Tracking</li>
        <li>✔ Performance Reviews</li>
        <li>✔ Training & Development</li>
      </ul>
      <button class="hr-btn">Access HR Dashboard</button>
    </div>

    <div class="card">
      <h3>Finance</h3>
      <ul>
        <li>✔ Financial Reports & Analytics</li>
        <li>✔ Invoice & Payment Tracking</li>
        <li>✔ Budget Management</li>
        <li>✔ Expense Tracking</li>
      </ul>
      <button class="finance-btn">Access Finance Dashboard</button>
    </div>

    <div class="card">
      <h3>User Management</h3>
      <ul>
        <li>✔ Create New User Accounts</li>
        <li>✔ Manage User Profiles</li>
        <li>✔ Assign Roles & Positions</li>
        <li>✔ Account Credentials Management</li>
      </ul>
      <button class="user-btn">Access User Management</button>
    </div>
  </div>
</main>

</body>
</html>
