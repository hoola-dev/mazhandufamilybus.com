
START TRANSACTION;

SET @id := (SELECT `id` FROM `fields` WHERE `key` = "plugin_locale_titles");
UPDATE `multi_lang` SET `content` = 'Translate' WHERE `foreign_id` = @id AND `model` = "pjField" AND `field` = "title";

INSERT IGNORE INTO `fields` VALUES (NULL, 'plugin_locale_lbl_id', 'backend', 'Label / ID:', 'plugin', NULL);
SET @id := (SELECT LAST_INSERT_ID());
INSERT IGNORE INTO `multi_lang` VALUES (NULL, @id, 'pjField', '::LOCALE::', 'title', 'ID:', 'plugin');

INSERT IGNORE INTO `fields` VALUES (NULL, 'plugin_locale_lbl_show_id', 'backend', 'Label / Show ID in all titles to easily locate them', 'plugin', NULL);
SET @id := (SELECT LAST_INSERT_ID());
INSERT IGNORE INTO `multi_lang` VALUES (NULL, @id, 'pjField', '::LOCALE::', 'title', 'Show IDs', 'plugin');

INSERT IGNORE INTO `fields` VALUES (NULL, 'plugin_locale_separator', 'backend', 'Locale plugin / Delimiter', 'plugin', '2014-07-16 14:02:18');
SET @id := (SELECT LAST_INSERT_ID());
INSERT IGNORE INTO `multi_lang` VALUES (NULL, @id, 'pjField', '::LOCALE::', 'title', 'Delimiter', 'plugin');

INSERT IGNORE INTO `fields` VALUES (NULL, 'plugin_locale_separators_ARRAY_comma', 'arrays', 'Locale plugin / Delimiter: comma', 'plugin', '2014-07-16 14:02:36');
SET @id := (SELECT LAST_INSERT_ID());
INSERT IGNORE INTO `multi_lang` VALUES (NULL, @id, 'pjField', '::LOCALE::', 'title', 'Comma', 'plugin');

INSERT IGNORE INTO `fields` VALUES (NULL, 'plugin_locale_separators_ARRAY_semicolon', 'arrays', 'Locale plugin / Delimiter: semicolon', 'plugin', '2014-07-16 14:02:52');
SET @id := (SELECT LAST_INSERT_ID());
INSERT IGNORE INTO `multi_lang` VALUES (NULL, @id, 'pjField', '::LOCALE::', 'title', 'Semicolon', 'plugin');

INSERT IGNORE INTO `fields` VALUES (NULL, 'plugin_locale_separators_ARRAY_tab', 'arrays', 'Locale plugin / Delimiter: tab', 'plugin', '2014-07-16 14:03:09');
SET @id := (SELECT LAST_INSERT_ID());
INSERT IGNORE INTO `multi_lang` VALUES (NULL, @id, 'pjField', '::LOCALE::', 'title', 'Tab', 'plugin');

INSERT IGNORE INTO `fields` VALUES (NULL, 'error_bodies_ARRAY_PAL20', 'arrays', 'error_bodies_ARRAY_PAL20', 'plugin', '2014-07-21 07:54:40');
SET @id := (SELECT LAST_INSERT_ID());
INSERT IGNORE INTO `multi_lang` VALUES (NULL, @id, 'pjField', '::LOCALE::', 'title', 'The following languages have been found. Select those you want to import.', 'plugin');

INSERT IGNORE INTO `fields` VALUES (NULL, 'error_titles_ARRAY_PAL20', 'arrays', 'error_titles_ARRAY_PAL20', 'plugin', '2014-07-21 07:55:25');
SET @id := (SELECT LAST_INSERT_ID());
INSERT IGNORE INTO `multi_lang` VALUES (NULL, @id, 'pjField', '::LOCALE::', 'title', 'Import confirmation', 'plugin');

