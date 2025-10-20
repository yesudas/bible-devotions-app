// Copy functionality for 3-Minute Meditation App
// Version: 2025.10.1

class CopyManager {
    constructor() {
        this.init();
    }

    init() {
        this.addCopyButtons();
        this.bindEvents();
    }

    addCopyButtons() {
        // Add copy buttons to the navigation section (next to "View All")
        const navigationCenter = document.querySelector('.navigation-center');
        if (navigationCenter) {
            const copyButtonsContainer = document.createElement('div');
            copyButtonsContainer.className = 'copy-controls mt-2';
            copyButtonsContainer.innerHTML = `
                <button id="copyTextBtn" class="copy-btn" title="Copy Meditation Text">
                    <i class="fas fa-copy"></i>
                    <span class="copy-btn-label">Copy Text</span>
                </button>
                <button id="copyLinkBtn" class="copy-btn" title="Copy Current Link">
                    <i class="fas fa-link"></i>
                    <span class="copy-btn-label">Share Link</span>
                </button>
            `;
            navigationCenter.appendChild(copyButtonsContainer);
        }
    }

    bindEvents() {
        // Copy text button
        const copyTextBtn = document.getElementById('copyTextBtn');
        if (copyTextBtn) {
            copyTextBtn.addEventListener('click', () => this.copyMeditationText());
        }

        // Copy link button
        const copyLinkBtn = document.getElementById('copyLinkBtn');
        if (copyLinkBtn) {
            copyLinkBtn.addEventListener('click', () => this.copyCurrentLink());
        }
    }

    copyMeditationText() {
        try {
            // Get all the meditation content
            const content = this.extractMeditationContent();
            
            if (content) {
                this.copyToClipboard(content, 'Meditation text copied to clipboard!');
            } else {
                this.showNotification('No meditation content found to copy', 'error');
            }
        } catch (error) {
            console.error('Error copying meditation text:', error);
            this.showNotification('Failed to copy meditation text', 'error');
        }
    }

    copyCurrentLink() {
        try {
            const currentUrl = this.cleanUrl(window.location.href);
            this.copyToClipboard(currentUrl, 'Link copied to clipboard!');
        } catch (error) {
            console.error('Error copying link:', error);
            this.showNotification('Failed to copy link', 'error');
        }
    }

    cleanUrl(url) {
        try {
            const urlObj = new URL(url);
            // Remove f=app query parameter
            urlObj.searchParams.delete('f');
            return urlObj.toString();
        } catch (error) {
            console.error('Error cleaning URL:', error);
            return url;
        }
    }

    extractMeditationContent() {
        const devotionContent = document.querySelector('.devotion-content');
        if (!devotionContent) return null;

        const title = document.querySelector('.devotion-header h2')?.textContent?.trim() || '';
        let content = `${title}\n${'='.repeat(title.length)}\n\n`;

        const sections = devotionContent.querySelectorAll('.section');
        sections.forEach(section => {
            const heading = section.querySelector('h2')?.textContent?.trim();
            const text = this.extractTextFromSection(section);
            
            if (heading && text) {
                content += `${heading}\n${'-'.repeat(heading.length)}\n${text}\n\n`;
            }
        });

        // Add source attribution
        const cleanUrl = this.cleanUrl(window.location.href);
        content += `\n---\nSource: 3-Minute Meditation - WordOfGod.in\nLink: ${cleanUrl}`;

        return content;
    }

    extractTextFromSection(section) {
        let text = '';
        const heading = section.querySelector('h2')?.textContent?.trim();
        
        // Get verse reference if it exists
        const verseRef = section.querySelector('.verse-reference');
        if (verseRef) {
            text += verseRef.textContent.trim() + '\n\n';
            return text.trim();
        }

        // Special handling for Recommended Book section
        if (heading && heading.includes('Recommended Book')) {
            const bookTitle = section.querySelector('h6.fw-bold')?.textContent?.trim();
            const bookAuthor = section.querySelector('p.text-muted')?.textContent?.trim();
            const blockquote = section.querySelector('blockquote');
            
            if (bookTitle) {
                text += `${bookTitle}\n`;
            }
            if (bookAuthor) {
                text += `${bookAuthor}\n\n`;
            }
            if (blockquote) {
                const quoteText = blockquote.querySelector('p')?.textContent?.trim();
                const pageInfo = blockquote.querySelector('small')?.textContent?.trim();
                if (quoteText) {
                    text += `"${quoteText}"\n`;
                }
                if (pageInfo) {
                    text += `${pageInfo}\n`;
                }
            }
            return text.trim();
        }

        // Special handling for Author section
        if (heading && heading.includes('Author')) {
            const authorName = section.querySelector('strong')?.textContent?.trim();
            if (authorName) {
                text += `${authorName}\n`;
            }
            
            // Get contact info
            const whatsappLink = section.querySelector('a[href^="https://wa.me/"]');
            const emailLink = section.querySelector('a[href^="mailto:"]');
            
            if (whatsappLink) {
                const whatsappText = whatsappLink.textContent.trim();
                text += `WhatsApp: ${whatsappText}\n`;
            }
            
            if (emailLink) {
                const emailText = emailLink.textContent.trim();
                text += `Email: ${emailText}\n`;
            }
            
            return text.trim();
        }

        // For all other sections, get paragraphs (excluding those in blockquotes to avoid duplication)
        const paragraphs = section.querySelectorAll('p:not(.text-muted):not(blockquote p)');
        paragraphs.forEach(p => {
            const pText = p.textContent.trim();
            if (pText && !p.closest('blockquote')) {
                text += pText + '\n\n';
            }
        });

        // Get standalone blockquotes (not in recommended book section)
        if (!heading || !heading.includes('Recommended Book')) {
            const blockquotes = section.querySelectorAll('blockquote');
            blockquotes.forEach(quote => {
                const quoteText = quote.textContent.trim();
                if (quoteText) {
                    text += `"${quoteText}"\n\n`;
                }
            });
        }

        return text.trim();
    }

    async copyToClipboard(text, successMessage) {
        try {
            if (navigator.clipboard && window.isSecureContext) {
                // Use modern clipboard API
                await navigator.clipboard.writeText(text);
            } else {
                // Fallback for older browsers or non-HTTPS
                this.fallbackCopyToClipboard(text);
            }
            this.showNotification(successMessage, 'success');
        } catch (error) {
            console.error('Clipboard API failed:', error);
            this.fallbackCopyToClipboard(text);
            this.showNotification(successMessage, 'success');
        }
    }

    fallbackCopyToClipboard(text) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            document.execCommand('copy');
        } catch (error) {
            console.error('Fallback copy failed:', error);
            throw error;
        } finally {
            document.body.removeChild(textArea);
        }
    }

    showNotification(message, type = 'success') {
        // Remove existing notification
        const existingNotification = document.querySelector('.copy-notification');
        if (existingNotification) {
            existingNotification.remove();
        }

        // Create notification
        const notification = document.createElement('div');
        notification.className = `copy-notification ${type}`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check' : 'exclamation-triangle'}"></i>
            <span>${message}</span>
        `;

        document.body.appendChild(notification);

        // Show notification
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);

        // Hide notification after 3 seconds
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }
}

// Initialize copy manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new CopyManager();
});

// Export for module use if needed
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CopyManager;
}