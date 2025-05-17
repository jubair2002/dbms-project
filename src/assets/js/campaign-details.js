document.addEventListener('DOMContentLoaded', function() {
    // Tab functionality
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
            document.getElementById(tabId).classList.add('active');
        });
    });
    
    // Donation amount buttons
    const amountButtons = document.querySelectorAll('.amount-btn');
    amountButtons.forEach(button => {
        button.addEventListener('click', function() {
            amountButtons.forEach(btn => btn.classList.remove('selected'));
            this.classList.add('selected');
        });
    });
    
    // Mobile menu toggle would go here if needed
});

// Campaign details JavaScript file
document.addEventListener('DOMContentLoaded', function() {
    // Tab functionality
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons and panes
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabPanes.forEach(pane => pane.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Show corresponding tab pane
            const tabId = this.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
        });
    });
    
    // Handle donation amount buttons
    const amountButtons = document.querySelectorAll('.amount-btn');
    let customAmountInput;
    let selectedAmount = 50; // Default amount
    
    amountButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            amountButtons.forEach(btn => btn.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Handle custom amount
            if (this.textContent === 'Custom') {
                // If custom amount input doesn't exist, create it
                if (!customAmountInput) {
                    customAmountInput = document.createElement('input');
                    customAmountInput.type = 'number';
                    customAmountInput.min = '1';
                    customAmountInput.placeholder = 'Enter amount';
                    customAmountInput.className = 'custom-amount';
                    customAmountInput.style.width = '100px';
                    customAmountInput.style.padding = '5px';
                    customAmountInput.style.marginTop = '10px';
                    customAmountInput.style.border = '1px solid #ddd';
                    customAmountInput.style.borderRadius = '4px';
                    
                    // Add event listener to update selected amount
                    customAmountInput.addEventListener('input', function() {
                        if (this.value && !isNaN(this.value) && this.value > 0) {
                            selectedAmount = parseFloat(this.value);
                        }
                    });
                    
                    // Insert after the buttons
                    this.parentNode.appendChild(customAmountInput);
                    
                    // Focus on the input
                    customAmountInput.focus();
                } else {
                    // Show the existing input if it was hidden
                    customAmountInput.style.display = 'block';
                    customAmountInput.focus();
                }
            } else {
                // Hide custom amount input if it exists
                if (customAmountInput) {
                    customAmountInput.style.display = 'none';
                }
                
                // Set the selected amount based on the button text
                selectedAmount = parseFloat(this.textContent.replace('$', ''));
            }
        });
    });
    
    // Handle the donate button click
    const donateBtn = document.querySelector('.donate-btn');
    if (donateBtn) {
        donateBtn.addEventListener('click', function() {
            // Get the campaign ID from the URL
            const urlParams = new URLSearchParams(window.location.search);
            const campaignId = urlParams.get('id');
            
            if (campaignId) {
                // Redirect to donation page with campaign ID and amount
                window.location.href = `donate.php?campaign_id=${campaignId}&amount=${selectedAmount}`;
            }
        });
    }
});