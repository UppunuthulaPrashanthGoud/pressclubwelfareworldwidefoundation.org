<?php 
require_once 'config/config.php';
// require_once 'config/razorpay-order-api.php'; // COMMENTED: Uncomment when enabling online payments

// Database connection
$db = getDbConnection();

// Get logged-in user data if available
$user_data = null;
if (isLoggedIn()) {
    try {
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND status = 'approved'");
        $stmt->execute([$_SESSION['user_id']]);
        $user_data = $stmt->fetch();
    } catch (PDOException $e) {
        logError('Error fetching user data for donation form: ' . $e->getMessage());
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'donate') {
    header('Content-Type: application/json');
    
    if (!verifyCSRF($_POST['csrf_token'])) {
        logError('Invalid CSRF token in donation form submission');
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit;
    }

    // Validate required fields
    $required_fields = ['name', 'F_name', 'mobile', 'address', 'amount', 'payment_method'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            logError("Missing required field: $field");
            echo json_encode(['success' => false, 'message' => "Please fill all required fields"]);
            exit;
        }
    }

    $name = sanitizeInput($_POST['name']);
    $father_name = sanitizeInput($_POST['F_name']);
    $mobile = sanitizeInput($_POST['mobile']);
    $email = !empty($_POST['email']) ? sanitizeInput($_POST['email']) : null;
    $address = sanitizeInput($_POST['address']);
    $pancard = !empty($_POST['pancard']) ? sanitizeInput($_POST['pancard']) : null;
    $amount = floatval($_POST['amount']);
    $payment_method = sanitizeInput($_POST['payment_method']);
    $order_id = !empty($_POST['order_id']) ? sanitizeInput($_POST['order_id']) : null;
    $payment_id = !empty($_POST['payment_id']) ? sanitizeInput($_POST['payment_id']) : null;
    $photo = null;
    $payment_proof = null;
    // MODIFIED: Always pending for offline payments
    $status = 'pending'; // Previously: $payment_method === 'online' ? 'completed' : 'pending';
    $user_id = isLoggedIn() ? $_SESSION['user_id'] : 0;

    // Validate amount
    if ($amount < 10) {
        logError("Invalid donation amount: $amount");
        echo json_encode(['success' => false, 'message' => 'Donation amount must be at least ₹10']);
        exit;
    }

    // Handle photo upload
    if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadFile($_FILES['photo'], 'img/users');
        if ($uploadResult['success']) {
            $photo = $uploadResult['filename'];
        } else {
            logError('Photo upload failed: ' . $uploadResult['message']);
            echo json_encode(['success' => false, 'message' => $uploadResult['message']]);
            exit;
        }
    }

    // Handle payment proof upload for offline payments
    if ($payment_method === 'offline' && !empty($_FILES['payment_proof']['name']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadFile($_FILES['payment_proof'], 'img/payments');
        if ($uploadResult['success']) {
            $payment_proof = $uploadResult['filename'];
        } else {
            logError('Payment proof upload failed: ' . $uploadResult['message']);
            echo json_encode(['success' => false, 'message' => $uploadResult['message']]);
            exit;
        }
    } elseif ($payment_method === 'offline' && empty($_FILES['payment_proof']['name'])) {
        logError('Missing payment proof for offline payment');
        echo json_encode(['success' => false, 'message' => 'Payment proof is required for offline payments']);
        exit;
    }

    try {
        $stmt = $db->prepare("
            INSERT INTO donations (
                name, father_name, mobile, email, address, pan_card, 
                amount, photo, order_id, payment_id, payment_proof, status, created_at, payment_method, user_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)
        ");
        $stmt->execute([
            $name, $father_name, $mobile, $email, $address, $pancard,
            $amount, $photo, $order_id, $payment_id, $payment_proof, $status, $payment_method, $user_id
        ]);
        $donation_id = $db->lastInsertId();
        
        /* COMMENTED: Email sending for completed donations - Uncomment when enabling online payments
        if ($status === 'completed' && !empty($email)) {
            try {
                // Get donation data for email
                $donation_data = [
                    'id' => $donation_id,
                    'name' => $name,
                    'father_name' => $father_name,
                    'mobile' => $mobile,
                    'email' => $email,
                    'address' => $address,
                    'pan_card' => $pancard,
                    'amount' => $amount,
                    'payment_id' => $payment_id,
                    'order_id' => $order_id,
                    'status' => $status,
                    'payment_method' => $payment_method,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                // Generate receipt HTML for email
                require_once 'admin/includes/receipt-generator.php';
                $receipt_options = [
                    'type' => 'donation',
                    'auto_print' => false,
                    'show_buttons' => false,
                    'download' => false,
                    'email_template' => true
                ];
                $receipt_html = generateUniversalReceipt($donation_data, $receipt_options);
                
                // Send email with receipt
                $email_subject = "Donation Receipt - Thank You for Your Contribution";
                $email_body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <h2 style='color: #291872; text-align: center;'>Thank You for Your Donation!</h2>
                    <p>Dear {$name},</p>
                    <p>We have successfully received your donation of ₹{$amount}. Your generosity helps us continue our mission and make a positive impact.</p>
                    <p><strong>Donation Details:</strong></p>
                    <ul>
                        <li>Donation ID: {$donation_id}</li>
                        <li>Amount: ₹{$amount}</li>
                        <li>Payment Method: " . ucfirst($payment_method) . "</li>
                        <li>Transaction ID: {$payment_id}</li>
                        <li>Date: " . date('d M Y, h:i A') . "</li>
                    </ul>
                    <hr style='margin: 20px 0;'>
                    <h3 style='color: #291872;'>Your Receipt</h3>
                    {$receipt_html}
                    <hr style='margin: 20px 0;'>
                    <p>If you have any questions about your donation, please don't hesitate to contact us.</p>
                    <p>Thank you once again for your support!</p>
                    <p>Best regards,<br>" . SITE_NAME . " Team</p>
                </div>";
                
                $email_sent = sendEmail($email, $email_subject, $email_body);
                
                if ($email_sent) {
                    logError("Donation confirmation email sent successfully to: $email for donation ID: $donation_id");
                } else {
                    logError("Failed to send donation confirmation email to: $email for donation ID: $donation_id");
                }
                
            } catch (Exception $e) {
                logError('Error sending donation confirmation email: ' . $e->getMessage());
                // Don't fail the donation process if email fails
            }
        }
        */
        
        // Generate receipt URL for successful donations
        $receipt_url = '';
        /* COMMENTED: Receipt generation for completed donations - Uncomment when enabling online payments
        if ($status === 'completed') {
            $receipt_url = SITE_URL . '/generate_receipt.php?donation_id=' . $donation_id;
        }
        */
        
        echo json_encode([
            'success' => true,
            'payment_id' => $payment_id,
            'order_id' => $order_id,
            'donation_id' => $donation_id,
            'amount' => $amount,
            'status' => $status,
            'receipt_url' => $receipt_url
        ]);
    } catch (PDOException $e) {
        logError('Donation form submission error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to save donation. Please try again.']);
        exit;
    }
    exit;
}

