<?php
require_once 'config/config.php';
require_once 'admin/includes/universal-id-card-generator.php';

$idCardGenerator = new UniversalIdCardGenerator();

// Handle AJAX request FIRST - before any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'get_user_data') {
    if (ob_get_level()) {
        ob_clean();
    }
    
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    
    try {
        if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
            logError('Invalid CSRF token for registration_id: ' . ($_POST['registration_id'] ?? 'unknown'));
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            exit;
        }

        $registration_id = isset($_POST['registration_id']) ? sanitizeInput($_POST['registration_id']) : '';
        
        if (empty($registration_id) || !preg_match('/^CGMA[0-9A-Z]+$/', $registration_id)) {
            logError('Invalid registration ID: ' . $registration_id);
            echo json_encode(['success' => false, 'message' => 'Invalid registration ID format (e.g., CGMA20250809001)']);
            exit;
        }

        $memberData = $idCardGenerator->getMemberData($registration_id, 'registration_id');
        
        if ($memberData) {
            logError('Member data retrieved for ' . $registration_id . ': ' . json_encode($memberData));
            echo json_encode([
                'success' => true, 
                'data' => $memberData
            ]);
        } else {
            logError('No approved record found for registration_id: ' . $registration_id);
            echo json_encode([
                'success' => false, 
                'message' => 'Registration ID "' . $registration_id . '" not found or not approved'
            ]);
        }
        
    } catch (PDOException $e) {
        logError('Database error in id-card-download.php: ' . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => 'Database connection error. Please try again.'
        ]);
    } catch (Exception $e) {
        logError('General error in id-card-download.php: ' . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => 'An unexpected error occurred. Please try again.'
        ]);
    }
    exit;
}

include 'header.php';
include 'navbar.php';
?>

<main class="container my-5">
    <h3 class="section-heading text-center"><span>ID Card Download</span></h3>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card download-card p-4">
                <div class="card-body">
                    <h5 class="text-center mb-4">Enter Your Registration ID</h5>
                    <form id="download-form">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
                        <div class="mb-3">
                            <label for="registration-id" class="form-label">Registration ID</label>
                            <input type="text" class="form-control" id="registration-id" name="registration_id" 
                                   placeholder="Enter your registration ID (e.g., CGMA20250809001)" required
                                   pattern="CGMA[0-9A-Z]+">
                            <div class="form-text">Format: CGMA20250809001</div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-download-primary" onclick="generateUniversalIdCard()">
                                <i class="fas fa-id-card me-2"></i>Generate ID Card
                            </button>
                        </div>
                    </form>
                    <div id="loading" style="display: none; text-align: center; margin-top: 20px;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p>Generating ID Card...</p>
                    </div>
                    <div id="downloadSection" style="display: none; margin-top: 20px; text-align: center;">
                        <a id="downloadLink" href="#" class="btn btn-success">
                            <i class="fas fa-download me-2"></i>Download ID Card
                        </a>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-12 text-center">
                    <canvas id="idCardCanvas" width="590" height="2040" style="border: 1px solid #ddd; max-width: 100%; height: auto;"></canvas>
                </div>
            </div>
        </div>
    </div>
</main>

 Required Libraries 
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.4.4/build/qrcode.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

 Generate empty JavaScript to make the generator available 
<?php echo $idCardGenerator->generateJavaScript([]); ?>

<script>
// Global variables
let memberData = null;

// Fetch user data
async function fetchUserData(registrationId) {
    console.log('Fetching data for registration ID:', registrationId);
    const formData = new FormData();
    formData.append('action', 'get_user_data');
    formData.append('registration_id', registrationId);
    formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);

    try {
        const response = await fetch(window.location.href, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Expected JSON but got:', text);
            throw new Error('Server returned non-JSON response');
        }
        
        const data = await response.json();
        console.log('Fetch response:', data);
        
        if (!data.success) {
            throw new Error(data.message || 'Failed to fetch user data');
        }
        
        return data.data;
    } catch (error) {
        console.error('Fetch error:', error);
        throw error;
    }
}

// Main ID card generation function
async function generateUniversalIdCard() {
    const registrationId = document.getElementById('registration-id').value.trim().toUpperCase();
    console.log('Generating ID card for:', registrationId);
    
    if (!registrationId || !/^CGMA[0-9A-Z]+$/.test(registrationId)) {
        Swal.fire('Error', 'Please enter a valid registration ID (e.g., CGMA20250809001)', 'error');
        return;
    }

    document.getElementById('loading').style.display = 'block';
    document.getElementById('downloadSection').style.display = 'none';

    try {
        memberData = await fetchUserData(registrationId);
        console.log('Member data:', memberData);
        
        if (!memberData) {
            throw new Error('No member data received');
        }
        
        // Update the universal generator with fetched data
        UniversalIdCardGenerator.memberData = memberData;
        
        UniversalIdCardGenerator.generate((success, result) => {
            document.getElementById('loading').style.display = 'none';
            
            if (success) {
                const downloadLink = document.getElementById('downloadLink');
                downloadLink.href = result;
                downloadLink.download = `ID_Card_${memberData.member_id}.png`;
                document.getElementById('downloadSection').style.display = 'block';
                
                Swal.fire({
                    title: 'Success!',
                    text: 'ID Card generated successfully! Click the download button.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: 'Failed to generate ID card: ' + result,
                    icon: 'error'
                });
            }
        });
        
    } catch (error) {
        console.error('Generate ID card error:', error);
        document.getElementById('loading').style.display = 'none';
        Swal.fire({
            title: 'Error',
            text: error.message || 'Failed to generate ID card',
            icon: 'error'
        });
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    console.log('ID Card Download page loaded');
    document.getElementById('registration-id').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            generateUniversalIdCard();
        }
    });
});
</script>

<style>
.download-card {
    border: 2px solid #114471;
    border-radius: 15px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.btn-download-primary {
    background-color: #114471;
    border-color: #114471;
    color: white;
    padding: 12px 20px;
    font-weight: bold;
    border-radius: 8px;
}
.btn-download-primary:hover {
    background-color: #0d3559;
    border-color: #0d3559;
}
#loading {
    margin: 20px 0;
}
.spinner-border {
    width: 3rem;
    height: 3rem;
}
.section-heading {
    color: #114471;
    font-weight: bold;
    margin-bottom: 30px;
}
.section-heading span {
    border-bottom: 3px solid #e31e24;
    padding-bottom: 5px;
}
</style>

<?php include 'footer.php'; ?>
