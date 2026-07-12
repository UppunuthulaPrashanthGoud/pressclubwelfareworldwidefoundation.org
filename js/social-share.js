/**
 * Enhanced Social Sharing Utility
 * Handles sharing across multiple platforms with image previews
 */

class SocialShareManager {
    constructor() {
        this.init();
    }

    init() {
        // Detect user agent for platform-specific optimizations
        this.userAgent = navigator.userAgent.toLowerCase();
        this.isMobile = /android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i.test(this.userAgent);
        this.isIOS = /ipad|iphone|ipod/.test(this.userAgent);
        this.isAndroid = /android/.test(this.userAgent);
        
        // Preload share icons
        this.preloadIcons();
        
        // Add event listeners
        this.bindEvents();
    }

    preloadIcons() {
        const icons = [
            'fa-facebook-f',
            'fa-twitter', 
            'fa-whatsapp',
            'fa-linkedin',
            'fa-telegram',
            'fa-share',
            'fa-link'
        ];
        
        // Preload FontAwesome icons
        icons.forEach(icon => {
            const link = document.createElement('link');
            link.rel = 'preload';
            link.as = 'font';
            link.href = `https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/webfonts/fa-brands-400.woff2`;
            link.type = 'font/woff2';
            link.crossOrigin = 'anonymous';
            document.head.appendChild(link);
        });
    }

    bindEvents() {
        // Add click tracking for analytics
        document.addEventListener('click', (e) => {
            if (e.target.closest('[data-share]')) {
                const platform = e.target.closest('[data-share]').dataset.share;
                this.trackShare(platform);
            }
        });
    }

    trackShare(platform) {
        // Analytics tracking (integrate with your analytics service)
        if (typeof gtag !== 'undefined') {
            gtag('event', 'share', {
                method: platform,
                content_type: 'news_article'
            });
        }
    }

    // Enhanced Facebook sharing with debugging
    shareOnFacebook(url, title, description, image) {
        const shareUrl = 'https://www.facebook.com/sharer/sharer.php?' + new URLSearchParams({
            u: url,
            quote: `${title} - ${description}`
        });

        // Debug Facebook sharing
        if (this.isDebugMode()) {
            console.log('Facebook Share Debug:', {
                url: url,
                title: title,
                description: description,
                image: image,
                shareUrl: shareUrl
            });
        }

        this.openPopup(shareUrl, 'facebook', 580, 296);
        return false;
    }

    // Enhanced Twitter sharing
    shareOnTwitter(url, title, description, hashtags = []) {
        const text = `${title}\n\n${description}`;
        const shareUrl = 'https://twitter.com/intent/tweet?' + new URLSearchParams({
            text: text,
            url: url,
            hashtags: hashtags.join(',')
        });

        this.openPopup(shareUrl, 'twitter', 550, 235);
        return false;
    }

    // Enhanced WhatsApp sharing
    shareOnWhatsApp(url, title, description) {
        const text = `*${title}*\n\n${description}\n\n${url}`;
        
        let shareUrl;
        if (this.isMobile) {
            shareUrl = `whatsapp://send?text=${encodeURIComponent(text)}`;
        } else {
            shareUrl = `https://web.whatsapp.com/send?text=${encodeURIComponent(text)}`;
        }

        if (this.isMobile) {
            window.location.href = shareUrl;
        } else {
            this.openPopup(shareUrl, 'whatsapp', 600, 600);
        }
        return false;
    }

    // LinkedIn sharing
    shareOnLinkedIn(url, title, description) {
        const shareUrl = 'https://www.linkedin.com/sharing/share-offsite/?' + new URLSearchParams({
            url: url
        });

        this.openPopup(shareUrl, 'linkedin', 520, 570);
        return false;
    }

    // Telegram sharing
    shareOnTelegram(url, title, description) {
        const text = `${title}\n\n${description}`;
        const shareUrl = 'https://t.me/share/url?' + new URLSearchParams({
            url: url,
            text: text
        });

        this.openPopup(shareUrl, 'telegram', 600, 400);
        return false;
    }

    // Email sharing
    shareViaEmail(url, title, description) {
        const subject = encodeURIComponent(title);
        const body = encodeURIComponent(`${description}\n\n${url}`);
        const mailtoUrl = `mailto:?subject=${subject}&body=${body}`;
        
        window.location.href = mailtoUrl;
        return false;
    }

