# Gunslinger – Project Status Report

**Application:** Gunslinger (bulk email campaign tool)
**Live URL:** https://gunslinger.cc
**Prepared:** 11 November 2026
**Prepared by:** Indrajit

---

## 1. Summary

Gunslinger is a web app for sending bulk email campaigns. A user logs in, uploads an Excel list of email addresses, picks a SendGrid email template and a sender address, and sends the campaign. Each campaign is recorded in Airtable.

**Current status:**

- The live site at https://gunslinger.cc is **up and working**. Login was tested on 11 Nov 2026, and the Campaign page loads its SendGrid templates.
- We have the **application source code** in GitHub.
- **Confirmed:** the live app is hosted on **GoDaddy cPanel hosting**, not on Vercel. The domain gunslinger.cc is registered in our older GoDaddy development account and is set to auto-renew. Details are in Section 5.
- **Still missing:** the hosting (cPanel) login, server file access (SSH/SFTP), and a backup of the files that exist only on the server, including the production settings file (`.env`). Until we have these, we can view the site but cannot safely update it.

---

## 2. What the App Does

| Step | What happens |
|---|---|
| 1. Login | User signs in with email and password. Users are stored in Airtable. |
| 2. Upload recipients | User uploads an Excel file (.xls/.xlsx). Email addresses must be in **column B**, and the first row is treated as a header. The file is saved on the server and a "Recipient Group" record is created in Airtable. |
| 3. Create campaign | User enters a subject and campaign name, then picks a recipient group, a sender email and a SendGrid template. |
| 4. Send | The app sends the emails through SendGrid in batches of 1,000, with an unsubscribe group attached. |
| 5. History | The campaign (name, sender, group, subject, emails sent/failed) is saved to Airtable and shown on the **Campaigns** page. |

---

## 3. Technology Used

| Area | Technology |
|---|---|
| Main application (live) | PHP 8.1, CodeIgniter 4 framework |
| Front-end | HTML, Tailwind CSS, jQuery |
| Email sending | **SendGrid** (Twilio SendGrid) API, using dynamic templates |
| Database | **Airtable** (no SQL database is used) |
| Excel reading | PhpSpreadsheet library |
| Hosting | **GoDaddy** cPanel hosting (Linux, Apache, PHP 8.1) |

### Airtable tables used

| Table | Purpose |
|---|---|
| `auth` | App users (email, hashed password, name, status) |
| `recipient_group` | Uploaded email lists (name, number of emails, file name) |
| `email_domains` | Sender email addresses |
| `campaigns` | History of sent campaigns |

### Settings the app needs (kept in a `.env` file on the server, not in GitHub)

| Setting | Purpose |
|---|---|
| `AIRTABLE_BASE_ID` | Which Airtable base to use |
| `AIRTABLE_ACCESS_TOKEN` | Airtable access key |
| `SENDGRID_API_KEY` | SendGrid access key |
| `SENDGRID_API_URL` | SendGrid send endpoint |
| `UNSUBSCRIBE_SENDGRID_GROUP_ID` | SendGrid unsubscribe group |

---

## 4. Code Repositories

| Repository | Branch | Contents |
|---|---|---|
| RVAgency/GunSlinger | `gunslinger-production-godaddy` | **Live PHP app** (copied from the production server). This is the version running on gunslinger.cc. |
| RVAgency/GunSlinger | `main` | An **earlier Next.js version** of the app (August 2025). It was built for Vercel and uses the same Airtable and SendGrid services. It is **not** the version running on gunslinger.cc. |

> **Note on Vercel:** The Vercel deployment mentioned earlier most likely refers to the Next.js version on the `main` branch. The live site runs on the PHP version. The Vercel account and project still need to be checked.

---

## 5. Production (Live) Setup

### Domain – gunslinger.cc

| Item | Value |
|---|---|
| Registrar | GoDaddy (older development account, which also holds leadgensys.cc and geomap.cc) |
| Registered | 26 July 2025 |
| Expires | 26 July 2027 – **auto-renew is ON** |
| Privacy / transfer lock | ON / ON |
| DNS managed at | GoDaddy (ns17 / ns18.domaincontrol.com) |
| Points to | `184.168.30.73` (GoDaddy server); `www.gunslinger.cc` points to the same place |

