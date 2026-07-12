<?php
/**
 * Team Members Section Component
 * Displays recent team members on homepage
 */
?>

<!-- OUR TEAM MEMBERS SECTION -->
<?php if (!empty($recent_users)): ?>
<div class="container my-5">
    <h3 class="section-heading text-center"><span>Our Team Members</span></h3>

    <!-- Users Grid -->
    <div id="team-members-section" class="row g-4 justify-content-center">
        <?php 
        $display_limit = 8; // Show 8 items per page in section
        $total_users = count($recent_users);
        
        foreach ($recent_users as $index => $user): 
            $page_number = floor($index / $display_limit) + 1;
        ?>
            <div class="col-xl-3 col-lg-4 col-md-6 team-member-card" data-page="<?php echo $page_number; ?>" style="<?php echo $page_number > 1 ? 'display: none;' : ''; ?>">
                <div class="supporter-card">
                    <div class="supporter-img-container">
                        <img src="<?php echo $user['profile_image'] ? 'uploads/profiles/' . htmlspecialchars($user['profile_image']) : 'img/default-avatar.png'; ?>" 
                             alt="<?php echo htmlspecialchars($user['name']); ?>">
                    </div>
                    <div class="supporter-body">
                        <div class="supporter-name">
                            <h5><?php echo htmlspecialchars($user['name']); ?></h5>
                        </div>
                        <div class="supporter-details text-center">
                            <p class="mb-1 fw-bold"><?php echo htmlspecialchars($user['designation_hindi'] ?? ucfirst(str_replace('_', ' ', $user['membership_type']))); ?></p>
                            <p class="mb-0 small text-white-50"><i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($user['district'] . ', ' . $user['state']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <?php if ($total_users > $display_limit): 
        $total_pages = ceil($total_users / $display_limit);
    ?>
    <!-- Section Pagination -->
    <nav aria-label="Team members pagination" class="mt-4">
        <ul class="pagination pagination-sm justify-content-center" id="team-members-pagination">
            <li class="page-item disabled" id="team-prev">
                <a class="page-link" href="#team-members-section" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $i === 1 ? 'active' : ''; ?>" data-page="<?php echo $i; ?>">
                    <a class="page-link" href="#team-members-section"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php echo $total_pages === 1 ? 'disabled' : ''; ?>" id="team-next">
                <a class="page-link" href="#team-members-section" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
    
    <div class="text-center mt-4">
        <a href="our-team.php" class="btn btn-primary btn-lg">
            <i class="fas fa-users me-2"></i>View All Team Members
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const teamCards = document.querySelectorAll('.team-member-card');
    const teamPagination = document.getElementById('team-members-pagination');
    
    if (teamPagination) {
        const teamPageItems = teamPagination.querySelectorAll('.page-item[data-page]');
        const teamPrevBtn = document.getElementById('team-prev');
        const teamNextBtn = document.getElementById('team-next');
        let currentTeamPage = 1;
        const totalTeamPages = teamPageItems.length;
        
        function showTeamPage(pageNum) {
            // Hide all cards
            teamCards.forEach(card => {
                card.style.display = 'none';
            });
            
            // Show cards for current page
            const cardsToShow = document.querySelectorAll(`.team-member-card[data-page="${pageNum}"]`);
            cardsToShow.forEach(card => {
                card.style.display = 'block';
            });
            
            // Update pagination active state
            teamPageItems.forEach(item => {
                item.classList.remove('active');
                if (parseInt(item.dataset.page) === pageNum) {
                    item.classList.add('active');
                }
            });
            
            // Update prev/next buttons
            if (pageNum <= 1) {
                teamPrevBtn.classList.add('disabled');
            } else {
                teamPrevBtn.classList.remove('disabled');
            }
            
            if (pageNum >= totalTeamPages) {
                teamNextBtn.classList.add('disabled');
            } else {
                teamNextBtn.classList.remove('disabled');
            }
            
            currentTeamPage = pageNum;
        }
        
        // Page number click
        teamPageItems.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const pageNum = parseInt(this.dataset.page);
                showTeamPage(pageNum);
            });
        });
        
        // Previous button
        teamPrevBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (currentTeamPage > 1) {
                showTeamPage(currentTeamPage - 1);
            }
        });
        
        // Next button
        teamNextBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (currentTeamPage < totalTeamPages) {
                showTeamPage(currentTeamPage + 1);
            }
        });
    }
});
</script>
<?php endif; ?>