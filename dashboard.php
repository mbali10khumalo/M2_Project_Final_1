    <?php
    // Dashboard page
    require_once 'db.php';
    require_once 'header.php';

    // Check if user is logged in
    if (!isset($_SESSION['loggedin'])) {
        header('Location: login.php');
        exit;
    }

    // Get counts for dashboard
    $employeeCount = $pdo->query("SELECT COUNT(*) FROM employees")->fetchColumn();
    $pendingLeave = $pdo->query("SELECT COUNT(*) FROM leave_requests WHERE status = 'Pending'")->fetchColumn();
    $absentToday = $pdo->query("SELECT COUNT(*) FROM attendance WHERE date = CURDATE() AND status = 'Absent'")->fetchColumn();
    ?>

    <style>
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background: linear-gradient(135deg, #0f2027, #1c3b49ff);
            color: #fff;
        }

    .h{
        background-color: #779dadff;
    }

        .bottom-table {
            margin-top: 1px;
        }
    </style>

    <h2 class="display-5 text-center mb-1">Dashboard</h2>
    <hr class="mb-2">
    <div class="row mt-4 mb-3">
        <div class="col-md-4 mb-2">
            <div class="card" style="background: linear-gradient(135deg, #0f2027, #1c3b49ff); color: #fff;">
                <div class="card-body text-center">
                    <h5 class="card-title">Total Employees</h5>
                    <p class="display-4"><?php echo $employeeCount; ?></p>
                    <a href="employees.php" class="btn btn-light w-100">View All</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="card" style="background: linear-gradient(135deg, #0f2027, #1c3b49ff); color: #fff;">
                <div class="card-body text-center">
                    <h5 class="card-title">Pending Leave Requests</h5>
                    <p class="display-4"><?php echo $pendingLeave; ?></p>
                    <a href="leave.php" class="btn btn-light w-100">Manage Requests</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="card" style="background: linear-gradient(135deg, #0f2027, #1c3b49ff); color: #fff;">
                <div class="card-body text-center">
                    <h5 class="card-title">Absent Today</h5>
                    <p class="display-4"><?php echo $absentToday; ?></p>
                    <a href="attendance.php" class="btn btn-light w-100">View Attendance</a>
                </div>
            </div>
        </div>
    </div>


<div class="bottom-table">
    <div class="row mt-2">
        <div class="col-md-6 mb-2">
            <div class="card">
                <div class="card-body">
                <h5 class="card-title text-center mb-3">Recent Leave Requests</h5>
                    <table class="table table-striped table-hover table-bordered table-dark">
                        <thead>
                            <tr class="table-secondary">
                                <th>Employee</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("
                                SELECT l.date, l.status, e.name 
                                FROM leave_requests l
                                JOIN employees e ON l.employee_id = e.employee_id
                                ORDER BY l.date DESC
                                LIMIT 5
                            ");
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr>
                                    <td>{$row['name']}</td>
                                    <td>{$row['date']}</td>
                                    <td><span class='badge bg-".($row['status'] == 'Approved' ? 'success' : ($row['status'] == 'Denied' ? 'danger' : 'warning'))."'>{$row['status']}</span></td>
                                </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title text-center mb-3">Recent Attendance</h5>
                    <table class="table table-striped table-hover table-bordered table-dark">
                        <thead>
                            <tr class="table-secondary">
                                <th>Employee</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("
                                SELECT a.date, a.status, e.name 
                                FROM attendance a
                                JOIN employees e ON a.employee_id = e.employee_id
                                ORDER BY a.date DESC
                                LIMIT 5
                            ");
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr>
                                    <td>{$row['name']}</td>
                                    <td>{$row['date']}</td>
                                    <td class='".($row['status'] == 'Present' ? 'attendance-present' : 'attendance-absent')."'>{$row['status']}</td>
                                </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

    <?php require_once 'footer.php'; ?>