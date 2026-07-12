<?php
session_start();
require_once 'config/config.php';

// Database connection
$db = getDbConnection();

// Fetch active about content
$stmt = $db->prepare("SELECT * FROM about_content WHERE status = 'active' ORDER BY sort_order ASC");
$stmt->execute();
$aboutSections = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Sanitize description to allow safe HTML (assumes CKEditor outputs safe HTML)
foreach ($aboutSections as &$section) {
    $section['description'] = $section['description'];
}

include 'header.php';
include 'navbar.php';
?>

<!-- Full-Width About Us Section -->
<div class="container-fluid navbar-margin-pusher">
    <div class="container my-5">
        <div class="row">
            <div class="col-12">
                <div class="section-heading">
                    <span>About Us</span>
                </div>
            </div>
        </div>

        <!-- About Content Sections -->
        <div class="about-content">
            <?php if (!empty($aboutSections)): ?>
                <?php foreach ($aboutSections as $index => $section): ?>
                    <div class="about-section py-5 <?php echo $index % 2 === 0 ? 'bg-light' : 'bg-white'; ?>">
                        <div class="container">
                            <div class="row align-items-center <?php echo $index % 2 === 1 ? 'flex-row-reverse' : ''; ?>">
                                <?php if (!empty($section['image'])): ?>
                                    <div class="col-lg-6 mb-4 mb-lg-0 animate__animated animate__fadeInLeft">
                                        <img src="img/about/<?php echo htmlspecialchars($section['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($section['title']); ?>" 
                                             class="img-fluid rounded shadow-sm">
                                    </div>
                                <?php else: ?>
                                    <div class="col-lg-6 mb-4 mb-lg-0 animate__animated animate__fadeInLeft">
                                        <div class="image-placeholder bg-secondary text-white d-flex align-items-center justify-content-center rounded shadow-sm" style="height: 300px;">
                                            <i class="fas fa-image fa-3x"></i>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <div class="col-lg-6 animate__animated animate__fadeInRight">
                                    <h3 class="mb-3" style="border-left: 4px solid var(--primary-dark); padding-left: 10px;">
                                        <?php echo htmlspecialchars($section['title']); ?>
                                    </h3>
                                    <div class="content-html"><?php echo $section['description']; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="container py-5 text-center">
                    <p class="lead text-muted">इस समय कोई सामग्री उपलब्ध नहीं है।</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Call-to-Action Cards -->
        <div class="cta-section py-5 bg-white">
            <div class="container">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card-custom text-center animate__animated animate__zoomIn">
                            <i class="fas fa-users fa-3x mb-3" style="color: var(--primary-color);"></i>
                            <h5>सदस्यता</h5>
                            <p class="text-muted">हमारे साथ जुड़कर सामाजिक विकास में योगदान दें।</p>
                            <a href="users-apply-form.php" class="btn btn-primary">सदस्य बनें</a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card-custom text-center animate__animated animate__zoomIn animate__delay-1s">
                            <i class="fas fa-heart fa-3x mb-3" style="color: var(--primary-color);"></i>
                            <h5>दान करें</h5>
                            <p class="text-muted">हमारे कार्यों में आर्थिक सहयोग प्रदान करें।</p>
                            <a href="donation_form.php" class="btn btn-primary">दान करें</a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card-custom text-center animate__animated animate__zoomIn animate__delay-2s">
                            <i class="fas fa-phone fa-3x mb-3" style="color: var(--success-color);"></i>
                            <h5>संपर्क करें</h5>
                            <p class="text-muted">किसी भी सहायता के लिए हमसे संपर्क करें।</p>
                            <a href="contact-us.php" class="btn btn-success">संपर्क करें</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<style>
/* About Us Section */
.about-us-section {
    width: 100%;
    margin: 0;
    padding: 0;
    font-family: 'Teko', sans-serif;
    color: var(--text-color);
}

.about-hero {
    padding: 4rem 0;
}

.about-hero .main-title {
    font-family: 'Bakbak One', sans-serif;
    font-size: 2.8rem;
    color: var(--text-white);
    text-shadow: 2px 2px 4px var(--shadow-medium);
}

.about-hero .subtitle {
    font-size: 1.25rem;
    color: var(--text-white);
    max-width: 800px;
    margin: 0 auto;
}

.about-section {
    padding: 4rem 0;
}

