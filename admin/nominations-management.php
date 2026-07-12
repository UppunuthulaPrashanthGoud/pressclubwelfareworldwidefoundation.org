<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

$pageTitle = 'Nominations Management';
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success = isset($_GET['success']) ? sanitizeInput($_GET['success']) : '';
$error = '';
$nomination = [];

$db = getDbConnection();

$currentUserType = $_SESSION['user_type'] ?? 'member';
if (!in_array($currentUserType, ['admin', 'coordinator'])) {
    header("Location: " . SITE_URL . "/admin/index.php");
    exit;
}

// --- FETCH MASTER DATA (AWARDS & VENUES) ---
$masterAwards = [];
try {
    $stmtAw = $db->query("SELECT award_name FROM awards_list WHERE status = 'active' ORDER BY award_name ASC");
    $masterAwards = $stmtAw->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    // Fallback if table doesn't exist yet
    $masterAwards = ['HONORARY DOCTORATE AWARD', 'LIFETIME ACHIEVEMENT AWARD']; 
}

$masterVenues = [];
try {
    $stmtVen = $db->query("SELECT venue_name FROM venues_list WHERE status = 'active' ORDER BY venue_name ASC");
    $masterVenues = $stmtVen->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $masterVenues = ['Online / Virtual Ceremony'];
}
// -------------------------------------------

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $formAction = $_POST['action'];

        // --- ADD NEW NOMINATION ---
        if ($formAction === 'add') {
            $name = sanitizeInput($_POST['name']);
            $mobile = sanitizeInput($_POST['mobile']);
            $email = sanitizeInput($_POST['email']);
            $category = sanitizeInput($_POST['category']);
            $award = sanitizeInput($_POST['award']);
            $venue = sanitizeInput($_POST['venue']);
            $profession = sanitizeInput($_POST['profession']);
            $gender = sanitizeInput($_POST['gender']);
            $address = sanitizeInput($_POST['address']);
            $state = sanitizeInput($_POST['state']);
            $district = sanitizeInput($_POST['district']);
            $pincode = sanitizeInput($_POST['pincode']);
            $status = sanitizeInput($_POST['status']);
            
            // Checkboxes
            $is_online = isset($_POST['is_online']) ? 1 : 0;
            $is_offline = isset($_POST['is_offline']) ? 1 : 0;
            $is_ecert = isset($_POST['is_ecert']) ? 1 : 0;

            if (empty($name) || empty($mobile) || empty($email) || empty($award)) {
                $error = "Name, Mobile, Email, and Award are required.";
            } else {
                try {
                    // Check for duplicates
                    $stmt = $db->prepare("SELECT COUNT(*) FROM nominations WHERE mobile = ? OR email = ?");
                    $stmt->execute([$mobile, $email]);
                    if ($stmt->fetchColumn() > 0) {
                        throw new Exception("Mobile number or Email already registered.");
                    }

                    // Handle File Uploads
                    $uploaded_files = [];
                    $file_fields = ['profile_image', 'document_one', 'document_two', 'document_three', 'document_four'];
                    
                    foreach ($file_fields as $field) {
                        if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                            $uploadResult = uploadFile($_FILES[$field], '../uploads/documents'); 
                            if ($uploadResult['success']) {
                                $uploaded_files[$field] = $uploadResult['filename'];
                            } else {
                                throw new Exception("Error uploading $field: " . $uploadResult['message']);
                            }
                        } else {
                            $uploaded_files[$field] = null;
                        }
                    }

                    // Generate ID
                    do {
                        $registration_id = 'GHDAF' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
                        $stmt = $db->prepare("SELECT COUNT(*) FROM nominations WHERE registration_id = ?");
                        $stmt->execute([$registration_id]);
                    } while ($stmt->fetchColumn() > 0);

                    // Insert Query
                    $query = "INSERT INTO nominations (
                        registration_id, name, mobile, email, category, award, venue, 
                        profession, gender, address, state, district, pincode, 
                        is_online, is_offline, is_ecert, status,
                        profile_image, document_one, document_two, document_three, document_four, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                    
                    $stmt = $db->prepare($query);
                    $stmt->execute([
                        $registration_id, $name, $mobile, $email, $category, $award, $venue,
                        $profession, $gender, $address, $state, $district, $pincode,
                        $is_online, $is_offline, $is_ecert, $status,
                        $uploaded_files['profile_image'], $uploaded_files['document_one'], 
                        $uploaded_files['document_two'], $uploaded_files['document_three'], 
                        $uploaded_files['document_four']
                    ]);

                    $success = "New nomination added successfully! Reg ID: " . $registration_id;
                    header("Location: nominations-management.php?success=" . urlencode($success));
                    exit;

                } catch (Exception $e) {
                    $error = "Error: " . $e->getMessage();
                }
            }
        }

        // --- EDIT NOMINATION ---
        if ($formAction === 'edit') {
            $name = sanitizeInput($_POST['name']);
            $mobile = sanitizeInput($_POST['mobile']);
            $email = sanitizeInput($_POST['email']);
            $category = sanitizeInput($_POST['category']);
            $award = sanitizeInput($_POST['award']);
            $venue = sanitizeInput($_POST['venue']);
            $profession = sanitizeInput($_POST['profession']);
            $gender = sanitizeInput($_POST['gender']);
            $address = sanitizeInput($_POST['address']);
            $state = sanitizeInput($_POST['state']);
            $district = sanitizeInput($_POST['district']);
            $pincode = sanitizeInput($_POST['pincode']);
            $status = sanitizeInput($_POST['status']);
            
            // Checkboxes
            $is_online = isset($_POST['is_online']) ? 1 : 0;
            $is_offline = isset($_POST['is_offline']) ? 1 : 0;
            $is_ecert = isset($_POST['is_ecert']) ? 1 : 0;

            if (empty($name) || empty($mobile) || empty($email) || empty($award)) {
                $error = "Name, Mobile, Email, and Award are required.";
            } else {
                try {
                    // Handle File Uploads if new files are provided
                    $stmt = $db->prepare("SELECT profile_image, document_one, document_two, document_three, document_four FROM nominations WHERE id = ?");
                    $stmt->execute([$id]);
                    $currentFiles = $stmt->fetch(PDO::FETCH_ASSOC);

                    $uploaded_files = [];
                    $file_fields = ['profile_image', 'document_one', 'document_two', 'document_three', 'document_four'];
                    
                    foreach ($file_fields as $field) {
                        if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                            $uploadResult = uploadFile($_FILES[$field], '../uploads/documents'); 
                            if ($uploadResult['success']) {
                                $uploaded_files[$field] = $uploadResult['filename'];
                            } else {
                                throw new Exception("Error uploading $field: " . $uploadResult['message']);
                            }
                        } else {
                            $uploaded_files[$field] = $currentFiles[$field]; // Keep old file
                        }
                    }

                    // Update Query
                    $query = "UPDATE nominations SET 
                        name=?, mobile=?, email=?, category=?, award=?, venue=?, 
                        profession=?, gender=?, address=?, state=?, district=?, pincode=?, 
                        is_online=?, is_offline=?, is_ecert=?, status=?,
                        profile_image=?, document_one=?, document_two=?, document_three=?, document_four=?
                        WHERE id=?";
                    
                    $stmt = $db->prepare($query);
                    $stmt->execute([
                        $name, $mobile, $email, $category, $award, $venue,
                        $profession, $gender, $address, $state, $district, $pincode,
                        $is_online, $is_offline, $is_ecert, $status,
                        $uploaded_files['profile_image'], $uploaded_files['document_one'], 
                        $uploaded_files['document_two'], $uploaded_files['document_three'], 
                        $uploaded_files['document_four'],
                        $id
                    ]);

                    $success = "Nomination updated successfully!";
                    header("Location: nominations-management.php?success=" . urlencode($success));
                    exit;

                } catch (Exception $e) {
                    $error = "Error: " . $e->getMessage();
                }
            }
        }

        // --- APPROVE / REJECT (No Changes Needed Here) ---
        if ($formAction === 'approve' || $formAction === 'reject') {
            $nomination_id = isset($_POST['nomination_id']) ? (int)$_POST['nomination_id'] : 0;
            $status = $formAction === 'approve' ? 'approved' : 'rejected';
            $rejection_reason = isset($_POST['rejection_reason']) ? sanitizeInput($_POST['rejection_reason']) : '';

            try {
                $stmt = $db->prepare("SELECT * FROM nominations WHERE id = ?");
                $stmt->execute([$nomination_id]);
                $nomData = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$nomData) throw new Exception("Nomination not found.");

                $stmt = $db->prepare("UPDATE nominations SET status = ? WHERE id = ?");
                $stmt->execute([$status, $nomination_id]);

                // Update User Status Sync (Optional)
                if (!empty($nomData['registration_id'])) {
                    try {
                        $stmtUser = $db->prepare("UPDATE users SET status = ? WHERE registration_id = ?");
                        $stmtUser->execute([$status, $nomData['registration_id']]);
                    } catch (PDOException $ex) {}
                }

                // Send Email
                if (!empty($nomData['email'])) {
                    try {
                        require_once 'includes/email-templates.php';
                        $emailData = [
                            'registration_id' => $nomData['registration_id'],
                            'name' => $nomData['name'],
                            'email' => $nomData['email'],
                            'award' => $nomData['award'],
                            'status' => $status,
                            'created_at' => $nomData['created_at']
                        ];

                        $emailSubject = "Nomination Status Update - " . ORGANIZATION_NAME;
                        if ($status === 'approved') {
                            $emailBody = function_exists('getApprovalEmailTemplate') 
                                ? getApprovalEmailTemplate($emailData) 
                                : "Dear {$nomData['name']},<br>Your nomination for {$nomData['award']} has been APPROVED.";
                        } else {
                            $emailBody = function_exists('getRejectionEmailTemplate') 
                                ? getRejectionEmailTemplate($emailData, $rejection_reason)
                                : "Dear {$nomData['name']},<br>Your nomination has been rejected. Reason: $rejection_reason";
                        }
                        sendEmail($nomData['email'], $emailSubject, $emailBody, true);
                    } catch (Exception $e) {
                        error_log('Error sending nomination email: ' . $e->getMessage());
                    }
                }

                $success = "Nomination " . ($status === 'approved' ? 'approved' : 'rejected') . " successfully.";
                header("Location: nominations-management.php?success=" . urlencode($success));
                exit;

            } catch (Exception $e) {
                $error = "Error: " . $e->getMessage();
            }
        }

        // --- DELETE ---
        if ($formAction === 'delete') {
            $nomination_id = isset($_POST['nomination_id']) ? (int)$_POST['nomination_id'] : 0;
            try {
                $stmt = $db->prepare("SELECT profile_image, document_one, document_two, document_three, document_four, registration_id FROM nominations WHERE id = ?");
                $stmt->execute([$nomination_id]);
                $filesData = $stmt->fetch(PDO::FETCH_ASSOC);

                $stmt = $db->prepare("DELETE FROM nominations WHERE id = ?");
                $stmt->execute([$nomination_id]);

                // Cleanup Files
                $file_fields = ['profile_image', 'document_one', 'document_two', 'document_three', 'document_four'];
                foreach ($file_fields as $field) {
                    if (!empty($filesData[$field])) {
                        $filePath = __DIR__ . '/../uploads/documents/' . $filesData[$field];
                        if (file_exists($filePath)) unlink($filePath);
                    }
                }
                $success = "Nomination deleted successfully.";
                header("Location: nominations-management.php?success=" . urlencode($success));
                exit;
            } catch (Exception $e) {
                $error = "Error: " . $e->getMessage();
            }
        }
    }
}

