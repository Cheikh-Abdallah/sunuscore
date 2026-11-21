-- ============================================================
-- SUNUSCORE - Seed idempotent
-- Compatible MariaDB 10.11 / MySQL 8.0
-- ============================================================

SET NAMES utf8mb4;

-- Roles
INSERT INTO roles (code, label) VALUES
    ('super_admin', 'Super administrateur'),
    ('admin',       'Administrateur'),
    ('operator',    'Opérateur')
ON DUPLICATE KEY UPDATE label = VALUES(label);

-- Super admin
INSERT INTO users (role_id, username, email, password_hash, first_name, last_name, is_active)
SELECT r.id, 'admin', 'admin@sunuscore.local',
       '$2y$12$fIYJKoIqTyGln1rDaD50e.vLtvdnqLfGKqWUX0o4AmlsIbZcIDJm2',
       'Super', 'Admin', 1
FROM roles r WHERE r.code = 'super_admin'
ON DUPLICATE KEY UPDATE email = VALUES(email), password_hash = VALUES(password_hash);

-- Compétition de démonstration
INSERT INTO competitions (name, slug, description, location, start_date, end_date, points_win, points_draw, points_loss, tiebreak_rules, status)
VALUES ('Grand Tournoi de l''Unité', 'grand-tournoi-unite',
        'Compétition de démonstration SUNUSCORE.',
        'Quartier', '2026-11-21', '2026-12-31',
        3, 1, 0,
        JSON_ARRAY('points','goal_diff','goals_for','head_to_head'),
        'upcoming')
ON DUPLICATE KEY UPDATE description = VALUES(description), start_date = VALUES(start_date), end_date = VALUES(end_date);

-- Équipes
INSERT INTO teams (name, slug, neighborhood)
VALUES
    ('Bidiw Bi',   'bidiw-bi',   NULL),
    ('Fass',       'fass',       NULL),
    ('Dagar Gui',  'dagar-gui',  NULL),
    ('Renaissance','renaissance',NULL),
    ('Avenir',     'avenir',     NULL),
    ('Natangué',   'natangue',   NULL),
    ('Espoir',     'espoir',     NULL),
    ('Sing-Sing',  'sing-sing',  NULL)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Inscriptions à la compétition
INSERT INTO competition_teams (competition_id, team_id)
SELECT c.id, t.id
FROM competitions c, teams t
WHERE c.slug = 'grand-tournoi-unite'
  AND t.slug IN ('bidiw-bi','fass','dagar-gui','renaissance','avenir','natangue','espoir','sing-sing')
ON DUPLICATE KEY UPDATE status = 'active';

-- Matchs programmés (idempotence via NOT EXISTS)
INSERT INTO matches (competition_id, home_team_id, away_team_id, venue, scheduled_at, call_time, status, matchday)
SELECT c.id, th.id, ta.id, 'Antenne (Terrain Avenir)', '2026-11-21 17:00:00', '2026-11-21 16:30:00', 'scheduled', 1
FROM competitions c, teams th, teams ta
WHERE c.slug='grand-tournoi-unite' AND th.slug='bidiw-bi' AND ta.slug='fass'
  AND NOT EXISTS (
      SELECT 1 FROM matches m
      WHERE m.competition_id=c.id AND m.home_team_id=th.id AND m.away_team_id=ta.id
        AND m.scheduled_at='2026-11-21 17:00:00'
  );

INSERT INTO matches (competition_id, home_team_id, away_team_id, venue, scheduled_at, status, matchday)
SELECT c.id, th.id, ta.id, NULL, '2026-11-25 17:00:00', 'scheduled', 2
FROM competitions c, teams th, teams ta
WHERE c.slug='grand-tournoi-unite' AND th.slug='dagar-gui' AND ta.slug='renaissance'
  AND NOT EXISTS (
      SELECT 1 FROM matches m
      WHERE m.competition_id=c.id AND m.home_team_id=th.id AND m.away_team_id=ta.id
        AND m.scheduled_at='2026-11-25 17:00:00'
  );

INSERT INTO matches (competition_id, home_team_id, away_team_id, venue, scheduled_at, status, matchday)
SELECT c.id, th.id, ta.id, NULL, '2026-11-28 17:00:00', 'scheduled', 3
FROM competitions c, teams th, teams ta
WHERE c.slug='grand-tournoi-unite' AND th.slug='avenir' AND ta.slug='natangue'
  AND NOT EXISTS (
      SELECT 1 FROM matches m
      WHERE m.competition_id=c.id AND m.home_team_id=th.id AND m.away_team_id=ta.id
        AND m.scheduled_at='2026-11-28 17:00:00'
  );

INSERT INTO matches (competition_id, home_team_id, away_team_id, venue, scheduled_at, status, matchday)
SELECT c.id, th.id, ta.id, NULL, '2026-12-02 17:00:00', 'scheduled', 4
FROM competitions c, teams th, teams ta
WHERE c.slug='grand-tournoi-unite' AND th.slug='espoir' AND ta.slug='sing-sing'
  AND NOT EXISTS (
      SELECT 1 FROM matches m
      WHERE m.competition_id=c.id AND m.home_team_id=th.id AND m.away_team_id=ta.id
        AND m.scheduled_at='2026-12-02 17:00:00'
  );