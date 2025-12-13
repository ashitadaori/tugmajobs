# Debug Company Logos

## Check if companies have logos in database

Run this in your Laravel Tinker or create a test route:

```php
// Check all employers and their logos
$employers = \App\Models\User::where('role', 'employer')
    ->with('employerProfile')
    ->get();

foreach($employers as $employer) {
    echo "Company: " . $employer->name . "\n";
    echo "Has Profile: " . ($employer->employerProfile ? 'Yes' : 'No') . "\n";
    
    if($employer->employerProfile) {
        echo "Company Logo Field: " . ($employer->employerProfile->company_logo ?? 'NULL') . "\n";
        echo "Logo URL: " . ($employer->employerProfile->logo_url ?? 'NULL') . "\n";
    }
    echo "---\n";
}
```

## Or check in database directly:

```sql
SELECT 
    users.name as company_name,
    employer_profiles.company_logo,
    employer_profiles.company_name as profile_company_name
FROM users
LEFT JOIN employer_profiles ON users.id = employer_profiles.user_id
WHERE users.role = 'employer';
```

## Possible Issues:

1. **No logos uploaded yet** - Companies haven't uploaded logos
2. **Wrong field name** - Database uses different column name
3. **Wrong path** - Logo path is incorrect in database
4. **Storage link missing** - Run: `php artisan storage:link`

## Quick Fix - Test with one company:

1. Login as an employer
2. Go to Profile Settings
3. Upload a company logo
4. Then check admin Companies page

## Alternative: Show user profile image instead

If you want to show the user's profile image instead of company logo, I can update the code to use `$company->image` instead.

Let me know what you find!
