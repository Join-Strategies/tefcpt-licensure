#!/usr/bin/env node
/**
 * Seed the TEF-CPT Licensure page on tefcpt.org from local content files.
 *
 * Reads:
 *   - src/content/professions/*.md   (frontmatter holds structured fields)
 *   - src/content/page/*.md          (hero, eligibility, how-it-works, faq, contact)
 *   - public/form-1-pdfs/*.pdf       (uploaded to WP media library)
 *
 * Writes:
 *   - PDFs into WP media library (POST /wp-json/wp/v2/media)
 *   - ACF fields on the target page (POST /wp-json/wp/v2/pages/<id> with acf payload)
 *
 * Configuration via env vars:
 *   WP_BASE_URL        e.g. https://www.tefcpt.org
 *   WP_PAGE_ID         numeric ID of the Licensure page (created manually first)
 *   WP_USERNAME        WP application user
 *   WP_APP_PASSWORD    WP application password (NOT the login password)
 *
 * Usage: node wp/seed-script.mjs [--dry-run]
 *
 * Note: this script is the scaffold for the deploy path. It expects the
 * Licensure page to already exist in WP (created via wp-admin) with the
 * ACF field group from wp/acf-field-group.json attached.
 */

import { readFile, readdir } from 'node:fs/promises';
import { existsSync } from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const repoRoot = path.resolve(__dirname, '..');

const DRY_RUN = process.argv.includes('--dry-run');

const cfg = {
  baseUrl: process.env.WP_BASE_URL,
  pageId: process.env.WP_PAGE_ID,
  username: process.env.WP_USERNAME,
  appPassword: process.env.WP_APP_PASSWORD,
};

function requireConfig() {
  const missing = Object.entries(cfg).filter(([, v]) => !v).map(([k]) => k);
  if (missing.length && !DRY_RUN) {
    console.error(`Missing required env vars: ${missing.join(', ')}`);
    process.exit(1);
  }
}

function authHeader() {
  const token = Buffer.from(`${cfg.username}:${cfg.appPassword}`).toString('base64');
  return `Basic ${token}`;
}

function parseFrontmatter(raw) {
  const match = raw.match(/^---\n([\s\S]*?)\n---\n?([\s\S]*)$/);
  if (!match) throw new Error('Missing frontmatter');
  const [, fm, body] = match;
  const data = parseYaml(fm);
  return { data, body: body.trim() };
}

function parseYaml(yaml) {
  const out = {};
  const lines = yaml.split('\n');
  let currentKey = null;
  let nested = null;
  for (const line of lines) {
    if (!line.trim() || line.trim().startsWith('#')) continue;
    if (line.startsWith('  ') && nested) {
      const m = line.match(/^\s{2}(\w+):\s*(.*)$/);
      if (m) nested[m[1]] = coerceYamlValue(m[2]);
      continue;
    }
    const m = line.match(/^(\w+):\s*(.*)$/);
    if (!m) continue;
    const [, key, val] = m;
    if (val === '') {
      nested = {};
      out[key] = nested;
      currentKey = key;
    } else {
      out[key] = coerceYamlValue(val);
      nested = null;
      currentKey = key;
    }
  }
  return out;
}

function coerceYamlValue(v) {
  const s = v.trim();
  if (s === 'null') return null;
  if (s === 'true') return true;
  if (s === 'false') return false;
  if (/^-?\d+(\.\d+)?$/.test(s)) return Number(s);
  return s.replace(/^"(.*)"$/, '$1').replace(/^'(.*)'$/, '$1');
}

async function loadProfessions() {
  const dir = path.join(repoRoot, 'src', 'content', 'professions');
  const files = (await readdir(dir)).filter((f) => f.endsWith('.md'));
  const items = [];
  for (const f of files) {
    const raw = await readFile(path.join(dir, f), 'utf8');
    const { data, body } = parseFrontmatter(raw);
    const slug = f.replace(/\.md$/, '');
    items.push({ ...data, slug, card_body: body });
  }
  return items.sort((a, b) => (a.order ?? 0) - (b.order ?? 0));
}

