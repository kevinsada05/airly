# Architecture & Constraints

## Purpose
A traditional server-rendered Laravel application that ingests static images of geographic areas, runs asynchronous AI analysis to detect pollution, and visualizes zone-level pollution severity on a map.

## Core Data Model (Proposed)

### User
- Standard Laravel user account.

### ImageUpload
Fields (recommended):
- `id`
- `user_id` (nullable if drone/admin)
- `lat`
- `lng`
- `captured_at`
- `file_path`
- `source` (`drone` | `user`)
- `location_accuracy` (optional, meters)
- `status` (`pending` | `processing` | `processed` | `failed`)
- `analysis_version`

### AnalysisResult
Fields (recommended):
- `id`
- `image_upload_id`
- `pollution_detected` (bool)
- `severity` (`green` | `orange` | `red`)
- `confidence`
- `raw_output` (json)
- `model_name` or `model_version`
- `processed_at`

### Zone
Fields (recommended):
- `id`
- `name`
- `polygon` (geojson)
- `current_severity`
- `updated_at`

### ZoneImage (optional)
Used only for caching/manual overrides/admin corrections.
- `zone_id`
- `image_upload_id`

### ZoneHistory
Append-only. Never overwritten. Used for timeline/reporting.
- `zone_id`
- `severity`
- `computed_at`
- `image_count`
- `notes`

## Key Flows
1. Upload → `pending`
2. Queue job → `processing`
3. `AnalysisResult` written
4. `ImageUpload` finalized
5. Zone severity recomputed
6. Map renders latest state

## Processing Architecture
- Laravel queues for async analysis.
- Worker(s) run AI inference or call an internal service.
- Results persisted; no real-time streaming.

## Zone Assignment
Prefer dynamic spatial lookup (point-in-polygon).
`ZoneImage` is optional, for caching or manual overrides only.

## UI Structure (Blade)
- Upload flow (form + status)
- Image detail view (original, annotations, AI result)
- Zone list/detail with history
- Map view with colored polygons
- Admin area for zone management and manual overrides

## Constraints
- Traditional server-rendered Blade views only.
- No mobile app.
- No real-time features (no websockets, no live feed).
- No public APIs.
- Images are static (drone/ground photos).
- Focus on visual evidence and historical tracking.
- Map shows zones only, not live telemetry.

## Non-Functional Considerations
- Storage: local disk or object storage; originals preserved.
- Queue reliability: retries, dead-letter handling.
- Auditability: store AI result versions for reproducibility.
- Performance: thumbnails + pagination for image galleries.
- Security: limit uploads by type/size, sanitize metadata.
- Privacy: location data access restricted to authorized users.
