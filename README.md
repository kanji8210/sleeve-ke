# Sleeve KE - WordPress Job Management Plugin

A comprehensive WordPress plugin for managing job postings, applications, candidates, and employers in Kenya.

## Description

Sleeve KE is a fully-featured job management system designed for WordPress. It provides a complete solution for job boards, recruitment agencies, and companies looking to manage their hiring process directly from their WordPress site.

## Features

- **Custom User Roles:**
  - **Employer** - Can post jobs, manage applications, and handle payments
  - **Candidate** - Can browse jobs, submit applications, and manage their profile
  - **Sleeve Admin** - Full administrative access to manage applications, jobs, candidates, employers, and payments

- **Admin Dashboard** - Comprehensive dashboard for Sleeve Admins to manage all aspects of the job portal
- **Applications Management** - Track and manage job applications
- **Jobs Management** - Create, edit, and manage job postings
- **Candidates Management** - Manage candidate profiles and applications
- **Employers Management** - Manage employer accounts and their job postings
- **Payments Management** - Track and manage payment transactions

## Installation

1. Upload the `sleeve-ke` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to 'Sleeve KE' in the admin menu to configure the plugin

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
│   └── class-sleeve-ke-i18n.php
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

## User Roles and Capabilities

### Employer
- Read content
- Edit and publish posts
- Upload files
- Manage jobs
- View applications
- Manage payments

### Candidate
- Read content
- Upload files
- View jobs
- Apply to jobs
- Manage profile

### Sleeve Admin
- All Employer and Candidate capabilities
- Manage all applications
- Manage all jobs
- Manage all candidates
- Manage all employers
- Manage all payments
- Edit and delete users
- Manage WordPress options

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

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher

## License

This plugin is licensed under the GPL v2 or later.

## Support

For bug reports and feature requests, please use the [GitHub Issues](https://github.com/kanji8210/sleeve-ke/issues) page.

## Credits

Developed by the Sleeve KE Team