# Guided sidebar flow replaces the profession card grid

The page was an information-dense grid of 8 profession cards, each cramming Form 1 download, NYSED checklist, and licensure + exam fee triggers. We replaced it with a guided sidebar flow (eligibility → profession → per-profession prep/process steps → fee submission) after prototyping three guided variants; the sidebar/stepper (variant B) won.

Notarization is modeled as **offline prep, not a blocking gate**: NYSED journeys split into a "prepare your Form 1 (offline)" phase and an always-reachable "submit your fee request (online)" phase, with each step marked on-page vs offline. The notarized Form 1 is scanned and uploaded inside the licensure fee request; TEF prints and mails it to the state. OASAS and AAMA have no prep phase and are never gated.

## Considered Options

- **Profession card grid (original).** Simple and static, but too dense and gave no sense of sequence or what happens offline.
- **Full-screen Typeform (variant A)** and **conversational (variant C).** Rejected: A hijacks the whole viewport and reads as a survey; C buries reference content in a transcript.
- **Sidebar stepper (variant B, chosen).** A persistent journey-map rail communicates the offline/online sequence and lets the fee tasks remain a non-linear hub.

## Consequences

- The page is now a client-side state machine. To avoid forking logic, the flow JS lives in a shared `public/*.js` consumed by both the Astro page and the WP `render.php` block, mirroring the shared `public/styles.css`.
- Build is phased: the flow is built in Astro first and the design locked with the client, then `render.php` is rewritten to emit a data blob the shared script consumes (ACF schema/seed updated if the data shape changes).
- Prep/process step copy moves into editable, regulator-level content templates (not hardcoded in JS), parameterized per profession by Form 1 PDF and checklist.
- The standalone EligibilityTeaser and generic HowItWorks sections are retired; their copy moves into the flow. A slim hero remains above, FAQ and contact below.
