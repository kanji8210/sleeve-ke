# Sleeve KE - WordPress Job Management Plugin

A comprehensive WordPress plugin for managing job postings, applications, candidates, and employers in Kenya.

## Description

Sleeve KE is a fully-featured job management system designed for WordPress. It provides a complete solution for job boards, recruitment agencies, and companies looking to manage their hiring process directly from their WordPress site.

## Features

### Automatic Initialization
- **Automatic Role Creation**: On plugin activation, three custom user roles are automatically created:
  - **Employer** - Can create and manage job postings, view and manage applications
  - **Candidate** - Can view jobs, apply for positions, and manage their profile
  - **Sleeve Admin** - Full administrative access to manage applications, jobs, candidates, employers, and payments

- **Automatic Database Table Creation**: Five database tables are automatically created on activation:
  - **sleeve_jobs**: Stores job postings with details like title, description, requirements, salary, location, etc.
  - **sleeve_applications**: Tracks job applications from candidates
  - **sleeve_candidates**: Extended profile information for candidates (skills, experience, resume, etc.)
  - **sleeve_employers**: Extended profile information for employers (company details, industry, etc.)
  - **sleeve_payments**: Payment tracking for premium features or services

### Management Features
- **Admin Dashboard** - Comprehensive dashboard for Sleeve Admins to manage all aspects of the job portal
- **Applications Management** - Track and manage job applications
- **Jobs Management** - Create, edit, and manage job postings
- **Candidates Management** - Manage candidate profiles and applications
- **Employers Management** - Manage employer accounts and their job postings
- **Payments Management** - Track and manage payment transactions

## Installation

1. Upload the `sleeve-ke` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Roles and database tables will be created automatically upon activation
4. Navigate to 'Sleeve KE' in the admin menu to configure the plugin

## Folder Structure

```
sleeve-ke/
├── admin/                      # Admin-specific functionality
│   └── class-sleeve-ke-admin.php
├── public/                     # Public-facing functionality
│   └── class-sleeve-ke-public.php
├── includes/                   # Core plugin files
│   ├── class-sleeve-ke.php
│   ├── class-sleeve-ke-activator.php
│   ├── class-sleeve-ke-deactivator.php
│   ├── class-sleeve-ke-loader.php
│   ├── class-sleeve-ke-i18n.php
│   ├── class-sleeve-ke-database.php
│   └── class-sleeve-ke-roles.php
├── languages/                  # Translation files
│   └── sleeve-ke.pot
├── assets/                     # Static assets
│   ├── css/                    # Stylesheets
│   │   ├── sleeve-ke-admin.css
│   │   └── sleeve-ke-public.css
│   ├── js/                     # JavaScript files
│   │   ├── sleeve-ke-admin.js
│   │   └── sleeve-ke-public.js
│   └── images/                 # Image files
├── sleeve-ke.php               # Main plugin file
└── uninstall.php               # Uninstallation script
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

## User Roles and Capabilities

### Employer
- Read content
- Edit and publish posts
- Upload files
- Manage jobs
- View applications
- Manage payments
- Create and manage their own job postings
- View and manage applications to their jobs

### Candidate
- Read content
- Upload files
- View jobs
- Apply to jobs
- Manage profile
- View their own applications
- Edit their profile
- Upload files (resume, documents)

### Sleeve Admin
- All Employer and Candidate capabilities
- Manage all applications
- Manage all jobs
- Manage all candidates
- Manage all employers
- Manage all payments
- Edit and delete users
- Manage WordPress options
- Process payments and manage transactions

## Usage

### For Sleeve Admins
1. After activation, log in to WordPress as an administrator
2. Navigate to 'Sleeve KE' in the admin menu
3. Use the submenu items to manage:
   - Applications
   - Jobs
   - Candidates
   - Employers
   - Payments

### For Developers
The plugin follows WordPress plugin development best practices:
- Object-oriented architecture
- Separation of concerns (admin, public, includes)
- Internationalization ready
- Action and filter hooks for extensibility

## Uninstall

When uninstalling the plugin:
- All custom roles are removed
- All database tables are dropped
- All plugin options are deleted

**Warning**: Uninstalling will permanently delete all job postings, applications, and related data.

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher

## Version

Current version: 1.0.0

## License

This plugin is licensed under the GPL v2 or later.

## Support

For bug reports and feature requests, please use the [GitHub Issues](https://github.com/kanji8210/sleeve-ke/issues) page.

## Credits

Developed by the Sleeve KE Team
