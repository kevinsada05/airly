# Web Routing & Page Flow

## Public Pages

1. URL: `/`
Method: `GET`
Auth: None
Blade: `home.index`
Purpose: Marketing/landing page with product overview and call-to-action.

2. URL: `/login`
Method: `GET`
Auth: None
Blade: `auth.login`
Purpose: Login form.

3. URL: `/register`
Method: `GET`
Auth: None
Blade: `auth.register`
Purpose: Registration form.

4. URL: `/password/forgot`
Method: `GET`
Auth: None
Blade: `auth.passwords.email`
Purpose: Request password reset link.

5. URL: `/password/reset/{token}`
Method: `GET`
Auth: None
Blade: `auth.passwords.reset`
Purpose: Reset password form.

## Authenticated User Pages

1. URL: `/dashboard`
Method: `GET`
Auth: Required
Blade: `dashboard.index`
Purpose: User overview, recent uploads, and processing status.

2. URL: `/uploads`
Method: `GET`
Auth: Required
Blade: `uploads.index`
Purpose: List user image uploads with status and filters.

3. URL: `/uploads/create`
Method: `GET`
Auth: Required
Blade: `uploads.create`
Purpose: Upload form for image + location + metadata.

4. URL: `/uploads/{upload}`
Method: `GET`
Auth: Required
Blade: `uploads.show`
Purpose: Image detail view with analysis results.

5. URL: `/zones`
Method: `GET`
Auth: Required
Blade: `zones.index`
Purpose: List zones with current pollution severity.

6. URL: `/zones/{zone}`
Method: `GET`
Auth: Required
Blade: `zones.show`
Purpose: Zone detail page with history and evidence images.

7. URL: `/map`
Method: `GET`
Auth: Required
Blade: `map.index`
Purpose: Interactive map showing zone polygons and severity colors.

8. URL: `/account`
Method: `GET`
Auth: Required
Blade: `account.index`
Purpose: Account settings and profile management.

## Admin-Only Pages

1. URL: `/admin`
Method: `GET`
Auth: Admin only
Blade: `admin.dashboard`
Purpose: Admin overview, system health, queue status summary.

2. URL: `/admin/uploads`
Method: `GET`
Auth: Admin only
Blade: `admin.uploads.index`
Purpose: Global upload list with filters and moderation.

3. URL: `/admin/uploads/{upload}`
Method: `GET`
Auth: Admin only
Blade: `admin.uploads.show`
Purpose: Admin view of an upload with audit details.

4. URL: `/admin/zones`
Method: `GET`
Auth: Admin only
Blade: `admin.zones.index`
Purpose: Manage zones and severity overrides.

5. URL: `/admin/zones/{zone}`
Method: `GET`
Auth: Admin only
Blade: `admin.zones.show`
Purpose: Zone admin detail with history and override controls.

6. URL: `/admin/zones/{zone}/edit`
Method: `GET`
Auth: Admin only
Blade: `admin.zones.edit`
Purpose: Edit zone polygon, name, and metadata.

7. URL: `/admin/analysis`
Method: `GET`
Auth: Admin only
Blade: `admin.analysis.index`
Purpose: Review AI model versions and analysis runs.

8. URL: `/admin/analysis/{upload}`
Method: `GET`
Auth: Admin only
Blade: `admin.analysis.show`
Purpose: View raw AI output and model metadata for a specific upload.

9. URL: `/admin/history/zones`
Method: `GET`
Auth: Admin only
Blade: `admin.history.zones`
Purpose: Zone history timeline for reporting and audits.
