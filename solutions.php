<?php
// Simulate fetching solution data from a database.
$solutions = [
    [
        'image' => 'complain_img/1749222284611426687542565589895_0662025203523.jpg',
        'solved_on' => '2025-06-04',
        'title' => 'Aaa',
        'description' => 'Test'
    ],
    [
        'image' => 'https://via.placeholder.com/800x600/eee/888?text=Solution+Image+2',
        'solved_on' => '2024-05-20',
        'title' => 'Community Water Project',
        'description' => 'Successfully installed a new water purification system for the village, ensuring clean drinking water for over 500 families.'
    ],
    [
        'image' => 'https://via.placeholder.com/800x600/eee/888?text=Solution+Image+3',
        'solved_on' => '2024-04-15',
        'title' => 'Educational Material Distribution',
        'description' => 'Provided essential educational supplies, including books and stationery, to 150 underprivileged students in the region.'
    ]
];
?>

<?php include 'header.php'; ?>
<?php include 'navbar.php'; ?>

<main class="container my-5">
    <h3 class="section-heading text-center"><span>Latest Solutions</span></h3>

    <div class="row g-4">
        <?php foreach ($solutions as $solution): ?>
            <div class="col-lg-4 col-md-6">
                <div class="card solution-card h-100 shadow-sm">
                    <img src="<?php echo strpos($solution['image'], '://') !== false ? $solution['image'] : 'https://sanatandharmajagruti.in/' . $solution['image']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($solution['title']); ?>">
                    <div class="card-body d-flex flex-column">
                        <p class="card-text text-end text-muted small mb-2">
                            SOLVED ON - <?php echo date("F j, Y", strtotime($solution['solved_on'])); ?>
                        </p>
                        <h5 class="card-title fw-bold"><?php echo htmlspecialchars($solution['title']); ?></h5>
                        <p class="card-text flex-grow-1">
                            <?php echo htmlspecialchars($solution['description']); ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<?php include 'footer.php'; ?>