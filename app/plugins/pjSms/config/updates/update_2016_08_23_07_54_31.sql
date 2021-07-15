
START TRANSACTION;

INSERT INTO `fields` VALUES (NULL, 'plugin_sms_reset_search', 'backend', 'SMS plugin / Reset search', 'plugin', '2016-08-23 07:22:36');

SET @id := (SELECT LAST_INSERT_ID());

INSERT INTO `multi_lang` VALUES (NULL, @id, 'pjField', '::LOCALE::', 'title', 'Reset search', 'plugin');

COMMIT;