<?php
require_once 'config/config.php';

// Database connection (kept for consistency with other pages)
$db = getDbConnection();

// Static list of awards
$awards_list = array(
    "Ron Man of India Award", "Bharat Iron Lady of India Award", "Lifetime Achievement Award", "MBR APJ Kalam Award",
    "Business Icon Award", "National Education Icon Award", "Bharat Excellence Leadership Award", "Bharat Nari Shakti Award",
    "International Seva Ratan Award", "National Icon Award", "National Shri Maharana Partap Award", "Bharat MBR Dr Bhim Rao Ambedkar Award",
    "Bharat MBR Subhash Chandra Bose Award", "National MBR Bhamashah Award", "Indian Kala Bhaskar Award", "National Kala Ratan Award",
    "National Excellence Leadership Award", "Bharat Excellence Business Award", "International Excellence in Education Award",
    "Bharat Best/Young/Social Entrepreneur Award", "Indian Woman Power Award", "Bharat Rani Laxmi Bai Award",
    "National Best Business Man/Business Women Award", "Bharat Woman Entrepreneur Award", "International Extraordinary Author/Writer/Kid Award",
    "National Best Innovative Leadership Award", "National Youth Icon Award", "National Global Excellence Award",
    "International Global Artist Award", "Bharat Global Socialist Award", "Indian Global Nobel Award", "National Global Educator Award",
    "International Global Bravery Award", "Bharat Global Iron Lady Award", "Indian Global Iron Man Award", "National Global Business Award",
    "International Global Genius Award", "Bharat Global Fashion Icon Award", "Indian Global Beauty Pageant/Queen Award",
    "National Global Youth Icon Award", "International Global Human Rights Activist Award", "Bharat Global Miss Pageant Award",
    "Indian Global Kids Icon Award", "National Global Power of Women Award", "International Global Female Anchor/Model/Artist/Actor Award",
    "Bharat Global Business Women Award", "National Global Author/Writer Award", "International Global Glamour Award",
    "Bharat Golden Globe Award", "Indian Global Brilliant Kid Award", "National Global Yoga Award", "International Global Singer Award",
    "Bharat Global Dancer Award", "National Best Achiever Award", "International Young Achiever Award", "Bharat India Bravery Award",
    "Indian The Phoenix Award", "National The Shining Star Award", "International Man/Woman of the Year Award", "Bharat Best Glamour Award",
    "Indian Best Glamour Women Award", "National Best Inspiration Award", "International Multi-Talented Award", "Bharat The Diamond Award",
    "Indian Achievers Excellence Award", "National Most Desirable Award", "International Best Leadership Award", "Bharat The Moral Hero Award",
    "Indian Outstanding Contribution to Society Award", "National Best Social Activist Award", "International Best Volunteer Award",
    "Bharat Frontline Medical Hero Award", "Indian Backline Medical Hero Award", "National Education Hero Award",
    "International Delivery Hero Award", "Bharat Public Service Hero Award", "Indian Covid Warrior Award", "National Best Artist Award",
    "International Best Dancer Award", "Bharat Best Singer Award", "Indian Best Playback Singer Award",
    "National Best Rock Vocal Performance Award", "International Best Pop Artist Award", "Bharat Best Vocalist Award",
    "Indian Best Choreographer Award", "National Best Journalist Award", "International Best Photo Journalist Award",
    "Bharat Media Hero Award", "Indian Digital Man/Woman Award", "National The Braniac Award",
    "International The Eccentric Performer Award", "Bharat Little Master Award", "Indian Young Professional Award",
    "National Best Reciter Award", "International Spontaneous Reciter Award", "Bharat Most Eminent Senior Citizen Award",
    "Indian Best Astrologer Award", "National Best Writer Award", "International Best Philanthropist Award",
    "Bharat Emerging Scientist Award", "Indian Innovative Leadership Award", "National Best Achievers' Award",
    "International Honorary Doctor Award", "Bharat Global Award", "Indian Unique Award", "National Woman Power Award",
    "International Best Female Anchors/Model/Artist/Film/Industry Award", "Bharat Beauty Queen Award", "Indian Diamond Queen Award",
    "National Woman Entrepreneur Award", "National Medal of Distinction Award", "International Most Inspiring Teacher Award",
    "Bharat Teaching Excellence Award", "Indian Dynamic Teacher Award", "National Best Teacher Award",
    "International Innovation in Education Award", "Bharat Best Faculty Award", "Indian Best Researcher Award",
    "National Young Engineer of the Year Award", "International Special Recognition Award", "National Creative Business Award",
    "International Dynamic Business Award", "Bharat Best Technology Award", "Indian Fastest Growing Small Business Award",
    "National Innovative Business Award", "International Best Entrepreneur Award", "Bharat Emerging Entrepreneur Award",
    "Indian Excellence Digital Marketing Award", "National Marketing Excellence Award", "International Best E-commerce Business Award",
    "National Super Grasping & Memory Power Kid Award", "International Incredible Memory Power Award", "Bharat Brilliant Kid Award",
    "Indian An Artistic Girl Award – Drawing, Painting, Acting & Animation", "National Youngest Master of G.K. Award",
    "International Multi-Talented Award", "Bharat Elastic Girl Award", "Indian Best Story Teller Award",
    "National Super Talented Toddler Award", "International Amazing Kid Award"
);

