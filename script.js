// Form Switcher
document.addEventListener('DOMContentLoaded', function() {
    const adminBtn = document.getElementById('adminBtn');
    const candidateBtn = document.getElementById('candidateBtn');
    const adminForm = document.getElementById('adminForm');
    const candidateForm = document.getElementById('candidateForm');

    adminBtn.addEventListener('click', function() {
        adminBtn.classList.add('active');
        candidateBtn.classList.remove('active');
        adminForm.classList.add('active');
        candidateForm.classList.remove('active');
    });

    candidateBtn.addEventListener('click', function() {
        candidateBtn.classList.add('active');
        adminBtn.classList.remove('active');
        candidateForm.classList.add('active');
        adminForm.classList.remove('active');
    });

    // Admin Form Submission
    const adminRegistrationForm = document.getElementById('adminRegistrationForm');
    adminRegistrationForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Password validation
        const password = document.getElementById('adminPassword').value;
        const confirmPassword = document.getElementById('adminConfirmPassword').value;
        
        if (password !== confirmPassword) {
            alert('Passwords do not match!');
            return;
        }

        // Collect form data
        const formData = new FormData(adminRegistrationForm);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }

        // Log the data (in production, this would be sent to a server)
        console.log('Admin Registration Data:', data);
        
        // Show success message
        showSuccessMessage();
        adminRegistrationForm.reset();
    });

    // Candidate Form Submission
    const candidateRegistrationForm = document.getElementById('candidateRegistrationForm');
    candidateRegistrationForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Password validation
        const password = document.getElementById('candidatePassword').value;
        const confirmPassword = document.getElementById('candidateConfirmPassword').value;
        
        if (password !== confirmPassword) {
            alert('Passwords do not match!');
            return;
        }

        // File size validation
        const files = [
            { input: document.getElementById('profileImage'), maxSize: 5 * 1024 * 1024, name: 'Profile Image' },
            { input: document.getElementById('picture1'), maxSize: 5 * 1024 * 1024, name: 'Picture 1' },
            { input: document.getElementById('picture2'), maxSize: 5 * 1024 * 1024, name: 'Picture 2' },
            { input: document.getElementById('picture3'), maxSize: 5 * 1024 * 1024, name: 'Picture 3' },
            { input: document.getElementById('picture4'), maxSize: 5 * 1024 * 1024, name: 'Picture 4' },
            { input: document.getElementById('profilePdf'), maxSize: 10 * 1024 * 1024, name: 'Profile PDF' },
            { input: document.getElementById('resumePdf'), maxSize: 10 * 1024 * 1024, name: 'Resume PDF' }
        ];

        for (let fileObj of files) {
            if (fileObj.input.files.length > 0) {
                const file = fileObj.input.files[0];
                if (file.size > fileObj.maxSize) {
                    alert(`${fileObj.name} is too large. Maximum size is ${fileObj.maxSize / (1024 * 1024)}MB`);
                    return;
                }
            }
        }

        // Validate that at least one sector is selected
        const sectors = document.getElementById('interestedSectors');
        if (sectors.selectedOptions.length === 0) {
            alert('Please select at least one interested sector');
            return;
        }

        // Validate that at least one country is selected
        const countries = document.getElementById('workCountries');
        if (countries.selectedOptions.length === 0) {
            alert('Please select at least one country you want to work in');
            return;
        }

        // Collect form data
        const formData = new FormData(candidateRegistrationForm);
        const data = {
            personalInfo: {},
            socialMedia: {},
            documents: {},
            preferences: {}
        };
        
        // Basic fields
        data.personalInfo.fullName = formData.get('fullName');
        data.personalInfo.email = formData.get('email');
        data.personalInfo.phone = formData.get('phone');
        
        // Social media
        data.socialMedia.linkedIn = formData.get('linkedIn');
        data.socialMedia.twitter = formData.get('twitter');
        data.socialMedia.facebook = formData.get('facebook');
        data.socialMedia.instagram = formData.get('instagram');
        
        // Files
        data.documents.profileImage = document.getElementById('profileImage').files[0]?.name || '';
        data.documents.pictures = [
            document.getElementById('picture1').files[0]?.name || '',
            document.getElementById('picture2').files[0]?.name || '',
            document.getElementById('picture3').files[0]?.name || '',
            document.getElementById('picture4').files[0]?.name || ''
        ];
        data.documents.profilePdf = document.getElementById('profilePdf').files[0]?.name || '';
        data.documents.resumePdf = document.getElementById('resumePdf').files[0]?.name || '';
        
        // Preferences
        data.preferences.interestedSectors = Array.from(sectors.selectedOptions).map(option => option.value);
        data.preferences.workCountries = Array.from(countries.selectedOptions).map(option => option.value);

        // Log the data (in production, this would be sent to a server)
        console.log('Candidate Registration Data:', data);
        
        // Show success message
        showSuccessMessage();
        candidateRegistrationForm.reset();
    });

    // Success message handler
    function showSuccessMessage() {
        const successMessage = document.getElementById('successMessage');
        const adminFormEl = document.getElementById('adminForm');
        const candidateFormEl = document.getElementById('candidateForm');
        
        adminFormEl.style.display = 'none';
        candidateFormEl.style.display = 'none';
        successMessage.style.display = 'block';
        
        // Scroll to top
        window.scrollTo(0, 0);
        
        // Hide success message and show form again after 5 seconds
        setTimeout(function() {
            successMessage.style.display = 'none';
            if (adminBtn.classList.contains('active')) {
                adminFormEl.style.display = 'block';
            } else {
                candidateFormEl.style.display = 'block';
            }
        }, 5000);
    }

    // File input preview (optional enhancement)
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (this.files.length > 0) {
                const fileName = this.files[0].name;
                const fileSize = (this.files[0].size / 1024 / 1024).toFixed(2);
                console.log(`Selected: ${fileName} (${fileSize}MB)`);
            }
        });
    });
});
