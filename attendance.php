<?php
// Attendance tracking page
require_once 'db.php';
require_once 'header.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

// Handle filters
$employeeId = $_GET['employee_id'] ?? 0;
$searchDate = $_GET['search_date'] ?? '';
$employeeName = '';

if ($employeeId) {
    $stmt = $pdo->prepare("SELECT name FROM employees WHERE employee_id = ?");
    $stmt->execute([$employeeId]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    $employeeName = $employee ? $employee['name'] : '';
}

// Build query based on filters
$query = "SELECT a.*, e.name FROM attendance a 
          JOIN employees e ON a.employee_id = e.employee_id";
$conditions = [];
$params = [];

if ($employeeId) {
    $conditions[] = "a.employee_id = ?";
    $params[] = $employeeId;
}
if (!empty($searchDate)) {
    $conditions[] = "a.date = ?";
    $params[] = $searchDate;
}
if ($conditions) {
    $query .= " WHERE " . implode(' AND ', $conditions);
}
$query .= " ORDER BY a.date DESC LIMIT 50";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$attendanceRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all employees for dropdown
$employees = $pdo->query("SELECT employee_id, name FROM employees ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Google Fonts + Font Awesome -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>
    body {
        background-color: #f1f4f9;
        font-family: 'Inter', sans-serif;
    }

    .attendance-card {
        border-radius: 14px;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: 1px solid #dfdfdfff;
        background-color: #ffffff;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
    }

    .attendance-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .attendance-card .card-header {
        background: linear-gradient(135deg, #0f2027, #1c3b49ff);
        color: white;
        padding: 1rem 1.25rem;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .attendance-card .card-body {
        padding: 1.25rem 1.5rem;
    }

    .attendance-card .card-body p {
        margin-bottom: 0.75rem;
    }

    .badge {
        font-size: 0.85rem;
        padding: 0.4em 0.75em;
        border-radius: 6px;
    }

    .badge.bg-success {
        background-color: #28a745 !important;
    }

    .badge.bg-danger {
        background-color: #dc3545 !important;
    }

    .filter-form {
        margin-bottom: 2rem;
    }

    .filter-form .form-select,
    .filter-form .form-control {
        max-width: 300px;
        display: inline-block;
        margin-right: 1rem;
    }

    .filter-form .btn {
        vertical-align: top;
    }

    .header-section {
        text-align: center;
        margin-bottom: 2rem;
    }

    .header-section p {
        color: #6c757d;
    }

    .icon {
        margin-right: 8px;
        color: #555;
    }
</style>

<div class="container">
    <div class="header-section">
        <h2 class="display-5 mb-4">Attendance Tracking <?php echo $employeeName ? "for $employeeName" : ''; ?></h2>
    </div>
<hr class="mb-5">
    <!-- Filter form -->
    <form method="GET" class="filter-form d-flex flex-wrap align-items-center justify-content-center">
        <select name="employee_id" class="form-select mb-2">
            <option value="0">All Employees</option>
            <?php foreach ($employees as $emp): ?>
                <option value="<?php echo $emp['employee_id']; ?>" <?php echo $employeeId == $emp['employee_id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($emp['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="date" name="search_date" class="form-control mb-2" value="<?php echo htmlspecialchars($searchDate); ?>" placeholder="Date">

        <button type="submit" class="btn btn-primary mb-2">
            <i class="fas fa-filter me-1"></i> Filter
        </button>
    </form>

    

    <div class="row g-4">
        <?php if (count($attendanceRecords) > 0): ?>
            <?php foreach ($attendanceRecords as $record): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card attendance-card h-100">
                        <div class="card-header">
                            <?php echo !$employeeId ? htmlspecialchars($record['name']) : 'Attendance Record'; ?>
                        </div>
                        <div class="card-body">
                            <p><i class="fas fa-calendar icon"></i><strong>Date:</strong> <?php echo $record['date']; ?></p>
                            <p><i class="fas fa-calendar-day icon"></i><strong>Day:</strong> <?php echo date('l', strtotime($record['date'])); ?></p>
                            <p>
                                <i class="fas fa-user-check icon"></i>
                                <strong>Status:</strong>
                                <span class="badge <?php echo $record['status'] == 'Present' ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo $record['status']; ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted text-center">No attendance records found for your filters.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'footer.php'; ?>