/**
 * Splits the award name into a prefix (first word, e.g., "National") and the main title (rest of the name).
 * This helps create the two-line visual effect.
 * @param string $awardName The full name of the award.
 * @return array An array with 'prefix' and 'title'.
 */
function splitAwardName($awardName) {
    $parts = explode(' ', $awardName, 2);
    if (count($parts) > 1) {
        // First word is the prefix, rest is the main title
        return ['prefix' => $parts[0], 'title' => $parts[1]];
    }
    return ['prefix' => '', 'title' => $awardName]; // Fallback
}

// Determine the midpoint to split the list (140 awards total / 2 = 70)
$midpoint = ceil(count($awards_list) / 2);

$column1_awards = array_slice($awards_list, 0, $midpoint);
$column2_awards = array_slice($awards_list, $midpoint);
$initial_index_col2 = $midpoint; // Used to continue the numbering in the second column

include 'header.php';
include 'navbar.php';
?>

<style>
/* Enhanced CSS for a beautiful, two-line award display */
.awards-list-group {
    list-style: none;
    padding: 0;
    margin-top: 30px;
}

.award-item {
    /* Modern card look with subtle gradient and shadow */
    background: linear-gradient(135deg, #ffffff 0%, #fcfcfc 100%);
    border: none;
    margin-bottom: 15px;
    padding: 20px 25px;
    border-radius: 12px;
    display: flex;
    align-items: flex-start; 
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08); /* Light shadow */
    border-left: 5px solid var(--bs-primary); /* Accent border */
}

.award-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 18px rgba(0, 0, 0, 0.15); /* Lift on hover */
    border-left-color: #0d6efd; /* Darken accent color on hover */
}

.award-item .award-number {
    font-weight: 700;
    font-size: 1.5rem;
    color: #343a40; /* Darker text for number */
    margin-right: 20px;
    width: 45px;
    text-align: center;
    line-height: 1.2;
    padding-top: 2px;
}

.award-item .award-content {
    flex-grow: 1;
    display: flex;
    flex-direction: column; /* This stacks the prefix and title visually */
    line-height: 1.2;
}

.award-item .award-prefix {
    font-size: 0.9rem;
    font-weight: 500;
    color: var(--bs-primary); /* Primary color for the scope/prefix */
    text-transform: uppercase;
    letter-spacing: 1px;
    /* Increased bottom margin for better separation */
    margin-bottom: 5px; 
    display: block; /* Ensures it takes up its own line */
}

.award-item .award-title {
    font-size: 1.15rem;
    font-weight: 600;
    color: #212529; /* Main title in deep dark color */
    word-wrap: break-word; /* Ensure long names break correctly */
}
</style>

<main class="container my-5">
    <h3 class="section-heading text-center"><span>List of Awards & Recognitions</span></h3>

    <div class="row justify-content-center">
        <!-- Using col-lg-6 for two columns on large screens, col-md-10 for single column on smaller screens -->
        <div class="col-lg-12">
            <div class="row">
                
                <!-- COLUMN 1 -->
                <div class="col-lg-6 mb-4">
                    <h5 class="text-center text-primary mb-3">Awards (1 - <?php echo count($column1_awards); ?>)</h5>
                    <ul class="awards-list-group">
                        <?php foreach ($column1_awards as $index => $award): 
                            $split = splitAwardName($award); // Split the award name
                        ?>
                            <li class="award-item" data-aos="fade-right" data-aos-delay="<?php echo $index * 30; ?>">
                                <div class="award-number"><?php echo $index + 1; ?>.</div> 
                                <div class="award-content">
                                    <!-- Line 1: The smaller prefix/scope -->
                                    <span class="award-prefix">
                                        <?php echo htmlspecialchars($split['prefix']); ?>
                                    </span>
                                    <!-- Line 2: The main award title -->
                                    <div class="award-title">
                                        <?php echo htmlspecialchars($split['title']); ?>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <!-- COLUMN 2 -->
                <div class="col-lg-6 mb-4">
                    <h5 class="text-center text-primary mb-3">Awards (<?php echo $initial_index_col2 + 1; ?> - <?php echo count($awards_list); ?>)</h5>
                    <ul class="awards-list-group">
                        <?php foreach ($column2_awards as $index => $award): 
                            $split = splitAwardName($award); // Split the award name
                            // Calculate the correct sequential number for the second column
                            $sequential_number = $initial_index_col2 + $index + 1;
                        ?>
                            <li class="award-item" data-aos="fade-left" data-aos-delay="<?php echo $index * 30; ?>">
                                <div class="award-number"><?php echo $sequential_number; ?>.</div> 
                                <div class="award-content">
                                    <!-- Line 1: The smaller prefix/scope -->
                                    <span class="award-prefix">
                                        <?php echo htmlspecialchars($split['prefix']); ?>
                                    </span>
                                    <!-- Line 2: The main award title -->
                                    <div class="award-title">
                                        <?php echo htmlspecialchars($split['title']); ?>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

            </div>
        </div>

        <?php if (empty($awards_list)): ?>
            <div class="col-12">
                <div class="text-center text-muted py-5">
                    <i class="fas fa-trophy mb-3" style="font-size: 3rem;"></i>
                    <p class="h5">The list of awards is currently empty.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'footer.php'; ?>