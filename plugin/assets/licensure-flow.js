// Shared guided-flow state machine for the Licensure & Exam Fees page.
// Consumed by BOTH the Astro page (src/pages/index.astro) and the WP block
// (wp/gutenberg-block/render.php). Do not fork this logic — keep the two
// renderers feeding it the same JSON shape via #flow-data / #flow-processes /
// #flow-config script tags and a #flow-root mount element.
(function () {
  const rootEl = document.getElementById('flow-root');
  const dataEl = document.getElementById('flow-data');
  const procEl = document.getElementById('flow-processes');
  const cfgEl = document.getElementById('flow-config');
  if (!rootEl || !dataEl || !procEl || !cfgEl) return;

  const PROFS = JSON.parse(dataEl.textContent);
  const PROCESSES = JSON.parse(procEl.textContent);
  const CONFIG = JSON.parse(cfgEl.textContent);

  const profBySlug = (slug) => PROFS.find((p) => p.slug === slug);
  const processFor = (regulator) => PROCESSES[regulator] || { submitHeading: 'Submit your fee request', prepSteps: [] };

  function esc(s) {
    return (s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  }
  function md(s) {
    return esc(s)
      .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
      .replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" rel="noopener">$1</a>')
      .replace(/\n{2,}/g, '<br><br>');
  }
  function embedUrl(url) {
    return url.includes('embed=') ? url : url + '&embed=true';
  }

  function form1Html(p) {
    if (!p.form1Pdf) return '';
    const rev = p.form1Revision ? ` <small class="lf-rev">${esc(p.form1Revision)}</small>` : '';
    return `<p class="lf-download"><a href="${p.form1Pdf}" download>Download Form 1 (PDF)</a>${rev}</p>`;
  }
  function checklistHtml(p) {
    if (!p.checklists || !p.checklists.length) return '';
    return '<p class="lf-checklist">' + p.checklists
      .map((c) => `<a href="${c.url}" target="_blank" rel="noopener">Official NYSED checklist (${esc(c.label)}) &#8599;</a>`)
      .join('<br>') + '</p>';
  }
  function fillTokens(html, p) {
    return html
      .split('{{name}}').join(esc(p.name))
      .split('{{form1}}').join(form1Html(p))
      .split('{{checklist}}').join(checklistHtml(p));
  }

  function feeTasks(p) {
    const tasks = [];
    const lic = p.licensure || { kind: 'none' };
    const exam = p.exam || { kind: 'none' };
    if (lic.kind === 'asana' && exam.kind === 'asana' && lic.url && lic.url === exam.url) {
      tasks.push({ key: 'combined', label: 'Licensure & exam fee', kind: 'asana', url: lic.url, notes: lic.notes, upload: p.regulator === 'NYSED' });
      return tasks;
    }
    if (lic.kind === 'asana') tasks.push({ key: 'lic', label: 'Licensure / application fee', kind: 'asana', url: lic.url, notes: lic.notes, upload: p.regulator === 'NYSED' });
    else if (lic.kind === 'walkin') tasks.push({ key: 'lic', label: 'Licensure fee', kind: 'walkin', notes: lic.notes });
    if (exam.kind === 'asana') tasks.push({ key: 'exam', label: 'Exam fee', kind: 'asana', url: exam.url, notes: exam.notes, upload: false });
    else if (exam.kind === 'walkin') tasks.push({ key: 'exam', label: 'Exam fee', kind: 'walkin', notes: exam.notes });
    return tasks;
  }

  const state = { eligible: null, slug: null, activeKey: 'elig', done: {}, form1Already: null };

  // ---- Item model (the rail) --------------------------------------------
  function items() {
    const list = [
      { key: 'elig', label: 'Get Started', kind: 'elig' },
      { key: 'prof', label: 'Choose profession', kind: 'prof' },
    ];
    if (state.slug) {
      const p = profBySlug(state.slug);
      const proc = processFor(p.regulator);
      if (proc.form1Gate) {
        list.push({ key: 'form1', label: 'Form 1', kind: 'form1gate', groupLabel: proc.prepHeading || 'Prepare' });
      } else {
        (proc.prepSteps || []).forEach((s, i) => {
          list.push({ key: 'prep' + i, label: s.title, kind: 'prep', stepIndex: i, mode: s.mode, groupLabel: proc.prepHeading || 'Prepare' });
        });
      }
      feeTasks(p).forEach((t) => {
        list.push({ key: 'task:' + t.key, label: t.label, kind: 'task', taskKey: t.key, mode: t.kind === 'walkin' ? 'offline' : 'on-page', groupLabel: proc.submitHeading });
      });
      list.push({ key: 'done', label: 'All set', kind: 'done' });
    }
    return list;
  }
  function clickable(it) {
    if (it.kind === 'elig' || it.kind === 'prof') return true;
    return !!state.slug;
  }
  function isDone(it) {
    if (it.kind === 'elig') return !!state.slug;
    if (it.kind === 'prof') return !!state.slug;
    if (it.kind === 'form1gate') return !!state.done['form1'];
    if (it.kind === 'prep' || it.kind === 'task') return !!state.done[it.key];
    return false;
  }
  function firstStepKey() {
    const list = items();
    const first = list.find((i) => i.kind === 'prep' || i.kind === 'task' || i.kind === 'form1gate');
    return first ? first.key : 'prof';
  }

  // ---- Panes ------------------------------------------------------------
  const badge = (p) => `<span class="regulator-badge regulator-${p.regulator.toLowerCase()}">${p.regulator}</span>`;
  const modeTag = (mode) => `<span class="lf-mode lf-mode-${mode}">${mode === 'offline' ? 'Offline / in person' : 'On this page'}</span>`;

  function pane(it) {
    const p = state.slug ? profBySlug(state.slug) : null;

    if (it.kind === 'elig') {
      const intro = CONFIG.heroText ? `<p><strong>${md(CONFIG.heroText)}</strong></p>` : '';
      return `<h2>Get Started / Eligibility / How it Works</h2>
        <div class="lf-pane-body">
          ${intro}
          <p class="lf-time-required"><strong>Time required:</strong> 5&ndash;10 minutes with completed materials</p>
          <p><strong>You&rsquo;ll need to:</strong></p>
          <ul class="lf-how-it-works-list">
            <li>Pick your Profession</li>
            <li>Obtain License information / Complete paperwork</li>
            <li>Complete reimbursement intake forms</li>
          </ul>
        </div>
        <div class="lf-actions" style="margin-top:var(--space-3)">
          <button class="lf-btn-outline" data-act="elig-yes">Get Started</button>
        </div>
        <p class="lf-disclaimer">${md(CONFIG.eligibilityText)}</p>`;
    }

    if (it.kind === 'prof') {
      const grid = PROFS.map((pr) =>
        `<button class="lf-choice" data-act="pick" data-slug="${pr.slug}"><span>${esc(pr.name)}</span>${badge(pr)}</button>`
      ).join('');
      return `<h2>Choose your profession</h2>
        <div class="lf-pane-body"><p>We'll walk you through how it works, then show your fee options.</p></div>
        <div class="lf-choice-grid">${grid}</div>`;
    }

    if (it.kind === 'prep') {
      const proc = processFor(p.regulator);
      const s = proc.prepSteps[it.stepIndex];
      return `<div class="lf-pane-head">${modeTag(s.mode)}<h2>${esc(s.title)} ${badge(p)}</h2></div>
        <div class="lf-pane-body">${fillTokens(md(s.body), p)}</div>
        <div class="lf-pane-nav">
          <button class="lf-btn ghost" data-act="prev">&larr; Back</button>
          <button class="lf-btn" data-act="next">Continue &rarr;</button>
        </div>`;
    }

    if (it.kind === 'form1gate') {
      if (state.form1Already === false) {
        return `<h2>Download, fill out, and notarize your Form 1</h2>
          <div class="lf-pane-body">
            <div class="lf-download-block">
              <span class="lf-download-icon">&#11123;</span>
              <div>${form1Html(p) || '<p>Download link coming soon.</p>'}<small>PDF download</small></div>
            </div>
            <p><strong>How to get your Form 1 notarized:</strong></p>
            <div class="lf-accordion">
              <details class="lf-accordion-item">
                <summary>Local Walk-In Locations</summary>
                <div class="lf-accordion-body">
                  <p>Notarization is offered at most banks and credit unions, shipping and copy centers, and certain New York Public Library branches.</p>
                  <p><strong>Typical cost:</strong> $0&ndash;$10</p>
                </div>
              </details>
              <details class="lf-accordion-item">
                <summary>Remote Online Notarization (RON)</summary>
                <div class="lf-accordion-body">
                  <p>Services: <a href="https://www.notarize.com" target="_blank" rel="noopener">Notarize</a> or <a href="https://www.onenotary.us" target="_blank" rel="noopener">OneNotary</a>.</p>
                  <p><strong>Typical Cost:</strong> $25</p>
                </div>
              </details>
              <details class="lf-accordion-item">
                <summary>Mobile Notaries</summary>
                <div class="lf-accordion-body">
                  <p>Services: <a href="https://www.notary911.com" target="_blank" rel="noopener">Notary911</a> or <a href="https://www.travelingnotarynewyork.com" target="_blank" rel="noopener">Traveling Notary New York</a>.</p>
                  <p><strong>Typical cost:</strong> $75&ndash;$150</p>
                </div>
              </details>
            </div>
            <p class="lf-form1-note">Once you have your notarized Form 1, return to this page to complete the reimbursement steps.</p>
          </div>
          <div class="lf-pane-nav">
            <button class="lf-btn ghost" data-act="form1-back">&larr; Back</button>
            <button class="lf-btn" data-act="form1-done">Continue &rarr;</button>
          </div>`;
      }
      return `<h2>Do you already have your notarized Form 1?</h2>
        <div class="lf-actions" style="margin-top:var(--space-3);gap:var(--space-2)">
          <button class="lf-btn-gold" data-act="form1-yes">Yes</button>
          <button class="lf-btn-gold ghost" data-act="form1-no">No</button>
        </div>`;
    }

    if (it.kind === 'task') {
      const proc = processFor(p.regulator);
      const firstTask = items().find((x) => x.kind === 'task');
      const intro = proc.submitIntro && firstTask && firstTask.key === it.key
        ? `<div class="lf-nudge">${fillTokens(md(proc.submitIntro), p)}</div>`
        : '';
      const t = feeTasks(p).find((x) => x.key === it.taskKey);
      if (t.kind === 'walkin') {
        const body = t.notes ? md(t.notes) : 'This fee is handled in person. Contact TEF Career Services for next steps.';
        return `${intro}<div class="lf-pane-head">${modeTag('offline')}<h2>${esc(t.label)} ${badge(p)}</h2></div>
          <div class="lf-pane-body"><p class="walk-in-notice">${body}</p></div>
          <div class="lf-pane-nav"><button class="lf-btn ghost" data-act="prev">&larr; Back</button></div>`;
      }
      const notes = t.notes ? md(t.notes) : 'Submit your fee request to TEF using the form below.';
      const done = state.done[it.key];
      return `<div class="lf-pane-head">${modeTag('on-page')}<h2>${esc(t.label)} ${badge(p)}</h2></div>
        ${intro}
        <div class="lf-pane-body">${notes}</div>
        <div class="lf-embed"><iframe title="${esc(t.label)}" src="${embedUrl(t.url)}" loading="lazy"></iframe></div>
        <div class="lf-pane-nav">
          <button class="lf-btn ghost" data-act="prev">&larr; Back</button>
          <button class="lf-btn" data-act="task-done">${done ? 'Done &check;' : 'Mark as done'}</button>
        </div>`;
    }

    return `<h2>You're all set</h2>
      <div class="lf-pane-body">${md(CONFIG.contactText)}</div>
      <div class="lf-pane-nav"><button class="lf-btn" data-act="restart">Start over</button></div>`;
  }

  // ---- Render -----------------------------------------------------------
  function render() {
    const list = items();
    if (!list.find((i) => i.key === state.activeKey)) state.activeKey = list[0].key;
    const active = list.find((i) => i.key === state.activeKey);

    let rail = '';
    let lastGroup = null;
    let n = 0;
    let activeNum = 0;
    let activeLabel = '';
    list.forEach((it) => {
      if (it.groupLabel && it.groupLabel !== lastGroup) {
        rail += `<li class="lf-group">${esc(it.groupLabel)}</li>`;
        lastGroup = it.groupLabel;
      } else if (!it.groupLabel) {
        lastGroup = null;
      }
      n += 1;
      const isActive = it.key === state.activeKey;
      const done = isDone(it) && !isActive;
      const lock = !clickable(it);
      const cls = isActive ? 'is-active' : done ? 'is-done' : lock ? 'is-locked' : '';
      const dot = done ? '&#10003;' : '';
      if (isActive) {
        activeNum = n;
        activeLabel = it.label;
      }
      rail += `<li class="lf-step ${cls}" data-act="jump" data-key="${it.key}">
        <span class="lf-dot">${dot}</span><span class="lf-step-label">${esc(it.label)}</span></li>`;
    });

    rootEl.innerHTML = `
      <div class="lf">
        <nav class="lf-rail" aria-label="Your progress">
          <p class="lf-rail-title">Your path</p>
          <ul class="lf-steps">${rail}</ul>
          <p class="lf-active-label">Step ${activeNum} of ${n} &middot; ${esc(activeLabel)}</p>
        </nav>
        <section class="lf-pane" aria-live="polite">${pane(active)}</section>
      </div>`;
  }

  // ---- URL deep-linking -------------------------------------------------
  function syncUrl() {
    const url = new URL(location.href);
    if (state.slug) url.searchParams.set('profession', state.slug);
    else url.searchParams.delete('profession');
    history.replaceState({}, '', url);
  }

  function pickProfession(slug) {
    if (!profBySlug(slug)) return;
    state.slug = slug;
    state.eligible = true;
    state.done = {};
    state.form1Already = null;
    state.activeKey = firstStepKey();
    syncUrl();
  }

  function navBy(delta) {
    const list = items();
    const i = list.findIndex((x) => x.key === state.activeKey);
    const next = Math.min(Math.max(0, i + delta), list.length - 1);
    state.activeKey = list[next].key;
  }

  rootEl.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-act]');
    if (!btn) return;
    const list = items();
    const active = list.find((x) => x.key === state.activeKey);
    switch (btn.dataset.act) {
      case 'elig-yes': state.eligible = true; state.activeKey = 'prof'; break;
      case 'elig-no': state.eligible = false; break;
      case 'elig-anyway': state.activeKey = 'prof'; break;
      case 'pick': pickProfession(btn.dataset.slug); break;
      case 'form1-yes':
        state.form1Already = true;
        state.done['form1'] = true;
        navBy(1);
        break;
      case 'form1-no':
        state.form1Already = false;
        break;
      case 'form1-back':
        state.form1Already = null;
        break;
      case 'form1-done':
        state.done['form1'] = true;
        navBy(1);
        break;
      case 'next':
        if (active && (active.kind === 'prep' || active.kind === 'task')) state.done[active.key] = true;
        navBy(1);
        break;
      case 'prev': navBy(-1); break;
      case 'task-done':
        if (active && active.kind === 'task') state.done[active.key] = true;
        navBy(1);
        break;
      case 'restart':
        state.eligible = null; state.slug = null; state.done = {}; state.activeKey = 'elig'; state.form1Already = null;
        syncUrl();
        break;
      case 'jump': {
        const it = list.find((x) => x.key === btn.dataset.key);
        if (it && clickable(it)) state.activeKey = it.key;
        break;
      }
    }
    render();
  });

  // ---- Init -------------------------------------------------------------
  const wanted = new URLSearchParams(location.search).get('profession');
  if (wanted && profBySlug(wanted)) {
    state.slug = wanted;
    state.eligible = true;
    state.activeKey = firstStepKey();
  }
  render();
})();
