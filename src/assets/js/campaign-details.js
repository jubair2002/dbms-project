// Campaign Details JavaScript - Full Screen Version
document.addEventListener('DOMContentLoaded', function() {
    // Initialize full screen handling
    setupFullScreenHandling();
    
    // Initialize all components
    initializeTabs();
    initializeDonationForm();
    
    // Setup smooth scrolling for better UX
    setupSmoothScrolling();
});

// Full screen setup function
function setupFullScreenHandling() {
    // Prevent zoom and ensure full screen
    document.addEventListener('touchstart', function(event) {
        if (event.touches.length > 1) {
            event.preventDefault();
        }
    });

    let lastTouchEnd = 0;
    document.addEventListener('touchend', function(event) {
        const now = (new Date()).getTime();
        if (now - lastTouchEnd <= 300) {
            event.preventDefault();
        }
        lastTouchEnd = now;
    }, false);

    // Adjust viewport height for full screen
    adjustViewportHeight();

    // Handle orientation change
    window.addEventListener('orientationchange', function() {
        setTimeout(adjustViewportHeight, 100);
    });

    // Handle resize
    window.addEventListener('resize', adjustViewportHeight);
}

// Adjust viewport height for full screen
function adjustViewportHeight() {
    const vh = window.innerHeight;
    document.body.style.height = vh + 'px';
    const container = document.querySelector('.container');
    if (container) {
        container.style.height = vh + 'px';
    }
}

// Initialize tab functionality
function initializeTabs() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons and panes
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabPanes.forEach(pane => pane.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Show corresponding tab content
            const tabId = this.getAttribute('data-tab');
            const targetPane = document.getElementById(tabId);
            if (targetPane) {
                targetPane.classList.add('active');
            }
            
            // Add smooth transition effect
            setTimeout(() => {
                if (targetPane) {
                    targetPane.style.opacity = '1';
                }
            }, 50);
        });
    });
}

// Initialize donation form functionality
function initializeDonationForm() {
    const amountButtons = document.querySelectorAll('.amount-btn');
    let customAmountInput = null;
    let selectedAmount = 50; // Default amount
    
    amountButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            amountButtons.forEach(btn => btn.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Handle custom amount
            if (this.textContent === 'Custom') {
                handleCustomAmount(this);
            } else {
                // Hide custom amount input if it exists
                if (customAmountInput) {
                    customAmountInput.style.display = 'none';
                }
                
                // Set the selected amount based on the button text
                selectedAmount = parseFloat(this.textContent.replace('$', ''));
                updateDonationAmount(selectedAmount);
            }
        });
    });
    
    // Handle custom amount functionality
    function handleCustomAmount(button) {
        if (!customAmountInput) {
            customAmountInput = document.createElement('input');
            customAmountInput.type = 'number';
            customAmountInput.min = '1';
            customAmountInput.placeholder = 'Enter amount ($)';
            customAmountInput.className = 'custom-amount';
            
            // Add event listener to update selected amount
            customAmountInput.addEventListener('input', function() {
                if (this.value && !isNaN(this.value) && this.value > 0) {
                    selectedAmount = parseFloat(this.value);
                    updateDonationAmount(selectedAmount);
                }
            });
            
            // Insert after the donation amounts container
            const donationAmounts = document.querySelector('.donation-amounts');
            donationAmounts.appendChild(customAmountInput);
        }
        
        // Show and focus the input
        customAmountInput.style.display = 'block';
        setTimeout(() => customAmountInput.focus(), 100);
    }
    
    // Update donation amount in the donate button
    function updateDonationAmount(amount) {
        const donateBtn = document.querySelector('.donate-btn');
        const hiddenInput = document.getElementById('donationAmount');
        
        if (donateBtn) {
            // Update the href attribute for the donate button
            const currentHref = donateBtn.getAttribute('href');
            const baseUrl = currentHref.split('&amount=')[0];
            donateBtn.setAttribute('href', `${baseUrl}&amount=${amount}`);
        }
        
        if (hiddenInput) {
            hiddenInput.value = amount;
        }
    }
    
    // Handle the donate button click with confirmation
    const donateBtn = document.querySelector('.donate-btn');
    if (donateBtn) {
        donateBtn.addEventListener('click', function(e) {
            // Add loading state
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            this.style.pointerEvents = 'none';
            
            // Reset after a short delay (in case of navigation issues)
            setTimeout(() => {
                this.innerHTML = originalText;
                this.style.pointerEvents = 'auto';
            }, 3000);
        });
    }
    
    // Set default amount
    const defaultButton = document.querySelector('.amount-btn');
    if (defaultButton) {
        defaultButton.click();
    }
}

// Setup smooth scrolling for better UX
function setupSmoothScrolling() {
    // Smooth scroll for tab content changes
    const tabButtons = document.querySelectorAll('.tab-btn');
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabContent = document.querySelector('.tab-content');
            if (tabContent) {
                tabContent.scrollTop = 0;
            }
        });
    });
    
    // Smooth scroll for back button
    const backBtn = document.querySelector('.back-btn');
    if (backBtn) {
        backBtn.addEventListener('click', function(e) {
            // Add a slight delay for better UX
            this.style.transform = 'translateY(-2px)';
            setTimeout(() => {
                this.style.transform = 'translateY(0)';
            }, 150);
        });
    }
}

// Add progressive loading for images
function setupImageLoading() {
    const campaignImage = document.querySelector('.campaign-main-image img');
    if (campaignImage) {
        campaignImage.addEventListener('load', function() {
            this.style.opacity = '1';
            this.style.filter = 'none';
        });
        
        // Add loading placeholder
        campaignImage.style.opacity = '0.7';
        campaignImage.style.filter = 'blur(2px)';
        campaignImage.style.transition = 'all 0.5s ease';
    }
}

// Initialize image loading when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    setupImageLoading();
});

// Add visual feedback for stat cards
function initializeStatCards() {
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach((card, index) => {
        // Add staggered animation on load
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
        
        // Initial state
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'all 0.5s ease';
    });
}

// Initialize stat cards animation
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(initializeStatCards, 500);
});

// Handle progress bar animation
function animateProgressBar() {
    const progressBar = document.querySelector('.progress-large');
    if (progressBar) {
        const targetWidth = progressBar.style.width;
        progressBar.style.width = '0%';
        
        setTimeout(() => {
            progressBar.style.width = targetWidth;
        }, 1000);
    }
}

// Initialize progress bar animation
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(animateProgressBar, 800);
});

// Add error handling for network requests
window.addEventListener('online', function() {
    showNotification('Connection restored', 'success');
});

window.addEventListener('offline', function() {
    showNotification('Connection lost - some features may not work', 'warning');
});

// Simple notification system
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 12px 20px;
        border-radius: 6px;
        color: white;
        font-weight: 500;
        z-index: 1000;
        animation: slideIn 0.3s ease;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;
    
    // Set background color based on type
    switch (type) {
        case 'success':
            notification.style.backgroundColor = '#4CAF50';
            break;
        case 'warning':
            notification.style.backgroundColor = '#ff9800';
            break;
        case 'error':
            notification.style.backgroundColor = '#f44336';
            break;
        default:
            notification.style.backgroundColor = '#2196F3';
    }
    
    document.body.appendChild(notification);
    
    // Remove notification after 3 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }
    }, 3000);
}

// Add CSS for notifications
const notificationStyles = document.createElement('style');
notificationStyles.textContent = `
    @keyframes slideIn {
        from { transform: translateX(300px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(300px); opacity: 0; }
    }
`;
document.head.appendChild(notificationStyles);