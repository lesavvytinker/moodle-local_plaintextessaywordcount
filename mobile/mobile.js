// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Moodle app support for Essay (word count) questions.
 *
 * The app runs this file once when it loads the plugin. The object at the end ("result") gives:
 * - componentInit: run for each question shown, with "this" being that question's component.
 *   It reads the question HTML Moodle rendered and sets up what essaywc.html displays.
 * - isCompleteResponse / isGradableResponse / isSameResponse: the same checks the app uses for
 *   core Essay, so saving, the summary page and offline attempts behave the same way.
 *
 * Supported in the app: the plain-text formats (with or without the word count), no attachments.
 * Anything else (HTML editor formats, file attachments) shows the app's usual
 * "this question can't be answered in the app" message.
 *
 * @copyright  2026 Harvey, Equip English
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// The object holding the app's libraries while this file is loaded.
var essaywcLibs = this;

/**
 * Count words the same way as Moodle's count_words($text, FORMAT_PLAIN).
 * Keep in step with amd/src/wordcount.js.
 *
 * @param {string} text
 * @returns {number}
 */
var essaywcCountWords = function(text) {
    if (!text) {
        return 0;
    }
    text = text.replace(/(\w|^|\s)<(?![^<>]*>)(?=\w)/gu, '$1');
    text = text.replace(/(<\/(?!(?:a|b|del|em|i|ins|s|small|span|strong|sub|sup|u)>)\w+>|<br>|<br\s*\/>)/g, '$1 ');
    text = text.replace(/<!--[\s\S]*?(-->|$)/g, '').replace(/<(?!\s)[^>]*(>|$)/g, '');
    if (text.indexOf('&') !== -1) {
        var decoder = document.createElement('textarea');
        decoder.innerHTML = text;
        text = decoder.value;
    }
    return text.split(/[\p{Z}\p{Cc}—–]+/u).filter(function(w) {
        return w !== '';
    }).length;
};

/**
 * Get the word limits for a question, from the site's settings or from the rendered counter.
 *
 * @param {object} question the question from the app.
 * @returns {{min: number, max: number}}
 */
var essaywcLimits = function(question) {
    var settings = question && question.parsedSettings;
    if (settings && (settings.minwordlimit !== undefined || settings.maxwordlimit !== undefined)) {
        return {min: parseInt(settings.minwordlimit, 10) || 0, max: parseInt(settings.maxwordlimit, 10) || 0};
    }
    var div = document.createElement('div');
    div.innerHTML = (question && question.html) || '';
    var counter = div.querySelector('.qtype_essaywc-counter');
    return {
        min: counter ? parseInt(counter.getAttribute('data-min'), 10) || 0 : 0,
        max: counter ? parseInt(counter.getAttribute('data-max'), 10) || 0 : 0,
    };
};

