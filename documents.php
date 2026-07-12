<?php
session_start();
require_once 'config/config.php';

// Get database connection
$db = getDbConnection();

// Get documents
$stmt = $db->prepare("SELECT * FROM documents WHERE status = 'active' ORDER BY created_at DESC");
$stmt->execute();
$documents = $stmt->fetchAll();

include 'header.php';
include 'navbar.php';
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf_viewer.min.css">

<div class="container-fluid navbar-margin-pusher">
    <div class="container my-5">
        <div class="row">
            <div class="col-12">
                <div class="section-heading">
                    <span>महत्वपूर्ण दस्तावेज़</span>
                </div>
            </div>
        </div>

        <div class="documents-content">
            <?php if (!empty($documents)): ?>
                <!-- Single Column Layout -->
                <div class="row justify-content-center">
                    <div class="col-12 col-lg-10 col-xl-8">
                        <?php foreach ($documents as $document): ?>
                            <div class="document-card mb-4">
                                <div class="document-header">
                                    <h5>
                                        <i class="fas fa-file-pdf text-danger"></i>
                                        <?php echo htmlspecialchars($document['title']); ?>
                                    </h5>
                                </div>
                                
                                <!-- PDF Viewer Container - Always Visible -->
                                <div class="pdf-viewer-container" id="container-<?php echo $document['id']; ?>">
                                    <div class="pdf-controls">
                                        <button class="btn btn-sm btn-secondary prev-page" onclick="changePage(<?php echo $document['id']; ?>, -1)" disabled>
                                            <i class="fas fa-chevron-left"></i> पिछला
                                        </button>
                                        <span class="page-info">
                                            पृष्ठ <span class="current-page">1</span> / <span class="total-pages">1</span>
                                        </span>
                                        <button class="btn btn-sm btn-secondary next-page" onclick="changePage(<?php echo $document['id']; ?>, 1)" disabled>
                                            अगला <i class="fas fa-chevron-right"></i>
                                        </button>
                                    </div>
                                    <div class="pdf-canvas-container">
                                        <canvas class="pdf-canvas" id="canvas-<?php echo $document['id']; ?>"></canvas>
                                    </div>
                                    <div class="loading-indicator" id="loading-<?php echo $document['id']; ?>">
                                        <i class="fas fa-spinner fa-spin"></i> PDF लोड हो रहा है...
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle fa-2x mb-3"></i>
                    <h5>कोई दस्तावेज़ उपलब्ध नहीं</h5>
                    <p>इस समय कोई दस्तावेज़ उपलब्ध नहीं हैं।</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.documents-content {
    padding: 2rem 0;
}

.document-card {
    background: var(--white-bg);
    border-radius: 15px;
    box-shadow: 0 8px 24px var(--shadow-medium);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    overflow: hidden;
    margin-bottom: 2rem;
}

.document-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 32px var(--shadow-dark);
}

.document-header {
    padding: 2rem;
    border-bottom: 2px solid #f0f0f0;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
}

.document-header h5 {
    margin: 0;
    font-family: 'Bakbak One', sans-serif;
    color: var(--primary-color);
    font-size: 1.3rem;
    text-align: center;
}

.pdf-viewer-container {
    padding: 2rem;
    background: #f8f9fa;
    /* Always visible - no display: none */
}

