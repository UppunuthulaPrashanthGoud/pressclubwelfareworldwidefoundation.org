<?php
/**
 * Management Team Section Component
 * Displays management team members on homepage
 */
?>

<!-- MANAGEMENT TEAM SECTION -->
<?php if (!empty($management_team)): ?>
<div class="container my-5">
    <h3 class="section-heading text-center"><span>Management Team</span></h3>

    <!-- Management Team Grid -->
    <div id="management-team-section" class="row g-4 justify-content-center">
        <?php 
        $display_limit = 8; // Show 8 items per page in section
        $total_members = count($management_team);
        
        foreach ($management_team as $index => $member): 
            $page_number = floor($index / $display_limit) + 1;
        ?>
            <div class="col-xl-3 col-lg-4 col-md-6 management-member-card" data-page="<?php echo $page_number; ?>" style="<?php echo $page_number > 1 ? 'display: none;' : ''; ?>">
                <div class="supporter-card">
                    <div class="supporter-img-container">
                        <img src="<?php echo $member['image'] ? 'uploads/team/' . htmlspecialchars($member['image']) : 'img/default-avatar.png'; ?>" 
                             alt="<?php echo htmlspecialchars($member['name']); ?>">
                    </div>
                    <div class="supporter-body">
                        <div class="supporter-name">
                            <h5><?php echo htmlspecialchars($member['name']); ?></h5>
                        </div>
                        <div class="supporter-details text-center">
                            <p class="mb-1 fw-bold"><?php echo htmlspecialchars($member['designation']); ?></p>
                            <?php if ($member['email']): ?>
                                <p class="mb-0 small text-white-50"><i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($member['email']); ?></p>
                            <?php endif; ?>
                            <?php if ($member['phone']): ?>
                                <p class="mb-0 small text-white-50"><i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($member['phone']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <?php if ($total_members > $display_limit): 
        $total_pages = ceil($total_members / $display_limit);
    ?>
    <!-- Section Pagination -->
    <nav aria-label="Management team pagination" class="mt-4">
        <ul class="pagination pagination-sm justify-content-center" id="management-team-pagination">
            <li class="page-item disabled" id="mgmt-prev">
                <a class="page-link" href="#management-team-section" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $i === 1 ? 'active' : ''; ?>" data-page="<?php echo $i; ?>">
                    <a class="page-link" href="#management-team-section"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php echo $total_pages === 1 ? 'disabled' : ''; ?>" id="mgmt-next">
                <a class="page-link" href="#management-team-section" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
    
    <div class="text-center mt-4">
        <a href="management-team.php" class="btn btn-primary btn-lg">
            <i class="fas fa-user-tie me-2"></i>View Complete Management Team
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mgmtCards = document.querySelectorAll('.management-member-card');
    const mgmtPagination = document.getElementById('management-team-pagination');
    
    if (mgmtPagination) {
        const mgmtPageItems = mgmtPagination.querySelectorAll('.page-item[data-page]');
        const mgmtPrevBtn = document.getElementById('mgmt-prev');
        const mgmtNextBtn = document.getElementById('mgmt-next');
        let currentMgmtPage = 1;
        const totalMgmtPages = mgmtPageItems.length;
        
        function showMgmtPage(pageNum) {
            // Hide all cards
            mgmtCards.forEach(card => {
                card.style.display = 'none';
            });
            
            // Show cards for current page
            const cardsToShow = document.querySelectorAll(`.management-member-card[data-page="${pageNum}"]`);
            cardsToShow.forEach(card => {
                card.style.display = 'block';
            });
            
            // Update pagination active state
            mgmtPageItems.forEach(item => {
                item.classList.remove('active');
                if (parseInt(item.dataset.page) === pageNum) {
                    item.classList.add('active');
                }
            });
            
            // Update prev/next buttons
            if (pageNum <= 1) {
                mgmtPrevBtn.classList.add('disabled');
            } else {
                mgmtPrevBtn.classList.remove('disabled');
            }
            
            if (pageNum >= totalMgmtPages) {
                mgmtNextBtn.classList.add('disabled');
            } else {
                mgmtNextBtn.classList.remove('disabled');
            }
            
            currentMgmtPage = pageNum;
        }
        
        // Page number click
        mgmtPageItems.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const pageNum = parseInt(this.dataset.page);
                showMgmtPage(pageNum);
            });
        });
        
        // Previous button
        mgmtPrevBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (currentMgmtPage > 1) {
                showMgmtPage(currentMgmtPage - 1);
            }
        });
        
        // Next button
        mgmtNextBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (currentMgmtPage < totalMgmtPages) {
                showMgmtPage(currentMgmtPage + 1);
            }
        });
    }
});
</script>
<?php endif; ?>