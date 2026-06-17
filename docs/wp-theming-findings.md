# WP theming & styling-interface findings

Research-only writeup. How styling works on tefcpt.org, how this repo's styles reach the
production page, and what a future theme-alignment would involve. No styling changes were
made as part of this investigation.

Sources: live theme CSS pulled from
`https://www.tefcpt.org/wp-content/themes/landslide/dist/assets/main-Bw76xsio.css`, the
sister repo `tefcpt-career-services` (`AGENTS.md` / `CONTEXT.md` / `plugin/`), and this
repo's `wp/` scaffolding.

## 1. Two separate styling worlds

There are two distinct style systems in play, and they currently do **not** share tokens:

| | This licensure page | Live tefcpt.org theme |
|---|---|---|
| Source | `public/styles.css` (hand-written, ~400 lines) | `landslide/dist/assets/main-*.css` (compiled Tailwind) |
| Tokens | CSS custom properties (`--color-accent`, etc.) | Tailwind utility classes (`bg-primary-600`, `text-secondary-500`) |
| Accent | Blue `#0b5394` | Maroon/burgundy `#650B17`–`#8A2432` |
| Fonts | System stack (`-apple-system`, Roboto fallback) | Poppins (headings) + Roboto (body), Google Fonts |
| Consumed by | Astro prototype **and** the WP Gutenberg block | The whole WordPress site |

The licensure page's neutral tokens are intentional per `AGENTS.md` ("Content is portable",
"Class names match between Astro and WP") so the design layer stays host-agnostic. Matching
the Landslide theme is a deliberate future step, not an accident of drift.

## 2. The live theme: "LOG OFF Movement" by Landslide Digital

- Theme path: `/wp-content/themes/landslide/`, compiled CSS at `dist/assets/main-*.css`
  (hashed filename, so the exact URL changes on each theme build).
- Built with **Tailwind** (full preflight reset + utility classes + `@tailwindcss/typography`
  `prose` classes) plus a few Vue scoped-component styles (`[data-v-*]`).
- Fonts: **Poppins** and **Roboto**, both `sans-serif`, applied via utility classes
  (some with `!important`).
- Animation: AOS (Animate On Scroll) via CDN (per sister repo).
- Container breakpoints: 500 / 768 / 1024 / 1280 / 1432 / 1640 px.

### Brand palette (extracted from the live CSS)

Primary — deep maroon/burgundy (the dominant brand color):

| Token | RGB | Hex |
|---|---|---|
| primary-200 | 255 160 125 | `#FFA07D` |
| primary-300 | 197 22 47 | `#C5162F` |
| primary-400 | 167 25 45 | `#A7192D` |
| primary-500 | 138 36 50 | `#8A2432` |
| primary-600 | 101 11 23 | `#650B17` |
| primary-700 | 83 6 18 | `#530612` |
| primary-800 | 61 3 16 | `#3D0310` |
| primary-900 | 39 0 22 | `#270016` |

Secondary — gold/yellow:

| Token | RGB | Hex |
|---|---|---|
| secondary-400 | 255 217 33 | `#FFD921` |
| secondary-500 | 254 203 0 | `#FECB00` |

Accent — orange: accent-500 `#FF8A00`, accent-900 `#481803`.

Grays / structural: gray-100 `#F8F8F8`, gray-200 `#F2E9E1` (the default border color in the
reset), gray-500 `#B8AAA0` (input borders/placeholders).

Themeable focus color (`--focus-color`) appears as `#C5162F`, `#E3A800`, `#FF8A00`, `#FFD921`
in different contexts. Dialog backdrop is `#270016de` (primary-900 + alpha).

## 3. How this repo's styling reaches a WordPress page

The page is server-rendered by a custom Gutenberg block, not by the theme directly:

- `wp/gutenberg-block/block.json` registers `tefcpt/licensure-page` with
  `"render": "file:./render.php"`.
- `wp/gutenberg-block/render.php` reads ACF fields (`wp/acf-field-group.json`) and emits
  markup using the **same class names** as the Astro prototype (`.hero`, `.profession-card`,
  `.fee-section`, `.asana-modal`, etc.). Class-name parity with `public/styles.css` is the
  contract that keeps prototype and production visually identical.
- For that markup to be styled in production, `public/styles.css` must be **enqueued** on the
  page. The sister plugin shows the established pattern (`tefcpt-career-services.php`):

  ```php
  add_action( 'wp_enqueue_scripts', 'tefcpt_enqueue_calendar' );
  function tefcpt_enqueue_calendar() {
      if ( ! is_page( 2068 ) ) return;            // page-scoped enqueue
      wp_enqueue_style( 'tefcpt-calendar',
          plugin_dir_url( __FILE__ ) . 'assets/calendar.css', [], '0.2.0' );
      wp_enqueue_script( 'tefcpt-calendar', /* ... */ );
  }
  ```

  i.e. assets are enqueued from a plugin, scoped to a specific page ID, with a manual version
  string for cache-busting. The licensure page would follow the same shape (enqueue
  `styles.css` + `licensure-flow.js` on its own page ID).

Note: the interactive "Your path" stepper (the `lf-*` classes in `public/licensure-flow.js`
and `public/styles.css`) is **prototype-only**. `render.php` ships a static profession-grid
instead and never references `lf-*`. So the mobile-stepper work touches only the Astro layer.

## 4. WPEngine interface mechanics (from the sister repo)

- Staging: `https://tefcpt.wpenginepowered.com/` (the standing test target — real theme, real
  ACF data, real posts). DNS for production still points at the old host.
- SSH: `tefcpt@tefcpt.ssh.wpengine.net` with `~/.ssh/wpengine_ed25519`; WP root
  `/home/wpe-user/sites/tefcpt/`; WP-CLI available (PHP 8.4).
- Deploy pattern is `rsync` of a plugin folder into
  `wp-content/plugins/<slug>/` over SSH.
- **Cloudflare blocks headless/curl** against the live site; use the agent-browser (Chrome
  CDP) flow for any wp-admin interaction. (curl against the public theme CSS file itself does
  work, which is how the palette above was retrieved.)

## 5. Implications for a future theme-alignment (out of scope now)

If/when the licensure page should visually match tefcpt.org:

1. **Tokens, not utilities.** Keep the CSS-custom-property approach in `public/styles.css`;
   just repoint the values: `--color-accent` → primary-600 `#650B17`, hover → primary-800
   `#3D0310`; introduce a gold secondary (`#FECB00`) for highlights; align borders to
   `#F2E9E1`. This preserves the Astro/WP class-name contract with a one-file change.
2. **Fonts.** Adopt Poppins (headings) + Roboto (body) to match. On WP they already load with
   the theme; in the Astro prototype they'd need a Google Fonts link.
3. **Regulator colors.** The current `--color-nysed/oasas/aama` (blue/purple/red) are
   functional, not brand — decide whether to keep them as semantic accents or fold them into
   the maroon/gold palette.
4. **Don't depend on theme utility classes.** The theme's hashed CSS filename and Tailwind
   utilities are a moving target; relying on them would couple the page to theme builds.
   Self-contained tokens in `styles.css` (enqueued per-page) stay robust.
