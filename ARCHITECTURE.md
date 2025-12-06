# System Architecture - Gestion d'Encadrement

## System Flow Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                     AUTHENTICATION LAYER                         │
│                   (authentification.php)                         │
└────────────────────────────┬────────────────────────────────────┘
                             │
                    ┌────────▼────────┐
                    │  login_process  │
                    │    (validates)   │
                    └────────┬────────┘
                             │
              ┌──────────────┼──────────────┐
              │              │              │
         ┌────▼────┐    ┌───▼────┐   ┌────▼─────┐
         │  ADMIN  │    │ STUDENT │   │ ADVISOR  │
         │Dashboard│    │Dashboard│   │Dashboard │
         └────┬────┘    └────┬───┘   └────┬─────┘
              │              │             │
              │              │             │
┌─────────────▼──────────────┼─────────────▼──────────┐
│         ADMIN FEATURES     │      ADVISOR FEATURES   │
│  • Manage Users            │   • View Students       │
│  • Add/Edit/Delete         │   • See Assignments     │
│  • View All Students       │                         │
│  • Run Auto-Assignment     │                         │
│  • Validate Assignments    │                         │
│  • Manual Assignment       │                         │
└────────────────────────────┼─────────────────────────┘
                             │
               ┌─────────────▼──────────────┐
               │     STUDENT FEATURES       │
               │  • Select 3 Preferences    │
               │  • View Assigned Advisor   │
               │  • Reset Preferences       │
               └────────────────────────────┘
```

## Database Relationships

```
┌──────────────────────────────────────────────────────────────┐
│                         USERS TABLE                          │
│  ┌────────────────────────────────────────────────────────┐  │
│  │ Common Fields:                                         │  │
│  │  • id, nom, prenom, email, mot_de_passe, role         │  │
│  ├────────────────────────────────────────────────────────┤  │
│  │ Student Fields (NULL for non-students):                │  │
│  │  • numero_inscription, parcours                        │  │
│  │  • moyenne_1ere_annee, moyenne_2eme_annee             │  │
│  ├────────────────────────────────────────────────────────┤  │
│  │ Advisor Fields (NULL for non-advisors):                │  │
│  │  • quota_max                                           │  │
│  └────────────────────────────────────────────────────────┘  │
└───────────┬──────────────────────────┬───────────────────────┘
            │                          │
            │                          │
    ┌───────▼────────┐         ┌──────▼─────────┐
    │  PREFERENCES   │         │  AFFECTATIONS  │
    │                │         │                │
    │ • etudiant_id  │         │ • etudiant_id  │
    │ • choix1_id    │         │ • encadrant_id │
    │ • choix2_id    │         │ • valide_par_  │
    │ • choix3_id    │         │   chef         │
    └────────────────┘         └────────────────┘
```

## User Journey Maps

### Student Journey
```
1. Login
   │
   ├──> Student Dashboard
   │    │
   │    ├──> Select 3 Preferred Advisors
   │    │    │
   │    │    └──> Submit Preferences
   │    │         │
   │    │         └──> Wait for Assignment
   │    │
   │    ├──> View Assigned Advisor (after validation)
   │    │
   │    └──> Reset Preferences (if needed)
   │
   └──> Logout
```

### Admin Journey
```
1. Login
   │
   ├──> Admin Dashboard
   │    │
   │    ├──> Manage User Accounts
   │    │    ├──> Add New User
   │    │    ├──> Edit Existing User
   │    │    └──> Delete User
   │    │
   │    ├──> View All Students
   │    │    └──> See Student Details
   │    │
   │    ├──> Run Auto-Assignment Algorithm
   │    │    └──> Assigns based on:
   │    │         • Student rankings
   │    │         • Preferences
   │    │         • Advisor quotas
   │    │
   │    └──> Manage Assignments
   │         ├──> View All Assignments
   │         ├──> Manually Modify
   │         └──> Validate Assignments
   │
   └──> Logout
```

### Advisor Journey
```
1. Login
   │
   ├──> Advisor Dashboard
   │    │
   │    └──> View Assigned Students
   │         ├──> Student Name
   │         ├──> Registration Number
   │         ├──> Program/Parcours
   │         └──> Contact Info
   │
   └──> Logout
```

## Auto-Assignment Algorithm Flow

```
┌────────────────────────────────────────┐
│  1. Fetch Students with Preferences    │
│     • Get all unassigned students      │
│     • Calculate average score:         │
│       (avg1 + 2*avg2) / 3              │
└──────────────┬─────────────────────────┘
               │
               ▼
┌────────────────────────────────────────┐
│  2. Sort Students by Score (DESC)      │
│     • Higher scores get priority       │
└──────────────┬─────────────────────────┘
               │
               ▼
