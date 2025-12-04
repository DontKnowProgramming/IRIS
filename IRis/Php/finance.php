<?php
session_start();
require_once "config.php"; // make sure config.php defines $pdo

// Handle add, update, approve, disapprove, and payment actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_expense'])) {
        $cat = $_POST['category'];
        $desc = $_POST['description'];
        $amt = $_POST['amount'];
        $vend = $_POST['vendor'];
        $date = $_POST['date'];
        $stmt = $pdo->prepare("INSERT INTO expenses (category, description, amount, vendor, date, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
        $stmt->execute([$cat, $desc, $amt, $vend, $date]);
    }
    if (isset($_POST['approve'])) {
        $id = $_POST['expense_id'];
        $stmt = $pdo->prepare("UPDATE expenses SET status='Approved' WHERE id=?");
        $stmt->execute([$id]);
    }
    if (isset($_POST['disapprove'])) {
        $id = $_POST['expense_id'];
        $stmt = $pdo->prepare("UPDATE expenses SET status='Disapproved' WHERE id=?");
        $stmt->execute([$id]);
    }
    if (isset($_POST['mark_paid'])) {
        $id = $_POST['expense_id'];
        $stmt = $pdo->prepare("UPDATE expenses SET status='Paid' WHERE id=?");
        $stmt->execute([$id]);
    }
    if (isset($_POST['export_report'])) {
        // Implement CSV or PDF export of expenses (see below)
    }
}

