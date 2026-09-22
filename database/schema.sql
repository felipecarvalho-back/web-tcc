-- =====================================================
-- BANCO DE DADOS: SENTINELA FATEC
-- Estrutura de Tabelas (DDL)
-- =====================================================

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

CREATE SCHEMA IF NOT EXISTS `sentinela_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sentinela_db`;

-- -----------------------------------------------------
-- 1. Tabela: usuarios
-- Guarda Administradores, Porteiros e Condutores (Docentes/Funcionários)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL,
  `cpf` VARCHAR(14) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `senha` VARCHAR(255) NOT NULL,
  `tipo_usuario` ENUM('admin', 'porteiro', 'professor', 'funcionario') NOT NULL DEFAULT 'professor',
  `codigo_acesso` VARCHAR(20) NULL COMMENT 'Ex: DOC-94281, GDA-104, ADM-001',
  `departamento` VARCHAR(100) NULL COMMENT 'Ex: DSM, GTI, Secretaria Acadêmica',
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `cpf_UNIQUE` (`cpf` ASC),
  UNIQUE INDEX `email_UNIQUE` (`email` ASC),
  INDEX `idx_codigo_acesso` (`codigo_acesso` ASC)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- 2. Tabela: prestadores_servico
-- Terceirizados e empresas prestadoras de serviço
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `prestadores_servico` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL,
  `cpf_cnpj` VARCHAR(18) NOT NULL,
  `empresa` VARCHAR(100) NOT NULL,
  `inicio_contrato` DATE NOT NULL,
  `fim_contrato` DATE NOT NULL,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `cpf_cnpj_UNIQUE` (`cpf_cnpj` ASC)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- 3. Tabela: visitantes
-- Visitantes eventuais registrados na portaria
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `visitantes` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL,
  `cpf` VARCHAR(14) NOT NULL,
  `motivo_visita` VARCHAR(255) NOT NULL,
  `departamento_destino` VARCHAR(100) NULL,
  `permanencia_estimada` VARCHAR(50) NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_visitante_cpf` (`cpf` ASC)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- 4. Tabela: veiculos
-- Veículos cadastrados (1 condutor pode possuir N veículos)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `veiculos` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `placa` VARCHAR(10) NOT NULL,
  `modelo` VARCHAR(50) NULL,
  `cor` VARCHAR(30) NULL,
  `tipo_proprietario` ENUM('professor', 'funcionario', 'prestador', 'visitante') NOT NULL,
  `usuario_id` BIGINT UNSIGNED NULL,
  `prestador_servico_id` BIGINT UNSIGNED NULL,
  `visitante_id` BIGINT UNSIGNED NULL,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `placa_UNIQUE` (`placa` ASC),
  INDEX `fk_veiculos_usuario_idx` (`usuario_id` ASC),
  INDEX `fk_veiculos_prestador_idx` (`prestador_servico_id` ASC),
  INDEX `fk_veiculos_visitante_idx` (`visitante_id` ASC),
  CONSTRAINT `fk_veiculos_usuario`
    FOREIGN KEY (`usuario_id`)
    REFERENCES `usuarios` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_veiculos_prestador`
    FOREIGN KEY (`prestador_servico_id`)
    REFERENCES `prestadores_servico` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_veiculos_visitante`
    FOREIGN KEY (`visitante_id`)
    REFERENCES `visitantes` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- 5. Tabela: registros_acesso
-- Passagens capturadas pela câmera OCR / Liberações na Cancela
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `registros_acesso` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `veiculo_id` BIGINT UNSIGNED NULL COMMENT 'NULL quando o veículo for não cadastrado',
  `placa_reconhecida` VARCHAR(10) NOT NULL,
  `placa_corrigida` VARCHAR(10) NULL,
  `taxa_confianca` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Percentual OCR de 0 a 100',
  `foto_path` VARCHAR(255) NULL,
  `tipo_movimentacao` ENUM('entrada', 'saida') NOT NULL DEFAULT 'entrada',
  `status` ENUM('autorizado', 'pendente', 'nao_cadastrado', 'liberado_manual', 'recusado') NOT NULL DEFAULT 'pendente',
  `operador_id` BIGINT UNSIGNED NULL COMMENT 'Porteiro/Admin que operou a cancela',
  `docente_autorizado_id` BIGINT UNSIGNED NULL COMMENT 'Docente vinculado na liberação manual',
  `justificativa` VARCHAR(255) NULL COMMENT 'Motivo da liberação manual ou correção de OCR',
  `data_hora` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `fk_registros_veiculo_idx` (`veiculo_id` ASC),
  INDEX `fk_registros_operador_idx` (`operador_id` ASC),
  INDEX `fk_registros_docente_idx` (`docente_autorizado_id` ASC),
  INDEX `idx_placa_reconhecida` (`placa_reconhecida` ASC),
  INDEX `idx_data_hora` (`data_hora` ASC),
  CONSTRAINT `fk_registros_veiculo`
    FOREIGN KEY (`veiculo_id`)
    REFERENCES `veiculos` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_registros_operador`
    FOREIGN KEY (`operador_id`)
    REFERENCES `usuarios` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_registros_docente`
    FOREIGN KEY (`docente_autorizado_id`)
    REFERENCES `usuarios` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
