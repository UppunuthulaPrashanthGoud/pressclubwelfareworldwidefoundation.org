<?php
require_once 'config.php'; // Your database connection file

// Fetch National Committee Members (Admins and Coordinators)
$stmt_national = $db->prepare("
    SELECT u.id, u.name, u.user_type, u.profile_image, u.district, u.state, u.designation, u.profession 
    FROM users u 
    WHERE u.status = 'approved' 
    AND u.user_type IN ('admin', 'coordinator')
    ORDER BY 
        CASE u.user_type 
            WHEN 'admin' THEN 1 
            WHEN 'coordinator' THEN 2 
        END,
        u.state ASC,
        u.district ASC,
        u.created_at DESC
");
$stmt_national->execute();
$national_members = $stmt_national->fetchAll(PDO::FETCH_ASSOC);

// Fetch State Committee Members (State membership type)
$stmt_state = $db->prepare("
    SELECT u.id, u.name, u.user_type, u.profile_image, u.district, u.state, u.designation, u.profession, u.membership_type 
    FROM users u 
    WHERE u.status = 'approved' 
    AND u.membership_type = 'state'
    ORDER BY u.state ASC, u.district ASC, u.created_at DESC
");
$stmt_state->execute();
$state_members = $stmt_state->fetchAll(PDO::FETCH_ASSOC);

// Fetch District Committee Members (District, Block, Tehsil, Mandal membership types)
$stmt_district = $db->prepare("
    SELECT u.id, u.name, u.user_type, u.profile_image, u.district, u.state, u.designation, u.profession, u.membership_type 
    FROM users u 
    WHERE u.status = 'approved' 
    AND u.membership_type IN ('district', 'block', 'tehsil', 'mandal')
    ORDER BY 
        CASE u.membership_type 
            WHEN 'district' THEN 1 
            WHEN 'block' THEN 2 
            WHEN 'tehsil' THEN 3 
            WHEN 'mandal' THEN 4 
        END,
        u.state ASC, 
        u.district ASC, 
        u.created_at DESC
");
$stmt_district->execute();
$district_members = $stmt_district->fetchAll(PDO::FETCH_ASSOC);

// Fetch Member Committee Members (Active membership type only)
$stmt_member = $db->prepare("
    SELECT u.id, u.name, u.user_type, u.profile_image, u.district, u.state, u.designation, u.profession, u.membership_type 
    FROM users u 
    WHERE u.status = 'approved' 
    AND u.membership_type = 'active'
    ORDER BY u.state ASC, u.district ASC, u.created_at DESC
");
$stmt_member->execute();
$member_members = $stmt_member->fetchAll(PDO::FETCH_ASSOC);

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

// Get all unique districts
$all_districts = array_merge(
    array_column($national_members, 'district'),
    array_column($state_members, 'district'),
    array_column($district_members, 'district'),
    array_column($member_members, 'district')
);
$districts = array_unique(array_filter($all_districts));
sort($districts);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Committees</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .committee-tabs {
            margin-bottom: 30px;
        }
        .nav-tabs .nav-link {
            color: #666;
            font-weight: 500;
            border: none;
            border-bottom: 3px solid transparent;
        }
        .nav-tabs .nav-link.active {
            color: #007bff;
            border-bottom: 3px solid #007bff;
            background: transparent;
        }
        .section-heading {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 2rem;
        }
        .section-heading span {
            border-bottom: 3px solid #007bff;
            padding-bottom: 10px;
        }
        .supporter-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .supporter-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        /* Fix for uniform image sizing */
        .supporter-img-container {
            width: 100%;
            height: 280px;
            overflow: hidden;
            position: relative;
            background-color: #f0f0f0;
        }
        .supporter-img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            display: block;
        }
        .supporter-body {
            padding: 20px;
            color: white;
        }
        .supporter-name h5 {
            margin: 0 0 15px 0;
            font-weight: 600;
            text-align: center;
            font-size: 1.1rem;
        }
        .supporter-details p {
            margin-bottom: 5px;
            font-size: 0.9rem;
        }
        .form-select, .form-control {
            border-radius: 8px;
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
    </style>
</head>
<body>
    <div class="container-fluid py-5">
        <!-- Committee Tabs -->
        <ul class="nav nav-tabs committee-tabs justify-content-center" id="committeeTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="national-tab" data-bs-toggle="tab" data-bs-target="#national" type="button" role="tab">
                    <i class="fas fa-flag me-2"></i>National Committee
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="state-tab" data-bs-toggle="tab" data-bs-target="#state" type="button" role="tab">
                    <i class="fas fa-map me-2"></i>State Committee
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="district-tab" data-bs-toggle="tab" data-bs-target="#district" type="button" role="tab">
                    <i class="fas fa-city me-2"></i>District Committee
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="member-tab" data-bs-toggle="tab" data-bs-target="#member" type="button" role="tab">
                    <i class="fas fa-users me-2"></i>Active Members
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="committeeTabContent">
            
            <!-- NATIONAL COMMITTEE TAB -->
            <div class="tab-pane fade show active" id="national" role="tabpanel">
                <div class="container">
                    <h3 class="section-heading text-center mb-4"><span>National Committee</span></h3>
                    
                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <select id="national-state-filter" class="form-select">
                                <option value="">All States</option>
                                <?php foreach ($states as $state): ?>
                                    <option value="<?php echo htmlspecialchars($state); ?>"><?php echo htmlspecialchars($state); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <select id="national-district-filter" class="form-select">
                                <option value="">All Districts</option>
                                <?php foreach ($districts as $district): ?>
                                    <option value="<?php echo htmlspecialchars($district); ?>"><?php echo htmlspecialchars($district); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" id="national-search" class="form-control" placeholder="Search by name, designation, or area...">
                            </div>
                        </div>
                    </div>

                    <!-- Members Grid -->
                    <div id="national-grid" class="row g-4 justify-content-center">
                        <?php if (empty($national_members)): ?>
                            <div class="col-12 text-center text-muted mt-5">
                                <h5><i class="fas fa-info-circle"></i> No national committee members found.</h5>
                            </div>
                        <?php else: ?>
                            <?php foreach ($national_members as $member): ?>
                                <div class="col-xl-3 col-lg-4 col-md-6 national-item" 
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
                                                        echo $member['user_type'] === 'admin' ? 'Administrator' : 'National Coordinator';
                                                    }
                                                    ?>
                                                </p>
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
                        
                        <div id="national-no-results" class="col-12 text-center text-muted mt-5 d-none">
                            <h4><i class="fas fa-exclamation-circle"></i> No members found.</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STATE COMMITTEE TAB -->
            <div class="tab-pane fade" id="state" role="tabpanel">
                <div class="container">
                    <h3 class="section-heading text-center mb-4"><span>State Committee</span></h3>
                    
                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <select id="state-state-filter" class="form-select">
                                <option value="">All States</option>
                                <?php foreach ($states as $state): ?>
                                    <option value="<?php echo htmlspecialchars($state); ?>"><?php echo htmlspecialchars($state); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <select id="state-district-filter" class="form-select">
                                <option value="">All Districts</option>
                                <?php foreach ($districts as $district): ?>
                                    <option value="<?php echo htmlspecialchars($district); ?>"><?php echo htmlspecialchars($district); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" id="state-search" class="form-control" placeholder="Search by name, designation, or area...">
                            </div>
                        </div>
                    </div>

                    <!-- Members Grid -->
                    <div id="state-grid" class="row g-4 justify-content-center">
                        <?php if (empty($state_members)): ?>
                            <div class="col-12 text-center text-muted mt-5">
                                <h5><i class="fas fa-info-circle"></i> No state committee members found.</h5>
                            </div>
                        <?php else: ?>
                            <?php foreach ($state_members as $member): ?>
                                <div class="col-xl-3 col-lg-4 col-md-6 state-item" 
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
                                                        echo 'State Coordinator';
                                                    }
                                                    ?>
                                                </p>
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
                        
                        <div id="state-no-results" class="col-12 text-center text-muted mt-5 d-none">
                            <h4><i class="fas fa-exclamation-circle"></i> No members found.</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DISTRICT COMMITTEE TAB -->
            <div class="tab-pane fade" id="district" role="tabpanel">
                <div class="container">
                    <h3 class="section-heading text-center mb-4"><span>District Committee</span></h3>
                    
                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-md-2 mb-3">
                            <select id="district-state-filter" class="form-select">
                                <option value="">All States</option>
                                <?php foreach ($states as $state): ?>
                                    <option value="<?php echo htmlspecialchars($state); ?>"><?php echo htmlspecialchars($state); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <select id="district-district-filter" class="form-select">
                                <option value="">All Districts</option>
                                <?php foreach ($districts as $district): ?>
                                    <option value="<?php echo htmlspecialchars($district); ?>"><?php echo htmlspecialchars($district); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <select id="district-type-filter" class="form-select">
                                <option value="">All Types</option>
                                <option value="district">District</option>
                                <option value="block">Block</option>
                                <option value="tehsil">Tehsil</option>
                                <option value="mandal">Mandal</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" id="district-search" class="form-control" placeholder="Search by name, designation, or area...">
                            </div>
                        </div>
                    </div>

                    <!-- Members Grid -->
                    <div id="district-grid" class="row g-4 justify-content-center">
                        <?php if (empty($district_members)): ?>
                            <div class="col-12 text-center text-muted mt-5">
                                <h5><i class="fas fa-info-circle"></i> No district committee members found.</h5>
                            </div>
                        <?php else: ?>
                            <?php foreach ($district_members as $member): ?>
                                <div class="col-xl-3 col-lg-4 col-md-6 district-item" 
                                     data-name="<?php echo strtolower(htmlspecialchars($member['name'])); ?>" 
                                     data-type="<?php echo strtolower(htmlspecialchars($member['user_type'])); ?>"
                                     data-designation="<?php echo strtolower(htmlspecialchars($member['designation'] ?? '')); ?>"
                                     data-state="<?php echo strtolower(htmlspecialchars($member['state'])); ?>"
                                     data-district="<?php echo strtolower(htmlspecialchars($member['district'])); ?>"
                                     data-membership="<?php echo strtolower(htmlspecialchars($member['membership_type'])); ?>"
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
                                                        echo ucfirst($member['membership_type']) . ' Coordinator';
                                                    }
                                                    ?>
                                                </p>
                                                <p class="mb-0 small text-white-50"><i class="fas fa-layer-group me-1"></i><?php echo ucfirst(htmlspecialchars($member['membership_type'])); ?> Level</p>
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
                        
                        <div id="district-no-results" class="col-12 text-center text-muted mt-5 d-none">
                            <h4><i class="fas fa-exclamation-circle"></i> No members found.</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MEMBER COMMITTEE TAB -->
            <div class="tab-pane fade" id="member" role="tabpanel">
                <div class="container">
                    <h3 class="section-heading text-center mb-4"><span>Active Members</span></h3>
                    
                    <!-- Filters -->
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
                        
                        <div id="member-no-results" class="col-12 text-center text-muted mt-5 d-none">
                            <h4><i class="fas fa-exclamation-circle"></i> No members found.</h4>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // National Committee Filter
        const nationalSearch = document.getElementById('national-search');
        const nationalStateFilter = document.getElementById('national-state-filter');
        const nationalDistrictFilter = document.getElementById('national-district-filter');
        const nationalItems = document.querySelectorAll('.national-item');
        const nationalNoResults = document.getElementById('national-no-results');

        function filterNational() {
            const searchTerm = nationalSearch.value.toLowerCase().trim();
            const selectedState = nationalStateFilter.value.toLowerCase();
            const selectedDistrict = nationalDistrictFilter.value.toLowerCase();
            let visibleCount = 0;

            nationalItems.forEach(item => {
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
                nationalNoResults.classList.remove('d-none');
            } else {
                nationalNoResults.classList.add('d-none');
            }
        }

        nationalSearch.addEventListener('input', filterNational);
        nationalStateFilter.addEventListener('change', filterNational);
        nationalDistrictFilter.addEventListener('change', filterNational);

        // State Committee Filter
        const stateSearch = document.getElementById('state-search');
        const stateStateFilter = document.getElementById('state-state-filter');
        const stateDistrictFilter = document.getElementById('state-district-filter');
        const stateItems = document.querySelectorAll('.state-item');
        const stateNoResults = document.getElementById('state-no-results');

        function filterState() {
            const searchTerm = stateSearch.value.toLowerCase().trim();
            const selectedState = stateStateFilter.value.toLowerCase();
            const selectedDistrict = stateDistrictFilter.value.toLowerCase();
            let visibleCount = 0;

            stateItems.forEach(item => {
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
                stateNoResults.classList.remove('d-none');
            } else {
                stateNoResults.classList.add('d-none');
            }
        }

        stateSearch.addEventListener('input', filterState);
        stateStateFilter.addEventListener('change', filterState);
        stateDistrictFilter.addEventListener('change', filterState);

        // District Committee Filter
        const districtSearch = document.getElementById('district-search');
        const districtStateFilter = document.getElementById('district-state-filter');
        const districtDistrictFilter = document.getElementById('district-district-filter');
        const districtTypeFilter = document.getElementById('district-type-filter');
        const districtItems = document.querySelectorAll('.district-item');
        const districtNoResults = document.getElementById('district-no-results');

        function filterDistrict() {
            const searchTerm = districtSearch.value.toLowerCase().trim();
            const selectedState = districtStateFilter.value.toLowerCase();
            const selectedDistrict = districtDistrictFilter.value.toLowerCase();
            const selectedType = districtTypeFilter.value.toLowerCase();
            let visibleCount = 0;

            districtItems.forEach(item => {
                const name = item.getAttribute('data-name');
                const designation = item.getAttribute('data-designation');
                const state = item.getAttribute('data-state');
                const district = item.getAttribute('data-district');
                const membership = item.getAttribute('data-membership');
                const profession = item.getAttribute('data-profession');

                const matchesSearch = !searchTerm || 
                    name.includes(searchTerm) || 
                    designation.includes(searchTerm) || 
                    state.includes(searchTerm) || 
                    district.includes(searchTerm) ||
                    membership.includes(searchTerm) ||
                    profession.includes(searchTerm);
                
                const matchesState = !selectedState || state === selectedState;
                const matchesDistrict = !selectedDistrict || district === selectedDistrict;
                const matchesType = !selectedType || membership === selectedType;

                if (matchesSearch && matchesState && matchesDistrict && matchesType) {
                    item.style.display = 'block';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            if (visibleCount === 0) {
                districtNoResults.classList.remove('d-none');
            } else {
                districtNoResults.classList.add('d-none');
            }
        }

        districtSearch.addEventListener('input', filterDistrict);
        districtStateFilter.addEventListener('change', filterDistrict);
        districtDistrictFilter.addEventListener('change', filterDistrict);
        districtTypeFilter.addEventListener('change', filterDistrict);

        // Member Committee Filter
        const memberSearch = document.getElementById('member-search');
        const memberStateFilter = document.getElementById('member-state-filter');
        const memberDistrictFilter = document.getElementById('member-district-filter');
        const memberItems = document.querySelectorAll('.member-item');
        const memberNoResults = document.getElementById('member-no-results');

        function filterMember() {
            const searchTerm = memberSearch.value.toLowerCase().trim();
            const selectedState = memberStateFilter.value.toLowerCase();
            const selectedDistrict = memberDistrictFilter.value.toLowerCase();
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
                memberNoResults.classList.remove('d-none');
            } else {
                memberNoResults.classList.add('d-none');
            }
        }

        memberSearch.addEventListener('input', filterMember);
        memberStateFilter.addEventListener('change', filterMember);
        memberDistrictFilter.addEventListener('change', filterMember);
    });
    </script>
</body>
</html>