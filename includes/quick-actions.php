<?php
/**
 * Quick Action Buttons Component
 * Displays quick action buttons for user interactions
 */
?>

<div class="quick-action-buttons d-flex flex-wrap justify-content-center gap-3 mb-4" data-aos="fade-up">
    <a href="users-apply-form.php" class="btn btn-action"><i class="fa fa-user-plus"></i> Become a Member</a>
    <!-- <a href="id-card-download.php" class="btn btn-action"><i class="fa fa-id-card"></i> ID Card</a> -->
    <a href="donation-form.php" class="btn btn-action"><i class="fa fa-money-bill"></i> Donate</a>
    <a href="upcoming-event.php" class="btn btn-action"><i class="fa fa-tasks"></i> Upcoming Events</a>
    <a href="management-team.php" class="btn btn-action"><i class="fa fa-users"></i> Management Team</a>
    <a href="crowdfunding.php" class="btn btn-action"><i class="fas fa-hand-holding-heart"></i> Crowdfunding</a>
</div>

<style>
@media (max-width: 768px) {
    .quick-action-buttons {
        gap: 2px !important;
    }
    
    .quick-action-buttons .btn {
        font-size: 12px;
        padding: 8px 12px;
    }
    
    .quick-action-buttons .btn i {
        font-size: 14px;
    }
}
</style>