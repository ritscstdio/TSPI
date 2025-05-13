# User Authentication System Setup

This directory contains all the files needed for the user authentication system in TSPI.

## Database Setup

To set up the database tables needed for the authentication system:

1. Open PHPMyAdmin (http://localhost/phpmyadmin/)
2. Select your database (tspi_blog)
3. Click on the "Import" tab
4. Click "Browse" and select the file `setup_auth_tables.sql` from this directory
5. Click "Go" to execute the SQL commands

Alternatively, you can:
1. Open the SQL tab in PHPMyAdmin
2. Copy and paste the contents of `setup_auth_tables.sql`
3. Click "Go" to execute the SQL commands

## What This System Provides

- User registration with email verification
- Secure login with password hashing
- User profile management
- Role-based access control (user, editor, admin)
- Password reset functionality

## Files In This Directory

- **login.php**: User login form
- **signup.php**: User registration form
- **verify.php**: Email verification page
- **profile.php**: User profile page
- **logout.php**: Logout functionality
- **setup_auth_tables.sql**: SQL to create required database tables

## Important Notes

The authentication system is designed for regular users, not just administrators. Users who sign up through the public form are assigned the 'user' role by default.

The system works seamlessly with your existing TSPI users table and can be used to implement commenting, user profiles, and other user-specific features. 