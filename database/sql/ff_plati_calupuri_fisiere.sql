-- SQL script to mirror the 2025_10_05_182213_create_ff_plati_calupuri_fisiere_table migration

CREATE TABLE `ff_plati_calupuri_fisiere` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `plata_calup_id` bigint unsigned NOT NULL,
  `cale` varchar(255) NOT NULL,
  `nume_original` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ff_plati_calupuri_fisiere_plata_calup_id_index` (`plata_calup_id`),
  CONSTRAINT `ff_plati_calupuri_fisiere_plata_calup_id_foreign` FOREIGN KEY (`plata_calup_id`) REFERENCES `ff_plati_calupuri` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional data migration from the legacy single-file column.
INSERT INTO `ff_plati_calupuri_fisiere` (`plata_calup_id`, `cale`, `nume_original`, `created_at`, `updated_at`)
SELECT `id`, `fisier_pdf`, SUBSTRING_INDEX(`fisier_pdf`, '/', -1), NOW(), NOW()
FROM `ff_plati_calupuri`
WHERE `fisier_pdf` IS NOT NULL AND `fisier_pdf` <> '';

-- Optional clean-up of the legacy column once data has been migrated.
ALTER TABLE `ff_plati_calupuri`
  DROP COLUMN `fisier_pdf`;
