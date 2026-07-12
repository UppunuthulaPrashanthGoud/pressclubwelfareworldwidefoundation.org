<?php
/**
 * Razorpay Payment Button Component with Script
 * 
 * @param string $type - 'donation' or 'membership'
 * @param string $button_text - Optional custom button text
 * @param string $heading - Optional custom heading
 * @param string $razorpay_url - Razorpay payment link URL
 */

// Default values
$type = isset($type) ? $type : 'donation';
$button_text = isset($button_text) ? $button_text : 'Pay Now';
$heading = isset($heading) ? $heading : 'Quick Online Payment';

// Razorpay payment link (you can make this configurable)
$razorpay_url = isset($razorpay_url) ? $razorpay_url : 'https://pages.razorpay.com/pl_MOeFYdD5xa3fOS/view';

// Type-specific configurations
$configurations = [
    'donation' => [
        'heading' => 'Quick Online Donation',
        'button_text' => 'Donate Now',
        'icon' => 'fa-heart',
        'color' => '#528FF0'
    ],
    'membership' => [
        'heading' => 'Quick Online Payment for Membership',
        'button_text' => 'Pay Membership Fee',
        'icon' => 'fa-credit-card',
        'color' => '#528FF0'
    ]
];

$config = $configurations[$type] ?? $configurations['donation'];

// Override with custom values if provided
$final_heading = !empty($heading) && $heading !== 'Quick Online Payment' ? $heading : $config['heading'];
$final_button_text = !empty($button_text) && $button_text !== 'Pay Now' ? $button_text : $config['button_text'];
?>

<!-- Razorpay Payment Button Section -->
<div class="row mb-4">
    <div class="col-md-8 offset-md-2">
        <div class="razorpay-payment-button-section p-4 rounded text-center">
            <h5 class="mb-3">
                <i class="fas <?php echo $config['icon']; ?> me-2"></i>
                <?php echo htmlspecialchars($final_heading); ?>
            </h5>
            
            <div class="razorpay-embed-btn" 
                 data-url="<?php echo htmlspecialchars($razorpay_url); ?>" 
                 data-text="<?php echo htmlspecialchars($final_button_text); ?>" 
                 data-color="<?php echo htmlspecialchars($config['color']); ?>" 
                 data-size="large">
            </div>
            
            <div class="alert alert-info mt-3 mb-0" style="font-size: 0.9rem;">
                <i class="fas fa-info-circle"></i> 
                <strong>Important:</strong> Please enter your registered email and phone number while making payment
            </div>
            
            <?php if ($type === 'membership'): ?>
            <p class="text-muted small mt-2 mb-0">
                After successful payment, your membership will be activated within 24 hours
            </p>
            <?php endif; ?>
        </div>
    </div>
</div>
<hr class="my-4">

<style>
.razorpay-payment-button-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 2px solid #dee2e6;
    transition: all 0.3s ease;
}

.razorpay-payment-button-section:hover {
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.razorpay-payment-button-section h5 {
    color: #291872;
    font-weight: 600;
}

.razorpay-embed-btn {
    margin: 15px 0;
}

.razorpay-payment-button-section .alert-info {
    background-color: #e7f3ff;
    border-color: #b3d9ff;
    color: #004085;
}
</style>

<script>
// Razorpay Embed Button Initialization
(function(){
    var d = document; 
    var x = !d.getElementById('razorpay-embed-btn-js');
    
    if(x){ 
        var s = d.createElement('script'); 
        s.defer = true;
        s.id = 'razorpay-embed-btn-js';
        s.src = 'https://cdn.razorpay.com/static/embed_btn/bundle.js';
        s.onload = function() {
            console.log('Razorpay embed button script loaded successfully');
        };
        s.onerror = function() {
            console.error('Failed to load Razorpay embed button script');
        };
        d.body.appendChild(s);
    } else {
        var rzp = window['__rzp__'];
        if(rzp && rzp.init) {
            rzp.init();
        }
    }
})();
</script>