include 'header.php'; 
include 'navbar.php'; 
?>

<main class="container my-5">
    <h3 class="section-heading text-center"><span>Donate Form</span></h3>

    <div class="card p-3 p-md-5 form-container">
        <form id="donation-form" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="donate">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
            <input type="hidden" name="order_id" id="order_id">

            <!-- Payment Method - MODIFIED: Only Offline Option -->
            <div class="row mb-4 align-items-center">
                <div class="col-md-2 form-field-title"><label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label></div>
                <div class="col-md-4">
                    <select class="form-select" id="payment_method" name="payment_method" required>
                        <!-- COMMENTED: Uncomment when enabling online payments
                        <option value="online">Online (Razorpay)</option>
                        -->
                        <option value="offline" selected>Offline (Bank Transfer)</option>
                    </select>
                </div>
            </div>
            <hr class="my-4">

            <!-- Personal Details -->
            <div class="row mb-4 align-items-center">
                <div class="col-md-2 form-field-title"><label for="name" class="form-label">Full Name <span class="text-danger">*</span></label></div>
                <div class="col-md-4">
                    <input type="text" id="name" name="name" class="form-control" placeholder="Full Name" 
                           value="<?php echo $user_data ? htmlspecialchars($user_data['name']) : ''; ?>" required>
                </div>
                
                <div class="col-md-2 form-field-title"><label for="fname" class="form-label">Father's Name <span class="text-danger">*</span></label></div>
                <div class="col-md-4">
                    <input type="text" id="fname" name="F_name" class="form-control" placeholder="Father's Name" 
                           value="<?php echo $user_data ? htmlspecialchars($user_data['sdw_name']) : ''; ?>" required>
                </div>
            </div>

            <div class="row mb-4 align-items-center">
                <div class="col-md-2 form-field-title"><label for="mobile" class="form-label">Mobile No. <span class="text-danger">*</span></label></div>
                <div class="col-md-4">
                    <input type="tel" id="mobile" name="mobile" class="form-control" placeholder="10-digit mobile number" 
                           pattern="[0-9]{10}" value="<?php echo $user_data ? htmlspecialchars($user_data['mobile']) : ''; ?>" required>
                </div>

                <div class="col-md-2 form-field-title"><label for="email" class="form-label">Email</label></div>
                <div class="col-md-4">
                    <input type="email" id="email" name="email" class="form-control" placeholder="Optional Email Address" 
                           value="<?php echo $user_data ? htmlspecialchars($user_data['email']) : ''; ?>">
                </div>
            </div>

            <div class="row mb-4 align-items-center">
                 <div class="col-md-2 form-field-title"><label for="address" class="form-label">Address <span class="text-danger">*</span></label></div>
                 <div class="col-md-10">
                     <textarea id="address" name="address" required placeholder="Full Address" class="form-control" rows="2"><?php echo $user_data ? htmlspecialchars($user_data['address']) : ''; ?></textarea>
                 </div>
            </div>
            <hr class="my-4">

            <!-- Donation and Upload -->
            <div class="row mb-4 align-items-center">
                <div class="col-md-2 form-field-title"><label for="amount" class="form-label">Amount (₹) <span class="text-danger">*</span></label></div>
                <div class="col-md-4"><input type="number" id="amount" name="amount" class="form-control" placeholder="Enter amount to donate" min="10" step="1" required></div>
            </div>

            <!-- 80G Tax Rebate Option -->
            <div class="row mb-4 align-items-center">
                <div class="col-md-2 form-field-title"><label class="form-label">80G Tax Rebate</label></div>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="need_80g" name="need_80g">
                        <label class="form-check-label" for="need_80g">
                            I want 80G tax rebate certificate
                        </label>
                    </div>
                    <small class="text-muted">Check this if you need a tax exemption certificate under section 80G</small>
                </div>
            </div>

            <!-- PAN Card Field (Hidden by default, shown when 80G is checked) -->
            <div class="row mb-4 align-items-center pancard-section d-none">
                <div class="col-md-2 form-field-title"><label for="pancard" class="form-label">PAN Card <span class="text-danger">*</span></label></div>
                <div class="col-md-4">
                    <input type="text" id="pancard" name="pancard" class="form-control" placeholder="Enter PAN Number (e.g., ABCDE1234F)" pattern="[A-Z]{5}[0-9]{4}[A-Z]{1}" maxlength="10" style="text-transform: uppercase;">
                    <small class="text-muted">PAN Card is mandatory for 80G tax rebate certificate</small>
                </div>
            </div>

            <div class="row mb-4 align-items-center">
                <div class="col-md-2 form-field-title"><label>Photo</label></div>
                <div class="col-md-4">
                    <div class="image-uploader" style="width: 120px; height: 120px;">
                        <input type="file" id="photo_input" name="photo" class="d-none" accept="image/*">
                        <?php if ($user_data && !empty($user_data['profile_image'])): ?>
                            <!-- Show existing profile image -->
                            <label for="photo_input" class="uploader-empty d-none">
                                <i class="fa fa-plus"></i>
                                <p class="small">Upload (Optional)</p>
                            </label>
                            <div class="uploader-preview">
                                <img src="<?php echo SITE_URL . '/img/users/' . htmlspecialchars($user_data['profile_image']); ?>" alt="Profile Preview">
                                <div class="uploader-controls">
                                    <button type="button" class="btn btn-light btn-sm view-btn"><i class="fa fa-eye"></i></button>
                                    <button type="button" class="btn btn-danger btn-sm delete-btn"><i class="fa fa-trash"></i></button>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Show upload placeholder -->
                            <label for="photo_input" class="uploader-empty">
                                <i class="fa fa-plus"></i>
                                <p class="small">Upload (Optional)</p>
                            </label>
                            <div class="uploader-preview d-none">
                                <img src="#" alt="Preview">
                                <div class="uploader-controls">
                                    <button type="button" class="btn btn-light btn-sm view-btn"><i class="fa fa-eye"></i></button>
                                    <button type="button" class="btn btn-danger btn-sm delete-btn"><i class="fa fa-trash"></i></button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <hr class="my-4">