INSERT IGNORE INTO `fields` VALUES (NULL, 'error_titles_ARRAY_PAL11', 'arrays', 'error_titles_ARRAY_PAL11', 'plugin', '2014-07-21 07:58:06');
SET @id := (SELECT LAST_INSERT_ID());
INSERT IGNORE INTO `multi_lang` VALUES (NULL, @id, 'pjField', '::LOCALE::', 'title', 'Import failed', 'plugin');

INSERT IGNORE INTO `fields` VALUES (NULL, 'error_bodies_ARRAY_PAL11', 'arrays', 'error_bodies_ARRAY_PAL11', 'plugin', '2014-07-21 07:58:37');
SET @id := (SELECT LAST_INSERT_ID());
INSERT IGNORE INTO `multi_lang` VALUES (NULL, @id, 'pjField', '::LOCALE::', 'title', 'Missing, empty or invalid parameters.', 'plugin');

INSERT IGNORE INTO `fields` VALUES (NULL, 'error_titles_ARRAY_PAL12', 'arrays', 'error_titles_ARRAY_PAL12', 'plugin', '2014-07-21 07:59:00');
SET @id := (SELECT LAST_INSERT_ID());
INSERT IGNORE INTO `multi_lang` VALUES (NULL, @id, 'pjField', '::LOCALE::', 'title', 'Import failed', 'plugin');

INSERT IGNORE INTO `fields` VALUES (NULL, 'error_bodies_ARRAY_PAL12', 'arrays', 'error_bodies_ARRAY_PAL12', 'plugin', '2014-07-21 07:59:46');
SET @id := (SELECT LAST_INSERT_ID());
INSERT IGNORE INTO `multi_lang` VALUES (NULL, @id, 'pjField', '::LOCALE::', 'title', 'File have not been uploaded.', 'plugin');

INSERT IGNORE INTO `fields` VALUES (NULL, 'error_titles_ARRAY_PAL13', 'arrays', 'error_titles_ARRAY_PAL13', 'plugin', '2014-07-21 08:00:05');
SET @id := (SELECT LAST_INSERT_ID());
INSERT IGNORE INTO `multi_lang` VALUES (NULL, @id, 'pjField', '::LOCALE::', 'title', 'Import failed', 'plugin');

INSERT IGNORE INTO `fields` VALUES (NULL, 'error_bodies_ARRAY_PAL13', 'arrays', 'error_bodies_ARRAY_PAL13', 'plugin', '2014-07-21 08:01:02');
SET @id := (SELECT LAST_INSERT_ID());
INSERT IGNORE INTO `multi_lang` VALUES (NULL, @id, 'pjField', '::LOCALE::', 'title', 'Uploaded file cannot open for reading.', 'plugin');

INSERT IGNORE INTO `fields` VALUES (NULL, 'error_titles_ARRAY_PAL14', 'arrays', 'error_titles_ARRAY_PAL14', 'plugin', '2014-07-21 08:01:15');
SET @id := (SELECT LAST_INSERT_ID());
INSERT IGNORE INTO `multi_lang` VALUES (NULL, @id, 'pjField', '::LOCALE::', 'title', 'Import failed', 'plugin');

INSERT IGNORE INTO `fields` VALUES (NULL, 'error_bodies_ARRAY_PAL14', 'arrays', 'error_bodies_ARRAY_PAL14', 'plugin', '2014-07-21 08:01:37');
SET @id := (SELECT LAST_INSERT_ID());
INSERT IGNORE INTO `multi_lang` VALUES (NULL, @id, 'pjField', '::LOCALE::', 'title', 'New line(s) have been found.', 'plugin');

INSERT IGNORE INTO `fields` VALUES (NULL, 'error_titles_ARRAY_PAL15', 'arrays', 'error_titles_ARRAY_PAL15', 'plugin', '2014-07-21 08:01:51');
SET @id := (SELECT LAST_INSERT_ID());
INSERT IGNORE INTO `multi_lang` VALUES (NULL, @id, 'pjField', '::LOCALE::', 'title', 'Import failed', 'plugin');

