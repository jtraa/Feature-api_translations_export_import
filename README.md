<b>API Translations – Import & Export</b>

This feature makes it possible to export and import translation files in the application.
Only the files that were modified in the Laravel project are included in this commit.

<b>Goal of this feature</b>

To easily update translations inside the system without manually editing JSON / PHP files in the codebase.

Instead of developers manually changing translation files, we can now export the current translations, edit them (for example in Excel / Google Sheets), and import them back in.

<b>How it works</b>

<b>Export</b>
	•	Export all (or selected) translations
	•	Output as a clean data format (CSV / JSON)
	•	File can then be edited outside of the system

<b>Import</b>
	•	Import translations to merge with current tranlsations
	•	The system updates the translations based on the imported data

<b>Why this feature exists</b>
	•	Faster translations updates
	•	Non-developers can update wording
	•	No manual editing inside the repository
	•	Cleaner flow for proofreading & corrections

<b>Use cases</b>
	•	New languages
	•	Corrections from external translators
	•	Bulk editing
