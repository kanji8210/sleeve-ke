# Sleeve KE - Employer Registration Portal

A modern, responsive employer registration form for the Sleeve KE job platform. This form allows companies to register and provide detailed information about their organization and job posting preferences.

## Features

### Company Information
- **Company Name**: Text input for the company name
- **Country Selection**: Dropdown with East African countries and international options
- **Location/City**: Text input for specific location within the country

### Job Posting Preferences
- **Job Sector**: Comprehensive dropdown with 12 major sectors including:
  - Technology
  - Healthcare
  - Finance & Banking
  - Education
  - Manufacturing
  - Retail & Sales
  - Hospitality & Tourism
  - Agriculture
  - Construction
  - Telecommunications
  - Legal Services
  - Other

- **Sub-Sector**: Dynamic dropdown that populates based on the selected job sector with relevant sub-sectors
- **Nature of Job**: Selection for employment type:
  - Full-Time
  - Part-Time
  - Contract
  - Temporary
  - Internship
  - Freelance

- **Gender Preference**: Optional selection for gender preference in hiring

### Terms and Agreements
- **Terms of Employment**: Text area for detailed employment terms description
- **NDA Requirement**: Optional checkbox to indicate if candidates need to sign a Non-Disclosure Agreement
- **Terms Acceptance**: Required checkbox for accepting the platform's terms and conditions

## Technical Implementation

### Technologies Used
- **HTML5**: Semantic markup for form structure
- **CSS3**: Modern styling with gradients, transitions, and responsive design
- **Vanilla JavaScript**: Client-side form validation and dynamic behavior

### Key Features
1. **Dynamic Sub-Sector Loading**: Sub-sector dropdown automatically populates based on the selected job sector
2. **Client-Side Validation**: Real-time form validation with error messages
3. **Responsive Design**: Mobile-friendly layout that works on all screen sizes
4. **Success Feedback**: Clear success message upon form submission
5. **Form Reset**: Automatic form reset after successful submission
6. **Accessibility**: Proper labels, required field indicators, and keyboard navigation support

## Usage

### Running Locally

1. Clone the repository:
```bash
git clone https://github.com/kanji8210/sleeve-ke.git
cd sleeve-ke
```

2. Serve the HTML file using any HTTP server. For example, using Python:
```bash
python3 -m http.server 8080
```

3. Open your browser and navigate to:
```
http://localhost:8080/employer-registration.html
```

### File Structure
```
sleeve-ke/
├── employer-registration.html  # Main form HTML
├── styles.css                  # Form styling
├── script.js                   # Form validation and interactivity
└── README.md                   # This file
```

## Form Validation

The form includes comprehensive validation:
- All required fields must be filled
- Terms and conditions must be accepted
- Real-time error messages for invalid inputs
- Visual feedback (border colors) for field states

## Screenshots

### Empty Form
![Employer Registration Form](https://github.com/user-attachments/assets/667c791d-3256-45c6-9e77-8412f4fddc5d)

### Filled Form
![Filled Registration Form](https://github.com/user-attachments/assets/bc805b2e-ed45-46a2-9ead-70809822da17)

### Success Message
![Registration Success](https://github.com/user-attachments/assets/6f80a50f-f4ca-4dcb-8912-f0f0bd57e67e)

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers

## Future Enhancements

Potential improvements for future versions:
- Backend integration for data persistence
- Email verification system
- Multi-step form wizard
- File upload for company documents
- Integration with payment gateway for premium listings
- Admin dashboard for managing registrations

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is open source and available under the MIT License.

## Contact

For questions or support, please open an issue on GitHub.