# Webhook multipart upload profiles

This document describes the outgoing webhook formats of the sender side of the
frontend-form webhook integration, the opt-in multipart mode for file/image
uploads, and the compatibility changes introduced with it.

The matching receiver is implemented separately (the generic `acf-rest-upload`
receiver). Sender and receiver can be deployed independently because multipart
is opt-in per webhook and JSON remains the default.

## Request modes

| Mode | Default | Content-Type | File upload support |
|------|---------|--------------|---------------------|
| `json` | Yes | `application/json` | No |
| `multipart` | No (per-webhook `Request Format` setting) | `multipart/form-data; boundary=...` | Yes |
| `multipart-create` | No (per-webhook `Request Format` setting) | `multipart/form-data; boundary=...` | Yes, for compatible version-2 creates |

- JSON mode is unchanged: the configured body template is hydrated with the
  legacy catch-all `*` context key, JSON-encoded and sent as before. Protocol
  and idempotency headers are not added in JSON mode. Uploads are never
  snapshotted or read in JSON mode.
- Both multipart profiles use the existing encoder for bracket parameters and binary file parts.

## Protocol v2 (multipart-create)

Select `multipart-create` only for a compatible create-only receiver.
Sponsor destinations accept collection POST creates with at most one referenced image per request.
Updates, galleries, nested uploads, and generic files are not supported by that receiver.

The sender controls these headers:

```text
Content-Type: multipart/form-data; boundary=<boundary>
X-ACF-Rest-Upload-Version: 2
```

The value `2` is the application protocol version. It does not require HTTP/2 transport.
The sender removes configured `Idempotency-Key` headers and sends no replacement.
Configured content-type and protocol-version headers cannot override these values.
Other configured headers, including authentication, remain available.

An image template such as `{"acf":{"image":"{{image.0}}"}}` produces an ordinary `acf[image]` parameter.
Its scalar value is `$file:<key>`. The selected bytes appear in `_acf_rest_files[<key>]`.
The key is an internal reference, not a destination identity.
Absent optional image inputs use the omission rule described below.
Existing attachment IDs remain ordinary values and require native receiver validation.

The sender performs exactly one HTTP transport attempt.
Transport errors and all non-2xx responses fail the handler without retry or JSON fallback.
This includes timeouts, HTTP 425, HTTP 429, HTTP 5xx, and incompatible-version rejections.
A timeout does not prove that the receiver saved nothing.
Every manual or browser resubmission is independent and can duplicate posts, images, and notifications.
There is no duplicate-prevention or crash-recovery guarantee. Check the destination before resubmitting.

Both profiles preserve snapshots across Database handling and remove them after webhook success or failure.
Preparation failures stop transport. The 8 MiB aggregate file limit and 1 MiB parameter limit apply to both profiles.
A lower origin upload limit takes precedence.

The shared encoder retains version-1 null and empty-list markers.
The version-2 receiver rejects those reserved markers. Do not configure null or empty-list templates for this profile.
Do not use version-1 gallery or clearing features with the version-2 receiver.

## Protocol v1 (multipart)

### Headers

The sender controls these headers itself; configured webhook headers with the
same name (comparison is case-insensitive and whitespace-trimmed) are ignored:

```text
Content-Type: multipart/form-data; boundary=<boundary>
Idempotency-Key: <random UUID v4, reused for retries of one submission>
X-ACF-Rest-Upload-Version: 1
```

The idempotency UUID contains no form data. Other configured headers (for
example `Authorization`) are passed through.

### Parameter names

- Nested maps flatten to bracket names: `acf[image]`, `title[raw]`.
- Numeric list positions are preserved as explicit index segments:
  `acf[gallery][0]`, `acf[gallery][1]`. The sender never emits `[]` for list
  entries, so paths are deterministic end to end (mixed galleries keep
  existing attachment IDs and new uploads at their exact positions).
- Keys containing `[` or `]` cannot be represented unambiguously and abort the
  send with an error.

### File references and file parts

- A field value `$file:<key>` references an upload. The literal token stays as
  the field value; the binary is sent exactly once as
  `_acf_rest_files[<key>]`. The key is restricted to `[A-Za-z0-9_-]+`.
