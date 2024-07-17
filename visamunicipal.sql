-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 17/07/2024 às 01:48
-- Versão do servidor: 8.3.0
-- Versão do PHP: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `visamunicipal`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `acoes_pontuacao`
--

DROP TABLE IF EXISTS `acoes_pontuacao`;
CREATE TABLE IF NOT EXISTS `acoes_pontuacao` (
  `id` int NOT NULL AUTO_INCREMENT,
  `acao_id` int NOT NULL,
  `grupo_risco_id` int NOT NULL,
  `municipio` varchar(100) NOT NULL,
  `pontuacao` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `acao_id` (`acao_id`),
  KEY `grupo_risco_id` (`grupo_risco_id`)
) ENGINE=InnoDB AUTO_INCREMENT=123 DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `acoes_pontuacao`
--

INSERT INTO `acoes_pontuacao` (`id`, `acao_id`, `grupo_risco_id`, `municipio`, `pontuacao`) VALUES
(25, 7, 1, 'GURUPI', 0),
(26, 7, 1, 'GURUPI', 35),
(27, 7, 2, 'GURUPI', 45),
(28, 7, 3, 'GURUPI', 55),
(29, 8, 1, 'GURUPI', 35),
(30, 7, 2, 'GURUPI', 45),
(31, 7, 3, 'GURUPI', 55),
(32, 9, 1, 'GURUPI', 80),
(33, 7, 2, 'GURUPI', 90),
(34, 7, 3, 'GURUPI', 110),
(35, 10, 1, 'GURUPI', 0),
(36, 10, 2, 'GURUPI', 0),
(37, 10, 3, 'GURUPI', 0),
(38, 11, 1, 'GURUPI', 35),
(39, 7, 2, 'GURUPI', 40),
(40, 11, 3, 'GURUPI', 45),
(41, 11, 2, 'GURUPI', 40),
(42, 12, 1, 'GURUPI', 30),
(43, 12, 2, 'GURUPI', 35),
(44, 12, 3, 'GURUPI', 40),
(45, 13, 1, 'GURUPI', 35),
(46, 13, 2, 'GURUPI', 40),
(47, 13, 3, 'GURUPI', 45),
(48, 14, 1, 'GURUPI', 50),
(49, 14, 2, 'GURUPI', 60),
(50, 14, 3, 'GURUPI', 70),
(51, 15, 1, 'GURUPI', 50),
(52, 15, 2, 'GURUPI', 60),
(53, 15, 3, 'GURUPI', 70),
(54, 16, 1, 'GURUPI', 70),
(55, 16, 2, 'GURUPI', 80),
(56, 16, 3, 'GURUPI', 90),
(57, 17, 1, 'GURUPI', 50),
(58, 17, 2, 'GURUPI', 60),
(59, 17, 3, 'GURUPI', 70),
(60, 18, 1, 'GURUPI', 50),
(61, 18, 2, 'GURUPI', 60),
(62, 18, 3, 'GURUPI', 70),
(63, 19, 1, 'GURUPI', 30),
(64, 19, 2, 'GURUPI', 35),
(65, 19, 3, 'GURUPI', 40),
(66, 20, 1, 'GURUPI', 40),
(67, 20, 2, 'GURUPI', 45),
(68, 20, 3, 'GURUPI', 50),
(69, 21, 1, 'GURUPI', 80),
(70, 21, 2, 'GURUPI', 85),
(71, 21, 3, 'GURUPI', 90),
(72, 22, 1, 'GURUPI', 25),
(73, 22, 2, 'GURUPI', 30),
(74, 22, 3, 'GURUPI', 35),
(75, 23, 1, 'GURUPI', 30),
(76, 23, 2, 'GURUPI', 35),
(77, 23, 3, 'GURUPI', 40),
(78, 24, 1, 'GURUPI', 30),
(79, 24, 2, 'GURUPI', 35),
(80, 24, 3, 'GURUPI', 40),
(81, 25, 1, 'GURUPI', 30),
(82, 25, 2, 'GURUPI', 35),
(83, 25, 3, 'GURUPI', 40),
(84, 26, 1, 'GURUPI', 30),
(85, 26, 2, 'GURUPI', 35),
(86, 26, 3, 'GURUPI', 40),
(87, 28, 1, 'GURUPI', 40),
(88, 28, 2, 'GURUPI', 50),
(89, 28, 3, 'GURUPI', 60),
(90, 29, 1, 'GURUPI', 40),
(91, 29, 2, 'GURUPI', 50),
(92, 29, 3, 'GURUPI', 60),
(93, 30, 1, 'GURUPI', 40),
(94, 30, 2, 'GURUPI', 50),
(95, 30, 3, 'GURUPI', 60),
(96, 35, 1, 'GURUPI', 50),
(97, 36, 1, 'GURUPI', 50),
(98, 35, 2, 'GURUPI', 50),
(99, 35, 3, 'GURUPI', 50),
(100, 36, 2, 'GURUPI', 50),
(101, 36, 3, 'GURUPI', 50),
(102, 37, 1, 'GURUPI', 50),
(103, 37, 2, 'GURUPI', 50),
(104, 37, 3, 'GURUPI', 50),
(105, 41, 1, 'GURUPI', 40),
(106, 41, 2, 'GURUPI', 50),
(107, 41, 3, 'GURUPI', 60),
(108, 44, 1, 'GURUPI', 25),
(109, 44, 2, 'GURUPI', 30),
(110, 44, 3, 'GURUPI', 35),
(111, 45, 1, 'GURUPI', 30),
(112, 45, 2, 'GURUPI', 40),
(113, 45, 3, 'GURUPI', 50),
(114, 46, 1, 'GURUPI', 35),
(115, 46, 2, 'GURUPI', 45),
(116, 46, 3, 'GURUPI', 55),
(117, 47, 1, 'GURUPI', 40),
(118, 47, 2, 'GURUPI', 50),
(119, 47, 3, 'GURUPI', 60),
(120, 48, 1, 'GURUPI', 25),
(121, 48, 2, 'GURUPI', 30),
(122, 48, 3, 'GURUPI', 35);

-- --------------------------------------------------------

--
-- Estrutura para tabela `alertas_processo`
--

DROP TABLE IF EXISTS `alertas_processo`;
CREATE TABLE IF NOT EXISTS `alertas_processo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `processo_id` int NOT NULL,
  `descricao` text NOT NULL,
  `prazo` date NOT NULL,
  `status` enum('ativo','finalizado') NOT NULL DEFAULT 'ativo',
  PRIMARY KEY (`id`),
  KEY `processo_id` (`processo_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `alertas_processo`
--

INSERT INTO `alertas_processo` (`id`, `processo_id`, `descricao`, `prazo`, `status`) VALUES
(2, 1, 'teste', '2024-06-13', 'finalizado'),
(8, 17, 'Verificar Documentação DO eSTABELECIMENTO', '2024-06-28', 'finalizado'),
(17, 38, 'testando', '2024-07-08', 'finalizado'),
(18, 53, 'Empresa deverá apresentar off line', '2024-07-15', 'finalizado');

-- --------------------------------------------------------

--
-- Estrutura para tabela `arquivos`
--

DROP TABLE IF EXISTS `arquivos`;
CREATE TABLE IF NOT EXISTS `arquivos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `processo_id` int NOT NULL,
  `tipo_documento` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `caminho_arquivo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_upload` datetime NOT NULL,
  `codigo_verificador` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero_arquivo` int NOT NULL,
  `conteudo` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('rascunho','finalizado','assinado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'rascunho',
  `sigiloso` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `processo_id` (`processo_id`)
) ENGINE=InnoDB AUTO_INCREMENT=741 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `arquivos`
--

