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

namespace qtype_essaywc\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\writer;

/**
 * Privacy provider for qtype_essaywc. Stores only question-authoring default preferences.
 *
 * @package    qtype_essaywc
 * @copyright  2026 Harvey, Equip English
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\user_preference_provider {
    /** @var string[] Preference names (without the qtype_essaywc_ prefix) this plugin stores. */
    public const PREFERENCES = [
        'defaultmark',
        'responseformat',
        'responserequired',
        'responsefieldlines',
        'attachments',
        'attachmentsrequired',
        'maxbytes',
    ];

    #[\Override]
    public static function get_metadata(collection $collection): collection {
        foreach (self::PREFERENCES as $name) {
            $collection->add_user_preference('qtype_essaywc_' . $name, 'privacy:preference:' . $name);
        }
        return $collection;
    }

    #[\Override]
    public static function export_user_preferences(int $userid) {
        foreach (self::PREFERENCES as $name) {
            $value = get_user_preferences('qtype_essaywc_' . $name, null, $userid);
            if ($value === null) {
                continue;
            }
            writer::export_user_preference(
                'qtype_essaywc',
                $name,
                self::describe($name, $value),
                get_string('privacy:preference:' . $name, 'qtype_essaywc')
            );
        }
    }

    /**
     * Turn a stored preference value into something readable.
     *
     * @param string $name preference name.
     * @param mixed $value stored value.
     * @return string readable value.
     */
    protected static function describe(string $name, $value): string {
        switch ($name) {
            case 'responseformat':
                global $CFG;
                require_once($CFG->libdir . '/questionlib.php');
                $formats = \question_bank::get_qtype('essaywc')->response_formats();
                return $formats[$value] ?? (string) $value;
            case 'responserequired':
                return get_string($value ? 'responseisrequired' : 'responsenotrequired', 'qtype_essay');
            case 'responsefieldlines':
                return get_string('nlines', 'qtype_essay', $value);
            case 'attachments':
                if ($value == 0) {
                    return get_string('no');
                }
                return $value == -1 ? get_string('unlimited') : (string) $value;
            case 'attachmentsrequired':
                return $value == 0 ? get_string('attachmentsoptional', 'qtype_essay') : (string) $value;
            case 'maxbytes':
                return display_size((int) $value);
            default:
                return (string) $value;
        }
    }
}
