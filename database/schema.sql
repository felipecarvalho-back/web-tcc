-- =====================================================
-- BANCO DE DADOS: SENTINELA FATEC
-- Estrutura de Tabelas Otimizada (DDL)
-- =====================================================

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

CREATE SCHEMA IF NOT EXISTS `sentinela_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sentinela_db`;

-- -----------------------------------------------------
-- 1. TABELA: usuarios
-- EXCLUSIVA para quem faz LOGIN no software (Administrador e Porteiro)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL,
  `cpf` VARCHAR(14) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `senha` VARCHAR(255) NOT NULL,
  `perfil` ENUM('admin', 'porteiro') NOT NULL DEFAULT 'porteiro',
  `codigo_operador` VARCHAR(20) NULL COMMENT 'Ex: GDA-104, ADM-001',
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `cpf_UNIQUE` (`cpf` ASC),
  UNIQUE INDEX `email_UNIQUE` (`email` ASC)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- 2. TABELA: condutores
-- Centraliza quem entra com veículo (Professores, Funcionários, Prestadores e Visitantes)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `condutores` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL,
  `cpf_cnpj` VARCHAR(18) NOT NULL,
  `tipo` ENUM('professor', 'funcionario', 'prestador', 'visitante') NOT NULL DEFAULT 'professor',
  `codigo_acesso` VARCHAR(20) NULL COMMENT 'Matrícula: DOC-94281, ADM-10293',
  `departamento` VARCHAR(100) NULL COMMENT 'Para docentes/funcionários: DSM, GTI, RH',
  `empresa` VARCHAR(100) NULL COMMENT 'Para prestadores de serviço terceirizados',
  `motivo_visita` VARCHAR(255) NULL COMMENT 'Para visitantes eventuais',
  `validade_inicio` DATE NULL COMMENT 'Início de contrato / autorização',
  `validade_fim` DATE NULL COMMENT 'Fim de contrato / autorização',
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_codigo_acesso` (`codigo_acesso` ASC),
  INDEX `idx_cpf_cnpj` (`cpf_cnpj` ASC)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- 3. TABELA: veiculos
-- Cada veículo pertence a 1 condutor (1 condutor pode possuir N veículos)
-- NENHUMA FK NULA: Elimina o anti-pattern de múltiplas FKs com NULLs
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `veiculos` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `condutor_id` BIGINT UNSIGNED NOT NULL,
  `placa` VARCHAR(10) NOT NULL,
  `modelo` VARCHAR(50) NULL,
  `cor` VARCHAR(30) NULL,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `placa_UNIQUE` (`placa` ASC),
  INDEX `fk_veiculos_condutor_idx` (`condutor_id` ASC),
  CONSTRAINT `fk_veiculos_condutor`
    FOREIGN KEY (`condutor_id`)
    REFERENCES `condutores` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- 4. TABELA: registros_acesso
-- Passagens capturadas pela câmera OCR e Liberações Manuais na Cancela
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `registros_acesso` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `veiculo_id` BIGINT UNSIGNED NULL COMMENT 'NULL quando o veículo for não cadastrado',
  `condutor_autorizado_id` BIGINT UNSIGNED NULL COMMENT 'Condutor validado na liberação manual (ex: professor com carro de amigo)',
  `operador_id` BIGINT UNSIGNED NOT NULL COMMENT 'Porteiro/Admin logado que operou o sistema',
  `placa_reconhecida` VARCHAR(10) NOT NULL,
  `placa_corrigida` VARCHAR(10) NULL,
  `taxa_confianca` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Percentual OCR de 0 a 100',
  `foto_path` VARCHAR(255) NULL,
  `tipo_movimentacao` ENUM('entrada', 'saida') NOT NULL DEFAULT 'entrada',
  `status` ENUM('autorizado', 'pendente', 'nao_cadastrado', 'liberado_manual', 'recusado') NOT NULL DEFAULT 'pendente',
  `justificativa` VARCHAR(255) NULL COMMENT 'Motivo da liberação manual ou correção de placa',
  `data_hora` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `fk_registros_veiculo_idx` (`veiculo_id` ASC),
  INDEX `fk_registros_condutor_idx` (`condutor_autorizado_id` ASC),
  INDEX `fk_registros_operador_idx` (`operador_id` ASC),
  INDEX `idx_placa_reconhecida` (`placa_reconhecida` ASC),
  INDEX `idx_data_hora` (`data_hora` ASC),
  CONSTRAINT `fk_registros_veiculo`
    FOREIGN KEY (`veiculo_id`)
    REFERENCES `veiculos` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_registros_condutor`
    FOREIGN KEY (`condutor_autorizado_id`)
    REFERENCES `condutores` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_registros_operador`
    FOREIGN KEY (`operador_id`)
    REFERENCES `usuarios` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
