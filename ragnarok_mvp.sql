-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 19/03/2026 às 18:24
-- Versão do servidor: 9.1.0
-- Versão do PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `ragnarok_mvp`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `configuracoes`
--

DROP TABLE IF EXISTS `configuracoes`;
CREATE TABLE IF NOT EXISTS `configuracoes` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `usuario_id` int UNSIGNED NOT NULL,
  `alerta_minutos` int UNSIGNED NOT NULL DEFAULT '5' COMMENT 'Alerta X minutos antes do respawn',
  `som_ativo` tinyint(1) NOT NULL DEFAULT '1',
  `notif_browser` tinyint(1) NOT NULL DEFAULT '1',
  `tema` enum('dark','light') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'dark',
  `atualizado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_id` (`usuario_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `configuracoes`
--

INSERT INTO `configuracoes` (`id`, `usuario_id`, `alerta_minutos`, `som_ativo`, `notif_browser`, `tema`, `atualizado_em`) VALUES
(1, 3, 5, 1, 1, 'dark', '2026-03-17 15:51:03'),
(2, 4, 5, 1, 1, 'dark', '2026-03-19 14:22:14'),
(3, 5, 5, 1, 1, 'dark', '2026-03-19 14:23:50');

-- --------------------------------------------------------

--
-- Estrutura para tabela `monitoramentos`
--

DROP TABLE IF EXISTS `monitoramentos`;
CREATE TABLE IF NOT EXISTS `monitoramentos` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `usuario_id` int UNSIGNED NOT NULL,
  `mvp_id` int UNSIGNED NOT NULL,
  `servidor_id` int UNSIGNED NOT NULL,
  `iniciado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `morto_em` datetime DEFAULT NULL COMMENT 'Quando o MVP foi marcado como morto',
  `spawn_est` datetime DEFAULT NULL COMMENT 'Estimativa calculada de respawn',
  `finalizado` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `servidor_id` (`servidor_id`),
  KEY `idx_usuario` (`usuario_id`),
  KEY `idx_mvp` (`mvp_id`),
  KEY `idx_iniciado` (`iniciado_em`),
  KEY `idx_finalizado` (`finalizado`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `monitoramentos`
--

INSERT INTO `monitoramentos` (`id`, `usuario_id`, `mvp_id`, `servidor_id`, `iniciado_em`, `morto_em`, `spawn_est`, `finalizado`) VALUES
(1, 5, 3, 1, '2026-03-19 15:21:22', '2026-03-19 15:21:00', '2026-03-19 16:21:00', 0),
(2, 5, 13, 1, '2026-03-19 15:21:46', '2026-03-19 15:21:00', '2026-03-19 17:21:00', 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `mvps`
--

DROP TABLE IF EXISTS `mvps`;
CREATE TABLE IF NOT EXISTS `mvps` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mapa` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `spawn_min` int UNSIGNED NOT NULL,
  `item_id` int UNSIGNED DEFAULT NULL COMMENT 'ID do card no divine-pride para URL automática',
  `imagem_url` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nivel` smallint UNSIGNED DEFAULT NULL,
  `elemento` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `criado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ativo` (`ativo`),
  KEY `idx_nome` (`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `mvps`
--

INSERT INTO `mvps` (`id`, `nome`, `mapa`, `spawn_min`, `item_id`, `imagem_url`, `nivel`, `elemento`, `ativo`, `criado_em`) VALUES
(2, 'Orc Herói', 'gef_fild03', 60, 4143, NULL, 50, 'Terra', 1, '2026-03-19 14:48:35'),
(3, 'Abelha-Rainha', 'mjolnir_04', 60, 4132, NULL, 78, 'Vento', 1, '2026-03-19 14:50:07'),
(4, 'Amon Ra', 'moc_pryd06', 60, 4236, NULL, 69, 'Terra', 1, '2026-03-19 14:51:17'),
(5, 'Aranha Rainha', 'gl_cas01_', 120, 27362, NULL, 195, 'Sombrio', 1, '2026-03-19 14:52:51'),
(6, 'Atroce', 'ra_fild03', 3, 4425, NULL, 113, 'Sombrio', 1, '2026-03-19 14:59:14'),
(7, 'Bafomé', 'prt_maze03', 120, 4147, NULL, 81, 'Sombrio', 1, '2026-03-19 14:59:56'),
(8, 'Belzebu', 'qqq', 360, 4145, NULL, 147, 'Fantasma', 1, '2026-03-19 15:01:39'),
(9, 'Besouro-Ladrão Dourado', 'prt_sewb4', 60, 4128, NULL, 65, 'Fogo', 1, '2026-03-19 15:02:13'),
(10, 'Boitatá', 'bra_dun02', 120, 27126, NULL, 93, NULL, 1, '2026-03-19 15:02:57'),
(11, 'Cavaleiro da Tempestade', 'xmas_dun02', 60, 4318, NULL, 92, 'Vento', 1, '2026-03-19 15:03:53'),
(12, 'Detardeurus', 'abyss_03', 180, 4386, NULL, 135, 'Sombrio', 1, '2026-03-19 15:04:32'),
(13, 'Doppelganger', 'gef_dun02', 120, 4142, NULL, 77, 'Sombrio', 1, '2026-03-19 15:05:25'),
(14, 'Drácula', 'gef_dun01', 60, 4134, NULL, 75, 'Sombrio', 1, '2026-03-19 15:05:52'),
(15, 'Drake', 'treasure02', 120, 4137, NULL, 91, 'Maldito', 1, '2026-03-19 15:06:25'),
(16, 'Eddga', 'pay_fild10', 60, 4123, NULL, 65, 'Fogo', 1, '2026-03-19 15:07:07'),
(17, 'Faraó', 'in_sphinx5', 60, 4148, NULL, 85, 'Sombrio', 1, '2026-03-19 15:07:51'),
(18, 'Flor do Luar', 'pay_dun04', 60, 4131, NULL, 79, 'Fogo', 1, '2026-03-19 15:08:19'),
(19, 'Freeoni', 'moc_fild17', 120, 4121, NULL, 71, 'Neutro', 1, '2026-03-19 15:08:48'),
(20, 'Gorynych', 'mosk_dun03', 120, 27162, NULL, 97, 'Terra', 1, '2026-03-19 15:09:38'),
(21, 'Hatii', 'xmas_fild01', 120, 4324, NULL, 98, 'Água', 1, '2026-03-19 15:10:06'),
(22, 'Leak', 'dew_dun01', 120, 4520, NULL, 94, 'Sombrio', 1, '2026-03-19 15:10:33'),
(23, 'Maya', 'anthell02', 120, 4146, NULL, 55, 'Terra', 1, '2026-03-19 15:11:21'),
(24, 'Osíris', 'moc_pryd04', 60, 4144, NULL, 68, 'Maldito', 1, '2026-03-19 15:11:45'),
(25, 'Pesar Noturno', 'ra_san05', 300, 4408, NULL, 139, 'Fantasma', 1, '2026-03-19 15:12:10'),
(26, 'R48-85-BESTIA', 'sp_rudus2', 60, 27319, NULL, 174, 'Neutro', 1, '2026-03-19 15:13:04'),
(27, 'Rainha Scaraba', 'dic_dun02', 120, 4507, NULL, 140, 'Terra', 1, '2026-03-19 15:13:31'),
(28, 'RSX-0806', 'ein_dun02', 125, 4342, NULL, 100, 'Neutro', 1, '2026-03-19 15:13:57'),
(29, 'Samurai Encarnado', 'ama_dun03', 91, 4263, NULL, 100, 'Sombrio', 1, '2026-03-19 15:14:28'),
(30, 'Senhor das Trevas', 'gl_chyard', 60, 4168, NULL, 96, 'Maldito', 1, '2026-03-19 15:15:22'),
(31, 'Tao Gunka', 'beach_dun', 300, 4302, NULL, 110, 'Neutro', 1, '2026-03-19 15:15:47'),
(32, 'Valquíria Randgris', 'odin_tem03', 480, 4407, NULL, 141, 'Sagrado', 1, '2026-03-19 15:16:16'),
(33, 'Vesper', 'jupe_core', 120, 4374, NULL, 128, 'Sagrado', 1, '2026-03-19 15:16:49'),
(34, 'Lady Branca', 'lou_dun03', 117, 4372, NULL, 97, 'Vento', 1, '2026-03-19 15:18:14');

-- --------------------------------------------------------

--
-- Estrutura para tabela `servidores`
--

DROP TABLE IF EXISTS `servidores`;
CREATE TABLE IF NOT EXISTS `servidores` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo` enum('low','mid','high','custom') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'mid',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `criado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `servidores`
--

INSERT INTO `servidores` (`id`, `nome`, `descricao`, `tipo`, `ativo`, `criado_em`) VALUES
(1, 'LATAM', NULL, 'mid', 1, '2026-03-19 15:20:41'),
(2, 'NIDHHOG', NULL, 'mid', 1, '2026-03-19 15:20:51'),
(3, 'HERO', NULL, 'mid', 1, '2026-03-19 15:20:57');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `senha_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','usuario') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'usuario',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `criado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ultimo_login` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_ativo` (`ativo`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `username`, `email`, `senha_hash`, `role`, `ativo`, `criado_em`, `ultimo_login`) VALUES
(3, 'jean', 'jean.c1987@aluno.ifsc.edu.br', '$2y$12$udVL.05PGGhFElj.YmHToOCyy.9UIXPyw9xUGgUmKhA7Zh5DbAYpi', 'admin', 1, '2026-03-17 15:51:03', '2026-03-17 16:08:02'),
(4, 'jean7874', 'jean@gmail.com', '$2y$12$tcckH5gdKHaM8HuR52nHT.4A9wjbj0J4nyP83.y.f3hHlAC21wxFC', 'usuario', 1, '2026-03-19 14:22:14', NULL),
(5, 'jeansantos', 'jeansantos@gmail.com', '$2y$12$.mjRPrasyyUmnAVqKsLA6elLyCseD3MRnoaWOKF/JCYuwqNlWuWC6', 'admin', 1, '2026-03-19 14:23:50', '2026-03-19 14:43:38');

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `vw_historico_monitoramentos`
-- (Veja abaixo para a visão atual)
--
DROP VIEW IF EXISTS `vw_historico_monitoramentos`;
CREATE TABLE IF NOT EXISTS `vw_historico_monitoramentos` (
`id` bigint unsigned
,`username` varchar(50)
,`email` varchar(120)
,`mvp_nome` varchar(100)
,`mapa` varchar(100)
,`servidor_nome` varchar(100)
,`iniciado_em` datetime
,`morto_em` datetime
,`spawn_est` datetime
,`finalizado` tinyint(1)
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `vw_ranking_mvps`
-- (Veja abaixo para a visão atual)
--
DROP VIEW IF EXISTS `vw_ranking_mvps`;
CREATE TABLE IF NOT EXISTS `vw_ranking_mvps` (
);

-- --------------------------------------------------------

--
-- Estrutura para view `vw_historico_monitoramentos`
--
DROP TABLE IF EXISTS `vw_historico_monitoramentos`;

DROP VIEW IF EXISTS `vw_historico_monitoramentos`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_historico_monitoramentos`  AS SELECT `m`.`id` AS `id`, `u`.`username` AS `username`, `u`.`email` AS `email`, `mv`.`nome` AS `mvp_nome`, `mv`.`mapa` AS `mapa`, `s`.`nome` AS `servidor_nome`, `m`.`iniciado_em` AS `iniciado_em`, `m`.`morto_em` AS `morto_em`, `m`.`spawn_est` AS `spawn_est`, `m`.`finalizado` AS `finalizado` FROM (((`monitoramentos` `m` join `usuarios` `u` on((`u`.`id` = `m`.`usuario_id`))) join `mvps` `mv` on((`mv`.`id` = `m`.`mvp_id`))) join `servidores` `s` on((`s`.`id` = `m`.`servidor_id`))) ;

-- --------------------------------------------------------

--
-- Estrutura para view `vw_ranking_mvps`
--
DROP TABLE IF EXISTS `vw_ranking_mvps`;

DROP VIEW IF EXISTS `vw_ranking_mvps`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_ranking_mvps`  AS SELECT `mv`.`id` AS `id`, `mv`.`nome` AS `mvp_nome`, `s`.`nome` AS `servidor_nome`, count(`m`.`id`) AS `total_monitoramentos`, max(`m`.`iniciado_em`) AS `ultimo_monitoramento` FROM ((`mvps` `mv` join `servidores` `s` on((`s`.`id` = `mv`.`servidor_id`))) left join `monitoramentos` `m` on((`m`.`mvp_id` = `mv`.`id`))) GROUP BY `mv`.`id`, `mv`.`nome`, `s`.`nome` ORDER BY `total_monitoramentos` DESC ;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `configuracoes`
--
ALTER TABLE `configuracoes`
  ADD CONSTRAINT `configuracoes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `monitoramentos`
--
ALTER TABLE `monitoramentos`
  ADD CONSTRAINT `monitoramentos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `monitoramentos_ibfk_2` FOREIGN KEY (`mvp_id`) REFERENCES `mvps` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `monitoramentos_ibfk_3` FOREIGN KEY (`servidor_id`) REFERENCES `servidores` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
