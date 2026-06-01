#!/usr/bin/env node
/**
 * Sanity check: confirm every Asana form URL and NYSED checklist URL
 * in src/content/professions/*.md is reachable (2xx). Useful after a
 * client content review or after redeploy.
 */

import { readFile, readdir } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const dir = path.resolve(__dirname, '..', 'src', 'content', 'professions');

function parse(raw) {
  const m = raw.match(/^---\n([\s\S]*?)\n---/);
  if (!m) return {};
  const out = {};
  let nested = null;
  for (const line of m[1].split('\n')) {
    if (line.startsWith('  ')) {
      const kv = line.match(/^\s{2}(\w+):\s*(.*)$/);
      if (kv && nested) nested[kv[1]] = kv[2].replace(/^"(.*)"$/, '$1');
      continue;
    }
    const kv = line.match(/^(\w+):\s*(.*)$/);
    if (!kv) continue;
    if (kv[2] === '') {
      nested = {};
      out[kv[1]] = nested;
    } else {
      out[kv[1]] = kv[2].replace(/^"(.*)"$/, '$1');
      nested = null;
    }
  }
  return out;
}

async function head(url) {
  if (!url || url === 'null') return null;
  try {
    const res = await fetch(url, { method: 'HEAD', redirect: 'follow' });
    return res.status;
  } catch (err) {
    return `ERR ${err.message}`;
  }
}

async function main() {
  const files = (await readdir(dir)).filter((f) => f.endsWith('.md'));
  let failures = 0;
  for (const f of files) {
    const raw = await readFile(path.join(dir, f), 'utf8');
    const data = parse(raw);
    const checks = [
      ['checklistUrl', data.checklistUrl],
      ['licensureFee.asanaFormUrl', data.licensureFee?.asanaFormUrl],
      ['examFee.asanaFormUrl', data.examFee?.asanaFormUrl],
    ];
    console.log(`\n${f}`);
    for (const [name, url] of checks) {
      if (!url || url === 'null') continue;
      const status = await head(url);
      const ok = typeof status === 'number' && status >= 200 && status < 400;
      console.log(`  ${ok ? 'OK ' : 'FAIL'} ${status} ${name}`);
      if (!ok) failures++;
    }
  }
  if (failures) {
    console.error(`\n${failures} URL(s) failed.`);
    process.exit(1);
  }
}

main();
