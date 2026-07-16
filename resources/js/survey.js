import { Model } from 'survey-js-ui';
import 'survey-core/survey-core.min.css';

document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('surveyContainer');

    if (!container || !window.SURVEY_CONFIG) {
        return;
    }

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
        post(config.completeUrl, { data: sender.data })
            .then((response) => response.json())
            .then((result) => {
                if (result.redirect) {
                    window.location.href = result.redirect;
                }
            });
    });

    survey.render(container);
});
