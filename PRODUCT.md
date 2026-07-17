# Product

## Register

product

## Users

Two overlapping audiences, both landing on the same screens:

- **Business/IT decision-makers** (`company_user` role) who self-assess their own organization's cloud migration readiness — usually mid-market ops or IT leads under time pressure, filling out the survey between other work.
- **Consultants** (`consultant` role) running the assessment on behalf of a client org, then sharing the resulting report externally.
- **Admins** overseeing organizations and assessments.

The core loop: log in (or land via signed report link with no login), fill a multi-page survey about infrastructure/compliance/budget, wait for the scoring + AI narrative pipeline to run, then read/share a report with a readiness score, platform recommendation, 6R migration map, compliance register, and TCO bands.

## Product Purpose

CloudPilot AI turns a structured questionnaire into a credible, decision-grade cloud-readiness report: a numeric readiness score, a recommended target platform, a 6R disposition per declared app, a compliance register (PDPL/NCA ECC etc.), and TCO estimates. Success is a user trusting the output enough to act on it or forward it to a stakeholder — the report is the deliverable, not the dashboard.

## Brand Personality

Confident, precise, analyst-grade. Not playful, not corporate-bland. Think "a sharp internal tool built by people who trust their own numbers" — closer to a rigorous audit tool than a marketing SaaS. Three words: **confident, precise, uncluttered.**

## Anti-references

- The current stock Laravel Breeze/Tailwind look: default indigo links, plain white cards, zero personality, indistinguishable from a tutorial scaffold. This is the primary thing to move away from.
- Generic "SaaS dashboard" clichés: hero-metric-with-gradient-accent cards, identical icon+heading+text card grids, cheerful onboarding illustrations.

## Design Principles

1. **The report is the product.** Every other screen (dashboard, survey) exists to get the user to a report they trust — prioritize clarity and momentum over decoration on the way there.
2. **Density with restraint.** This is a product surface serving a workflow, not a marketing poster — apply the Framer DESIGN.md's palette, type, and component vocabulary, but favor product-register conventions (tighter hierarchy, functional density, sparing use of gradient spotlight cards) over marketing-register conventions (giant hero type, atmosphere-first layout).
3. **Numbers read as credible, not decorative.** Scores, TCO bands, and compliance status are the trust signal — typography and color for data should read as precise/analytical, never playful.
4. **No login friction where it doesn't belong.** Signed report links work without auth by design; the UI around them should reinforce "here's your report," not force the visitor through app chrome.
5. **Progress must always be visible.** The survey is multi-page and answers autosave — the user should never wonder if their input was captured or how much is left.

## Accessibility & Inclusion

WCAG AA minimum: maintain contrast ratios against the dark canvas per DESIGN.md's ink/ink-muted hierarchy, respect `prefers-reduced-motion`, and keep all interactive targets (pills, inputs, survey controls) at the 44px touch-height DESIGN.md already specifies. No accessibility requirements beyond AA were specified by the user.
