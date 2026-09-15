# Webhook multipart upload protocol (version 1)

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

- JSON mode is unchanged: the configured body template is hydrated with the
  legacy catch-all `*` context key, JSON-encoded and sent as before. Protocol
  and idempotency headers are not added in JSON mode. Uploads are never
  snapshotted or read in JSON mode.
- Multipart mode sends the hydrated payload flattened into native PHP/WordPress
  bracket parameter names plus binary file parts, in one POST request.

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

## Failure handling and compatibility changes

Behavior that differs from earlier webhook releases:

1. **Non-2xx responses are errors.** Earlier releases ignored the HTTP status
   and treated any completed transfer as success. The handler now fails on
   every non-2xx response. Error records contain only the status code;
   response bodies and headers are never logged or attached to errors.
2. **Timeout is configurable** per webhook (1–120 seconds, default 20).
   Previously it was fixed at 20 seconds.
3. **Retries** (multipart mode only): transport failures and HTTP 408, 425,
   429, 5xx are retried twice with bounded exponential backoff (0.5 s base,
   30 s cap), honoring `Retry-After`. A 409 is retried only when the response
   body is recognizable as an `acf-rest-upload` in-progress conflict (an
   active idempotency lock); any other 409 fails immediately. JSON mode sends
   exactly once, as before.
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

Automatic transport retries reuse the same operation ID. A new browser
submission creates a new ID and is not a replay of a timed-out submission.
Check the original operation before manually submitting again. The receiver
returns 409 for changed data under an already-used key and 410 when a retained
completed claim points to a deleted resource. Neither response is retried.

## Sender lifecycle (multipart)

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
