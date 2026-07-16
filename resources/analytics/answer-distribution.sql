-- Answer Distribution Dashboard ("market intelligence" aggregate)
-- Anonymized by construction: this groups by question_key/answer value only
-- and never joins to organizations, users, or any other identifying table.

-- SQLite (current dev database) - verified against seeded data.
-- SQLite's json_each() works uniformly whether `answers.value` holds a
-- scalar JSON string (radiogroup/dropdown questions) or a JSON array
-- (checkbox questions), so one query covers both.
SELECT
    a.question_key,
    je.value AS answer,
    COUNT(*) AS respondent_count,
    ROUND(100.0 * COUNT(*) / SUM(COUNT(*)) OVER (PARTITION BY a.question_key), 1) AS pct_of_respondents
FROM answers a, json_each(a.value) je
GROUP BY a.question_key, je.value
ORDER BY a.question_key, respondent_count DESC;

-- MySQL 8.0.4+ equivalent (untested locally - no MySQL instance available
-- in this dev environment; verify against real data before relying on it
-- in production). MySQL's JSON_TABLE requires a row source per array
-- element, so scalar and array values need separate branches.
--
-- SELECT
--     a.question_key,
--     jt.answer,
--     COUNT(*) AS respondent_count
-- FROM answers a
-- JOIN JSON_TABLE(
--     CASE WHEN JSON_TYPE(a.value) = 'ARRAY' THEN a.value ELSE JSON_ARRAY(a.value) END,
--     '$[*]' COLUMNS (answer VARCHAR(255) PATH '$')
-- ) AS jt
-- GROUP BY a.question_key, jt.answer
-- ORDER BY a.question_key, respondent_count DESC;
