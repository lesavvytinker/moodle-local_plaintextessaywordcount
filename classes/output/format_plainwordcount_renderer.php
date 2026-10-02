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

namespace qtype_essaywc\output;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/type/essay/renderer.php');

/**
 * "Plain text, with word count" response format: a plain textarea with a live word counter underneath.
 *
 * @package    qtype_essaywc
 * @copyright  2026 Harvey, Equip English
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_plainwordcount_renderer extends \qtype_essay_format_plain_renderer {
    #[\Override]
    protected function class_name() {
        // Keep the core class so existing theme styling for plain-text essays still applies.
        return 'qtype_essay_plain qtype_essaywc_plain';
    }

    #[\Override]
    public function response_area_input($name, $qa, $step, $lines, $context) {
        $inputname = $qa->get_qt_field_name($name);
        $id = $inputname . '_id';
        $counterid = $id . '_wc';
        $response = (string) $step->get_qt_var($name);

        $responselabel = $this->displayoptions->add_question_identifier_to_label(get_string('answertext', 'qtype_essay'));
        $output = \html_writer::tag('label', $responselabel, ['class' => 'visually-hidden', 'for' => $id]);
        $output .= $this->textarea(
            $response,
            $lines,
            ['name' => $inputname, 'id' => $id, 'aria-describedby' => $counterid]
        );
        $output .= \html_writer::empty_tag(
            'input',
            ['type' => 'hidden', 'name' => $inputname . 'format', 'value' => \FORMAT_PLAIN]
        );
        $output .= $this->counter($counterid, $response, $qa->get_question());

        $this->page->requires->js_call_amd('qtype_essaywc/wordcount', 'init', [$id, $counterid]);
        return $output;
    }

    /**
     * The counter box. It is filled in on the server too, so it shows the right number
     * as soon as the page loads, even before (or without) JavaScript.
     *
     * @param string $counterid id for the counter element.
     * @param string $response the current response text.
     * @param \qtype_essay_question $question the question, for its word limits.
     * @return string HTML.
     */
    protected function counter(string $counterid, string $response, \qtype_essay_question $question): string {
        $min = (int) $question->minwordlimit;
        $max = (int) $question->maxwordlimit;
        $count = $response === '' ? 0 : count_words($response, \FORMAT_PLAIN);

        if ($min && $max) {
            $limits = get_string('limitsrange', 'qtype_essaywc', ['min' => $min, 'max' => $max]);
        } else if ($min) {
            $limits = get_string('limitsmin', 'qtype_essaywc', $min);
        } else if ($max) {
            $limits = get_string('limitsmax', 'qtype_essaywc', $max);
        } else {
            $limits = '';
        }

        [$state, $statustext] = self::state($count, $min, $max);

        $inner = \html_writer::tag(
            'span',
            get_string('wordcountlabel', 'qtype_essaywc'),
            ['class' => 'qtype_essaywc-label']
        );
        $inner .= ' ' . \html_writer::tag('span', $count, ['class' => 'qtype_essaywc-number']);
        if ($limits !== '') {
            $inner .= ' ' . \html_writer::tag('span', $limits, ['class' => 'qtype_essaywc-limits']);
        }
        $inner .= ' ' . \html_writer::tag('span', $statustext, ['class' => 'qtype_essaywc-status']);

        return \html_writer::div($inner, 'qtype_essaywc-counter ' . $state, [
            'id' => $counterid,
            'data-min' => $min,
            'data-max' => $max,
            'data-str-under' => get_string('statusunder', 'qtype_essaywc'),
            'data-str-over' => get_string('statusover', 'qtype_essaywc'),
        ]);
    }

    /**
     * Work out the counter state for a word count.
     *
     * @param int $count words typed.
     * @param int $min minimum, 0 for none.
     * @param int $max maximum, 0 for none.
     * @return array [css class, status text].
     */
    public static function state(int $count, int $min, int $max): array {
        if ($max && $count > $max) {
            return ['is-over', get_string('statusover', 'qtype_essaywc')];
        }
        if ($min && $count < $min) {
            return ['is-under', get_string('statusunder', 'qtype_essaywc')];
        }
        return [($min || $max) ? 'is-ok' : 'is-neutral', ''];
    }
}
