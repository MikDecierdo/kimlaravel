/* ============================================================
   Department Head — Dashboard JS
   PHP data bridge (in blade): _chartLabels, _chartVotes
   ============================================================ */

// ── Quick Actions: auto-open modals on target pages ─────
(function () {
    var map = {
        openAddElection:  function () { if (typeof openModal       === 'function') openModal(); },
        openAddCandidate: function () { if (typeof openModal       === 'function') openModal(); },
        openAddStudent:   function () { if (typeof openAddModal    === 'function') openAddModal(); },
        openAddEvent:     function () { if (typeof openAddEventModal === 'function') openAddEventModal(); },
    };
    Object.keys(map).forEach(function (key) {
        if (sessionStorage.getItem(key)) {
            sessionStorage.removeItem(key);
            window.addEventListener('DOMContentLoaded', function () {
                try { map[key](); } catch (e) {}
            });
        }
    });
})();

// ── Candidate Votes Chart ───────────────────────────────
function initCandidateChart(labels, votes) {
    var canvas = document.getElementById('candidateVotesChart');
    if (!canvas) return;
    var maxVote = Math.max.apply(null, votes.concat([1]));
    var ctx = canvas.getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Votes',
                data: votes,
                backgroundColor: votes.map(function (v) {
                    return v === maxVote ? 'rgba(128,0,32,0.92)' : 'rgba(128,0,32,0.35)';
                }),
                borderColor: 'rgba(128,0,32,1)',
                borderWidth: 1.5,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function (ctx) { return ' ' + ctx.parsed.x + ' votes'; }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { precision: 0, color: '#6b7280', font: { size: 12 } },
                    grid: { color: '#f1f5f9' }
                },
                y: {
                    ticks: {
                        color: '#1f2937',
                        font: { size: 12, weight: '600' },
                        callback: function (val) {
                            var lbl = this.getLabelForValue(val);
                            return lbl.length > 32 ? lbl.substring(0, 32) + '…' : lbl;
                        }
                    },
                    grid: { display: false }
                }
            }
        }
    });
}

// Initialise chart when DOM is ready (if PHP data bridge provided the arrays)
document.addEventListener('DOMContentLoaded', function () {
    if (typeof _chartLabels !== 'undefined' && typeof _chartVotes !== 'undefined') {
        initCandidateChart(_chartLabels, _chartVotes);
    }
});
