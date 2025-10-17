// Sub-sector mappings based on job sector
const subSectorOptions = {
    technology: [
        'Software Development',
        'Web Development',
        'Mobile App Development',
        'Data Science',
        'Cybersecurity',
        'IT Support',
        'DevOps',
        'Cloud Computing',
        'AI/Machine Learning',
        'UI/UX Design'
    ],
    healthcare: [
        'Nursing',
        'Medical Practitioners',
        'Pharmacy',
        'Laboratory Services',
        'Healthcare Administration',
        'Medical Research',
        'Public Health',
        'Mental Health Services'
    ],
    finance: [
        'Banking',
        'Investment',
        'Insurance',
        'Accounting',
        'Financial Analysis',
        'Auditing',
        'Risk Management',
        'Microfinance'
    ],
    education: [
        'Primary Education',
        'Secondary Education',
        'Higher Education',
        'Vocational Training',
        'Educational Administration',
        'Curriculum Development',
        'Special Education',
        'Early Childhood Education'
    ],
    manufacturing: [
        'Production',
        'Quality Control',
        'Supply Chain',
        'Maintenance',
        'Industrial Engineering',
        'Assembly',
        'Packaging',
        'Warehouse Management'
    ],
    retail: [
        'Store Management',
        'Sales',
        'Customer Service',
        'Merchandising',
        'Inventory Management',
        'E-commerce',
        'Brand Management',
        'Retail Operations'
    ],
    hospitality: [
        'Hotel Management',
        'Food & Beverage',
        'Event Planning',
        'Tour Operations',
        'Front Office',
        'Housekeeping',
        'Travel Agency',
        'Resort Management'
    ],
    agriculture: [
        'Crop Production',
        'Livestock Management',
        'Agribusiness',
        'Agricultural Research',
        'Farm Management',
        'Agricultural Extension',
        'Horticulture',
        'Fisheries'
    ],
    construction: [
        'Civil Engineering',
        'Architecture',
        'Project Management',
        'Electrical Works',
        'Plumbing',
        'Carpentry',
        'Masonry',
        'Quantity Surveying'
    ],
    telecommunications: [
        'Network Engineering',
        'Telecommunications Support',
        'Fiber Optics',
        'Mobile Services',
        'Satellite Communications',
        'VoIP Services',
        'Telecommunications Sales',
        'Technical Support'
    ],
    legal: [
        'Corporate Law',
        'Criminal Law',
        'Family Law',
        'Property Law',
        'Legal Research',
        'Paralegal Services',
        'Arbitration',
        'Legal Consulting'
    ],
    other: [
        'General Services',
        'Consulting',
        'Administration',
        'Other'
    ]
};

// Initialize form
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('employerRegistrationForm');
    const jobSectorSelect = document.getElementById('jobSector');
    const subSectorSelect = document.getElementById('subSector');

    // Handle job sector change
    jobSectorSelect.addEventListener('change', function() {
        const selectedSector = this.value;
        
        // Clear and reset sub-sector dropdown
        subSectorSelect.innerHTML = '<option value="">Select Sub-Sector</option>';
        
        if (selectedSector && subSectorOptions[selectedSector]) {
            // Enable sub-sector dropdown
            subSectorSelect.disabled = false;
            
            // Populate sub-sector options
            subSectorOptions[selectedSector].forEach(function(subSector) {
                const option = document.createElement('option');
                option.value = subSector.toLowerCase().replace(/\s+/g, '-');
                option.textContent = subSector;
                subSectorSelect.appendChild(option);
            });
        } else {
            // Disable sub-sector dropdown
            subSectorSelect.disabled = true;
            subSectorSelect.innerHTML = '<option value="">Select Job Sector First</option>';
        }
    });

    // Handle form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate form
        if (!validateForm()) {
            return;
        }
        
        // Collect form data
        const formData = new FormData(form);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            if (key === 'ndaRequired' || key === 'acceptTerms') {
                data[key] = formData.get(key) === 'on';
            } else {
                data[key] = value;
            }
        }
        
        // Display success message
        displaySuccessMessage(data);
        
        // Log form data (in production, this would be sent to a server)
        console.log('Form submitted:', data);
    });

    // Form validation
    function validateForm() {
        let isValid = true;
        const requiredFields = form.querySelectorAll('[required]');
        
        // Remove existing error messages
        const existingErrors = form.querySelectorAll('.error-message');
        existingErrors.forEach(error => error.remove());
        
        requiredFields.forEach(function(field) {
            if (!field.value.trim()) {
                isValid = false;
                showError(field, 'This field is required');
            }
        });
        
        // Validate terms acceptance
        const acceptTerms = document.getElementById('acceptTerms');
        if (!acceptTerms.checked) {
            isValid = false;
            showError(acceptTerms, 'You must accept the terms and conditions');
        }
        
        return isValid;
    }

    // Show error message
    function showError(field, message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        field.parentElement.appendChild(errorDiv);
        field.style.borderColor = '#e74c3c';
    }

    // Display success message
    function displaySuccessMessage(data) {
        // Remove existing success messages
        const existingSuccess = document.querySelector('.success-message');
        if (existingSuccess) {
            existingSuccess.remove();
        }
        
        const successDiv = document.createElement('div');
        successDiv.className = 'success-message';
        successDiv.innerHTML = `
            <strong>Registration Successful!</strong><br>
            Thank you for registering, ${data.companyName}. Your employer account has been created.<br>
            We will review your application and get back to you shortly.
        `;
        
        form.parentElement.insertBefore(successDiv, form);
        
        // Scroll to top to show success message
        window.scrollTo({ top: 0, behavior: 'smooth' });
        
        // Optional: Reset form after 3 seconds
        setTimeout(function() {
            form.reset();
            subSectorSelect.disabled = true;
            subSectorSelect.innerHTML = '<option value="">Select Job Sector First</option>';
        }, 3000);
    }

    // Reset border color on input
    const inputs = form.querySelectorAll('input, select, textarea');
    inputs.forEach(function(input) {
        input.addEventListener('input', function() {
            this.style.borderColor = '#ddd';
            const errorMessage = this.parentElement.querySelector('.error-message');
            if (errorMessage) {
                errorMessage.remove();
            }
        });
    });
});