// Data Fetching
$total_revenue = $pdo->query("SELECT SUM(amount) FROM revenue")->fetchColumn();
$total_expenses = $pdo->query("SELECT SUM(amount) FROM expenses WHERE status IN('Approved','Paid')")->fetchColumn();
$net_profit = $total_revenue - $total_expenses;
$pending_expenses = $pdo->query("SELECT COUNT(*) FROM expenses WHERE status='Pending'")->fetchColumn();
$expenses = $pdo->query("SELECT * FROM expenses ORDER BY date DESC")->fetchAll(PDO::FETCH_ASSOC);
$budget = $pdo->query("SELECT * FROM budget")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Finance | Management Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { background: #f6fff9; }
        .topbar { background: #119f44; color: #fff; padding: 12px 24px; display: flex; align-items: center; justify-content: space-between; }
        .card-stat { text-align: center; background: #fff; margin: 1rem; border-radius: 10px; box-shadow: 0 3px 10px rgba(0,0,0,0.07); padding: 2rem; }
        .tabs { display: flex; margin: 2rem auto 1rem auto; justify-content: center; }
        .tabs button { border: none; padding: 8px 32px; background: #eee; margin: 0 4px; font-weight: bold; }
        .tabs .active { background: #119f44; color: #fff; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .btn-green { background: #119f44; color: #fff; }
        .btn-green:hover { background: #0c7c32; }
    </style>
    <script>
        function switchTab(tab) {
            document.querySelectorAll(".tab-content").forEach(e => e.classList.remove("active"));
            document.getElementById(tab).classList.add("active");
            document.querySelectorAll(".tabs button").forEach(e => e.classList.remove("active"));
            document.getElementById(tab+"Btn").classList.add("active");
        }
    </script>
</head>
<body>
    <div class="topbar">
        <a href="login.php" class="btn btn-success">&larr; Back to Dashboard</a>
        <h2>Finance</h2>
        <div>
            <span class="badge bg-secondary">Finance Department</span>
   
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="d-flex flex-wrap justify-content-center">
        <div class="card-stat"><h2>₱<?= number_format($total_revenue,0) ?></h2><div>Total Revenue</div></div>
        <div class="card-stat"><h2 style="color:red;">₱<?= number_format($total_expenses,0) ?></h2><div>Total Expenses</div></div>
        <div class="card-stat"><h2 style="color:green;">₱<?= number_format($net_profit,0) ?></h2><div>Net Profit</div></div>
        <div class="card-stat"><h2 style="color:orange;"><?= $pending_expenses ?></h2><div>Pending Expenses</div></div>
    </div>

    <!-- Tabs -->
    <div class="tabs">
        <button id="expensesBtn" class="active" onclick="switchTab('expenses')">Expenses</button>
        <button id="budgetBtn" onclick="switchTab('budget')">Budget</button>
        <button id="reportsBtn" onclick="switchTab('reports')">Reports</button>
    </div>

    <!-- Expenses Tab -->
    <div id="expenses" class="tab-content active">
        <h3>Expense Management</h3>
        <form method="POST" class="row g-2 mb-3 align-items-center">
            <input type="hidden" name="add_expense" value="1">
            <div class="col"><input type="text" placeholder="Category" name="category" required class="form-control"></div>
            <div class="col"><input type="text" placeholder="Description" name="description" required class="form-control"></div>
            <div class="col"><input type="number" placeholder="Amount" name="amount" required class="form-control"></div>
            <div class="col"><input type="text" placeholder="Vendor" name="vendor" required class="form-control"></div>
            <div class="col"><input type="date" name="date" required class="form-control"></div>
            <div class="col"><button class="btn btn-green">Add Expense</button></div>
        </form>
        <table class="table table-bordered mt-2">
            <thead>
                <tr>
                    <th>Category</th><th>Description</th><th>Amount</th><th>Vendor</th><th>Date</th><th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($expenses as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['category']) ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td>₱<?= number_format($row['amount'],0) ?></td>
                    <td><?= htmlspecialchars($row['vendor']) ?></td>
                    <td><?= htmlspecialchars($row['date']) ?></td>
                    <td>
                        <?php if ($row['status'] == 'Paid'): ?>
                            <span class="badge bg-success">Paid</span>
                        <?php elseif ($row['status'] == 'Approved'): ?>
                            <span class="badge bg-primary">Approved</span>
                        <?php elseif ($row['status'] == 'Disapproved'): ?>
                            <span class="badge bg-danger">Disapproved</span>
                        <?php else: ?>
                            <span class="badge bg-warning">Pending</span>
                        <?php endif ?>
                    </td>
                    <td>
                        <?php if($row['status']=='Pending'): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="expense_id" value="<?= $row['id'] ?>">
                            <button name="approve" class="btn btn-success btn-sm">Approve</button>
                            <button name="disapprove" class="btn btn-danger btn-sm">Disapprove</button>
                        </form>
                        <?php endif ?>
                        <?php if($row['status']=='Approved'): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="expense_id" value="<?= $row['id'] ?>">
                            <button name="mark_paid" class="btn btn-primary btn-sm">Mark as Paid</button>
                        </form>
                        <?php endif ?>
                    </td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>

    <!-- Budget Tab -->
    <div id="budget" class="tab-content">
        <h3>Project Budget Tracking</h3>
        <table class="table table-bordered mt-2">
            <thead>
                <tr>
                    <th>Project</th><th>Budget</th><th>Spent</th><th>Remaining</th><th>Completion</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($budget as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['project']) ?></td>
                    <td>₱<?= number_format($row['total_budget'],0) ?></td>
                    <td>₱<?= number_format($row['spent'],0) ?></td>
                    <td>₱<?= number_format($row['total_budget']-$row['spent'],0) ?></td>
                    <td>
                        <div style="width:100px;background:#eee;border-radius:5px">
                            <div style="width:<?= round(100*$row['spent']/$row['total_budget']) ?>%;background:#119f44;color:white;text-align:center;border-radius:5px">
                                <?= round(100*$row['spent']/$row['total_budget']) ?>%
                            </div>
                        </div>
                    </td>
                    <td><a href="project_detail.php?id=<?= $row['id'] ?>" class="btn btn-outline-dark btn-sm">View Details</a></td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>

    <!-- Reports Tab -->
    <div id="reports" class="tab-content">
        <h3>Financial Summary</h3>
        <form method="POST">
            <button name="export_report" class="btn btn-dark">Export Full Report</button>
        </form>
        <table class="table mt-3">
            <tr>
                <th>Total Revenue:</th>
                <td>₱<?= number_format($total_revenue,0) ?></td>
            </tr>
            <tr>
                <th>Total Expenses:</th>
                <td>₱<?= number_format($total_expenses,0) ?></td>
            </tr>
            <tr>
                <th>Net Profit:</th>
                <td>₱<?= number_format($net_profit,0) ?></td>
            </tr>
            <tr>
                <th>Profit Margin:</th>
                <td><?= $total_revenue > 0 ? round(100*$net_profit/$total_revenue,1) : 0 ?>%</td>
            </tr>
        </table>
    </div>
</body>
</html>
