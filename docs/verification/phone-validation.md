# Phone validation verification — MFF-323S

## Candidate and environment

- Feature branch: `feat/phone-field`, based on `f79abade5d246501bec8af853f41856f09cc1b8b`, plus the native ACF error-association fix committed with this report.
- Isolated application worktree: `.worktrees/openstack-municipio-dev/mff-323s`, branch `verify/mff-323s`, base `cf68158c4`.
- Disposable database: `mff_323s_verify`. WordPress 7.1.3, ACF 6.8.9, PHP 8.3.35.
- Native server: `http://localhost:8793`; Chrome 156 via the existing local CDP connection.
- Candidate plugin source, vendor dependencies and Vite output were copied into the isolated application. The original development checkout and database were not changed.
- Local MU guard suppresses WordPress mail and external WordPress HTTP requests. This is not a general network-isolation guarantee.

## Observed results

| Criterion | Evidence and result |
| --- | --- |
| Editor can save/reload independent modes | Browser edited field group 3: changed the three fields to Valid/None/Possible, saved, reloaded and checked the selected values. Restored None/Possible/Valid through the same UI and checked reload. Passed. |
| Native malformed request and maxlength | Actual `acf_validate_save_post()` with enabled text fields and maxlength rejected top-level and repeater array values without exception. Exact row/input names and a pre-existing error were retained. Other valid strings were unchanged. Passed. |
| Required/optional/zero | Actual native validation retained the required error for empty required input, accepted empty optional input, and rejected `0`. Passed. |
| Native reject/correct/save | Published test post 7 with `123`: native ACF displayed the phone error. Reached the field using the keyboard, corrected it, submitted, and reloaded. Original formatted string and ordinary text persisted. Passed. Save Draft intentionally bypasses native ACF validation; this test uses Publish/Update. |
| Native accessible association | Initial browser check found no `aria-invalid` or `aria-describedby`. The accompanying fix marks only enabled phone fields and uses ACF invalid/valid actions to associate/remove the native error. Repeated real-browser rejection showed both attributes and the error ID; correction and save/reload passed. |
| Frontend error identity | Form module 20 on page 21 submitted two repeater rows. Only the invalid second row received `aria-invalid` and a nonempty described error; the first row stayed unmarked. REST returned 400 with the library-backed message. Passed. |
| Frontend correction and retention | Reached Return, Previous and the failing input through keyboard traversal, corrected it, and checked that its stale error cleared. Ordinary text and final-step note remained. Passed. Traversal used Tab and Shift+Tab; this is not proof of an uninterrupted forward-only tab order. |
| Strict mode | A possible-but-invalid number passed Possible and failed Valid in actual native validation. Frontend Valid rejection used the same error association; correction produced REST 200 and the success overlay. Passed. |
| Conditional behavior | Conditional required phone field was disabled while its select condition was false. The browser submission succeeded without it, and the database handler did not store it. Changing the condition to true also revealed/enabled the required field; changing it back disabled it again. Passed. |
| Actual handler/storage | Database handler created draft post 23. Readback matched the original national, `+` and `00` strings, both repeater rows, ordinary text and final note. Passed. Separate ACF update/cache-flush/load checks also preserved strings. |

## Checks and evidence locations

Owner executed these checks, without delegating verification:

- `npm test`: 11 suites, 56 tests passed after the native accessibility fix.
- `npm run build`: passed. Main frontend bundle `init.DvUSmbSZ.js`; new native bundle `phone-validation.Dw9V_UI1.js` at verification time.
- Full PHPUnit suite: 112 tests, 298 assertions, 3 loopback multipart-parser tests skipped by the sandbox.
- `git diff --check`: passed.
- Real application checks used `wp --path=<isolated-worktree>/wp --url=http://localhost:8793 eval-file <script>`.
- Browser checks used Python Playwright connected to the existing Chrome CDP endpoint. No request stubbing or replacement phone algorithm was used.

Local reproducibility/debug artifacts (temporary, not repository fixtures):

- `/tmp/mff-browser-fixtures.php`: persisted browser fixtures; do not rerun blindly because it creates posts.
- `/tmp/mff-browser-check.py`, `/tmp/mff-native-browser.py`, `/tmp/mff-editor-check.py`: browser checks.
- `/tmp/mff-final-runtime.php`: native validation and actual handler readback assertions.
- `/tmp/mff-browser-evidence/`: rejected/corrected frontend and native screenshots, plus editor reload screenshot.
- `/tmp/mff-browser-check.log`, `/tmp/mff-native-browser.log`, `/tmp/mff-final-runtime.log`: results.

## Limits and observations

This is one local WordPress/ACF/Chrome integration, not an exhaustive browser, handler or deployment matrix. The browser could reach port 8793 although shell curl could not. Munidev initially reported uncertain process observation; no duplicate start or signal was sent.

The copied environment's custom select interrupted forward Tab traversal; reverse traversal reached the controls and phone input. This behavior is outside the changed phone component and was not repaired or represented as a clean forward-tab-order result. The test demonstrates a keyboard correction route, not universal keyboard behavior of the host theme.

No email/webhook destination was exercised. No push, merge or deployment occurred. The pre-existing development dependency advisory and target PHP/root dependency compatibility remain predeployment concerns. Keep the isolated server/database until the user finishes inspection; removal requires checking current resource identity.