.pdf-controls {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
    padding: 1rem;
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.pdf-controls .btn {
    font-weight: 600;
    padding: 0.6rem 1rem;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.pdf-controls .btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.page-info {
    font-weight: 600;
    color: var(--text-color);
    font-size: 1.1rem;
    padding: 0.5rem 1rem;
    background: #e9ecef;
    border-radius: 8px;
}

.pdf-canvas-container {
    text-align: center;
    background: white;
    border-radius: 10px;
    padding: 2rem;
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    min-height: 500px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.pdf-canvas {
    max-width: 100%;
    max-height: 600px;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    transition: transform 0.3s ease;
}

.pdf-canvas:hover {
    transform: scale(1.02);
}

.loading-indicator {
    text-align: center;
    padding: 3rem;
    color: var(--text-muted);
    font-size: 1.2rem;
    font-weight: 500;
}

/* Responsive Design */
@media (max-width: 768px) {
    .document-header {
        padding: 1.5rem;
    }
    
    .document-header h5 {
        font-size: 1.1rem;
    }
    
    .pdf-controls {
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .pdf-canvas {
        max-height: 400px;
    }
    
    .pdf-viewer-container {
        padding: 1.5rem;
    }
}

@media (max-width: 576px) {
    .documents-content {
        padding: 1rem 0;
    }
    
    .document-header {
        padding: 1rem;
    }
    
    .document-header h5 {
        font-size: 1rem;
    }
    
    .pdf-controls {
        padding: 0.8rem;
        gap: 0.8rem;
    }
    
    .pdf-controls .btn {
        font-size: 0.8rem;
        padding: 0.4rem 0.8rem;
    }
    
    .page-info {
        font-size: 0.9rem;
        padding: 0.4rem 0.8rem;
    }
    
    .pdf-canvas {
        max-height: 300px;
    }
    
    .pdf-canvas-container {
        padding: 1rem;
        min-height: 350px;
    }
}
</style>

<script>
// Initialize PDF.js worker
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

// Track loaded PDFs
const loadedPDFs = {};

// Auto-load all PDFs when page loads
document.addEventListener('DOMContentLoaded', function() {
    <?php foreach ($documents as $document): ?>
        loadPDF('<?php echo SITE_URL . '/' . htmlspecialchars($document['file_path']); ?>', <?php echo $document['id']; ?>);
    <?php endforeach; ?>
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });
});

// Function to change page
function changePage(docId, direction) {
    if (loadedPDFs[docId]) {
        const pdfData = loadedPDFs[docId];
        const newPage = pdfData.currentPage + direction;
        
        if (newPage >= 1 && newPage <= pdfData.totalPages) {
            renderPage(docId, newPage);
        }
    }
}

// Function to load and render PDF
async function loadPDF(url, docId) {
    const canvasElem = document.getElementById(`canvas-${docId}`);
    const containerElem = document.getElementById(`container-${docId}`);
    const prevButton = containerElem.querySelector('.prev-page');
    const nextButton = containerElem.querySelector('.next-page');
    const currentPageElem = containerElem.querySelector('.current-page');
    const totalPagesElem = containerElem.querySelector('.total-pages');
    const loadingElem = document.getElementById(`loading-${docId}`);
    
    try {
        loadingElem.style.display = 'block';
        canvasElem.style.display = 'none';
        
        const loadingTask = pdfjsLib.getDocument(url);
        const pdf = await loadingTask.promise;
        
        const currentPage = 1;
        const totalPages = pdf.numPages;
        totalPagesElem.textContent = totalPages;
        
        // Store PDF reference
        loadedPDFs[docId] = { pdf, currentPage, totalPages };
        
        // Initial render
        await renderPage(docId, currentPage);
        
        // Hide loading and show canvas
        loadingElem.style.display = 'none';
        canvasElem.style.display = 'block';
        
        // Enable/disable buttons
        updateButtons(docId);
        
    } catch (error) {
        console.error('Error loading PDF:', error);
        loadingElem.innerHTML = '<i class="fas fa-exclamation-triangle text-danger"></i> PDF लोड करने में त्रुटि। कृपया बाद में पुनः प्रयास करें।';
    }
}

// Function to render a specific page
async function renderPage(docId, pageNumber) {
    const pdfData = loadedPDFs[docId];
    const canvasElem = document.getElementById(`canvas-${docId}`);
    const containerElem = document.getElementById(`container-${docId}`);
    const currentPageElem = containerElem.querySelector('.current-page');
    
    try {
        const page = await pdfData.pdf.getPage(pageNumber);
        const viewport = page.getViewport({ scale: 1.5 });
        
        // Set canvas dimensions
        const context = canvasElem.getContext('2d');
        canvasElem.height = viewport.height;
        canvasElem.width = viewport.width;
        
        // Render PDF page
        const renderContext = {
            canvasContext: context,
            viewport: viewport
        };
        
        await page.render(renderContext).promise;
        
        // Update current page
        pdfData.currentPage = pageNumber;
        currentPageElem.textContent = pageNumber;
        
        // Update buttons
        updateButtons(docId);
        
    } catch (error) {
        console.error('Error rendering page:', error);
    }
}

// Function to update button states
function updateButtons(docId) {
    const pdfData = loadedPDFs[docId];
    const containerElem = document.getElementById(`container-${docId}`);
    const prevButton = containerElem.querySelector('.prev-page');
    const nextButton = containerElem.querySelector('.next-page');
    
    prevButton.disabled = pdfData.currentPage <= 1;
    nextButton.disabled = pdfData.currentPage >= pdfData.totalPages;
}
</script>

<?php include 'footer.php'; ?>