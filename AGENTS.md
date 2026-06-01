<coding_guidelines>
# TEF-CPT Licensure Page

Participant-facing page on tefcpt.org that consolidates NYSED licensure, OASAS CASAC, and exam fee application processes for CPT participants. Repo is design source-of-truth + WP deployment scaffold.

## Sister repo

`tef-cpt` (Career Services event maintenance) is separate. Don't conflate the two: this repo is participant-facing UI, that one is automation/scripts.

## Tech stack

- Astro 5+ for the prototype/design layer (`src/`).
- Plain CSS (`public/styles.css`).
- ACF for WP integration (`wp/acf-field-group.json`).
- Custom Gutenberg block server-renders the WP page (`wp/gutenberg-block/render.php`).
- Node scripts in `scripts/` for PDF refresh and live-page verification.

## Working principles

- **Content is portable.** Per-profession data lives in Markdown frontmatter with a Zod-validated schema. Keep it that way so a future migration to a microsite OR plugin doesn't require rewriting content.
- **Asana form delivery is abstracted.** All form rendering goes through `AsanaFormEmbed.astro` (Astro) or the modal markup in `render.php` (WP). MVP is iframe; future swap to native Asana API stays inside that component contract.
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