- Mixed galleries are supported: existing destination attachment IDs and
  `$file:` references coexist in one list, positions preserved.
- Snapshot files never referenced by the hydrated payload are **not** sent
  (the receiver would reject them with HTTP 400).

### Null and empty values

Multipart fields cannot represent JSON `null` or an empty list directly, so
both are signalled through reserved lists of bracket paths:

```text
_acf_rest_nulls[] = acf[optional_image]
_acf_rest_empty[] = acf[gallery]
```

- `_acf_rest_nulls[]`: inject an explicit `null` at the path (clear the field).
- `_acf_rest_empty[]`: apply an explicit empty list at the path.
- A payload key colliding with a reserved field name
  (`_acf_rest_nulls`, `_acf_rest_empty`, `_acf_rest_files`) aborts the send.
- A field that is both explicitly `null` in the submitted data and the target
  of a file reference is a conflict; the send aborts before any request.
- An exact image template reference, such as `{{image.0}}`, omits its
  destination property when ACF identifies the source as an image field and
  its submitted value is empty with no selected upload. This avoids sending an
  empty string for an unselected optional image. Selected image fields keep
  their own destination property and binary part. This rule applies only to
  multipart image references. JSON hydration and non-image empty strings,
  `false`, zero and empty lists retain their existing behavior.
  Completely absent inputs are identified using the form's registered field
  keys, so omission does not depend on ACF finding a saved value by field name.
  Explicit submitted nulls retain their legacy multipart representation; this
  omission rule does not introduce an image-clearing operation.

## Failure handling and compatibility changes

Behavior that differs from earlier webhook releases:

1. **Non-2xx responses are errors.** Earlier releases ignored the HTTP status
   and treated any completed transfer as success. The handler now fails on
   every non-2xx response. Error records contain only the status code;
   response bodies and headers are never logged or attached to errors.
2. **Timeout is configurable** per webhook (1–120 seconds, default 20).
   Previously it was fixed at 20 seconds.
3. **Retries** (`multipart` version 1 only): transport failures and HTTP 408, 425,
   429, 5xx are retried twice with bounded exponential backoff (0.5 s base,
   30 s cap), honoring `Retry-After`. A 409 is retried only when the response
   body is recognizable as an `acf-rest-upload` in-progress conflict (an
   active idempotency lock); any other 409 fails immediately. JSON mode sends
   exactly once, as before. `multipart-create` also sends exactly once.
4. **Aggregate upload guard**: the total size of referenced uploads is checked
   against the lower of the origin `wp_max_upload_size()` and **8 MiB** before
   the in-memory body is built. JSON-encoded multipart parameter data is
   limited to **1 MiB**, including requests without files. Uploads with an
   undeterminable size fail closed. Reserve at least 256 MiB of PHP memory
   for the buffered transport and normal image processing; arbitrary-size
   files and unbounded decoded image dimensions are not supported.
5. **Snapshot failures abort the send**: a selected upload (HTTP `UPLOAD_ERR_OK`)
   that cannot be read or copied to a snapshot fails the handler instead of
   being silently dropped. Snapshots are cleaned up in a `finally` handler and
   by a shutdown guard.

In version 1, automatic transport retries reuse the same operation ID. A new browser
submission creates a new ID and is not a replay of a timed-out submission.
Check the original operation before manually submitting again. The receiver
returns 409 for changed data under an already-used key and 410 when a retained
completed claim points to a deleted resource. Neither response is retried.

## Sender lifecycle (both multipart profiles)

1. Existing validators run first; uploads are snapshotted before handlers.
2. `$file:` references are merged into the hydration context, keyed by ACF
   field key and field name; gallery references keep their numeric indexes and
   merge with submitted existing attachment IDs.
3. The configured JSON body template is hydrated (same template UX as JSON
   mode; no second file-mapping configuration).
4. The payload is flattened, null/empty paths are emitted, unreferenced files
   are dropped, the aggregate limit is enforced, and the body is encoded.
5. Protocol headers are added, the request is sent through the WordPress HTTP
   API with the retry policy above, and snapshots are removed afterwards.
