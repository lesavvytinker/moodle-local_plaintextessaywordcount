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

/**
 * Supplies the Moodle app with the template and JavaScript for Essay (word count) questions.
 *
 * @package    qtype_essaywc
 * @copyright  2026 Harvey, Equip English
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mobile {
    /**
     * Return the app template and JavaScript. The question HTML itself comes from the quiz web services.
     *
     * @param array $args arguments from the app (unused).
     * @return array
     */
    public static function mobile_get_essaywc($args) {
        global $CFG;
        $dir = $CFG->dirroot . '/question/type/essaywc/mobile';
        return [
            'templates' => [
                [
                    'id' => 'main',
                    'html' => file_get_contents($dir . '/essaywc.html'),
                ],
            ],
            'javascript' => file_get_contents($dir . '/mobile.js'),
        ];
    }
}
