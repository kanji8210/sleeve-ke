# Changelog

All notable changes to the Sleeve KE plugin will be documented in this file.

## [1.0.0] - 2025-10-17

### Added
- Initial release of Sleeve KE WordPress plugin
- **Automatic Role Creation on Activation**:
  - Employer role with job management capabilities
  - Candidate role with application capabilities
  - Sleve Admin role with full management capabilities
  - Custom capabilities added to Administrator role
  
- **Automatic Database Table Creation on Activation**:
  - `sleeve_jobs` table for job postings
  - `sleeve_applications` table for tracking applications
  - `sleeve_candidates` table for extended candidate profiles
  - `sleeve_employers` table for extended employer/company profiles
  - `sleeve_payments` table for payment tracking
  
- **Plugin Structure**:
  - Main plugin file (sleeve-ke.php)
  - Core plugin class (class-sleeve-ke.php)
  - Activator class for initialization (class-sleeve-ke-activator.php)
  - Deactivator class for cleanup (class-sleeve-ke-deactivator.php)
  - Roles management class (class-sleeve-ke-roles.php)
  - Database management class (class-sleeve-ke-database.php)
  - Uninstall script for complete removal (uninstall.php)
  
- **Documentation**:
  - Comprehensive README.md
  - Testing guide (TESTING.md)
  - Changelog (CHANGELOG.md)
  
### Features
- Roles and tables are created automatically when the plugin is activated
- No manual database setup required
- Clean uninstall removes all plugin data
- WordPress coding standards compliant
- Proper security checks (direct access prevention)
- Version tracking for future migrations

### Database Schema
- Jobs table with employer tracking, status management, and expiration dates
- Applications table with unique constraints to prevent duplicate applications
- Candidates table for skills, experience, education, and portfolio information
- Employers table for company details, industry, and size information
- Payments table for transaction tracking with multiple payment methods

### User Roles
- **Employer**: Create jobs, manage applications, upload files
- **Candidate**: View jobs, apply for positions, manage profile, upload files
- **Sleve Admin**: Full management of jobs, applications, candidates, employers, and payments

### Security
- Direct file access prevention
- WordPress database prefix usage
- Proper WordPress hooks for activation/deactivation
- Clean uninstall process