INSERT INTO `arquivos` (`id`, `processo_id`, `tipo_documento`, `caminho_arquivo`, `data_upload`, `codigo_verificador`, `numero_arquivo`, `conteudo`, `status`, `sigiloso`) VALUES
(740, 53, 'DESPACHO', 'uploads/DESPACHO_740_2024.pdf', '2024-07-11 23:19:01', '5eeb64c4f131c6829f4a6ae37411084e', 740, '<p>verificar em coloco o estabelecimento</p>', 'assinado', 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `assinaturas`
--

DROP TABLE IF EXISTS `assinaturas`;
CREATE TABLE IF NOT EXISTS `assinaturas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `arquivo_id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `data_assinatura` datetime NOT NULL,
  `status` enum('pendente','assinado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente',
  PRIMARY KEY (`id`),
  KEY `arquivo_id` (`arquivo_id`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1053 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `assinaturas`
--

INSERT INTO `assinaturas` (`id`, `arquivo_id`, `usuario_id`, `data_assinatura`, `status`) VALUES
(1006, 702, 40, '2024-07-08 02:18:16', 'assinado'),
(1007, 703, 40, '2024-07-08 02:19:46', 'assinado'),
(1008, 704, 40, '2024-07-08 02:20:06', 'assinado'),
(1009, 705, 40, '2024-07-08 02:20:20', 'assinado'),
(1010, 706, 40, '2024-07-08 02:21:13', 'assinado'),
(1011, 707, 40, '2024-07-08 02:28:23', 'assinado'),
(1012, 708, 40, '2024-07-08 02:30:44', 'pendente'),
(1014, 709, 40, '2024-07-08 02:32:50', 'assinado'),
(1015, 710, 40, '2024-07-08 02:33:11', 'pendente'),
(1016, 711, 40, '2024-07-08 02:35:10', 'assinado'),
(1017, 712, 40, '2024-07-08 02:35:32', 'assinado'),
(1018, 713, 40, '2024-07-08 02:36:07', 'assinado'),
(1020, 714, 40, '2024-07-08 02:36:40', 'assinado'),
(1022, 715, 40, '2024-07-08 02:37:12', 'assinado'),
(1023, 716, 14, '2024-07-08 14:22:51', 'assinado'),
(1024, 717, 14, '2024-07-08 15:06:47', 'assinado'),
(1025, 718, 14, '2024-07-08 15:08:11', 'assinado'),
(1026, 719, 15, '2024-07-08 15:14:59', 'assinado'),
(1027, 720, 15, '2024-07-08 15:21:25', 'assinado'),
(1030, 721, 15, '2024-07-08 15:22:03', 'assinado'),
(1031, 722, 15, '2024-07-08 15:22:17', 'assinado'),
(1032, 723, 15, '2024-07-08 15:29:12', 'pendente'),
(1033, 724, 15, '2024-07-08 15:30:02', 'assinado'),
(1034, 725, 15, '2024-07-08 15:32:40', 'pendente'),
(1035, 727, 15, '2024-07-08 19:59:43', 'pendente'),
(1037, 728, 15, '2024-07-08 20:00:09', 'pendente'),
(1039, 731, 14, '2024-07-08 20:16:39', 'assinado'),
(1040, 732, 14, '2024-07-08 20:17:11', 'assinado'),
(1044, 735, 15, '2024-07-08 20:27:55', 'assinado'),
(1045, 736, 15, '2024-07-08 20:31:02', 'assinado'),
(1047, 736, 14, '2024-07-08 20:31:53', 'assinado'),
(1048, 736, 40, '2024-07-08 20:33:16', 'assinado'),
(1049, 737, 42, '2024-07-11 22:47:31', 'assinado'),
(1050, 738, 42, '2024-07-11 22:48:09', 'assinado'),
(1051, 739, 42, '2024-07-11 22:52:09', 'assinado'),
(1052, 740, 42, '2024-07-11 23:19:05', 'assinado');

-- --------------------------------------------------------

--
-- Estrutura para tabela `atividade_grupo_risco`
--

DROP TABLE IF EXISTS `atividade_grupo_risco`;
CREATE TABLE IF NOT EXISTS `atividade_grupo_risco` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cnae` varchar(20) NOT NULL,
  `grupo_risco_id` int NOT NULL,
  `municipio` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_cnae_grupo` (`cnae`,`grupo_risco_id`,`municipio`),
  KEY `fk_grupo_risco_atividade` (`grupo_risco_id`)
) ENGINE=InnoDB AUTO_INCREMENT=317 DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `atividade_grupo_risco`
--

INSERT INTO `atividade_grupo_risco` (`id`, `cnae`, `grupo_risco_id`, `municipio`) VALUES
(304, '0162801', 3, 'GURUPI'),
(277, '1061901', 3, 'GURUPI'),
(237, '1063500', 2, 'GURUPI'),
(315, '1081302', 3, 'GURUPI'),
(238, '1091102', 2, 'GURUPI'),
(297, '1092900', 3, 'GURUPI'),
(300, '1094500', 3, 'GURUPI'),
(298, '1095300', 3, 'GURUPI'),
(296, '1096100', 3, 'GURUPI'),
(299, '1099604', 3, 'GURUPI'),
(260, '3250706', 2, 'GURUPI'),
(309, '3250709', 3, 'GURUPI'),
(294, '3600602', 3, 'GURUPI'),
(189, '3811400', 2, 'GURUPI'),
(258, '4520002', 2, 'GURUPI'),
(259, '4520005', 2, 'GURUPI'),
(255, '4520006', 2, 'GURUPI'),
(249, '4611700', 2, 'GURUPI'),
(251, '4617600', 2, 'GURUPI'),
(250, '4618401', 2, 'GURUPI'),
(248, '4618402', 2, 'GURUPI'),
(203, '4622200', 2, 'GURUPI'),
(153, '4623109', 1, 'GURUPI'),
(168, '4631100', 2, 'GURUPI'),
(198, '4632001', 2, 'GURUPI'),
(164, '4632002', 2, 'GURUPI'),
(279, '4632003', 3, 'GURUPI'),
(165, '4633801', 2, 'GURUPI'),
(192, '4633802', 2, 'GURUPI'),
(196, '4634601', 2, 'GURUPI'),
(154, '4634602', 1, 'GURUPI'),
(197, '4634699', 2, 'GURUPI'),
(191, '4635401', 2, 'GURUPI'),
(199, '4635402', 2, 'GURUPI'),
(193, '4635403', 2, 'GURUPI'),
(194, '4635499', 2, 'GURUPI'),
(195, '4637101', 2, 'GURUPI'),
(190, '4637102', 2, 'GURUPI'),
(175, '4637104', 2, 'GURUPI'),
(170, '4637105', 2, 'GURUPI'),
(204, '4637106', 2, 'GURUPI'),
(200, '4637107', 2, 'GURUPI'),
(176, '4639701', 2, 'GURUPI'),
(177, '4639702', 2, 'GURUPI'),
(287, '4639702', 3, 'GURUPI'),
(171, '4644301', 2, 'GURUPI'),
(284, '4644301', 3, 'GURUPI'),
(172, '4644302', 2, 'GURUPI'),
(285, '4644302', 3, 'GURUPI'),
(167, '4645101', 2, 'GURUPI'),
(282, '4645101', 3, 'GURUPI'),
(182, '4645102', 2, 'GURUPI'),
(291, '4645102', 3, 'GURUPI'),
(181, '4645103', 2, 'GURUPI'),
(290, '4645103', 3, 'GURUPI'),
(280, '4646001', 3, 'GURUPI'),
(178, '4646002', 2, 'GURUPI'),
(179, '4649408', 2, 'GURUPI'),
(288, '4649408', 3, 'GURUPI'),
(180, '4649409', 2, 'GURUPI'),
(289, '4649409', 3, 'GURUPI'),
(169, '4664800', 2, 'GURUPI'),
(283, '4664800', 3, 'GURUPI'),
(205, '4679601', 2, 'GURUPI'),
(166, '4682600', 2, 'GURUPI'),
(281, '4683400', 3, 'GURUPI'),
(202, '4687702', 2, 'GURUPI'),
(155, '4687703', 1, 'GURUPI'),
(183, '4687703', 2, 'GURUPI'),
(174, '4691500', 2, 'GURUPI'),
(173, '4692300', 2, 'GURUPI'),
(286, '4692300', 3, 'GURUPI'),
(216, '4711301', 2, 'GURUPI'),
(218, '4711302', 2, 'GURUPI'),
(217, '4712100', 2, 'GURUPI'),
(245, '4721102', 2, 'GURUPI'),
(214, '4721103', 2, 'GURUPI'),
(209, '4722901', 2, 'GURUPI'),
(246, '4722902', 2, 'GURUPI'),
(208, '4723700', 2, 'GURUPI'),
(213, '4724500', 2, 'GURUPI'),
(314, '4729601', 3, 'GURUPI'),
(219, '4729602', 2, 'GURUPI'),
(220, '4729699', 2, 'GURUPI'),
(210, '4731800', 2, 'GURUPI'),
(162, '4751202', 1, 'GURUPI'),
(293, '4771701', 3, 'GURUPI'),
(215, '4771704', 2, 'GURUPI'),
(211, '4772500', 2, 'GURUPI'),
(207, '4773300', 2, 'GURUPI'),
(206, '4774100', 2, 'GURUPI'),
(212, '4784900', 2, 'GURUPI'),
(156, '4789002', 1, 'GURUPI'),
(292, '4789004', 3, 'GURUPI'),
(221, '4789005', 2, 'GURUPI'),
(266, '4921301', 2, 'GURUPI'),
(264, '4921302', 2, 'GURUPI'),
(265, '4922101', 2, 'GURUPI'),
(263, '4922102', 2, 'GURUPI'),
(262, '4924800', 2, 'GURUPI'),
(268, '4929901', 2, 'GURUPI'),
(267, '4929902', 2, 'GURUPI'),
(270, '4930201', 2, 'GURUPI'),
(269, '4930202', 2, 'GURUPI'),
(271, '4930204', 2, 'GURUPI'),
(272, '5211701', 3, 'GURUPI'),
(163, '5222200', 1, 'GURUPI'),
(256, '5320202', 2, 'GURUPI'),
(240, '5510801', 2, 'GURUPI'),
(244, '5510803', 2, 'GURUPI'),
(247, '5590603', 2, 'GURUPI'),
(252, '5611201', 2, 'GURUPI'),
(241, '5611203', 2, 'GURUPI'),
(253, '5612100', 2, 'GURUPI'),
(254, '5620102', 2, 'GURUPI'),
(185, '5620103', 2, 'GURUPI'),
(239, '5620104', 2, 'GURUPI'),
(149, '5914600', 1, 'GURUPI'),
(201, '632002', 2, 'GURUPI'),
(151, '6421200', 1, 'GURUPI'),
(152, '6424701', 1, 'GURUPI'),
(160, '6511102', 1, 'GURUPI'),
(161, '6550200', 1, 'GURUPI'),
(276, '7500100', 3, 'GURUPI'),
(243, '8121400', 2, 'GURUPI'),
(159, '8219901', 1, 'GURUPI'),
(186, '8230002', 2, 'GURUPI'),
(295, '8511200', 3, 'GURUPI'),
(224, '8512100', 2, 'GURUPI'),
(235, '8513900', 2, 'GURUPI'),
(236, '8520100', 2, 'GURUPI'),
(227, '8531700', 2, 'GURUPI'),
(228, '8532500', 2, 'GURUPI'),
(229, '8533300', 2, 'GURUPI'),
(225, '8541400', 2, 'GURUPI'),
(226, '8542200', 2, 'GURUPI'),
(232, '8591100', 2, 'GURUPI'),
(231, '8592901', 2, 'GURUPI'),
(234, '8592903', 2, 'GURUPI'),
(230, '8592999', 2, 'GURUPI'),
(233, '8593700', 2, 'GURUPI'),
(158, '8599601', 1, 'GURUPI'),
(157, '8599602', 1, 'GURUPI'),
(222, '8599605', 2, 'GURUPI'),
(316, '8621601', 3, 'GURUPI'),
(313, '8621602', 3, 'GURUPI'),
(261, '8622400', 2, 'GURUPI'),
(273, '8630504', 3, 'GURUPI'),
(312, '8630506', 3, 'GURUPI'),
(302, '8640202', 3, 'GURUPI'),
(305, '8640205', 3, 'GURUPI'),
(306, '8640207', 3, 'GURUPI'),
(308, '8640208', 3, 'GURUPI'),
(307, '8640209', 3, 'GURUPI'),
(278, '8711501', 3, 'GURUPI'),
(301, '8711502', 3, 'GURUPI'),
(187, '8711504', 2, 'GURUPI'),
(275, '8712300', 3, 'GURUPI'),
(274, '8720499', 3, 'GURUPI'),
(303, '8730101', 3, 'GURUPI'),
(188, '9312300', 2, 'GURUPI'),
(223, '9329801', 2, 'GURUPI'),
(150, '9491000', 1, 'GURUPI'),
(242, '9601701', 2, 'GURUPI'),
(184, '9602501', 2, 'GURUPI'),
(257, '9603304', 2, 'GURUPI'),
(310, '9603305', 3, 'GURUPI'),
(148, '9609203', 1, 'GURUPI'),
(311, '9609206', 3, 'GURUPI');

-- --------------------------------------------------------

--
-- Estrutura para tabela `configuracoes_apis`
--

DROP TABLE IF EXISTS `configuracoes_apis`;
CREATE TABLE IF NOT EXISTS `configuracoes_apis` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome_api` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `chave_api` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome_api` (`nome_api`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `configuracoes_apis`
--

INSERT INTO `configuracoes_apis` (`id`, `nome_api`, `chave_api`) VALUES
(8, 'tiny', '69b8u77ejqxv4frd73vojaa3t1a5zqwp0kk9wwripdg1azzi'),
(11, 'google_maps', 'AIzaSyDPWiU5cGfgzr5owzmdYcPBpoNSIF-V5KU');

-- --------------------------------------------------------

--
-- Estrutura para tabela `documentos`
--

DROP TABLE IF EXISTS `documentos`;
CREATE TABLE IF NOT EXISTS `documentos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `processo_id` int NOT NULL,
  `nome_arquivo` varchar(255) NOT NULL,
  `caminho_arquivo` varchar(255) NOT NULL,
  `data_upload` datetime NOT NULL,
  `motivo_negacao` text,
  `status` enum('pendente','aprovado','negado') DEFAULT 'pendente',
  PRIMARY KEY (`id`),
  KEY `processo_id` (`processo_id`)
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `documentos`
--

INSERT INTO `documentos` (`id`, `processo_id`, `nome_arquivo`, `caminho_arquivo`, `data_upload`, `motivo_negacao`, `status`) VALUES
(61, 32, 'ACO329~1.PDF', 'uploads/ACO329~1.PDF', '2024-07-04 23:09:31', NULL, 'aprovado'),
(89, 53, 'CNPJ.pdf', 'uploads/CNPJ.pdf', '2024-07-11 21:27:03', NULL, 'aprovado'),
(90, 53, 'DECLARAÇÃO DE PRODUÇAO.pdf', 'uploads/DECLARAÇÃO DE PRODUÇAO.pdf', '2024-07-11 21:27:03', NULL, 'aprovado'),
(91, 53, 'DOCUMENTO RL.pdf', 'uploads/DOCUMENTO RL.pdf', '2024-07-11 21:27:03', NULL, 'aprovado'),
(93, 53, 'ALTERACAO CONTRATUAL AUTENTICADA.pdf', 'uploads/ALTERACAO CONTRATUAL AUTENTICADA(1).pdf', '2024-07-11 21:39:56', NULL, 'aprovado');

-- --------------------------------------------------------

--
-- Estrutura para tabela `estabelecimentos`
--

DROP TABLE IF EXISTS `estabelecimentos`;
CREATE TABLE IF NOT EXISTS `estabelecimentos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_externo_id` int DEFAULT NULL,
  `cnpj` varchar(20) NOT NULL,
  `descricao_identificador_matriz_filial` varchar(255) DEFAULT NULL,
  `nome_fantasia` varchar(255) DEFAULT NULL,
  `descricao_situacao_cadastral` varchar(255) DEFAULT NULL,
  `data_situacao_cadastral` date DEFAULT NULL,
  `data_inicio_atividade` date DEFAULT NULL,
  `cnae_fiscal` varchar(20) DEFAULT NULL,
  `cnae_fiscal_descricao` varchar(255) DEFAULT NULL,
  `descricao_tipo_de_logradouro` varchar(255) DEFAULT NULL,
  `logradouro` varchar(255) DEFAULT NULL,
  `numero` varchar(20) DEFAULT NULL,
  `complemento` varchar(255) DEFAULT NULL,
  `bairro` varchar(255) DEFAULT NULL,
  `cep` varchar(20) DEFAULT NULL,
  `uf` varchar(2) DEFAULT NULL,
  `municipio` varchar(255) DEFAULT NULL,
  `ddd_telefone_1` varchar(20) DEFAULT NULL,
  `ddd_telefone_2` varchar(20) DEFAULT NULL,
  `razao_social` varchar(255) DEFAULT NULL,
  `natureza_juridica` varchar(255) DEFAULT NULL,
  `qsa` text,
  `cnaes_secundarios` text,
  `nome_socio_1` varchar(255) DEFAULT NULL,
  `qualificacao_socio_1` varchar(255) DEFAULT NULL,
  `nome_socio_2` varchar(255) DEFAULT NULL,
  `qualificacao_socio_2` varchar(255) DEFAULT NULL,
  `nome_socio_3` varchar(255) DEFAULT NULL,
  `qualificacao_socio_3` varchar(255) DEFAULT NULL,
  `status` enum('pendente','aprovado','rejeitado') DEFAULT 'pendente',
  `motivo_negacao` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `estabelecimentos`
--

INSERT INTO `estabelecimentos` (`id`, `usuario_externo_id`, `cnpj`, `descricao_identificador_matriz_filial`, `nome_fantasia`, `descricao_situacao_cadastral`, `data_situacao_cadastral`, `data_inicio_atividade`, `cnae_fiscal`, `cnae_fiscal_descricao`, `descricao_tipo_de_logradouro`, `logradouro`, `numero`, `complemento`, `bairro`, `cep`, `uf`, `municipio`, `ddd_telefone_1`, `ddd_telefone_2`, `razao_social`, `natureza_juridica`, `qsa`, `cnaes_secundarios`, `nome_socio_1`, `qualificacao_socio_1`, `nome_socio_2`, `qualificacao_socio_2`, `nome_socio_3`, `qualificacao_socio_3`, `status`, `motivo_negacao`) VALUES
(2, 3, '07.546.521/0004-30', 'FILIAL', 'S.A. ALIMENTOS', 'ATIVA', '2018-08-08', '2018-08-08', '4632002', 'Comércio atacadista de farinhas, amidos e féculas', 'AVENIDA', 'GOIAS', '872', 'QUADRACHACARA LOTE 41R GALPAO02', 'SETOR CENTRAL', '77410010', 'TO', 'GURUPI', '6230866363', 'Não Informado', 'STA - DISTRIBUIDORA DE ALIMENTOS LTDA', 'Sociedade Empresária Limitada', '[{\"pais\":null,\"nome_socio\":\"JOAO CARLOS DIVINO FELICIANO\",\"codigo_pais\":null,\"faixa_etaria\":\"Entre 41 a 50 anos\",\"cnpj_cpf_do_socio\":\"***454201**\",\"qualificacao_socio\":\"S\\u00f3cio-Administrador\",\"codigo_faixa_etaria\":5,\"data_entrada_sociedade\":\"2011-08-26\",\"identificador_de_socio\":2,\"cpf_representante_legal\":\"***000000**\",\"nome_representante_legal\":\"\",\"codigo_qualificacao_socio\":49,\"qualificacao_representante_legal\":\"N\\u00e3o informada\",\"codigo_qualificacao_representante_legal\":0},{\"pais\":null,\"nome_socio\":\"JOSE DIVINO CARLOS FELICIANO\",\"codigo_pais\":null,\"faixa_etaria\":\"Entre 51 a 60 anos\",\"cnpj_cpf_do_socio\":\"***523651**\",\"qualificacao_socio\":\"S\\u00f3cio-Administrador\",\"codigo_faixa_etaria\":6,\"data_entrada_sociedade\":\"2011-08-26\",\"identificador_de_socio\":2,\"cpf_representante_legal\":\"***000000**\",\"nome_representante_legal\":\"\",\"codigo_qualificacao_socio\":49,\"qualificacao_representante_legal\":\"N\\u00e3o informada\",\"codigo_qualificacao_representante_legal\":0}]', '[{\"codigo\":4632003,\"descricao\":\"Com\\u00e9rcio atacadista de cereais e leguminosas beneficiados, farinhas, amidos e f\\u00e9culas, com atividade de fracionamento e acondicionamento associada\"},{\"codigo\":4646001,\"descricao\":\"Com\\u00e9rcio atacadista de cosm\\u00e9ticos e produtos de perfumaria\"}]', 'JOAO CARLOS DIVINO FELICIANO', 'Sócio-Administrador', 'JOSE DIVINO CARLOS FELICIANO', 'Sócio-Administrador', NULL, NULL, 'aprovado', NULL),
(10, NULL, '54.780.219/0001-80', NULL, 'DROGA.SUA', 'ATIVA', '2024-04-17', '2024-04-17', '4771701', 'Comércio varejista de produtos farmacêuticos, sem manipulação de fórmulas', 'AVENIDA', 'GOIÁS', '3401', 'SALA 10 E 11 LOTE CHACARA 96', 'ZONA URBANA', '77410010', 'TO', 'GURUPI', '6332158825', '000000000000', 'DROGA.SUA LTDA', 'Sociedade Empresária Limitada', '[{\"pais\":null,\"nome_socio\":\"JANE MARITA DE JESUS\",\"codigo_pais\":null,\"faixa_etaria\":\"Entre 61 a 70 anos\",\"cnpj_cpf_do_socio\":\"***537641**\",\"qualificacao_socio\":\"S\\u00f3cio-Administrador\",\"codigo_faixa_etaria\":7,\"data_entrada_sociedade\":\"2024-04-17\",\"identificador_de_socio\":2,\"cpf_representante_legal\":\"***000000**\",\"nome_representante_legal\":\"\",\"codigo_qualificacao_socio\":49,\"qualificacao_representante_legal\":\"N\\u00e3o informada\",\"codigo_qualificacao_representante_legal\":0}]', '[{\"codigo\":4729602,\"descricao\":\"Com\\u00e9rcio varejista de mercadorias em lojas de conveni\\u00eancia\"},{\"codigo\":4772500,\"descricao\":\"Com\\u00e9rcio varejista de cosm\\u00e9ticos, produtos de perfumaria e de higiene pessoal\"},{\"codigo\":4789005,\"descricao\":\"Com\\u00e9rcio varejista de produtos saneantes domissanit\\u00e1rios\"}]', 'JANE MARITA DE JESUS', 'Sócio-Administrador', NULL, NULL, NULL, NULL, 'aprovado', NULL),
(12, NULL, '37.029.217/0001-34', NULL, 'DUOCOR - CENTRO CLINICO E DIAGNOSTICO EM CARDIOLOGIA', 'ATIVA', '2020-04-29', '2020-04-29', '8640207', 'Serviços de diagnóstico por imagem sem uso de radiação ionizante, exceto ressonância magnética', 'AVENIDA', 'PARAIBA', '2055', 'QUADRA96L3A ENTRE RUA 7E8', 'SETOR CENTRAL', '77410060', 'TO', 'GURUPI', '3432396565', 'Não Informado', 'DUOCOR - CENTRO CLINICO E DIAGNOSTICO EM CARDIOLOGIA LTDA', 'Sociedade Empresária Limitada', '[{\"pais\":null,\"nome_socio\":\"ROBERTO MARIO ARRUDA VERZOLA\",\"codigo_pais\":null,\"faixa_etaria\":\"Entre 41 a 50 anos\",\"cnpj_cpf_do_socio\":\"***447538**\",\"qualificacao_socio\":\"S\\u00f3cio\",\"codigo_faixa_etaria\":5,\"data_entrada_sociedade\":\"2020-04-29\",\"identificador_de_socio\":2,\"cpf_representante_legal\":\"***000000**\",\"nome_representante_legal\":\"\",\"codigo_qualificacao_socio\":22,\"qualificacao_representante_legal\":\"N\\u00e3o informada\",\"codigo_qualificacao_representante_legal\":0},{\"pais\":null,\"nome_socio\":\"LORENA MARQUES FREITAS VERZOLA\",\"codigo_pais\":null,\"faixa_etaria\":\"Entre 31 a 40 anos\",\"cnpj_cpf_do_socio\":\"***490246**\",\"qualificacao_socio\":\"S\\u00f3cio\",\"codigo_faixa_etaria\":4,\"data_entrada_sociedade\":\"2020-04-29\",\"identificador_de_socio\":2,\"cpf_representante_legal\":\"***000000**\",\"nome_representante_legal\":\"\",\"codigo_qualificacao_socio\":22,\"qualificacao_representante_legal\":\"N\\u00e3o informada\",\"codigo_qualificacao_representante_legal\":0},{\"pais\":null,\"nome_socio\":\"MARIO HENRIQUE ARRUDA VERZOLA\",\"codigo_pais\":null,\"faixa_etaria\":\"Entre 41 a 50 anos\",\"cnpj_cpf_do_socio\":\"***153018**\",\"qualificacao_socio\":\"Administrador\",\"codigo_faixa_etaria\":5,\"data_entrada_sociedade\":\"2022-08-23\",\"identificador_de_socio\":2,\"cpf_representante_legal\":\"***000000**\",\"nome_representante_legal\":\"\",\"codigo_qualificacao_socio\":5,\"qualificacao_representante_legal\":\"N\\u00e3o informada\",\"codigo_qualificacao_representante_legal\":0}]', '[{\"codigo\":8630502,\"descricao\":\"Atividade m\\u00e9dica ambulatorial com recursos para realiza\\u00e7\\u00e3o de exames complementares\"},{\"codigo\":8630503,\"descricao\":\"Atividade m\\u00e9dica ambulatorial restrita a consultas\"},{\"codigo\":8640202,\"descricao\":\"Laborat\\u00f3rios cl\\u00ednicos\"},{\"codigo\":8640208,\"descricao\":\"Servi\\u00e7os de diagn\\u00f3stico por registro gr\\u00e1fico - ECG, EEG e outros exames an\\u00e1logos\"},{\"codigo\":8640299,\"descricao\":\"Atividades de servi\\u00e7os de complementa\\u00e7\\u00e3o diagn\\u00f3stica e terap\\u00eautica n\\u00e3o especificadas anteriormente\"}]', 'ROBERTO MARIO ARRUDA VERZOLA', 'Sócio', 'LORENA MARQUES FREITAS VERZOLA', 'Sócio', 'MARIO HENRIQUE ARRUDA VERZOLA', 'Administrador', 'aprovado', NULL),
(26, NULL, '27.361.595/0001-67', 'MATRIZ', 'CASA DE CARNE ALVORADA', 'ATIVA', '2017-03-22', '2017-03-22', '4634601', 'Comércio atacadista de carnes bovinas e suínas e derivados', 'AVENIDA', 'CASTELO BRANCO', '280', 'Não Informado', 'SETOR CENTRAL', '77805110', 'TO', 'ARAGUAINA', '6334131814', 'Não Informado', 'B P S PIMENTEL', 'Empresário (Individual)', '[]', '[{\"codigo\":4711302,\"descricao\":\"Com\\u00e9rcio varejista de mercadorias em geral, com predomin\\u00e2ncia de produtos aliment\\u00edcios - supermercados\"},{\"codigo\":4722901,\"descricao\":\"Com\\u00e9rcio varejista de carnes - a\\u00e7ougues\"}]', NULL, NULL, NULL, NULL, NULL, NULL, 'aprovado', NULL),
(29, 1, '02.329.995/0001-64', 'MATRIZ', 'HOSPITAL DE OLHOS DE PALMAS', 'ATIVA', '2005-11-03', '1998-01-01', '8650099', 'Atividades de profissionais da área de saúde não especificadas anteriormente', 'QUADRA', '402 SUL AVENIDA JOAQUIM TEOTONIO SEGURADO', 'S/N', 'CONJ 01 LOTE 02', 'PLANO DIRETOR SUL', '77021622', 'TO', 'PALMAS', '6332199734', 'Não Informado', 'HOSPITAL DE OLHOS DE PALMAS LTDA', 'Sociedade Empresária Limitada', '[{\"pais\":null,\"nome_socio\":\"ANA BEATRIZ DIAS\",\"codigo_pais\":null,\"faixa_etaria\":\"Entre 51 a 60 anos\",\"cnpj_cpf_do_socio\":\"***537191**\",\"qualificacao_socio\":\"S\\u00f3cio\",\"codigo_faixa_etaria\":6,\"data_entrada_sociedade\":\"1998-01-01\",\"identificador_de_socio\":2,\"cpf_representante_legal\":\"***000000**\",\"nome_representante_legal\":\"\",\"codigo_qualificacao_socio\":22,\"qualificacao_representante_legal\":\"N\\u00e3o informada\",\"codigo_qualificacao_representante_legal\":0},{\"pais\":null,\"nome_socio\":\"TULIO CESAR DE OLIVEIRA\",\"codigo_pais\":null,\"faixa_etaria\":\"Entre 61 a 70 anos\",\"cnpj_cpf_do_socio\":\"***056981**\",\"qualificacao_socio\":\"S\\u00f3cio-Administrador\",\"codigo_faixa_etaria\":7,\"data_entrada_sociedade\":\"1998-01-01\",\"identificador_de_socio\":2,\"cpf_representante_legal\":\"***000000**\",\"nome_representante_legal\":\"\",\"codigo_qualificacao_socio\":49,\"qualificacao_representante_legal\":\"N\\u00e3o informada\",\"codigo_qualificacao_representante_legal\":0},{\"pais\":null,\"nome_socio\":\"NUBIA CRISTINA DE FREITAS MAIA\",\"codigo_pais\":null,\"faixa_etaria\":\"Entre 51 a 60 anos\",\"cnpj_cpf_do_socio\":\"***120676**\",\"qualificacao_socio\":\"S\\u00f3cio\",\"codigo_faixa_etaria\":6,\"data_entrada_sociedade\":\"2009-03-13\",\"identificador_de_socio\":2,\"cpf_representante_legal\":\"***000000**\",\"nome_representante_legal\":\"\",\"codigo_qualificacao_socio\":22,\"qualificacao_representante_legal\":\"N\\u00e3o informada\",\"codigo_qualificacao_representante_legal\":0},{\"pais\":null,\"nome_socio\":\"CRISTIANO LEITES FLAMIA\",\"codigo_pais\":null,\"faixa_etaria\":\"Entre 41 a 50 anos\",\"cnpj_cpf_do_socio\":\"***367910**\",\"qualificacao_socio\":\"S\\u00f3cio\",\"codigo_faixa_etaria\":5,\"data_entrada_sociedade\":\"2009-03-13\",\"identificador_de_socio\":2,\"cpf_representante_legal\":\"***000000**\",\"nome_representante_legal\":\"\",\"codigo_qualificacao_socio\":22,\"qualificacao_representante_legal\":\"N\\u00e3o informada\",\"codigo_qualificacao_representante_legal\":0},{\"pais\":null,\"nome_socio\":\"GUSTAVO HERMANO LAGE\",\"codigo_pais\":null,\"faixa_etaria\":\"Entre 41 a 50 anos\",\"cnpj_cpf_do_socio\":\"***508311**\",\"qualificacao_socio\":\"S\\u00f3cio\",\"codigo_faixa_etaria\":5,\"data_entrada_sociedade\":\"2009-03-13\",\"identificador_de_socio\":2,\"cpf_representante_legal\":\"***000000**\",\"nome_representante_legal\":\"\",\"codigo_qualificacao_socio\":22,\"qualificacao_representante_legal\":\"N\\u00e3o informada\",\"codigo_qualificacao_representante_legal\":0},{\"pais\":null,\"nome_socio\":\"ONILSON BATISTA DA SILVA\",\"codigo_pais\":null,\"faixa_etaria\":\"Entre 51 a 60 anos\",\"cnpj_cpf_do_socio\":\"***155906**\",\"qualificacao_socio\":\"S\\u00f3cio\",\"codigo_faixa_etaria\":6,\"data_entrada_sociedade\":\"2009-03-13\",\"identificador_de_socio\":2,\"cpf_representante_legal\":\"***000000**\",\"nome_representante_legal\":\"\",\"codigo_qualificacao_socio\":22,\"qualificacao_representante_legal\":\"N\\u00e3o informada\",\"codigo_qualificacao_representante_legal\":0},{\"pais\":null,\"nome_socio\":\"AVILAGES PARTICIPACOES E EMPREENDIMENTOS LTDA\",\"codigo_pais\":null,\"faixa_etaria\":\"N\\u00e3o se aplica\",\"cnpj_cpf_do_socio\":\"26883744000195\",\"qualificacao_socio\":\"S\\u00f3cio\",\"codigo_faixa_etaria\":0,\"data_entrada_sociedade\":\"2009-03-13\",\"identificador_de_socio\":1,\"cpf_representante_legal\":\"***300877**\",\"nome_representante_legal\":\"MARCOS PEREIRA DE AVILA\",\"codigo_qualificacao_socio\":22,\"qualificacao_representante_legal\":\"Administrador\",\"codigo_qualificacao_representante_legal\":5},{\"pais\":null,\"nome_socio\":\"MARCO TULIO CHATER VIEGAS\",\"codigo_pais\":null,\"faixa_etaria\":\"Entre 41 a 50 anos\",\"cnpj_cpf_do_socio\":\"***835877**\",\"qualificacao_socio\":\"S\\u00f3cio\",\"codigo_faixa_etaria\":5,\"data_entrada_sociedade\":\"2013-05-22\",\"identificador_de_socio\":2,\"cpf_representante_legal\":\"***000000**\",\"nome_representante_legal\":\"\",\"codigo_qualificacao_socio\":22,\"qualificacao_representante_legal\":\"N\\u00e3o informada\",\"codigo_qualificacao_representante_legal\":0},{\"pais\":null,\"nome_socio\":\"FABIANA RICHA VALIM CHATER VIEGAS\",\"codigo_pais\":null,\"faixa_etaria\":\"Entre 41 a 50 anos\",\"cnpj_cpf_do_socio\":\"***606901**\",\"qualificacao_socio\":\"S\\u00f3cio\",\"codigo_faixa_etaria\":5,\"data_entrada_sociedade\":\"2013-05-22\",\"identificador_de_socio\":2,\"cpf_representante_legal\":\"***000000**\",\"nome_representante_legal\":\"\",\"codigo_qualificacao_socio\":22,\"qualificacao_representante_legal\":\"N\\u00e3o informada\",\"codigo_qualificacao_representante_legal\":0},{\"pais\":null,\"nome_socio\":\"GIUSEPE GRACIOLLI\",\"codigo_pais\":null,\"faixa_etaria\":\"Entre 41 a 50 anos\",\"cnpj_cpf_do_socio\":\"***152940**\",\"qualificacao_socio\":\"S\\u00f3cio\",\"codigo_faixa_etaria\":5,\"data_entrada_sociedade\":\"2013-05-22\",\"identificador_de_socio\":2,\"cpf_representante_legal\":\"***000000**\",\"nome_representante_legal\":\"\",\"codigo_qualificacao_socio\":22,\"qualificacao_representante_legal\":\"N\\u00e3o informada\",\"codigo_qualificacao_representante_legal\":0},{\"pais\":null,\"nome_socio\":\"INSTITUTO DE OLHOS DE PALMAS S\\/A\",\"codigo_pais\":null,\"faixa_etaria\":\"N\\u00e3o se aplica\",\"cnpj_cpf_do_socio\":\"37377041000101\",\"qualificacao_socio\":\"S\\u00f3cio\",\"codigo_faixa_etaria\":0,\"data_entrada_sociedade\":\"2011-02-02\",\"identificador_de_socio\":1,\"cpf_representante_legal\":\"***300877**\",\"nome_representante_legal\":\"MARCOS PEREIRA DE AVILA\",\"codigo_qualificacao_socio\":22,\"qualificacao_representante_legal\":\"Administrador\",\"codigo_qualificacao_representante_legal\":5},{\"pais\":null,\"nome_socio\":\"TAUAN DE OLIVEIRA\",\"codigo_pais\":null,\"faixa_etaria\":\"Entre 31 a 40 anos\",\"cnpj_cpf_do_socio\":\"***550291**\",\"qualificacao_socio\":\"S\\u00f3cio\",\"codigo_faixa_etaria\":4,\"data_entrada_sociedade\":\"2019-06-06\",\"identificador_de_socio\":2,\"cpf_representante_legal\":\"***000000**\",\"nome_representante_legal\":\"\",\"codigo_qualificacao_socio\":22,\"qualificacao_representante_legal\":\"N\\u00e3o informada\",\"codigo_qualificacao_representante_legal\":0},{\"pais\":null,\"nome_socio\":\"TAINAN DE OLIVEIRA\",\"codigo_pais\":null,\"faixa_etaria\":\"Entre 31 a 40 anos\",\"cnpj_cpf_do_socio\":\"***430421**\",\"qualificacao_socio\":\"S\\u00f3cio\",\"codigo_faixa_etaria\":4,\"data_entrada_sociedade\":\"2019-06-06\",\"identificador_de_socio\":2,\"cpf_representante_legal\":\"***000000**\",\"nome_representante_legal\":\"\",\"codigo_qualificacao_socio\":22,\"qualificacao_representante_legal\":\"N\\u00e3o informada\",\"codigo_qualificacao_representante_legal\":0},{\"pais\":null,\"nome_socio\":\"TULIO CESAR DE OLIVEIRA JUNIOR\",\"codigo_pais\":null,\"faixa_etaria\":\"Entre 31 a 40 anos\",\"cnpj_cpf_do_socio\":\"***900301**\",\"qualificacao_socio\":\"S\\u00f3cio\",\"codigo_faixa_etaria\":4,\"data_entrada_sociedade\":\"2019-06-06\",\"identificador_de_socio\":2,\"cpf_representante_legal\":\"***000000**\",\"nome_representante_legal\":\"\",\"codigo_qualificacao_socio\":22,\"qualificacao_representante_legal\":\"N\\u00e3o informada\",\"codigo_qualificacao_representante_legal\":0},{\"pais\":null,\"nome_socio\":\"MARIA CLAUDIA SCHELINI DE OLIVEIRA\",\"codigo_pais\":null,\"faixa_etaria\":\"Entre 31 a 40 anos\",\"cnpj_cpf_do_socio\":\"***584251**\",\"qualificacao_socio\":\"S\\u00f3cio\",\"codigo_faixa_etaria\":4,\"data_entrada_sociedade\":\"2019-06-06\",\"identificador_de_socio\":2,\"cpf_representante_legal\":\"***000000**\",\"nome_representante_legal\":\"\",\"codigo_qualificacao_socio\":22,\"qualificacao_representante_legal\":\"N\\u00e3o informada\",\"codigo_qualificacao_representante_legal\":0}]', '[{\"codigo\":8660700,\"descricao\":\"Atividades de apoio \\u00e0 gest\\u00e3o de sa\\u00fade\"}]', 'ANA BEATRIZ DIAS', 'Sócio', 'TULIO CESAR DE OLIVEIRA', 'Sócio-Administrador', 'NUBIA CRISTINA DE FREITAS MAIA', 'Sócio', 'aprovado', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `grupo_risco`
--

DROP TABLE IF EXISTS `grupo_risco`;
CREATE TABLE IF NOT EXISTS `grupo_risco` (
  `id` int NOT NULL AUTO_INCREMENT,
  `descricao` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `grupo_risco`
--

INSERT INTO `grupo_risco` (`id`, `descricao`) VALUES
(1, 'GRUPO 1'),
(2, 'GRUPO 2'),
(3, 'GRUPO 3');

-- --------------------------------------------------------

--
-- Estrutura para tabela `logomarcas`
--

DROP TABLE IF EXISTS `logomarcas`;
CREATE TABLE IF NOT EXISTS `logomarcas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `municipio` varchar(100) NOT NULL,
  `caminho_logomarca` varchar(255) NOT NULL,
  `espacamento` int DEFAULT '40',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `logomarcas`
--

INSERT INTO `logomarcas` (`id`, `municipio`, `caminho_logomarca`, `espacamento`) VALUES
(3, 'GURUPI', '../../uploads/logomarcas/logo.png', 30);

-- --------------------------------------------------------

--
-- Estrutura para tabela `log_visualizacoes`
--

DROP TABLE IF EXISTS `log_visualizacoes`;
CREATE TABLE IF NOT EXISTS `log_visualizacoes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `arquivo_id` int NOT NULL,
  `data_visualizacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=126 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `log_visualizacoes`
--

INSERT INTO `log_visualizacoes` (`id`, `usuario_id`, `arquivo_id`, `data_visualizacao`) VALUES
(96, 1, 363, '2024-07-05 16:59:13'),
(97, 1, 366, '2024-07-05 17:03:48'),
(98, 1, 368, '2024-07-05 17:09:26'),
(99, 1, 369, '2024-07-05 17:10:46'),
(100, 1, 370, '2024-07-05 17:12:17'),
(101, 1, 371, '2024-07-05 17:14:01'),
(102, 1, 372, '2024-07-05 17:23:36'),
(103, 1, 373, '2024-07-05 17:30:46'),
(104, 1, 373, '2024-07-05 20:43:39'),
(105, 1, 373, '2024-07-05 20:43:56'),
(106, 1, 587, '2024-07-06 12:54:55'),
(107, 1, 82, '2024-07-11 21:25:53'),
(108, 1, 86, '2024-07-11 21:26:15'),
(109, 1, 86, '2024-07-11 21:26:19'),
(110, 1, 88, '2024-07-11 21:29:54'),
(111, 1, 87, '2024-07-11 21:30:02'),
(112, 1, 87, '2024-07-11 21:30:09'),
(113, 1, 87, '2024-07-11 21:31:21'),
(114, 1, 93, '2024-07-11 21:40:51'),
(115, 1, 93, '2024-07-11 21:42:18'),
(116, 1, 93, '2024-07-11 21:42:23'),
(117, 1, 100, '2024-07-11 22:46:30'),
(118, 1, 738, '2024-07-11 22:48:21'),
(119, 1, 739, '2024-07-11 22:52:26'),
(120, 1, 93, '2024-07-11 23:18:18'),
(121, 1, 740, '2024-07-11 23:19:18'),
(122, 1, 740, '2024-07-11 23:25:19'),
(123, 1, 93, '2024-07-11 23:25:22'),
(124, 1, 740, '2024-07-11 23:32:46'),
(125, 1, 93, '2024-07-11 23:32:48');

-- --------------------------------------------------------

--
-- Estrutura para tabela `modelos_documentos`
--

DROP TABLE IF EXISTS `modelos_documentos`;
CREATE TABLE IF NOT EXISTS `modelos_documentos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tipo_documento` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `conteudo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `municipio` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `modelos_documentos`
--

INSERT INTO `modelos_documentos` (`id`, `tipo_documento`, `conteudo`, `municipio`) VALUES
(2, 'PARECER TÉCNICO', '<p style=\"text-align: justify;\"><strong>ATividades licenciadas:&nbsp;</strong></p>\r\n<p style=\"text-align: justify;\"><strong>1 - OBJETIVO:&nbsp;</strong>Concess&atilde;o de Alvar&aacute; Sanit&aacute;rio</p>\r\n<p style=\"text-align: justify;\"><strong>2 - DATA DA INSPE&Ccedil;&Atilde;O:&nbsp; </strong>Realizei (amos) a inspe&ccedil;&atilde;o na data da assinatura deste documento.</p>\r\n<p style=\"text-align: justify;\"><strong>3 - ATOS NORMATIVOS:</strong>&nbsp;Lei Federal n&ordm; 6.473, de 20 de agosto de 1977, Lei Municipal n&ordm; 1.085, de 31 de dezembro de 1994 e demais atos regulamentares;</p>\r\n<p style=\"text-align: justify;\"><strong>4 - AN&Aacute;LISE DA DOCUMENTA&Ccedil;&Atilde;O:</strong>&nbsp; A empresa protocolou a documenta&ccedil;&atilde;o necess&aacute;ria para libera&ccedil;&atilde;o do Alvar&aacute; Sanit&aacute;rio.</p>\r\n<p style=\"text-align: justify;\"><strong>5 - PARECER:&nbsp;</strong>A empresa supra citada , apresenta-se em boas condi&ccedil;&otilde;es higi&ecirc;nico-sanit&aacute;rias, estruturais e f&iacute;sicas para desenvolvimento de suas atividades;</p>\r\n<p style=\"text-align: justify;\">Portanto manifesto-me (maifestamo-nos) favor&aacute;vel (favor&aacute;veis) ao Licenciamento Sanit&aacute;rio da(s) atividade(s) sujeita(s) a Vigil&acirc;ncia Sanit&aacute;ria, desenvolvida(s) pelo estabelecimento.</p>\r\n<p style=\"text-align: justify;\">Eis o parecer.</p>', 'GURUPI'),
(3, 'ALVARÁ SANITÁRIO', '<p style=\"text-align: left;\"><strong>ATIVIDADES LICENCIADAS:</strong></p>\r\n<p style=\"text-align: center;\"><strong>VALIDADE: 31/03/2025</strong></p>\r\n<p style=\"text-align: center;\"><strong>\"Esta licen&ccedil;a perde a validade caso a empresa se torne irregular\"</strong></p>\r\n<p style=\"text-align: center;\"><strong>DEVER&Aacute; SER AFIXADO EM LOCAL VIS&Iacute;VEL</strong></p>\r\n<p>&nbsp;</p>', 'GURUPI'),
(4, 'AUTO DE INFRAÇÃO', '<p>TESTE AUTO DE INFRA&Ccedil;&Atilde;O</p>', 'GURUPI');

-- --------------------------------------------------------

--
-- Estrutura para tabela `ordem_servico`
--

DROP TABLE IF EXISTS `ordem_servico`;
CREATE TABLE IF NOT EXISTS `ordem_servico` (
  `id` int NOT NULL AUTO_INCREMENT,
  `estabelecimento_id` int DEFAULT NULL,
  `processo_id` int DEFAULT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date NOT NULL,
  `acoes_executadas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `tecnicos` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pdf_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('ativa','finalizada') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ativa',
  `descricao_encerramento` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `pontuacao` int NOT NULL DEFAULT '0',
  `observacao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `estabelecimento_id` (`estabelecimento_id`),
  KEY `processo_id` (`processo_id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `ordem_servico`
--

INSERT INTO `ordem_servico` (`id`, `estabelecimento_id`, `processo_id`, `data_inicio`, `data_fim`, `acoes_executadas`, `tecnicos`, `pdf_path`, `status`, `descricao_encerramento`, `pontuacao`, `observacao`) VALUES
(19, 19, 35, '2024-07-05', '2024-07-06', '[\"7\"]', '[\"14\",\"15\",\"17\",\"42\"]', '../../uploads/ordem_servico/ordem_servico_1720149415.pdf', 'finalizada', 'teste', 0, ''),
(31, 29, 53, '2024-07-11', '2024-07-12', '[\"7\",\"8\"]', '[\"42\"]', '../../uploads/ordem_servico/ordem_servico_1720748932.pdf', 'finalizada', 'teste', 0, 'teste');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pontuacao_tecnicos`
--

DROP TABLE IF EXISTS `pontuacao_tecnicos`;
CREATE TABLE IF NOT EXISTS `pontuacao_tecnicos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tecnico_id` int NOT NULL,
  `pontuacao` int NOT NULL,
  `data` datetime NOT NULL,
  `ordem_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tecnico_id` (`tecnico_id`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `pontuacao_tecnicos`
--

INSERT INTO `pontuacao_tecnicos` (`id`, `tecnico_id`, `pontuacao`, `data`, `ordem_id`) VALUES
(64, 42, 0, '2024-07-11 23:00:20', 31);

-- --------------------------------------------------------

--
-- Estrutura para tabela `processos`
--

DROP TABLE IF EXISTS `processos`;
CREATE TABLE IF NOT EXISTS `processos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `estabelecimento_id` int NOT NULL,
  `tipo_processo` varchar(255) DEFAULT NULL,
  `data_abertura` date NOT NULL,
  `numero_processo` varchar(255) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'ATIVO',
  `motivo_parado` text,
  PRIMARY KEY (`id`),
  KEY `estabelecimento_id` (`estabelecimento_id`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `processos`
--

INSERT INTO `processos` (`id`, `estabelecimento_id`, `tipo_processo`, `data_abertura`, `numero_processo`, `status`, `motivo_parado`) VALUES
(46, 10, 'LICENCIAMENTO', '2024-07-09', '2024/00001', 'ATIVO', NULL),
(47, 2, 'LICENCIAMENTO', '2024-07-09', '2024/00002', 'ATIVO', NULL),
(50, 26, 'ADMINISTRATIVO', '2024-07-11', '2024/00005', 'ATIVO', NULL),
(53, 29, 'LICENCIAMENTO', '2024-07-11', '2024/00004', 'ATIVO', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `processos_acompanhados`
--

DROP TABLE IF EXISTS `processos_acompanhados`;
CREATE TABLE IF NOT EXISTS `processos_acompanhados` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `processo_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `processo_id` (`processo_id`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `processos_acompanhados`
--

INSERT INTO `processos_acompanhados` (`id`, `usuario_id`, `processo_id`) VALUES
(10, 15, 9),
(22, 19, 17);

-- --------------------------------------------------------

--
-- Estrutura para tabela `processos_responsaveis`
--

DROP TABLE IF EXISTS `processos_responsaveis`;
CREATE TABLE IF NOT EXISTS `processos_responsaveis` (
  `id` int NOT NULL AUTO_INCREMENT,
  `processo_id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `descricao` text NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pendente',
  PRIMARY KEY (`id`),
  KEY `processo_id` (`processo_id`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `processos_responsaveis`
--

INSERT INTO `processos_responsaveis` (`id`, `processo_id`, `usuario_id`, `descricao`, `status`) VALUES
(20, 38, 14, 'Fazer O.S para estabelecimento.', 'resolvido'),
(22, 53, 42, 'teste', 'resolvido');

-- --------------------------------------------------------

--
-- Estrutura para tabela `responsaveis_legais`
--

DROP TABLE IF EXISTS `responsaveis_legais`;
CREATE TABLE IF NOT EXISTS `responsaveis_legais` (
  `id` int NOT NULL AUTO_INCREMENT,
  `estabelecimento_id` int NOT NULL,
  `nome` varchar(255) NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `documento_identificacao` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `estabelecimento_id` (`estabelecimento_id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `responsaveis_legais`
--

INSERT INTO `responsaveis_legais` (`id`, `estabelecimento_id`, `nome`, `cpf`, `email`, `telefone`, `documento_identificacao`) VALUES
(1, 1, 'Erick Vinicius Rodrigues', '01758848111', 'erickafram08@gmail.com', '63981013083', '1.cnh.pdf'),
(2, 2, 'JOÃO CARLOS DIVINO FELICIANO', '84345420130', 'joaofeliciano@yahoo.com.br', '6333990000', 'boleto shopeee.pdf'),
(4, 14, 'Erick Vinicius Rodrigues', '01758848111', 'erickafram08@gmail.com', '63981013083', '1.cnh.pdf'),
(8, 16, 'Erick Vinicius Rodrigues', '01758848111', 'erickafram08@gmail.com', '63981013083', '1.cnh.pdf'),
(9, 17, 'Erick Vinicius Rodrigues', '87921502172', 'erickafram08@gmail.com', '62981013083', 'home3.png'),
(23, 19, 'Erick Vinicius Rodrigues', '87921502172', 'erickafram08@gmail.com', '62981013083', 'home3.png'),
(24, 24, 'Kauany Neres', '02365675190', 'kauanyneres@gmail.com', '63981013083', 'logo.png'),
(25, 29, 'Erick Vinicius Rodrigues', '01758848111', 'erickafram08@gmail.com', '63981013083', '1.cnh.pdf');

-- --------------------------------------------------------

--
-- Estrutura para tabela `responsaveis_tecnicos`
--

DROP TABLE IF EXISTS `responsaveis_tecnicos`;
CREATE TABLE IF NOT EXISTS `responsaveis_tecnicos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `estabelecimento_id` int NOT NULL,
  `nome` varchar(255) NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `conselho` varchar(50) NOT NULL,
  `numero_registro_conselho` varchar(50) NOT NULL,
  `carteirinha_conselho` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `estabelecimento_id` (`estabelecimento_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `responsaveis_tecnicos`
--

INSERT INTO `responsaveis_tecnicos` (`id`, `estabelecimento_id`, `nome`, `cpf`, `email`, `telefone`, `conselho`, `numero_registro_conselho`, `carteirinha_conselho`) VALUES
(1, 10, 'MIRIAN ALMEIDA TELES GUIMARÃES', '01859890121', 'contab@grupoaraguaia.com.br', '63 3215-8825', 'CRF', '1732', 'doc droga sua.pdf'),
(2, 12, 'LORENA MARQUES FREITAS VERZOLA', '06749024670', 'legalizacao@cacmg.cnt.br', '(34)3239-6565', 'CRM', '4719', 'RT DUO CORE.pdf'),
(3, 13, 'JOACIL ALVES JAPIASSU', '33696381187', 'joasiljapiassu@hotmail.com', '63 3312 1059', 'CRM', 'CRBM 00473', 'Scan_0003.pdf');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tipos_acoes_executadas`
--

DROP TABLE IF EXISTS `tipos_acoes_executadas`;
CREATE TABLE IF NOT EXISTS `tipos_acoes_executadas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `descricao` varchar(255) NOT NULL,
  `codigo_procedimento` varchar(20) NOT NULL,
  `atividade_sia` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `tipos_acoes_executadas`
--

INSERT INTO `tipos_acoes_executadas` (`id`, `descricao`, `codigo_procedimento`, `atividade_sia`) VALUES
(7, 'INSPEÇÃO SANITÁRIA DE ESTABELECIMENTOS SUJEITOS A VIGILÂNCIA SANITÁRIA, EXCETO DE ALIMENTOS', '01.02.01.017-0', 1),
(8, 'REINSPEÇÃO SANITÁRIA DE ESTABELECIMENTOS SUJEITOS A VIGILÂNCIA SANITÁRIA, EXCETO DE ALIMENTOS', '01.02.01.017-0', 1),
(9, 'CONFERÊNCIA DE BMPO OU INVENTÁRIO DE MEDICAMENTOS SUJEITOS A CONTROLE ESPECIAL', '01.02.01.017-0', 1),
(10, 'AÇÃO NÃO REALIZADA', '00.00.00.000-0', 0),
(11, 'ADVERTÊNCIA', '01.02.01.017-0', 1),
(12, 'ORIENTAÇÃO SANITÁRIA', '01.02.01.017-0', 1),
(13, 'NOTIFICAÇÃO SANITÁRIA', '01.02.01.017-0', 1),
(14, 'AUTO DE INFRAÇÃO', '01.02.01.052-8', 1),
(15, 'AUTO DE COLETA E AMOSTRA', '01.02.01.017-0', 1),
(16, 'INTERDIÇÃO OU DESINTERDIÇÃO DE ESTABELECIMENTO OU PRODUTO', '01.02.01.017-0', 1),
(17, 'TERMO DE APREENSÃO', '01.02.01.017-0', 1),
(18, 'TERMO DE INUTILIZAÇÃO', '01.02.01.017-0', 1),
(19, 'DILIGÊNCIA DE AVERIGUAÇÃO E CONST. OU ORIENTATIVAS', '01.02.01.017-0', 1),
(20, 'RELATÓRIO FISCAL', '01.02.01.017-0', 1),
(21, 'LAUDO TÉCNICO', '01.02.01.017-0', 1),
(22, 'MANIFESTAÇÃO PROCESSUAL', '01.02.01.017-0', 1),
(23, 'CADASTRO DE ESTABELECIMENOS SUJEITOS A VIGILÂNCIA SANITÁRIA', '01.02.01.007-2', 1),
(24, 'CADASTRO DE INSTITUIÇÕES DE LONGA PERMANÊNCIA PARA IDOSOS', '01.02.01.027-7', 1),
(25, 'CADASTRO DE ESTABELECIMENOS DE SERVIÇOS DE ALIMENTAÇÃO', '01.02.01.045-5', 1),
(26, 'CADASTRO DE SERVIÇO DE DIAGNÓSTICO E TRATAMENTO DO CÂNCER DE COLO DE UTERO E MAMA', '01.02.01.033-1', 1),
(27, 'EXCLUSÃO DE CADASTRO DE ESTABELECIMENTOS A VIGILÂNCIA SANITÁRIA COM ATIVIDADES ENCERRADAS', '01.02.01.016-1', 1),
(28, 'INSPEÇÃO SANITÁRIA DE INSTITUIÇÕES DE LONGA PERMANÊNCIA PARA IDOSOS', '01.02.01.028-5', 1),
(29, 'INSPEÇÃO SANITÁRIA DE ESTABELECIMENTOS DE SERVIÇOS DE ALIMENTAÇÃO', '01.02.01.046-3', 1),
(30, 'INSPEÇÃO SANITÁRIA DE SERVIÇOS DE DIAGNÓSTICO E TRATAMENTO DO CÂNCER DE COLO DE ÚTERO E MAMA', '01.02.01.034-0', 1),
(31, 'LICENCIAMENTO DOS ESTABELECIMENTO SUJEITOS A VIGILÂNCIA SANITÁRIA', '01.02.01.018-8', 1),
(32, 'LICENCIAMENTO SANITÁRIO DE INSTITUIÇÕES DE LONGA PERMANÊNCIA PARA IDOSOS', '01.02.01.029-3', 1),
(33, 'LICENCIAMENTO SANITÁRIO DE ESTABELECIMENOS DE SERVIÇOS DE ALIMENTAÇÃO', '01.02.01.047-1', 1),
(34, 'LICENCIAMENTO SANITÁRIO DE SERVIÇO DE DIAGNÓSTICO E TRATAMENTO DO CÂNCER DE COLO DE UTERO E MAMA', '01.02.01.035-8', 1),
(35, 'APRESENTAÇÃO OU REALIZAÇÃO DE PALESTRAS, CONFERÊNCIAS, CURSOS E SIMILARES - ATIVIDADES EDUCATIVAS PARA A POPULAÇÃO', '01.02.01.022-6', 1),
(36, 'APRESENTAÇÃO OU REALIZAÇÃO DE PALESTRAS, CONFERÊNCIAS, CURSOS E SIMILARES - ATIVIDADES EDUCATIVAS PARA O SETOR REGULADO', '01.02.01.005-6', 1),
(37, 'APRESENTAÇÃO OU REALIZAÇÃO DE PALESTRAS, CONFERÊNCIAS, CURSOS E SIMILARES - ATIVIDADES EDUCATIVAS SOBRE A TEMÁTICA DA DENGUE', '01.02.01.050-1', 1),
(38, 'ANÁLISE DE PROJETOS BÁSICOS DE ARQUITETURA', '01.02.01.006-4', 1),
(39, 'APROVAÇÃO DE PROJETOS BÁSICOS DE ARQUITETURA', '01.02.01.019-6', 1),
(40, 'RECEBIMENTO DE DENÚNCIAS / RECLAMAÇÕES', '01.02.01.023-4', 1),
(41, 'ATENDIMENTO DE DENUNCIAS / RECLAMAÇÕES - INSPEÇÃO PARA APURAÇÃO DE DENÚNCIA / RECLAMAÇÕES', '01.02.01.024-2', 1),
(42, 'CONCLUSÃO DE PROCESSO ADMINSTRATIVO SANITÁRIO', '01.02.01.053-6', 1),
(43, 'FISCALIZAÇÃO DO USO DE PRODUTOS FUMÍGENOS DERIVADOS DO TABACO EM AMBIENTES COLETIVOS FECHADOS, PÚBLICOS OU PRIVADOS.', '01.02.01.048-0', 1),
(44, 'ALIMENTAÇÃO EM SISTEMAS DE INFORMAÇÃO E BANCO DE DADOS RELACIONADOS A ATIVIDADES SANITÁRIAS', '00.00.00.000-0', 0),
(45, 'INSPEÇÃO PRÉVIA', '01.02.01.017-0', 1),
(46, 'INSPEÇÃO DE ROTINA', '01.02.01.017-0', 1),
(47, 'INSPEÇÃO DE RENOVAÇÃO DE ALVARÁ', '01.02.01.017-0', 1),
(48, 'OUTRAS ATIVIDADES', '00.00.00.000-0', 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome_completo` varchar(255) NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `municipio` varchar(100) DEFAULT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `nivel_acesso` int DEFAULT NULL,
  `senha` varchar(255) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'ativo',
  `tempo_vinculo` int DEFAULT NULL,
  `escolaridade` varchar(50) DEFAULT NULL,
  `tipo_vinculo` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_municipio` (`municipio`)
) ENGINE=MyISAM AUTO_INCREMENT=43 DEFAULT CHARSET=latin1;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome_completo`, `cpf`, `email`, `telefone`, `municipio`, `cargo`, `nivel_acesso`, `senha`, `status`, `tempo_vinculo`, `escolaridade`, `tipo_vinculo`) VALUES
(14, 'Marcelo Prevedello Pigatto ', '645.358.351-87', 'mpigatto@hotmail.com', '(63) 9 8401-5605', 'GURUPI', 'Gerente Municipal', 4, '$2y$10$hR7WID26wkSzS/xj2YcwyOpgRH5xPcRUo.jRy5N1Zkb77FQ3p1UHe', 'ativo', 1, 'Fundamental', 'Contratado'),
(15, 'Patrick Neves Barros', '012.907.371-77', 'patrick_nevesbarros@yahoo.com.br', '(63) 9 8496-0081', 'GURUPI', 'Gerente Municipal', 3, '$2y$10$mwMLWf7celhJinMEgiMBO.n4m.7s4iY5xXZOvHJUBE7wwRMMVdl2C', 'ativo', 0, 'Superior', 'Efetivo'),
(17, 'Poliana Ribeiro Valadares Veras', '977.116.321-34', 'poliveras38@icloud.com', '(63) 9 9999-9999', 'GURUPI', 'Fiscal Municipal', 4, '$2y$10$9MfAoohyuItC2.Q15OKRGe4l/HS2Cc4dXYEjt4Xb0x209rvq2XMrK', 'ativo', NULL, NULL, NULL),
(40, 'Erick Vinicius Rodrigues', '017.588.481-11', 'erickafram08@gmail.com', '(63) 9 8101-3083', 'PALMAS', 'Gerente Municipal', 1, '$2y$10$Sw.4zDz5P.NwNNgO/nfpZes1O1ZNhE5pv8l97dHCGxIMDFqBXL7Lm', 'ativo', NULL, NULL, NULL),
(42, 'Kauany Neres', '023.656.751-90', 'kauanysss@gmail.com', '(62) 9 8101-5545', 'PALMAS', 'Gerente Municipal', 3, '$2y$10$8RyYY/DNKEyhe.cJhsw97OtCyX1s.KMYqACQeGK2wvzWd0fWDz5XG', 'ativo', 4, 'Superior', 'Contratado'),
(41, 'Erick Vinicius Rodrigues', '017.588.481-55', 'erickafram088@gmail.com', '(62) 9 8101-5487', 'PALMAS', 'Gerente Municipal', 1, '$2y$10$7Nf7miCqDX5IIN2h27pfOuv.aTn6MFKcYOBfnlLZDZCvn79SOhbdG', 'ativo', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios_estabelecimentos`
--

DROP TABLE IF EXISTS `usuarios_estabelecimentos`;
CREATE TABLE IF NOT EXISTS `usuarios_estabelecimentos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `estabelecimento_id` int NOT NULL,
  `tipo_vinculo` enum('CONTADOR','RESPONSÁVEL LEGAL','RESPONSÁVEL TÉCNICO','FUNCIONÁRIO') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_estabelecimento_unique` (`usuario_id`,`estabelecimento_id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `estabelecimento_id` (`estabelecimento_id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `usuarios_estabelecimentos`
--

INSERT INTO `usuarios_estabelecimentos` (`id`, `usuario_id`, `estabelecimento_id`, `tipo_vinculo`) VALUES
(3, 3, 2, 'CONTADOR'),
(20, 1, 29, 'CONTADOR'),
(21, 1, 10, 'CONTADOR');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios_externos`
--

DROP TABLE IF EXISTS `usuarios_externos`;
CREATE TABLE IF NOT EXISTS `usuarios_externos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome_completo` varchar(255) NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `vinculo_estabelecimento` varchar(255) DEFAULT NULL,
  `tipo_vinculo` enum('CONTADOR','RESPONSÁVEL LEGAL','RESPONSÁVEL TÉCNICO','FUNCIONÁRIO') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `vinculo_estabelecimento` (`vinculo_estabelecimento`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `usuarios_externos`
--

INSERT INTO `usuarios_externos` (`id`, `nome_completo`, `cpf`, `telefone`, `email`, `senha`, `vinculo_estabelecimento`, `tipo_vinculo`) VALUES
(1, 'Rogério Silva Rodrigues', '879.215.021-72', '(63) 98101-3083', 'rogeriosrodrigues@gmail.com', '$2y$10$ElQM3EdmtO6Yvz.YFvzMK.C0/AzixSVEEaNclg0wu4ZZFiwWVXl2m', 'CONTADOR', 'CONTADOR'),
(2, 'Erick Vinicius', '017.588.481-11', '(63) 98101-3083', 'erickafram08@gmail.com', '$2y$10$GcGgABRvqVRLpLeYL5YC/ONJoRH3koJeqM79IKrMmG2u2cR.iLwzC', 'CONTADOR', 'CONTADOR'),
(3, 'JESSÉ MILHOMENS DE ABREU', '959.225.551-20', '(63) 99932-6000', 'jessemilhomens@hotmail.com', '$2y$10$zAb9RWSX7yEOQCACNeGljOZ3RR/CQ33M/gdtHFArO7So7IwHHnq36', 'CONTADOR', 'CONTADOR'),
(5, 'Kauny Neres Rodrigues', '023.656.751-90', '(63) 98101-5545', 'kauanyneres@gmail.com', '$2y$10$Q/RelVlu2lI0pcO4sikptOwEmG5KKgRR7wD.a1ETK3bgwuszjehea', 'CONTADOR', 'CONTADOR');

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `atividade_grupo_risco`
--
ALTER TABLE `atividade_grupo_risco`
  ADD CONSTRAINT `fk_grupo_risco_atividade` FOREIGN KEY (`grupo_risco_id`) REFERENCES `grupo_risco` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `usuarios_estabelecimentos`
--
ALTER TABLE `usuarios_estabelecimentos`
  ADD CONSTRAINT `usuarios_estabelecimentos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios_externos` (`id`),
  ADD CONSTRAINT `usuarios_estabelecimentos_ibfk_2` FOREIGN KEY (`estabelecimento_id`) REFERENCES `estabelecimentos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