<!-- Bank Details -->
<div class="payment-info-section p-4 rounded mb-4">
    <h4 class="text-center mb-3">Bank Details for Offline Payment</h4>
    <p><strong>Bank Name:</strong> <?php echo htmlspecialchars(BANK_NAME); ?><br>
    <strong>A/c Name:</strong> <?php echo htmlspecialchars(ACCOUNT_NAME); ?><br>
    <strong>A/c No.:</strong> <?php echo htmlspecialchars(ACCOUNT_NUMBER); ?><br>
    <strong>IFSC Code:</strong> <?php echo htmlspecialchars(IFSC_CODE); ?></p>
    <?php if (!empty(QR_CODE_IMAGE)): ?>
        <div class="text-center mb-3">
            <h6>Scan to Pay (Offline)</h6>
            <img src="<?php echo QR_CODE_IMAGE; ?>" alt="QR Code" style="max-width: 150px;">
        </div>
    <?php endif; ?>
    <hr>
</div>

<?php 
// Include Razorpay payment button
$type = 'donation';
$razorpay_url = 'https://pages.razorpay.com/pl_MOeFYdD5xa3fOS/view';
include 'includes/razorpay-payment-button.php'; 
?>

            <!-- Offline Payment Proof - MODIFIED: Always visible -->
            <div class="row mb-4 align-items-center offline-payment-proof">
                <div class="col-md-2 form-field-title"><label for="payment_proof" class="form-label">Payment Proof <span class="text-danger">*</span></label></div>
                <div class="col-md-4">
                    <div class="image-uploader">
                        <input type="file" id="payment_proof" name="payment_proof" class="d-none" accept="image/*,application/pdf" required>
                        <label for="payment_proof" class="uploader-empty">
                            <i class="fa fa-plus fa-2x"></i>
                            <p>Upload Payment Proof</p>
                        </label>
                        <div class="uploader-preview d-none">
                            <img src="#" alt="Preview">
                            <div class="uploader-controls">
                                <button type="button" class="btn btn-light btn-sm view-btn"><i class="fa fa-eye"></i></button>
                                <button type="button" class="btn btn-danger btn-sm delete-btn"><i class="fa fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Login Prompt for Non-logged in Users -->
            <?php if (!isLoggedIn()): ?>
            <div class="alert alert-info mb-4">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Tip:</strong> <a href="<?php echo SITE_URL; ?>/user-login.php" class="alert-link">Login to your account</a> to auto-fill your details and make donation easier!
            </div>
            <?php endif; ?>

            <!-- Submit Button -->
            <div class="text-center">
                <button type="submit" class="btn btn-submit-grad btn-lg">
                    <i class="fas fa-heart me-2"></i>Submit Donation
                </button>
            </div>
        </form>
    </div>
