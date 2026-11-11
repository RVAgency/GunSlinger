# Gunslinger – Deployment Guide

Last verified: 2026-11-11. This guide contains no secrets. Wherever a credential is needed, it says where the credential lives.

Anything marked **UNKNOWN** has not been confirmed. Do not guess. Ask Indrajit.

---

## 1. Hosting

| Item | Value | How it was confirmed |
|---|---|---|
| Provider | GoDaddy | Reverse DNS: `73.30.168.184.host.secureserver.net` |
| Product | cPanel hosting (Linux, Apache, PHP 8.1 via EasyApache `ea-php81`) | cPanel login page on port 2083. `public_html/.htaccess` sets the `ea-php81` handler. |
| Shared hosting or VPS | **UNKNOWN — needs confirmation from Indrajit.** Port 2087 (WHM) also answers, but GoDaddy shared servers expose it too. | – |
| Server IP | `184.168.30.73` | DNS + GoDaddy API |
| cPanel account (Linux user) | `gunadmin` (home `/home/gunadmin`) | Paths in production error logs |
| Control panel | `https://184.168.30.73:2083` (cPanel login). It can also be reached from GoDaddy account → My Products → Web Hosting → Manage. | HTTP 200, page title "cPanel Login" |
| GoDaddy account that owns the hosting | **UNKNOWN — needs confirmation from Indrajit.** It is probably the same "old dev" account that holds the domain (see Section 6). The API key cannot see hosting products (`/v1/shoppers/me` → 403). | – |
| cPanel password | **UNKNOWN location — needs confirmation from Indrajit.** It is not stored in this repo. | – |

## 2. Server layout

These paths come from `app/Config/Paths.php`, `app/Config/Constants.php` and the production logs.

```
/home/gunadmin/
├── public_html/        # web root (index.php, .htaccess, uploads/)  ← in Git
├── app/                # CodeIgniter application code               ← in Git
├── system/             # CodeIgniter 4 framework                     ← NOT in Git
├── vendor/             # Composer packages (PhpSpreadsheet etc.)     ← NOT in Git (expected path: ROOTPATH/vendor)
├── writable/           # cache, logs, session, uploads/excels/       ← runtime data, NOT in Git
└── .env                # API keys (Airtable, SendGrid)               ← NOT in Git
```

Do **not** overwrite `system/`, `vendor/`, `writable/` or `.env` during a deploy. They exist only on the server.

Required `.env` variable names: `AIRTABLE_BASE_ID`, `AIRTABLE_ACCESS_TOKEN`, `SENDGRID_API_KEY`, `SENDGRID_API_URL`, `UNSUBSCRIBE_SENDGRID_GROUP_ID`.

The base URL is hard-coded in `app/Config/App.php` as `https://gunslinger.cc/`.

## 3. File access

| Method | Host | Port | Username | Auth | Status |
|---|---|---|---|---|---|
| SSH / SFTP | `184.168.30.73` (or `gunslinger.cc`) | 22 | `gunadmin` (assumed: cPanel user) | **UNKNOWN** (password or key). The credential location is **UNKNOWN — needs confirmation from Indrajit.** | Port open, banner `SSH-2.0-OpenSSH_9.9`. **Login not tested** (no credential available). On GoDaddy shared hosting, SSH must be enabled in cPanel first. |
| FTP | `184.168.30.73` | 21 | – | – | Port closed/filtered. Use SFTP instead. |
| cPanel File Manager | `https://184.168.30.73:2083` | 2083 | `gunadmin` | cPanel password | Login page reachable. **Login not tested.** |
| Git deploy (cPanel "Git Version Control") | – | – | – | – | **UNKNOWN.** There is no evidence it is used. The repo was created on 2025-08 by copying files *off* the server, which suggests manual uploads. |

## 4. Deploy procedure

**How Vishal deployed historically: UNKNOWN — needs confirmation from Indrajit.** No deploy scripts, CI config or Git hooks exist in this repo.

The procedure below is a **proposed** procedure. Nobody has tested it yet. Confirm file access (Section 3) before using it.

1. Work on branch `gunslinger-production-godaddy`. This is the code that runs in production.
   - The `main` branch on `RVAgency/GunSlinger` is a separate, older Next.js app. Never deploy it here.
