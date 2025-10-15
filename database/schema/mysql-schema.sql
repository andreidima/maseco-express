/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `camioane`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `camioane` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tip_camion` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numar_inmatriculare` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numar_remorca` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pret_km_goi` decimal(8,2) DEFAULT NULL,
  `pret_km_plini` decimal(8,2) DEFAULT NULL,
  `nume_sofer` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefon_sofer` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `skype_sofer` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `firma_id` int unsigned DEFAULT NULL,
  `observatii` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `camioane_istoric`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `camioane_istoric` (
  `id_pk` int unsigned NOT NULL AUTO_INCREMENT,
  `id` int unsigned NOT NULL,
  `tip_camion` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numar_inmatriculare` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numar_remorca` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pret_km_goi` decimal(8,2) DEFAULT NULL,
  `pret_km_plini` decimal(8,2) DEFAULT NULL,
  `nume_sofer` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefon_sofer` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `skype_sofer` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `firma_id` int unsigned DEFAULT NULL,
  `observatii` text COLLATE utf8mb4_unicode_ci,
  `operare_user_id` int unsigned DEFAULT NULL,
  `operare_descriere` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `operare_data` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comenzi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comenzi` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `data_creare` date DEFAULT NULL,
  `interval_notificari` time DEFAULT NULL,
  `stare` tinyint unsigned DEFAULT '1' COMMENT '1-inchisa / 2-deschisa / 3-anulata',
  `transportator_contract` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transportator_limba_id` tinyint unsigned DEFAULT NULL,
  `transportator_valoare_contract` decimal(8,2) DEFAULT NULL,
  `transportator_moneda_id` tinyint unsigned DEFAULT NULL,
  `transportator_zile_scadente` smallint DEFAULT NULL,
  `transportator_termen_plata_id` tinyint unsigned DEFAULT NULL,
  `transportator_transportator_id` int unsigned DEFAULT NULL,
  `transportator_procent_tva_id` tinyint unsigned DEFAULT NULL,
  `transportator_metoda_de_plata_id` tinyint unsigned DEFAULT NULL,
  `transportator_format_documente` tinyint unsigned DEFAULT NULL COMMENT '1-per post / 2-digital',
  `transportator_blocare_incarcare_documente` tinyint unsigned DEFAULT NULL,
  `transportator_status_documente` tinyint unsigned DEFAULT NULL COMMENT '0-incomplete / 1-complete',
  `transportator_tarif_pe_km` tinyint unsigned DEFAULT NULL COMMENT '0-nu / 1-da',
  `transportator_pret_km_goi` decimal(8,2) DEFAULT NULL,
  `transportator_pret_km_plini` decimal(8,2) DEFAULT NULL,
  `transportator_km_goi` decimal(8,2) DEFAULT NULL,
  `transportator_km_plini` decimal(8,2) DEFAULT NULL,
  `transportator_valoare_km_goi` decimal(8,2) DEFAULT NULL,
  `transportator_valoare_km_plini` decimal(8,2) DEFAULT NULL,
  `transportator_pret_autostrada` decimal(8,2) DEFAULT NULL,
  `transportator_pret_ferry` decimal(8,2) DEFAULT NULL,
  `client_contract` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `client_limba_id` tinyint unsigned DEFAULT NULL,
  `client_valoare_contract_initiala` decimal(8,2) DEFAULT NULL,
  `client_valoare_contract` decimal(8,2) DEFAULT NULL,
  `client_moneda_id` tinyint unsigned DEFAULT NULL,
  `client_zile_scadente` smallint DEFAULT NULL,
  `client_termen_plata_id` tinyint unsigned DEFAULT NULL,
  `client_client_id` int unsigned DEFAULT NULL,
  `client_procent_tva_id` tinyint unsigned DEFAULT NULL,
  `client_metoda_de_plata_id` tinyint unsigned DEFAULT NULL,
  `client_tarif_pe_km` tinyint unsigned DEFAULT NULL COMMENT '0-nu / 1-da',
  `descriere_marfa` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `camion_id` int unsigned DEFAULT NULL,
  `observatii_interne` text COLLATE utf8mb4_unicode_ci,
  `observatii_externe` text COLLATE utf8mb4_unicode_ci,
  `factura_id` int unsigned DEFAULT NULL,
  `factura_transportator` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_factura_transportator` date DEFAULT NULL,
  `data_scadenta_plata_transportator` date DEFAULT NULL,
  `data_plata_transportator` date DEFAULT NULL,
  `documente_transport_incarcate` tinyint unsigned DEFAULT NULL,
  `factura_transportator_incarcata` tinyint unsigned DEFAULT NULL,
  `debit_note` text COLLATE utf8mb4_unicode_ci,
  `debit_note_suma` decimal(8,2) DEFAULT NULL,
  `debit_note_ore` smallint DEFAULT NULL,
  `debit_note_adresa` text COLLATE utf8mb4_unicode_ci,
  `user_id` int unsigned DEFAULT NULL,
  `operator_user_id` int unsigned DEFAULT NULL,
  `cheie_unica` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comenzi_clienti`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comenzi_clienti` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `comanda_id` int unsigned DEFAULT NULL,
  `client_id` int DEFAULT NULL,
  `ordine_afisare` tinyint unsigned DEFAULT NULL,
  `contract` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `limba_id` tinyint unsigned DEFAULT NULL,
  `valoare_contract_initiala` decimal(8,2) DEFAULT NULL,
  `moneda_id` tinyint unsigned DEFAULT NULL,
  `zile_scadente` smallint DEFAULT NULL,
  `termen_plata_id` tinyint unsigned DEFAULT NULL,
  `procent_tva_id` tinyint unsigned DEFAULT NULL,
  `metoda_de_plata_id` tinyint unsigned DEFAULT NULL,
  `tarif_pe_km` tinyint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comenzi_clienti_istoric`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comenzi_clienti_istoric` (
  `id_pk` int unsigned NOT NULL AUTO_INCREMENT,
  `id` int unsigned DEFAULT NULL,
  `comanda_id` int unsigned DEFAULT NULL,
  `client_id` int DEFAULT NULL,
  `ordine_afisare` tinyint unsigned DEFAULT NULL,
  `contract` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `limba_id` tinyint unsigned DEFAULT NULL,
  `valoare_contract_initiala` decimal(8,2) DEFAULT NULL,
  `moneda_id` tinyint unsigned DEFAULT NULL,
  `zile_scadente` smallint DEFAULT NULL,
  `termen_plata_id` tinyint unsigned DEFAULT NULL,
  `procent_tva_id` tinyint unsigned DEFAULT NULL,
  `metoda_de_plata_id` tinyint unsigned DEFAULT NULL,
  `tarif_pe_km` tinyint unsigned DEFAULT NULL,
  `operare_user_id` int unsigned DEFAULT NULL,
  `operare_descriere` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `operare_data` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comenzi_cron_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comenzi_cron_jobs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `comanda_id` int unsigned DEFAULT NULL,
  `inceput` datetime DEFAULT NULL,
  `sfarsit` datetime DEFAULT NULL,
  `urmatorul_mesaj_incepand_cu` datetime DEFAULT NULL,
  `informare_incepere_comanda` tinyint(1) DEFAULT '0',
  `contract_trimis_pe_email_catre_transportator` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comenzi_fisiere`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comenzi_fisiere` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `comanda_id` int unsigned DEFAULT NULL,
  `categorie` tinyint DEFAULT NULL COMMENT '1uploadedByTransporter\r\n2fisiereInterne',
  `nume` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cale` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `validat` tinyint unsigned DEFAULT NULL,
  `user_id` smallint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comenzi_fisiere_emailuri`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comenzi_fisiere_emailuri` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `comanda_id` int unsigned DEFAULT NULL,
  `tip` tinyint unsigned DEFAULT NULL COMMENT '1-transportatorCatreMasecoTransportDocuments\r\n2-MasecoCatreTransportatorGoodDocuments\r\n3-MasecoCatreTransportatorBadDocuments\r\n4-transportatorCatreMasecoInvoice',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mesaj` varchar(2000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comenzi_fisiere_istoric`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comenzi_fisiere_istoric` (
  `id_pk` int unsigned NOT NULL AUTO_INCREMENT,
  `id` int unsigned NOT NULL,
  `comanda_id` int unsigned DEFAULT NULL,
  `categorie` tinyint DEFAULT NULL COMMENT '1uploadedByTransporter\r\n2fisiereInterne',
  `nume` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cale` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `validat` tinyint unsigned DEFAULT NULL,
  `user_id` smallint unsigned DEFAULT NULL,
  `operare_user_id` int unsigned DEFAULT NULL,
  `operare_descriere` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `operare_data` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comenzi_istoric`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comenzi_istoric` (
  `id_pk` int unsigned NOT NULL AUTO_INCREMENT,
  `id` int unsigned NOT NULL,
  `data_creare` date DEFAULT NULL,
  `interval_notificari` time DEFAULT NULL,
  `stare` tinyint unsigned DEFAULT NULL COMMENT '1-inchisa / 2-deschisa / 3-anulata',
  `transportator_contract` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transportator_limba_id` tinyint unsigned DEFAULT NULL,
  `transportator_valoare_contract` decimal(8,2) DEFAULT NULL,
  `transportator_moneda_id` tinyint unsigned DEFAULT NULL,
  `transportator_zile_scadente` smallint DEFAULT NULL,
  `transportator_termen_plata_id` tinyint unsigned DEFAULT NULL,
  `transportator_transportator_id` int unsigned DEFAULT NULL,
  `transportator_procent_tva_id` tinyint unsigned DEFAULT NULL,
  `transportator_metoda_de_plata_id` tinyint unsigned DEFAULT NULL,
  `transportator_format_documente` tinyint unsigned DEFAULT NULL COMMENT '1-per post / 2-digital',
  `transportator_blocare_incarcare_documente` tinyint unsigned DEFAULT NULL,
  `transportator_status_documente` tinyint unsigned DEFAULT NULL,
  `transportator_tarif_pe_km` tinyint unsigned DEFAULT NULL COMMENT '0-nu / 1-da',
  `transportator_pret_km_goi` decimal(8,2) DEFAULT NULL,
  `transportator_pret_km_plini` decimal(8,2) DEFAULT NULL,
  `transportator_km_goi` decimal(8,2) DEFAULT NULL,
  `transportator_km_plini` decimal(8,2) DEFAULT NULL,
  `transportator_valoare_km_goi` decimal(8,2) DEFAULT NULL,
  `transportator_valoare_km_plini` decimal(8,2) DEFAULT NULL,
  `transportator_pret_autostrada` decimal(8,2) DEFAULT NULL,
  `transportator_pret_ferry` decimal(8,2) DEFAULT NULL,
  `client_contract` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `client_limba_id` tinyint unsigned DEFAULT NULL,
  `client_valoare_contract_initiala` decimal(8,2) DEFAULT NULL,
  `client_valoare_contract` decimal(8,2) DEFAULT NULL,
  `client_moneda_id` tinyint unsigned DEFAULT NULL,
  `client_zile_scadente` smallint DEFAULT NULL,
  `client_termen_plata_id` tinyint unsigned DEFAULT NULL,
  `client_client_id` int unsigned DEFAULT NULL,
  `client_procent_tva_id` tinyint unsigned DEFAULT NULL,
  `client_metoda_de_plata_id` tinyint unsigned DEFAULT NULL,
  `client_tarif_pe_km` tinyint unsigned DEFAULT NULL COMMENT '0-nu / 1-da',
  `descriere_marfa` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `camion_id` int unsigned DEFAULT NULL,
  `observatii_interne` text COLLATE utf8mb4_unicode_ci,
  `observatii_externe` text COLLATE utf8mb4_unicode_ci,
  `factura_id` int unsigned DEFAULT NULL,
  `factura_transportator` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_factura_transportator` date DEFAULT NULL,
  `data_scadenta_plata_transportator` date DEFAULT NULL,
  `data_plata_transportator` date DEFAULT NULL,
  `documente_transport_incarcate` tinyint unsigned DEFAULT NULL,
  `factura_transportator_incarcata` tinyint unsigned DEFAULT NULL,
  `debit_note` text COLLATE utf8mb4_unicode_ci,
  `debit_note_suma` decimal(8,2) DEFAULT NULL,
  `debit_note_ore` smallint DEFAULT NULL,
  `debit_note_adresa` text COLLATE utf8mb4_unicode_ci,
  `user_id` int unsigned DEFAULT NULL,
  `operator_user_id` int unsigned DEFAULT NULL,
  `cheie_unica` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `operare_user_id` int unsigned DEFAULT NULL,
  `operare_descriere` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `operare_data` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comenzi_locuri_operare`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comenzi_locuri_operare` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `comanda_id` int unsigned DEFAULT NULL,
  `loc_operare_id` int unsigned DEFAULT NULL,
  `tip` tinyint unsigned DEFAULT NULL COMMENT '1-incarcare / 2-descarcare',
  `ordine` tinyint unsigned DEFAULT NULL,
  `data_ora` datetime DEFAULT NULL,
  `durata` time DEFAULT '00:00:00',
  `observatii` varchar(2000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referinta` varchar(2000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comenzi_locuri_operare_istoric`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comenzi_locuri_operare_istoric` (
  `id_pk` int unsigned NOT NULL AUTO_INCREMENT,
  `id` int unsigned NOT NULL,
  `comanda_id` int unsigned DEFAULT NULL,
  `loc_operare_id` int unsigned DEFAULT NULL,
  `tip` tinyint unsigned DEFAULT NULL COMMENT '1-incarcare / 2-descarcare',
  `ordine` tinyint unsigned DEFAULT NULL,
  `data_ora` datetime DEFAULT NULL,
  `durata` time DEFAULT '00:00:00',
  `observatii` varchar(2000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referinta` varchar(2000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `operare_user_id` int unsigned DEFAULT NULL,
  `operare_descriere` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `operare_data` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comenzi_statusuri`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comenzi_statusuri` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `comanda_id` int unsigned DEFAULT NULL,
  `mod_transmitere` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(2000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `curs_bnr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `curs_bnr` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `moneda_nume` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valoare` decimal(20,6) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `date_personale`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `date_personale` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nume` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reg_com` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cif` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adresa` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banca_nume` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `swift_code` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `iban_eur` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `iban_eur_banca` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `iban_ron` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `iban_ron_banca` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `capital_social` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `documente_word`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `documente_word` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nume` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nivel_acces` tinyint unsigned DEFAULT NULL COMMENT '1-admin / 2-operator',
  `continut` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `locked_by` int unsigned DEFAULT NULL,
  `locked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `documente_word_chk_1` CHECK (json_valid(`continut`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `documente_word_istoric`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `documente_word_istoric` (
  `id_pk` int unsigned NOT NULL AUTO_INCREMENT,
  `id` int unsigned NOT NULL,
  `nume` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nivel_acces` tinyint unsigned DEFAULT NULL,
  `continut` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `operare_user_id` int unsigned DEFAULT NULL,
  `operare_descriere` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `operare_data` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pk`),
  CONSTRAINT `documente_word_istoric_chk_1` CHECK (json_valid(`continut`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `facturi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `facturi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `comanda_client_id` int unsigned DEFAULT NULL,
  `seria` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data` date DEFAULT NULL,
  `moneda_id` tinyint DEFAULT NULL,
  `procent_tva_id` tinyint unsigned DEFAULT NULL,
  `zile_scadente` smallint unsigned DEFAULT NULL,
  `alerte_scadenta` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'se pot adauga mai multe zile, cu cate inainte de scadenta sa se trimita alertele',
  `ultima_alerta_trimisa` date DEFAULT NULL,
  `furnizor_nume` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `furnizor_reg_com` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `furnizor_cif` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `furnizor_adresa` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `furnizor_banca` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `furnizor_swift_code` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `furnizor_iban_eur` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `furnizor_iban_eur_banca` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `furnizor_iban_ron` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `furnizor_iban_ron_banca` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `furnizor_capital_social` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `client_id` int unsigned DEFAULT NULL,
  `client_contract` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `client_nume` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `client_cif` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `client_adresa` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `client_tara_id` int unsigned DEFAULT NULL,
  `client_limba_id` tinyint unsigned DEFAULT NULL,
  `client_telefon` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `client_email` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `curs_moneda` decimal(10,6) DEFAULT NULL,
  `total_fara_tva_moneda` decimal(10,2) DEFAULT NULL,
  `total_tva_moneda` decimal(10,2) DEFAULT NULL,
  `total_moneda` decimal(10,2) DEFAULT NULL,
  `total_fara_tva_lei` decimal(10,2) DEFAULT NULL,
  `total_tva_lei` decimal(10,2) DEFAULT NULL,
  `total_lei` decimal(10,2) DEFAULT NULL,
  `intocmit_de` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cnp` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aviz_insotire` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delegat` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buletin` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `auto` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mentiuni` varchar(2000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stornata` tinyint unsigned NOT NULL DEFAULT '0',
  `stornare_factura_id_originala` int unsigned DEFAULT NULL,
  `stornare_motiv` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `factura_transportator` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_plata_transportator` date DEFAULT NULL COMMENT 'it has no business in this tabel, but it''s put here temporary',
  `observatii` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `facturi_chitante`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `facturi_chitante` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `factura_id` int unsigned DEFAULT NULL,
  `seria` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numar` int unsigned DEFAULT NULL,
  `data` date DEFAULT NULL,
  `suma` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `facturi_produse`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `facturi_produse` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `factura_id` int unsigned DEFAULT NULL,
  `comanda_id` int unsigned DEFAULT NULL,
  `nr_crt` int unsigned DEFAULT NULL,
  `denumire` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `um` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cantitate` int DEFAULT NULL,
  `pret_unitar_fara_tva` decimal(10,2) DEFAULT NULL,
  `valoare` decimal(10,2) DEFAULT NULL,
  `valoare_tva` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `service_ff_facturi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `service_ff_facturi` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `denumire_furnizor` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cont_iban` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numar_factura` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_factura` date NOT NULL,
  `data_scadenta` date NOT NULL,
  `suma` decimal(12,2) NOT NULL,
  `moneda` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `departament_vehicul` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observatii` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `service_ff_facturi_status_data_scadenta_index` (`data_scadenta`),
  KEY `service_ff_facturi_denumire_furnizor_index` (`denumire_furnizor`),
  KEY `service_ff_facturi_departament_vehicul_index` (`departament_vehicul`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `service_ff_facturi_plati`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `service_ff_facturi_plati` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `factura_id` bigint unsigned NOT NULL,
  `calup_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `service_ff_facturi_plati_factura_id_unique` (`factura_id`),
  KEY `service_ff_facturi_plati_calup_id_foreign` (`calup_id`),
  CONSTRAINT `service_ff_facturi_plati_calup_id_foreign` FOREIGN KEY (`calup_id`) REFERENCES `service_ff_plati_calupuri` (`id`) ON DELETE CASCADE,
  CONSTRAINT `service_ff_facturi_plati_factura_id_foreign` FOREIGN KEY (`factura_id`) REFERENCES `service_ff_facturi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `service_ff_plati_calupuri`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `service_ff_plati_calupuri` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `denumire_calup` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_plata` date DEFAULT NULL,
  `observatii` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `service_ff_plati_calupuri_status_data_plata_index` (`data_plata`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `service_ff_plati_calupuri_fisiere`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `service_ff_plati_calupuri_fisiere` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `plata_calup_id` bigint unsigned NOT NULL,
  `cale` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nume_original` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `service_ff_plati_calupuri_fisiere_plata_calup_id_index` (`plata_calup_id`),
  CONSTRAINT `service_ff_plati_calupuri_fisiere_plata_calup_id_foreign` FOREIGN KEY (`plata_calup_id`) REFERENCES `service_ff_plati_calupuri` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `firme`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `firme` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nume` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contract_nr` int unsigned DEFAULT NULL,
  `contract_data` date DEFAULT NULL,
  `tip_partener` tinyint unsigned NOT NULL COMMENT '1-clienti / 2-transportatori',
  `tara_id` smallint unsigned DEFAULT NULL,
  `cui` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cui_extern` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reg_com` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `format_documente` tinyint(1) DEFAULT NULL COMMENT '1-per post / 2-digital	',
  `oras` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `judet` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adresa` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cod_postal` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banca` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cont_iban` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banca_eur` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cont_iban_eur` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zile_scadente` smallint unsigned DEFAULT NULL,
  `persoana_contact` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `skype` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_factura` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefon` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fax` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_bursa` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observatii` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `firme_istoric`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `firme_istoric` (
  `id_pk` int unsigned NOT NULL AUTO_INCREMENT,
  `id` int unsigned NOT NULL,
  `nume` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contract_nr` int DEFAULT NULL,
  `contract_data` date DEFAULT NULL,
  `tip_partener` tinyint unsigned NOT NULL COMMENT '1-clienti / 2-transportatori',
  `tara_id` smallint unsigned DEFAULT NULL,
  `cui` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cui_extern` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reg_com` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `format_documente` tinyint(1) DEFAULT NULL COMMENT '1-per post / 2-digital	',
  `oras` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `judet` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adresa` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cod_postal` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banca` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cont_iban` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banca_eur` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cont_iban_eur` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zile_scadente` smallint unsigned DEFAULT NULL,
  `persoana_contact` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `skype` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_factura` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefon` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fax` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_bursa` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observatii` text COLLATE utf8mb4_unicode_ci,
  `operare_user_id` int unsigned DEFAULT NULL,
  `operare_descriere` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `operare_data` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fisiere`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fisiere` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nume` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `categorie` tinyint DEFAULT NULL COMMENT '1maseco/ 2masini',
  `fisier_nume` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fisier_cale` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observatii` varchar(2000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fisiere_istoric`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fisiere_istoric` (
  `id_pk` int unsigned NOT NULL AUTO_INCREMENT,
  `id` int unsigned NOT NULL,
  `nume` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `categorie` tinyint DEFAULT NULL COMMENT '1maseco/ 2masini',
  `fisier_nume` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fisier_cale` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observatii` varchar(2000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `operare_user_id` int unsigned DEFAULT NULL,
  `operare_descriere` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `operare_data` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `flota_statusuri`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `flota_statusuri` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `utilizator_id` int unsigned DEFAULT NULL,
  `nr_auto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dimenssions` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `out_of_eu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `info` tinyint unsigned DEFAULT NULL COMMENT '1 - In tranzit, fara cursa dupa descarcare /\r\n2 - De grupat /\r\n3 - Liber',
  `abilities` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_of_the_shipment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comanda` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `info_ii` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `info_iii` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `special_info` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `e_km` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `flota_statusuri_c`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `flota_statusuri_c` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nr_auto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dimenssions` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `out_of_eu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `info_i` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `info_ii` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ordine` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `flota_statusuri_informatii`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `flota_statusuri_informatii` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `modalitate_de_plata` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `spot` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `termen` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `info` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `info_2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `flota_statusuri_utilizatori`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `flota_statusuri_utilizatori` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nume` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `culoare_background` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `culoare_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ordine_afisare` tinyint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `intermedieri`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intermedieri` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `comanda_id` int unsigned DEFAULT NULL,
  `observatii` varchar(2000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `motis` decimal(8,2) DEFAULT NULL,
  `dkv` decimal(8,2) DEFAULT NULL,
  `astra` decimal(8,2) DEFAULT NULL,
  `plata_client` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `predat_la_contabilitate` tinyint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `key_performance_indicators`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `key_performance_indicators` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned DEFAULT NULL,
  `data` date DEFAULT NULL,
  `observatii` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `key_performance_indicators_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `key_performance_indicators_history` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `kpi_id` int unsigned NOT NULL COMMENT 'Reference to the original KPI',
  `user_id` int NOT NULL COMMENT 'The user for whom the KPI belongs',
  `observatii` text COLLATE utf8mb4_unicode_ci,
  `data` date DEFAULT NULL,
  `performed_by_user_id` int unsigned NOT NULL COMMENT 'The user who performed the action',
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '''create'', ''update'', ''delete''',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT ' When the operation occurred',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `limbi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `limbi` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nume` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `locuri_operare`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `locuri_operare` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nume` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tara_id` smallint unsigned NOT NULL,
  `judet` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `oras` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adresa` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cod_postal` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `persoana_contact` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `skype` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefon` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observatii` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `locuri_operare_istoric`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `locuri_operare_istoric` (
  `id_pk` int unsigned NOT NULL AUTO_INCREMENT,
  `id` int unsigned NOT NULL,
  `nume` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tara_id` smallint unsigned NOT NULL,
  `judet` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `oras` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adresa` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cod_postal` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `persoana_contact` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `skype` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefon` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observatii` text COLLATE utf8mb4_unicode_ci,
  `operare_user_id` int unsigned DEFAULT NULL,
  `operare_descriere` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `operare_data` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `masini_valabilitati`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `masini_valabilitati` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nr_auto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nume_sofer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `detalii_sofer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `divizie` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valabilitate_1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valabilitate_1_inceput` date DEFAULT NULL,
  `valabilitate_1_sfarsit` date DEFAULT NULL,
  `observatii_1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valabilitate_2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valabilitate_2_inceput` date DEFAULT NULL,
  `valabilitate_2_sfarsit` date DEFAULT NULL,
  `observatii_2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mementouri`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mementouri` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nume` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_expirare` date DEFAULT NULL,
  `email` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT 'masecoexpres@gmail.com',
  `telefon` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descriere` mediumtext COLLATE utf8mb4_unicode_ci,
  `observatii` mediumtext COLLATE utf8mb4_unicode_ci,
  `tip` tinyint unsigned DEFAULT NULL COMMENT '1-general / 2-rca / 3-itp+rovinieta',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mementouri_alerte`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mementouri_alerte` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `memento_id` int unsigned DEFAULT NULL,
  `data` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mesaje_trimise_email`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mesaje_trimise_email` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `comanda_id` int unsigned DEFAULT NULL,
  `firma_id` int unsigned DEFAULT NULL,
  `categorie` tinyint unsigned DEFAULT NULL COMMENT '1informareIncepereComanda 2cerereStatus 3contractCatreTransportator 4contractCcaCatreTransportator 5raspunsLaCerereStatus 6trimitereCodAutentificarePrinEmail 7transportatorCatreMasecoDocumenteIncarcate 8debitNoteCatreTransportator 9informareAdaugareClientNouInDB',
  `email` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `categorie` (`categorie`,`comanda_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mesaje_trimise_sms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mesaje_trimise_sms` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `categorie` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subcategorie` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referinta_id` int unsigned DEFAULT NULL,
  `telefon` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mesaj` varchar(700) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trimis` tinyint DEFAULT NULL,
  `mesaj_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `eroare_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `raspuns` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `metode_de_plata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `metode_de_plata` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nume` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `monede`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `monede` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nume` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `oferte_curse`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oferte_curse` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `id_sursa` int unsigned DEFAULT NULL,
  `email_subiect` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_expeditor` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_primirii` datetime DEFAULT NULL,
  `email_text` text COLLATE utf8mb4_unicode_ci,
  `gmail_link` text COLLATE utf8mb4_unicode_ci,
  `incarcare_cod_postal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `incarcare_localitate` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `incarcare_data_ora` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descarcare_cod_postal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descarcare_localitate` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descarcare_data_ora` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `detalii_cursa` text COLLATE utf8mb4_unicode_ci,
  `greutate` int unsigned DEFAULT NULL,
  `latitudine` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `longitudine` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metoda_procesare` tinyint unsigned DEFAULT NULL,
  `failure_type` tinyint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_from` (`email_expeditor`),
  KEY `idx_received` (`data_primirii`),
  KEY `idx_updated_at` (`updated_at`),
  KEY `idx_deleted_at` (`deleted_at`),
  KEY `idx_created_at` (`created_at`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `procente_tva`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `procente_tva` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nume` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `statii_peco`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `statii_peco` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `numar_statie` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nume` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `strada` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cod_postal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `localitate` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitudine` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `longitudine` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `coordonate` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `statii_peco_istoric`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `statii_peco_istoric` (
  `id_pk` int unsigned NOT NULL AUTO_INCREMENT,
  `id` int unsigned NOT NULL,
  `numar_statie` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nume` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `strada` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cod_postal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `localitate` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitudine` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `longitudine` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `coordonate` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `operare_user_id` int unsigned DEFAULT NULL,
  `operare_descriere` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `operare_data` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tari`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tari` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `nume` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gmt_offset` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `telescope_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `telescope_entries` (
  `sequence` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `family_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `should_display_on_index` tinyint(1) NOT NULL DEFAULT '1',
  `type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`sequence`),
  UNIQUE KEY `telescope_entries_uuid_unique` (`uuid`),
  KEY `telescope_entries_batch_id_index` (`batch_id`),
  KEY `telescope_entries_family_hash_index` (`family_hash`),
  KEY `telescope_entries_created_at_index` (`created_at`),
  KEY `telescope_entries_type_should_display_on_index_index` (`type`,`should_display_on_index`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `telescope_entries_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `telescope_entries_tags` (
  `entry_uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tag` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  KEY `telescope_entries_tags_entry_uuid_tag_index` (`entry_uuid`,`tag`),
  KEY `telescope_entries_tags_tag_index` (`tag`),
  CONSTRAINT `telescope_entries_tags_entry_uuid_foreign` FOREIGN KEY (`entry_uuid`) REFERENCES `telescope_entries` (`uuid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `telescope_monitoring`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `telescope_monitoring` (
  `tag` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `termene_de_plata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `termene_de_plata` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nume` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `role` tinyint(1) DEFAULT NULL COMMENT '1-admin | 2-dispecer',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefon` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cod_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activ` tinyint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'2014_10_12_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'2014_10_12_100000_create_password_reset_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'2014_10_12_100000_create_password_resets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2018_08_08_100000_create_telescope_entries_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2019_08_19_000000_create_failed_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2019_12_14_000001_create_personal_access_tokens_table',1);
