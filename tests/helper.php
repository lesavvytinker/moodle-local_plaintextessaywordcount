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
 * Test helper for Essay (word count) questions.
 *
 * @package    qtype_essaywc
 * @copyright  2026 Harvey, Equip English
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_essaywc_test_helper extends question_test_helper {
    #[\Override]
    public function get_test_questions() {
        return ['plainwordcount', 'plain'];
    }

    /**
     * Make a question object.
     *
     * @param string $format response format.
     * @return qtype_essaywc_question
     */
    protected function make_question(string $format): qtype_essaywc_question {
        question_bank::load_question_definition_classes('essaywc');
        $q = new qtype_essaywc_question();
        test_question_maker::initialise_a_question($q);
        $q->name = 'Summarise written text';
        $q->questiontext = 'Summarise the passage in one sentence of 5 to 75 words.';
        $q->generalfeedback = '';
        $q->responseformat = $format;
        $q->responserequired = 1;
        $q->responsefieldlines = 10;
        $q->minwordlimit = 5;
        $q->maxwordlimit = 75;
        $q->attachments = 0;
        $q->attachmentsrequired = 0;
        $q->maxbytes = 0;
        $q->filetypeslist = null;
        $q->graderinfo = '';
        $q->graderinfoformat = FORMAT_HTML;
        $q->responsetemplate = '';
        $q->responsetemplateformat = FORMAT_PLAIN;
        $q->qtype = question_bank::get_qtype('essaywc');
        return $q;
    }

    /**
     * Question using the word-count format.
     *
     * @return qtype_essaywc_question
     */
    public function make_essaywc_question_plainwordcount() {
        return $this->make_question('plainwordcount');
    }

    /**
     * Question using the ordinary plain-text format.
     *
     * @return qtype_essaywc_question
     */
    public function make_essaywc_question_plain() {
        return $this->make_question('plain');
    }

    /**
     * Form data for the word-count format.
     *
     * @return stdClass
     */
    public function get_essaywc_question_form_data_plainwordcount() {
        $fromform = new stdClass();
        $fromform->name = 'Summarise written text';
        $fromform->questiontext = ['text' => 'Summarise the passage in one sentence of 5 to 75 words.', 'format' => FORMAT_HTML];
        $fromform->defaultmark = 1.0;
        $fromform->generalfeedback = ['text' => '', 'format' => FORMAT_HTML];
        $fromform->responseformat = 'plainwordcount';
        $fromform->responserequired = 1;
        $fromform->responsefieldlines = 10;
        $fromform->minwordenabled = 1;
        $fromform->minwordlimit = 5;
        $fromform->maxwordenabled = 1;
        $fromform->maxwordlimit = 75;
        $fromform->attachments = 0;
        $fromform->attachmentsrequired = 0;
        $fromform->maxbytes = 0;
        $fromform->filetypeslist = '';
        $fromform->graderinfo = ['text' => '<p>Check content, form and grammar.</p>', 'format' => FORMAT_HTML];
        $fromform->responsetemplate = ['text' => '', 'format' => FORMAT_PLAIN];
        $fromform->status = \core_question\local\bank\question_version_status::QUESTION_STATUS_READY;
        return $fromform;
    }

    /**
     * Form data for the ordinary plain-text format.
     *
     * @return stdClass
     */
    public function get_essaywc_question_form_data_plain() {
        $fromform = $this->get_essaywc_question_form_data_plainwordcount();
        $fromform->responseformat = 'plain';
        return $fromform;
    }
}