// Fetch Data for View/Edit
if (($action === 'view' || $action === 'edit') && $id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM nominations WHERE id = ?");
        $stmt->execute([$id]);
        $nomination = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$nomination) {
            $error = "Nomination not found.";
            $action = 'list';
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
        $action = 'list';
    }
}

// Fetch List Data
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
$award_filter = isset($_GET['award']) ? sanitizeInput($_GET['award']) : '';

$whereConditions = [];
$params = [];

if (!empty($search)) {
    $whereConditions[] = "(name LIKE ? OR email LIKE ? OR mobile LIKE ? OR registration_id LIKE ?)";
    $params = array_fill(0, 4, "%$search%");
}
if (!empty($status_filter)) {
    $whereConditions[] = "status = ?";
    $params[] = $status_filter;
}
if (!empty($award_filter)) {
    $whereConditions[] = "award = ?";
    $params[] = $award_filter;
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Count total
$countQuery = "SELECT COUNT(*) FROM nominations $whereClause";
$stmt = $db->prepare($countQuery);
$stmt->execute($params);
$totalRecords = $stmt->fetchColumn();

// Fetch records
$query = "SELECT * FROM nominations $whereClause ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $db->prepare($query);
$stmt->execute($params);
$nominations = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalPages = ceil($totalRecords / $limit);

include 'includes/header.php';
?>

<script src="<?php echo SITE_URL; ?>/admin/assets/js/cities.js"></script>

<?php include 'includes/sidebar.php'; ?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        
        <!-- LIST VIEW -->
        <?php if ($action === 'list'): ?>
            <div class="page-header">
                <h1 class="page-title">Nomination Applications</h1>
                <div class="page-actions">
                     <a href="nominations-management.php?action=add" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Nomination</a>
                </div>
            </div>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="admin-card">
                <div class="card-header"><i class="fas fa-filter"></i> Filters</div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="search" placeholder="Search ID, Name, Email..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="status">
                                <option value="">All Statuses</option>
                                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="award">
                                <option value="">All Awards</option>
                                <?php foreach($masterAwards as $mAward): ?>
                                    <option value="<?php echo htmlspecialchars($mAward); ?>" <?php echo $award_filter == $mAward ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($mAward); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="admin-card mt-4">
                <div class="card-header"><i class="fas fa-list"></i> Nominations List</div>
                <div class="card-body table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Reg ID</th>
                                <th>Name</th>
                                <th>Contact</th>
                                <th>Award Category</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($nominations)): ?>
                                <tr><td colspan="6" class="text-center">No nominations found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($nominations as $n): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($n['registration_id']); ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if($n['profile_image']): ?>
                                                    <img src="../uploads/documents/<?php echo $n['profile_image']; ?>" class="rounded-circle me-2" style="width:30px;height:30px;object-fit:cover;">
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars($n['name']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <small><?php echo htmlspecialchars($n['mobile']); ?></small><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($n['email']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($n['award']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $n['status'] === 'approved' ? 'bg-success' : ($n['status'] === 'rejected' ? 'bg-danger' : 'bg-warning'); ?>">
                                                <?php echo ucfirst($n['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="nominations-management.php?action=view&id=<?php echo $n['id']; ?>" class="btn btn-sm btn-info" title="View"><i class="fas fa-eye"></i></a>
                                                <a href="nominations-management.php?action=edit&id=<?php echo $n['id']; ?>" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                                
                                                <?php if ($n['status'] === 'approved'): ?>
                                                    <a href="honorary_awards.php?action=add&nomination_id=<?php echo $n['id']; ?>" class="btn btn-sm btn-dark" title="Generate Award">
                                                        <i class="fas fa-award"></i> Generate
                                                    </a>
                                                <?php endif; ?>

                                                <button onclick="deleteNomination(<?php echo $n['id']; ?>, '<?php echo addslashes($n['name']); ?>')" class="btn btn-sm btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="card-footer">
                        <nav>
                            <ul class="pagination justify-content-center mb-0">
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>

        <!-- VIEW DETAILS -->
        <?php elseif ($action === 'view'): ?>
            <div class="page-header">
                <h1 class="page-title">Nomination Details</h1>
                <div class="page-actions">
                    <a href="nominations-management.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="admin-card">
                        <div class="card-header">Personal & Professional Info</div>
                        <div class="card-body">
                            <!-- Info Table... -->
                            <div class="row mb-3">
                                <div class="col-md-3 text-center">
                                    <?php if(!empty($nomination['profile_image'])): ?>
                                        <img src="../uploads/documents/<?php echo htmlspecialchars($nomination['profile_image']); ?>" class="img-thumbnail" style="max-width: 100%;">
                                    <?php else: ?>
                                        <div class="bg-light p-4 text-center border">No Photo</div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-9">
                                    <table class="table table-borderless">
                                        <tr><th width="30%">Reg ID:</th><td><?php echo htmlspecialchars($nomination['registration_id']); ?></td></tr>
                                        <tr><th>Name:</th><td><?php echo htmlspecialchars($nomination['name']); ?></td></tr>
                                        <tr><th>Email:</th><td><?php echo htmlspecialchars($nomination['email']); ?></td></tr>
                                        <tr><th>Mobile:</th><td><?php echo htmlspecialchars($nomination['mobile']); ?></td></tr>
                                        <tr><th>Gender:</th><td><?php echo htmlspecialchars($nomination['gender']); ?></td></tr>
                                        <tr><th>Address:</th><td><?php echo htmlspecialchars($nomination['address'] . ', ' . $nomination['district'] . ', ' . $nomination['state'] . ' - ' . $nomination['pincode']); ?></td></tr>
                                    </table>
                                </div>
                            </div>
                            <hr>
                            <h5>Application Details</h5>
                            <dl class="row">
                                <dt class="col-sm-3">Award Category</dt><dd class="col-sm-9"><?php echo htmlspecialchars($nomination['award']); ?></dd>
                                <dt class="col-sm-3">Category</dt><dd class="col-sm-9"><?php echo htmlspecialchars($nomination['category']); ?></dd>
                                <dt class="col-sm-3">Preferred Venue</dt><dd class="col-sm-9"><?php echo htmlspecialchars($nomination['venue']); ?></dd>
                                <dt class="col-sm-3">Qualification</dt><dd class="col-sm-9"><?php echo htmlspecialchars($nomination['qualification']); ?></dd>
                                <dt class="col-sm-3">Profession</dt><dd class="col-sm-9"><?php echo htmlspecialchars($nomination['profession']); ?></dd>
                            </dl>
                            <hr>
                            <h5>Bio & Talent</h5>
                            <dl>
                                <dt>About:</dt>
                                <dd class="bg-light p-2 rounded"><?php echo nl2br(htmlspecialchars($nomination['about'])); ?></dd>
                                <dt class="mt-2">Talent/Achievements:</dt>
                                <dd class="bg-light p-2 rounded"><?php echo nl2br(htmlspecialchars($nomination['talent'])); ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="admin-card mb-3">
                        <div class="card-header">Status & Actions</div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="fw-bold">Current Status:</label>
                                <span class="badge fs-6 <?php echo $nomination['status'] === 'approved' ? 'bg-success' : ($nomination['status'] === 'rejected' ? 'bg-danger' : 'bg-warning'); ?>">
                                    <?php echo ucfirst($nomination['status']); ?>
                                </span>
                            </div>
                            
                            <?php if ($nomination['status'] === 'pending'): ?>
                                <div class="d-grid gap-2 mb-3">
                                    <button onclick="approveNomination(<?php echo $nomination['id']; ?>, '<?php echo addslashes($nomination['name']); ?>')" class="btn btn-success">Approve</button>
                                    <button onclick="rejectNomination(<?php echo $nomination['id']; ?>, '<?php echo addslashes($nomination['name']); ?>')" class="btn btn-warning">Reject</button>
                                </div>
                            <?php endif; ?>

                            <?php if ($nomination['status'] === 'approved'): ?>
                                <div class="d-grid gap-2 mb-3">
                                    <a href="honorary_awards.php?action=add&nomination_id=<?php echo $nomination['id']; ?>" class="btn btn-dark">
                                        <i class="fas fa-award"></i> Generate Award
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="admin-card">
                        <div class="card-header">Documents</div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <?php 
                                $docs = [
                                    'ID Proof' => 'document_one',
                                    'Edu Cert' => 'document_two',
                                    'Achievement 1' => 'document_three',
                                    'Achievement 2' => 'document_four'
                                ];
                                foreach($docs as $label => $field): 
                                    if(!empty($nomination[$field])):
                                ?>
                                <li class="mb-2">
                                    <strong><?php echo $label; ?>:</strong><br>
                                    <a href="../uploads/documents/<?php echo htmlspecialchars($nomination[$field]); ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-1">
                                        <i class="fas fa-download"></i> View/Download
                                    </a>
                                </li>
                                <?php endif; endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        <!-- ADD / EDIT VIEW -->
        <?php elseif ($action === 'add' || $action === 'edit'): ?>
            <div class="page-header">
                <h1 class="page-title"><?php echo ($action === 'add') ? 'Add New Nomination' : 'Edit Nomination'; ?></h1>
                <div class="page-actions">
                    <a href="nominations-management.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
                </div>
            </div>

            <div class="admin-card">
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $action; ?>">
                        <?php if($action === 'edit'): ?>
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                        <?php endif; ?>

                        <h5 class="mb-3 text-primary">Basic Information</h5>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($nomination['name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Mobile <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="mobile" value="<?php echo htmlspecialchars($nomination['mobile'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($nomination['email'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Award <span class="text-danger">*</span></label>
                                <select class="form-select" name="award" required>
                                    <option value="">Select Award</option>
                                    <?php 
                                    $currentAward = $nomination['award'] ?? '';
                                    foreach($masterAwards as $aw) {
                                        $selected = ($currentAward === $aw) ? 'selected' : '';
                                        echo "<option value='$aw' $selected>$aw</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Venue</label>
                                <select class="form-select" name="venue">
                                    <option value="">Select Venue</option>
                                    <?php 
                                    $currentVenue = $nomination['venue'] ?? '';
                                    foreach($masterVenues as $mv) {
                                        $selected = ($currentVenue === $mv) ? 'selected' : '';
                                        echo "<option value='$mv' $selected>$mv</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="pending" <?php echo (isset($nomination['status']) && $nomination['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="approved" <?php echo (isset($nomination['status']) && $nomination['status'] === 'approved') ? 'selected' : ''; ?>>Approved</option>
                                    <option value="rejected" <?php echo (isset($nomination['status']) && $nomination['status'] === 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                             <div class="col-md-6">
                                <label class="form-label">Category</label>
                                <input type="text" class="form-control" name="category" value="<?php echo htmlspecialchars($nomination['category'] ?? ''); ?>">
                            </div>
                            <!-- Membership Type Removed -->
                            <div class="col-md-6">
                                <label class="form-label">Profession</label>
                                <input type="text" class="form-control" name="profession" value="<?php echo htmlspecialchars($nomination['profession'] ?? ''); ?>">
                            </div>
                        </div>

                        <h5 class="mb-3 mt-4 text-primary">Address Info</h5>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">State</label>
                                <select class="form-select" id="state" name="state"></select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">District</label>
                                <select class="form-select" id="district" name="district"></select>
                            </div>
                             <div class="col-md-4">
                                <label class="form-label">Pincode</label>
                                <input type="text" class="form-control" name="pincode" value="<?php echo htmlspecialchars($nomination['pincode'] ?? ''); ?>">
                            </div>
                            <div class="col-12 mt-2">
                                <label class="form-label">Address</label>
                                <textarea class="form-control" name="address"><?php echo htmlspecialchars($nomination['address'] ?? ''); ?></textarea>
                            </div>
                        </div>

                         <h5 class="mb-3 mt-4 text-primary">Documents (Optional for Admin)</h5>
                         <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Profile Image</label>
                                <input type="file" class="form-control" name="profile_image">
                            </div>
                             <div class="col-md-4">
                                <label class="form-label">ID Proof</label>
                                <input type="file" class="form-control" name="document_one">
                            </div>
                             <div class="col-md-4">
                                <label class="form-label">Educational Cert.</label>
                                <input type="file" class="form-control" name="document_two">
                            </div>
                         </div>
                         <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Achievement Proof 1</label>
                                <input type="file" class="form-control" name="document_three">
                            </div>
                             <div class="col-md-6">
                                <label class="form-label">Achievement Proof 2</label>
                                <input type="file" class="form-control" name="document_four">
                            </div>
                         </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary"><?php echo ($action === 'add') ? 'Create Nomination' : 'Update Nomination'; ?></button>
                            </div>
                        </div>
                        <input type="hidden" name="gender" value="<?php echo htmlspecialchars($nomination['gender'] ?? 'Male'); ?>">
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Hidden Forms for JS Actions -->
<form id="approveForm" method="POST" style="display:none;">
    <input type="hidden" name="action" value="approve">
    <input type="hidden" id="approveId" name="nomination_id">
</form>
<form id="rejectForm" method="POST" style="display:none;">
    <input type="hidden" name="action" value="reject">
    <input type="hidden" id="rejectId" name="nomination_id">
    <input type="hidden" id="rejectReason" name="rejection_reason">
</form>
<form id="deleteForm" method="POST" style="display:none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" id="deleteId" name="nomination_id">
</form>

<script>
    function approveNomination(id, name) {
        if(confirm("Are you sure you want to APPROVE nomination for " + name + "?")) {
            document.getElementById('approveId').value = id;
            document.getElementById('approveForm').submit();
        }
    }

    function rejectNomination(id, name) {
        let reason = prompt("Please enter reason for rejection for " + name + ":");
        if(reason) {
            document.getElementById('rejectId').value = id;
            document.getElementById('rejectReason').value = reason;
            document.getElementById('rejectForm').submit();
        }
    }

    function deleteNomination(id, name) {
        if(confirm("Are you sure you want to DELETE nomination for " + name + "?")) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteForm').submit();
        }
    }

    // Handle Cities JS for Add/Edit mode
    <?php if ($action === 'edit' || $action === 'add'): ?>
    document.addEventListener('DOMContentLoaded', function() {
        if(typeof print_state === 'function') {
            print_state('state');
            
            // Set initial state
            const stateSel = document.getElementById('state');
            const distSel = document.getElementById('district');
            const dbState = "<?php echo addslashes($nomination['state'] ?? ''); ?>";
            const dbDist = "<?php echo addslashes($nomination['district'] ?? ''); ?>";

            if(dbState) {
                stateSel.value = dbState;
                const stateIdx = state_arr.indexOf(dbState);
                if(stateIdx !== -1) {
                    print_city('district', stateIdx + 1);
                    distSel.value = dbDist;
                }
            }

            stateSel.addEventListener('change', function() {
                const idx = state_arr.indexOf(this.value);
                if(idx !== -1) print_city('district', idx + 1);
                else distSel.innerHTML = '';
            });
        }
    });
    <?php endif; ?>
</script>

<?php include 'includes/footer.php'; ?>