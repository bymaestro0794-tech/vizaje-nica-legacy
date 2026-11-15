# Legacy creator cabinet ↔ Vizaje Partners

M5.5.2 keeps the Partners grant and service credentials on the Legacy server.
The browser talks only to same-origin `/account/creator-link/*` endpoints.
The grant is encrypted in `creator_partner_links`; it is never rendered into
HTML, returned by a Legacy endpoint, or written to logs. HMAC uses the separate
`LEGACY_TO_PARTNERS_HMAC_KEYS` key ring and the Partners M5.3B.1B.1 contract.

## Manual database patch

Review and apply `database/patches/2026-11-16-create-creator-partner-links.sql`
manually after a backup. The patch is not run by the application. It creates
one InnoDB mapping per Legacy client and per Partners identity. No foreign key
is declared because installations may differ in the signedness of `clients.id`.

## Private configuration

Copy `docs/examples/partners-creator.private.php.example` to a PHP file outside
the web document root, for example:

`/home/ACCOUNT/private/partners-creator.php`

Set `LEGACY_PARTNERS_PRIVATE_CONFIG` in the PHP runtime to that absolute path.
The file must return the same array structure as the example. Replace both
public placeholders with independently generated values before enabling the
integration:

- `hmac_keys[].secret`: unpadded Base64URL encoding of at least 32 random
  bytes, with exactly one active key.
- `grant_encryption_keys[].secret`: unpadded Base64URL encoding of exactly 32
  random bytes, with exactly one active key.
- Configure the matching HMAC key on Partners and set `enabled` to `true` only
  after the dev handshake is ready.
- `redirect_uri` must exactly match the registered callback for the environment:
  `https://dev.vizaje-nica.com/account/creator-link/callback` or
  `https://vizaje-nica.com/account/creator-link/callback`.

The loader rejects a configured file that is missing, unreadable, inside the
document root, emits output, or returns invalid values. It does not fall back
to environment secrets when an explicit private path is configured but invalid.
Existing environment variables remain supported when no private path is set.
Never copy the example placeholders into an enabled configuration.

For local development, use local-only secrets and the local HTTP callback only
if the Partners dev environment explicitly registers it. Do not reuse production
keys locally.

## Flow and boundaries

1. An authenticated `is_creator` Legacy user starts linking with CSRF. Legacy
   stores PKCE verifier and state in the PHP session and sends a signed initiate
   request.
2. The browser visits only the validated Partners authorize URL.
3. The callback checks the current Legacy session, client ID, session ID, state,
   age, and PKCE verifier before exchanging the one-time code server-to-server.
4. Legacy validates the exchange response and stores the encrypted grant and
   identity mapping. A clean account URL is loaded after the callback.
5. Dashboard, links, revoke, and unlink requests are made server-to-server with
   the grant and current `clients.id`; the browser receives only allowlisted
   presentation data.

Creator status is checked against Partners before dashboard access and each
mutating link operation. An unavailable Partners API leaves the storefront
usable and displays unavailable states. This work does not change checkout,
prices, referral attribution, commissions, or payouts.

## Verification boundary

Standalone tests cover the local protocol, validation, crypto, URL allowlists,
presenters, and private config behavior without a database or network. They do
not prove the live Partners payload shape or account-linking E2E. Before enabling
the dev flag, run the linking and dashboard flow against the Partners dev API
with a dedicated dev key and confirm the analytics response fields against its
actual contract. Apply the SQL patch only to the intended dev database first.
