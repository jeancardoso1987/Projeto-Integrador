CREATE DATABASE IF NOT EXISTS ragnarok_mvp
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE ragnarok_mvp;

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
    '$2y$12$8HYmoPwH..OAyAOQGnqOwudaKId8RHom3gKrxgr2vC8YEKq21285u',
    'admin'
);

CREATE TABLE IF NOT EXISTS servidores (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome      VARCHAR(100) NOT NULL,
    descricao VARCHAR(255) NULL,
    tipo      ENUM('low','mid','high','custom') NOT NULL DEFAULT 'mid',
    ativo     TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO servidores (id, nome, descricao, tipo, ativo) VALUES
(1, 'LATAM',   NULL, 'mid', 1),
(2, 'NIDHHOG', NULL, 'mid', 1),
(3, 'HERO',    NULL, 'mid', 1);

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

INSERT INTO mvps (id, nome, mapa, spawn_min, item_id, imagem_url, nivel, elemento, ativo) VALUES
(2,  'Orc Herói',                'gef_fild03', 60,  4143,  NULL, 50,  'Terra',    1),
(3,  'Abelha-Rainha',            'mjolnir_04', 60,  4132,  NULL, 78,  'Vento',    1),
(4,  'Amon Ra',                  'moc_pryd06', 60,  4236,  NULL, 69,  'Terra',    1),
(5,  'Aranha Rainha',            'gl_cas01_',  120, 27362, NULL, 195, 'Sombrio',  1),
(6,  'Atroce',                   'ra_fild03',  3,   4425,  NULL, 113, 'Sombrio',  1),
(7,  'Bafomé',                   'prt_maze03', 120, 4147,  NULL, 81,  'Sombrio',  1),
(8,  'Belzebu',                  'qqq',        360, 4145,  NULL, 147, 'Fantasma', 1),
(9,  'Besouro-Ladrão Dourado',   'prt_sewb4',  60,  4128,  NULL, 65,  'Fogo',     1),
(10, 'Boitatá',                  'bra_dun02',  120, 27126, NULL, 93,  NULL,       1),
(11, 'Cavaleiro da Tempestade',  'xmas_dun02', 60,  4318,  NULL, 92,  'Vento',    1),
(12, 'Detardeurus',              'abyss_03',   180, 4386,  NULL, 135, 'Sombrio',  1),
(13, 'Doppelganger',             'gef_dun02',  120, 4142,  NULL, 77,  'Sombrio',  1),
(14, 'Drácula',                  'gef_dun01',  60,  4134,  NULL, 75,  'Sombrio',  1),
(15, 'Drake',                    'treasure02', 120, 4137,  NULL, 91,  'Maldito',  1),
(16, 'Eddga',                    'pay_fild10', 60,  4123,  NULL, 65,  'Fogo',     1),
(17, 'Faraó',                    'in_sphinx5', 60,  4148,  NULL, 85,  'Sombrio',  1),
(18, 'Flor do Luar',             'pay_dun04',  60,  4131,  NULL, 79,  'Fogo',     1),
(19, 'Freeoni',                  'moc_fild17', 120, 4121,  NULL, 71,  'Neutro',   1),
(20, 'Gorynych',                 'mosk_dun03', 120, 27162, NULL, 97,  'Terra',    1),
(21, 'Hatii',                    'xmas_fild01',120, 4324,  NULL, 98,  'Água',     1),
(22, 'Leak',                     'dew_dun01',  120, 4520,  NULL, 94,  'Sombrio',  1),
(23, 'Maya',                     'anthell02',  120, 4146,  NULL, 55,  'Terra',    1),
(24, 'Osíris',                   'moc_pryd04', 60,  4144,  NULL, 68,  'Maldito',  1),
(25, 'Pesar Noturno',            'ra_san05',   300, 4408,  NULL, 139, 'Fantasma', 1),
(26, 'R48-85-BESTIA',            'sp_rudus2',  60,  27319, NULL, 174, 'Neutro',   1),
(27, 'Rainha Scaraba',           'dic_dun02',  120, 4507,  NULL, 140, 'Terra',    1),
(28, 'RSX-0806',                 'ein_dun02',  125, 4342,  NULL, 100, 'Neutro',   1),
(29, 'Samurai Encarnado',        'ama_dun03',  91,  4263,  NULL, 100, 'Sombrio',  1),
(30, 'Senhor das Trevas',        'gl_chyard',  60,  4168,  NULL, 96,  'Maldito',  1),
(31, 'Tao Gunka',                'beach_dun',  300, 4302,  NULL, 110, 'Neutro',   1),
(32, 'Valquíria Randgris',       'odin_tem03', 480, 4407,  NULL, 141, 'Sagrado',  1),
(33, 'Vesper',                   'jupe_core',  120, 4374,  NULL, 128, 'Sagrado',  1),
(34, 'Lady Branca',              'lou_dun03',  117, 4372,  NULL, 97,  'Vento',    1);

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
