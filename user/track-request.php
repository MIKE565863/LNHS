<?php
require_once '../includes/auth.php';
$auth->requireUser();

$user = $auth->getCurrentUser();
$conn = getDB();

$request = null;
$error = '';

// Handle search
if (isset($_GET['request_number']) && !empty($_GET['request_number'])) {
    $stmt = $conn->prepare("
        SELECT dr.*, 
               COUNT(du.id) as file_count
        FROM document_requests dr
        LEFT JOIN document_uploads du ON dr.id = du.request_id
        WHERE dr.request_number = ? AND dr.user_id = ?
        GROUP BY dr.id
    ");
    $stmt->execute([$_GET['request_number'], $user['id']]);
    $request = $stmt->fetch();
    
    if (!$request) {
        $error = 'Request not found or you do not have permission to view it.';
    }
}

function getStatusIcon($status) {
    $icons = [
        'pending' => 'fas fa-clock text-warning',
        'processing' => 'fas fa-cog text-info',
        'approved' => 'fas fa-check text-success',
        'denied' => 'fas fa-times text-danger',
        'ready_for_pickup' => 'fas fa-bell text-primary',
        'completed' => 'fas fa-check-circle text-success'
    ];
    return $icons[$status] ?? 'fas fa-question text-secondary';
}

function getStatusStep($status) {
    $steps = [
        'pending' => 1,
        'processing' => 2,
        'approved' => 3,
        'denied' => 3,
        'ready_for_pickup' => 4,
        'completed' => 5
    ];
    return $steps[$status] ?? 1;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Request - LNHS Documents Request Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-graduation-cap me-2"></i>
                LNHS Documents Portal
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user me-2"></i><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 sidebar">
                <div class="position-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="request-document.php">
                                <i class="fas fa-plus-circle"></i>
                                New Request
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="my-requests.php">
                                <i class="fas fa-file-alt"></i>
                                My Requests
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="track-request.php">
                                <i class="fas fa-search"></i>
                                Track Request
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="notifications.php">
                                <i class="fas fa-bell"></i>
                                Notifications
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Track Request</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="dashboard.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                        </a>
                    </div>
                </div>

                <!-- Search Form -->
                <div class="row mb-4">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-search me-2"></i>Search Request</h5>
                            </div>
                            <div class="card-body">
                                <form method="GET" action="">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="request_number" 
                                               placeholder="Enter your request number (e.g., REQ-2024-1234)" 
                                               value="<?php echo isset($_GET['request_number']) ? htmlspecialchars($_GET['request_number']) : ''; ?>" required>
                                        <button class="btn btn-primary" type="submit">
                                            <i class="fas fa-search me-2"></i>Track Request
                                        </button>
                                    </div>
                                    <small class="form-text text-muted mt-2">
                                        Enter your request number to track the status of your document request.
                                    </small>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>How to Track</h6>
                            </div>
                            <div class="card-body">
                                <ol class="list-unstyled">
                                    <li><i class="fas fa-check me-2 text-success"></i>Enter your request number</li>
                                    <li><i class="fas fa-check me-2 text-success"></i>Click "Track Request"</li>
                                    <li><i class="fas fa-check me-2 text-success"></i>View current status</li>
                                </ol>
                                <hr>
                                <small class="text-muted">
                                    Your request number was provided when you submitted your request.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($request): ?>
                <!-- Request Details -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-file-alt me-2"></i>
                                    Request Details - <?php echo htmlspecialchars($request['request_number']); ?>
                                </h5>
                                <span class="badge bg-<?php echo getStatusBadge($request['status']); ?> fs-6">
                                    <i class="<?php echo getStatusIcon($request['status']); ?> me-2"></i>
                                    <?php echo formatStatus($request['status']); ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <!-- Progress Steps -->
                                <div class="progress-steps mb-4">
                                    <div class="progress-step <?php echo getStatusStep($request['status']) >= 1 ? 'completed' : ''; ?>">
                                        <div class="progress-step-circle">
                                            <i class="fas fa-file"></i>
                                        </div>
                                        <small>Submitted</small>
                                    </div>
                                    <div class="progress-step <?php echo getStatusStep($request['status']) >= 2 ? 'active' : ''; ?> <?php echo getStatusStep($request['status']) > 2 ? 'completed' : ''; ?>">
                                        <div class="progress-step-circle">
                                            <i class="fas fa-cog"></i>
                                        </div>
                                        <small>Processing</small>
                                    </div>
                                    <div class="progress-step <?php echo getStatusStep($request['status']) >= 3 ? 'active' : ''; ?> <?php echo getStatusStep($request['status']) > 3 ? 'completed' : ''; ?>">
                                        <div class="progress-step-circle">
                                            <i class="fas fa-check"></i>
                                        </div>
                                        <small><?php echo $request['status'] === 'denied' ? 'Denied' : 'Approved'; ?></small>
                                    </div>
                                    <?php if ($request['status'] !== 'denied'): ?>
                                    <div class="progress-step <?php echo getStatusStep($request['status']) >= 4 ? 'active' : ''; ?> <?php echo getStatusStep($request['status']) > 4 ? 'completed' : ''; ?>">
                                        <div class="progress-step-circle">
                                            <i class="fas fa-bell"></i>
                                        </div>
                                        <small>Ready</small>
                                    </div>
                                    <div class="progress-step <?php echo getStatusStep($request['status']) >= 5 ? 'completed' : ''; ?>">
                                        <div class="progress-step-circle">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <small>Completed</small>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Request Information -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Request Information</h6>
                                        <table class="table table-sm table-borderless">
                                            <tr>
                                                <td><strong>Request Number:</strong></td>
                                                <td><?php echo htmlspecialchars($request['request_number']); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Document Type:</strong></td>
                                                <td><?php echo ucwords(str_replace('_', ' ', $request['document_type'])); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Purpose:</strong></td>
                                                <td><?php echo htmlspecialchars($request['purpose']); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Preferred Date:</strong></td>
                                                <td><?php echo date('M j, Y', strtotime($request['preferred_release_date'])); ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Status Information</h6>
                                        <table class="table table-sm table-borderless">
                                            <tr>
                                                <td><strong>Current Status:</strong></td>
                                                <td>
                                                    <span class="badge bg-<?php echo getStatusBadge($request['status']); ?>">
                                                        <?php echo formatStatus($request['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Submitted:</strong></td>
                                                <td><?php echo date('M j, Y g:i A', strtotime($request['created_at'])); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Last Updated:</strong></td>
                                                <td><?php echo date('M j, Y g:i A', strtotime($request['updated_at'])); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Files Uploaded:</strong></td>
                                                <td><?php echo $request['file_count']; ?> files</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <?php if (!empty($request['additional_notes'])): ?>
                                <div class="mt-3">
                                    <h6>Additional Notes</h6>
                                    <p class="text-muted"><?php echo htmlspecialchars($request['additional_notes']); ?></p>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($request['admin_notes'])): ?>
                                <div class="mt-3">
                                    <h6>Admin Notes</h6>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <?php echo htmlspecialchars($request['admin_notes']); ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <div class="mt-4">
                                    <a href="view-request.php?id=<?php echo $request['id']; ?>" class="btn btn-primary">
                                        <i class="fas fa-eye me-2"></i>View Full Details
                                    </a>
                                    <a href="my-requests.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-list me-2"></i>View All Requests
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>