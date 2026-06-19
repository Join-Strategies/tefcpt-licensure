#!/usr/bin/env node
/**
 * Reads src/content/processes/*.md and emits public/processes.json.
 *
 * This makes the process definitions (prep steps, submit copy) a single
 * edit-point: edit the Markdown file, run `npm run build`, and both the
 * Astro page and the WP render.php block get the updated content.
 *
 * plugin/assets/processes.json is a deploy copy — keep in sync with
 * public/processes.json the same way styles.css and licensure-flow.js are.
 */

import { readFileSync, readdirSync, writeFileSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';
import { load as yamlLoad } from 'js-yaml';

const __dirname = dirname(fileURLToPath(import.meta.url));
const root = join(__dirname, '..');
const processesDir = join(root, 'src', 'content', 'processes');
const outFile = join(root, 'public', 'processes.json');

const files = readdirSync(processesDir).filter((f) => f.endsWith('.md'));

const result = {};

for (const file of files) {
  const raw = readFileSync(join(processesDir, file), 'utf8');

  const match = raw.match(/^---\r?\n([\s\S]*?)\r?\n---/);
  if (!match) {
    console.warn(`gen-processes: no frontmatter found in ${file}, skipping`);
    continue;
  }

  const fm = yamlLoad(match[1]);
  if (!fm || !fm.regulator) {
    console.warn(`gen-processes: missing regulator in ${file}, skipping`);
    continue;
  }

  result[fm.regulator] = {
    prepHeading: fm.prepHeading ?? 'Prepare',
    submitHeading: fm.submitHeading,
    submitIntro: fm.submitIntro ?? null,
    form1Gate: fm.form1Gate ?? false,
    prepSteps: (fm.prepSteps ?? []).map((s) => ({
      title: s.title,
      mode: s.mode,
      body: s.body,
    })),
  };
}

writeFileSync(outFile, JSON.stringify(result, null, 2) + '\n', 'utf8');
console.log(`gen-processes: wrote ${outFile} (${Object.keys(result).join(', ')})`);
