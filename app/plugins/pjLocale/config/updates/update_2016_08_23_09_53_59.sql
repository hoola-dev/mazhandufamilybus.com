
START TRANSACTION;

INSERT IGNORE INTO `fields` VALUES (NULL, 'plugin_locale_reset_search', 'backend', 'Locale plugin / Reset search', 'plugin', '2016-08-23 09:48:29');

SET @id := (SELECT LAST_INSERT_ID());

INSERT IGNORE INTO `multi_lang` VALUES (NULL, @id, 'pjField', '::LOCALE::', 'title', 'Reset search', 'plugin');

COMMIT;