# Design system — TEF-CPT Licensure Page

The single source of truth for the page's visual language. All values live as CSS
custom properties at the top of `public/styles.css` (copied to
`plugin/assets/styles.css` on deploy). **Never hardcode a font size, spacing value,
radius, or color in a component — reference a token.** If a value you need doesn't
exist, add a token, don't invent a one-off.

The system is anchored to the Landslide theme (tefcpt.org): Poppins headings +
Roboto body, maroon/gold palette, editorial scale (large headings, generous
whitespace, tight letter-spacing on big type).

## Color

| Token | Value | Use |
|---|---|---|
| `--color-bg` | `#ffffff` | page background |
| `--color-text` | `#1a1a1a` | headings, strong text |
| `--color-body` | `#374151` | body copy (matches theme prose) |
| `--color-muted` | `#6b5c54` | secondary text, captions |
| `--color-border` | `#F2E9E1` | borders, dividers |
| `--color-accent` | `#650B17` | maroon — primary brand, headings, primary buttons |
| `--color-accent-hover` | `#3D0310` | hover state |
| `--color-gold` | `#FECB00` | gold — accents, the active step, FAQ toggle |
| `--color-gold-dark` | `#7a5b00` | text on gold-tinted backgrounds |
| `--color-nysed/oasas/aama` | blue/purple/red | regulator badges (functional, not brand) |
| `--color-soft-bg` | `#F8F8F8` | callouts, fills |

## Type scale

7 steps. Every heading/text element maps to exactly one.

| Token | Size | Line-height | Letter-spacing | Use |
|---|---|---|---|---|
| `--fs-display` | `clamp(2.25rem, 5vw, 3rem)` | `--lh-display` (1.1) | `--ls-display` (-0.02em) | hero H1 |
| `--fs-h2` | `clamp(1.75rem, 3.5vw, 2.25rem)` | `--lh-heading` (1.2) | `--ls-display` | section headings (FAQ, footer, how-it-works) |
| `--fs-h3` | `1.375rem` | `--lh-snug` (1.35) | `--ls-heading` (-0.015em) | card titles, FAQ questions, step titles |
| `--fs-lg` | `1.125rem` | `--lh-snug` | — | lead/intro copy, modal header |
| `--fs-base` | `1rem` | `--lh-prose` (1.75) for prose, `--lh-normal` (1.55) for UI | — | body |
| `--fs-sm` | `0.875rem` | `--lh-normal` | — | captions, notes, disclaimers |
| `--fs-eyebrow` | `0.75rem` | 1.2 | `--ls-eyebrow` (0.08em) | uppercase labels, badges, group headings |

Families: `--font-head` (Poppins) for headings/eyebrows/badges, `--font-body`
(Roboto) for everything else.

## Spacing

A single 0.25rem-based scale. Use these for all padding/margin/gap.

| Token | Value |
|---|---|
| `--space-0` | 0.25rem |
| `--space-1` | 0.5rem |
| `--space-2` | 1rem |
| `--space-3` | 1.5rem |
| `--space-4` | 2rem |
| `--space-5` | 3rem |
| `--space-6` | 4rem — major section rhythm |

## Radii & elevation

| Token | Value | Use |
|---|---|---|
| `--radius-sm` | 8px | buttons, small inputs, choice cards |
| `--radius` | 12px | cards, panes, callouts, FAQ entries |
| `--radius-lg` | 20px | feature surfaces |
| `--radius-pill` | 9999px | badges, gold CTAs |
| `--shadow-sm` | `0 1px 3px rgba(39,0,22,.06)` | resting cards |
| `--shadow` | `0 6px 24px rgba(39,0,22,.08)` | hover / open state |

## Component patterns

- **Card** (`.profession-card`, `.lf-pane`, `.faq-entry`): white bg, `--radius`,
  1px `--color-border`, `--shadow-sm` at rest → `--shadow` on hover/open.
- **Buttons**: one base, four variants — primary maroon (`.lf-btn`, `.asana-trigger`),
  ghost (`.lf-btn.ghost`), uppercase outline CTA (`.lf-btn-outline`), gold pill
  (`.lf-btn-gold`). Text link = `.lf-link`.
- **Badge / pill** (`.regulator-badge`, `.lf-mode`): Poppins, `--fs-eyebrow`,
  uppercase, `--radius-pill`.
- **Accordion**: gold-circle toggle with CSS-drawn `+`/`–` (`.faq-icon`); body
  restores prose spacing the theme's Tailwind preflight strips.
- **Eyebrow** (`.eyebrow`, `.hero-eyebrow`): uppercase Poppins label above a heading.

## Theme interop

The Landslide theme's Tailwind preflight resets headings and lists to `inherit`
and its utility classes are specific. Flow-internal elements (`.lf-pane h2`,
`.lf-pane-body p`, `.lf-steps`) therefore need `!important` to win — these are
consolidated in one "Theme interop" block in `styles.css`, not scattered.

## Rules of thumb

1. Reference tokens; never hardcode a raw size/space/color in a component.
2. New surfaces compose existing patterns (card, button, badge) before inventing.
3. Headings inside our markup are scoped under `.page` so theme chrome is untouched.
4. Edit `public/styles.css` first, then copy to `plugin/assets/` before deploy.
