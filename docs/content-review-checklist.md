# Content review checklist

Open items for the client (TEF / Join Strategies) to confirm before the page goes live.

## Card-level data

- [ ] **CMA in scope?** Certified Medical Assistant is national (AAMA), not NY State. The source doc lists it under Exam Fees only. Confirm whether CMA should appear on the page at all. See `src/content/professions/cma.md`.
- [ ] **Shared Asana URL for RPT / CMA / RT Exam Fees.** All three professions in the source doc share the Asana form key `-w0v7byOE8o2kVdgz1JqIg`. Confirm this is a single intentional shared intake form or, if not, supply the correct per-profession URLs. See review notes in `rpt.md`, `rt.md`, `cma.md`.
- [ ] **Form 1 revision indicators.** Source PDFs carry revision dates in their page footers (e.g., "Revised 11/19"). Confirm whether to display these revision markers on the cards, and decide the refresh cadence.
- [ ] **PA and MHC walk-in exam wording.** Cards currently say "Handled walk-in — contact career services." Confirm wording and confirm the canonical contact channel for these specific cases.

## Page-level copy

- [ ] **Hero intro.** One-sentence framing in `src/content/page/hero.md`. Confirm or replace.
- [ ] **Eligibility teaser.** Confirm exactly who qualifies and how participants should self-check. See `src/content/page/eligibility.md`.
- [ ] **How-it-works.** Confirm the NYSED vs OASAS step explanation in `src/content/page/how-it-works.md`. Specifically confirm the NYSED phone number (currently 518-474-3817) and the contact-us URL.
- [ ] **FAQ.** The current questions in `src/content/page/faq.md` are placeholders inferred from the source documents. Replace with the actual frequently-asked questions or sign off on the placeholders.
- [ ] **Contact footer.** Confirm the canonical TEF Career Services contact email and phone in `src/content/page/contact.md`.

## Page placement & access

- [ ] **URL / slug on tefcpt.org.** Proposed: `/cpt-licensure/`. Confirm or supply an alternative slug.
- [ ] **Navigation placement.** Confirm whether the page appears in any nav, footer, or only via direct link from coordinator communications.
- [ ] **Internal links from other tefcpt.org pages?** e.g., should the Career Services page link out to this one?

## Operational

- [ ] **Who runs the PDF refresh script** (`scripts/refresh-pdfs.mjs`), and on what cadence? Quarterly is the default assumption.
- [ ] **NYSED PDF source URLs.** The refresh script's `SOURCES` map is empty — populate with the canonical NYSED PDF URLs per profession.
- [ ] **WP page creation.** A target page must exist in WP first (with the ACF field group from `wp/acf-field-group.json` attached). Confirm page ID and slug before running the seed script.
- [ ] **WP application password.** Generate an application password for the WP user used by `wp/seed-script.mjs`. Add to `.env`.
