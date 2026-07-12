<?php
require_once 'config/config.php';

// Database connection
$db = getDbConnection();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register_camp') {
    header('Content-Type: application/json');
    
    if (!verifyCSRF($_POST['csrf_token'])) {
        logError('Invalid CSRF token in camp form submission');
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit;
    }

    // Validate required fields
    $required_fields = ['program', 'name', 'father_name', 'address', 'class', 'payment_method', 'place'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            logError("Missing required field: $field");
            echo json_encode(['success' => false, 'message' => 'Please fill all required fields']);
            exit;
        }
    }

    $program = sanitizeInput($_POST['program']);
    $name = sanitizeInput($_POST['name']);
    $father_name = sanitizeInput($_POST['father_name']);
    $address = sanitizeInput($_POST['address']);
    $class = sanitizeInput($_POST['class']);
    $payment_method = sanitizeInput($_POST['payment_method']);
    $place = sanitizeInput($_POST['place']);
    $email = !empty($_POST['email']) ? sanitizeInput($_POST['email']) : null;

    // Amount 0 for free, else parse
    $amount = $payment_method === 'free' ? 0.00 : floatval($_POST['amount'] ?? 0);

    // IDs after online payment
    $payment_id = !empty($_POST['payment_id']) ? sanitizeInput($_POST['payment_id']) : null;
    $order_id   = !empty($_POST['order_id']) ? sanitizeInput($_POST['order_id']) : null;

    // Status — prefer provided valid status, else infer
    $clientStatus = isset($_POST['status']) ? sanitizeInput($_POST['status']) : null;
    $status = in_array($clientStatus, ['pending', 'completed'], true)
        ? $clientStatus
        : ($payment_method === 'online' ? 'completed' : ($payment_method === 'free' ? 'completed' : 'pending'));

    // Validate min amount for non-free
    if ($payment_method !== 'free' && $amount < 10) {
        logError("Invalid camp registration amount: $amount");
        echo json_encode(['success' => false, 'message' => 'Amount must be at least ₹10 for non-free registrations']);
        exit;
    }

    // Offline proof upload
    $payment_proof = null;
    if ($payment_method === 'offline') {
        if (!empty($_FILES['payment_proof']['name']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
            // Use same folder convention as donation form
            $uploadResult = uploadFile($_FILES['payment_proof'], 'img/payments');
            if ($uploadResult['success']) {
                $payment_proof = $uploadResult['filename'];
            } else {
                logError('Payment proof upload failed: ' . $uploadResult['message']);
                echo json_encode(['success' => false, 'message' => $uploadResult['message']]);
                exit;
            }
        } else {
            logError('Missing payment proof for offline payment');
            echo json_encode(['success' => false, 'message' => 'Payment proof is required for offline payments']);
            exit;
        }
    }

    // Generate unique camp ID
    $camp_id = generateUniqueId('CMP');

    try {
        $stmt = $db->prepare("
            INSERT INTO camps (
                program, name, father_name, address, class, amount, payment_method, 
                place, email, payment_id, order_id, payment_proof, status, camp_id, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $program, $name, $father_name, $address, $class, $amount, $payment_method,
            $place, $email, $payment_id, $order_id, $payment_proof, $status, $camp_id
        ]);

        echo json_encode([
            'success' => true,
            'camp_id' => $camp_id,
            'amount' => $amount,
            'status' => $status
        ]);
    } catch (PDOException $e) {
        logError('Camp registration error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to save registration. Please try again.']);
    }
    exit;
}

include 'header.php';
include 'navbar.php';
?>