2. Back up first (see Section 5). Download `/home/gunadmin/app` and `/home/gunadmin/public_html`, or make a cPanel backup.
3. Upload **only the changed files** under `app/` and/or `public_html/` to the same paths under `/home/gunadmin/`, using SFTP or cPanel File Manager.
4. There is no build step. This is plain PHP, and the front-end uses CDN scripts.
5. If you added a Composer package, run `composer install --no-dev` in `/home/gunadmin` over SSH. Whether `composer` exists on the server is **UNKNOWN**.
6. Clear the cache if config or routes changed: delete the contents of `/home/gunadmin/writable/cache/` but keep `index.html`. No service restart is needed or possible on shared hosting.

## 5. Verification

Run these after a deploy:

```bash
curl -s -o /dev/null -w "%{http_code}\n" https://gunslinger.cc/            # expect 200
curl -s https://gunslinger.cc/ | grep -o "<title>[^<]*"                    # expect "<title>Login"
curl -s -o /dev/null -w "%{http_code} %{redirect_url}\n" https://gunslinger.cc/campaigns   # expect 302 https://gunslinger.cc/login
```

Note: `curl -I` (HEAD) returns **404**. That is normal, because routes are GET-only. Always use GET.

Then do a manual check:
1. Log in.
2. Confirm the Campaign page loads the SendGrid templates, recipient groups and senders.
3. Confirm the Campaigns list loads.

Also check `/home/gunadmin/writable/logs/log-<today>.log` for new `CRITICAL` entries.

## 6. Rollback and backups

- **Rollback:** re-upload the files from the backup taken in step 2 of Section 4, or check out the previous commit of `gunslinger-production-godaddy` and upload those files.
- **Existing backups:** **UNKNOWN — needs confirmation from Indrajit.** It is not known whether GoDaddy automatic backups (cPanel → Backup) are enabled. The only off-server copy of the code is this Git repo, and it excludes `system/`, `vendor/`, `.env` and `writable/`.
- **Recommended:** take one full cPanel backup now and store it outside the server.

## 7. Domain and DNS

The domain is in the **old dev GoDaddy account**. That account also holds geomap.cc, leadgensys.cc and rva-dev.com. The account was confirmed via `GET /v1/domains`.

DNS is managed with the GoDaddy API, using the **dev-account key**. This key is **NOT** the key in the LeadGenSys app's active config; that one belongs to the funeral-home account, which cannot see gunslinger.cc. The dev key is kept as `GODADDY_API_KEY` / `GODADDY_API_SECRET` in Indrajit's local `gunslinger/.env`, which git ignores. It is also kept, commented out, in `leadgensys-rva/.env`.

| Field | Value |
|---|---|
| Status | ACTIVE |
| Created | 2025-07-26 |
| Expires | 2027-07-26 (auto-renew **on**) |
| Privacy / lock / transfer protection | on / on / on |
| Nameservers | `ns17.domaincontrol.com`, `ns18.domaincontrol.com` |

Current DNS records:

| Type | Name | Value | TTL |
|---|---|---|---|
| A | @ | `184.168.30.73` | 600 |
| NS | @ | ns17 / ns18.domaincontrol.com | 3600 |
| CNAME | www | @ | 3600 |
| CNAME | pay | paylinks.commerce.godaddy.com (GoDaddy default) | 3600 |
| CNAME | _domainconnect | _domainconnect.gd.domaincontrol.com (GoDaddy default) | 3600 |
| TXT | _dmarc | `v=DMARC1; p=reject; adkim=r; aspf=r; rua=mailto:dmarc_rua@onsecureserver.net;` | 3600 |

There are no MX, SPF or SendGrid DKIM records. That is expected: the app sends from `mobilefuneralservice.com` and `resthavenbatonrouge.com`, not from gunslinger.cc.

Read-only check (put the key and secret in environment variables, never in a file in this repo):

```bash
curl -s -H "Authorization: sso-key $GODADDY_API_KEY:$GODADDY_API_SECRET" \
  https://api.godaddy.com/v1/domains/gunslinger.cc/records
```

## 8. Open questions for Indrajit

1. Is the hosting cPanel shared or a VPS, and which GoDaddy account and login owns it?
2. Where are the cPanel and SSH credentials stored?
3. How were deploys done before (File Manager, SFTP or Git)?
4. Are automatic backups enabled?
