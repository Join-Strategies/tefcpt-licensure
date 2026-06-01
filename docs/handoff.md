# Handoff: TEF-CPT Licensure & Exam Fees Page

This repo holds the design source-of-truth, content data, and deployment scaffolding for the Licensure & Exam Fees page on tefcpt.org. It is intentionally separate from the `tef-cpt` repo (which handles Career Services event maintenance).

## What lives where

| Asset | Location | Source-of-truth |
| --- | --- | --- |
| Structured per-profession data | `src/content/professions/*.md` (frontmatter) | Repo (this file) |
| Per-card body copy | Same files (Markdown body) | Repo initially, then editable in WP after seeding |
| Page section copy (hero / how-it-works / eligibility / FAQ / contact) | `src/content/page/*.md` | Repo initially, then editable in WP after seeding |
| Form 1 PDFs | `public/form-1-pdfs/*.pdf` | Repo |
| Page design (HTML/CSS) | `src/components/*.astro` + `public/styles.css` | Repo |
| ACF schema | `wp/acf-field-group.json` | Repo |
| WP rendering | `wp/gutenberg-block/render.php` | Repo |

After the initial seed runs, **live ACF values in WP are the runtime source-of-truth** — they get edited in wp-admin. The repo holds the schema, initial seed, and design.

## Local development

```bash
npm install
npm run dev
# preview at http://localhost:4321
```

## WP deployment

```bash
# one-time: create a target page in wp-admin and attach the ACF field group from wp/acf-field-group.json
# one-time: generate a WP application password for the deploy user
cp .env.example .env  # if/when added
# populate WP_BASE_URL, WP_PAGE_ID, WP_USERNAME, WP_APP_PASSWORD

npm run deploy-wp -- --dry-run   # preview payload
npm run deploy-wp                # push for real
```

## PDF refresh

```bash
# 1. populate the SOURCES map in scripts/refresh-pdfs.mjs with the canonical NYSED PDF URLs
# 2. run quarterly (or as needed)
npm run refresh-pdfs

# 3. review changes, update form1Revision in any updated profession content files,
#    commit, and redeploy.
```

## URL verification

```bash
npm run verify   # HEADs every Asana form URL and NYSED checklist URL to ensure they still resolve
```

## Future evolution paths

The repo is structured so either of these is feasible without a rewrite:

- **Standalone microsite.** `astro build` already produces a static site that can be deployed to `licensure.tefcpt.org` (or similar) on Cloudflare Pages / Netlify / Vercel.
- **WordPress plugin.** The `wp/` directory can grow into a full plugin: the Gutenberg block already exists, and the seed script's logic can be ported to PHP for live data-binding against an internal participant database. The Asana form embed is hidden behind `AsanaFormEmbed.astro` / the block's modal markup, so swapping the iframe for a native form-API integration is a single component-level change.