</main>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <div class="modal-header">
        <h5 class="modal-title" id="successModalLabel">Thank You!</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
        <div id="successMessage"></div>
        <p id="donationId" class="fw-bold"></p>
        <p id="offlineMessage" class="text-info small">Your donation will be confirmed after payment verification.</p>
        <!-- COMMENTED: Receipt section - Uncomment when enabling online payments
        <div id="receiptSection" class="mt-4 d-none">
          <div class="alert alert-success">
            <i class="fas fa-receipt me-2"></i>
            <strong>Your donation receipt is ready!</strong>
          </div>
          <button type="button" id="downloadReceiptBtn" class="btn btn-success me-2">
            <i class="fas fa-download me-1"></i>Download Receipt
          </button>
          <button type="button" id="viewReceiptBtn" class="btn btn-outline-success">
            <i class="fas fa-eye me-1"></i>View Receipt
          </button>
        </div>
        -->
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Image Preview Modal -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-labelledby="imagePreviewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body p-0">
        <img src="/placeholder.svg" id="fullImagePreview" class="img-fluid w-100">
      </div>
    </div>
  </div>
</div>

<!-- Spinner Loader -->
<div class="loader-overlay d-none">
    <div class="spinner-border text-light" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

<!-- COMMENTED: Razorpay script - Uncomment when enabling online payments
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
-->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('donation-form');
    const successModal = new bootstrap.Modal(document.getElementById('successModal'));
    const imagePreviewModal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
    const paymentMethodSelect = document.getElementById('payment_method');
    const offlinePaymentProof = document.querySelector('.offline-payment-proof');
    const need80gCheckbox = document.getElementById('need_80g');
    const pancardSection = document.querySelector('.pancard-section');
    const pancardInput = document.getElementById('pancard');
    
    // Toggle PAN Card field based on 80G checkbox
    need80gCheckbox.addEventListener('change', function() {
        if (this.checked) {
            pancardSection.classList.remove('d-none');
            pancardInput.required = true;
        } else {
            pancardSection.classList.add('d-none');
            pancardInput.required = false;
            pancardInput.value = '';
        }
    });

    // Auto-uppercase PAN card input
    pancardInput.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });
    
    // Image Uploader Logic
    document.querySelectorAll('.image-uploader').forEach(uploader => {
        const input = uploader.querySelector('input[type="file"]');
        const emptyState = uploader.querySelector('.uploader-empty');
        const previewState = uploader.querySelector('.uploader-preview');
        const previewImg = previewState.querySelector('img');
        const viewBtn = previewState.querySelector('button.view-btn');
        const deleteBtn = uploader.querySelector('button.delete-btn');

        input.addEventListener('change', () => {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    previewImg.src = e.target.result;
                    emptyState.classList.add('d-none');
                    previewState.classList.remove('d-none');
                };
                reader.readAsDataURL(input.files[0]);
            }
        });

        viewBtn.addEventListener('click', () => {
            document.getElementById('fullImagePreview').src = previewImg.src;
            imagePreviewModal.show();
        });

        deleteBtn.addEventListener('click', () => {
            input.value = '';
            previewState.classList.add('d-none');
            emptyState.classList.remove('d-none');
        });
    });

    /* COMMENTED: Payment method toggle - Not needed for offline only
    // Toggle payment proof field based on payment method
    paymentMethodSelect.addEventListener('change', function() {
        if (this.value === 'offline') {
            offlinePaymentProof.classList.remove('d-none');
            document.getElementById('payment_proof').required = true;
        } else {
            offlinePaymentProof.classList.add('d-none');
            document.getElementById('payment_proof').required = false;
        }
    });
    */

    // Form Submission - MODIFIED: Only offline payment
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const amount = parseFloat(document.getElementById('amount').value);
        const paymentMethod = document.getElementById('payment_method').value;

        if (isNaN(amount) || amount < 10) {
            Swal.fire('Error', 'Please enter a valid amount (minimum ₹10).', 'error');
            return;
        }

        // Validate PAN card if 80G is checked
        if (need80gCheckbox.checked) {
            const panValue = pancardInput.value.trim();
            const panPattern = /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/;
            
            if (!panValue) {
                Swal.fire('Error', 'Please enter your PAN Card number for 80G tax rebate.', 'error');
                pancardInput.focus();
                return;
            }
            
            if (!panPattern.test(panValue)) {
                Swal.fire('Error', 'Please enter a valid PAN Card number (e.g., ABCDE1234F).', 'error');
                pancardInput.focus();
                return;
            }
        }

        // MODIFIED: Only offline payment handling
        if (document.getElementById('payment_proof').files.length > 0) {
            submitDonationData('pending');
        } else {
            Swal.fire('Error', 'Please upload payment proof for offline payment.', 'error');
        }

        /* COMMENTED: Online payment handling - Uncomment when enabling online payments
        if (paymentMethod === 'online') {
            // Create order first, then trigger Razorpay
            createOrderAndPay(amount);
        } else if (document.getElementById('payment_proof').files.length > 0) {
            submitDonationData('pending');
        } else {
            Swal.fire('Error', 'Please upload payment proof for offline payment.', 'error');
        }
        */
    });

    /* COMMENTED: Razorpay functions - Uncomment when enabling online payments
    
    // Create Razorpay Order and initiate payment
    function createOrderAndPay(amount) {
        const loader = document.querySelector('.loader-overlay');
        loader.classList.remove('d-none');

        // Create order via API
        fetch('<?php echo SITE_URL; ?>/api/create-order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                amount: amount,
                currency: 'INR',
                receipt: 'donation_' + Date.now(),
                notes: {
                    type: 'donation',
                    donor_name: document.getElementById('name').value,
                    donor_mobile: document.getElementById('mobile').value
                }
            })
        })
        .then(response => response.json())
        .then(data => {
            loader.classList.add('d-none');
            
            if (data.success) {
                // Store order ID
                document.getElementById('order_id').value = data.order.id;
                
                // Trigger Razorpay with order
                triggerRazorpayWithOrder(data.order, data.key_id);
            } else {
                Swal.fire('Error', data.message || 'Failed to create order', 'error');
            }
        })
        .catch(error => {
            loader.classList.add('d-none');
            console.error('Order creation error:', error);
            Swal.fire('Error', 'Failed to create order. Please try again.', 'error');
        });
    }

    function triggerRazorpayWithOrder(order, keyId) {
        const options = {
            "key": keyId,
            "amount": order.amount,
            "currency": order.currency,
            "name": "<?php echo SITE_NAME; ?>",
            "description": "Donation",
            "image": "<?php echo SITE_URL; ?>/img/logo.png",
            "order_id": order.id,
            "handler": function(response) {
                // Verify payment first
                verifyPaymentAndSubmit(response);
            },
            "prefill": {
                "name": document.getElementById('name').value,
                "email": document.getElementById('email').value || '',
                "contact": document.getElementById('mobile').value
            },
            "theme": { "color": "#291872" },
            "modal": {
                "ondismiss": function() {
                    console.log('Payment modal closed');
                }
            }
        };
        
        const rzp1 = new Razorpay(options);
        rzp1.on('payment.failed', function(response) {
            console.error('Payment failed:', response.error);
            Swal.fire('Payment Failed', response.error.description, 'error');
        });
        rzp1.open();
    }

    function verifyPaymentAndSubmit(razorpayResponse) {
        const loader = document.querySelector('.loader-overlay');
        loader.classList.remove('d-none');

        // Verify payment signature
        fetch('<?php echo SITE_URL; ?>/api/verify-payment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                razorpay_order_id: razorpayResponse.razorpay_order_id,
                razorpay_payment_id: razorpayResponse.razorpay_payment_id,
                razorpay_signature: razorpayResponse.razorpay_signature
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Payment verified, now submit donation data
                submitDonationData('completed', razorpayResponse.razorpay_payment_id);
            } else {
                loader.classList.add('d-none');
                Swal.fire('Payment Verification Failed', data.message, 'error');
            }
        })
        .catch(error => {
            loader.classList.add('d-none');
            console.error('Payment verification error:', error);
            Swal.fire('Error', 'Payment verification failed. Please contact support.', 'error');
        });
    }
    
    */ // END OF COMMENTED RAZORPAY FUNCTIONS

    function submitDonationData(status, paymentId = null) {
        const loader = document.querySelector('.loader-overlay');
        if (!loader.classList.contains('d-none')) {
            // Loader already shown from verification
        } else {
            loader.classList.remove('d-none');
        }

        const formData = new FormData(form);
        if (paymentId) {
            formData.append('payment_id', paymentId);
        }
        formData.append('status', status);
        
        fetch('donation-form.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            loader.classList.add('d-none');
            
            if (data.success) {
                // MODIFIED: Always show pending message for offline donations
                const successMessage = `<p>Your donation request of ₹${formData.get('amount')} has been submitted successfully. It will be confirmed after payment verification.</p>`;
                
                /* COMMENTED: Online payment success message - Uncomment when enabling online payments
                const successMessage = status === 'completed' 
                    ? `<p>Your donation of ₹${formData.get('amount')} has been received successfully. Thank you for your support!</p><p>Your transaction ID is ${paymentId || 'N/A'}.</p>`
                    : `<p>Your donation request of ₹${formData.get('amount')} has been submitted. It will be confirmed after payment verification.</p>`;
                */
                
                document.getElementById('successMessage').innerHTML = successMessage;
                document.getElementById('donationId').innerHTML = `<strong>Donation ID:</strong> ${data.donation_id}`;
                
                // MODIFIED: Always show offline message
                document.getElementById('offlineMessage').classList.remove('d-none');
                
                /* COMMENTED: Show receipt section for completed donations - Uncomment when enabling online payments
                if (status === 'pending') {
                    document.getElementById('offlineMessage').classList.remove('d-none');
                }
                
                // Show receipt section for completed donations
                if (status === 'completed' && data.receipt_url) {
                    const receiptSection = document.getElementById('receiptSection');
                    const downloadBtn = document.getElementById('downloadReceiptBtn');
                    const viewBtn = document.getElementById('viewReceiptBtn');
                    
                    receiptSection.classList.remove('d-none');
                    
                    // Set up receipt buttons
                    downloadBtn.onclick = function() {
                        window.open(data.receipt_url + '&download=1', '_blank');
                    };
                    
                    viewBtn.onclick = function() {
                        window.open(data.receipt_url, '_blank');
                    };
                    
                    // Auto-open receipt after 2 seconds
                    setTimeout(() => {
                        window.open(data.receipt_url, '_blank');
                    }, 2000);
                }
                */
                
                successModal.show();
                form.reset();
                
                // Reset 80G checkbox and PAN card section
                need80gCheckbox.checked = false;
                pancardSection.classList.add('d-none');
                pancardInput.required = false;
                
                // Reset image uploaders
                document.querySelectorAll('.image-uploader').forEach(uploader => {
                    const input = uploader.querySelector('input[type="file"]');
                    const emptyState = uploader.querySelector('.uploader-empty');
                    const previewState = uploader.querySelector('.uploader-preview');
                    
                    input.value = '';
                    previewState.classList.add('d-none');
                    emptyState.classList.remove('d-none');
                });
                
                // MODIFIED: Payment proof field always visible for offline only
                // offlinePaymentProof.classList.add('d-none');
                // document.getElementById('payment_proof').required = false;
                
                // Repopulate user data if logged in
                <?php if ($user_data): ?>
                document.getElementById('name').value = '<?php echo addslashes($user_data['name']); ?>';
                document.getElementById('fname').value = '<?php echo addslashes($user_data['sdw_name']); ?>';
                document.getElementById('mobile').value = '<?php echo addslashes($user_data['mobile']); ?>';
                document.getElementById('email').value = '<?php echo addslashes($user_data['email']); ?>';
                document.getElementById('address').value = '<?php echo addslashes($user_data['address']); ?>';
                
                // Restore profile image preview if exists
                <?php if (!empty($user_data['profile_image'])): ?>
                const photoUploader = document.querySelector('#photo_input').closest('.image-uploader');
                const photoEmpty = photoUploader.querySelector('.uploader-empty');
                const photoPreview = photoUploader.querySelector('.uploader-preview');
                const photoImg = photoPreview.querySelector('img');
                
                photoImg.src = '<?php echo SITE_URL . '/img/users/' . addslashes($user_data['profile_image']); ?>';
                photoEmpty.classList.add('d-none');
                photoPreview.classList.remove('d-none');
                <?php endif; ?>
                <?php endif; ?>
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(error => {
            loader.classList.add('d-none');
            Swal.fire('Error', 'Something went wrong. Please try again.', 'error');
            console.error('Fetch error:', error);
        });
    }
});
</script>

<?php include 'footer.php'; ?>