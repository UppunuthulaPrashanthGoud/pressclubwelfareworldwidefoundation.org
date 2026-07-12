<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';
require_once 'includes/text_utils.php';

// Only admin can access this page
if (!isAdmin()) {
    header("Location: " . ADMIN_URL . "index.php");
    exit;
}

// Initialize variables
$action = isset($_GET['action']) ? sanitizeInput($_GET['action']) : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success = isset($_GET['success']) ? sanitizeInput($_GET['success']) : '';
$error = '';
$letter = [];

// Handle AJAX request for user details
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ajax']) && $_GET['ajax'] === 'get_user_details') {
    header('Content-Type: application/json');
    $user_id = (int)$_GET['user_id'];
    $db = getDbConnection();
    try {
        // Fetch address along with other details
        $stmt = $db->prepare("SELECT name, email, mobile, address FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode([
            'success' => !!$user,
            'user' => $user
        ]);
    } catch (Exception $e) {
        logError('Error fetching user details: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to fetch user details']);
    }
    exit;
}

$db = getDbConnection();

// Fetch initial data for Edit or Add
if ($action === 'add' && isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $user_id = (int)$_GET['user_id'];
    $stmt = $db->prepare("SELECT u.name, u.address FROM users u WHERE u.id = ? AND status = 'approved'");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        $error = "User not found or not approved.";
        $action = 'list';
    }
} elseif ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT a.*, u.name AS user_name FROM award_letters a JOIN users u ON a.user_id = u.id WHERE a.id = ?");
    $stmt->execute([$id]);
    $letter = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$letter) {
        $error = "Award letter not found.";
        $action = 'list';
    }
}

// Initialize letter array for new records
if ($action === 'add') {
    $letter = [
        'recipient_name' => '',
        'recipient_address' => '',
        'award_title' => '',
        'field_of_contribution' => '',
        'issue_date' => date('Y-m-d'),
        'ceremony_date' => '',
        'ceremony_location' => '',
        'user_id' => '',
        'status' => 'active'
    ];
    if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
        $user_id = (int)$_GET['user_id'];
        $stmt = $db->prepare("SELECT name, address FROM users WHERE id = ? AND status = 'approved'");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $letter['recipient_name'] = $user['name'];
            $letter['recipient_address'] = $user['address'];
            $letter['user_id'] = $user_id;
        }
    }
}

// Fetch Active Awards and Venues for Dropdowns
$active_awards = [];
$active_venues = [];
if ($action === 'add' || $action === 'edit') {
    try {
        $stmtAwards = $db->query("SELECT award_name FROM awards_list WHERE status = 'active' ORDER BY award_name ASC");
        $active_awards = $stmtAwards->fetchAll(PDO::FETCH_ASSOC);

        $stmtVenues = $db->query("SELECT venue_name FROM venues_list WHERE status = 'active' ORDER BY venue_name ASC");
        $active_venues = $stmtVenues->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        logError("Database error fetching awards/venues: " . $e->getMessage());
    }
}

/**
 * Function to generate a unique award letter number
 */
