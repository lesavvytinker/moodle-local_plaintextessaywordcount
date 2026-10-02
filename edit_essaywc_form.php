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
 * Editing form for Essay (word count) questions.
 *
 * @package    qtype_essaywc
 * @copyright  2026 Harvey, Equip English
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/essay/edit_essay_form.php');

/**
 * Same form as core Essay, with the extra response format and this plugin's file area.
 *
 * @copyright  2026 Harvey, Equip English
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_essaywc_edit_form extends qtype_essay_edit_form {
    #[\Override]
    protected function definition_inner($mform) {
        parent::definition_inner($mform);

        // The core form builds this list from qtype_essay. Swap in ours, which adds the word-count format.
        /** @var MoodleQuickForm_select $select */
        $select = $mform->getElement('responseformat');
        $select->removeOptions();
        $select->loadArray(question_bank::get_qtype('essaywc')->response_formats());
        $mform->setDefault(
            'responseformat',
            $this->get_default_value('responseformat', qtype_essaywc::FORMAT_PLAINWORDCOUNT)
        );
    }

    #[\Override]
    protected function data_preprocessing($question) {
        // Skip the core Essay version, which would load grader info files from the core Essay file area.
        $question = question_edit_form::data_preprocessing($question);

        if (empty($question->options)) {
            return $question;
        }

        $question->responseformat = $question->options->responseformat;
        $question->responserequired = $question->options->responserequired;
        $question->responsefieldlines = $question->options->responsefieldlines;
        $question->minwordenabled = $question->options->minwordlimit ? 1 : 0;
        $question->minwordlimit = $question->options->minwordlimit;
        $question->maxwordenabled = $question->options->maxwordlimit ? 1 : 0;
        $question->maxwordlimit = $question->options->maxwordlimit;
        $question->attachments = $question->options->attachments;
        $question->attachmentsrequired = $question->options->attachmentsrequired;
        $question->filetypeslist = $question->options->filetypeslist;
        $question->maxbytes = $question->options->maxbytes;

        $draftid = file_get_submitted_draft_itemid('graderinfo');
        $question->graderinfo = [];
        $question->graderinfo['text'] = file_prepare_draft_area(
            $draftid,
            $this->context->id,
            'qtype_essaywc',
            'graderinfo',
            !empty($question->id) ? (int) $question->id : null,
            $this->fileoptions,
            $question->options->graderinfo
        );
        $question->graderinfo['format'] = $question->options->graderinfoformat;
        $question->graderinfo['itemid'] = $draftid;

        $question->responsetemplate = [
            'text' => $question->options->responsetemplate,
            'format' => $question->options->responsetemplateformat,
        ];

        return $question;
    }

    #[\Override]
    public function qtype() {
        return 'essaywc';
    }
}
