<?php include 'header.php'; ?>
<?php include 'navbar.php'; ?>

<!-- Google reCAPTCHA Script -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<main class="container my-5">
    <h3 class="section-heading text-center"><span>User Registration</span></h3>

    <div class="card p-3 p-md-5 form-container">
        <form id="registration-form" method="post" enctype="multipart/form-data">
            <div class="row g-4">
                <!-- User Details -->
                <div class="col-md-6">
                    <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="Name" class="form-control" placeholder="Enter your full name" required>
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                    <input type="email" id="email" name="Email" class="form-control" placeholder="Enter your email" required>
                </div>
                <div class="col-md-6">
                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                    <input type="password" id="password" name="Password" class="form-control" placeholder="Create a password" required>
                </div>
                <div class="col-md-6">
                    <label for="c_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                    <input type="password" id="c_password" name="C_password" class="form-control" placeholder="Confirm your password" required>
                    <div id="password-error" class="text-danger small mt-1 d-none">Passwords do not match.</div>
                </div>

                <hr class="my-3">

                <!-- Profile Picture & reCAPTCHA -->
                <div class="col-md-4">
                    <label class="form-label">Profile Picture (Optional)</label>
                     <div class="image-uploader mx-auto mx-md-0" style="width: 150px; height: 150px;">
                        <input type="file" id="profile_pic_input" name="Profile" class="d-none" accept="image/*">
                        <label for="profile_pic_input" class="uploader-empty">
                            <i class="fa fa-plus"></i>
                            <p class="small">Upload Image</p>
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
                <div class="col-md-8 d-flex align-items-center justify-content-center justify-content-md-start">
                    <div class="g-recaptcha" data-sitekey="6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI"></div> <!-- Use your actual site key -->
                </div>

                <!-- Submit Button -->
                <div class="col-12 text-center mt-4">
                    <button type="submit" class="btn btn-submit-grad btn-lg">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Register
                    </button>
                </div>
            </div>
        </form>
    </div>
</main>

<!-- Reusable Image Preview Modal -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content"><div class="modal-body p-0"><img src="" id="fullImagePreview" class="img-fluid w-100"></div></div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registration-form');
    const submitButton = form.querySelector('button[type="submit"]');
    const imagePreviewModal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('c_password');
    const passwordErrorDiv = document.getElementById('password-error');

    // Reusable Image Uploader Logic
    document.querySelectorAll('.image-uploader').forEach(uploader => {
        const input = uploader.querySelector('input[type="file"]');
        const emptyState = uploader.querySelector('.uploader-empty');
        const previewState = uploader.querySelector('.uploader-preview');
        // ... (rest of the uploader logic is the same)
        const previewImg = previewState.querySelector('img');
        const viewBtn = previewState.querySelector('.view-btn');
        const deleteBtn = previewState.querySelector('.delete-btn');

        input.addEventListener('change', () => {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => { previewImg.src = e.target.result; emptyState.classList.add('d-none'); previewState.classList.remove('d-none'); };
                reader.readAsDataURL(input.files[0]);
            }
        });
        viewBtn.addEventListener('click', () => { document.getElementById('fullImagePreview').src = previewImg.src; imagePreviewModal.show(); });
        deleteBtn.addEventListener('click', () => { input.value = ''; previewState.classList.add('d-none'); emptyState.classList.remove('d-none'); });
    });

    // Client-side password match validation
    function validatePasswords() {
        if (password.value !== confirmPassword.value) {
            passwordErrorDiv.classList.remove('d-none');
            return false;
        } else {
            passwordErrorDiv.classList.add('d-none');
            return true;
        }
    }
    password.addEventListener('input', validatePasswords);
    confirmPassword.addEventListener('input', validatePasswords);

    // Form Submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        if (!validatePasswords()) {
            Swal.fire('Error', 'Passwords do not match!', 'error');
            return;
        }
        
        const spinner = submitButton.querySelector('.spinner-border');
        spinner.classList.remove('d-none');
        submitButton.disabled = true;

        const formData = new FormData(form);

        // --- AJAX SIMULATION ---
        // Replace this block with your actual fetch call to 'ajax/insert-user-registration.php'
        console.log("Submitting registration form data...");
        setTimeout(() => {
            spinner.classList.add('d-none');
            submitButton.disabled = false;
            
            // Simulate a success response
            const simulatedResponse = "1";
            
            if (simulatedResponse === "1") {
                Swal.fire({ icon: 'success', title: 'Registration Successful!', text: 'You can now log in with your credentials.' });
                form.reset();
                grecaptcha.reset(); // Reset reCAPTCHA
                document.querySelectorAll('.image-uploader .delete-btn').forEach(btn => btn.click());
            } else if (simulatedResponse === "2") {
                Swal.fire({ icon: 'error', title: 'Registration Failed', text: 'This email is already registered.' });
            } else if (simulatedResponse === "4") {
                Swal.fire({ icon: 'error', title: 'CAPTCHA Failed', text: 'Please complete the CAPTCHA and try again.' });
            } else {
                 Swal.fire({ icon: 'error', title: 'Oops...', text: 'Something went wrong!' });
            }
        }, 1500);
        // --- END SIMULATION ---
    });
});
</script>

<?php include 'footer.php'; ?>