async function loadPageSection(name) {
  const file = path.join(repoRoot, 'src', 'content', 'page', `${name}.md`);
  if (!existsSync(file)) return '';
  const { body } = parseFrontmatter(await readFile(file, 'utf8'));
  return body;
}

async function uploadPdfIfNeeded(localPath) {
  if (DRY_RUN) {
    console.log(`[dry-run] would upload ${path.basename(localPath)}`);
    return `https://www.tefcpt.org/wp-content/uploads/dry-run/${path.basename(localPath)}`;
  }
  const fileName = path.basename(localPath);
  const buf = await readFile(localPath);
  const res = await fetch(`${cfg.baseUrl}/wp-json/wp/v2/media`, {
    method: 'POST',
    headers: {
      Authorization: authHeader(),
      'Content-Type': 'application/pdf',
      'Content-Disposition': `attachment; filename="${fileName}"`,
    },
    body: buf,
  });
  if (!res.ok) {
    const text = await res.text();
    throw new Error(`PDF upload failed (${res.status}): ${text}`);
  }
  const json = await res.json();
  return json.source_url;
}

function mapProfessionToAcf(p, pdfUrlByPath) {
  return {
    name: p.name,
    slug: p.slug,
    regulator: p.regulator,
    form1_pdf: p.form1Pdf ? pdfUrlByPath.get(p.form1Pdf) ?? '' : '',
    form1_revision: p.form1Revision ?? '',
    checklist_url: p.checklistUrl ?? '',
    licensure_asana_url: p.licensureFee?.asanaFormUrl ?? '',
    licensure_walk_in_only: !!p.licensureFee?.walkInOnly,
    licensure_notes: p.licensureFee?.notes ?? '',
    exam_asana_url: p.examFee?.asanaFormUrl ?? '',
    exam_walk_in_only: !!p.examFee?.walkInOnly,
    exam_notes: p.examFee?.notes ?? '',
    card_body: p.card_body ?? '',
    needs_content_review: !!p.needsContentReview,
    review_notes: p.reviewNotes ?? '',
  };
}

async function main() {
  requireConfig();

  const professions = await loadProfessions();
  const hero = await loadPageSection('hero');
  const eligibility = await loadPageSection('eligibility');
  const howItWorks = await loadPageSection('how-it-works');
  const faq = await loadPageSection('faq');
  const contact = await loadPageSection('contact');

  const pdfUrlByPath = new Map();
  const uniquePdfPaths = [
    ...new Set(professions.map((p) => p.form1Pdf).filter(Boolean)),
  ];
  for (const pdfPath of uniquePdfPaths) {
    const local = path.join(repoRoot, 'public', pdfPath.replace(/^\//, ''));
    const url = await uploadPdfIfNeeded(local);
    pdfUrlByPath.set(pdfPath, url);
    console.log(`  ${pdfPath} -> ${url}`);
  }

  const payload = {
    acf: {
      hero_intro_copy: hero,
      eligibility_copy: eligibility,
      how_it_works: howItWorks,
      faq,
      contact_footer: contact,
      professions: professions.map((p) => mapProfessionToAcf(p, pdfUrlByPath)),
    },
  };

  if (DRY_RUN) {
    console.log('[dry-run] payload preview:');
    console.log(JSON.stringify(payload, null, 2));
    return;
  }

  const res = await fetch(`${cfg.baseUrl}/wp-json/wp/v2/pages/${cfg.pageId}`, {
    method: 'POST',
    headers: {
      Authorization: authHeader(),
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(payload),
  });
  if (!res.ok) {
    const text = await res.text();
    throw new Error(`Page update failed (${res.status}): ${text}`);
  }
  console.log(`Seed complete. Page ${cfg.pageId} updated.`);
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
