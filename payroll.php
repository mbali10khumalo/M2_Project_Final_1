<?php
// Payroll management page
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

// Function to calculate payroll (converted from JS to PHP)
function calculatePayroll($pdo, $employeeId = 0) {
    if ($employeeId) {
        $sql = "SELECT p.*, e.name, e.salary 
                FROM payroll p
                JOIN employees e ON p.employee_id = e.employee_id
                WHERE p.employee_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$employeeId]);
    } else {
        $sql = "SELECT p.*, e.name, e.salary 
                FROM payroll p
                JOIN employees e ON p.employee_id = e.employee_id
                ORDER BY e.name";
        $stmt = $pdo->query($sql);
    }

    $results = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Prevent division by zero
        $hoursWorked = floatval($row['hours_worked']);
        $hourlyRate = ($hoursWorked > 0) ? ($row['final_salary'] / $hoursWorked) : 0;
        $leaveHours = $row['leave_deductions'] * 8;
        $deductionAmount = $hourlyRate * $leaveHours;
        $netSalary = $row['final_salary'] - $deductionAmount;

        $results[] = [
            'employeeId' => $row['employee_id'],
            'name' => $row['name'],
            'hoursWorked' => $row['hours_worked'],
            'leaveDeductions' => $row['leave_deductions'],
            'grossSalary' => $row['salary'],
            'hourlyRate' => round($hourlyRate, 2),
            'leaveHours' => $leaveHours,
            'deductionAmount' => round($deductionAmount, 2),
            'netSalary' => round($netSalary, 2)
        ];
    }
    return $results;
}

$payrollData = calculatePayroll($pdo, $employeeId);
?>

<h2 class="text-center display-5 mb-4">Payroll Management Table <?php echo $employeeName ? "for $employeeName" : ''; ?></h2>
<hr class="mb-5">
<div class="row mt-4">
    <div class="col-md-12">
        <?php if ($employeeId): ?>
            <a href="payroll.php" class="btn btn-secondary mb-3">View All Payroll</a>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered text-center">
                        <thead>
                            <tr class="table-dark">
                                <?php if (!$employeeId): ?>
                                    <th>Employee</th>
                                <?php endif; ?>
                                <th>Hours Worked</th>
                                <th>Leave Deductions</th>
                                <th>Hourly Rate</th>
                                <th>Gross Salary</th>
                                <th>Deductions</th>
                                <th>Net Salary</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payrollData as $payroll): ?>
                                <tr>
                                    <?php if (!$employeeId): ?>
                                        <td><?php echo htmlspecialchars($payroll['name']); ?></td>
                                    <?php endif; ?>
                                    <td><?php echo $payroll['hoursWorked']; ?></td>
                                    <td><?php echo $payroll['leaveDeductions']; ?> days</td>
                                    <td>R <?php echo number_format($payroll['hourlyRate'], 2); ?></td>
                                    <td>R <?php echo number_format($payroll['grossSalary'], 2); ?></td>
                                    <td>R <?php echo number_format($payroll['deductionAmount'], 2); ?></td>
                                    <td>R <?php echo number_format($payroll['netSalary'], 2); ?></td>
                                    <td>
                                        <a href="generate_payslip.php?employee_id=<?php echo $payroll['employeeId']; ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-download"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>