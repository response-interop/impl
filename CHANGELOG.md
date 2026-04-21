# Change Log

## NEXT

- `ResponseSender` implements `ResponseBodySenderService`.
- `FileResponseBody` and `JsonResponseBody` type-hint `ResponseBodySenderService`.
- `ResponseSender::sendResponseBodyResource()` rejects negative `$length` and `$offset`.
- `ResponseHeaders` regex allows underscores; minimum length 1 per RFC 3864 §4.1.
- `ResponseCookieHelper` lowercases parsed cookie attribute names.
- Drop unused `StreamInterop` import in `Response`.
- Require `php >=8.4` (was `^8.4`).
- Add `pmjones/php-styler`; composer scripts `cs-fix`, `cs-check`, `cs-clear`; style check wired into `check`.
- Expand README with install, usage, and class ↔ interface mapping.

## 1.0.0-dev1

Initial release for private review.

