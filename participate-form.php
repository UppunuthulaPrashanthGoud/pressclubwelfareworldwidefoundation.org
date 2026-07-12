<?php 
require_once 'config/config.php';

// Database connection
$db = getDbConnection(); // This should work with your config.php

$event_id = $_GET['event_id'] ?? '';
$event = null;

if ($event_id) {
    $stmt = $db->prepare("SELECT * FROM events WHERE id = ? AND status = ? LIMIT 1");
    $stmt->execute([$event_id, 'active']);
    $event = $stmt->fetch();
}

if (!$event) {
    header('Location: upcoming-event.php');
    exit;
}

include 'header.php'; 
include 'navbar.php'; 
?>

<main class="container my-5">
    <h3 class="section-heading text-center"><span>Event Participation Form</span></h3>

    <div class="row">
        <div class="col-lg-8">
            <div class="card p-4 form-container">
                <h4 class="mb-4"><?php echo htmlspecialchars($event['title']); ?></h4>
                <p><strong>Date & Time:</strong> <?php echo date('F j, Y \a\t g:i A', strtotime($event['event_date'] . ' ' . $event['event_time'])); ?></p>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
                
                <form id="participation-form" method="post">
                    <input type="hidden" name="action" value="submit_participation">
                    <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event['id']); ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label for="mobile" class="form-label">Mobile Number <span class="text-danger">*</span></label>
                            <input type="tel" id="mobile" name="mobile" class="form-control" pattern="[0-9]{10}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="city" class="form-label">City</label>
                            <input type="text" id="city" name="city" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="is_ngo" class="form-label">Are you from NGO?</label>
                            <select id="is_ngo" name="is_ngo" class="form-select">
                                <option value="No">No</option>
                                <option value="Yes">Yes</option>
                            </select>
                        </div>
                        <div class="col-12" id="ngo-id-field" style="display: none;">
                            <label for="ngo_id" class="form-label">NGO ID</label>
                            <input type="text" id="ngo_id" name="ngo_id" class="form-control">
                        </div>
                        <div class="col-12">
                            <label for="donation_detail" class="form-label">Donation Details (Optional)</label>
                            <textarea id="donation_detail" name="donation_detail" class="form-control" rows="3" placeholder="Any donation or contribution details..."></textarea>
                        </div>
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-submit-grad btn-lg">
                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                Book My Seat
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Event Details</h5>
                    <?php if ($event['image']): ?>
                        <img src="img/events/<?php echo htmlspecialchars($event['image']); ?>" class="img-fluid rounded mb-3" alt="Event Image">
                    <?php endif; ?>
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('participation-form');
    const submitButton = form.querySelector('button[type="submit"]');
    const isNgoSelect = document.getElementById('is_ngo');
    const ngoIdField = document.getElementById('ngo-id-field');

    // Show/hide NGO ID field based on selection
    isNgoSelect.addEventListener('change', function() {
        if (this.value === 'Yes') {
            ngoIdField.style.display = 'block';
            document.getElementById('ngo_id').required = true;
        } else {
            ngoIdField.style.display = 'none';
            document.getElementById('ngo_id').required = false;
        }
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const spinner = submitButton.querySelector('.spinner-border');
        spinner.classList.remove('d-none');
        submitButton.disabled = true;

        const formData = new FormData(form);
        
        fetch('process-participation.php', { // Changed to a new file for processing
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
                    title: 'Seat Booked Successfully!',
                    text: 'Thank you for your participation. We will contact you soon.',
                    confirmButtonColor: 'var(--primary-color)'
                }).then(() => {
                    form.reset();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Booking Failed',
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