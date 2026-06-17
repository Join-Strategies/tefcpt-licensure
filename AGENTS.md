<coding_guidelines>
# TEF-CPT Licensure Page

Participant-facing page on tefcpt.org that consolidates NYSED licensure, OASAS CASAC, and exam fee application processes for CPT participants. Repo is design source-of-truth + WP deployment scaffold.

## Sister repo

`tef-cpt` (Career Services event maintenance) is separate. Don't conflate the two: this repo is participant-facing UI, that one is automation/scripts.

## Tech stack

- Astro 5+ for the prototype/design layer (`src/`).
- Plain CSS (`public/styles.css`) — brand-aligned tokens (maroon/gold, Poppins/Roboto). **Edit here first**, then copy to `plugin/assets/styles.css` before deploying.
- WP plugin in `plugin/` — registers the `tefcpt/licensure-page` Gutenberg block, enqueues assets page-scoped to the `page-licensure.php` template, and loads the ACF field group. Mirrors the career-services plugin shape.
- `plugin/assets/styles.css` and `plugin/assets/licensure-flow.js` are deploy copies of `public/styles.css` and `public/licensure-flow.js`. Keep them in sync.
- ACF field group at `wp/acf-field-group.json` (canonical) and `plugin/gutenberg-block/acf-field-group.json` (deploy copy — keep in sync). Gated on `page_template == page-licensure.php`.
- `wp/` folder contains the superseded standalone block scaffold — retained for reference, superseded by `plugin/`.
- Node scripts in `scripts/` for PDF refresh and live-page verification.

## WPEngine staging

- **URL:** https://tefcpt.wpenginepowered.com/
- **SSH:** `tefcpt@tefcpt.ssh.wpengine.net` with `~/.ssh/wpengine_ed25519`; WP root `/home/wpe-user/sites/tefcpt/`
- **Plugin path:** `~/sites/tefcpt/wp-content/plugins/tefcpt-licensure-page/`

Deploy plugin to staging (bump version in `tefcpt-licensure-page.php` + both enqueue calls first; copy assets from `public/` before running):

```bash
cp public/styles.css plugin/assets/styles.css
cp public/licensure-flow.js plugin/assets/licensure-flow.js
rsync -avz --delete -e "ssh -i ~/.ssh/wpengine_ed25519" \
  /Users/pete/Code/tefcpt-licensure-page/plugin/ \
  tefcpt@tefcpt.ssh.wpengine.net:~/sites/tefcpt/wp-content/plugins/tefcpt-licensure-page/
ssh -i ~/.ssh/wpengine_ed25519 tefcpt@tefcpt.ssh.wpengine.net \
  "cd /home/wpe-user/sites/tefcpt && wp page-cache flush && wp cdn-cache flush"
```

**Flush caches after every deploy** — WPEngine serves full-page cache to anonymous users; logged-in admins bypass it, which is why an admin sees updates but incognito does not. Also bump the version string on each asset-changing deploy.

## Working principles

- **Content is portable.** Per-profession data lives in Markdown frontmatter with a Zod-validated schema. Keep it that way so a future migration to a microsite OR plugin doesn't require rewriting content.
- **Asana form delivery is abstracted.** All form rendering goes through the inline embed in `licensure-flow.js`. The iframe src is set from ACF data — no hard-coded URLs in markup. Future swap to native Asana API stays inside that contract.
- **Class names match between Astro and WP.** `public/styles.css` is consumed by both the Astro prototype and the WP page. Don't fork the styles.
- **No PII in this repo.** This page does not collect or hold participant data — submissions go directly to Asana. If that changes, requirements escalate (encryption, secure storage, etc.) and the WS3 plugin spec applies.

## Conventions

- Profession content files use `{regulator}` enum (NYSED / OASAS / AAMA). Keep this list closed; new regulators require schema + UI updates.
- `needsContentReview: true` is a flag for client conversation, surfaced in `docs/content-review-checklist.md`. Don't quietly resolve these — confirm with the client before flipping the flag.
- PDF filenames in `public/form-1-pdfs/` are stable identifiers referenced by content files. Renaming a PDF requires updating every profession that references it.
- The seed script is **idempotent and dry-run friendly**. Run with `--dry-run` first whenever touching it.

## Out of scope

Eligibility checks, document upload, staff dashboard, SSN handling, state submission PDF generation, status tracking, participant database integration. These are WS3-plugin-phase concerns, deferred. See sister Obsidian note `WS3-licensure-payment-process.spec.md` for the long-term vision.

</coding_guidelines>
