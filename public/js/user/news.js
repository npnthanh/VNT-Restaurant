document.addEventListener('DOMContentLoaded', () => {
  const scrollContainer = document.getElementById('menuScroll');

  if (!scrollContainer) {
    return;
  }

  const checkScroll = () => {
    const leftBoundary = scrollContainer.scrollLeft + 40;
    const rightBoundary = scrollContainer.scrollLeft + scrollContainer.clientWidth - 40;
    const links = scrollContainer.querySelectorAll('a');

    links.forEach((link) => {
      const rect = link.getBoundingClientRect();
      const parentRect = scrollContainer.getBoundingClientRect();
      const linkLeft = rect.left - parentRect.left + scrollContainer.scrollLeft;
      const linkRight = rect.right - parentRect.left + scrollContainer.scrollLeft;

      if (linkRight < leftBoundary || linkLeft > rightBoundary) {
        link.classList.add('dimmed');
      } else {
        link.classList.remove('dimmed');
      }
    });
  };

  checkScroll();
  scrollContainer.addEventListener('scroll', checkScroll, { passive: true });
  window.addEventListener('resize', checkScroll);
});
