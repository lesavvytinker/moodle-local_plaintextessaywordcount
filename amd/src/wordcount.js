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
 * Live word counter for the "Plain text, with word count" essay format.
 *
 * Runs entirely in the browser (no server calls), so it keeps working offline.
 * The counting rules copy Moodle's count_words() for plain text, so the number
 * the student sees is the same number Moodle uses for word limits and grading.
 *
 * @module     qtype_essaywc/wordcount
 * @copyright  2026 Harvey, Equip English
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Decode HTML entities, as PHP html_entity_decode() does.
 *
 * @param {string} text
 * @returns {string}
 */
const decodeEntities = (text) => {
    if (text.indexOf('&') === -1) {
        return text;
    }
    const doc = new DOMParser().parseFromString('<!doctype html><body>', 'text/html');
    const el = doc.createElement('textarea');
    el.innerHTML = text;
    return el.value;
};

/**
 * Remove HTML tags, as PHP strip_tags() does for the cases students actually type.
 *
 * @param {string} text
 * @returns {string}
 */
const stripTags = (text) => text
    .replace(/<!--[\s\S]*?(-->|$)/g, '')
    // Like PHP, a "<" followed by anything other than white space starts a tag, which runs to ">" or the end.
    .replace(/<(?!\s)[^>]*(>|$)/g, '');

/**
 * Count words the same way as Moodle's count_words($text, FORMAT_PLAIN).
 *
 * @param {string} text
 * @returns {number}
 */
export const countWords = (text) => {
    if (!text) {
        return 0;
    }
    // A "<" stuck to the front of a word, that does not start a real tag, is not a tag.
    text = text.replace(/(\w|^|\s)<(?![^<>]*>)(?=\w)/gu, '$1');
    // Block-level closing tags and <br> separate words.
    text = text.replace(/(<\/(?!(?:a|b|del|em|i|ins|s|small|span|strong|sub|sup|u)>)\w+>|<br>|<br\s*\/>)/g, '$1 ');
    text = decodeEntities(stripTags(text));
    // Words are separated by Unicode spaces/separators, control characters, en dash and em dash.
    return text.split(/[\p{Z}\p{Cc}—–]+/u).filter((w) => w !== '').length;
};

/**
 * Attach the counter to one textarea.
 *
 * @param {string} textareaId id of the response textarea.
 * @param {string} counterId id of the counter box.
 */
export const init = (textareaId, counterId) => {
    const textarea = document.getElementById(textareaId);
    const counter = document.getElementById(counterId);
    if (!textarea || !counter || counter.dataset.wcReady) {
        return;
    }
    counter.dataset.wcReady = '1';

    const number = counter.querySelector('.qtype_essaywc-number');
    const status = counter.querySelector('.qtype_essaywc-status');
    const min = parseInt(counter.dataset.min, 10) || 0;
    const max = parseInt(counter.dataset.max, 10) || 0;

    const update = () => {
        const count = countWords(textarea.value);
        number.textContent = String(count);

        let state = (min || max) ? 'is-ok' : 'is-neutral';
        let text = '';
        if (max && count > max) {
            state = 'is-over';
            text = counter.dataset.strOver;
        } else if (min && count < min) {
            state = 'is-under';
            text = counter.dataset.strUnder;
        }
        counter.classList.remove('is-ok', 'is-neutral', 'is-under', 'is-over');
        counter.classList.add(state);
        status.textContent = text;
    };

    textarea.addEventListener('input', update);
    update();
};
