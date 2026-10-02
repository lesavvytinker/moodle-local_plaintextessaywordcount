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
 * Question type class for Essay (word count).
 *
 * @package    qtype_essaywc
 * @copyright  2026 Harvey, Equip English
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/type/essay/questiontype.php');

/**
 * Essay (word count) question type.
 *
 * Behaves exactly like core Essay, but adds a "Plain text, with word count"
 * response format and stores its options in its own table so it never
 * touches core Essay data.
 *
 * @copyright  2026 Harvey, Equip English
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_essaywc extends qtype_essay {
    /** @var string The response format this plugin adds. */
    public const FORMAT_PLAINWORDCOUNT = 'plainwordcount';

    #[\Override]
    public function get_question_options($question) {
        global $DB;
        $question->options = $DB->get_record(
            'qtype_essaywc_options',
            ['questionid' => $question->id],
            '*',
            MUST_EXIST
        );
        // Skip qtype_essay::get_question_options(), which reads the core Essay table.
        return question_type::get_question_options($question);
    }

    #[\Override]
    public function save_question_options($formdata) {
        global $DB;
        $context = $formdata->context;

        $options = $DB->get_record('qtype_essaywc_options', ['questionid' => $formdata->id]);
        if (!$options) {
            $options = new stdClass();
            $options->questionid = $formdata->id;
            $options->id = $DB->insert_record('qtype_essaywc_options', $options);
        }

        $options->responseformat = $formdata->responseformat;
        $options->responserequired = $formdata->responserequired;
        $options->responsefieldlines = $formdata->responsefieldlines;
        $options->minwordlimit = !empty($formdata->minwordenabled) ? $formdata->minwordlimit : null;
        $options->maxwordlimit = !empty($formdata->maxwordenabled) ? $formdata->maxwordlimit : null;
        $options->attachments = $formdata->attachments;
        if ((int)$formdata->attachments === 0 && $formdata->attachmentsrequired > 0) {
            $options->attachmentsrequired = 0;
        } else {
            $options->attachmentsrequired = $formdata->attachmentsrequired;
        }
        $options->filetypeslist = $formdata->filetypeslist ?? null;
        $options->maxbytes = $formdata->maxbytes ?? 0;
        $options->graderinfo = $this->import_or_save_files(
            $formdata->graderinfo,
            $context,
            'qtype_essaywc',
            'graderinfo',
            $formdata->id
        );
        $options->graderinfoformat = $formdata->graderinfo['format'];
        $options->responsetemplate = $formdata->responsetemplate['text'];
        $options->responsetemplateformat = $formdata->responsetemplate['format'];
        $DB->update_record('qtype_essaywc_options', $options);
    }

    #[\Override]
    public function delete_question($questionid, $contextid) {
        global $DB;
        $DB->delete_records('qtype_essaywc_options', ['questionid' => $questionid]);
        question_type::delete_question($questionid, $contextid);
    }

    #[\Override]
    public function response_formats() {
        $formats = [];
        foreach (parent::response_formats() as $key => $label) {
            $formats[$key] = $label;
            if ($key === 'plain') {
                $formats[self::FORMAT_PLAINWORDCOUNT] = get_string('formatplainwordcount', 'qtype_essaywc');
            }
        }
        return $formats;
    }

    #[\Override]
    public function move_files($questionid, $oldcontextid, $newcontextid) {
        question_type::move_files($questionid, $oldcontextid, $newcontextid);
        $fs = get_file_storage();
        $fs->move_area_files_to_new_context(
            $oldcontextid,
            $newcontextid,
            'qtype_essaywc',
            'graderinfo',
            $questionid
        );
    }

    #[\Override]
    protected function delete_files($questionid, $contextid) {
        question_type::delete_files($questionid, $contextid);
        $fs = get_file_storage();
        $fs->delete_area_files($contextid, 'qtype_essaywc', 'graderinfo', $questionid);
    }

    /**
     * Import from Moodle XML. The XML is identical to core Essay apart from type="essaywc",
     * so an existing Essay can be converted by changing only that attribute.
     *
     * @param array $data the XML tree for one question.
     * @param stdClass|null $question partially-built question, unused.
     * @param qformat_xml $format the importer.
     * @param mixed $extra unused.
     * @return stdClass|false the question object, or false if this XML is not ours.
     */
    public function import_from_xml($data, $question, qformat_xml $format, $extra = null) {
        if (!isset($data['@']['type']) || $data['@']['type'] !== 'essaywc') {
            return false;
        }
        $qo = $format->import_essay($data);
        $qo->qtype = 'essaywc';
        // Core Essay defaults the format to the HTML editor. Default ours to the word-count box.
        $qo->responseformat = $format->getpath(
            $data,
            ['#', 'responseformat', 0, '#'],
            self::FORMAT_PLAINWORDCOUNT
        );
        // The importer stores graderinfo files against the question's own component,
        // which is already qtype_essaywc because $qo->qtype is set above.
        return $qo;
    }

    /**
     * Export to Moodle XML, in exactly the same shape as core Essay.
     *
     * @param stdClass $question the question data.
     * @param qformat_xml $format the exporter.
     * @param mixed $extra unused.
     * @return string XML fragment.
     */
    public function export_to_xml($question, qformat_xml $format, $extra = null) {
        $fs = get_file_storage();
        $o = $question->options;
        $expout = '';
        $expout .= "    <responseformat>" . $o->responseformat . "</responseformat>\n";
        $expout .= "    <responserequired>" . $o->responserequired . "</responserequired>\n";
        $expout .= "    <responsefieldlines>" . $o->responsefieldlines . "</responsefieldlines>\n";
        $expout .= "    <minwordlimit>" . $o->minwordlimit . "</minwordlimit>\n";
        $expout .= "    <maxwordlimit>" . $o->maxwordlimit . "</maxwordlimit>\n";
        $expout .= "    <attachments>" . $o->attachments . "</attachments>\n";
        $expout .= "    <attachmentsrequired>" . $o->attachmentsrequired . "</attachmentsrequired>\n";
        $expout .= "    <maxbytes>" . $o->maxbytes . "</maxbytes>\n";
        $expout .= "    <filetypeslist>" . $o->filetypeslist . "</filetypeslist>\n";
        $expout .= "    <graderinfo " . $format->format($o->graderinfoformat) . ">\n";
        $expout .= $format->writetext($o->graderinfo, 3);
        $expout .= $format->write_files($fs->get_area_files(
            $question->contextid,
            'qtype_essaywc',
            'graderinfo',
            $question->id
        ));
        $expout .= "    </graderinfo>\n";
        $expout .= "    <responsetemplate " . $format->format($o->responsetemplateformat) . ">\n";
        $expout .= $format->writetext($o->responsetemplate, 3);
        $expout .= "    </responsetemplate>\n";
        return $expout;
    }
}
