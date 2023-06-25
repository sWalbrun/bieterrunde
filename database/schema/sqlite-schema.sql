PRAGMA synchronous = OFF;
PRAGMA journal_mode = MEMORY;
BEGIN TRANSACTION;
CREATE TABLE `bidderRound` (
  `id` integer  NOT NULL PRIMARY KEY AUTOINCREMENT
,  `createdAt` timestamp NULL DEFAULT NULL
,  `updatedAt` timestamp NULL DEFAULT NULL
,  `startOfSubmission` datetime DEFAULT NULL
,  `endOfSubmission` datetime DEFAULT NULL
,  `note` text COLLATE BINARY
,  `tenant_id` varchar(125) DEFAULT NULL
);
CREATE TABLE `domains` (
  `id` integer  NOT NULL PRIMARY KEY AUTOINCREMENT
,  `domain` varchar(50) NOT NULL
,  `tenant_id` varchar(125) NOT NULL
,  `created_at` timestamp NULL DEFAULT NULL
,  `updated_at` timestamp NULL DEFAULT NULL
,  UNIQUE (`domain`)
,  CONSTRAINT `domains_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenant` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
);
CREATE TABLE `failed_jobs` (
  `id` integer  NOT NULL PRIMARY KEY AUTOINCREMENT
,  `uuid` varchar(125) NOT NULL
,  `connection` text NOT NULL
,  `queue` text NOT NULL
,  `payload` longtext NOT NULL
,  `exception` longtext NOT NULL
,  `failed_at` timestamp NOT NULL DEFAULT current_timestamp
,  UNIQUE (`uuid`)
);
CREATE TABLE `jobs` (
  `id` integer  NOT NULL PRIMARY KEY AUTOINCREMENT
,  `queue` varchar(125) NOT NULL
,  `payload` longtext NOT NULL
,  `attempts` integer  NOT NULL
,  `reserved_at` integer  DEFAULT NULL
,  `available_at` integer  NOT NULL
,  `created_at` integer  NOT NULL
);
CREATE TABLE `migrations` (
  `id` integer  NOT NULL PRIMARY KEY AUTOINCREMENT
,  `migration` varchar(125) NOT NULL
,  `batch` integer NOT NULL
);
CREATE TABLE `model_has_permissions` (
  `permission_id` integer  NOT NULL
,  `model_type` varchar(125) NOT NULL
,  `model_id` integer  NOT NULL
,  PRIMARY KEY (`permission_id`,`model_id`,`model_type`)
,  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permission` (`id`) ON DELETE CASCADE
);
CREATE TABLE `model_has_roles` (
  `role_id` integer  NOT NULL
,  `model_type` varchar(125) NOT NULL
,  `model_id` integer  NOT NULL
,  PRIMARY KEY (`role_id`,`model_id`,`model_type`)
,  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`) ON DELETE CASCADE
);
CREATE TABLE `notifications` (
  `id` char(36) NOT NULL
,  `type` varchar(125) NOT NULL
,  `notifiable_type` varchar(125) NOT NULL
,  `notifiable_id` integer  NOT NULL
,  `data` text NOT NULL
,  `read_at` timestamp NULL DEFAULT NULL
,  `created_at` timestamp NULL DEFAULT NULL
,  `updated_at` timestamp NULL DEFAULT NULL
,  PRIMARY KEY (`id`)
);
CREATE TABLE `offer` (
  `id` integer  NOT NULL PRIMARY KEY AUTOINCREMENT
,  `createdAt` timestamp NULL DEFAULT NULL
,  `updatedAt` timestamp NULL DEFAULT NULL
,  `amount` double(8,2) DEFAULT NULL
,  `round` integer DEFAULT NULL
,  `fkUser` integer  DEFAULT NULL
,  `fkTopic` integer  DEFAULT NULL
,  CONSTRAINT `offer_fktopic_foreign` FOREIGN KEY (`fkTopic`) REFERENCES `topic` (`id`)
,  CONSTRAINT `offer_fkuser_foreign` FOREIGN KEY (`fkUser`) REFERENCES `user` (`id`) ON DELETE CASCADE
);
CREATE TABLE `password_resets` (
  `email` varchar(125) NOT NULL
,  `token` varchar(125) NOT NULL
,  `created_at` timestamp NULL DEFAULT NULL
);
CREATE TABLE `permission` (
  `id` integer  NOT NULL PRIMARY KEY AUTOINCREMENT
,  `name` varchar(125) NOT NULL
,  `guard_name` varchar(125) NOT NULL
,  `created_at` timestamp NULL DEFAULT NULL
,  `updated_at` timestamp NULL DEFAULT NULL
,  UNIQUE (`name`,`guard_name`)
);
CREATE TABLE `personal_access_tokens` (
  `id` integer  NOT NULL PRIMARY KEY AUTOINCREMENT
,  `tokenable_type` varchar(125) NOT NULL
,  `tokenable_id` integer  NOT NULL
,  `name` varchar(125) NOT NULL
,  `token` varchar(64) NOT NULL
,  `abilities` text COLLATE BINARY
,  `last_used_at` timestamp NULL DEFAULT NULL
,  `created_at` timestamp NULL DEFAULT NULL
,  `updated_at` timestamp NULL DEFAULT NULL
,  UNIQUE (`token`)
);
CREATE TABLE `role` (
  `id` integer  NOT NULL PRIMARY KEY AUTOINCREMENT
,  `name` varchar(125) NOT NULL
,  `guard_name` varchar(125) NOT NULL
,  `created_at` timestamp NULL DEFAULT NULL
,  `updated_at` timestamp NULL DEFAULT NULL
,  UNIQUE (`name`,`guard_name`)
);
CREATE TABLE `role_has_permissions` (
  `permission_id` integer  NOT NULL
,  `role_id` integer  NOT NULL
,  PRIMARY KEY (`permission_id`,`role_id`)
,  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permission` (`id`) ON DELETE CASCADE
,  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`) ON DELETE CASCADE
);
CREATE TABLE `sessions` (
  `id` varchar(125) NOT NULL
,  `user_id` integer  DEFAULT NULL
,  `ip_address` varchar(45) DEFAULT NULL
,  `user_agent` text COLLATE BINARY
,  `payload` text NOT NULL
,  `last_activity` integer NOT NULL
,  PRIMARY KEY (`id`)
);
CREATE TABLE `share` (
  `id` integer  NOT NULL PRIMARY KEY AUTOINCREMENT
,  `createdAt` timestamp NULL DEFAULT NULL
,  `updatedAt` timestamp NULL DEFAULT NULL
,  `value` varchar(125) DEFAULT NULL
,  `fkUser` integer  NOT NULL
,  `fkTopic` integer  NOT NULL
,  CONSTRAINT `share_fktopic_foreign` FOREIGN KEY (`fkTopic`) REFERENCES `topic` (`id`)
,  CONSTRAINT `share_fkuser_foreign` FOREIGN KEY (`fkUser`) REFERENCES `user` (`id`)
);
CREATE TABLE `tenant` (
  `id` varchar(125) NOT NULL
,  `created_at` timestamp NULL DEFAULT NULL
,  `updated_at` timestamp NULL DEFAULT NULL
,  `data` json DEFAULT NULL
,  PRIMARY KEY (`id`)
);
CREATE TABLE `topic` (
  `id` integer  NOT NULL PRIMARY KEY AUTOINCREMENT
,  `createdAt` timestamp NULL DEFAULT NULL
,  `updatedAt` timestamp NULL DEFAULT NULL
,  `name` varchar(125) DEFAULT NULL
,  `targetAmount` double(8,2) DEFAULT NULL
,  `rounds` integer DEFAULT NULL
,  `fkBidderRound` integer  NOT NULL
,  CONSTRAINT `topic_fkbidderround_foreign` FOREIGN KEY (`fkBidderRound`) REFERENCES `bidderRound` (`id`)
);
CREATE TABLE `topicReport` (
  `id` integer  NOT NULL PRIMARY KEY AUTOINCREMENT
,  `roundWon` integer  DEFAULT NULL
,  `countParticipants` integer  DEFAULT NULL
,  `countRounds` integer  DEFAULT NULL
,  `sumAmount` double(8,2) DEFAULT NULL
,  `fkBidderRound` integer  DEFAULT NULL
,  `createdAt` timestamp NULL DEFAULT NULL
,  `updatedAt` timestamp NULL DEFAULT NULL
,  `fkTopic` integer  NOT NULL
,  `name` varchar(125) DEFAULT NULL
,  CONSTRAINT `topicreport_fktopic_foreign` FOREIGN KEY (`fkTopic`) REFERENCES `topic` (`id`)
);
CREATE TABLE `user` (
  `id` integer  NOT NULL PRIMARY KEY AUTOINCREMENT
,  `name` varchar(125) NOT NULL
,  `email` varchar(125) NOT NULL
,  `email_verified_at` timestamp NULL DEFAULT NULL
,  `password` varchar(125) NOT NULL
,  `two_factor_secret` text COLLATE BINARY
,  `two_factor_recovery_codes` text COLLATE BINARY
,  `remember_token` varchar(100) DEFAULT NULL
,  `contributionGroup` text  DEFAULT NULL
,  `paymentInterval` text  DEFAULT NULL
,  `joinDate` timestamp NULL DEFAULT NULL
,  `exitDate` timestamp NULL DEFAULT NULL
,  `current_team_id` integer  DEFAULT NULL
,  `profile_photo_path` varchar(2048) DEFAULT NULL
,  `createdAt` timestamp NULL DEFAULT NULL
,  `updatedAt` timestamp NULL DEFAULT NULL
,  `tenant_id` varchar(125) DEFAULT NULL
);
CREATE TABLE `user_topic` (
  `id` integer  NOT NULL PRIMARY KEY AUTOINCREMENT
,  `createdAt` timestamp NULL DEFAULT NULL
,  `updatedAt` timestamp NULL DEFAULT NULL
,  `fkUser` integer  NOT NULL
,  `fkTopic` integer  NOT NULL
,  CONSTRAINT `user_topic_fktopic_foreign` FOREIGN KEY (`fkTopic`) REFERENCES `topic` (`id`)
,  CONSTRAINT `user_topic_fkuser_foreign` FOREIGN KEY (`fkUser`) REFERENCES `user` (`id`)
);
INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1);
INSERT INTO `migrations` VALUES (2,'2014_10_12_100000_create_password_resets_table',1);
INSERT INTO `migrations` VALUES (3,'2014_10_12_200000_add_two_factor_columns_to_users_table',1);
INSERT INTO `migrations` VALUES (4,'2019_08_19_000000_create_failed_jobs_table',1);
INSERT INTO `migrations` VALUES (5,'2019_12_14_000001_create_personal_access_tokens_table',1);
INSERT INTO `migrations` VALUES (6,'2021_05_27_173757_vegetables',1);
INSERT INTO `migrations` VALUES (7,'2021_12_10_174624_create_bidder_round',1);
INSERT INTO `migrations` VALUES (8,'2021_12_10_175210_create_offer',1);
INSERT INTO `migrations` VALUES (9,'2021_12_10_192501_create_sessions_table',1);
INSERT INTO `migrations` VALUES (10,'2022_01_04_174227_create_bidder_round_report',1);
INSERT INTO `migrations` VALUES (11,'2022_01_06_150812_create_notifications_table',1);
INSERT INTO `migrations` VALUES (12,'2022_01_06_151520_create_jobs_table',1);
INSERT INTO `migrations` VALUES (13,'2022_04_15_000010_create_tenant_table',1);
INSERT INTO `migrations` VALUES (14,'2022_04_15_000020_create_domains_table',1);
INSERT INTO `migrations` VALUES (15,'2022_11_16_193804_relate_user_and_bidder_round',1);
INSERT INTO `migrations` VALUES (16,'2023_01_04_152317_cascade_delete_bidder_round',1);
INSERT INTO `migrations` VALUES (17,'2023_01_15_110649_create_permission_tables',1);
INSERT INTO `migrations` VALUES (18,'2023_02_12_115659_cascade_delete_user',1);
INSERT INTO `migrations` VALUES (19,'2023_06_08_153046_remove_unused_models',1);
INSERT INTO `migrations` VALUES (20,'2023_06_16_143322_create_topics',1);
CREATE INDEX "idx_user_topic_user_topic_fkuser_foreign" ON "user_topic" (`fkUser`);
CREATE INDEX "idx_user_topic_user_topic_fktopic_foreign" ON "user_topic" (`fkTopic`);
CREATE INDEX "idx_topicReport_bidderroundreport_fkbidderround_foreign" ON "topicReport" (`fkBidderRound`);
CREATE INDEX "idx_topicReport_topicreport_fktopic_foreign" ON "topicReport" (`fkTopic`);
CREATE INDEX "idx_sessions_sessions_user_id_index" ON "sessions" (`user_id`);
CREATE INDEX "idx_sessions_sessions_last_activity_index" ON "sessions" (`last_activity`);
CREATE INDEX "idx_model_has_permissions_model_has_permissions_model_id_model_type_index" ON "model_has_permissions" (`model_id`,`model_type`);
CREATE INDEX "idx_jobs_jobs_queue_index" ON "jobs" (`queue`);
CREATE INDEX "idx_topic_topic_fkbidderround_foreign" ON "topic" (`fkBidderRound`);
CREATE INDEX "idx_notifications_notifications_notifiable_type_notifiable_id_index" ON "notifications" (`notifiable_type`,`notifiable_id`);
CREATE INDEX "idx_model_has_roles_model_has_roles_model_id_model_type_index" ON "model_has_roles" (`model_id`,`model_type`);
CREATE INDEX "idx_offer_offer_fkuser_foreign" ON "offer" (`fkUser`);
CREATE INDEX "idx_offer_offer_fktopic_foreign" ON "offer" (`fkTopic`);
CREATE INDEX "idx_share_share_fkuser_foreign" ON "share" (`fkUser`);
CREATE INDEX "idx_share_share_fktopic_foreign" ON "share" (`fkTopic`);
CREATE INDEX "idx_personal_access_tokens_personal_access_tokens_tokenable_type_tokenable_id_index" ON "personal_access_tokens" (`tokenable_type`,`tokenable_id`);
CREATE INDEX "idx_domains_domains_tenant_id_foreign" ON "domains" (`tenant_id`);
CREATE INDEX "idx_role_has_permissions_role_has_permissions_role_id_foreign" ON "role_has_permissions" (`role_id`);
CREATE INDEX "idx_password_resets_password_resets_email_index" ON "password_resets" (`email`);
END TRANSACTION;
