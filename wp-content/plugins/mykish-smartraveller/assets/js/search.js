(function () {
  function renderResults(results) {
    const target = document.getElementById('mykish-search-results');
    if (!target) return;

    if (!results.length) {
      target.innerHTML = '<p class="mykish-empty">No destinations found.</p>';
      return;
    }

    target.innerHTML = results.slice(0, 8).map((entry) => {
      const item = entry.item || entry;
      return `
        <a class="mykish-result" href="${item.url}">
          <strong>${item.country}</strong>
          <span>Level ${item.advisory_level} · ${item.advisory_text}</span>
          <small>${item.summary || ''}</small>
        </a>
      `;
    }).join('');
  }

  document.addEventListener('DOMContentLoaded', function () {
    if (typeof Fuse === 'undefined' || typeof MykishSmartravellerData === 'undefined') return;

    const input = document.getElementById('mykish-search-input');
    if (!input) return;

    const fuse = new Fuse(MykishSmartravellerData.dataset, {
      includeScore: true,
      threshold: 0.4,
      ignoreLocation: true,
      keys: ['country', 'regions_text', 'queries', 'summary']
    });

    renderResults(MykishSmartravellerData.dataset.slice(0, 6));

    input.addEventListener('input', function () {
      const value = input.value.trim();
      if (!value) {
        renderResults(MykishSmartravellerData.dataset.slice(0, 6));
        return;
      }

      const results = fuse.search(value);
      renderResults(results);
    });

    document.querySelectorAll('.mykish-summary-grid .mykish-card').forEach((card) => {
      card.addEventListener('click', function () {
        const level = Number(card.dataset.filterLevel);
        const filtered = MykishSmartravellerData.dataset.filter((item) => Number(item.advisory_level) === level);
        renderResults(filtered);
      });
    });
  });
})();
