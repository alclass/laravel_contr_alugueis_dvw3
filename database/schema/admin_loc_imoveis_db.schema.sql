-- phpMyAdmin SQL Dump
-- version 4.7.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 14, 2017 at 01:35 AM
-- Server version: 5.7.19-0ubuntu0.16.04.1
-- PHP Version: 7.0.22-0ubuntu0.16.04.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `admin_loc_imoveis_db`
--
CREATE DATABASE IF NOT EXISTS `admin_loc_imoveis_db` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE `admin_loc_imoveis_db`;

-- --------------------------------------------------------

--
-- Table structure for table `additiontarifas`
--

CREATE TABLE `additiontarifas` (
  `id` int(10) UNSIGNED NOT NULL,
  `cobrancatipo_id` int(11) NOT NULL,
  `contract_id` int(11) NOT NULL,
  `monthyeardateref` date DEFAULT NULL,
  `n_cota` int(11) DEFAULT NULL,
  `total_cotas` int(11) DEFAULT NULL,
  `lineinfo` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valor` decimal(9,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `amortizationpayments`
--

CREATE TABLE `amortizationpayments` (
  `id` smallint(6) NOT NULL,
  `payer_person_id` int(10) UNSIGNED NOT NULL,
  `is_loan_delivery` tinyint(1) NOT NULL DEFAULT '0',
  `loan_duration_in_months` tinyint(3) UNSIGNED DEFAULT NULL,
  `paydate` date NOT NULL,
  `valor_pago` decimal(9,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Pagtos de financiamento contextualizado SAC/PRICE';

-- --------------------------------------------------------

--
-- Table structure for table `bankaccounts`
--

CREATE TABLE `bankaccounts` (
  `id` int(10) UNSIGNED NOT NULL,
  `banknumber` int(11) NOT NULL,
  `bank_4char` char(4) COLLATE utf8_unicode_ci NOT NULL,
  `bankname` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `agency` int(11) NOT NULL,
  `account` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `customer` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cpf` varchar(11) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `billingitems`
--

CREATE TABLE `billingitems` (
  `id` int(10) UNSIGNED NOT NULL,
  `cobranca_id` int(10) UNSIGNED DEFAULT NULL,
  `cobrancatipo_id` tinyint(3) UNSIGNED NOT NULL,
  `brief_description` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `charged_value` decimal(9,2) NOT NULL,
  `ref_type` char(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'D',
  `freq_used_ref` char(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'M',
  `monthyeardateref` date DEFAULT NULL,
  `n_cota_ref` tinyint(3) UNSIGNED DEFAULT NULL,
  `total_cotas_ref` tinyint(3) UNSIGNED DEFAULT NULL,
  `was_original_value_modified` tinyint(1) NOT NULL DEFAULT '0',
  `brief_description_for_modifier_if_any` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `original_value_if_needed` decimal(9,2) DEFAULT NULL,
  `percent_in_modifying_if_any` tinyint(4) DEFAULT NULL,
  `money_amount_in_modifying_if_any` decimal(8,2) DEFAULT NULL,
  `obs` varchar(144) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cobrancas`
--

CREATE TABLE `cobrancas` (
  `id` int(10) UNSIGNED NOT NULL,
  `monthyeardateref` date NOT NULL,
  `n_seq_from_dateref` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `duedate` date DEFAULT NULL,
  `discount` decimal(9,2) DEFAULT NULL,
  `price_increase_if_any` decimal(9,2) DEFAULT NULL,
  `lineinfo_discount_or_increase` varchar(144) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tot_adic_em_tribs` decimal(9,2) DEFAULT NULL,
  `n_items` tinyint(3) UNSIGNED DEFAULT NULL,
  `contract_id` int(11) DEFAULT NULL,
  `bankaccount_id` int(11) DEFAULT NULL,
  `n_parcelas` int(11) NOT NULL DEFAULT '1',
  `are_parcels_monthly` tinyint(1) DEFAULT NULL,
  `parcel_n_days_interval` int(11) DEFAULT NULL,
  `has_been_paid` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cobrancatipos`
--

CREATE TABLE `cobrancatipos` (
  `id` int(10) UNSIGNED NOT NULL,
  `char4id` char(4) COLLATE utf8_unicode_ci DEFAULT NULL,
  `brief_description` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `is_repasse` tinyint(1) DEFAULT '0',
  `long_description` varchar(120) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `condominiotarifas`
--

CREATE TABLE `condominiotarifas` (
  `id` int(10) UNSIGNED NOT NULL,
  `imovel_id` int(11) NOT NULL,
  `tarifa_valor` decimal(9,2) NOT NULL,
  `monthyeardateref` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contractbillingrules`
--

CREATE TABLE `contractbillingrules` (
  `id` int(10) UNSIGNED NOT NULL,
  `contract_id` int(11) NOT NULL,
  `cobrancatipo_id` int(11) NOT NULL,
  `ref_type` char(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'D',
  `freq_used_ref` char(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'M',
  `total_cotas_ref` tinyint(3) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contracts`
--

CREATE TABLE `contracts` (
  `id` int(10) UNSIGNED NOT NULL,
  `imovel_id` int(11) NOT NULL,
  `initial_rent_value` decimal(9,2) DEFAULT NULL,
  `current_rent_value` decimal(9,2) DEFAULT NULL,
  `bankaccount_id` tinyint(3) UNSIGNED DEFAULT NULL,
  `reajuste_indice4char` char(4) COLLATE utf8_unicode_ci DEFAULT 'IGPM',
  `corrmonet_indice4char` char(4) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'SELI',
  `pay_day_when_monthly` int(11) DEFAULT NULL,
  `apply_multa_incid_mora` tinyint(1) DEFAULT NULL,
  `perc_multa_incid_mora` tinyint(3) DEFAULT NULL,
  `apply_juros_fixos_am` tinyint(1) DEFAULT NULL,
  `perc_juros_fixos_am` int(3) DEFAULT NULL,
  `apply_corrmonet_am` tinyint(1) DEFAULT NULL,
  `signing_date` date DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `duration_in_months` int(11) DEFAULT NULL,
  `n_days_aditional` tinyint(4) DEFAULT NULL,
  `repassar_condominio` tinyint(1) NOT NULL DEFAULT '1',
  `repassar_iptu` tinyint(1) NOT NULL DEFAULT '1',
  `is_active` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contract_user`
--

CREATE TABLE `contract_user` (
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `contract_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `corrmonets`
--

CREATE TABLE `corrmonets` (
  `id` int(10) UNSIGNED NOT NULL,
  `mercado_indicador_id` tinyint(3) UNSIGNED NOT NULL,
  `indice4char` char(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fraction_value` decimal(6,5) NOT NULL,
  `monthyeardateref` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `imoveis`
--

CREATE TABLE `imoveis` (
  `id` int(10) UNSIGNED NOT NULL,
  `apelido` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `predio_nome` varchar(35) COLLATE utf8_unicode_ci DEFAULT NULL,
  `logradouro` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `tipo_lograd` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `numero` smallint(6) UNSIGNED DEFAULT NULL,
  `complemento` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `cep` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `tipo_imov` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `n_quartos` tinyint(3) UNSIGNED DEFAULT NULL,
  `n_banheiros` tinyint(3) UNSIGNED DEFAULT NULL,
  `n_dependencias` tinyint(4) UNSIGNED DEFAULT NULL,
  `n_salas` tinyint(3) UNSIGNED DEFAULT NULL,
  `n_cozinhas` tinyint(3) UNSIGNED DEFAULT NULL,
  `varanda_area_m2` tinyint(3) UNSIGNED DEFAULT NULL,
  `n_vagas_garagem` tinyint(3) UNSIGNED DEFAULT NULL,
  `is_rentable` tinyint(1) NOT NULL DEFAULT '1',
  `area_edif_iptu_m2` smallint(6) DEFAULT NULL,
  `area_terr_iptu_m2` smallint(6) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `iptutabelas`
--

CREATE TABLE `iptutabelas` (
  `id` int(10) UNSIGNED NOT NULL,
  `imovel_id` int(11) NOT NULL,
  `optado_por_cota_unica` tinyint(1) NOT NULL DEFAULT '0',
  `ano` smallint(5) UNSIGNED NOT NULL,
  `ano_quitado` tinyint(1) NOT NULL DEFAULT '0',
  `n_cota_quitada_ate_entao` tinyint(3) UNSIGNED DEFAULT NULL,
  `valor_parcela_unica` decimal(8,2) NOT NULL,
  `valor_parcela_10x` decimal(7,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mercadoindices`
--

CREATE TABLE `mercadoindices` (
  `id` int(10) UNSIGNED NOT NULL,
  `indice4char` char(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sigla` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `since` date DEFAULT NULL,
  `url_datasource` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `info` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `migration` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `moradebitos`
--

CREATE TABLE `moradebitos` (
  `id` int(10) UNSIGNED NOT NULL,
  `contract_id` int(11) NOT NULL,
  `monthyeardateref` date DEFAULT NULL,
  `is_open` tinyint(1) NOT NULL DEFAULT '1',
  `ini_debt_date` date NOT NULL,
  `ini_debt_value` decimal(9,2) NOT NULL,
  `changed_debt_date` date DEFAULT NULL,
  `changed_debt_value` decimal(6,2) DEFAULT NULL,
  `mora_rules_id` tinyint(4) DEFAULT NULL,
  `lineinfo` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `history` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(10) UNSIGNED NOT NULL,
  `amount` decimal(8,2) NOT NULL,
  `bankname` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `deposited_on` date NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `imovel_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `persons`
--

CREATE TABLE `persons` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id_if_applicable` int(10) UNSIGNED DEFAULT NULL,
  `cpf` int(11) DEFAULT NULL,
  `first_name` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `middle_names` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_name` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `relation` char(4) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `first_name` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `middle_names` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_name` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cpf` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rg` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tipo_relacao` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(191) COLLATE utf8_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `additiontarifas`
--
ALTER TABLE `additiontarifas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `amortizationpayments`
--
ALTER TABLE `amortizationpayments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bankaccounts`
--
ALTER TABLE `bankaccounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `billingitems`
--
ALTER TABLE `billingitems`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cobranca_id_index` (`cobranca_id`);

--
-- Indexes for table `cobrancas`
--
ALTER TABLE `cobrancas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contract_id` (`contract_id`);

--
-- Indexes for table `cobrancatipos`
--
ALTER TABLE `cobrancatipos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `text_id_index` (`char4id`);

--
-- Indexes for table `condominiotarifas`
--
ALTER TABLE `condominiotarifas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contractbillingrules`
--
ALTER TABLE `contractbillingrules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contracts`
--
ALTER TABLE `contracts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contract_user`
--
ALTER TABLE `contract_user`
  ADD KEY `imovel_user_imovel_id_foreign` (`contract_id`),
  ADD KEY `imovel_user_user_id_foreign` (`user_id`);

--
-- Indexes for table `corrmonets`
--
ALTER TABLE `corrmonets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `corrmonets_indicador_index` (`indice4char`),
  ADD KEY `mercado_indicador_index` (`mercado_indicador_id`);

--
-- Indexes for table `imoveis`
--
ALTER TABLE `imoveis`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `iptutabelas`
--
ALTER TABLE `iptutabelas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mercadoindices`
--
ALTER TABLE `mercadoindices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `moradebitos`
--
ALTER TABLE `moradebitos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`),
  ADD KEY `password_resets_token_index` (`token`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payments_user_id_index` (`user_id`),
  ADD KEY `payments_imovel_id_index` (`imovel_id`);

--
-- Indexes for table `persons`
--
ALTER TABLE `persons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `additiontarifas`
--
ALTER TABLE `additiontarifas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `amortizationpayments`
--
ALTER TABLE `amortizationpayments`
  MODIFY `id` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `bankaccounts`
--
ALTER TABLE `bankaccounts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `billingitems`
--
ALTER TABLE `billingitems`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
--
-- AUTO_INCREMENT for table `cobrancas`
--
ALTER TABLE `cobrancas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `cobrancatipos`
--
ALTER TABLE `cobrancatipos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `condominiotarifas`
--
ALTER TABLE `condominiotarifas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
--
-- AUTO_INCREMENT for table `contractbillingrules`
--
ALTER TABLE `contractbillingrules`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `contracts`
--
ALTER TABLE `contracts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `corrmonets`
--
ALTER TABLE `corrmonets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
--
-- AUTO_INCREMENT for table `imoveis`
--
ALTER TABLE `imoveis`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `iptutabelas`
--
ALTER TABLE `iptutabelas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `mercadoindices`
--
ALTER TABLE `mercadoindices`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `moradebitos`
--
ALTER TABLE `moradebitos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
--
-- AUTO_INCREMENT for table `persons`
--
ALTER TABLE `persons`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `billingitems`
--
ALTER TABLE `billingitems`
  ADD CONSTRAINT `cobranca_id_fk` FOREIGN KEY (`cobranca_id`) REFERENCES `cobrancas` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
