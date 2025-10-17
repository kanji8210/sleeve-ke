# Sleeve KE - WordPress Job Board Plugin

A comprehensive job board and recruitment management plugin for WordPress.

## Features

### Automatic Initialization
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

## Installation

1. Upload the `sleeve-ke` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Roles and database tables will be created automatically upon activation

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

## Uninstall

When uninstalling the plugin:
- All custom roles are removed
- All database tables are dropped
- All plugin options are deleted

**Warning**: Uninstalling will permanently delete all job postings, applications, and related data.

## Version

Current version: 1.0.0

## License

GPL v2 or later