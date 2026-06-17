/* ============================================================
   Department Head — Votes Status List JS
   No PHP data bridge needed — pure JS filter functions.
   Filter IDs: vsFilterYearFrom, vsFilterYearTo, vsFilterStatus, vsSearchInput
   Card selector: #vsGrid .vs-card  (data-year, data-status, data-name)
   ============================================================ */

function vsApplyFilters() {
    var yearFrom = parseInt(document.getElementById('vsFilterYearFrom').value) || null;
    var yearTo   = parseInt(document.getElementById('vsFilterYearTo').value)   || null;
    var status   = (document.getElementById('vsFilterStatus')  ? document.getElementById('vsFilterStatus').value.toLowerCase()  : '');
    var search   = (document.getElementById('vsSearchInput')   ? document.getElementById('vsSearchInput').value.toLowerCase().trim() : '');

    var cards = document.querySelectorAll('#vsGrid .vs-card');
    var visibleCount = 0;

    cards.forEach(function (card) {
        var cardYear   = parseInt(card.dataset.year);
        var cardStatus = card.dataset.status || '';
        var cardName   = (card.dataset.name  || '').toLowerCase();

        var matchYear   = (!yearFrom || cardYear >= yearFrom) && (!yearTo || cardYear <= yearTo);
        var matchStatus = !status || cardStatus === status;
        var matchSearch = !search || cardName.indexOf(search) !== -1;

        if (matchYear && matchStatus && matchSearch) {
            card.style.display = '';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    var noResults = document.getElementById('vsNoResults');
    if (noResults) noResults.style.display = visibleCount === 0 ? 'block' : 'none';
}

function vsResetFilters() {
    ['vsFilterYearFrom', 'vsFilterYearTo', 'vsFilterStatus', 'vsSearchInput'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.value = '';
    });
    vsApplyFilters();
}
