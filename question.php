<?php
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
 * Essay (word count) question definition class.
 *
 * @package    qtype_essaywc
 * @copyright  2026 Harvey, Equip English
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/essay/question.php');

/**
 * Represents an Essay (word count) question. Identical to core Essay except for
 * where its renderers and grader-information files live.
 *
 * @copyright  2026 Harvey, Equip English
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_essaywc_question extends qtype_essay_question {
    #[\Override]
    public function get_format_renderer(moodle_page $page) {
        if ($this->responseformat === 'plainwordcount') {
            return $page->get_renderer('qtype_essaywc', 'format_plainwordcount');
        }
        // Every other format reuses the core Essay renderers unchanged.
        return $page->get_renderer('qtype_essay', 'format_' . $this->responseformat);
    }

    #[\Override]
    public function check_file_access($qa, $options, $component, $filearea, $args, $forcedownload) {
        if ($component === 'qtype_essaywc' && $filearea === 'graderinfo') {
            return $options->manualcomment && $args[0] == $this->id;
        }
        return parent::check_file_access($qa, $options, $component, $filearea, $args, $forcedownload);
    }

    /**
     * Always show the word count when reviewing or grading, not only when word limits are set.
     *
     * @param array $response the response data.
     * @return string the word count message, or '' if there is no text response.
     */
    #[\Override]
    public function get_word_count_message_for_review(array $response): string {
        $message = parent::get_word_count_message_for_review($response);
        if ($message !== '' || $this->responseformat === 'noinline') {
            return $message;
        }
        if (!array_key_exists('answer', $response) || $response['answer'] === '') {
            return '';
        }
        $count = count_words($response['answer'], $response['answerformat'] ?? FORMAT_PLAIN);
        return get_string('wordcount', 'qtype_essay', $count);
    }
}
