-- Completion Funnel Dashboard
-- Written and verified against SQLite (the current dev database). For a
-- MySQL/Postgres production database, these queries run unchanged - no
-- engine-specific JSON functions are used here (see answer-distribution.sql
-- for the query that does need an engine-specific rewrite).

-- Card 1: Headline funnel (started -> completed -> report generated)
-- "Call booked" is intentionally absent: no booking feature exists yet in
-- the app, so there is nothing to count. Add a stage here once that exists.
SELECT
    COUNT(DISTINCT a.id) AS started,
    COUNT(DISTINCT CASE WHEN a.status = 'completed' THEN a.id END) AS completed,
    COUNT(DISTINCT r.assessment_id) AS reports_generated,
    ROUND(100.0 * COUNT(DISTINCT CASE WHEN a.status = 'completed' THEN a.id END)
        / NULLIF(COUNT(DISTINCT a.id), 0), 1) AS completion_rate_pct,
    ROUND(100.0 * COUNT(DISTINCT r.assessment_id)
        / NULLIF(COUNT(DISTINCT CASE WHEN a.status = 'completed' THEN a.id END), 0), 1) AS report_generation_rate_pct
FROM assessments a
LEFT JOIN reports r ON r.assessment_id = a.id AND r.generated_at IS NOT NULL;

-- Card 2: Funnel trend over time (one row per day)
SELECT
    DATE(a.created_at) AS day,
    COUNT(DISTINCT a.id) AS started,
    COUNT(DISTINCT CASE WHEN a.status = 'completed' THEN a.id END) AS completed
FROM assessments a
GROUP BY DATE(a.created_at)
ORDER BY day;

-- Card 3: Page-level drop-off
-- Granularity note: Phase 1 instruments `page_completed` events per survey
-- page (business / infrastructure / apps / compliance / budget), not per
-- individual question. This is page-level drop-off, not question-level -
-- that would need a JS/backend change to fire an event per answer, which
-- Phase 1 deliberately didn't do to avoid over-instrumenting a debounced
-- autosave flow.
SELECT
    se.page_name,
    COUNT(DISTINCT se.assessment_id) AS reached_this_page,
    ROUND(100.0 * COUNT(DISTINCT se.assessment_id)
        / NULLIF((SELECT COUNT(DISTINCT assessment_id) FROM survey_events WHERE event = 'survey_started'), 0), 1)
        AS pct_of_starters_who_reached_it
FROM survey_events se
WHERE se.event = 'page_completed'
GROUP BY se.page_name
ORDER BY reached_this_page DESC;
