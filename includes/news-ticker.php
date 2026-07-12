<?php if (!empty($news_ticker)): ?>
<div class="news-ticker-container">
    <div class="news-ticker-wrapper">
        <div class="ticker-label">
            <i class="fas fa-newspaper"></i>
            <span>Latest News</span>
        </div>
        <div class="ticker-content">
            <div class="ticker-scroll" id="newsTicker">
                <?php foreach ($news_ticker as $ticker_item): ?>
                    <div class="ticker-item">
                        <a href="news-details.php?id=<?php echo $ticker_item['id']; ?>" class="ticker-link">
                            <i class="fas fa-circle ticker-bullet"></i>
                            <?php echo $ticker_item['title']; ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="ticker-controls">
            <button class="ticker-control" id="tickerPause" title="Pause">
                <i class="fas fa-pause"></i>
            </button>
            <button class="ticker-control" id="tickerPlay" title="Play" style="display: none;">
                <i class="fas fa-play"></i>
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
/* NEWS TICKER STYLES - OPTIMIZED FOR MOBILE SPEED */
.news-ticker-container {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    border-bottom: 3px solid var(--primary-color);
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
    position: relative;
}

.news-ticker-wrapper {
    display: flex;
    align-items: center;
    min-height: 50px;
    position: relative;
}

.ticker-label {
    background: var(--primary-color);
    color: white;
    padding: 12px 20px;
    font-weight: bold;
    font-size: 14px;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 140px;
    justify-content: center;
    box-shadow: 2px 0 5px rgba(0,0,0,0.2);
    position: relative;
    z-index: 2;
}

.ticker-label i {
    font-size: 16px;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.ticker-content {
    flex: 1;
    overflow: hidden;
    height: 50px;
    position: relative;
    background: rgba(255,255,255,0.95);
}

/* OPTIMIZED TICKER ANIMATION - BALANCED SPEED */
.ticker-scroll {
    display: flex;
    align-items: center;
    height: 100%;
    animation: tickerMove 30s linear infinite; /* Balanced base speed */
    white-space: nowrap;
}

@keyframes tickerMove {
    0% { transform: translateX(100%); }
    100% { transform: translateX(-100%); }
}

.ticker-item {
    display: inline-flex;
    align-items: center;
    padding-right: 50px;
    height: 100%;
}

.ticker-link {
    color: var(--text-dark);
    text-decoration: none;
    font-weight: 500;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: color 0.3s ease;
    white-space: nowrap;
}

.ticker-link:hover {
    color: var(--primary-color);
    text-decoration: none;
}

.ticker-bullet {
    color: var(--primary-color);
    font-size: 8px;
    animation: blink 2s infinite;
}

@keyframes blink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.ticker-controls {
    padding: 0 15px;
    display: flex;
    gap: 5px;
}

.ticker-control {
    background: rgba(255,255,255,0.9);
    border: none;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    color: var(--primary-color);
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.ticker-control:hover {
    background: white;
    transform: scale(1.1);
    color: var(--primary-dark);
}

.ticker-control i {
    font-size: 12px;
}

/* BALANCED MOBILE TICKER SPEEDS */
@media (max-width: 1199.98px) {
    .ticker-scroll {
        animation-duration: 25s; /* Slightly faster on large tablets */
    }
    
    .ticker-label {
        min-width: 120px;
        padding: 10px 15px;
        font-size: 13px;
    }
}

@media (max-width: 991.98px) {
    .ticker-scroll {
        animation-duration: 22s; /* Moderately faster on tablets */
    }
    
    .ticker-label {
        min-width: 100px;
        padding: 8px 12px;
        font-size: 12px;
    }
}

/* BALANCED FOR MOBILE - REASONABLE TICKER SPEED */
@media (max-width: 767.98px) {
    .ticker-scroll {
        animation-duration: 18s !important; /* Reasonably fast on mobile */
    }
    
    .news-ticker-wrapper {
        min-height: 45px;
    }
    
    .ticker-content {
        height: 45px;
    }
    
    .ticker-label {
        min-width: 90px;
        padding: 8px 10px;
        font-size: 11px;
    }
    
    .ticker-link {
        font-size: 13px;
    }
    
    .ticker-controls {
        padding: 0 10px;
    }
    
    .ticker-control {
        width: 30px;
        height: 30px;
    }
}

/* SMALL MOBILE - BALANCED SPEED */
@media (max-width: 575.98px) {
    .ticker-scroll {
        animation-duration: 15s !important; /* Fast but readable on small mobile */
    }
    
    .news-ticker-wrapper {
        min-height: 40px;
    }
    
    .ticker-content {
        height: 40px;
    }
    
    .ticker-label {
        min-width: 80px;
        padding: 6px 8px;
        font-size: 10px;
        gap: 4px;
    }
    
    .ticker-link {
        font-size: 12px;
    }
    
    .ticker-controls {
        padding: 0 8px;
    }
    
    .ticker-control {
        width: 28px;
        height: 28px;
    }
    
    .ticker-control i {
        font-size: 10px;
    }
}

/* PAUSE/PLAY TICKER STATES */
.ticker-scroll.paused {
    animation-play-state: paused;
}

.ticker-scroll.playing {
    animation-play-state: running;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // MOBILE OPTIMIZED NEWS TICKER
    const ticker = document.getElementById('newsTicker');
    const pauseBtn = document.getElementById('tickerPause');
    const playBtn = document.getElementById('tickerPlay');
    
    // Function to set mobile-optimized ticker speed
    function setMobileTickerSpeed() {
        if (!ticker) return;
        
        const screenWidth = window.innerWidth;
        let duration;
        
        // Set different speeds based on screen size (BALANCED)
        if (screenWidth <= 575) {
            duration = '15s'; // Fast but readable for small mobile
        } else if (screenWidth <= 767) {
            duration = '18s'; // Reasonably fast for mobile
        } else if (screenWidth <= 991) {
            duration = '22s'; // Moderate for tablet
        } else if (screenWidth <= 1199) {
            duration = '25s'; // Slightly faster for large tablet
        } else {
            duration = '30s'; // Normal for desktop
        }
        
        ticker.style.animationDuration = duration;
    }
    
    // Set initial speed
    setMobileTickerSpeed();
    
    if (ticker && pauseBtn && playBtn) {
        pauseBtn.addEventListener('click', function() {
            ticker.classList.add('paused');
            ticker.classList.remove('playing');
            pauseBtn.style.display = 'none';
            playBtn.style.display = 'flex';
        });
        
        playBtn.addEventListener('click', function() {
            ticker.classList.remove('paused');
            ticker.classList.add('playing');
            playBtn.style.display = 'none';
            pauseBtn.style.display = 'flex';
        });
        
        // Auto-pause on hover
        const tickerContainer = document.querySelector('.news-ticker-container');
        if (tickerContainer) {
            tickerContainer.addEventListener('mouseenter', function() {
                ticker.style.animationPlayState = 'paused';
            });
            
            tickerContainer.addEventListener('mouseleave', function() {
                if (!ticker.classList.contains('paused')) {
                    ticker.style.animationPlayState = 'running';
                }
            });
        }
    }
    
    // MOBILE OPTIMIZED: Dynamic ticker speed calculation based on content and screen size
    function calculateOptimalTickerSpeed() {
        const tickerItems = document.querySelectorAll('.ticker-item');
        if (tickerItems.length > 0 && ticker) {
            const totalWidth = Array.from(tickerItems).reduce((total, item) => {
                return total + item.offsetWidth;
            }, 0);
            
            const screenWidth = window.innerWidth;
            let baseDuration;
            
            // Mobile-first approach with balanced speeds
            if (screenWidth <= 575) {
                baseDuration = 12; // Fast but readable for small mobile
            } else if (screenWidth <= 767) {
                baseDuration = 18; // Reasonably fast for mobile
            } else if (screenWidth <= 991) {
                baseDuration = 22; // Moderate for tablet
            } else if (screenWidth <= 1199) {
                baseDuration = 25; // Slightly faster for large tablet
            } else {
                baseDuration = 30; // Normal for desktop
            }
            
            // Adjust based on content length (more content = slightly slower)
            const contentMultiplier = Math.max(0.9, Math.min(1.3, totalWidth / 3000));
            const adjustedDuration = Math.max(12, baseDuration * contentMultiplier);
            
            ticker.style.animationDuration = adjustedDuration + 's';
        }
    }
    
    // Calculate initial speed
    calculateOptimalTickerSpeed();
    
    // Recalculate on window resize with optimized debouncing
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            calculateOptimalTickerSpeed();
        }, 100); // Faster debounce for mobile responsiveness
    });
});

