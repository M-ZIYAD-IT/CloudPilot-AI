# Metabase Dashboard Spec (Phase 4)

Two dashboards, per the plan's §8: a completion funnel (product ops) and an
answer-distribution aggregate (the "market intelligence" content the idea
doc calls out as itself sellable later). Metabase itself isn't running yet —
see **Status** below for why, and what unblocks it.

## Dashboard 1 — Completion Funnel

Source: `funnel-dashboard.sql` (3 cards, in order).

| Card | Shows | Chart type |
|---|---|---|
| 1 | started → completed → report generated, with conversion rates | Metabase "Funnel" visualization, or a stat row |
| 2 | Daily started/completed counts | Line chart, `day` on X |
| 3 | Which survey page each starter reached, and what % of starters that represents | Bar chart, `page_name` on X |

**Known gap — "call booked" isn't a stage here.** The idea doc's funnel
sketch (§8) includes started → completed → report generated → call booked.
There's no booking feature in the app yet, so there's nothing to count for
that last stage. Add it once a booking flow (or even just a "book a call"
link with a tracked click) exists.

**Known gap — drop-off is per-page, not per-question.** Phase 1 instruments
`page_completed` events for each of the 5 survey pages (business,
infrastructure, apps, compliance, budget), not an event per individual
question. Getting true per-question drop-off would mean firing an event on
every answer, which Phase 1 deliberately avoided to keep the debounced
autosave flow simple. If page-level drop-off analysis later points at a
*specific* page as the problem, that's the moment to add finer-grained
instrumentation to that page specifically — not to every question up front.

## Dashboard 2 — Answer Distribution

Source: `answer-distribution.sql`. One card: question_key / answer / count
/ percentage, filterable by `question_key` in Metabase. This is the raw
material for "62% of respondents cite data residency as their top blocker"
style content — anonymized by construction, since the query never touches
organization or user identity.

## Status: Metabase is not connected yet

Decision made when starting Phase 4: skip standing up a live Metabase
instance for now and prepare these queries instead, because of a real
mismatch between this project's tooling choices so far:

- The dev database is **SQLite** (chosen in Phase 0 to avoid needing Herd
  Pro for MySQL).
- Metabase has **no official SQLite driver**. Connecting it to SQLite means
  either a community-maintained plugin (version-coupled to the exact
  Metabase build, and not chosen here) or switching the app to MySQL/Postgres
  first.

Both SQL files are written and verified to run correctly on SQLite against
the actual seeded dev data (`funnel-dashboard.sql` entirely; the SQLite
branch of `answer-distribution.sql`). The MySQL branch of
`answer-distribution.sql` is written but **not** verified — there's no MySQL
instance in this dev environment to test it against.

### To actually stand this up later

1. Provision a database Metabase supports natively (MySQL or Postgres) —
   this will already be true in production per the idea doc's original
   stack choice.
2. Install Metabase (standalone `.jar` + a JRE is the lightest option for a
   small VPS; no Docker required).
3. Point Metabase at that database as a read-only connection.
4. Paste each query in this folder into Metabase as a "New Question → SQL
   query" card, and add the cards to a dashboard.
