# Admin Page Titles Fixed

## Issue:
All admin pages showed "Dashboard" as the title, even when on different pages like "Jobs Management" or "KYC Verifications".

## Solution:
Added `@section('page_title', 'Page Name')` to each admin page view.

## Files Updated:

### 1. Jobs Management Pages:
- `resources/views/admin/jobs/index.blade.php` → "Jobs Management"
- `resources/views/admin/jobs/create.blade.php` → "Post New Job"
- `resources/views/admin/jobs/pending.blade.php` → "Pending Jobs"

### 2. KYC Page:
- `resources/views/admin/kyc/didit-verifications.blade.php` → "KYC Verifications"

### 3. User Management:
- Already had correct title: "User Management"

## How It Works:

The layout file (`resources/views/layouts/admin.blade.php`) uses:
```php
<h4 class="mb-0">@yield('page_title', 'Dashboard')</h4>
```

Each page now defines its title:
```php
@section('page_title', 'Jobs Management')
```

## Result:

Now when you navigate to:
- **Jobs Management** → Shows "Jobs Management" at top
- **Post New Job** → Shows "Post New Job" at top
- **Pending Jobs** → Shows "Pending Jobs" at top
- **KYC Verifications** → Shows "KYC Verifications" at top
- **Dashboard** → Shows "Dashboard" at top (default)

---

**Status:** ✅ Fixed
**Date:** October 27, 2025
