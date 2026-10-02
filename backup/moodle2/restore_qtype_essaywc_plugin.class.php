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
 * Restore support for Essay (word count) questions.
 *
 * @package    qtype_essaywc
 * @copyright  2026 Harvey, Equip English
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_qtype_essaywc_plugin extends restore_qtype_plugin {
    /**
     * Paths handled by the plugin at question level.
     *
     * @return restore_path_element[]
     */
    protected function define_question_plugin_structure() {
        return [new restore_path_element('essaywc', $this->get_pathfor('/essaywc'))];
    }

    /**
     * Process the qtype/essaywc element.
     *
     * @param array $data the options row from the backup.
     */
    public function process_essaywc($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        $questioncreated = $this->get_mappingid(
            'question_created',
            $this->get_old_parentid('question')
        ) ? true : false;

        if ($questioncreated) {
            $data->questionid = $this->get_new_parentid('question');
            $newitemid = $DB->insert_record('qtype_essaywc_options', $data);
            $this->set_mapping('qtype_essaywc', $oldid, $newitemid);
        }
    }

    /**
     * Content to be processed by the links decoder.
     *
     * @return restore_decode_content[]
     */
    public static function define_decode_contents() {
        return [new restore_decode_content('qtype_essaywc_options', 'graderinfo', 'qtype_essaywc')];
    }
}
