<?php
require_once 'db.php';
require_once 'header.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

// Check if filtering by employee
$employeeId = $_GET['employee_id'] ?? 0;
$employeeName = '';

if ($employeeId) {
    $stmt = $pdo->prepare("SELECT name FROM employees WHERE employee_id = ?");
    $stmt->execute([$employeeId]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    $employeeName = $employee ? $employee['name'] : '';
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id']) && isset($_POST['status'])) {
    $requestId = $_POST['request_id'];
    $status = $_POST['status'];
    
    $stmt = $pdo->prepare("UPDATE leave_requests SET status = ? WHERE id = ?");
    $stmt->execute([$status, $requestId]);
    
    // Update attendance if leave is approved
    if ($status === 'Approved') {
        // Get the leave request details
        $leaveStmt = $pdo->prepare("SELECT employee_id, date FROM leave_requests WHERE id = ?");
        $leaveStmt->execute([$requestId]);
        $leave = $leaveStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($leave) {
            // Check if attendance record already exists
            $attendanceCheck = $pdo->prepare("SELECT id FROM attendance WHERE employee_id = ? AND date = ?");
            $attendanceCheck->execute([$leave['employee_id'], $leave['date']]);
            
            if ($attendanceCheck->rowCount() === 0) {
                // Insert new attendance record for the leave day
                $insertAttendance = $pdo->prepare("INSERT INTO attendance (employee_id, date, status) VALUES (?, ?, 'Absent')");
                $insertAttendance->execute([$leave['employee_id'], $leave['date']]);
            }
        }
    }
    
    // Redirect to avoid form resubmission
    header("Location: leave.php" . ($employeeId ? "?employee_id=$employeeId" : ""));
    exit;
}

// Handle new leave request submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_leave'])) {
    $employeeId = $_POST['employee_id'];
    $date = $_POST['date'];
    $reason = $_POST['reason'];
    
    // Validate inputs
    if (empty($employeeId) || empty($date) || empty($reason)) {
        $error = "Please fill in all fields";
    } else {
        // Check if leave request already exists for this date
        $checkStmt = $pdo->prepare("SELECT id FROM leave_requests WHERE employee_id = ? AND date = ?");
        $checkStmt->execute([$employeeId, $date]);
        
        if ($checkStmt->rowCount() > 0) {
            $error = "A leave request already exists for this date";
        } else {
            // Insert new leave request
            $insertStmt = $pdo->prepare("INSERT INTO leave_requests (employee_id, date, reason, status) VALUES (?, ?, ?, 'Pending')");
            $insertStmt->execute([$employeeId, $date, $reason]);
            
            // Redirect to avoid form resubmission
            header("Location: leave.php" . ($employeeId ? "?employee_id=$employeeId" : ""));
            exit;
        }
    }
}

// Build query based on filter
if ($employeeId) {
    $sql = "SELECT l.*, e.name FROM leave_requests l 
            JOIN employees e ON l.employee_id = e.employee_id 
            WHERE l.employee_id = ? 
            ORDER BY l.status = 'Pending' DESC, l.date DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$employeeId]);
} else {
    $sql = "SELECT l.*, e.name FROM leave_requests l 
            JOIN employees e ON l.employee_id = e.employee_id 
            ORDER BY l.status = 'Pending' DESC, l.date DESC";
    $stmt = $pdo->query($sql);
}

$leaveRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all employees for the dropdown
$employees = $pdo->query("SELECT employee_id, name FROM employees ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>

<h2 class="text-center display-4 mb-4">Leave Requests <?php echo $employeeName ? "for $employeeName" : ''; ?></h2>
<hr class="mb-4">

<div class="row mt-5">
    <div class="col-md-12">
        <?php if ($employeeId): ?>
            <a href="leave.php" class="btn btn-secondary mb-3">View All Requests</a>
        <?php endif; ?>
        
        <!-- New Leave Request Form -->
        <div class="card mb-4 w-50 mx-auto">
            <div class="card-header" style="background: linear-gradient(135deg, #0f2027, #1c3b49ff);">
                <h5 class="card-title text-center text-white">Submit New Leave Request</h5>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="employee_id" class="form-label">Employee</label>
                                <select class="form-select" id="employee_id" name="employee_id" required <?php echo $employeeId ? 'disabled' : ''; ?>>
                                    <?php if ($employeeId): ?>
                                        <?php 
                                        $selectedEmployee = $pdo->prepare("SELECT employee_id, name FROM employees WHERE employee_id = ?");
                                        $selectedEmployee->execute([$employeeId]);
                                        $employee = $selectedEmployee->fetch(PDO::FETCH_ASSOC);
                                        ?>
                                        <option value="<?php echo $employee['employee_id']; ?>"><?php echo htmlspecialchars($employee['name']); ?></option>
                                        <input type="hidden" name="employee_id" value="<?php echo $employeeId; ?>">
                                    <?php else: ?>
                                        <option value="">Select Employee</option>
                                        <?php foreach ($employees as $emp): ?>
                                            <option value="<?php echo $emp['employee_id']; ?>" <?php echo isset($_POST['employee_id']) && $_POST['employee_id'] == $emp['employee_id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($emp['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="date" class="form-label">Date</label>
                                <input type="date" class="form-control" id="date" name="date" required 
                                       value="<?php echo $_POST['date'] ?? ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="reason" class="form-label">Reason</label>
                                <input type="text" class="form-control" id="reason" name="reason" required 
                                       value="<?php echo $_POST['reason'] ?? ''; ?>" placeholder="e.g., Vacation, Sick Leave">
                            </div>
                        </div>
                    </div>
                    <div class="request-btn text-center my-2">
                    <button type="submit" name="submit_leave" class="btn btn-outline-primary w-100">Submit Request</button>
                </div>
                </form>
            </div>
        </div>
        
        <!-- Leave Requests Table -->
        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #0f2027, #1c3b49ff);">
                <h5 class="card-title text-center text-white">Leave Requests</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr class="table-dark">
                                <?php if (!$employeeId): ?>
                                    <th>Employee</th>
                                <?php endif; ?>
                                <th>Date</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($leaveRequests)): ?>
                                <tr>
                                    <td colspan="<?php echo $employeeId ? 4 : 5; ?>" class="text-center">No leave requests found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($leaveRequests as $request): ?>
                                    <tr>
                                        <?php if (!$employeeId): ?>
                                            <td><?php echo htmlspecialchars($request['name']); ?></td>
                                        <?php endif; ?>
                                        <td><?php echo $request['date']; ?></td>
                                        <td><?php echo htmlspecialchars($request['reason']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $request['status'] == 'Approved' ? 'success' : 
                                                     ($request['status'] == 'Denied' ? 'danger' : 'warning'); 
                                            ?>">
                                                <?php echo $request['status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($request['status'] == 'Pending'): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                                    <button type="submit" name="status" value="Approved" class="btn btn-sm btn-outline-success">Approve</button>
                                                </form>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                                    <button type="submit" name="status" value="Denied" class="btn btn-sm btn-outline-danger">Deny</button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-muted">Request Finalized</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>