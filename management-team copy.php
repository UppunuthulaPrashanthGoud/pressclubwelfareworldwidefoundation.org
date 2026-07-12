<?php
session_start();
require_once 'config/config.php';

// Database connection
$db = getDbConnection();

// Fetch approved team members
$stmt = $db->prepare("
    SELECT name, designation, image, phone, email 
    FROM team_members 
    WHERE status = 'active' 
    ORDER BY sort_order ASC, created_at DESC
");
$stmt->execute();
$all_members = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch team members text content
$stmt_text = $db->prepare("
    SELECT content 
    FROM team_members_text 
    WHERE status = 'active' 
    ORDER BY sort_order ASC, created_at DESC
");
$stmt_text->execute();
$team_text_content = $stmt_text->fetchAll(PDO::FETCH_ASSOC);

// Separate members with photos and without photos
$members_with_photos = [];
$members_without_photos = [];

foreach ($all_members as $member) {
    if (!empty($member['image'])) {
        $members_with_photos[] = $member;
    } else {
        $members_without_photos[] = $member;
    }
}

// Limit photos to first 2 members
$featured_members = array_slice($members_with_photos, 0, 2);

include 'header.php'; 
include 'navbar.php'; 
?>

<main class="container my-5">
    <h3 class="section-heading text-center"><span>प्रबंधन टीम</span></h3>

    <!-- Search Bar -->
    <!--<div class="row justify-content-center mb-4">-->
    <!--    <div class="col-md-6">-->
    <!--        <div class="input-group">-->
    <!--            <span class="input-group-text"><i class="fas fa-search"></i></span>-->
    <!--            <input type="text" id="member-search" class="form-control" placeholder="नाम या पदनाम द्वारा खोजें...">-->
    <!--        </div>-->
    <!--    </div>-->
    <!--</div>-->

    <!-- Featured Members with Photos (Only 2) -->
    <?php if (!empty($featured_members)): ?>
    <div class="row justify-content-center mb-5">
        <div class="col-12">
            <h4 class="text-center mb-4 text-primary">मुख्य प्रबंधन टीम</h4>
        </div>
        <?php foreach ($featured_members as $member): ?>
            <div class="col-xl-3 col-lg-4 col-md-6 featured-member" 
                 data-name="<?php echo strtolower(htmlspecialchars($member['name'])); ?>" 
                 data-designation="<?php echo strtolower(htmlspecialchars($member['designation'])); ?>">
                <div class="supporter-card">
                    <div class="supporter-img-container">
                        <img src="uploads/team/<?php echo htmlspecialchars($member['image']); ?>" 
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
    <?php endif; ?>

    <!-- Team Members Text Content -->
    <?php if (!empty($team_text_content)): ?>
    <div class="row justify-content-center mb-5">
        <div class="col-12">
            <h4 class="text-center mb-4 text-secondary">टीम सदस्य विवरण</h4>
        </div>
        <div class="col-lg-10 col-md-12">
            <?php foreach ($team_text_content as $index => $text): ?>
                <div class="card shadow-sm mb-4 team-text-content">
                    <div class="card-body">
                        <div class="text-content">
                            <?php echo nl2br(htmlspecialchars($text['content'])); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

   
    <!-- No results message -->
    <div id="no-results" class="col-12 text-center text-muted mt-5 d-none">
        <div class="card">
            <div class="card-body">
                <h4><i class="fas fa-exclamation-circle"></i> कोई टीम सदस्य नहीं मिले।</h4>
                <p>कृपया अपनी खोज को संशोधित करें और फिर से प्रयास करें।</p>
            </div>
        </div>
    </div>

    <!-- Empty state -->
    <?php if (empty($all_members) && empty($team_text_content)): ?>
    <div class="col-12 text-center text-muted mt-5">
        <div class="card">
            <div class="card-body">
                <h4><i class="fas fa-users"></i> अभी तक कोई टीम सदस्य नहीं जोड़े गए हैं।</h4>
                <p>जल्द ही हमारी टीम की जानकारी यहाँ उपलब्ध होगी।</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

</main>

<style>
.team-text-content {
    transition: all 0.3s ease;
}

.team-text-content:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
}

.text-content {
    line-height: 1.6;
    font-size: 1rem;
    color: #333;
}

.text-content p {
    margin-bottom: 1rem;
}

.text-content:last-child {
    margin-bottom: 0;
}

@media (max-width: 768px) {
    .text-content {
        font-size: 0.9rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('member-search');
    const featuredMembers = document.querySelectorAll('.featured-member');
    const memberItems = document.querySelectorAll('.member-item');
    const teamTextContent = document.querySelectorAll('.team-text-content');
    const noResultsDiv = document.getElementById('no-results');

    searchInput.addEventListener('input', function() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        let visibleCount = 0;

        // Search in featured members
        featuredMembers.forEach(item => {
            const name = item.getAttribute('data-name');
            const designation = item.getAttribute('data-designation');

            if (name.includes(searchTerm) || designation.includes(searchTerm)) {
                item.style.display = 'block';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        // Search in member list
        memberItems.forEach(item => {
            const name = item.getAttribute('data-name');
            const designation = item.getAttribute('data-designation');

            if (name.includes(searchTerm) || designation.includes(searchTerm)) {
                item.style.display = 'block';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        // Search in text content
        teamTextContent.forEach(item => {
            const textContent = item.querySelector('.text-content').textContent.toLowerCase();

            if (textContent.includes(searchTerm)) {
                item.style.display = 'block';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        // Show/hide no results message
        if (visibleCount === 0 && searchTerm !== '') {
            noResultsDiv.classList.remove('d-none');
        } else {
            noResultsDiv.classList.add('d-none');
        }
    });
});
</script>

<?php include 'footer.php'; ?>