var result = {
    componentInit: function() {
        var self = this;
        var question = this.question;
        if (!question) {
            return essaywcLibs.CoreQuestionHelperProvider.showComponentError(this.onAbort);
        }

        var div = document.createElement('div');
        div.innerHTML = question.html;

        // The app cannot handle file attachments or the HTML editor for a plugin question type.
        // (Moodle always prints an empty .attachments box, so look for an upload field or attached files in it.)
        var hasAttachments = !!div.querySelector('div[id*=filemanager], .attachments input, .attachments a');
        var textarea = div.querySelector('textarea[name*=_answer]');
        var isPlain = !!div.querySelector('.qtype_essay_plain, .qtype_essay_monospaced');
        var isEditor = !!div.querySelector('.qtype_essay_editor, .qtype_essay_editorfilepicker');
        if (hasAttachments || isEditor || (textarea && !isPlain)) {
            return essaywcLibs.CoreQuestionHelperProvider.showComponentError(this.onAbort);
        }

        var qtext = div.querySelector('.qtext');
        if (!qtext) {
            return essaywcLibs.CoreQuestionHelperProvider.showComponentError(this.onAbort);
        }
        question.text = qtext.innerHTML;
        question.isMonospaced = !!div.querySelector('.qtype_essay_monospaced');

        // The counter as Moodle rendered it. Its labels come from the site, already translated.
        var counter = div.querySelector('.qtype_essaywc-counter');
        var counterText = function(selector) {
            var el = counter && counter.querySelector(selector);
            return el ? el.textContent : '';
        };
        this.wc = counter ? {
            label: counterText('.qtype_essaywc-label'),
            limits: counterText('.qtype_essaywc-limits'),
            min: parseInt(counter.getAttribute('data-min'), 10) || 0,
            max: parseInt(counter.getAttribute('data-max'), 10) || 0,
            strUnder: counter.getAttribute('data-str-under') || '',
            strOver: counter.getAttribute('data-str-over') || '',
            count: 0,
            state: 'is-neutral',
            status: '',
        } : null;

        /**
         * Recalculate the counter for some text.
         *
         * @param {string} value the answer text.
         */
        this.updateWordCount = function(value) {
            var wc = self.wc;
            if (!wc) {
                return;
            }
            wc.count = essaywcCountWords(value || '');
            wc.state = (wc.min || wc.max) ? 'is-ok' : 'is-neutral';
            wc.status = '';
            if (wc.max && wc.count > wc.max) {
                wc.state = 'is-over';
                wc.status = wc.strOver;
            } else if (wc.min && wc.count < wc.min) {
                wc.state = 'is-under';
                wc.status = wc.strUnder;
            }
        };

        /**
         * Handle typing in the answer box.
         *
         * @param {CustomEvent} event Ionic ionInput event.
         */
        this.onAnswerInput = function(event) {
            self.updateWordCount(event && event.detail ? event.detail.value : '');
        };

        if (this.review || !textarea || textarea.hasAttribute('readonly')) {
            // Reviewing: show the answer and Moodle's word count line, no live counter.
            question.reviewing = true;
            // The read-only answer box has no name, so find it by its class.
            var shown = textarea || div.querySelector('textarea.qtype_essay_response');
            question.answer = shown ? shown.value : '';
            var wordCountLine = div.querySelector('.answer > p');
            question.wordCountInfo = wordCountLine ? wordCountLine.innerHTML : '';
            this.wc = null;
            return true;
        }

        var formatInput = div.querySelector('input[type="hidden"][name*=answerformat]');
        question.textarea = {
            name: textarea.name,
            text: textarea.value,
        };
        question.formatInput = formatInput ? {name: formatInput.name, value: formatInput.value} : null;
        this.updateWordCount(question.textarea.text);

        return true;
    },

    /**
     * Whether the answer is complete: some text, inside any word limits (as Moodle decides).
     *
     * @param {object} question question.
     * @param {object} answers this question's answers.
     * @returns {number} 1 yes, 0 no.
     */
    isCompleteResponse: function(question, answers) {
        var answer = answers && answers.answer ? String(answers.answer) : '';
        if (answer === '') {
            return 0;
        }
        var limits = essaywcLimits(question);
        var count = essaywcCountWords(answer);
        if ((limits.max && count > limits.max) || (limits.min && count < limits.min)) {
            return 0;
        }
        return 1;
    },

    /**
     * Whether the answer can be graded: any text at all.
     *
     * @param {object} question question.
     * @param {object} answers this question's answers.
     * @returns {number} 1 yes, 0 no.
     */
    isGradableResponse: function(question, answers) {
        return answers && answers.answer ? 1 : 0;
    },

    /**
     * Whether the answer has not changed.
     *
     * @param {object} question question.
     * @param {object} prevAnswers previous answers.
     * @param {object} newAnswers new answers.
     * @returns {boolean}
     */
    isSameResponse: function(question, prevAnswers, newAnswers) {
        var prev = prevAnswers && prevAnswers.answer ? String(prevAnswers.answer) : '';
        var next = newAnswers && newAnswers.answer ? String(newAnswers.answer) : '';
        return prev === next;
    },
};

/* eslint-disable no-unused-expressions */
result;
