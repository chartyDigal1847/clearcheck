# SSO Integration Guide

## Current Setup

The system is now ready for SSO (Single Sign-On) integration. All authentication logic has been prepared with temporary user structures that can be easily replaced with your SSO provider.

## Database Structure

### Tables Ready for SSO:
- **users** - Main user table with role-based access (admin, student, clearance_checker)
- **students** - Student-specific data (grade_level, section, clearance_status)
- **clearance_checkers** - Checker-specific data (department, review statistics)
- **document_uploads** - Document tracking and approval workflow

### User Roles:
1. **admin** - Full system access, can view all uploads and manage users
2. **student** - Can view their clearance status and upload documents
3. **clearance_checker** - Can review and approve/reject documents for their department

## SSO Integration Points

### 1. Controllers to Update

Replace the temporary user methods in these controllers:

#### `app/Http/Controllers/AdminDashboardController.php`
```php
private function getAdminUser()
{
    // TODO: Replace with SSO authenticated user
    // Example:
    // return auth()->user();
}
```

#### `app/Http/Controllers/StudentDashboardController.php`
```php
private function getStudent()
{
    // TODO: Replace with SSO authenticated user
    // Example:
    // return auth()->user()->student;
}
```

#### `app/Http/Controllers/CheckerDashboardController.php`
```php
private function getChecker()
{
    // TODO: Replace with SSO authenticated user
    // Example:
    // return auth()->user();
}
```

### 2. Routes to Protect

Add your SSO middleware to these route groups in `routes/web.php`:

```php
// Example with SSO middleware:
Route::middleware(['sso.auth'])->prefix('clearanceport')->group(function () {
    // All routes here
});
```

### 3. User Creation Flow

When a user logs in via SSO for the first time:

1. **Check if user exists** in the `users` table
2. **Create user record** if not exists:
   ```php
   User::create([
       'name' => $ssoUser->name,
       'email' => $ssoUser->email,
       'role' => $ssoUser->role, // admin, student, or clearance_checker
   ]);
   ```
3. **Create role-specific record**:
   - If student: Create record in `students` table
   - If checker: Create record in `clearance_checkers` table

### 4. Departments for Clearance Checkers

The system supports these departments:
- **Library**
- **Finance**
- **Exams & Records**

Assign checkers to their respective departments during SSO user creation.

### 5. Student Grade Levels

Valid grade levels: **7, 8, 9, 10, 11, 12**

Students should have:
- `grade_level` (7-12)
- `section` (A, B, C, etc.)
- `reg_no` (unique registration number, e.g., HS2024001)

## Testing Without SSO

If you need to test the system before SSO is implemented:

1. Uncomment the seeders in `database/seeders/DatabaseSeeder.php`
2. Run: `php artisan migrate:fresh --seed`
3. This will create test users for all roles

## Next Steps

1. Install and configure your SSO provider package
2. Update the controller methods to use `auth()->user()`
3. Add SSO middleware to routes
4. Implement user creation logic for first-time SSO logins
5. Map SSO user attributes to the correct roles and departments

## Support

For questions about the clearance system structure, refer to:
- Database migrations in `database/migrations/`
- Models in `app/Models/`
- Controllers in `app/Http/Controllers/`
