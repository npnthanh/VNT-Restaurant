const slides = Array.from(document.querySelectorAll('.slide'));
const track = document.querySelector('.slides');
const next = document.querySelector('.next');
const prev = document.querySelector('.prev');
const hero = document.querySelector('.home-hero');
const mobileMedia = window.matchMedia('(max-width: 991px)');
const AUTOPLAY_INTERVAL_MS = 5000;
const AUTOPLAY_RESUME_DELAY_MS = 5000;

let index = 0;
let autoplayId = null;
let autoplayResumeId = null;
let isTouchTracking = false;
let touchStartX = 0;
let touchStartY = 0;
let touchDeltaX = 0;
let touchDeltaY = 0;

function getSlideCount() {
  return slides.length;
}

function getHeroWidth() {
  if (!hero) {
    return 1;
  }

  return hero.getBoundingClientRect().width || 1;
}

function setTrackTransform(offsetPx = 0) {
  if (!track) {
    return;
  }

  const dragPercent = mobileMedia.matches ? (offsetPx / getHeroWidth()) * 100 : 0;
  track.style.transform = `translate3d(${(-index * 100) + dragPercent}%, 0, 0)`;
}

function renderHero(offsetPx = 0) {
  if (!hero || !track) {
    return;
  }

  hero.classList.toggle('is-dragging', mobileMedia.matches && Math.abs(offsetPx) > 0);
  setTrackTransform(offsetPx);
}

function showSlide(nextIndex) {
  const count = getSlideCount();
  if (!count) {
    return;
  }

  index = ((nextIndex % count) + count) % count;
  renderHero(0);
}

function nextSlide() {
  showSlide(index + 1);
}

function prevSlide() {
  showSlide(index - 1);
}

function stopAutoplay() {
  clearInterval(autoplayId);
  autoplayId = null;
}

function clearAutoplayResume() {
  clearTimeout(autoplayResumeId);
  autoplayResumeId = null;
}

function startAutoplay() {
  stopAutoplay();
  clearAutoplayResume();

  if (getSlideCount() < 2) {
    return;
  }

  autoplayId = window.setInterval(() => {
    nextSlide();
  }, AUTOPLAY_INTERVAL_MS);
}

function scheduleAutoplayResume() {
  stopAutoplay();
  clearAutoplayResume();

  if (getSlideCount() < 2) {
    return;
  }

  autoplayResumeId = window.setTimeout(() => {
    startAutoplay();
  }, AUTOPLAY_RESUME_DELAY_MS);
}

function resetTouchState() {
  isTouchTracking = false;
  touchDeltaX = 0;
  touchDeltaY = 0;

  if (hero) {
    hero.classList.remove('is-dragging');
  }
}

function handleTouchEnd() {
  if (!isTouchTracking || !mobileMedia.matches) {
    resetTouchState();
    return;
  }

  const absX = Math.abs(touchDeltaX);
  const absY = Math.abs(touchDeltaY);

  if (absX > 55 && absX > absY + 12) {
    if (touchDeltaX < 0) {
      nextSlide();
    } else {
      prevSlide();
    }
  } else {
    renderHero(0);
  }

  resetTouchState();
  scheduleAutoplayResume();
}

if (track && getSlideCount()) {
  showSlide(index);

  if (next) {
    next.addEventListener('click', () => {
      nextSlide();
      scheduleAutoplayResume();
    });
  }

  if (prev) {
    prev.addEventListener('click', () => {
      prevSlide();
      scheduleAutoplayResume();
    });
  }

  if (hero && getSlideCount() > 1) {
    hero.addEventListener('touchstart', (event) => {
      if (!mobileMedia.matches || event.touches.length !== 1) {
        resetTouchState();
        return;
      }

      if (event.target.closest('.home-hero-actions')) {
        resetTouchState();
        return;
      }

      const touch = event.touches[0];
      isTouchTracking = true;
      touchStartX = touch.clientX;
      touchStartY = touch.clientY;
      touchDeltaX = 0;
      touchDeltaY = 0;
      stopAutoplay();
      clearAutoplayResume();
    }, { passive: true });

    hero.addEventListener('touchmove', (event) => {
      if (!isTouchTracking || !mobileMedia.matches || event.touches.length !== 1) {
        return;
      }

      const touch = event.touches[0];
      touchDeltaX = touch.clientX - touchStartX;
      touchDeltaY = touch.clientY - touchStartY;

      if (Math.abs(touchDeltaX) > Math.abs(touchDeltaY)) {
        renderHero(touchDeltaX);
      }
    }, { passive: true });

    hero.addEventListener('touchend', handleTouchEnd, { passive: true });
    hero.addEventListener('touchcancel', handleTouchEnd, { passive: true });
  }

  const handleViewportChange = () => {
    resetTouchState();
    showSlide(index);
  };

  if (typeof mobileMedia.addEventListener === 'function') {
    mobileMedia.addEventListener('change', handleViewportChange);
  } else if (typeof mobileMedia.addListener === 'function') {
    mobileMedia.addListener(handleViewportChange);
  }

  window.addEventListener('resize', () => {
    renderHero(0);
  });

  startAutoplay();
}
