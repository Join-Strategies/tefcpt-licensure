#!/usr/bin/env node
/**
 * Quarterly NYSED Form 1 refresh.
 *
 * For each NYSED profession in src/content/professions/*.md, fetch the
 * current Form 1 PDF from NYSED, diff bytes vs. the local copy in
 * public/form-1-pdfs/, and report any changes. Writes updated PDFs to
 * disk so the next deploy picks them up.
 *
 * Source URLs below are placeholders — the new repo's maintainer should
 * populate them from each profession's NYSED checklist page (already
 * captured in checklistUrl in the profession content files).
 */

import { readFile, writeFile, mkdir } from 'node:fs/promises';
import { existsSync } from 'node:fs';
import path from 'node:path';
import { createHash } from 'node:crypto';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const repoRoot = path.resolve(__dirname, '..');
const pdfDir = path.join(repoRoot, 'public', 'form-1-pdfs');

const SOURCES = {
  'social-work.pdf': null,
  'nursing.pdf':     null,
  'rpt.pdf':         null,
  'rt.pdf':          null,
  'pa.pdf':          null,
  'mhc.pdf':         null,
};

function sha256(buf) {
  return createHash('sha256').update(buf).digest('hex');
}

async function refresh(name, url) {
  if (!url) {
    console.log(`  ${name}: SKIP (no source URL configured)`);
    return;
  }
  const localPath = path.join(pdfDir, name);
  const localBuf = existsSync(localPath) ? await readFile(localPath) : null;
  const localHash = localBuf ? sha256(localBuf) : null;

  const res = await fetch(url);
  if (!res.ok) {
    console.log(`  ${name}: FETCH FAILED (${res.status})`);
    return;
  }
  const remote = Buffer.from(await res.arrayBuffer());
  const remoteHash = sha256(remote);

  if (localHash === remoteHash) {
    console.log(`  ${name}: unchanged`);
    return;
  }

  await mkdir(pdfDir, { recursive: true });
  await writeFile(localPath, remote);
  console.log(`  ${name}: UPDATED (${localHash?.slice(0, 8) ?? 'none'} -> ${remoteHash.slice(0, 8)})`);
}

async function main() {
  console.log('Refreshing NYSED Form 1 PDFs...');
  for (const [name, url] of Object.entries(SOURCES)) {
    try {
      await refresh(name, url);
    } catch (err) {
      console.log(`  ${name}: ERROR — ${err.message}`);
    }
  }
  console.log('Done. Review any UPDATED entries, update form1Revision in src/content/professions/*.md, commit, and redeploy.');
}

main();
