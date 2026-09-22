-- =====================================================
-- BANCO DE DADOS: SENTINELA FATEC
-- Arquitetura: Class Table Inheritance (Herança por Tabela de Classes)
-- =====================================================

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

CREATE SCHEMA IF NOT EXISTS `sentinela_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sentinela_db`;

-- -----------------------------------------------------
-- 1. TABELA: usuarios
-- Apenas usuários que acessam e operam o software (Admin e Porteiro)
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
-- 2. TABELA PAI: condutores (Generalização)
-- Atributos comuns a qualquer pessoa que transita com veículo no campus
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `condutores` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL,
  `cpf_cnpj` VARCHAR(18) NOT NULL,
  `tipo` ENUM('professor', 'funcionario', 'prestador', 'visitante') NOT NULL,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_condutor_tipo` (`tipo` ASC),
  INDEX `idx_condutor_cpf_cnpj` (`cpf_cnpj` ASC)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- 3. TABELA FILHA: condutores_professores (Especialização Docente)
-- Chave primária compartilhada (condutor_id é PK e FK simultaneamente)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `condutores_professores` (
  `condutor_id` BIGINT UNSIGNED NOT NULL,
  `codigo_acesso` VARCHAR(20) NOT NULL COMMENT 'Matrícula Funcional (Ex: DOC-94281)',
  `departamento` VARCHAR(100) NOT NULL COMMENT 'Ex: DSM, GTI, Banco de Dados',
  `titulacao` VARCHAR(50) NULL COMMENT 'Ex: Doutor, Mestre, Especialista',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`condutor_id`),
  UNIQUE INDEX `cod_acesso_docente_UNIQUE` (`codigo_acesso` ASC),
  CONSTRAINT `fk_professores_condutor`
    FOREIGN KEY (`condutor_id`)
    REFERENCES `condutores` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- 4. TABELA FILHA: condutores_funcionarios (Especialização Administrativa)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `condutores_funcionarios` (
  `condutor_id` BIGINT UNSIGNED NOT NULL,
  `codigo_acesso` VARCHAR(20) NOT NULL COMMENT 'Código Funcional (Ex: ADM-10293)',
  `setor` VARCHAR(100) NOT NULL COMMENT 'Ex: Secretaria Central, Biblioteca, RH',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`condutor_id`),
  UNIQUE INDEX `cod_acesso_func_UNIQUE` (`codigo_acesso` ASC),
  CONSTRAINT `fk_funcionarios_condutor`
    FOREIGN KEY (`condutor_id`)
    REFERENCES `condutores` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- 5. TABELA FILHA: condutores_prestadores (Especialização Terceirizados)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `condutores_prestadores` (
  `condutor_id` BIGINT UNSIGNED NOT NULL,
  `empresa` VARCHAR(100) NOT NULL COMMENT 'Razão Social ou Nome Fantasia',
  `cnpj` VARCHAR(18) NULL,
  `inicio_contrato` DATE NOT NULL,
  `fim_contrato` DATE NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`condutor_id`),
  CONSTRAINT `fk_prestadores_condutor`
    FOREIGN KEY (`condutor_id`)
    REFERENCES `condutores` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- 6. TABELA FILHA: condutores_visitantes (Especialização Visitantes Eventuais)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `condutores_visitantes` (
  `condutor_id` BIGINT UNSIGNED NOT NULL,
  `motivo_visita` VARCHAR(255) NOT NULL COMMENT 'Ex: Reunião de Coordenação, Banca de TCC',
  `setor_destino` VARCHAR(100) NULL COMMENT 'Ex: Bloco A • DSM',
  `permanencia_estimada` VARCHAR(50) NULL COMMENT 'Ex: 30 minutos, 1 hora',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`condutor_id`),
  CONSTRAINT `fk_visitantes_condutor`
    FOREIGN KEY (`condutor_id`)
    REFERENCES `condutores` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- 7. TABELA: veiculos
-- Relacionamento 1:N com condutores (Pai) - ZERO FKs Nulas
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
-- 8. TABELA: registros_acesso
-- Passagens capturadas pela câmera OCR e Liberações Operacionais na Cancela
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `registros_acesso` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `veiculo_id` BIGINT UNSIGNED NULL COMMENT 'NULL quando a placa não estiver previamente cadastrada',
  `condutor_autorizado_id` BIGINT UNSIGNED NULL COMMENT 'Condutor validado na liberação manual por código funcional',
  `operador_id` BIGINT UNSIGNED NOT NULL COMMENT 'Porteiro/Admin logado que operou o sistema',
  `placa_reconhecida` VARCHAR(10) NOT NULL,
  `placa_corrigida` VARCHAR(10) NULL,
  `taxa_confianca` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Taxa de precisão do OCR (0 a 100)',
  `foto_path` VARCHAR(255) NULL,
  `tipo_movimentacao` ENUM('entrada', 'saida') NOT NULL DEFAULT 'entrada',
  `status` ENUM('autorizado', 'pendente', 'nao_cadastrado', 'liberado_manual', 'recusado') NOT NULL DEFAULT 'pendente',
  `justificativa` VARCHAR(255) NULL COMMENT 'Ex: Veículo emprestado de terceiro, correção de caractere OCR',
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
