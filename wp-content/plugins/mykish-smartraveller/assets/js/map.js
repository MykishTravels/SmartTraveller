(function () {
  const colorByLevel = {
    1: '#34a853',
    2: '#fbbc05',
    3: '#ff8f00',
    4: '#ea4335'
  };

  document.addEventListener('DOMContentLoaded', function () {
    const mapEl = document.getElementById('mykish-advisory-map');
    if (!mapEl || typeof L === 'undefined') return;

    const countries = JSON.parse(mapEl.dataset.countries || '[]');

    const map = L.map('mykish-advisory-map').setView([20, 0], 2);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    countries.forEach((country) => {
      const marker = L.circleMarker([
        (Math.random() * 120) - 60,
        (Math.random() * 360) - 180
      ], {
        radius: 6,
        color: colorByLevel[country.advisory_level] || '#00c2cb',
        fillOpacity: 0.8
      }).addTo(map);

      marker.bindTooltip(`${country.country}: ${country.advisory_text}`);
      marker.on('click', () => {
        window.location.href = country.url;
      });
    });
  });
})();
