# Gunslinger – Deployment Guide

Last verified: 2026-11-11. This guide contains no secrets. Wherever a credential is needed, it says where the credential lives.

Anything marked **UNKNOWN** has not been confirmed. Do not guess. Ask Indrajit.

---

## 1. Hosting

| Item | Value | How it was confirmed |
|---|---|---|
| Provider | GoDaddy | Reverse DNS: `73.30.168.184.host.secureserver.net` |
| Product | **GoDaddy VPS** named "GunSlinger": AlmaLinux 9, cPanel & WHM v138, 2 CPU, 4 GB RAM, US West. Apache, with PHP 8.1 via EasyApache `ea-php81`. | GoDaddy hosting dashboard (2026-11-11) |
| Management | **Self-managed.** We are responsible for OS, PHP and security updates. GoDaddy shows a PHP security advisory (CVE-2026-109618/109619) as of 2026-11-11. | GoDaddy Message Center |
| WHM (server admin) | GoDaddy → My Hosting → GunSlinger → **Launch WHM** (opens `https://73.30.168.184.host.secureserver.net:2087`) | Opened 2026-11-11 |
| Server IP | `184.168.30.73` | DNS + GoDaddy API |
| cPanel account (Linux user) | `gunadmin` (home `/home/gunadmin`) | Paths in production error logs |
| Control panel | `https://184.168.30.73:2083` (cPanel login). It can also be reached from GoDaddy account → My Products → Web Hosting → Manage. | HTTP 200, page title "cPanel Login" |
| GoDaddy account that owns the hosting | The same GoDaddy account as the domain (Santosh's login), where Products lists "Gunslinger – Domain, Website". The API key cannot see hosting products (`/v1/shoppers/me` → 403). | GoDaddy Products page |
| cPanel access | No separate password is needed. Go to WHM → List Accounts → `gunadmin` → cP icon. | – |

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

Verified over SSH on 2026-11-11:
- CodeIgniter **4.6.3**, PHP **8.1.35**.
- `app/` and `public_html/` match this repo exactly (checksums compared).
- `vendor/`, `composer.json` and `composer.lock` are owned by **root**, and `composer` is not available to `gunadmin`. Adding or changing a Composer package needs root (WHM).
- `git` and `rsync` are available. Disk use is 19 of 99 GB.
- An earlier code snapshot exists at `~/gunslinger-code-2026-06-21.tgz`.

Required `.env` variable names: `AIRTABLE_BASE_ID`, `AIRTABLE_ACCESS_TOKEN`, `SENDGRID_API_KEY`, `SENDGRID_API_URL`, `UNSUBSCRIBE_SENDGRID_GROUP_ID`.

The base URL is hard-coded in `app/Config/App.php` as `https://gunslinger.cc/`.

## 3. File access

| Method | Host | Port | Username | Auth | Status |
|---|---|---|---|---|---|
| **SSH / SFTP (use this)** | `184.168.30.73` | 22 | `gunadmin` | SSH key `gunslinger_deploy` (ed25519). The private key is on Indrajit's PC at `~/.ssh/gunslinger_deploy`; the public key is authorized in cPanel → SSH Access. Shell is **Jailed Shell** (set in WHM → Manage Shell Access). | **Working.** Tested 2026-11-11. |
| FTP | `184.168.30.73` | 21 | – | – | Port closed/filtered. Use SFTP instead. |
| cPanel File Manager | `https://184.168.30.73:2083` | 2083 | `gunadmin` | cPanel password | Login page reachable. **Login not tested.** |
| Git deploy (cPanel "Git Version Control") | – | – | – | – | **UNKNOWN.** There is no evidence it is used. The repo was created on 2025-08 by copying files *off* the server, which suggests manual uploads. |

## 4. Deploy procedure

How Vishal deployed historically is unknown. There are no deploy scripts or CI. The procedure below uses the SSH access set up on 2026-11-11.

**Always get Indrajit's go-ahead before deploying.**

```bash
SSH="ssh -i ~/.ssh/gunslinger_deploy -o IdentitiesOnly=yes"
HOST=gunadmin@184.168.30.73

# 1. Commit the change on the branch that runs in production
git checkout gunslinger-production-godaddy   # never deploy RVAgency main (old Next.js app)

# 2. Back up the current code on the server
$SSH $HOST 'cd ~ && tar czf ~/backups/code-$(date +%F-%H%M).tgz app public_html/index.php public_html/.htaccess'
#    (create ~/backups once: $SSH $HOST 'mkdir -p ~/backups')

# 3. Dry run: list exactly which files would change, then show this to Indrajit
rsync -rlcvn --exclude='.gitkeep' -e "$SSH" app/ $HOST:app/
rsync -rlcvn -e "$SSH" public_html/index.php public_html/.htaccess $HOST:public_html/

# 4. Real upload (same commands without -n). Never use --delete. Never sync system/, vendor/, writable/ or .env.
rsync -rlcv --exclude='.gitkeep' -e "$SSH" app/ $HOST:app/

# 5. Clear the cache if config or routes changed
$SSH $HOST 'find ~/writable/cache -type f ! -name index.html -delete'
```

- There is no build step and no service restart: PHP picks up file changes immediately.
- Composer changes need root (see Section 2).

**Server-wide changes** (PHP version, OS or cPanel updates, reboot) affect **both** sites on this VPS: gunslinger.cc and leadgensys.cc. Only make them with explicit approval and after a full backup.

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

- **Rollback:** restore the tarball from step 2 of Section 4:
  ```bash
  $SSH $HOST 'cd ~ && tar xzf ~/backups/code-<timestamp>.tgz'
  ```
  Alternatively, check out the previous commit of `gunslinger-production-godaddy` and upload it with rsync.
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
