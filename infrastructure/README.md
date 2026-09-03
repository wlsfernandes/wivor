# Private media bucket setup

The disk selected by `WIVOR_MEDIA_DISK` must point to a private S3 bucket with Block Public Access and default server-side encryption enabled. The application issues short-lived, exact-key upload and delivery URLs; originals do not have public URLs.

Apply `s3-media-cors.json` to permit production browsers to use the signed `PUT` URLs. Add local development origins only in non-production buckets. Apply `s3-media-lifecycle.json` to abort incomplete multipart uploads after one day and remove abandoned objects under `temporary-uploads/` after two days. Merge these rules with any existing bucket lifecycle configuration rather than replacing unrelated rules.

The queue worker must be running for validation and derivative generation (`php artisan queue:work --timeout=180`), and Laravel's scheduler must run every minute so the scheduled daily `media:enforce-retention` command can send warnings and delete eligible media.
