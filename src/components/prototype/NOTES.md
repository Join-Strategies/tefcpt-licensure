# Prototype: guided / Typeform-style licensure flow

**Question being answered:** The current page (`index.astro`) is too information-dense — an
8-card profession grid where each card crams Form 1 download, NYSED checklist, licensure fee
and exam fee. Participants have to wade through it. **What should a guided, led-through-it
version look like instead?**

## How to run

```
npm run dev
```

Then visit `/prototype` and use the floating bottom-center switcher (or ← / → arrow keys) to
flip between the three guided UIs. The variant is also in the URL: `?variant=A|B|C`.

All three render the **same flow**, derived live from the `professions` content collection:

```
Eligibility gate -> Pick profession -> (branch by regulator) -> tailored steps -> done
  NYSED:  download Form 1 -> notarize -> licensure fee -> exam fee -> checklist
  OASAS:  combined intake form -> TEF letter -> upload to OASAS
  AAMA:   exam fee only
```

Walk-in fees and missing forms are handled by the data-driven step builder. Asana forms and
PDF downloads are **stubbed** (no real submissions).

## The three variants

- **A — Full-screen Typeform:** one question per screen, big choices, progress bar, Enter/number keys.
- **B — Stepper + content pane:** persistent journey-map rail with a content pane on the right.
- **C — Conversational:** chat transcript with reply chips.

## Verdict

_TODO (fill in after review):_ which variant won, what to steal from the others, and why.

## Cleanup

This is throwaway code. Once a winner is picked, fold it into the real page and delete:
`src/pages/prototype.astro`, `src/components/prototype/NOTES.md`,
`src/components/prototype/PrototypeSwitcher.astro`.
