-- =====================================================
-- BANCO DE DADOS: SENTINELA FATEC
-- Arquitetura: Class Table Inheritance (Herança por Tabela de Classes)
-- Modelo Otimizado com Controle de Estadia e Operador Único
-- =====================================================

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

CREATE SCHEMA IF NOT EXISTS `sentinela_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sentinela_db`;

-- -----------------------------------------------------
-- 1. TABELA: usuarios
-- Apenas usuários que acessam e operam o sistema (Admin, Operador e Supervisor)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(100) NOT NULL,
    `cpf` VARCHAR(14) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `senha` VARCHAR(255) NOT NULL,
    `perfil` ENUM('admin', 'operador', 'supervisor') NOT NULL DEFAULT 'operador',
    `codigo_operador` VARCHAR(20) UNIQUE,
    `ativo` BOOLEAN NOT NULL DEFAULT TRUE,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- 2. TABELA PAI: condutores (Generalização)
-- Atributos comuns a quem transita com veículos no campus
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `condutores` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(100) NOT NULL,
    `cpf` VARCHAR(14) NOT NULL UNIQUE,
    `tipo` ENUM('funcionario', 'professor', 'prestador', 'visitante') NOT NULL,
    `ativo` BOOLEAN NOT NULL DEFAULT TRUE,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- 3. TABELA: veiculos
-- Relacionamento 1:N com condutores (Pai) - Zero FKs nulas
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `veiculos` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `condutor_id` BIGINT UNSIGNED NOT NULL,
    `placa` VARCHAR(10) NOT NULL UNIQUE,
    `modelo` VARCHAR(50) NULL,
    `cor` VARCHAR(30) NULL,
    `ativo` BOOLEAN NOT NULL DEFAULT TRUE,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`condutor_id`) REFERENCES `condutores`(`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- 4. TABELAS ESPECIALIZADAS (Filhas com PK compartilhada como FK)
-- -----------------------------------------------------

-- 4.1 Especialização de Funcionários Administrativos
CREATE TABLE IF NOT EXISTS `condutores_funcionarios` (
    `condutor_id` BIGINT UNSIGNED PRIMARY KEY,
    `codigo_acesso` VARCHAR(20) NOT NULL UNIQUE COMMENT 'Ex: ADM-10293',
    `setor` VARCHAR(100) NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`condutor_id`) REFERENCES `condutores`(`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- 4.2 Especialização de Docentes / Professores
CREATE TABLE IF NOT EXISTS `condutores_professores` (
    `condutor_id` BIGINT UNSIGNED PRIMARY KEY,
    `codigo_acesso` VARCHAR(20) NOT NULL UNIQUE COMMENT 'Matrícula Funcional (Ex: DOC-94281)',
    `departamento` VARCHAR(100) NULL COMMENT 'Ex: DSM, GTI, Banco de Dados',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`condutor_id`) REFERENCES `condutores`(`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- 4.3 Especialização de Prestadores de Serviço
CREATE TABLE IF NOT EXISTS `condutores_prestadores` (
    `condutor_id` BIGINT UNSIGNED PRIMARY KEY,
    `empresa` VARCHAR(100) NOT NULL,
    `inicio_contrato` DATE NULL,
    `fim_contrato` DATE NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`condutor_id`) REFERENCES `condutores`(`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- 4.4 Especialização de Visitantes Eventuais
CREATE TABLE IF NOT EXISTS `condutores_visitantes` (
    `condutor_id` BIGINT UNSIGNED PRIMARY KEY,
    `motivo_visita` VARCHAR(255) NOT NULL,
    `setor_destino` VARCHAR(100) NULL,
    `permanencia_estimada` VARCHAR(50) NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`condutor_id`) REFERENCES `condutores`(`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- 5. TABELA: registros_acesso
-- Controle unificado de estadias e passagens na cancela
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `registros_acesso` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `veiculo_id` BIGINT UNSIGNED NULL COMMENT 'NULL se o veículo for de terceiro ou não cadastrado',
    `condutor_id` BIGINT UNSIGNED NULL COMMENT 'Identificado na leitura ou preenchido na liberação manual',
    `placa_registro` VARCHAR(10) NOT NULL,

    -- ENTRADA
    `data_hora_entrada` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `operador_id` BIGINT UNSIGNED NULL COMMENT 'Operador/Porteiro que autorizou a passagem',
    `taxa_confianca` TINYINT UNSIGNED NULL COMMENT 'Precisão do OCR (0 a 100)',
    `foto_entrada_path` VARCHAR(255) NULL,
    `placa_corrigida` VARCHAR(10) NULL,

    -- SAÍDA (Preenchida quando o veículo deixa o pátio)
    `data_hora_saida` TIMESTAMP NULL,

    -- STATUS E JUSTIFICATIVA
    `status` ENUM('em_patio', 'finalizado', 'bloqueado', 'liberado_manual') NOT NULL DEFAULT 'em_patio',
    `justificativa` VARCHAR(255) NULL COMMENT 'Ex: Veículo emprestado de terceiro, correção manual de OCR',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`veiculo_id`) REFERENCES `veiculos`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`condutor_id`) REFERENCES `condutores`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`operador_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