INSERT IGNORE INTO `fields` VALUES (NULL, 'error_bodies_ARRAY_PAL15', 'arrays', 'error_bodies_ARRAY_PAL15', 'plugin', '2014-07-21 08:04:05');
SET @id := (SELECT LAST_INSERT_ID());
INSERT IGNORE INTO `multi_lang` VALUES (NULL, @id, 'pjField', '::LOCALE::', 'title', 'Uploaded file doesn''t contain the necessary columns.', 'plugin');

INSERT IGNORE INTO `fields` VALUES (NULL, 'error_titles_ARRAY_PAL16', 'arrays', 'error_titles_ARRAY_PAL16', 'plugin', '2014-07-21 08:04:13');
SET @id := (SELECT LAST_INSERT_ID());
INSERT IGNORE INTO `multi_lang` VALUES (NULL, @id, 'pjField', '::LOCALE::', 'title', 'Import failed', 'plugin');

INSERT IGNORE INTO `fields` VALUES (NULL, 'error_bodies_ARRAY_PAL16', 'arrays', 'error_bodies_ARRAY_PAL16', 'plugin', '2014-07-21 08:05:29');
SET @id := (SELECT LAST_INSERT_ID());
INSERT IGNORE INTO `multi_lang` VALUES (NULL, @id, 'pjField', '::LOCALE::', 'title', 'Number of columns are not equal on every row.', 'plugin');

INSERT IGNORE INTO `fields` VALUES (NULL, 'error_titles_ARRAY_PAL17', 'arrays', 'error_titles_ARRAY_PAL17', 'plugin', '2014-07-21 08:06:10');
SET @id := (SELECT LAST_INSERT_ID());
INSERT IGNORE INTO `multi_lang` VALUES (NULL, @id, 'pjField', '::LOCALE::', 'title', 'Import failed', 'plugin');

INSERT IGNORE INTO `fields` VALUES (NULL, 'error_bodies_ARRAY_PAL17', 'arrays', 'error_bodies_ARRAY_PAL17', 'plugin', '2014-07-21 08:06:27');
SET @id := (SELECT LAST_INSERT_ID());
INSERT IGNORE INTO `multi_lang` VALUES (NULL, @id, 'pjField', '::LOCALE::', 'title', 'Invalid data found.', 'plugin');

INSERT IGNORE INTO `fields` VALUES (NULL, 'error_titles_ARRAY_PAL18', 'arrays', 'error_titles_ARRAY_PAL18', 'plugin', '2014-07-21 08:26:34');
SET @id := (SELECT LAST_INSERT_ID());
INSERT IGNORE INTO `multi_lang` VALUES (NULL, @id, 'pjField', '::LOCALE::', 'title', 'Import failed', 'plugin');

INSERT IGNORE INTO `fields` VALUES (NULL, 'error_bodies_ARRAY_PAL18', 'arrays', 'error_bodies_ARRAY_PAL18', 'plugin', '2014-07-21 08:27:01');
SET @id := (SELECT LAST_INSERT_ID());
INSERT IGNORE INTO `multi_lang` VALUES (NULL, @id, 'pjField', '::LOCALE::', 'title', 'Missing columns.', 'plugin');

INSERT IGNORE INTO `fields` VALUES (NULL, 'error_titles_ARRAY_PAL19', 'arrays', 'error_titles_ARRAY_PAL19', 'plugin', '2014-07-21 08:27:15');
SET @id := (SELECT LAST_INSERT_ID());
INSERT IGNORE INTO `multi_lang` VALUES (NULL, @id, 'pjField', '::LOCALE::', 'title', 'Import failed', 'plugin');

INSERT IGNORE INTO `fields` VALUES (NULL, 'error_bodies_ARRAY_PAL19', 'arrays', 'error_bodies_ARRAY_PAL19', 'plugin', '2014-07-21 08:27:27');
SET @id := (SELECT LAST_INSERT_ID());
INSERT IGNORE INTO `multi_lang` VALUES (NULL, @id, 'pjField', '::LOCALE::', 'title', 'Invalid data found.', 'plugin');

COMMIT;