<main class="container my-5">
    <h3 class="section-heading text-center"><span>Camp Registration Form</span></h3>

    <div class="card p-3 p-md-5 form-container">
        <form id="camp-form" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="register_camp">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
            <input type="hidden" name="order_id" id="order_id">

             Program Details 
            <div class="row mb-4 align-items-center">
                <div class="col-md-2 form-field-title"><label for="program" class="form-label">Program Name <span class="text-danger">*</span></label></div>
                <div class="col-md-4"><input type="text" id="program" name="program" class="form-control" placeholder="Program Name" required></div>

                <div class="col-md-2 form-field-title"><label for="place" class="form-label">Place <span class="text-danger">*</span></label></div>
                <div class="col-md-4"><input type="text" id="place" name="place" class="form-control" placeholder="Event Location" required></div>
            </div>
            <hr class="my-4">

             Payment Method 
            <div class="row mb-4 align-items-center">
                <div class="col-md-2 form-field-title"><label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label></div>
                <div class="col-md-4">
                    <select class="form-select" id="payment_method" name="payment_method" required>
                        <option value="online">Online (Razorpay)</option>
                        <option value="offline">Offline (Bank Transfer)</option>
                        <option value="free">Free</option>
                    </select>
                </div>

                <div class="col-md-2 form-field-title"><label for="amount" class="form-label">Amount (₹) <span class="text-danger amount-required">*</span></label></div>
                <div class="col-md-4"><input type="number" id="amount" name="amount" class="form-control" placeholder="Enter amount" min="10" step="1" required></div>
            </div>
            <hr class="my-4">

             Personal Details 
            <div class="row mb-4 align-items-center">
                <div class="col-md-2 form-field-title"><label for="name" class="form-label">Full Name <span class="text-danger">*</span></label></div>
                <div class="col-md-4"><input type="text" id="name" name="name" class="form-control" placeholder="Full Name" required></div>

                <div class="col-md-2 form-field-title"><label for="father_name" class="form-label">Father's Name <span class="text-danger">*</span></label></div>
                <div class="col-md-4"><input type="text" id="father_name" name="father_name" class="form-control" placeholder="Father's Name" required></div>
            </div>

            <div class="row mb-4 align-items-center">
                <div class="col-md-2 form-field-title"><label for="class" class="form-label">Class <span class="text-danger">*</span></label></div>
                <div class="col-md-4">
                    <select id="class" name="class" class="form-select" required>
                        <option value="" selected disabled>Select Class</option>
                        <option value="1">1</option><option value="2">2</option><option value="3">3</option>
                        <option value="4">4</option><option value="5">5</option><option value="6">6</option>
                        <option value="7">7</option><option value="8">8</option><option value="9">9</option>
                        <option value="10">10</option><option value="11">11</option><option value="12">12</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="col-md-2 form-field-title"><label for="email" class="form-label">Email</label></div>
                <div class="col-md-4"><input type="email" id="email" name="email" class="form-control" placeholder="Optional Email Address"></div>
            </div>

            <div class="row mb-4 align-items-center">
                <div class="col-md-2 form-field-title"><label for="address" class="form-label">Address <span class="text-danger">*</span></label></div>
                <div class="col-md-10"><textarea id="address" name="address" required placeholder="Full Address" class="form-control" rows="3"></textarea></div>
            </div>
            <hr class="my-4">

             Bank Details 
            <div class="payment-info-section p-4 rounded mb-4 offline-payment-info d-none">
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
                <p class="mt-3 small"><strong>Note:</strong> After submitting the form and completing payment, you will receive a camp ID. For offline payments, please upload proof of payment to verify your transaction.</p>
            </div>

             Offline Payment Proof 
            <div class="row mb-4 align-items-center offline-payment-proof d-none">
                <div class="col-md-2 form-field-title"><label for="payment_proof" class="form-label">Payment Proof <span class="text-danger">*</span></label></div>
                <div class="col-md-4">
                    <div class="image-uploader">
                        <input type="file" id="payment_proof" name="payment_proof" class="d-none" accept="image/*,application/pdf">
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

             Submit Button 
            <div class="text-center">
                <button type="submit" class="btn btn-submit-grad btn-lg">
                    <i class="fas fa-calendar-check me-2"></i>Register for Camp
                </button>
            </div>
        </form>
    </div>
