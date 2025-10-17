# Sleeve KE - WordPress Job Board Plugin

A comprehensive job board and recruitment management plugin for WordPress with a modern employer registration portal.

## Features

### WordPress Plugin Features
- **Automatic Role Creation**: On plugin activation, three custom user roles are automatically created:
  - **Employer**: Can create and manage job postings, view and manage applications
  - **Candidate**: Can view jobs, apply for positions, and manage their profile
  - **Sleve Admin**: Can manage all aspects of the job board including jobs, applications, candidates, employers, and payments

- **Automatic Database Table Creation**: Five database tables are automatically created on activation:
  - **sleeve_jobs**: Stores job postings with details like title, description, requirements, salary, location, etc.
  - **sleeve_applications**: Tracks job applications from candidates
  - **sleeve_candidates**: Extended profile information for candidates (skills, experience, resume, etc.)
  - **sleeve_employers**: Extended profile information for employers (company details, industry, etc.)
  - **sleeve_payments**: Payment tracking for premium features or services

### Employer Registration Portal
A modern, responsive employer registration form for the Sleeve KE job platform.

#### Company Information
- **Company Name**: Text input for the company name
- **Country Selection**: Dropdown with East African countries and international options
- **Location/City**: Text input for specific location within the country

#### Job Posting Preferences
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

#### Terms and Agreements
- **Terms of Employment**: Text area for detailed employment terms description
- **NDA Requirement**: Optional checkbox to indicate if candidates need to sign a Non-Disclosure Agreement
- **Terms Acceptance**: Required checkbox for accepting the platform's terms and conditions

## Technical Implementation

### Technologies Used
- **WordPress PHP**: Plugin architecture and WordPress integration
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

## Installation

1. Upload the `sleeve-ke` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Roles and database tables will be created automatically upon activation

## File Structure
```
sleeve-ke/
├── sleeve-ke.php                    # Main plugin file
├── includes/
│   ├── class-sleeve-ke.php          # Main plugin class
│   ├── class-sleeve-ke-activator.php # Plugin activation handler
│   ├── class-sleeve-ke-deactivator.php # Plugin deactivation handler
│   ├── class-sleeve-ke-database.php  # Database management
│   └── class-sleeve-ke-roles.php     # User roles management
├── employer-registration.html        # Employer registration form
├── styles.css                       # Form styling
├── script.js                        # Form validation and interactivity
├── uninstall.php                    # Plugin uninstall handler
└── README.md                        # This file
```

## Database Schema

### Jobs Table
- Job postings with employer information, descriptions, requirements, location, salary, and status

### Applications Table
- Job applications linking candidates to jobs with status tracking and notes

### Candidates Table
- Extended candidate profiles with skills, experience, education, and portfolio links

### Employers Table
- Company information including name, description, logo, website, and industry

### Payments Table
- Transaction records for payments with amount, currency, method, and status

## User Roles & Capabilities

### Employer Role
- Create and manage their own job postings
- View and manage applications to their jobs
- Upload files

### Candidate Role
- View available jobs
- Apply for jobs
- View their own applications
- Edit their profile
- Upload files (resume, documents)

### Sleve Admin Role
- Full management capabilities for jobs, applications, candidates, employers, and payments
- Can view and edit all content
- Process payments and manage transactions

## Form Validation

The employer registration form includes comprehensive validation:
- All required fields must be filled
- Terms and conditions must be accepted
- Real-time error messages for invalid inputs
- Visual feedback (border colors) for field states

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers

## Uninstall

When uninstalling the plugin:
- All custom roles are removed
- All database tables are dropped
- All plugin options are deleted

**Warning**: Uninstalling will permanently delete all job postings, applications, and related data.

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

## Version

Current version: 1.0.0

## License

GPL v2 or later