// BALANCED PERFORMANCE OPTIMIZATION
// Slightly reduce animations on low-end devices
if (navigator.hardwareConcurrency && navigator.hardwareConcurrency < 4) {
    const style = document.createElement('style');
    style.textContent = `
        .ticker-scroll {
            animation-duration: 12s !important; /* Fast but readable for low-end devices */
        }
    `;
    document.head.appendChild(style);
}

// Detect slow connection and optimize ticker moderately
if ('connection' in navigator) {
    const connection = navigator.connection;
    if (connection.effectiveType === '2g' || connection.effectiveType === 'slow-2g') {
        const style = document.createElement('style');
        style.textContent = `
            .ticker-scroll {
                animation-duration: 10s !important; /* Fast for slow connections but still readable */
            }
        `;
        document.head.appendChild(style);
    }
}

// Touch device optimization for mobile ticker
if ('ontouchstart' in window || navigator.maxTouchPoints > 0) {
    document.addEventListener('DOMContentLoaded', function() {
        const tickerContainer = document.querySelector('.news-ticker-container');
        const ticker = document.getElementById('newsTicker');
        
        if (tickerContainer && ticker) {
            // Pause ticker on touch
            tickerContainer.addEventListener('touchstart', function() {
                ticker.style.animationPlayState = 'paused';
            });
            
            // Resume ticker after touch ends
            tickerContainer.addEventListener('touchend', function() {
                setTimeout(function() {
                    if (!ticker.classList.contains('paused')) {
                        ticker.style.animationPlayState = 'running';
                    }
                }, 1000); // Resume after 1 second
            });
        }
    });
}
</script>