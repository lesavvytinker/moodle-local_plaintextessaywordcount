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
 * Strings for the Essay (word count) question type.
 *
 * @package    qtype_essaywc
 * @copyright  2026 Harvey, Equip English
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['formatplainwordcount'] = 'Plain text, with word count';
$string['limitsmax'] = '(no more than {$a} words)';
$string['limitsmin'] = '(at least {$a} words)';
$string['limitsrange'] = '({$a->min}–{$a->max} words)';
$string['pluginname'] = 'Essay (word count)';
$string['pluginname_help'] = 'Works exactly like the Essay question, with one extra response format: "Plain text, with word count". That format shows students a live word count under the answer box while they type. If you set a minimum or maximum word limit, the counter also shows the limit and flags when the answer is under or over it. Responses are graded manually.';
$string['pluginnameadding'] = 'Adding an Essay (word count) question';
$string['pluginnameediting'] = 'Editing an Essay (word count) question';
$string['pluginnamesummary'] = 'An Essay question with an extra "Plain text, with word count" response format that shows students a live word count as they type. Graded manually.';
$string['privacy:metadata'] = 'The Essay (word count) question type plugin allows question authors to set default options as user preferences.';
$string['privacy:preference:attachments'] = 'Number of allowed attachments.';
$string['privacy:preference:attachmentsrequired'] = 'Number of required attachments.';
$string['privacy:preference:defaultmark'] = 'The default mark set for a given question.';
$string['privacy:preference:maxbytes'] = 'Maximum file size.';
$string['privacy:preference:responsefieldlines'] = 'Number of lines indicating the size of the input box (textarea).';
$string['privacy:preference:responseformat'] = 'What is the response format (HTML editor, plain text, etc.)?';
$string['privacy:preference:responserequired'] = 'Whether the student is required to enter text or the text input is optional.';
$string['statusover'] = 'Over the limit';
$string['statusunder'] = 'Below the minimum';
$string['wordcountlabel'] = 'Word count:';
