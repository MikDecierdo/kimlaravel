/* ============================================================
   Department Head — Votes Status (detail) JS
   PHP data bridge (in blade): let totalSeconds = {{ $timeLeft['total_seconds'] }};
   ============================================================ */

function updateCountdown() {
    if (totalSeconds <= 0) {
        var el = document.getElementById('countdown');
        if (el) el.textContent = 'Ended';
        return;
    }

    var days    = Math.floor(totalSeconds / 86400);
    var hours   = Math.floor((totalSeconds % 86400) / 3600);
    var minutes = Math.floor((totalSeconds % 3600)  / 60);
    var secs    = totalSeconds % 60;

    var formatted = days + 'd ' + String(hours).padStart(2, '0') + 'h ' + String(minutes).padStart(2, '0') + 'm ' + String(secs).padStart(2, '0') + 's';

    var el = document.getElementById('countdown');
    if (el) el.textContent = formatted;

    totalSeconds--;
}

document.addEventListener('DOMContentLoaded', function () {
    if (typeof totalSeconds !== 'undefined') {
        updateCountdown();
        setInterval(updateCountdown, 1000);
    }
});