┌────────────────────────────────────────┐
│  3. Fetch Advisor Availability         │
│     • Get quota_max for each advisor   │
│     • Count current assignments        │
└──────────────┬─────────────────────────┘
               │
               ▼
┌────────────────────────────────────────┐
│  4. For Each Student (highest first):  │
│     ├─> Try Preference 1               │
│     │   └─> Available? → Assign        │
│     ├─> Try Preference 2               │
│     │   └─> Available? → Assign        │
│     └─> Try Preference 3               │
│         └─> Available? → Assign        │
└──────────────┬─────────────────────────┘
               │
               ▼
┌────────────────────────────────────────┐
│  5. Create Affectations (unvalidated) │
│     • valide_par_chef = 0              │
│     • date_affectation = NOW()         │
└──────────────┬─────────────────────────┘
               │
               ▼
┌────────────────────────────────────────┐
│  6. Admin Reviews & Validates          │
│     • Can manually adjust              │
│     • Set valide_par_chef = 1          │
└────────────────────────────────────────┘
```

## File Dependencies

```
authentification.php
    └── login_process.php
        └── connexion.php (database)
            ├── dashboards/admin_dashboard.php
            │   ├── manage_accounts.php
            │   │   ├── add_user.php
            │   │   ├── edit_user.php
            │   │   └── delete_user.php
            │   ├── list_students.php
            │   ├── affectations.php
            │   │   └── validate_assignment.php
            │   └── run_auto_assignment.php
            │
            ├── dashboards/etudiant_dashboard.php
            │   ├── submit_choix.php
            │   ├── mon_encadrant.php
            │   └── reset_etudiant.php
            │
            └── dashboards/encadrant_dashboard.php
```

## Security Layers

```
┌─────────────────────────────────────────────────────┐
│              SESSION VALIDATION                     │
│  • Check $_SESSION['user_id']                       │
│  • Check $_SESSION['user_role']                     │
│  • Redirect if not authorized                       │
└─────────────────┬───────────────────────────────────┘
                  │
┌─────────────────▼───────────────────────────────────┐
│              ROLE-BASED ACCESS                      │
│  • Admin: Full access                               │
│  • Student: Own data only                           │
│  • Advisor: Assigned students only                  │
└─────────────────┬───────────────────────────────────┘
                  │
┌─────────────────▼───────────────────────────────────┐
│              SQL INJECTION PREVENTION               │
│  • All queries use prepared statements              │
│  • Parameters bound with execute()                  │
└─────────────────┬───────────────────────────────────┘
                  │
┌─────────────────▼───────────────────────────────────┐
│              PASSWORD SECURITY                      │
│  • Bcrypt hashing (password_hash)                   │
│  • Password verification (password_verify)          │
│  • Never store plain text passwords                 │
└─────────────────────────────────────────────────────┘
```

## Key Data Structures

### Session Variables
```php
$_SESSION = [
    'user_id'   => 123,           // Primary key from users table
    'username'  => 'Amari',       // Last name
    'prenom'    => 'Ahmed',       // First name
    'email'     => 'ahmed@...',   // Email address
    'user_role' => 'etudiant'     // admin|etudiant|encadrant
];
```

### User Roles
```php
ENUM('admin', 'etudiant', 'encadrant')

admin      → Full system access
etudiant   → Can select preferences and view assignment
encadrant  → Can view assigned students
```

### Assignment Status
```php
valide_par_chef: TINYINT(1)

0 → Pending validation (created by auto-assignment)
1 → Validated by admin (student can see it)
```

## Quick Reference

### Important SQL Queries

**Get Students with Assignments:**
```sql
SELECT u_student.*, u_advisor.nom, u_advisor.prenom
FROM affectations a
JOIN users u_student ON a.etudiant_id = u_student.id
JOIN users u_advisor ON a.encadrant_id = u_advisor.id
WHERE u_student.role = 'etudiant'
  AND a.valide_par_chef = 1;
```

**Get Available Advisors:**
```sql
SELECT u.id, u.nom, u.prenom, u.quota_max, 
       COUNT(a.id) as current_count
FROM users u
LEFT JOIN affectations a ON u.id = a.encadrant_id
WHERE u.role = 'encadrant'
GROUP BY u.id
HAVING current_count < u.quota_max;
```

**Get Student Preferences:**
```sql
SELECT p.*, 
       u1.nom as choix1_nom, 
       u2.nom as choix2_nom, 
       u3.nom as choix3_nom
FROM preferences p
LEFT JOIN users u1 ON p.choix1_id = u1.id
LEFT JOIN users u2 ON p.choix2_id = u2.id
LEFT JOIN users u3 ON p.choix3_id = u3.id
WHERE p.etudiant_id = ?;
```

---

**System Status: ✅ Fully Operational**
