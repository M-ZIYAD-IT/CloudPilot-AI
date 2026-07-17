import { Model } from 'survey-js-ui';
import 'survey-core/survey-core.min.css';

// Shared markup for "something is happening / something went wrong" states
// inside the survey container — used both while SurveyJS is initializing and
// during the submit-then-redirect gap after the last page is completed.
function surveyStatusHtml(message, isError) {
    if (isError) {
        return `<div class="flex flex-col items-center justify-center gap-md py-20 text-center">
            <p class="text-body-sm text-danger max-w-sm">${message}</p>
        </div>`;
    }

    return `<div class="flex flex-col items-center justify-center gap-md py-20">
        <svg class="h-6 w-6 animate-spin text-ink-muted" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"></circle>
            <path class="opacity-90" d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
        </svg>
        <p class="text-body-sm text-ink-muted">${message}</p>
    </div>`;
}

document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('surveyContainer');

    if (!container || !window.SURVEY_CONFIG) {
        return;
    }

    try {
        initSurvey(container);
    } catch (error) {
        // survey.render() replaces the container's content on success, so the
        // loading spinner only survives if something threw before we got
        // there — show that failure instead of leaving a spinner forever.
        console.error('Survey failed to load:', error);
        container.innerHTML = surveyStatusHtml(
            "This assessment couldn't load. Try refreshing the page — if it keeps happening, contact support.",
            true
        );
    }
});

function initSurvey(container) {
    const config = window.SURVEY_CONFIG;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    function post(url, payload) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                Accept: 'application/json',
            },
            body: JSON.stringify(payload),
        });
    }

    const survey = new Model(config.json);
    survey.data = config.data;

    // We redirect to our own styled thank-you page on completion, so suppress
    // SurveyJS's built-in "Thank you for completing the survey" screen —
    // otherwise it flashes inside the widget for a moment before the redirect.
    survey.showCompletedPage = false;

    // SurveyJS applies its theme at runtime by writing CSS custom properties
    // directly onto the survey's root element (inline style), which beats any
    // ancestor-level CSS override — so the DESIGN.md dark palette has to be
    // passed through this API rather than set in app.css.
    survey.applyTheme({
        cssVariables: {
            '--sjs-general-backcolor': 'rgba(20, 20, 20, 1)',
            '--sjs-general-backcolor-dim': 'rgba(9, 9, 9, 1)',
            '--sjs-general-backcolor-dim-light': 'rgba(20, 20, 20, 1)',
            '--sjs-general-backcolor-dark': 'rgba(28, 28, 28, 1)',
            '--sjs-general-forecolor': 'rgba(255, 255, 255, 1)',
            '--sjs-general-forecolor-light': 'rgba(153, 153, 153, 1)',
            '--sjs-general-dim-forecolor': 'rgba(153, 153, 153, 1)',
            '--sjs-general-dim-forecolor-light': 'rgba(153, 153, 153, 0.7)',
            '--sjs-primary-backcolor': 'rgba(0, 153, 255, 1)',
            '--sjs-primary-backcolor-dark': 'rgba(0, 130, 217, 1)',
            '--sjs-primary-backcolor-light': 'rgba(0, 153, 255, 0.15)',
            '--sjs-primary-forecolor': 'rgba(255, 255, 255, 1)',
            '--sjs-primary-forecolor-light': 'rgba(255, 255, 255, 1)',
            '--sjs-secondary-backcolor': 'rgba(0, 153, 255, 1)',
            '--sjs-secondary-backcolor-light': 'rgba(0, 153, 255, 0.15)',
            '--sjs-secondary-forecolor': 'rgba(255, 255, 255, 1)',
            '--sjs-question-background': 'rgba(20, 20, 20, 1)',
            '--sjs-editor-background': 'rgba(9, 9, 9, 1)',
            '--sjs-border-default': 'rgba(38, 38, 38, 1)',
            '--sjs-border-light': 'rgba(26, 26, 26, 1)',
            '--sjs-border-inside': 'rgba(38, 38, 38, 1)',
            '--sjs-corner-radius': '10px',
            '--sjs-base-unit': '8px',
            '--sjs-font-family': "'Inter', ui-sans-serif, system-ui, sans-serif",
            '--sjs-special-red': 'rgba(248, 113, 113, 1)',
            '--sjs-special-red-light': 'rgba(248, 113, 113, 0.15)',
            '--sjs-special-red-forecolor': 'rgba(255, 255, 255, 1)',
            '--sjs-special-green': 'rgba(34, 197, 94, 1)',
            '--sjs-special-green-light': 'rgba(34, 197, 94, 0.15)',
            '--sjs-shadow-small': '0px 1px 2px 0px rgba(0, 0, 0, 0.35)',
            '--sjs-shadow-medium': '0px 2px 6px 0px rgba(0, 0, 0, 0.35)',
            '--sjs-shadow-large': '0px 8px 24px 0px rgba(0, 0, 0, 0.45)',
            '--sjs-shadow-inner': 'inset 0px 1px 2px 0px rgba(0, 0, 0, 0.35)',
        },
        isPanelless: true,
    });

    let saveTimer = null;
    survey.onValueChanged.add(function (sender) {
        clearTimeout(saveTimer);
        saveTimer = setTimeout(function () {
            post(config.saveUrl, { data: sender.data });
        }, 800);
    });

    survey.onCurrentPageChanged.add(function (sender, options) {
        clearTimeout(saveTimer);
        post(config.saveUrl, {
            data: sender.data,
            page: options.oldCurrentPage ? options.oldCurrentPage.name : null,
        });
    });

    survey.onComplete.add(function (sender) {
        // SurveyJS moves to its internal "completed" state the instant the last
        // page is submitted, and with showCompletedPage disabled that leaves the
        // container blank for however long the save/redirect round-trip takes.
        // Show a submitting state for that gap instead of nothing.
        container.innerHTML = surveyStatusHtml('Submitting your assessment&hellip;');

        post(config.completeUrl, { data: sender.data })
            .then((response) => {
                if (!response.ok) {
                    throw new Error(`Request failed with status ${response.status}`);
                }
                return response.json();
            })
            .then((result) => {
                if (result.redirect) {
                    window.location.href = result.redirect;
                } else {
                    throw new Error('Response did not include a redirect URL');
                }
            })
            .catch((error) => {
                console.error('Survey submission failed:', error);
                container.innerHTML = surveyStatusHtml(
                    'Something went wrong submitting your assessment. Your answers are saved — refresh the page to try submitting again.',
                    true
                );
            });
    });

    survey.render(container);
}
