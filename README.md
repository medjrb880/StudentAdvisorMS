# StudentAdvisorMS - Project Documentation

## Overview

This is a web application for managing student-advisor assignments in an academic institution. The system allows students to select their preferred advisors, and administrators can automatically assign advisors based on student rankings and advisor availability.

## Recent Fixes Applied

### 1. Database Structure Standardization

**Problem:** The project had inconsistent references to three different table structures:

- Some files referenced a `users` table
- Others referenced separate `etudiants` and `encadrants` tables
- This caused database query failures

**Solution:**

- Created a unified `users` table that stores all user types (admin, etudiant, encadrant)
- Added role-specific columns that are NULL for non-applicable roles
- Created `database_schema.sql` with the complete database structure

### 2. Session Variable Consistency

**Problem:**

- `advisor_assigned.php` used `$_SESSION['client_id']`
- Other files used `$_SESSION['user_id']`
- Missing `$_SESSION['prenom']` in login process

**Solution:**

- Standardized all files to use `$_SESSION['user_id']`
- Added `prenom` to session variables in `login_process.php`

### 3. Table Join Errors

**Problem:** Multiple files had incorrect JOIN statements:

- Joining with non-existent `etudiants` table
- Joining with non-existent `encadrants` table
- Using wrong column names (e.g., `login` instead of `email`)

**Solution:** Fixed all JOIN statements to use the `users` table:

```php
// Old (incorrect)
FROM affectations a
JOIN encadrants enc ON a.encadrant_id = enc.id

// New (correct)
FROM affectations a
JOIN users u ON a.encadrant_id = u.id
```

### 4. Role Validation Error

**Problem:** `validate_assignment.php` checked for role `'chef'` which doesn't exist in the system

**Solution:** Changed to check for `'admin'` role

### 5. User Management Forms

**Problem:**

- `add_user.php` and `edit_user.php` didn't handle:
  - First name (prenom)
  - Student-specific fields (numero_inscription, parcours, moyennes)
  - Advisor-specific fields (quota_max)

**Solution:**

- Added dynamic form fields that show/hide based on selected role
- Added JavaScript to toggle role-specific fields
- Updated backend logic to handle all fields properly

## Database Schema

### Users Table

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('admin', 'etudiant', 'encadrant') NOT NULL,

    -- Student-specific fields
    numero_inscription VARCHAR(50) NULL,
    parcours VARCHAR(100) NULL,
    moyenne_1ere_annee DECIMAL(4,2) NULL,
    moyenne_2eme_annee DECIMAL(4,2) NULL,

    -- Advisor-specific fields
    quota_max INT DEFAULT 0,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Preferences Table

```sql
CREATE TABLE preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    etudiant_id INT NOT NULL,
    choix1_id INT NULL,
    choix2_id INT NULL,
    choix3_id INT NULL,
    date_soumission TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (etudiant_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_etudiant (etudiant_id)
);
```

### Affectations Table

```sql
CREATE TABLE affectations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    etudiant_id INT NOT NULL,
    encadrant_id INT NOT NULL,
    date_affectation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    valide_par_chef TINYINT(1) DEFAULT 0,
    FOREIGN KEY (etudiant_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (encadrant_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_etudiant_assignment (etudiant_id)
);
```

## Installation Instructions

1. **Import Database:**

   ```bash
   mysql -u root -p gestion_encadrement < database_schema.sql
   ```

2. **Configure Database Connection:**
   Edit `connexion.php` with your database credentials:

   ```php
   $host = 'localhost';
   $dbname = 'gestion_encadrement';
   $username = 'root';
   $password = 'your_password';
   ```

3. **Default Credentials:**
   - **Admin:** admin@example.com / admin123
   - **Advisors:** jean.dupont@example.com / password123
   - **Students:** ahmed.amari@student.com / password123

## File Structure

```
Gestion_Encadrement/
├── authentification.php          # Login page
├── login_process.php            # Login handler
├── logout.php                   # Logout handler
├── connexion.php                # Database connection
├── database_schema.sql          # Database structure
├── style.css                    # Main stylesheet
├── profile.php                  # User profile page
├── advisor_assigned.php         # Student view of assigned advisor
├── validate_assignment.php      # Admin assignment validation
├── dashboards/
│   ├── admin_dashboard.php      # Admin main page
│   ├── etudiant_dashboard.php   # Student main page
│   ├── encadrant_dashboard.php  # Advisor main page
│   ├── add_user.php             # Add new user
│   ├── edit_user.php            # Edit user details
│   ├── delete_user.php          # Delete user
│   ├── manage_accounts.php      # User management
│   ├── list_students.php        # List all students
│   ├── submit_choix.php         # Submit preferences
│   ├── mon_encadrant.php        # View assigned advisor
│   ├── reset_etudiant.php       # Reset student preferences
│   ├── affectations.php         # View/manage assignments
│   ├── run_auto_assignment.php  # Auto-assign algorithm
│   └── [CSS files]              # Styling for each page
```

## Features

### For Students:

- Select up to 3 preferred advisors
- View assigned advisor once validated by admin
- Reset preferences and assignments

### For Advisors:

- View list of assigned students
- See student details (name, registration number, program)

### For Administrators:

- Manage user accounts (add, edit, delete)
- View all students and their information
- Run automatic assignment algorithm
- Manually adjust assignments
- Validate assignments

## Auto-Assignment Algorithm

The system assigns students to advisors based on:

1. **Student Ranking:** Calculated as `(moyenne_1ere_annee + 2 * moyenne_2eme_annee) / 3`
2. **Preferences:** Students are sorted by ranking (highest first)
3. **Availability:** Each advisor has a quota (maximum number of students)
4. **Priority:** Higher-ranked students get their first choice when possible

## Security Features

- Password hashing using `password_hash()` (bcrypt)
- Prepared statements to prevent SQL injection
- Session-based authentication
- Role-based access control
- CSRF protection (form validation)

## Common Issues & Solutions

### Issue: "Étudiant n'existe pas dans la base de données"

**Cause:** User account exists but not marked as 'etudiant' role
**Solution:** Check user's role in database: `SELECT * FROM users WHERE id = X`

### Issue: "No advisor displayed after assignment"

**Cause:** Assignment not validated (`valide_par_chef = 0`)
**Solution:** Admin must validate the assignment in the affectations page

### Issue: Can't login

**Cause:** Password mismatch or incorrect username
**Solution:** Use sample credentials or reset password in database

## Future Enhancements

- Email notifications when advisor is assigned
- Student profile with detailed information
- Advisor capacity management
- Assignment history and reports
- Export functionality for assignments
- Multi-language support

## Support

For issues or questions, contact the development team or refer to the code comments in each file.
