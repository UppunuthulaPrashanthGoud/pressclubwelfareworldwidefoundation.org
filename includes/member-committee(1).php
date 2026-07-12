<?php
// Fetch Active Members (Active membership type only)
$stmt = $db->prepare("
    SELECT u.id, u.name, u.user_type, u.profile_image, u.district, u.state, u.designation, u.profession, u.membership_type, u.registration_id 
    FROM users u 
    WHERE u.status = 'approved' 
    AND u.membership_type = 'active'
    ORDER BY u.state ASC, u.district ASC, u.created_at DESC
");
$stmt->execute();
$member_members = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

// Get unique districts from member members
$districts = array_unique(array_filter(array_column($member_members, 'district')));
sort($districts);
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

/* Responsive adjustments */
@media (max-width: 768px) {
    .supporter-img-container {
        height: 250px;
    }
}

@media (max-width: 576px) {
    .supporter-img-container {
        height: 220px;
    }
}

@media (max-width: 576px) {
    .supporter-img-container {
        height: 400px;
    }
}
</style>

<section class="committee-section py-5">
    <div class="container">
        <h3 class="section-heading text-center mb-4"><span>Active Members</span></h3>
        
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
                                        <p class="mb-1 small text-white-50"><i class="fas fa-id-card me-1"></i><?php echo htmlspecialchars($member['registration_id']); ?></p>
                                    <?php endif; ?>
                                    <p class="mb-0 small text-white-50"><i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($member['district'] . ', ' . $member['state']); ?></p>
                                    <?php if ($member['profession']): ?>
                                        <p class="mb-0 small text-white-50"><i class="fas fa-briefcase me-1"></i><?php echo htmlspecialchars($member['profession']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <!-- No results message -->
            <div id="member-no-results" class="col-12 text-center text-muted mt-5 d-none">
                <h4><i class="fas fa-exclamation-circle"></i> No members found.</h4>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('member-search');
    const stateFilter = document.getElementById('member-state-filter');
    const districtFilter = document.getElementById('member-district-filter');
    const memberItems = document.querySelectorAll('.member-item');
    const noResultsDiv = document.getElementById('member-no-results');

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

        if (visibleCount === 0) {
            noResultsDiv.classList.remove('d-none');
        } else {
            noResultsDiv.classList.add('d-none');
        }
    }

    searchInput.addEventListener('input', filterMembers);
    stateFilter.addEventListener('change', filterMembers);
    districtFilter.addEventListener('change', filterMembers);
});
</script>