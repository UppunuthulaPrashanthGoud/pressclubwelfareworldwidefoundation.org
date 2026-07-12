<?php
// Pagination settings
$items_per_page = 12;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $items_per_page;

// Fetch Active Members (Active membership type only) with pagination
$stmt = $db->prepare("
    SELECT u.id, u.name, u.user_type, u.profile_image, u.district, u.state, u.designation, u.profession, u.membership_type, u.registration_id 
    FROM users u 
    WHERE u.status = 'approved' 
    AND u.membership_type = 'active'
    ORDER BY u.state ASC, u.district ASC, u.created_at DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$member_members = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count for pagination
$count_stmt = $db->prepare("
    SELECT COUNT(*) as total 
    FROM users u 
    WHERE u.status = 'approved' 
    AND u.membership_type = 'active'
");
$count_stmt->execute();
$total_members = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_members / $items_per_page);

// Get unique states for filter
$states = [
    'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh', 'Goa', 'Gujarat', 
    'Haryana', 'Himachal Pradesh', 'Jharkhand', 'Karnataka', 'Kerala', 'Madhya Pradesh', 
    'Maharashtra', 'Manipur', 'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Punjab', 
    'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura', 'Uttar Pradesh', 
    'Uttarakhand', 'West Bengal', 'Delhi', 'Chandigarh', 'Puducherry', 'Jammu and Kashmir', 
    'Ladakh', 'Andaman and Nicobar Islands', 'Lakshadweep', 'Dadra and Nagar Haveli and Daman and Diu'
];
sort($states);

// Get unique districts from all members (not just current page)
$district_stmt = $db->prepare("
    SELECT DISTINCT district 
    FROM users 
    WHERE status = 'approved' 
    AND membership_type = 'active' 
    AND district IS NOT NULL 
    AND district != ''
    ORDER BY district ASC
");
$district_stmt->execute();
$districts = $district_stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<style>
/* Fix for uniform image sizing */
.supporter-img-container {
    width: 100%;
    height: 280px; /* Fixed height for uniformity */
    overflow: hidden;
    position: relative;
    border-radius: 8px 8px 0 0;
    background-color: #f0f0f0;
}

.supporter-img-container img {
    width: 100%;
    height: 100%;
    object-fit: cover; /* This ensures images fill the container while maintaining aspect ratio */
    object-position: center; /* Centers the image */
    display: block;
}

/* Ensure the card has consistent structure */
.supporter-card {
    height: 100%;
    display: flex;
    flex-direction: column;
}

/* Registration ID Badge Styling */
.registration-id-badge {
    display: inline-block;
    background: white;
    color: #333;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    margin: 8px 0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    border: 2px solid rgba(255, 255, 255, 0.3);
    transition: all 0.3s ease;
}

.registration-id-badge i {
    color: #4f5eea;
    margin-right: 6px;
}

.registration-id-badge:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

/* Pagination Styling */
.pagination-container {
    margin-top: 40px;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.pagination-info {
    text-align: center;
    margin-bottom: 15px;
    color: #666;
    font-size: 14px;
}

.pagination {
    display: flex;
    gap: 8px;
    list-style: none;
    padding: 0;
    margin: 0;
    flex-wrap: wrap;
    justify-content: center;
}

.pagination .page-item {
    display: inline-block;
}

.pagination .page-link {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    padding: 8px 12px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    color: #333;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    background: white;
}

.pagination .page-link:hover {
    background: linear-gradient(135deg, #4f5eea 0%, #5f4fc8 100%);
    color: white;
    border-color: #4f5eea;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(79, 94, 234, 0.3);
}

.pagination .page-item.active .page-link {
    background: linear-gradient(135deg, #4f5eea 0%, #5f4fc8 100%);
    color: white;
    border-color: #4f5eea;
    box-shadow: 0 4px 12px rgba(79, 94, 234, 0.3);
}

.pagination .page-item.disabled .page-link {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}

.pagination .page-link i {
    font-size: 14px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .supporter-img-container {
        height: 250px;
    }
    
    .pagination .page-link {
        min-width: 36px;
        height: 36px;
        padding: 6px 10px;
        font-size: 14px;
    }
}

@media (max-width: 576px) {
    .supporter-img-container {
        height: 400px;
    }
    
    .registration-id-badge {
        font-size: 12px;
        padding: 5px 12px;
    }
    
    .pagination {
        gap: 5px;
    }
    
    .pagination .page-link {
        min-width: 32px;
        height: 32px;
        padding: 5px 8px;
        font-size: 13px;
    }
}
</style>

<section class="committee-section py-5">
    <div class="container">
        <h3 class="section-heading text-center mb-4"><span>Active Members</span></h3>
        
        <!-- Total Count Info -->
        <div class="text-center mb-4">
            <span class="badge bg-primary fs-6 px-4 py-2">
                <i class="fas fa-users me-2"></i>Total Active Members: <?php echo number_format($total_members); ?>
            </span>
        </div>
        
        <!-- Filters and Search -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <select id="member-state-filter" class="form-select">
                    <option value="">All States</option>
                    <?php foreach ($states as $state): ?>
                        <option value="<?php echo htmlspecialchars($state); ?>"><?php echo htmlspecialchars($state); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <select id="member-district-filter" class="form-select">
                    <option value="">All Districts</option>
                    <?php foreach ($districts as $district): ?>
                        <option value="<?php echo htmlspecialchars($district); ?>"><?php echo htmlspecialchars($district); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" id="member-search" class="form-control" placeholder="Search by name, designation, or area...">
                </div>
            </div>
        </div>

        <!-- Members Grid -->
        <div id="member-grid" class="row g-4 justify-content-center">
            <?php if (empty($member_members)): ?>
                <div class="col-12 text-center text-muted mt-5">
                    <h5><i class="fas fa-info-circle"></i> No active members found.</h5>
                </div>
            <?php else: ?>
                <?php foreach ($member_members as $member): ?>
                    <div class="col-xl-3 col-lg-4 col-md-6 member-item" 
                         data-name="<?php echo strtolower(htmlspecialchars($member['name'])); ?>" 
                         data-type="<?php echo strtolower(htmlspecialchars($member['user_type'])); ?>"
                         data-designation="<?php echo strtolower(htmlspecialchars($member['designation'] ?? '')); ?>"
                         data-state="<?php echo strtolower(htmlspecialchars($member['state'])); ?>"
                         data-district="<?php echo strtolower(htmlspecialchars($member['district'])); ?>"
                         data-profession="<?php echo strtolower(htmlspecialchars($member['profession'] ?? '')); ?>">
                        <div class="supporter-card">
                            <div class="supporter-img-container">
                                <img src="<?php echo $member['profile_image'] ? 'uploads/profiles/' . htmlspecialchars($member['profile_image']) : 'img/default-avatar.png'; ?>" 
                                     alt="<?php echo htmlspecialchars($member['name']); ?>"
                                     loading="lazy">
                            </div>
                            <div class="supporter-body">
                                <div class="supporter-name">
                                    <h5><?php echo htmlspecialchars($member['name']); ?></h5>
                                </div>
                                <div class="supporter-details text-center">
                                    <p class="mb-1 fw-bold">
                                        <?php 
                                        if ($member['designation']) {
                                            echo htmlspecialchars($member['designation']);
                                        } else {
                                            echo 'Active Member';
                                        }
                                        ?>
                                    </p>
                                    
                                    <?php if ($member['registration_id']): ?>
                                        <div class="registration-id-badge">
                                            <i class="fas fa-id-card"></i><?php echo htmlspecialchars($member['registration_id']); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <p class="mb-1 small text-white-50 mt-2">
                                        <i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($member['district'] . ', ' . $member['state']); ?>
                                    </p>
                                    
                                    <?php if ($member['profession']): ?>
                                        <p class="mb-0 small text-white-50">
                                            <i class="fas fa-briefcase me-1"></i><?php echo htmlspecialchars($member['profession']); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <!-- No results message -->
            <div id="member-no-results" class="col-12 text-center text-muted mt-5 d-none">
                <h4><i class="fas fa-exclamation-circle"></i> No members found matching your search.</h4>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination-container">
            <div class="pagination-info">
                Showing page <?php echo $current_page; ?> of <?php echo $total_pages; ?>
                (<?php echo number_format($total_members); ?> total members)
            </div>
            <ul class="pagination">
                <!-- First Page -->
                <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=1" title="First Page">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                </li>
                
                <!-- Previous Page -->
                <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo max(1, $current_page - 1); ?>" title="Previous">
                        <i class="fas fa-angle-left"></i>
                    </a>
                </li>

                <?php
                // Calculate page range to show
                $range = 2; // Number of pages to show on each side of current page
                $start_page = max(1, $current_page - $range);
                $end_page = min($total_pages, $current_page + $range);

                // Adjust range if at the beginning or end
                if ($current_page <= $range) {
                    $end_page = min($total_pages, $range * 2 + 1);
                }
                if ($current_page > $total_pages - $range) {
                    $start_page = max(1, $total_pages - ($range * 2));
                }

                // Show first page if not in range
                if ($start_page > 1) {
                    echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
                    if ($start_page > 2) {
                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                }

                // Show page numbers
                for ($i = $start_page; $i <= $end_page; $i++):
                ?>
                    <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <?php
                // Show last page if not in range
                if ($end_page < $total_pages) {
                    if ($end_page < $total_pages - 1) {
                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                    echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . '">' . $total_pages . '</a></li>';
                }
                ?>

                <!-- Next Page -->
                <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo min($total_pages, $current_page + 1); ?>" title="Next">
                        <i class="fas fa-angle-right"></i>
                    </a>
                </li>
                
                <!-- Last Page -->
                <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $total_pages; ?>" title="Last Page">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                </li>
            </ul>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('member-search');
    const stateFilter = document.getElementById('member-state-filter');
    const districtFilter = document.getElementById('member-district-filter');
    const memberItems = document.querySelectorAll('.member-item');
    const noResultsDiv = document.getElementById('member-no-results');
    const paginationContainer = document.querySelector('.pagination-container');

    function filterMembers() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const selectedState = stateFilter.value.toLowerCase();
        const selectedDistrict = districtFilter.value.toLowerCase();
        let visibleCount = 0;

        memberItems.forEach(item => {
            const name = item.getAttribute('data-name');
            const designation = item.getAttribute('data-designation');
            const state = item.getAttribute('data-state');
            const district = item.getAttribute('data-district');
            const profession = item.getAttribute('data-profession');

            const matchesSearch = !searchTerm || 
                name.includes(searchTerm) || 
                designation.includes(searchTerm) || 
                state.includes(searchTerm) || 
                district.includes(searchTerm) ||
                profession.includes(searchTerm);
            
            const matchesState = !selectedState || state === selectedState;
            const matchesDistrict = !selectedDistrict || district === selectedDistrict;

            if (matchesSearch && matchesState && matchesDistrict) {
                item.style.display = 'block';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        // Show/hide no results message
        if (visibleCount === 0) {
            noResultsDiv.classList.remove('d-none');
        } else {
            noResultsDiv.classList.add('d-none');
        }

        // Hide pagination if filtering is active
        if (paginationContainer) {
            if (searchTerm || selectedState || selectedDistrict) {
                paginationContainer.style.display = 'none';
            } else {
                paginationContainer.style.display = 'flex';
            }
        }
    }

    searchInput.addEventListener('input', filterMembers);
    stateFilter.addEventListener('change', filterMembers);
    districtFilter.addEventListener('change', filterMembers);
});
</script>