function generateLetterNumber() {
    global $db;
    try {
        $stmt = $db->query("SELECT letter_no FROM award_letters ORDER BY id DESC LIMIT 1");
        $lastLetter = $stmt->fetch();
        
        $nextNumeric = $lastLetter ? ((int) substr($lastLetter['letter_no'], 5)) + 1 : 1;
        
        $exists = true;
        while ($exists) {
            $candidate = 'GDAWD' . str_pad($nextNumeric, 5, '0', STR_PAD_LEFT);
            $checkStmt = $db->prepare("SELECT COUNT(*) FROM award_letters WHERE letter_no = ?");
            $checkStmt->execute([$candidate]);
            if ($checkStmt->fetchColumn() == 0) {
                return $candidate; 
            }
            $nextNumeric++; 
        }
    } catch (PDOException $e) {
        // Fallback if table doesn't exist yet to prevent fatal error before creation
        return 'GDAWD' . str_pad(1, 5, '0', STR_PAD_LEFT);
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        $error = 'Invalid CSRF token.';
    } else if (isset($_POST['action'])) {
        $formAction = $_POST['action'];
        
        if ($formAction === 'add' || $formAction === 'edit') {
            $recipient_name = isset($_POST['recipient_name']) ? sanitizeInput($_POST['recipient_name']) : '';
            $recipient_address = isset($_POST['recipient_address']) ? sanitizeInput($_POST['recipient_address']) : '';
            $award_title = isset($_POST['award_title']) ? sanitizeInput($_POST['award_title']) : '';
            $field_of_contribution = isset($_POST['field_of_contribution']) ? sanitizeInput($_POST['field_of_contribution']) : '';
            $issue_date = isset($_POST['issue_date']) ? sanitizeInput($_POST['issue_date']) : '';
            $ceremony_date = isset($_POST['ceremony_date']) ? sanitizeInput($_POST['ceremony_date']) : '';
            $ceremony_location = isset($_POST['ceremony_location']) ? sanitizeInput($_POST['ceremony_location']) : '';
            $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : null;
            $status = isset($_POST['status']) ? sanitizeInput($_POST['status']) : 'active';
            
            // Validate basic fields
            if (empty($recipient_name) || empty($recipient_address) || empty($award_title) || empty($field_of_contribution) || empty($issue_date) || empty($ceremony_date) || empty($ceremony_location) || empty($user_id)) {
                $error = "Please fill all required fields.";
            } elseif (!in_array($status, ['active', 'inactive'])) {
                $error = "Invalid status.";
            } else {
                try {
                    if ($formAction === 'add') {
                        $letter_no = generateLetterNumber();
                        $stmt = $db->prepare("
                            INSERT INTO award_letters (letter_no, recipient_name, recipient_address, award_title, field_of_contribution, issue_date, ceremony_date, ceremony_location, status, user_id, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                        ");
                        $stmt->execute([$letter_no, $recipient_name, $recipient_address, $award_title, $field_of_contribution, $issue_date, $ceremony_date, $ceremony_location, $status, $user_id]);
                    } else {
                        $letter_id = isset($_POST['letter_id']) ? (int)$_POST['letter_id'] : 0;
                        $stmt = $db->prepare("
                            UPDATE award_letters 
                            SET recipient_name = ?, recipient_address = ?, award_title = ?, 
                                field_of_contribution = ?, issue_date = ?, ceremony_date = ?, 
                                ceremony_location = ?, status = ?, user_id = ?, updated_at = NOW() 
                            WHERE id = ?
                        ");
                        $success = $stmt->execute([$recipient_name, $recipient_address, $award_title, $field_of_contribution, $issue_date, $ceremony_date, $ceremony_location, $status, $user_id, $letter_id]);
                        if (!$success) {
                            throw new Exception("Failed to update award letter in database.");
                        }
                    }
                    
                    $success = "Award Letter successfully " . ($formAction === 'add' ? 'created!' : 'updated!');
                    header("Location: awards_letters.php?success=" . urlencode($success));
                    exit;
                } catch (Exception $e) {
                    logError('Award Letter processing error: ' . $e->getMessage());
                    $error = "Error: " . $e->getMessage();
                }
            }
        } elseif ($formAction === 'delete') {
            $letter_id = isset($_POST['letter_id']) ? (int)$_POST['letter_id'] : 0;
            try {
                $stmt = $db->prepare("DELETE FROM award_letters WHERE id = ?");
                $stmt->execute([$letter_id]);
                
                $success = "Award Letter successfully deleted!";
                header("Location: awards_letters.php?success=" . urlencode($success));
                exit;
            } catch (Exception $e) {
                logError('Award Letter deletion error: ' . $e->getMessage());
                $error = "Deletion error: " . $e->getMessage();
            }
        }
    }
}

// Get award letters for list with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

try {
    $stmt = $db->prepare("SELECT COUNT(*) FROM award_letters");
    $stmt->execute();
    $totalRecords = $stmt->fetchColumn();
    
    $stmt = $db->prepare("SELECT a.*, u.name as user_name FROM award_letters a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$limit, $offset]);
    $letters = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $totalPages = ceil($totalRecords / $limit);
} catch (PDOException $e) {
    // Handling error if table isn't created yet
    logError('Database error in award letter listing: ' . $e->getMessage());
    $error = "Database error: Please create the 'award_letters' table using the provided SQL query.";
    $letters = [];
    $totalPages = 0;
}

$pageTitle = ($action === 'add') ? "Add New Award Letter" : (($action === 'edit') ? "Edit Award Letter" : "Award Letters Management");

include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <?php if ($action === 'add'): ?>
                    <i class="fas fa-plus"></i> Add New Award Letter
                <?php elseif ($action === 'edit'): ?>
                    <i class="fas fa-edit"></i> Edit Award Letter
                <?php else: ?>
                    <i class="fas fa-award"></i> Award Letters Management
                <?php endif; ?>
            </h1>
            <div class="page-actions">
                <?php if ($action === 'add' || $action === 'edit'): ?>
                    <a href="awards_letters.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                <?php else: ?>
                    <a href="awards_letters.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Award Letter
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($action === 'add' || $action === 'edit'): ?>
            <div class="admin-card">
                <div class="card-header">
                    <i class="fas fa-<?php echo $action === 'add' ? 'plus' : 'edit'; ?>"></i> <?php echo ($action === 'add') ? "Add New Award Letter" : "Edit Award Letter"; ?>
                </div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="action" value="<?php echo $action; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="letter_id" value="<?php echo $letter['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="user_id" class="form-label">Select User <span class="text-danger">*</span></label>
                                <?php if ($action === 'edit'): ?>
                                    <input type="hidden" name="user_id" value="<?php echo $letter['user_id']; ?>">
                                    <input type="text" class="form-control" disabled value="<?php echo htmlspecialchars($letter['user_name'] ?? ''); ?>">
                                <?php else: ?>
                                    <select class="form-select" id="user_id" name="user_id" required>
                                        <option value="">-- Select User --</option>
                                        <?php
                                        try {
                                            $userStmt = $db->query("SELECT id, name FROM users WHERE status = 'approved' ORDER BY name");
                                            $users = $userStmt->fetchAll(PDO::FETCH_ASSOC);
                                            foreach ($users as $user) {
                                                $selected = (isset($letter['user_id']) && $letter['user_id'] == $user['id']) ? 'selected' : '';
                                                echo "<option value='{$user['id']}' $selected>" . htmlspecialchars($user['name']) . "</option>";
                                            }
                                        } catch (Exception $e) {
                                            echo "<option value=''>Error loading users</option>";
                                        }
                                        ?>
                                    </select>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="recipient_name" class="form-label">Recipient Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="recipient_name" name="recipient_name" required 
                                       value="<?php echo htmlspecialchars($letter['recipient_name'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="recipient_address" class="form-label">Recipient Address <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="recipient_address" name="recipient_address" required rows="3" placeholder="Enter full address for the letterhead"><?php echo htmlspecialchars($letter['recipient_address'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="award_title" class="form-label">Award Title <span class="text-danger">*</span></label>
                                <select class="form-select" id="award_title" name="award_title" required>
                                    <option value="">-- Select Award Title --</option>
                                    <?php foreach ($active_awards as $award): ?>
                                        <option value="<?php echo htmlspecialchars($award['award_name']); ?>" 
                                            <?php echo (isset($letter['award_title']) && strcasecmp($letter['award_title'], $award['award_name']) == 0) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($award['award_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="field_of_contribution" class="form-label">Field of Contribution <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="field_of_contribution" name="field_of_contribution" required 
                                       value="<?php echo htmlspecialchars($letter['field_of_contribution'] ?? ''); ?>" placeholder="e.g., Social Work, Education, etc.">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="issue_date" class="form-label">Letter Issue Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="issue_date" name="issue_date" required
                                       value="<?php echo htmlspecialchars($letter['issue_date'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="ceremony_date" class="form-label">Ceremony Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="ceremony_date" name="ceremony_date" required
                                       value="<?php echo htmlspecialchars($letter['ceremony_date'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="ceremony_location" class="form-label">Ceremony Location <span class="text-danger">*</span></label>
                                <select class="form-select" id="ceremony_location" name="ceremony_location" required>
                                    <option value="">-- Select Venue --</option>
                                    <?php foreach ($active_venues as $venue): ?>
                                        <option value="<?php echo htmlspecialchars($venue['venue_name']); ?>" 
                                            <?php echo (isset($letter['ceremony_location']) && $letter['ceremony_location'] == $venue['venue_name']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($venue['venue_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active" <?php echo (isset($letter['status']) && $letter['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo (isset($letter['status']) && $letter['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo ($action === 'add') ? "Create Award Letter" : "Update Letter"; ?>
                            </button>
                            <a href="awards_letters.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="admin-card mb-4">
                <div class="card-header">
                    <i class="fas fa-users"></i> Available Users
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                try {
                                    $userStmt = $db->query("SELECT id, name, email, mobile FROM users WHERE status = 'approved' ORDER BY name");
                                    $users = $userStmt->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($users as $user):
                                ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($user['mobile']); ?></td>
                                            <td>
                                                <a href="awards_letters.php?action=add&user_id=<?php echo $user['id']; ?>" 
                                                   class="btn btn-sm btn-primary" title="Create Award Letter">
                                                    <i class="fas fa-plus"></i> Create Letter
                                                </a>
                                            </td>
                                        </tr>
                                <?php 
                                    endforeach; 
                                } catch (Exception $e) {
                                    // Handle missing table silently on the view
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="admin-card">
                <div class="card-header">
                    <i class="fas fa-envelope-open-text"></i> Award Letters List
                </div>
                <div class="card-body">
                    <?php if (count($letters) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Ref No.</th>
                                        <th>Recipient Name</th>
                                        <th>Award Title</th>
                                        <th>Field of Contribution</th>
                                        <th>Issue Date</th>
                                        <th>Ceremony Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($letters as $let): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($let['letter_no']); ?></td>
                                            <td><?php echo htmlspecialchars($let['recipient_name']); ?></td>
                                            <td><span class="badge badge-info"><?php echo htmlspecialchars($let['award_title']); ?></span></td>
                                            <td><?php echo htmlspecialchars($let['field_of_contribution']); ?></td>
                                            <td><?php echo date('d-m-Y', strtotime($let['issue_date'])); ?></td>
                                            <td><?php echo date('d-m-Y', strtotime($let['ceremony_date'])); ?></td>
                                            <td>
                                                <span class="badge <?php echo $let['status'] == 'active' ? 'badge-success' : 'badge-danger'; ?>">
                                                    <?php echo $let['status'] == 'active' ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="awards_letters.php?action=edit&id=<?php echo $let['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-sm btn-danger delete-letter-btn" data-id="<?php echo $let['id']; ?>" data-letter-no="<?php echo htmlspecialchars($let['letter_no']); ?>" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <button class="btn btn-sm btn-success generate-letter" 
                                                        data-letter='<?php echo json_encode([
                                                            'letter_no' => $let['letter_no'],
                                                            'recipient_name' => $let['recipient_name'],
                                                            'recipient_address' => $let['recipient_address'],
                                                            'award_title' => $let['award_title'],
                                                            'field_of_contribution' => $let['field_of_contribution'],
                                                            'issue_date' => $let['issue_date'],
                                                            'ceremony_date' => $let['ceremony_date'],
                                                            'ceremony_location' => $let['ceremony_location']
                                                        ]); ?>' 
                                                        title="Download PDF">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No award letters found.
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($totalPages > 1): ?>
                    <div class="card-footer">
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center mb-0">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="modal fade" id="deleteLetterModal" tabindex="-1" aria-labelledby="deleteLetterModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteLetterModalLabel">Confirm Deletion</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Do you really want to delete Award Letter <strong id="letterNoSpan"></strong>?</p>
                            <p class="text-danger mt-2">This action cannot be undone.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <form method="post" id="deleteLetterForm">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="letter_id" id="delete_letter_id">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const { jsPDF } = window.jspdf;
    
    function formatDate(inputDate) {
        try {
            let date = new Date(inputDate);
            if (isNaN(date.getTime())) {
                const parts = inputDate.split('-');
                if (parts.length === 3) {
                    date = new Date(parts[0], parts[1]-1, parts[2]);
                }
            }
            if (isNaN(date.getTime())) return inputDate;
            
            let day = String(date.getDate()).padStart(2, '0');
            let month = String(date.getMonth() + 1).padStart(2, '0');
            let year = date.getFullYear();
            return `${day}-${month}-${year}`;
        } catch (e) {
            return inputDate;
        }
    }

    document.querySelectorAll('.generate-letter').forEach(button => {
        button.addEventListener('click', async function() {
            try {
                showNotification('Generating letter, please wait...', 'info');
                
                let data = JSON.parse(this.getAttribute('data-letter'));
                const formattedIssueDate = formatDate(data.issue_date);
                const formattedCeremonyDate = formatDate(data.ceremony_date);

                // Container for rendering html2canvas (A4 proportions)
                const container = document.createElement('div');
                container.style.cssText = `
                    width: 794px;
                    height: 1123px;
                    position: fixed;
                    left: -9999px;
                    font-family: Arial, Helvetica, sans-serif;
                    background-color: white;
                    color: black;
                    box-sizing: border-box;
                `;
                
                // Format Recipient Address securely
                const formattedAddress = data.recipient_address.replace(/\n/g, '<br>');

                // Set image path to the new distinct award-letter template saved on the server
                const templateImgPath = '<?php echo SITE_URL; ?>/templates/award-letter-template.png';

                container.innerHTML = `
                <!-- Background Image Template -->
                <img src="${templateImgPath}" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;" crossorigin="anonymous">
                
                <!-- Dynamic Content overlaid on the blank space of the template -->
                <div style="position: relative; z-index: 1; padding: 360px 80px 0 80px; height: 100%; box-sizing: border-box;">
                    
                    <!-- Recipient Section -->
                    <div style="margin-bottom: 25px; font-size: 15px; line-height: 1.5;">
                        <p style="margin: 0 0 8px 0;"><strong>TO:</strong></p>
                        <table style="border: none; width: 100%;">
                            <tr>
                                <td style="width: 80px; vertical-align: top;"><strong>Name:</strong></td>
                                <td>${data.recipient_name}</td>
                            </tr>
                            <tr>
                                <td style="vertical-align: top;"><strong>Address:</strong></td>
                                <td>${formattedAddress}</td>
                            </tr>
                            <tr>
                                <td style="vertical-align: top;"><strong>Date:</strong></td>
                                <td>${formattedIssueDate}</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Subject -->
                    <div style="margin-bottom: 30px; font-size: 15px;">
                        <p style="margin: 0; font-weight: bold; text-decoration: underline;">Subject: Congratulations on Your ${data.award_title} Selection</p>
                    </div>

                    <!-- Salutation & Body -->
                    <div style="margin-bottom: 20px; font-size: 15px; line-height: 1.8; text-align: justify;">
                        <p style="margin-bottom: 15px;">Dear <strong>${data.recipient_name}</strong>,</p>

                        <p style="margin-bottom: 15px;">
                            It is with great pleasure that we inform you of your selection for the <strong>${data.award_title}</strong>.
                        </p>
                        
                        <p style="margin-bottom: 15px;">
                            Your dedication to and your recent contributions to have set a remarkable standard.
                            This award recognizes your <strong>${data.field_of_contribution}</strong> and the impact you have made on our community.
                        </p>
                        
                        <p style="margin-bottom: 15px;">
                            As a token of our appreciation, you will receive rewards, e.g., certificate, trophy.<br>
                            We would like to formally present this to you at INDIAN AWARDS CEREMONY<br>
                            <strong>${formattedCeremonyDate}</strong> at <strong>${data.ceremony_location}</strong>.
                        </p>

                        <p style="margin-bottom: 15px;">
                            Once again, congratulations on this well-deserved achievement. We look forward to
                            your continued success.
                        </p>
                    </div>
                </div>
                `;
                
                document.body.appendChild(container);
                
                // Wait for the background image to fully load before generating PDF
                const images = container.querySelectorAll('img');
                const imagePromises = Array.from(images).map(img => {
                    return new Promise((resolve) => {
                        if (img.complete) {
                            resolve();
                        } else {
                            img.onload = resolve;
                            img.onerror = resolve;
                        }
                    });
                });
                
                await Promise.all(imagePromises);

                const canvas = await html2canvas(container, {
                    scale: 2, // 2x scale for print quality resolution
                    useCORS: true,
                    logging: false,
                    backgroundColor: null,
                    width: 794,
                    height: 1123
                });
                
                const imgData = canvas.toDataURL('image/jpeg', 1.0);
                
                const pdf = new jsPDF({
                    orientation: 'portrait',
                    unit: 'px',
                    format: [794, 1123]
                });
                
                pdf.addImage(imgData, 'JPEG', 0, 0, 794, 1123);
                
                const filename = `Award_Letter_${data.letter_no}_${data.recipient_name.replace(/[^a-zA-Z0-9]/g, '_')}.pdf`;
                pdf.save(filename);
                
                document.body.removeChild(container);
                showNotification('Award letter downloaded successfully!', 'success');
                
            } catch (error) {
                console.error('Letter generation error:', error);
                showNotification('Error generating letter. Please try again.', 'danger');
                
                const containers = document.querySelectorAll('div[style*="left: -9999px"]');
                containers.forEach(container => {
                    if (container.parentNode) {
                        container.parentNode.removeChild(container);
                    }
                });
            }
        });
    });

    // Delete functionality
    const deleteLetterModal = document.getElementById('deleteLetterModal');
    const deleteLetterForm = document.getElementById('deleteLetterForm');
    const letterNoSpan = document.getElementById('letterNoSpan');
    const deleteLetterIdInput = document.getElementById('delete_letter_id');
    
    document.querySelectorAll('.delete-letter-btn').forEach(button => {
        button.addEventListener('click', function() {
            const letterId = this.getAttribute('data-id');
            const letterNo = this.getAttribute('data-letter-no');
            
            if (letterNoSpan) letterNoSpan.textContent = letterNo;
            if (deleteLetterIdInput) deleteLetterIdInput.value = letterId;
            
            if (deleteLetterModal && typeof bootstrap !== 'undefined') {
                const modal = new bootstrap.Modal(deleteLetterModal);
                modal.show();
            }
        });
    });
    
    if (deleteLetterForm) {
        deleteLetterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
            submitBtn.disabled = true;
            
            const formData = new FormData(this);
            fetch('awards_letters.php', { method: 'POST', body: formData })
            .then(response => {
                if (response.ok) window.location.reload();
                else throw new Error('Network response was not ok');
            })
            .catch(error => {
                showNotification('Error deleting. Please try again.', 'danger');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }

    // Auto fill recipient name and address on user selection via AJAX
    const userSelect = document.getElementById('user_id');
    const recipientNameField = document.getElementById('recipient_name');
    const recipientAddressField = document.getElementById('recipient_address');
    
    if (userSelect && recipientNameField) {
        userSelect.addEventListener('change', function() {
            const selectedOption = userSelect.options[userSelect.selectedIndex];
            const userId = userSelect.value;
            
            if (selectedOption.text !== '-- Select User --' && userId) {
                recipientNameField.value = selectedOption.text;
                
                // Fetch user address dynamically
                fetch(`awards_letters.php?ajax=get_user_details&user_id=${userId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.user && recipientAddressField) {
                            recipientAddressField.value = data.user.address || '';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching user address:', error);
                        showNotification('Error fetching address data.', 'danger');
                    });
            } else {
                recipientNameField.value = '';
                if (recipientAddressField) recipientAddressField.value = '';
            }
        });
    }

    // Form validation wrapper
    const mainForm = document.querySelector('form[method="post"]:not(#deleteLetterForm)');
    if (mainForm) {
        mainForm.addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;
            let firstInvalidField = null;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                    if (!firstInvalidField) firstInvalidField = field;
                    
                    field.addEventListener('input', function() {
                        this.classList.remove('is-invalid');
                    }, { once: true });
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                if (firstInvalidField) {
                    firstInvalidField.focus();
                    firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                showNotification('Please fill all required fields.', 'danger');
            }
        });
    }

    // Alert dismissal
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        if (!alert.querySelector('.btn-close')) {
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        if (alert.parentNode) alert.parentNode.removeChild(alert);
                    }, 500);
                }
            }, 5000);
        }
    });

    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show`;
        notification.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 400px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);';
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : (type === 'info' ? 'info-circle' : 'exclamation-triangle')}"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.style.transition = 'opacity 0.5s ease';
                notification.style.opacity = '0';
                setTimeout(() => {
                    if (notification.parentNode) notification.parentNode.removeChild(notification);
                }, 500);
            }
        }, 4000);
    }
});
</script>

<?php include 'includes/footer.php'; ?>