.about-section.bg-light {
    background-color: var(--light-bg);
}

.about-section.bg-white {
    background-color: var(--white-bg);
}

.about-section h3 {
    font-family: 'Bakbak One', sans-serif;
    font-size: 1.75rem;
    color: var(--primary-color);
}

.about-section .content-html {
    line-height: 1.8;
    font-size: 1.1rem;
    color: var(--text-color);
}

.about-section .content-html p {
    margin-bottom: 1rem;
}

.about-section .content-html ul,
.about-section .content-html ol {
    margin-left: 1.5rem;
    margin-bottom: 1rem;
}

.about-section .content-html li {
    margin-bottom: 0.5rem;
}

.about-section img,
.about-section .image-placeholder {
    max-height: 300px;
    width: 100%;
    object-fit: cover;
    border-radius: 8px;
    box-shadow: 0 2px 10px var(--shadow-light);
}

.card-custom {
    padding: 2rem;
    border: none;
    border-radius: 10px;
    background: var(--white-bg);
    box-shadow: 0 4px 12px var(--shadow-medium);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card-custom:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 20px var(--shadow-dark);
}

.card-custom h5 {
    font-family: 'Bakbak One', sans-serif;
    font-size: 1.3rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.card-custom p {
    color: var(--text-muted);
    margin-bottom: 1.5rem;
}

.card-custom .btn {
    font-family: 'Teko', sans-serif;
    font-weight: 500;
    padding: 0.75rem 1.5rem;
    border-radius: 5px;
    background: var(--gradient-primary);
    border: none;
    color: var(--text-white);
}

.card-custom .btn:hover {
    background: var(--gradient-primary-reverse);
    transform: translateY(-2px);
}

/* Floating Buttons (already defined in style.css, included here for completeness) */
.floating-buttons {
    position: fixed;
    bottom: 20px;
    left: 20px;
    z-index: 1050;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.floating-btn-problem {
    background: var(--gradient-primary);
    color: var(--text-white);
    border-radius: 25px;
    padding: 12px 20px;
    font-weight: bold;
    box-shadow: 0 4px 15px var(--shadow-dark);
    transition: all 0.3s ease;
}

.floating-btn-problem:hover {
    background: var(--gradient-primary-reverse);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px var(--shadow-darker);
}

.floating-btn-whatsapp {
    background-color: var(--success-color);
    color: var(--text-white);
    width: 55px;
    height: 55px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    box-shadow: 0 4px 15px var(--shadow-dark);
    transition: all 0.3s ease;
}

.floating-btn-whatsapp:hover {
    background-color: var(--success-dark);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px var(--shadow-darker);
}

.floating-btn-donate {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1050;
    background: var(--gradient-primary);
    color: var(--text-white);
    border-radius: 25px;
    padding: 12px 20px;
    font-weight: bold;
    box-shadow: 0 4px 15px var(--shadow-dark);
    transition: all 0.3s ease;
}

.floating-btn-donate:hover {
    background: var(--gradient-primary-reverse);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px var(--shadow-darker);
}

/* Responsive Adjustments */
@media (max-width: 991.98px) {
    .about-hero {
        padding: 3rem 0;
    }

    .about-hero .main-title {
        font-size: 2.2rem;
    }

    .about-hero .subtitle {
        font-size: 1.1rem;
    }

    .about-section {
        padding: 3rem 0;
    }

    .about-section img,
    .about-section .image-placeholder {
        max-height: 250px;
    }
}

@media (max-width: 767.98px) {
    .about-section {
        padding: 2rem 0;
    }

    .about-section .row {
        flex-direction: column !important;
    }

    .about-section img,
    .about-section .image-placeholder {
        max-height: 200px;
        margin-bottom: 1.5rem;
    }

    .about-hero {
        padding: 2rem 0;
    }

    .about-hero .main-title {
        font-size: 1.8rem;
    }

    .about-hero .subtitle {
        font-size: 1rem;
    }

    .card-custom {
        padding: 1.5rem;
    }

    .card-custom .btn {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }
}

@media (max-width: 575.98px) {
    .about-section h3 {
        font-size: 1.5rem;
    }

    .about-section .content-html {
        font-size: 1rem;
    }

    .card-custom {
        padding: 1rem;
    }

    .card-custom h5 {
        font-size: 1.1rem;
    }
}
</style>



<?php include 'footer.php'; ?>