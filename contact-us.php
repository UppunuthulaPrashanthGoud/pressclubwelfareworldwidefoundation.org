<?php
session_start();
require_once 'config/config.php';

// Database connection
$db = getDbConnection();

// Fetch site configuration
$stmt = $db->prepare("SELECT address, phone1, phone2, email, map_embed_url FROM site_config WHERE id = 1");
$stmt->execute();
$siteConfig = $stmt->fetch(PDO::FETCH_ASSOC);

// Sanitize site config data
$siteConfig['address'] = htmlspecialchars($siteConfig['address'] ?? 'Vill And post Mudai, Mainpuri, Uttar Pradesh 205301');
$siteConfig['phone1'] = htmlspecialchars($siteConfig['phone1'] ?? '7454838285');
$siteConfig['phone2'] = htmlspecialchars($siteConfig['phone2'] ?? '');
$siteConfig['email'] = htmlspecialchars($siteConfig['email'] ?? 'official.ndfoundation@gmail.com');
$siteConfig['map_embed_url'] = htmlspecialchars($siteConfig['map_embed_url'] ?? '');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_contact') {
    header('Content-Type: application/json');
    
    if (!verifyCSRF($_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit;
    }

    $name = sanitizeInput($_POST['name']);
    $mobile = sanitizeInput($_POST['mobile']);
    $email = sanitizeInput($_POST['email']);
    $topic = sanitizeInput($_POST['topic']);
    $description = sanitizeInput($_POST['description']);

    try {
        $stmt = $db->prepare("INSERT INTO contact_messages (name, mobile, email, topic, description, created_at, status) VALUES (?, ?, ?, ?, ?, NOW(), 'pending')");
        $stmt->execute([$name, $mobile, $email, $topic, $description]);
        echo json_encode(['success' => true]);
        exit;
    } catch (PDOException $e) {
        logError('Contact form submission error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to submit message. Please try again.']);
        exit;
    }
}

include 'header.php';
include 'navbar.php';
?>

<!-- Full-Width Contact Section -->
<div class="container-fluid navbar-margin-pusher">
    <div class="container my-5">
        <div class="row">
            <div class="col-12">
                <h3 class="section-heading text-center"><span>Contact Us</span></h3>
            </div>
        </div>

        <!-- Contact Content -->
        <div class="contact-content">
            <div class="row">
                <!-- Contact Form -->
                <div class="col-lg-7" data-aos="fade-up">
                    <div class="card-custom mb-4">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Send Us a Message</h4>
                            <form id="contact-form" method="post">
                                <input type="hidden" name="action" value="submit_contact">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRF(), ENT_QUOTES, 'UTF-8'); ?>">
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                        <input type="text" id="name" name="name" class="form-control" placeholder="Full Name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="mobile" class="form-label">Mobile Number <span class="text-danger">*</span></label>
                                        <input type="tel" id="mobile" name="mobile" class="form-control" placeholder="10-digit number" pattern="[0-9]{10}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" id="email" name="email" class="form-control" placeholder="your@email.com" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="topic" class="form-label">Subject <span class="text-danger">*</span></label>
                                        <input type="text" id="topic" name="topic" class="form-control" placeholder="Message Subject" required>
                                    </div>
                                    <div class="col-12">
                                        <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                        <textarea id="description" name="description" class="form-control" placeholder="Describe your query..." rows="5" required></textarea>
                                    </div>
                                    <div class="col-12 text-center">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                            Send Message
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Contact Info & Map -->
                <div class="col-lg-5" data-aos="fade-up" data-aos-delay="200">
                    <div class="card-custom">
                        <h5 class="card-title mb-4">Contact Information</h5>
                        <div class="contact-info-item">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            <div>
                                <strong>Address</strong>
                                <p><?php echo $siteConfig['address']; ?></p>
                            </div>
                        </div>
                        <div class="contact-info-item">
                            <i class="fas fa-phone me-2"></i>
                            <div>
                                <strong>Call Us</strong>
                                <p><?php echo $siteConfig['phone1'] . ($siteConfig['phone2'] ? ' / ' . $siteConfig['phone2'] : ''); ?></p>
                            </div>
                        </div>
                        <div class="contact-info-item">
                            <i class="fas fa-envelope me-2"></i>
                            <div>
                                <strong>Email Us</strong>
                                <p><?php echo $siteConfig['email']; ?></p>
                            </div>
                        </div>
                        <?php if (!empty($siteConfig['map_embed_url']) && preg_match('/^https:\/\/www\.google\.com\/maps\/embed\?/', $siteConfig['map_embed_url'])): ?>
                            <div class="map-container mt-4">
                                <iframe src="<?php echo $siteConfig['map_embed_url']; ?>" width="100%" height="250" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning mt-4">
                                <i class="fas fa-exclamation-triangle"></i> Google Maps embed is not configured or invalid.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Contact Content */
.contact-content {
    padding: 2rem 0;
}

.card-custom {
    border: none;
    border-radius: 10px;
    background: var(--white-bg);
    box-shadow: 0 4px 12px var(--shadow-medium);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card-custom:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 20px var(--shadow-dark);
}

.card-custom .card-title {
    font-family: 'Bakbak One', sans-serif;
    font-size: 1.5rem;
    color: var(--primary-color);
    margin-bottom: 0.75rem;
}

.card-custom .btn-primary {
    background: var(--gradient-primary);
    border: none;
    font-family: 'Teko', sans-serif;
    font-weight: 500;
    padding: 0.5rem 1rem;
    border-radius: 5px;
    color: var(--text-white);
}

.card-custom .btn-primary:hover {
    background: var(--gradient-primary-reverse);
    transform: translateY(-2px);
}

.form-control {
    border-radius: 5px;
    border: 1px solid var(--primary-light);
}

.form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 5px rgba(255, 111, 15, 0.5);
}

/* Contact Info Styles */
.contact-info-item {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
}

.contact-info-item i {
    font-size: 1.5rem;
    color: var(--primary-color);
    margin-right: 1rem;
}

.contact-info-item p {
    margin: 0;
    color: var(--text-muted);
}

/* Map Container */
.map-container {
    border-radius: 10px;
    overflow: hidden;
}

/* Responsive Adjustments */
@media (max-width: 991.98px) {
    .contact-content {
        padding: 1.5rem 0;
    }

    .card-custom .card-title {
        font-size: 1.3rem;
    }
}

@media (max-width: 767.98px) {
    .contact-content {
        padding: 1rem 0;
    }

    .card-custom .card-title {
        font-size: 1.2rem;
    }
}

@media (max-width: 575.98px) {
    .section-heading span {
        font-size: 1.5rem;
    }

    .card-custom .btn-primary {
        font-size: 0.9rem;
        padding: 0.4rem 0.8rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('contact-form');
    const submitButton = form.querySelector('button[type="submit"]');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const spinner = submitButton.querySelector('.spinner-border');
        spinner.classList.remove('d-none');
        submitButton.disabled = true;

        const formData = new FormData(form);

        fetch('contact-us.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            spinner.classList.add('d-none');
            submitButton.disabled = false;
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Successfully Submitted!',
                    text: 'Thank you for your message. We will get back to you soon.',
                    confirmButtonColor: 'var(--primary-color)'
                });
                form.reset();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Submission Failed',
                    text: data.message,
                    confirmButtonColor: 'var(--secondary-color)'
                });
            }
        })
        .catch(error => {
            spinner.classList.add('d-none');
            submitButton.disabled = false;
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Something went wrong. Please try again.',
                confirmButtonColor: 'var(--secondary-color)'
            });
        });
    });
});
</script>

<?php include 'footer.php'; ?>