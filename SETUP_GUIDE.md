# Quick Setup Guide - Gestion d'Encadrement

## Step-by-Step Installation

### Step 1: Database Setup
1. Open your MySQL client (phpMyAdmin, MySQL Workbench, or command line)
2. Create the database:
   ```sql
   CREATE DATABASE gestion_encadrement CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Import the schema:
   ```bash
   # Command line method:
   mysql -u root -p gestion_encadrement < database_schema.sql
   
   # Or use phpMyAdmin:
   # - Select 'gestion_encadrement' database
   # - Click 'Import' tab
   # - Choose 'database_schema.sql' file
   # - Click 'Go'
   ```

### Step 2: Configure Connection
1. Open `connexion.php`
2. Update credentials if needed:
   ```php
   $host = 'localhost';
   $dbname = 'gestion_encadrement';
   $username = 'root';        // Your MySQL username
   $password = '';            // Your MySQL password
   ```

### Step 3: Start Local Server
```bash
# Using PHP built-in server (recommended for testing):
php -S localhost:8000

# Or use XAMPP/WAMP/MAMP
# Place project in htdocs/www folder
# Access via: http://localhost/Gestion_Encadrement/
```

### Step 4: Login
Open browser and go to:
- `http://localhost:8000/authentification.php`
- Or: `http://localhost/Gestion_Encadrement/authentification.php`

**Test Accounts:**
- **Admin:** admin@example.com / admin123
- **Advisor:** jean.dupont@example.com / password123  
- **Student:** ahmed.amari@student.com / password123

## Verification Checklist

- [ ] Database created and imported successfully
- [ ] No errors in connexion.php
- [ ] Can access login page
- [ ] Can login with admin account
- [ ] Can see dashboard for each role
- [ ] Students can submit preferences
- [ ] Admin can run auto-assignment
- [ ] Assignments appear correctly

## Troubleshooting

### "Connection failed" error
**Problem:** Cannot connect to database  
**Solution:** Check credentials in `connexion.php` and ensure MySQL is running

### "Table doesn't exist" error
**Problem:** Database schema not imported  
**Solution:** Run `database_schema.sql` import again

### Blank page after login
**Problem:** Session or PHP errors  
**Solution:** Check PHP error logs, enable error display:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### Password doesn't work
**Problem:** Password hash mismatch  
**Solution:** The default password for all sample accounts is "password123" or "admin123"
To create a new password hash:
```php
echo password_hash('your_password', PASSWORD_DEFAULT);
```

## Next Steps

1. **Add Users:** Use admin dashboard → "Gérer les comptes" → "Ajouter un utilisateur"
2. **Configure Advisors:** Set quota_max for each advisor (default is 5)
3. **Student Workflow:**
   - Students login → Select 3 preferences → Submit
   - Admin runs auto-assignment
   - Admin validates assignments
   - Students can view their assigned advisor

## File Permissions

Ensure proper file permissions (especially on Linux/Mac):
```bash
chmod 755 /path/to/Gestion_Encadrement
chmod 644 /path/to/Gestion_Encadrement/*.php
```

## Production Deployment

Before deploying to production:
1. Change all default passwords
2. Update database credentials
3. Disable error display in PHP
4. Enable HTTPS
5. Add proper backup system
6. Set up regular database backups

---

**Ready to use!** 🚀

For detailed information, see README.md