</main>

 Success Modal 
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center">
            <div class="modal-header">
                <h5 class="modal-title" id="successModalLabel">Camp Registration Successful!</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                <div id="successMessage"></div>
                <p id="campId" class="fw-bold"></p>
                <p id="offlineMessage" class="text-info small d-none">Your registration will be confirmed after payment verification.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

 Image Preview Modal 
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-labelledby="imagePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-0">
                <img src="/placeholder.svg" id="fullImagePreview" class="img-fluid w-100">
            </div>
        </div>
    </div>
</div>

 Spinner Loader 
<div class="loader-overlay d-none">
    <div class="spinner-border text-light" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('camp-form');
    const successModal = new bootstrap.Modal(document.getElementById('successModal'));
    const imagePreviewModal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
    const paymentMethodSelect = document.getElementById('payment_method');
    const offlinePaymentProof = document.querySelector('.offline-payment-proof');
    const offlinePaymentInfo = document.querySelector('.offline-payment-info');
    const amountInput = document.getElementById('amount');
    const amountRequired = document.querySelector('.amount-required');

    // Image uploader
    document.querySelectorAll('.image-uploader').forEach(uploader => {
        const input = uploader.querySelector('input[type="file"]');
        const emptyState = uploader.querySelector('.uploader-empty');
        const previewState = uploader.querySelector('.uploader-preview');
        const previewImg = previewState.querySelector('img');
        const viewBtn = previewState.querySelector('.view-btn');
        const deleteBtn = uploader.querySelector('.delete-btn');

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

    // Toggle fields based on payment method
    paymentMethodSelect.addEventListener('change', function() {
        if (this.value === 'offline') {
            offlinePaymentProof.classList.remove('d-none');
            offlinePaymentInfo.classList.remove('d-none');
            document.getElementById('payment_proof').required = true;
            amountInput.required = true;
            amountRequired.classList.remove('d-none');
        } else if (this.value === 'free') {
            offlinePaymentProof.classList.add('d-none');
            offlinePaymentInfo.classList.add('d-none');
            document.getElementById('payment_proof').required = false;
            amountInput.required = false;
            amountRequired.classList.add('d-none');
            amountInput.value = 0;
            amountInput.disabled = true;
        } else {
            offlinePaymentProof.classList.add('d-none');
            offlinePaymentInfo.classList.add('d-none');
            document.getElementById('payment_proof').required = false;
            amountInput.required = true;
            amountRequired.classList.remove('d-none');
            amountInput.disabled = false;
        }
    });

    // Form submit
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const amount = parseFloat(document.getElementById('amount').value);
        const paymentMethod = document.getElementById('payment_method').value;

        if (paymentMethod !== 'free' && (isNaN(amount) || amount < 10)) {
            Swal.fire('Error', 'Please enter a valid amount (minimum ₹10).', 'error');
            return;
        }

        if (paymentMethod === 'offline' && !document.getElementById('payment_proof').files.length) {
            Swal.fire('Error', 'Please upload payment proof for offline payment.', 'error');
            return;
        }

        if (paymentMethod === 'online') {
            createOrderAndPay(amount);
        } else {
            submitCampData(paymentMethod === 'free' ? 'completed' : 'pending');
        }
    });

    // Create Razorpay order and pay
    function createOrderAndPay(amount) {
        const loader = document.querySelector('.loader-overlay');
        loader.classList.remove('d-none');

        fetch('<?php echo SITE_URL; ?>/api/create-order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                amount: amount,
                currency: 'INR',
                receipt: 'camp_' + Date.now(),
                notes: {
                    type: 'camp',
                    program: document.getElementById('program').value,
                    student_name: document.getElementById('name').value,
                    place: document.getElementById('place').value
                }
            })
        })
        .then(res => res.json())
        .then(data => {
            loader.classList.add('d-none');
            if (data.success && data.order && data.order.id) {
                document.getElementById('order_id').value = data.order.id;
                triggerRazorpayWithOrder(data.order, data.key_id);
            } else {
                Swal.fire('Error', data.message || 'Failed to create order', 'error');
            }
        })
        .catch(err => {
            loader.classList.add('d-none');
            console.error('Order creation error:', err);
            Swal.fire('Error', 'Failed to create order. Please try again.', 'error');
        });
    }

    function triggerRazorpayWithOrder(order, keyId) {
        const options = {
            key: keyId || '<?php echo RAZORPAY_KEY_ID; ?>',
            amount: order.amount,
            currency: order.currency,
            name: "<?php echo SITE_NAME; ?>",
            description: "Camp Registration Fee",
            image: "<?php echo SITE_URL; ?>/img/logo.png",
            order_id: order.id,
            handler: function (response) {
                verifyPaymentAndSubmit(response);
            },
            prefill: {
                name: document.getElementById('name').value,
                email: document.getElementById('email').value || '',
                contact: '' // camp form has no mobile field
            },
            theme: { color: "#291872" }
        };

        const rz = new Razorpay(options);
        rz.on('payment.failed', function (response) {
            console.error('Payment failed:', response.error);
            Swal.fire('Payment Failed', response.error.description, 'error');
        });
        rz.open();
    }

    function verifyPaymentAndSubmit(razorpayResponse) {
        const loader = document.querySelector('.loader-overlay');
        loader.classList.remove('d-none');

        fetch('<?php echo SITE_URL; ?>/api/verify-payment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                razorpay_order_id: razorpayResponse.razorpay_order_id,
                razorpay_payment_id: razorpayResponse.razorpay_payment_id,
                razorpay_signature: razorpayResponse.razorpay_signature
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                submitCampData('completed', razorpayResponse.razorpay_payment_id);
            } else {
                loader.classList.add('d-none');
                Swal.fire('Payment Verification Failed', data.message || 'Invalid signature', 'error');
            }
        })
        .catch(err => {
            loader.classList.add('d-none');
            console.error('Verification error:', err);
            Swal.fire('Error', 'Payment verification failed. Please contact support.', 'error');
        });
    }

    function submitCampData(status, paymentId = null) {
        const loader = document.querySelector('.loader-overlay');
        if (loader.classList.contains('d-none')) loader.classList.remove('d-none');

        const formData = new FormData(form);
        if (paymentId) formData.append('payment_id', paymentId);
        formData.append('status', status);

        fetch('camp_form.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            loader.classList.add('d-none');
            if (data.success) {
                const successMessage = status === 'completed'
                    ? `<p>Your camp registration${data.amount > 0 ? ` for ₹${data.amount}` : ''} has been received successfully. Thank you for your participation!</p>${paymentId ? `<p>Your transaction ID is ${paymentId}.</p>` : ''}`
                    : `<p>Your camp registration request for ₹${data.amount} has been submitted. It will be confirmed after payment verification.</p>`;

                document.getElementById('successMessage').innerHTML = successMessage;
                document.getElementById('campId').innerHTML = `<strong>Camp ID:</strong> ${data.camp_id}`;

                if (status === 'pending') {
                    document.getElementById('offlineMessage').classList.remove('d-none');
                } else {
                    document.getElementById('offlineMessage').classList.add('d-none');
                }

                successModal.show();
                form.reset();
                document.querySelectorAll('.image-uploader .delete-btn').forEach(btn => btn.click());
                offlinePaymentProof.classList.add('d-none');
                offlinePaymentInfo.classList.add('d-none');
                document.getElementById('payment_proof').required = false;
                amountInput.required = true;
                amountRequired.classList.remove('d-none');
                amountInput.disabled = false;
            } else {
                Swal.fire('Error', data.message || 'Failed to save registration', 'error');
            }
        })
        .catch(err => {
            loader.classList.add('d-none');
            Swal.fire('Error', 'Something went wrong. Please try again.', 'error');
            console.error('Fetch error:', err);
        });
    }
});
</script>

<?php include 'footer.php'; ?>