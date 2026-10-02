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
 * Renderers for Essay (word count) questions.
 *
 * @package    qtype_essaywc
 * @copyright  2026 Harvey, Equip English
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/essay/renderer.php');

/**
 * Main renderer. Same as core Essay, but grader information comes from this plugin's file area.
 *
 * @copyright  2026 Harvey, Equip English
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_essaywc_renderer extends qtype_essay_renderer {
    #[\Override]
    public function manual_comment(question_attempt $qa, question_display_options $options) {
        if ($options->manualcomment != question_display_options::EDITABLE) {
            return '';
        }
        $question = $qa->get_question();
        return html_writer::nonempty_tag('div', $question->format_text(
            $question->graderinfo,
            $question->graderinfoformat,
            $qa,
            'qtype_essaywc',
            'graderinfo',
            $question->id
        ), ['class' => 'graderinfo']);
    }
}
