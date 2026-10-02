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
 * Backup support for Essay (word count) questions.
 *
 * @package    qtype_essaywc
 * @copyright  2026 Harvey, Equip English
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_qtype_essaywc_plugin extends backup_qtype_plugin {
    /**
     * Returns the qtype information to attach to the question element.
     *
     * @return backup_plugin_element
     */
    protected function define_question_plugin_structure() {
        $plugin = $this->get_plugin_element(null, '../../qtype', 'essaywc');
        $pluginwrapper = new backup_nested_element($this->get_recommended_name());
        $plugin->add_child($pluginwrapper);

        $essaywc = new backup_nested_element('essaywc', ['id'], [
                'responseformat', 'responserequired', 'responsefieldlines', 'minwordlimit', 'maxwordlimit',
                'attachments', 'attachmentsrequired', 'graderinfo', 'graderinfoformat', 'responsetemplate',
                'responsetemplateformat', 'filetypeslist', 'maxbytes']);
        $pluginwrapper->add_child($essaywc);

        $essaywc->set_source_table('qtype_essaywc_options', ['questionid' => backup::VAR_PARENTID]);

        return $plugin;
    }

    /**
     * File areas used by this question type.
     *
     * @return array filearea => mapping name
     */
    public static function get_qtype_fileareas() {
        return ['graderinfo' => 'question_created'];
    }
}
