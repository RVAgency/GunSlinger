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
- **Confirmed:** the live app runs on a **GoDaddy VPS server** (with cPanel/WHM), not on Vercel. The domain gunslinger.cc is in the same GoDaddy account and is set to auto-renew. Details are in Section 5.
- **Access restored (11 Nov 2026):** we now have full access to the server, through GoDaddy → WHM → cPanel and through secure SSH. We checked the code on the server, and it is **identical** to the code in GitHub. **We can now safely deploy future changes.**

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
| Type | **GoDaddy VPS** (self-managed): AlmaLinux 9, cPanel & WHM v138, 2 CPU, 4 GB RAM, 99 GB disk (19% used), US West |
| Sites on this server | **Two:** gunslinger.cc (account `gunadmin`) and leadgensys.cc (account `leadgensys`) |
| Software | PHP 8.1.35, CodeIgniter 4.6.3 |
| Control panel | GoDaddy → My Hosting → GunSlinger → **Launch WHM** → List Accounts → `gunadmin` → cPanel |
| File access | Secure SSH key access for the `gunadmin` account (restricted "jailed" shell). It can only reach the Gunslinger site, not leadgensys.cc or server settings. |
| Website status | Live. The login page and the redirect for logged-out users were checked on 11 Nov 2026. |

### How the app is deployed

- **There is no automatic deployment and no build step.** It is a plain PHP application. Changed files are copied to the server and take effect immediately; no restart is needed.
- **New process (from 11 Nov 2026):** code changes are made in GitHub, then copied to the server over secure SSH. Before every deploy:
  1. The list of files that will change is reviewed and approved.
  2. A backup of the current code is taken on the server, so a rollback takes seconds.
  3. Only application code is updated. The framework, libraries, settings file and uploaded lists are never touched.
  4. The live site is checked afterwards.
- Some parts exist **only on the server** and are not in GitHub:
  - the CodeIgniter framework (`system/`)
  - add-on libraries (`vendor/`)
  - the settings file with the Airtable and SendGrid keys (`.env`)
  - uploaded Excel recipient lists (`writable/uploads/`)

  These are covered by the server backup (below).
- **Backups:** a code snapshot from June 2026 exists on the server. A full cPanel account backup should be downloaded and kept off the server. GoDaddy's automatic VPS backups still need to be checked (VPS dashboard → Backups tab).

### Server health notes

- **Outdated PHP:** the server runs PHP 8.1, which no longer receives security updates. GoDaddy has also flagged a PHP security advisory for this server. Because the VPS is **self-managed**, GoDaddy will not apply updates for us; we have to plan the upgrade ourselves.
- **Shared server:** the PHP upgrade and any OS update or reboot affect **both** gunslinger.cc and leadgensys.cc. They should be scheduled, tested and done only after a full backup.

### Email (SendGrid) note

The app sends email from `mobilefuneralservice.com` and `resthavenbatonrouge.com`, not from gunslinger.cc. The SendGrid sender setup therefore lives on those two domains. gunslinger.cc itself is not set up to send email.

A full technical deployment guide is in the repository at `docs/DEPLOYMENT.md`.

---

## 6. Issues Found in the Code

| # | Issue | Impact | Priority |
|---|---|---|---|
| 1 | ~~No server access~~ – **Resolved 11 Nov 2026.** Server access is set up and the code is verified. | – | Done |
| 2 | The CodeIgniter framework files (`system/`) and libraries (`vendor/`) are not in GitHub. They exist only on the server. | The app cannot be rebuilt from GitHub alone. A full server backup covers this for now. | Medium |
| 2a | The server runs PHP 8.1, which no longer gets security updates, and GoDaddy has flagged a PHP advisory. | Security risk for both sites on the server. | **High** |
| 3 | A hidden test link on the login page shows server configuration details to anyone. | Security risk. It should be removed. | **High** |
| 4 | Only two sender addresses are allowed (`service@mobilefuneralservice.com` and `service@resthavenbatonrouge.com`), and they are fixed in the code. | Adding a new sender needs a code change. | Medium |
| 5 | The "name" field sent to every email template is fixed to "Vishal". | Wrong personalisation if a template uses the name. | Medium |
| 6 | Some error messages (missing file or template) would crash the request instead of showing a proper message. | Confusing errors for users. | Medium |
| 7 | All emails are sent while the browser waits. | Very large lists may time out. | Medium |
| 8 | The "Sign up" link on the login page does nothing. Users must be added directly in Airtable. | Minor. | Low |
| 9 | The `/dashboard` page is broken (missing layout file). It is not used in normal flow. | Minor. | Low |

---

## 7. Next Steps

1. ~~Check the domain and DNS in GoDaddy~~ – **Done**.
2. ~~Get server access (WHM/cPanel and SSH)~~ – **Done**, 11 Nov 2026.
3. ~~Verify that the live code matches GitHub~~ – **Done**: identical.
4. ~~Write deployment instructions~~ – **Done** (`docs/DEPLOYMENT.md`).
5. **Download a full cPanel backup** of the `gunadmin` account and check GoDaddy's automatic VPS backups.
6. **Fix the security issues:** remove the hidden test link (item 3), and plan the PHP upgrade for the whole server (item 2a).
7. **Confirm access** to the SendGrid (Twilio) account and the Airtable base.
8. **Check Vercel** to see whether the old Next.js version is still deployed, and decide if it should be removed.
9. **Fix the medium-priority code issues** (Section 6).

---

## 8. Information Needed from RVA

- ~~GoDaddy hosting access~~ – received
- SendGrid / Twilio account access
- Airtable base access
- Vercel account access, if used
- Any new sender email addresses to add
