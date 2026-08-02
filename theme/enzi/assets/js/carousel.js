(function () {
  'use strict';

  const AUTOPLAY_INTERVAL_MS = 4000;
  const PAGE_SCROLL_END_MS = 180;
  const VISIBILITY_THRESHOLD = 0.2;

  let pageScrolling = false;
  let pageScrollEndTimer = null;
  const pageScrollEndListeners = new Set();

  function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  }

  function isRtl() {
    return document.documentElement.getAttribute('dir') === 'rtl';
  }

  function initPageScrollWatcher() {
    if (initPageScrollWatcher.done) {
      return;
    }

    initPageScrollWatcher.done = true;

    window.addEventListener(
      'scroll',
      () => {
        pageScrolling = true;
        clearTimeout(pageScrollEndTimer);
        pageScrollEndTimer = window.setTimeout(() => {
          pageScrolling = false;
          pageScrollEndListeners.forEach((listener) => listener());
        }, PAGE_SCROLL_END_MS);
      },
      { passive: true }
    );
  }

  function onPageScrollEnd(listener) {
    initPageScrollWatcher();
    pageScrollEndListeners.add(listener);
    return () => pageScrollEndListeners.delete(listener);
  }

  function isPageScrolling() {
    return pageScrolling;
  }

  function bindCarouselVisibility(root, onChange) {
    initPageScrollWatcher();

    let intersecting = false;

    const sync = () => {
      onChange(intersecting && !isPageScrolling());
    };

    const observer = new IntersectionObserver(
      (entries) => {
        const nextIntersecting = entries.some(
          (entry) => entry.isIntersecting && entry.intersectionRatio >= VISIBILITY_THRESHOLD
        );

        if (nextIntersecting === intersecting) {
          return;
        }

        intersecting = nextIntersecting;
        sync();
      },
      { threshold: [0, VISIBILITY_THRESHOLD, 0.35, 0.6] }
    );

    observer.observe(root);
    onPageScrollEnd(sync);

    requestAnimationFrame(() => {
      const rect = root.getBoundingClientRect();

      if (!rect.height) {
        return;
      }

      const visibleHeight = Math.max(
        0,
        Math.min(rect.bottom, window.innerHeight) - Math.max(rect.top, 0)
      );
      const nextIntersecting = visibleHeight / rect.height >= VISIBILITY_THRESHOLD;

      if (nextIntersecting !== intersecting) {
        intersecting = nextIntersecting;
        sync();
      }
    });

    return {
      isActive() {
        return intersecting && !isPageScrolling();
      },
      disconnect() {
        observer.disconnect();
      },
    };
  }

  function initFadeCarousel(root) {
    const slides = root.querySelectorAll('[data-carousel-slide]');
    const dotsWrap = root.querySelector('[data-carousel-dots]');
    const prevBtn = root.querySelector('[data-carousel-prev]');
    const nextBtn = root.querySelector('[data-carousel-next]');

    if (!slides.length) {
      return;
    }

    let index = 0;
    let timer = null;
    const dots = [];

    if (dotsWrap) {
      slides.forEach((_, i) => {
        const dot = document.createElement('button');
        dot.type = 'button';
        dot.className = 'diako-carousel__dot' + (i === 0 ? ' is-active' : '');
        dot.setAttribute('role', 'tab');
        dot.setAttribute('aria-label', 'اسلاید ' + (i + 1));
        dot.setAttribute('aria-selected', i === 0 ? 'true' : 'false');
        dot.addEventListener('click', (event) => {
          event.preventDefault();
          event.stopPropagation();
          go(i, true);
        });
        dotsWrap.appendChild(dot);
        dots.push(dot);
      });
    }

    function syncDots() {
      dots.forEach((dot, i) => {
        const active = i === index;
        dot.classList.toggle('is-active', active);
        dot.setAttribute('aria-selected', active ? 'true' : 'false');
      });
    }

    function go(nextIndex, userTriggered) {
      const target = (nextIndex + slides.length) % slides.length;
      if (target === index) {
        return;
      }

      slides[index].classList.remove('is-active');
      index = target;
      slides[index].classList.add('is-active');
      syncDots();

      if (userTriggered) {
        restartAutoplay();
      }
    }

    function next() {
      if (isPageScrolling()) {
        return;
      }
      go(index + 1, false);
    }

    function prev() {
      go(index - 1, false);
    }

    function stopAutoplay() {
      if (timer) {
        clearInterval(timer);
        timer = null;
      }
    }

    function startAutoplay() {
      if (prefersReducedMotion() || slides.length < 2 || !visibility.isActive()) {
        return;
      }
      stopAutoplay();
      timer = setInterval(next, AUTOPLAY_INTERVAL_MS);
    }

    function restartAutoplay() {
      stopAutoplay();
      startAutoplay();
    }

    const visibility = bindCarouselVisibility(root, (isActive) => {
      if (isActive) {
        startAutoplay();
        return;
      }

      stopAutoplay();
    });

    function onFadeArrowClick(event, direction) {
      event.preventDefault();
      event.stopPropagation();

      if (direction < 0) {
        prev();
      } else {
        next();
      }

      restartAutoplay();
      event.currentTarget.blur();
    }

    prevBtn?.addEventListener('click', (event) => onFadeArrowClick(event, -1));
    nextBtn?.addEventListener('click', (event) => onFadeArrowClick(event, 1));

    document.addEventListener('visibilitychange', () => {
      if (document.hidden) {
        stopAutoplay();
      } else if (visibility.isActive()) {
        startAutoplay();
      }
    });

    root.addEventListener('keydown', (event) => {
      if (event.key === 'ArrowLeft') {
        event.preventDefault();
        prev();
        restartAutoplay();
      } else if (event.key === 'ArrowRight') {
        event.preventDefault();
        next();
        restartAutoplay();
      }
    });

    let touchStartX = 0;
    root.addEventListener(
      'touchstart',
      (event) => {
        touchStartX = event.changedTouches[0]?.clientX ?? 0;
      },
      { passive: true }
    );
    root.addEventListener(
      'touchend',
      (event) => {
        const touchEndX = event.changedTouches[0]?.clientX ?? 0;
        const delta = touchEndX - touchStartX;
        if (Math.abs(delta) < 40) {
          return;
        }

        // Match finger direction on screen (same in LTR and RTL).
        if (delta > 0) {
          next();
        } else {
          prev();
        }
        restartAutoplay();
      },
      { passive: true }
    );

    root.addEventListener('mouseenter', stopAutoplay);
    root.addEventListener('mouseleave', () => {
      if (visibility.isActive()) {
        startAutoplay();
      }
    });
  }

  function initTrackCarousel(root) {
    const viewport = root.querySelector('[data-carousel-viewport]');
    const track = root.querySelector('[data-carousel-track]');
    const prevBtn = root.querySelector('[data-carousel-prev]');
    const nextBtn = root.querySelector('[data-carousel-next]');
    const dotsWrap = root.querySelector('[data-carousel-dots]');

    if (!track) {
      return;
    }

    const items = Array.from(track.children).filter((node) => node.matches('li'));

    if (!items.length) {
      return;
    }

    if (isRtl()) {
      track.setAttribute('dir', 'rtl');
    }

    let dots = [];
    let scrollRaf = null;
    let currentSlide = 0;
    let scrollLockUntil = 0;
    let timer = null;

    function isScrollLocked() {
      return Date.now() < scrollLockUntil;
    }

    function lockScrollSync(duration) {
      scrollLockUntil = Date.now() + duration;
    }

    function getClipElement() {
      return viewport || track;
    }

    function getVisibleEdgeRect() {
      return getClipElement().getBoundingClientRect();
    }

    function getViewportPaddingInline() {
      const styles = getComputedStyle(getClipElement());
      return parseFloat(styles.paddingInlineStart) || parseFloat(styles.paddingLeft) || 0;
    }

    function getTrackGap() {
      const styles = getComputedStyle(track);
      const gapVar = parseFloat(getComputedStyle(root).getPropertyValue('--carousel-gap'));
      return gapVar || parseFloat(styles.columnGap || styles.gap) || 16;
    }

    function getMinSlideWidth() {
      const raw = getComputedStyle(root).getPropertyValue('--carousel-min-slide-width').trim();

      if (!raw) {
        return 280;
      }

      if (raw.endsWith('px')) {
        return parseFloat(raw) || 280;
      }

      const probe = document.createElement('div');
      probe.style.cssText = 'position:absolute;visibility:hidden;width:' + raw;
      root.appendChild(probe);
      const width = probe.offsetWidth;
      probe.remove();
      return width || 280;
    }

    function computeSlidesPerView() {
      const clipWidth = getClipElement().clientWidth;

      if (!clipWidth) {
        return 1;
      }

      const gap = getTrackGap();
      const minWidth = getMinSlideWidth();

      return Math.max(1, Math.floor((clipWidth + gap) / (minWidth + gap)));
    }

    function syncCarouselCols() {
      const cols = computeSlidesPerView();
      root.style.setProperty('--carousel-cols', String(cols));
      return cols;
    }

    function getSlidesPerView() {
      const cols = parseInt(getComputedStyle(root).getPropertyValue('--carousel-cols'), 10);

      if (cols > 0) {
        return cols;
      }

      return syncCarouselCols();
    }

    function isScrollable() {
      return items.length > getSlidesPerView();
    }

    function syncScrollableState() {
      syncCarouselCols();
      const scrollable = isScrollable();

      root.classList.toggle('diako-carousel--scrollable', scrollable);
      root.classList.toggle('diako-carousel--static', !scrollable);

      if (scrollable) {
        root.setAttribute('aria-roledescription', 'carousel');
        root.tabIndex = 0;
        markSlideStarts();
        buildDots();
        updateControls(0);
        startAutoplay();
        return;
      }

      stopAutoplay();
      root.removeAttribute('aria-roledescription');
      root.tabIndex = -1;
      items.forEach((item) => item.classList.remove('diako-carousel__slide-start'));

      if (dotsWrap) {
        dotsWrap.innerHTML = '';
        dots = [];
      }

      track.scrollTo({ left: 0, behavior: 'auto' });
    }

    function getSlideCount() {
      const perView = getSlidesPerView();
      return Math.max(1, Math.ceil(items.length / perView));
    }

    function getSlideItemIndex(slideIndex) {
      const slideCount = getSlideCount();
      const perView = getSlidesPerView();
      const normalized = ((slideIndex % slideCount) + slideCount) % slideCount;
      const itemIndex = normalized * perView;

      return Math.min(itemIndex, items.length - 1);
    }

    function getActiveItemIndex() {
      const edgeRect = getVisibleEdgeRect();
      const anchorX = isRtl() ? edgeRect.right - getViewportPaddingInline() : edgeRect.left + getViewportPaddingInline();
      let closest = 0;
      let minDistance = Infinity;

      items.forEach((item, i) => {
        const itemRect = item.getBoundingClientRect();
        const itemAnchor = isRtl() ? itemRect.right : itemRect.left;
        const distance = Math.abs(itemAnchor - anchorX);

        if (distance < minDistance) {
          minDistance = distance;
          closest = i;
        }
      });

      return closest;
    }

    function getActiveSlideIndex() {
      const perView = getSlidesPerView();
      const slideCount = getSlideCount();

      return Math.min(Math.floor(getActiveItemIndex() / perView), slideCount - 1);
    }

    function markSlideStarts() {
      const perView = getSlidesPerView();

      items.forEach((item, i) => {
        item.classList.toggle('diako-carousel__slide-start', i % perView === 0);
      });
    }

    function syncDots(slideIndex) {
      if (!dots.length) {
        return;
      }

      dots.forEach((dot, i) => {
        const active = i === slideIndex;
        dot.classList.toggle('is-active', active);
        dot.setAttribute('aria-selected', active ? 'true' : 'false');
      });
    }

    function updateControls(forcedSlide) {
      const activeSlide = typeof forcedSlide === 'number' ? forcedSlide : getActiveSlideIndex();
      currentSlide = activeSlide;
      syncDots(activeSlide);
    }

    function getScrollDeltaForItem(target) {
      const edgeRect = getVisibleEdgeRect();
      const targetRect = target.getBoundingClientRect();
      const pad = getViewportPaddingInline();

      if (isRtl()) {
        return targetRect.right - (edgeRect.right - pad);
      }

      return targetRect.left - (edgeRect.left + pad);
    }

    function scrollToSlide(slideIndex, userTriggered) {
      if (!isScrollable()) {
        return;
      }

      if (!userTriggered && isPageScrolling()) {
        return;
      }

      const slideCount = getSlideCount();
      const normalized = ((slideIndex % slideCount) + slideCount) % slideCount;
      const target = items[getSlideItemIndex(normalized)];

      if (!target) {
        return;
      }

      const previousSlide = currentSlide;

      lockScrollSync(prefersReducedMotion() ? 48 : 560);
      updateControls(normalized);

      const delta = getScrollDeltaForItem(target);
      const edgeWidth = getVisibleEdgeRect().width;
      const isWrapJump =
        Math.abs(normalized - previousSlide) > 1 || Math.abs(delta) > edgeWidth * 1.25;
      const behavior = prefersReducedMotion() || isWrapJump ? 'auto' : 'smooth';

      if (Math.abs(delta) >= 1) {
        track.scrollBy({ left: delta, behavior });
      }

      window.setTimeout(() => {
        const remainder = getScrollDeltaForItem(target);

        if (Math.abs(remainder) >= 2) {
          track.scrollBy({ left: remainder, behavior: 'auto' });
        }

        scrollLockUntil = 0;
        updateControls(normalized);
      }, prefersReducedMotion() ? 32 : 520);

      if (userTriggered) {
        restartAutoplay();
      }
    }

    function next() {
      scrollToSlide(currentSlide + 1, false);
    }

    function prev() {
      scrollToSlide(currentSlide - 1, false);
    }

    function stopAutoplay() {
      if (timer) {
        clearInterval(timer);
        timer = null;
      }
    }

    function startAutoplay() {
      if (prefersReducedMotion() || !isScrollable() || !visibility.isActive()) {
        return;
      }
      stopAutoplay();
      timer = setInterval(next, AUTOPLAY_INTERVAL_MS);
    }

    function restartAutoplay() {
      stopAutoplay();
      startAutoplay();
    }

    const visibility = bindCarouselVisibility(root, (isActive) => {
      if (isActive) {
        startAutoplay();
        return;
      }

      stopAutoplay();
    });

    function buildDots() {
      if (!dotsWrap || !isScrollable()) {
        return;
      }

      dotsWrap.innerHTML = '';
      dots = [];

      const slideCount = getSlideCount();

      for (let i = 0; i < slideCount; i += 1) {
        const dot = document.createElement('button');
        dot.type = 'button';
        dot.className = 'diako-carousel__dot' + (i === 0 ? ' is-active' : '');
        dot.setAttribute('role', 'tab');
        dot.setAttribute('aria-label', 'اسلاید ' + (i + 1));
        dot.setAttribute('aria-selected', i === 0 ? 'true' : 'false');
        dot.addEventListener('click', (event) => {
          event.preventDefault();
          event.stopPropagation();
          scrollToSlide(i, true);
        });
        dotsWrap.appendChild(dot);
        dots.push(dot);
      }
    }

    function onScroll() {
      if (scrollRaf || isPageScrolling()) {
        return;
      }
      scrollRaf = requestAnimationFrame(() => {
        scrollRaf = null;
        if (!isScrollLocked()) {
          updateControls();
        }
      });
    }

    function onArrowClick(event, direction) {
      if (!isScrollable()) {
        return;
      }

      event.preventDefault();
      event.stopPropagation();

      if (direction < 0) {
        scrollToSlide(currentSlide - 1, true);
      } else {
        scrollToSlide(currentSlide + 1, true);
      }

      event.currentTarget.blur();
    }

    prevBtn?.addEventListener('click', (event) => onArrowClick(event, -1));
    nextBtn?.addEventListener('click', (event) => onArrowClick(event, 1));

    track.addEventListener('scroll', onScroll, { passive: true });

    if ('onscrollend' in track) {
      track.addEventListener(
        'scrollend',
        () => {
          scrollLockUntil = 0;
          updateControls();
        },
        { passive: true }
      );
    }

    root.addEventListener('mouseenter', stopAutoplay);
    root.addEventListener('mouseleave', () => {
      if (visibility.isActive()) {
        startAutoplay();
      }
    });

    document.addEventListener('visibilitychange', () => {
      if (document.hidden) {
        stopAutoplay();
      } else if (visibility.isActive() && !root.matches(':hover')) {
        startAutoplay();
      }
    });

    root.addEventListener('keydown', (event) => {
      if (!isScrollable()) {
        return;
      }

      if (event.key === 'ArrowLeft') {
        event.preventDefault();
        scrollToSlide(currentSlide - 1, true);
      } else if (event.key === 'ArrowRight') {
        event.preventDefault();
        scrollToSlide(currentSlide + 1, true);
      }
    });

    let touchStartX = 0;
    root.addEventListener(
      'touchstart',
      (event) => {
        touchStartX = event.changedTouches[0]?.clientX ?? 0;
      },
      { passive: true }
    );
    root.addEventListener(
      'touchend',
      (event) => {
        if (!isScrollable()) {
          return;
        }

        const touchEndX = event.changedTouches[0]?.clientX ?? 0;
        const delta = touchEndX - touchStartX;
        if (Math.abs(delta) < 40) {
          return;
        }

        // Match finger direction on screen (same in LTR and RTL).
        if (delta > 0) {
          scrollToSlide(currentSlide + 1, true);
        } else {
          scrollToSlide(currentSlide - 1, true);
        }
      },
      { passive: true }
    );

    let resizeTimer;
    window.addEventListener('resize', () => {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(() => {
        const wasScrollable = root.classList.contains('diako-carousel--scrollable');
        syncCarouselCols();
        syncScrollableState();

        if (root.classList.contains('diako-carousel--scrollable')) {
          scrollToSlide(Math.min(currentSlide, getSlideCount() - 1), false);
        } else if (wasScrollable) {
          currentSlide = 0;
        }
      }, 150);
    });

    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        syncScrollableState();
      });
    });
  }

  function initCarousels() {
    document.querySelectorAll('[data-diako-carousel="fade"]').forEach(initFadeCarousel);
    document.querySelectorAll('[data-diako-carousel="track"]').forEach(initTrackCarousel);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCarousels);
  } else {
    initCarousels();
  }
})();
