# TEF-CPT Licensure & Exam Fees

The participant-facing page that consolidates licensure and exam-fee application steps for TEF CPT participants across multiple credentialing bodies. This glossary fixes the language used in content, components, and WP rendering.

## Language

**Regulator**:
The body that governs a credential's licensure or certification. A closed enum: NYSED (New York State), OASAS (New York State substance-abuse counseling), AAMA (national medical-assistant certification).
_Avoid_: Authority, agency, board

**Form 1**:
The New York State application form a participant must complete and have notarized before TEF submits it. Exists only for NYSED professions.
_Avoid_: Application form, NYS form

**Notarization**:
The offline, in-person (or remote online) step where a notary witnesses and certifies a participant's signed Form 1. A real-world errand the participant completes before submitting a fee request, not an in-page action. The notarized Form 1 is then scanned and uploaded inside the licensure fee request; TEF prints and mails it to the state (back-office, not a participant action). There is no physical handoff to TEF.
_Avoid_: Verification, certification

**Eligibility**:
Whether a person is an active 1199SEIU TEF CPT participant, the precondition for any fee coverage.

**Fee request**:
A participant's submission asking TEF to cover a fee. Two kinds: a **Licensure fee** (the application/licensure fee paid to the regulator) and an **Exam fee** (the credentialing exam fee).
_Avoid_: Application, intake (except where a regulator's own combined form is named that)

**Walk-in fee**:
A fee handled in person at TEF Career Services rather than through an online form (e.g. PA and MHC exam fees). Has no submittable form on the page.
_Avoid_: In-person fee, manual fee

**Prep phase**:
The offline portion of an NYSED journey: download Form 1, complete it, get it notarized. Presented as prerequisite guidance, never a blocking gate. OASAS and AAMA have no prep phase.
_Avoid_: Step 1, pre-flight

**Submission phase**:
The online portion of a journey: the actual fee request form(s), embedded inline on the page. Always reachable regardless of prep status. For NYSED, the scanned notarized Form 1 is uploaded inside the licensure fee request specifically; the exam fee request needs no Form 1.
_Avoid_: Step 2, checkout

**On-page vs offline**:
A per-step distinction surfaced in the UI. On-page steps are done on the page (download a PDF, submit a form); offline steps are real-world errands (complete and notarize Form 1). Every prep/process step carries a marker so participants know which is which.
_Avoid_: Online/manual

**Flow**:
The client-side state machine that drives the guided sidebar journey. Its logic lives in a shared `public/*.js` consumed by both the Astro page and the WP `render.php` block, the same way `public/styles.css` is shared. Astro and WP must not fork it.
_Avoid_: Wizard, stepper (use for the UI rail only)
