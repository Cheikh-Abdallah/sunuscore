-- ============================================================
-- SUNUSCORE - Schéma MySQL 8.0+
-- Encodage utf8mb4, moteur InnoDB, contraintes strictes.
-- Exécuter dans phpMyAdmin sur la base `sunuscore`.
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------- ROLES ----------
CREATE TABLE IF NOT EXISTS roles (
    id          TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    code        VARCHAR(30)  NOT NULL,
    label       VARCHAR(60)  NOT NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_roles_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- USERS ----------
CREATE TABLE IF NOT EXISTS users (
    id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    role_id          TINYINT UNSIGNED NOT NULL,
    username         VARCHAR(60)  NOT NULL,
    email            VARCHAR(190) NOT NULL,
    password_hash    VARCHAR(255) NOT NULL,
    first_name       VARCHAR(80)  NULL,
    last_name        VARCHAR(80)  NULL,
    is_active        TINYINT(1)   NOT NULL DEFAULT 1,
    last_login_at    DATETIME     NULL,
    last_login_ip    VARCHAR(45)  NULL,
    failed_attempts  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    locked_until     DATETIME     NULL,
    created_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by       BIGINT UNSIGNED NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_username (username),
    UNIQUE KEY uq_users_email (email),
    KEY idx_users_role (role_id),
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles (id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_users_created_by FOREIGN KEY (created_by) REFERENCES users (id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- COMPETITIONS ----------
CREATE TABLE IF NOT EXISTS competitions (
    id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name           VARCHAR(150) NOT NULL,
    slug           VARCHAR(170) NOT NULL,
    description    TEXT NULL,
    logo_path      VARCHAR(255) NULL,
    location       VARCHAR(150) NULL,
    start_date     DATE NULL,
    end_date       DATE NULL,
    points_win     TINYINT UNSIGNED NOT NULL DEFAULT 3,
    points_draw    TINYINT UNSIGNED NOT NULL DEFAULT 1,
    points_loss    TINYINT UNSIGNED NOT NULL DEFAULT 0,
    tiebreak_rules JSON NULL,  -- ex: ["points","goal_diff","goals_for","head_to_head"]
    status         ENUM('draft','upcoming','ongoing','finished','archived') NOT NULL DEFAULT 'draft',
    created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by     BIGINT UNSIGNED NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_competitions_slug (slug),
    KEY idx_competitions_status (status),
    CONSTRAINT fk_competitions_created_by FOREIGN KEY (created_by) REFERENCES users (id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- ZONES ----------
CREATE TABLE IF NOT EXISTS zones (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    competition_id  BIGINT UNSIGNED NOT NULL,
    name            VARCHAR(100) NOT NULL,
    position        SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_zones_comp_name (competition_id, name),
    CONSTRAINT fk_zones_competition FOREIGN KEY (competition_id) REFERENCES competitions (id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- POOLS ----------
CREATE TABLE IF NOT EXISTS pools (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    zone_id     BIGINT UNSIGNED NOT NULL,
    name        VARCHAR(100) NOT NULL,
    position    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_pools_zone_name (zone_id, name),
    CONSTRAINT fk_pools_zone FOREIGN KEY (zone_id) REFERENCES zones (id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- TEAMS ----------
CREATE TABLE IF NOT EXISTS teams (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name            VARCHAR(120) NOT NULL,
    slug            VARCHAR(140) NOT NULL,
    logo_path       VARCHAR(255) NULL,
    neighborhood    VARCHAR(120) NULL,
    president_name  VARCHAR(120) NULL,
    coach_name      VARCHAR(120) NULL,
    founded_year    SMALLINT UNSIGNED NULL,
    achievements    TEXT NULL,
    contact_phone   VARCHAR(30) NULL,
    contact_email   VARCHAR(190) NULL,
    description     TEXT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by      BIGINT UNSIGNED NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_teams_slug (slug),
    CONSTRAINT fk_teams_created_by FOREIGN KEY (created_by) REFERENCES users (id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- COMPETITION_TEAMS (inscriptions) ----------
CREATE TABLE IF NOT EXISTS competition_teams (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    competition_id  BIGINT UNSIGNED NOT NULL,
    team_id         BIGINT UNSIGNED NOT NULL,
    zone_id         BIGINT UNSIGNED NULL,
    pool_id         BIGINT UNSIGNED NULL,
    registered_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status          ENUM('active','withdrawn') NOT NULL DEFAULT 'active',
    created_by      BIGINT UNSIGNED NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_comp_team (competition_id, team_id),
    KEY idx_comp_teams_zone (zone_id),
    KEY idx_comp_teams_pool (pool_id),
    CONSTRAINT fk_ct_competition FOREIGN KEY (competition_id) REFERENCES competitions (id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_ct_team        FOREIGN KEY (team_id)        REFERENCES teams (id)        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_ct_zone        FOREIGN KEY (zone_id)        REFERENCES zones (id)        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_ct_pool        FOREIGN KEY (pool_id)        REFERENCES pools (id)        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_ct_created_by  FOREIGN KEY (created_by)     REFERENCES users (id)        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- PLAYERS (fiche permanente) ----------
CREATE TABLE IF NOT EXISTS players (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    first_name   VARCHAR(80)  NOT NULL,
    last_name    VARCHAR(80)  NOT NULL,
    photo_path   VARCHAR(255) NULL,
    birth_date   DATE NULL,
    phone        VARCHAR(30) NULL,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by   BIGINT UNSIGNED NULL,
    PRIMARY KEY (id),
    KEY idx_players_name (last_name, first_name),
    CONSTRAINT fk_players_created_by FOREIGN KEY (created_by) REFERENCES users (id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- COMPETITION_ROSTERS (inscription joueur dans une compétition) ----------
CREATE TABLE IF NOT EXISTS competition_rosters (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    competition_id  BIGINT UNSIGNED NOT NULL,
    team_id         BIGINT UNSIGNED NOT NULL,
    player_id       BIGINT UNSIGNED NOT NULL,
    jersey_number   TINYINT UNSIGNED NULL,
    position        ENUM('GK','DF','MF','FW') NULL,
    status          ENUM('active','injured','suspended','left') NOT NULL DEFAULT 'active',
    registered_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by      BIGINT UNSIGNED NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_roster_player_comp (competition_id, player_id),
    UNIQUE KEY uq_roster_team_number (competition_id, team_id, jersey_number),
    KEY idx_roster_team (team_id),
    CONSTRAINT fk_cr_competition FOREIGN KEY (competition_id) REFERENCES competitions (id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_cr_team        FOREIGN KEY (team_id)        REFERENCES teams (id)        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_cr_player      FOREIGN KEY (player_id)      REFERENCES players (id)      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_cr_created_by  FOREIGN KEY (created_by)     REFERENCES users (id)        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- MATCHES ----------
CREATE TABLE IF NOT EXISTS matches (
    id                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    competition_id     BIGINT UNSIGNED NOT NULL,
    home_team_id       BIGINT UNSIGNED NOT NULL,
    away_team_id       BIGINT UNSIGNED NOT NULL,
    zone_id            BIGINT UNSIGNED NULL,
    pool_id            BIGINT UNSIGNED NULL,
    matchday           SMALLINT UNSIGNED NULL,
    phase              VARCHAR(60) NULL,
    venue              VARCHAR(150) NULL,
    scheduled_at       DATETIME NOT NULL,
    call_time          DATETIME NULL,
    status             ENUM('scheduled','live','half_time','suspended','finished','postponed','cancelled') NOT NULL DEFAULT 'scheduled',
    current_minute     SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    current_period     ENUM('pre','first','half_time','second','full_time') NOT NULL DEFAULT 'pre',
    extra_time         SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    home_score         SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    away_score         SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    started_at         DATETIME NULL,
    finished_at        DATETIME NULL,
    created_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by         BIGINT UNSIGNED NULL,
    PRIMARY KEY (id),
    KEY idx_matches_competition (competition_id, scheduled_at),
    KEY idx_matches_status (status),
    KEY idx_matches_home (home_team_id),
    KEY idx_matches_away (away_team_id),
    KEY idx_matches_zone (zone_id),
    KEY idx_matches_pool (pool_id),
    CONSTRAINT fk_matches_competition FOREIGN KEY (competition_id) REFERENCES competitions (id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_matches_home FOREIGN KEY (home_team_id) REFERENCES teams (id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_matches_away FOREIGN KEY (away_team_id) REFERENCES teams (id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_matches_zone FOREIGN KEY (zone_id) REFERENCES zones (id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_matches_pool FOREIGN KEY (pool_id) REFERENCES pools (id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_matches_created_by FOREIGN KEY (created_by) REFERENCES users (id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT chk_matches_teams_diff CHECK (home_team_id <> away_team_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- MATCH_EVENTS ----------
CREATE TABLE IF NOT EXISTS match_events (
    id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    match_id       BIGINT UNSIGNED NOT NULL,
    team_id        BIGINT UNSIGNED NOT NULL,
    player_id      BIGINT UNSIGNED NULL,
    related_player_id BIGINT UNSIGNED NULL, -- passeur / joueur entrant
    event_type     ENUM('goal','own_goal','penalty_goal','yellow_card','red_card','substitution','period','match_start','match_end','half_time','resume','suspend','award') NOT NULL,
    minute         SMALLINT UNSIGNED NOT NULL,
    extra_minute   TINYINT UNSIGNED NOT NULL DEFAULT 0,
    detail         VARCHAR(255) NULL,
    is_voided      TINYINT(1) NOT NULL DEFAULT 0,
    voided_at      DATETIME NULL,
    voided_by      BIGINT UNSIGNED NULL,
    voided_reason  VARCHAR(255) NULL,
    created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by     BIGINT UNSIGNED NULL,
    PRIMARY KEY (id),
    KEY idx_events_match (match_id, is_voided),
    KEY idx_events_player (player_id),
    KEY idx_events_team (team_id),
    KEY idx_events_type (event_type),
    CONSTRAINT fk_events_match FOREIGN KEY (match_id) REFERENCES matches (id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_events_team FOREIGN KEY (team_id) REFERENCES teams (id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_events_player FOREIGN KEY (player_id) REFERENCES players (id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_events_related_player FOREIGN KEY (related_player_id) REFERENCES players (id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_events_created_by FOREIGN KEY (created_by) REFERENCES users (id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_events_voided_by FOREIGN KEY (voided_by) REFERENCES users (id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- MATCH_LINEUPS ----------
CREATE TABLE IF NOT EXISTS match_lineups (
    id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    match_id       BIGINT UNSIGNED NOT NULL,
    team_id        BIGINT UNSIGNED NOT NULL,
    player_id      BIGINT UNSIGNED NOT NULL,
    is_starting    TINYINT(1) NOT NULL DEFAULT 0,
    position       ENUM('GK','DF','MF','FW') NULL,
    jersey_number  TINYINT UNSIGNED NULL,
    created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_lineup_match_player (match_id, player_id),
    KEY idx_lineup_match_team (match_id, team_id),
    CONSTRAINT fk_lineup_match FOREIGN KEY (match_id) REFERENCES matches (id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_lineup_team FOREIGN KEY (team_id) REFERENCES teams (id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_lineup_player FOREIGN KEY (player_id) REFERENCES players (id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- MATCH_AWARDS (homme du match) ----------
CREATE TABLE IF NOT EXISTS match_awards (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    match_id     BIGINT UNSIGNED NOT NULL,
    player_id    BIGINT UNSIGNED NOT NULL,
    team_id      BIGINT UNSIGNED NOT NULL,
    award_type   ENUM('man_of_the_match') NOT NULL DEFAULT 'man_of_the_match',
    reason       VARCHAR(255) NULL,
    awarded_by   BIGINT UNSIGNED NULL,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_award_match_type (match_id, award_type),
    CONSTRAINT fk_award_match FOREIGN KEY (match_id) REFERENCES matches (id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_award_player FOREIGN KEY (player_id) REFERENCES players (id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_award_team FOREIGN KEY (team_id) REFERENCES teams (id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_award_awarded_by FOREIGN KEY (awarded_by) REFERENCES users (id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- AUDIT_LOGS ----------
CREATE TABLE IF NOT EXISTS audit_logs (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id     BIGINT UNSIGNED NULL,
    action      VARCHAR(80) NOT NULL,
    entity_type VARCHAR(60) NOT NULL,
    entity_id   BIGINT UNSIGNED NULL,
    old_values  JSON NULL,
    new_values  JSON NULL,
    ip_address  VARCHAR(45) NULL,
    user_agent  VARCHAR(255) NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_audit_user (user_id),
    KEY idx_audit_entity (entity_type, entity_id),
    KEY idx_audit_created (created_at),
    CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- SETTINGS ----------
CREATE TABLE IF NOT EXISTS settings (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT NULL,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_settings_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- PUSH_SUBSCRIPTIONS (préparation PWA, non utilisé Phase 1) ----------
CREATE TABLE IF NOT EXISTS push_subscriptions (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id      BIGINT UNSIGNED NULL,
    endpoint     VARCHAR(500) NOT NULL,
    p256dh_key   VARCHAR(255) NOT NULL,
    auth_key     VARCHAR(255) NOT NULL,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_push_endpoint (endpoint(191)),
    CONSTRAINT fk_push_user FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- FIN DU SCHEMA
-- ============================================================