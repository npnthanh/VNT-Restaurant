document.addEventListener('DOMContentLoaded', () => {
  const locations = window.locations || [];
  const scrollLinks = document.querySelectorAll('#menuScroll a');
  const container = document.querySelector('.location-container');

  if (!locations.length || !container) return;

  const resolveAsset = (path) => {
    if (!path) return `${window.assetUrl}images/location/L12L04.jpg`;
    if (/^https?:\/\//i.test(path)) return path;
    return `${window.assetUrl}${String(path).replace(/^\/+/, '')}`;
  };

  const setMapButton = (button, mapUrl) => {
    if (!button) return;
    button.dataset.mapUrl = mapUrl || '';
    button.disabled = !mapUrl;
    button.classList.toggle('is-disabled', !mapUrl);
    button.onclick = () => {
      if (button.dataset.mapUrl) {
        window.open(button.dataset.mapUrl, '_blank', 'noopener');
      }
    };
  };

  const bindLocationActions = (location) => {
    const bookBtn = container.querySelector('.book');
    const mapBtn = container.querySelector('.map');

    bookBtn?.addEventListener('click', () => {
      if (typeof window.openUserBookingModal === 'function') {
        window.openUserBookingModal({ locationId: location.id });
      }
    });

    setMapButton(mapBtn, location.map_url);
  };

  const updateLocationDisplay = (location) => {
    container.dataset.region = location.region_id;
    container.querySelector('h2').textContent = location.name;
    container.querySelector('.location-text > p').textContent = location.description || 'Chốn ăn chơi lý tưởng';
    container.querySelector('.open').textContent = location.status === 'active' ? 'ĐANG MỞ' : 'ĐÓNG CỬA';
    container.querySelector('.time').textContent = `HOẠT ĐỘNG TỪ ${location.formatted_time_start} – ${location.formatted_time_end}`;

    const infoValues = container.querySelectorAll('.info-location div strong');
    if (infoValues[0]) infoValues[0].textContent = `${location.capacity || '---'} KHÁCH`;
    if (infoValues[1]) infoValues[1].textContent = location.area ? `${Number(location.area).toLocaleString()} M²` : '---';
    if (infoValues[2]) infoValues[2].textContent = `${location.floors || '---'} TẦNG`;

    const image = container.querySelector('.location-image img');
    if (image) {
      image.src = resolveAsset(location.thumbnail);
      image.alt = location.name;
    }

    const bookBtn = container.querySelector('.book');
    if (bookBtn) {
      const nextBookBtn = bookBtn.cloneNode(true);
      bookBtn.replaceWith(nextBookBtn);
    }

    const mapBtn = container.querySelector('.map');
    if (mapBtn) {
      const nextMapBtn = mapBtn.cloneNode(true);
      mapBtn.replaceWith(nextMapBtn);
    }

    bindLocationActions(location);
  };

  const filterLocations = (regionId) => {
    const nextLocation = regionId
      ? locations.find((location) => String(location.region_id) === String(regionId))
      : locations[0];

    if (nextLocation) {
      updateLocationDisplay(nextLocation);
    }
  };

  scrollLinks.forEach((link) => {
    link.addEventListener('click', (event) => {
      event.preventDefault();
      scrollLinks.forEach((item) => item.classList.remove('active'));
      link.classList.add('active');
      filterLocations(link.dataset.region || '');
    });
  });

  updateLocationDisplay(locations[0]);
});
