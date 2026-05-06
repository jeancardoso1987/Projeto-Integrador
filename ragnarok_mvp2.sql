-- ============================================================
--  Ragnarok MVP Timer — Schema v2.1
--  Compatível com MySQL 5.7+ / MariaDB 10.3+
--
--  MUDANÇA v2.1:
--  - mvps não tem mais servidor_id (tempos globais)
--  - adicionado item_id para URL automática da imagem
--  - adicionado nivel e elemento para info no card
--
--  Instalação nova  → rode tudo
--  Já tinha v2.0    → rode só o bloco MIGRATION no final
-- ============================================================

CREATE DATABASE IF NOT EXISTS ragnarok_mvp
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE ragnarok_mvp;

-- ------------------------------------------------------------
-- USUARIOS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username     VARCHAR(50)  NOT NULL UNIQUE,
    email        VARCHAR(120) NOT NULL UNIQUE,
    senha_hash   VARCHAR(255) NOT NULL,
    role         ENUM('admin','usuario') NOT NULL DEFAULT 'usuario',
    ativo        TINYINT(1) NOT NULL DEFAULT 1,
    criado_em    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ultimo_login DATETIME NULL,
    INDEX idx_role  (role),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB;

INSERT INTO usuarios (username, email, senha_hash, role) VALUES (
    'admin', 'admin@localhost',
    '$2y$12$Yv7L9Q3K1mXwZpN8sT6uuOeD4vF2hJ0cRlA5gB1nM7kW3oPqI8eXC',
    'admin'
);

-- ------------------------------------------------------------
-- SERVIDORES
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS servidores (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome      VARCHAR(100) NOT NULL,
    descricao VARCHAR(255) NULL,
    tipo      ENUM('low','mid','high','custom') NOT NULL DEFAULT 'mid',
    ativo     TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- MVPs  (globais — mesmos tempos em todos os servidores)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS mvps (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome          VARCHAR(100) NOT NULL,
    mapa          VARCHAR(100) NOT NULL,
    spawn_min     INT UNSIGNED NOT NULL  COMMENT 'Tempo de respawn em minutos',
    item_id       INT UNSIGNED NULL      COMMENT 'ID do card no divine-pride para URL automática',
    imagem_url    VARCHAR(512) NULL      COMMENT 'URL manual — tem prioridade sobre item_id',
    nivel         SMALLINT UNSIGNED NULL,
    elemento      VARCHAR(30)  NULL,
    ativo         TINYINT(1) NOT NULL DEFAULT 1,
    criado_em     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ativo (ativo),
    INDEX idx_nome  (nome)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- MONITORAMENTOS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS monitoramentos (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id  INT UNSIGNED NOT NULL,
    mvp_id      INT UNSIGNED NOT NULL,
    servidor_id INT UNSIGNED NOT NULL,
    iniciado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    morto_em    DATETIME NULL,
    spawn_est   DATETIME NULL,
    finalizado  TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (usuario_id)  REFERENCES usuarios(id)  ON DELETE CASCADE,
    FOREIGN KEY (mvp_id)      REFERENCES mvps(id)      ON DELETE CASCADE,
    FOREIGN KEY (servidor_id) REFERENCES servidores(id) ON DELETE CASCADE,
    INDEX idx_usuario   (usuario_id),
    INDEX idx_mvp       (mvp_id),
    INDEX idx_iniciado  (iniciado_em),
    INDEX idx_finalizado(finalizado)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- CONFIGURACOES
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS configuracoes (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id     INT UNSIGNED NOT NULL UNIQUE,
    alerta_minutos INT UNSIGNED NOT NULL DEFAULT 5,
    som_ativo      TINYINT(1) NOT NULL DEFAULT 1,
    notif_browser  TINYINT(1) NOT NULL DEFAULT 1,
    tema           ENUM('dark','light') NOT NULL DEFAULT 'dark',
    atualizado_em  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- VIEWS
-- ============================================================
CREATE OR REPLACE VIEW vw_historico_monitoramentos AS
SELECT
    m.id, u.username, u.email,
    mv.nome AS mvp_nome, mv.mapa,
    s.nome  AS servidor_nome,
    m.iniciado_em, m.morto_em, m.spawn_est, m.finalizado
FROM monitoramentos m
JOIN usuarios   u  ON u.id  = m.usuario_id
JOIN mvps       mv ON mv.id = m.mvp_id
JOIN servidores s  ON s.id  = m.servidor_id;

CREATE OR REPLACE VIEW vw_ranking_mvps AS
SELECT
    mv.id,
    mv.nome                AS mvp_nome,
    COUNT(m.id)            AS total_monitoramentos,
    MAX(m.iniciado_em)     AS ultimo_monitoramento
FROM mvps mv
LEFT JOIN monitoramentos m ON m.mvp_id = mv.id
GROUP BY mv.id, mv.nome
ORDER BY total_monitoramentos DESC;

-- ============================================================
--  MIGRATION — só rode se já tinha o banco v2.0
--  Descomente e execute separadamente se necessário:
-- ============================================================
-- ALTER TABLE mvps
--     DROP FOREIGN KEY mvps_ibfk_1,
--     DROP INDEX idx_servidor,
--     DROP COLUMN servidor_id,
--     CHANGE COLUMN spawn_min_min spawn_min INT UNSIGNED NOT NULL,
--     DROP COLUMN spawn_max_min,
--     ADD COLUMN item_id       INT UNSIGNED NULL     AFTER imagem_url,
--     ADD COLUMN nivel         SMALLINT UNSIGNED NULL AFTER item_id,
--     ADD COLUMN elemento      VARCHAR(30) NULL       AFTER nivel,
--     MODIFY COLUMN imagem_url VARCHAR(512) NULL;