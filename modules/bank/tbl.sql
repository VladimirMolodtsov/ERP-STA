CREATE TABLE `rik_bank_extract` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `recordDate` DATETIME DEFAULT NULL COMMENT 'дата-время проводки',
  `debetRS` VARCHAR(50) COLLATE utf8_general_ci DEFAULT NULL,
  `debetINN` VARCHAR(20) COLLATE utf8_general_ci DEFAULT NULL,
  `debetOrgTitle` VARCHAR(250) COLLATE utf8_general_ci DEFAULT NULL,
  `creditRs` VARCHAR(50) COLLATE utf8_general_ci DEFAULT NULL,
  `creditINN` VARCHAR(20) COLLATE utf8_general_ci DEFAULT NULL,
  `creditOrgTitle` VARCHAR(250) COLLATE utf8_general_ci DEFAULT NULL,
  `debetSum` DOUBLE DEFAULT 0,
  `creditSum` DOUBLE DEFAULT 0,
  `docNum` VARCHAR(50) COLLATE utf8_general_ci DEFAULT NULL,
  `contrAgentBank` VARCHAR(250) COLLATE utf8_general_ci DEFAULT NULL,
  `description` TEXT COLLATE utf8_general_ci,
  `VO` VARCHAR(20) COLLATE utf8_general_ci DEFAULT NULL,
  `refOplata` BIGINT(20) DEFAULT 0,
  `refSupplierOplata` BIGINT(20) DEFAULT 0,
  `extractType` TINYINT(4) DEFAULT 0 COMMENT '0 - не установлено\r\n1-оплата от клиента (поступление денег)\r\n2-оплата поставщику (расход денег)',
  `orgRef` BIGINT(20) DEFAULT NULL,
  `reasonDocType` VARCHAR(50) COLLATE utf8_general_ci DEFAULT NULL COMMENT 'документ - основание оплаты, например по счету',
  `reasonDocNum` VARCHAR(50) COLLATE utf8_general_ci DEFAULT NULL COMMENT 'номер документа, являющегося основанием оплаты',
  `reasonDocDate` DATE DEFAULT NULL COMMENT 'дата документа, являющегося основанием оплаты',
  `reasonText` VARCHAR(150) COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY USING BTREE (`id`),
  UNIQUE KEY `id` USING BTREE (`id`)
) ENGINE=InnoDB
AUTO_INCREMENT=1 ROW_FORMAT=DYNAMIC CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'
;

CREATE TABLE `rik_bank_content` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `refBankHeader` BIGINT(20) DEFAULT NULL,
  `recordDate` DATETIME DEFAULT NULL COMMENT 'дата-время проводки',
  `debetRS` VARCHAR(50) COLLATE utf8_general_ci DEFAULT NULL,
  `debetINN` VARCHAR(20) COLLATE utf8_general_ci DEFAULT NULL,
  `debetOrgTitle` VARCHAR(250) COLLATE utf8_general_ci DEFAULT NULL,
  `creditRs` VARCHAR(50) COLLATE utf8_general_ci DEFAULT NULL,
  `creditINN` VARCHAR(20) COLLATE utf8_general_ci DEFAULT NULL,
  `creditOrgTitle` VARCHAR(250) COLLATE utf8_general_ci DEFAULT NULL,
  `debetSum` DOUBLE DEFAULT 0,
  `creditSum` DOUBLE DEFAULT 0,
  `docNum` VARCHAR(50) COLLATE utf8_general_ci DEFAULT NULL,
  `contrAgentBank` VARCHAR(250) COLLATE utf8_general_ci DEFAULT NULL,
  `description` TEXT COLLATE utf8_unicode_ci,
  `VO` VARCHAR(20) COLLATE utf8_general_ci DEFAULT NULL,
  `reasonDocType` VARCHAR(50) COLLATE utf8_general_ci DEFAULT NULL COMMENT 'документ - основание оплаты, например по счету',
  `reasonDocNum` VARCHAR(50) COLLATE utf8_general_ci DEFAULT NULL COMMENT 'номер документа, являющегося основанием оплаты',
  `reasonDocDate` DATE DEFAULT NULL COMMENT 'дата документа, являющегося основанием оплаты',
  `reasonText` VARCHAR(150) COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY USING BTREE (`id`),
  UNIQUE KEY `id` USING BTREE (`id`)
) ENGINE=InnoDB
AUTO_INCREMENT=1 ROW_FORMAT=DYNAMIC CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'
;

CREATE TABLE `rik_bank_header` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `creationDate` DATETIME DEFAULT NULL COMMENT 'дата-время создания файла (дата-время выписки)',
  `uploadTime` DATETIME DEFAULT NULL COMMENT 'дата-время загрузки в систему',
  `refManager` BIGINT(20) DEFAULT 0 COMMENT 'кто загрузил',
  `debetRemain` DOUBLE DEFAULT 0,
  `creditRemain` DOUBLE DEFAULT 0,
  PRIMARY KEY USING BTREE (`id`),
  UNIQUE KEY `id` USING BTREE (`id`)
) ENGINE=InnoDB
AUTO_INCREMENT=1 ROW_FORMAT=DYNAMIC CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci'
;
