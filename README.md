# Sleeve-KE Registration Portal

A comprehensive registration system for Sleeve-KE with separate forms for administrators and candidates.

## Features

### Admin Registration Form
- Full name, email, phone number
- Password with confirmation
- Admin role selection (Super Admin, Admin, Moderator)
- Form validation

### Candidate Registration Form
- **Personal Information**
  - Full name, email, phone number
  - Secure password with confirmation

- **Profile Image**
  - Upload profile picture (JPG, PNG, GIF up to 5MB)

- **Gallery Images**
  - Upload 4 professional pictures (up to 5MB each)

- **Social Media Links**
  - LinkedIn
  - Twitter/X
  - Facebook
  - Instagram

- **Documents**
  - Profile PDF (up to 10MB)
  - Résumé/CV PDF (up to 10MB)

- **Career Preferences**
  - **Interested Sectors** (multi-select):
    - Healthcare
    - Technology/IT
    - Engineering
    - Finance & Banking
    - Education
    - Hospitality & Tourism
    - Construction
    - Retail & Sales
    - Manufacturing
    - Transportation & Logistics
    - Telecommunications
    - Energy & Utilities
    - Agriculture
    - Legal Services
    - Media & Entertainment
    - Other

  - **Countries to Work In** (multi-select):
    - United Kingdom (UK)
    - Germany
    - United Arab Emirates (UAE)
    - Qatar
    - Bahrain
    - Israel

## How to Use

1. **Open the Application**
   - Open `index.html` in a web browser

2. **Select Registration Type**
   - Click "Admin Registration" or "Candidate Registration" button

3. **Fill Out the Form**
   - Complete all required fields (marked with *)
   - Upload necessary files
   - Select preferences using the dropdown menus

4. **Submit**
   - Click the submit button
   - A success message will appear upon successful submission

## File Structure

```
sleeve-ke/
├── index.html      # Main HTML file with both registration forms
├── styles.css      # Styling for the registration portal
├── script.js       # JavaScript for form validation and handling
└── README.md       # This file
```

## Form Validation

- **Password Requirements**: Minimum 8 characters
- **Email**: Valid email format required
- **File Sizes**: 
  - Images: Maximum 5MB each
  - PDFs: Maximum 10MB each
- **Multi-select Fields**: Hold Ctrl (Cmd on Mac) to select multiple options
- **Required Fields**: All fields marked with * must be filled

## Technical Details

- Pure HTML, CSS, and JavaScript (no frameworks required)
- Responsive design for mobile and desktop
- Client-side form validation
- File upload support with size validation
- Multi-select dropdowns for sectors and countries

## Future Enhancements

- Backend integration for data storage
- Email verification
- Image preview before upload
- Advanced password strength indicator
- Country-specific phone number validation
- Resume parsing
- Integration with job matching system

## Browser Compatibility

- Chrome (recommended)
- Firefox
- Safari
- Edge
- Opera

## Notes

- Currently, form data is logged to the browser console
- In production, integrate with a backend API to store registration data
- Ensure proper security measures for password storage
- Consider adding reCAPTCHA for bot prevention
- Add email verification for account activation