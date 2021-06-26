CREATE TABLE `rik_ats_log` (
  `internal` INTEGER(11) NOT NULL,
  `caller_id` VARCHAR(25) COLLATE utf8_general_ci NOT NULL,
  `called_did` VARCHAR(50) COLLATE utf8_general_ci DEFAULT NULL,
  `destination` VARCHAR(25) COLLATE utf8_general_ci NOT NULL,
  `event` VARCHAR(50) COLLATE utf8_general_ci NOT NULL,
  `call_start` DATETIME NOT NULL,
  `pbx_call_id` VARCHAR(75) COLLATE utf8_general_ci NOT NULL,
  `duration` INTEGER(11) DEFAULT NULL,
  `disposition` VARCHAR(50) COLLATE utf8_general_ci DEFAULT NULL,
  `is_recorded` INTEGER(11) NOT NULL DEFAULT 0,
  `call_id_with_rec` VARCHAR(75) COLLATE utf8_general_ci DEFAULT NULL,
  `external_num` VARCHAR(50) COLLATE utf8_general_ci DEFAULT NULL,
  `internal_num` VARCHAR(50) COLLATE utf8_general_ci DEFAULT NULL,
  `status_code` VARCHAR(20) COLLATE utf8_general_ci DEFAULT NULL
) ENGINE=InnoDB
ROW_FORMAT=DYNAMIC CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'
;

CREATE TABLE `rik_ats_redirection` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `ats_id` INTEGER(11) DEFAULT NULL,
  `redirect` VARCHAR(20) COLLATE utf8_general_ci DEFAULT NULL COMMENT 'номер на который переназначается вызов',
  PRIMARY KEY USING BTREE (`id`)
) ENGINE=InnoDB
AUTO_INCREMENT=1 ROW_FORMAT=DYNAMIC CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'
;

CREATE TABLE `rik_ats_state` (
  `internal` INTEGER(11) NOT NULL,
  `caller_id` VARCHAR(25) COLLATE utf8_general_ci NOT NULL,
  `called_did` VARCHAR(50) COLLATE utf8_general_ci DEFAULT NULL,
  `destination` VARCHAR(25) COLLATE utf8_general_ci NOT NULL,
  `event` VARCHAR(50) COLLATE utf8_general_ci NOT NULL,
  `call_start` DATETIME NOT NULL,
  `pbx_call_id` VARCHAR(75) COLLATE utf8_general_ci NOT NULL,
  `duration` INTEGER(11) DEFAULT NULL,
  `disposition` VARCHAR(50) COLLATE utf8_general_ci DEFAULT NULL,
  `is_recorded` INTEGER(11) NOT NULL DEFAULT 0,
  `call_id_with_rec` VARCHAR(75) COLLATE utf8_general_ci DEFAULT NULL,
  `external_num` VARCHAR(50) COLLATE utf8_general_ci DEFAULT NULL,
  `internal_num` VARCHAR(50) COLLATE utf8_general_ci DEFAULT NULL,
  `status_code` VARCHAR(20) COLLATE utf8_general_ci DEFAULT NULL
) ENGINE=InnoDB
ROW_FORMAT=DYNAMIC CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'
;