### Hosting server

| Item | Value |
|---|---|
| Type | **GoDaddy VPS** (self-managed): AlmaLinux 9, cPanel & WHM, 2 CPU, 4 GB RAM, US West |
| cPanel account | `gunadmin` |
| Control panel | GoDaddy → My Hosting → GunSlinger → **Launch WHM**, then the `gunadmin` cPanel |
| Security | GoDaddy has flagged a PHP security update for this server. Because the VPS is self-managed, we must apply it ourselves, after taking a backup. |
| File access | SSH/SFTP is available on the server, but **we do not have the login yet** |
| Website status | Live. The login page and the redirect for logged-out users were checked on 11 Nov 2026. |

### How the app is deployed

- **There is no automatic deployment.** No build tools, pipelines or deploy scripts exist. Files are placed directly on the server, and most likely were uploaded by hand through cPanel or SFTP. The exact method used previously still needs to be confirmed.
- **No build step is needed.** It is a plain PHP application.
- Only part of the app is in GitHub. These parts exist **only on the server**:
  - the CodeIgniter framework (`system/`)
  - add-on libraries (`vendor/`)
  - the settings file with the Airtable and SendGrid keys (`.env`)
  - uploaded Excel recipient lists (`writable/uploads/`)
- **Backups:** we have not yet confirmed whether GoDaddy backups are enabled. Taking a full backup is the first step before any change.

### Email (SendGrid) note

The app sends email from `mobilefuneralservice.com` and `resthavenbatonrouge.com`, not from gunslinger.cc. The SendGrid sender setup therefore lives on those two domains. gunslinger.cc itself is not set up to send email.

A full technical deployment guide is in the repository at `docs/DEPLOYMENT.md`.

---

## 6. Issues Found in the Code

| # | Issue | Impact | Priority |
|---|---|---|---|
| 1 | The hosting login, server access and the `.env` settings file are not available to us. | We cannot redeploy or fix the live site until we get access. | **High** |
| 2 | The CodeIgniter framework files (`system/`) and libraries (`vendor/`) are not in GitHub. They exist only on the server. | The app cannot be set up again from GitHub alone. | **High** |
| 3 | A hidden test link on the login page shows server configuration details to anyone. | Security risk. It should be removed. | **High** |
| 4 | Only two sender addresses are allowed (`service@mobilefuneralservice.com` and `service@resthavenbatonrouge.com`), and they are fixed in the code. | Adding a new sender needs a code change. | Medium |
| 5 | The "name" field sent to every email template is fixed to "Vishal". | Wrong personalisation if a template uses the name. | Medium |
| 6 | Some error messages (missing file or template) would crash the request instead of showing a proper message. | Confusing errors for users. | Medium |
| 7 | All emails are sent while the browser waits. | Very large lists may time out. | Medium |
| 8 | The "Sign up" link on the login page does nothing. Users must be added directly in Airtable. | Minor. | Low |
| 9 | The `/dashboard` page is broken (missing layout file). It is not used in normal flow. | Minor. | Low |

---

## 7. Next Steps

1. ~~Check the domain and DNS in GoDaddy~~ – **Done** (see Section 5).
2. **Get the cPanel hosting login** and turn on SSH/SFTP access for the `gunadmin` account.
3. **Take a full backup** from the server: `system/`, `vendor/`, `.env` and `writable/uploads/` (uploaded Excel lists).
4. **Confirm access** to the SendGrid (Twilio) account and the Airtable base.
5. **Check Vercel** to see whether the Next.js version is still deployed, and decide if it should be kept or removed.
6. **Fix the high-priority issues** (Section 6, items 1–3), then the medium ones.
7. ~~Add setup instructions to the repository~~ – **Done** (`docs/DEPLOYMENT.md`). It will be updated once server access is confirmed.

---

## 8. Information Needed from RVA

- The login for the GoDaddy account that holds the gunslinger.cc hosting (cPanel)
- SendGrid / Twilio account access
- Airtable base access
- Vercel account access, if used
- Any new sender email addresses to add
