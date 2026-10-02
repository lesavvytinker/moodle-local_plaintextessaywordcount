# Essay (word count) — qtype_essaywc

A Moodle question type that works exactly like the core **Essay** question, with one extra
response format: **Plain text, with word count**.

With that format, students see a live word count under the answer box as they type. If the
question has a minimum or maximum word limit, the counter also shows the limit and flags when
the answer is below the minimum or over the maximum.

Built for PTE/IELTS-style writing tasks (Summarise Written Text, Write Essay), where students need to
watch word limits while they write.

## What it does

- Adds "Plain text, with word count" to the Response format list. All the core Essay formats
  are still there, so this type can do anything Essay can.
- Counts words with the same rules as Moodle's own `count_words()`, so the number students see
  matches the number Moodle uses for word limits and shows on the grading screen.
- The count runs entirely in the browser. No server calls, no AI, and it keeps working on a
  poor or dropped connection.
- On review and manual-grading screens, the word count is always shown to the marker, even when
  no word limits are set.
- Grading is manual, exactly as with Essay: same grading screen, mark box, comment box and
  "Information for graders".

## Links

- Source code: https://github.com/lesavvytinker/moodle-qtype_essaywc
- Report a bug or request a feature: https://github.com/lesavvytinker/moodle-qtype_essaywc/issues

## Requirements

- Moodle 5.0 or 5.1
- Core Essay question type (always present)

## Install

1. Site administration → Plugins → Install plugins → upload `qtype_essaywc.zip`.
   Or unzip into `question/type/essaywc` (`public/question/type/essaywc` on Moodle 5.1) and
   visit Site administration → Notifications.
2. Add a question → **Essay (word count)**. "Plain text, with word count" is selected by default.

## Converting existing Essay questions

Moodle can't change a question's type in the editor. To convert:

1. Export the Essay questions to Moodle XML.
2. Change `<question type="essay">` to `<question type="essaywc">`.
3. Optionally change `<responseformat>plain</responseformat>` to
   `<responseformat>plainwordcount</responseformat>`.
4. Import the file.

Everything else in the XML (word limits, grader information and its files, response template)
is the same format as core Essay. Imported questions are new questions with new IDs, so anything
keyed to the old question IDs (for example a saved rubric) needs to be re-attached.

## Working with other plugins

Other plugins that check the question type by name need `essaywc` added alongside `essay`.
Things to look for:

- PHP: `$question->qtype === 'essay'` or `qtype->name() == 'essay'`
- SQL: `q.qtype = 'essay'` or `q.qtype IN ('essay', ...)`
- JS/CSS selectors: `.que.essay`. This type's wrapper is `.que.essaywc`.

The grading screen markup (mark input, comment editor, grader information) is identical to core
Essay, so plugins that work from that markup need no change.

## Development notes

- The type extends the core Essay classes and stores its options in its own table,
  `qtype_essaywc_options` (same columns as `qtype_essay_options`). It never reads or writes
  core Essay data.
- Grader information files are stored in the `qtype_essaywc` / `graderinfo` file area.
- Rebuild the JS after editing `amd/src/wordcount.js` with `npx grunt amd` from the Moodle root.

## Licence

GNU GPL v3 or later.
