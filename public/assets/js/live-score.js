/**
 * SUNUSCORE - Live polling robuste.
 * - Polling toutes les 12s pendant que le match est live.
 * - Indicateur de fraîcheur ("actualisé il y a Xs").
 * - Gestion d'erreur réseau avec retry exponentiel.
 * - Arrêt automatique quand le match n'est plus live.
 * - Évite les doublons DOM : reconstruit la timeline à chaque tick.
 */
(function () {
    'use strict';

    var root = document.getElementById('live-root');
    if (!root) return;

    var api   = root.getAttribute('data-api');
    var isLive = root.getAttribute('data-live') === '1';
    if (!api) return;

    var scoreEl   = document.getElementById('live-score');
    var statusEl  = document.getElementById('live-status');
    var eventsEl  = document.getElementById('live-events');
    var freshEl   = document.getElementById('live-freshness');

    var intervalMs = 12000;
    var backoff    = intervalMs;
    var stopped    = false;
    var lastUpdate = null;

    function fmtAgo(seconds) {
        if (seconds < 5) return "à l'instant";
        if (seconds < 60) return 'il y a ' + seconds + 's';
        return 'il y a ' + Math.floor(seconds / 60) + 'min';
    }

    function renderFreshness() {
        if (!freshEl || !lastUpdate) return;
        var s = Math.floor((Date.now() - lastUpdate) / 1000);
        freshEl.textContent = 'Actualisé ' + fmtAgo(s);
    }

    function renderEvents(events) {
        if (!eventsEl) return;
        var visible = events.filter(function (e) { return !e.voided; });
        if (visible.length === 0) {
            eventsEl.innerHTML =
                '<div class="empty-state"><h3>Aucun événement</h3>' +
                '<p>Les événements de ce match seront affichés dès leur saisie.</p></div>';
            return;
        }
        var labels = {
            goal: '⚽ But', penalty_goal: '⚽ Penalty', own_goal: '⚽ CSC',
            yellow_card: '🟨 Jaune', red_card: '🟥 Rouge', substitution: '🔄 Remplacement'
        };
        var html = '<div class="card">';
        visible.forEach(function (e) {
            var label = labels[e.type] || e.type;
            html += '<div class="timeline-row">';
            html += '<div class="tr-minute">' + e.minute + "'" +
                    (e.extra > 0 ? '+' + e.extra : '') + '</div>';
            html += '<div class="tr-body">';
            html += '<div class="tr-title">' + label +
                    ' <span class="text-muted">— ' + e.team_name + '</span></div>';
            if (e.player) {
                html += '<div class="text-small text-muted">' + e.player + '</div>';
            }
            html += '</div></div>';
        });
        html += '</div>';
        eventsEl.innerHTML = html;
    }

    function apply(data) {
        if (scoreEl) scoreEl.textContent = data.home_score + ' - ' + data.away_score;
        if (statusEl) {
            if (['live','half_time','suspended'].indexOf(data.status) >= 0) {
                statusEl.innerHTML = '● ' + data.minute + "' EN DIRECT";
            } else if (data.status === 'finished') {
                statusEl.textContent = 'TERMINÉ';
            } else {
                statusEl.textContent = data.status.toUpperCase();
            }
        }
        renderEvents(data.events || []);
        lastUpdate = Date.now();
        renderFreshness();

        if (data.status === 'finished' || data.status === 'cancelled') {
            stopped = true;
        }
    }

    function tick() {
        if (stopped) return;
        fetch(api + '?t=' + Date.now(), { headers: { 'Accept': 'application/json' }, cache: 'no-store' })
            .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
            .then(function (resp) {
                if (!resp.success) throw new Error(resp.message || 'API');
                apply(resp.data);
                backoff = intervalMs; // reset backoff
            })
            .catch(function () {
                backoff = Math.min(backoff * 2, 60000);
            })
            .finally(function () {
                if (!stopped) setTimeout(tick, backoff);
            });
    }

    // Indicateur fraîcheur mis à jour toutes les 5s
    setInterval(renderFreshness, 5000);

    // Premier appel immédiat, puis boucle
    if (isLive) {
        tick();
    } else {
        // Match terminé ou à venir : on rafraîchit une fois de temps en temps (1 min)
        setInterval(function () {
            fetch(api + '?t=' + Date.now()).then(r => r.json()).then(r => r.success && apply(r.data)).catch(() => {});
        }, 60000);
    }
})();