    // Native sharing (for mobile devices)
    async shareNative(url, title, description, image) {
        if (!navigator.share) {
            throw new Error('Native sharing not supported');
        }

        try {
            await navigator.share({
                title: title,
                text: description,
                url: url
            });
            return true;
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error('Error sharing:', error);
            }
            return false;
        }
    }

    // Copy to clipboard
    async copyToClipboard(url, showFeedback = true) {
        try {
            await navigator.clipboard.writeText(url);
            if (showFeedback) {
                this.showFeedback('Link copied to clipboard!', 'success');
            }
            return true;
        } catch (error) {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = url;
            textArea.style.position = 'fixed';
            textArea.style.top = '-999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                const successful = document.execCommand('copy');
                if (successful && showFeedback) {
                    this.showFeedback('Link copied to clipboard!', 'success');
                }
                return successful;
            } catch (err) {
                console.error('Fallback copy failed:', err);
                if (showFeedback) {
                    this.showFeedback('Failed to copy link', 'error');
                }
                return false;
            } finally {
                document.body.removeChild(textArea);
            }
        }
    }

    // Open popup window
    openPopup(url, name, width, height) {
        const left = Math.round((window.screen.width / 2) - (width / 2));
        const top = Math.round((window.screen.height / 2) - (height / 2));
        
        const popup = window.open(
            url,
            `${name}_share`,
            `width=${width},height=${height},left=${left},top=${top},scrollbars=yes,resizable=yes`
        );

        if (popup) {
            popup.focus();
        } else {
            // Popup blocked, fallback to new tab
            window.open(url, '_blank');
        }

        return popup;
    }

    // Show feedback message
    showFeedback(message, type = 'info', duration = 3000) {
        // Create feedback element if it doesn't exist
        let feedback = document.getElementById('share-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.id = 'share-feedback';
            feedback.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 12px 20px;
                border-radius: 5px;
                color: white;
                font-weight: 500;
                z-index: 10000;
                transition: all 0.3s ease;
                opacity: 0;
                transform: translateX(100%);
            `;
            document.body.appendChild(feedback);
        }

        // Set message and style based on type
        feedback.textContent = message;
        feedback.className = `share-feedback share-feedback-${type}`;
        
        const colors = {
            success: '#28a745',
            error: '#dc3545',
            warning: '#ffc107',
            info: '#17a2b8'
        };
        
        feedback.style.backgroundColor = colors[type] || colors.info;
        feedback.style.opacity = '1';
        feedback.style.transform = 'translateX(0)';

        // Hide after duration
        setTimeout(() => {
            feedback.style.opacity = '0';
            feedback.style.transform = 'translateX(100%)';
        }, duration);
    }

    // Debug mode check
    isDebugMode() {
        return window.location.hostname === 'localhost' || 
               window.location.search.includes('debug=1') ||
               localStorage.getItem('shareDebug') === 'true';
    }

    // Generate share buttons HTML
    generateShareButtons(url, title, description, image, options = {}) {
        const {
            platforms = ['facebook', 'twitter', 'whatsapp', 'copy'],
            showLabels = false,
            buttonClass = 'btn btn-sm me-2',
            containerClass = 'social-share-buttons'
        } = options;

        const buttons = {
            facebook: {
                icon: 'fab fa-facebook-f',
                label: 'Facebook',
                class: 'btn-primary facebook-share',
                onclick: `socialShare.shareOnFacebook('${url}', '${title}', '${description}', '${image}')`
            },
            twitter: {
                icon: 'fab fa-twitter',
                label: 'Twitter', 
                class: 'btn-info twitter-share',
                onclick: `socialShare.shareOnTwitter('${url}', '${title}', '${description}')`
            },
            whatsapp: {
                icon: 'fab fa-whatsapp',
                label: 'WhatsApp',
                class: 'btn-success whatsapp-share', 
                onclick: `socialShare.shareOnWhatsApp('${url}', '${title}', '${description}')`
            },
            linkedin: {
                icon: 'fab fa-linkedin-in',
                label: 'LinkedIn',
                class: 'btn-primary linkedin-share',
                onclick: `socialShare.shareOnLinkedIn('${url}', '${title}', '${description}')`
            },
            telegram: {
                icon: 'fab fa-telegram-plane',
                label: 'Telegram',
                class: 'btn-info telegram-share',
                onclick: `socialShare.shareOnTelegram('${url}', '${title}', '${description}')`
            },
            email: {
                icon: 'fas fa-envelope',
                label: 'Email',
                class: 'btn-secondary email-share',
                onclick: `socialShare.shareViaEmail('${url}', '${title}', '${description}')`
            },
            copy: {
                icon: 'fas fa-link',
                label: 'Copy Link',
                class: 'btn-secondary copy-link',
                onclick: `socialShare.copyToClipboard('${url}')`
            }
        };

        let html = `<div class="${containerClass}">`;
        
        platforms.forEach(platform => {
            const button = buttons[platform];
            if (button) {
                html += `
                    <button type="button" 
                            class="${buttonClass} ${button.class}" 
                            onclick="${button.onclick}" 
                            data-share="${platform}"
                            title="${button.label}">
                        <i class="${button.icon}"></i>
                        ${showLabels ? ` ${button.label}` : ''}
                    </button>
                `;
            }
        });

        html += '</div>';
        return html;
    }
}

// Initialize global instance
const socialShare = new SocialShareManager();

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SocialShareManager;
}