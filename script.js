// front-end behaviour for Ubuntu Market
// hero slider, mobile menu, filters, account dropdown etc

document.addEventListener('DOMContentLoaded', () => {
  // homepage hero carousel
  const heroCarousel = document.getElementById('heroCarousel');
  const heroTrack = document.getElementById('heroTrack');
  const heroSlidesEls = heroTrack
    ? Array.from(heroTrack.querySelectorAll('.hero-slide'))
    : [];
  const heroDots = Array.from(document.querySelectorAll('.hero-carousel .hero-dot'));
  const heroPrev = document.getElementById('heroPrev');
  const heroNext = document.getElementById('heroNext');

  const heroSlideCount = heroSlidesEls.length
    || parseInt(heroCarousel?.dataset.slideCount || '0', 10)
    || 0;

  let heroCurrent = 0;
  let heroInterval = null;
  let heroTouchStartX = 0;
  let heroReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  const heroAutoplayEnabled = () => {
    if (heroReducedMotion) return false;
    if (document.hidden) return false;
    return heroCarousel?.dataset.autoplay !== 'false' && heroSlideCount > 1;
  };

  const heroIntervalMs = () => {
    const ms = parseInt(heroCarousel?.dataset.interval || '5500', 10);
    return Number.isFinite(ms) && ms >= 3000 ? ms : 5500;
  };

  const setHeroIndex = (index) => {
    if (!heroTrack || heroSlideCount < 1) return;

    heroCurrent = ((index % heroSlideCount) + heroSlideCount) % heroSlideCount;
    heroTrack.style.setProperty('--hero-index', String(heroCurrent));
    heroTrack.dataset.index = String(heroCurrent);

    heroSlidesEls.forEach((slide, i) => {
      const active = i === heroCurrent;
      slide.classList.toggle('is-active', active);
      slide.setAttribute('aria-hidden', active ? 'false' : 'true');
      const link = slide.querySelector('.hero-slide-link');
      if (link) link.tabIndex = active ? 0 : -1;
    });

    heroDots.forEach((dot, i) => {
      const active = i === heroCurrent;
      dot.classList.toggle('active', active);
      dot.setAttribute('aria-selected', active ? 'true' : 'false');
    });

    if (heroCarousel) {
      heroCarousel.setAttribute('aria-label', `Featured deals, slide ${heroCurrent + 1} of ${heroSlideCount}`);
    }
  };

  const showNextHeroSlide = () => {
    if (heroSlideCount < 2) return;
    setHeroIndex(heroCurrent + 1);
  };

  const showPreviousHeroSlide = () => {
    if (heroSlideCount < 2) return;
    setHeroIndex(heroCurrent - 1);
  };

  const stopHeroTimer = () => {
    if (heroInterval) {
      clearInterval(heroInterval);
      heroInterval = null;
    }
  };

  const startHeroTimer = () => {
    stopHeroTimer();
    if (!heroAutoplayEnabled()) return;
    heroInterval = setInterval(showNextHeroSlide, heroIntervalMs());
  };

  const resetHeroTimer = () => {
    stopHeroTimer();
    startHeroTimer();
  };

  if (heroTrack && heroSlideCount > 0) {
    setHeroIndex(0);
    startHeroTimer();

    heroDots.forEach((dot) => {
      dot.addEventListener('click', () => {
        setHeroIndex(parseInt(dot.dataset.index || '0', 10));
        resetHeroTimer();
      });
    });

    heroPrev?.addEventListener('click', (event) => {
      event.preventDefault();
      showPreviousHeroSlide();
      resetHeroTimer();
    });

    heroNext?.addEventListener('click', (event) => {
      event.preventDefault();
      showNextHeroSlide();
      resetHeroTimer();
    });

    heroCarousel?.addEventListener('mouseenter', stopHeroTimer);
    heroCarousel?.addEventListener('mouseleave', startHeroTimer);

    heroCarousel?.addEventListener('focusin', stopHeroTimer);
    heroCarousel?.addEventListener('focusout', (event) => {
      if (!heroCarousel.contains(event.relatedTarget)) {
        startHeroTimer();
      }
    });

    document.addEventListener('visibilitychange', () => {
      if (document.hidden) {
        stopHeroTimer();
      } else {
        startHeroTimer();
      }
    });

    window.matchMedia('(prefers-reduced-motion: reduce)').addEventListener('change', (event) => {
      heroReducedMotion = event.matches;
      if (heroReducedMotion) {
        stopHeroTimer();
      } else {
        startHeroTimer();
      }
    });

    const heroFrame = document.getElementById('heroCarouselFrame');
    heroFrame?.addEventListener(
      'touchstart',
      (event) => {
        heroTouchStartX = event.changedTouches[0]?.clientX ?? 0;
      },
      { passive: true }
    );

    heroFrame?.addEventListener(
      'touchend',
      (event) => {
        const endX = event.changedTouches[0]?.clientX ?? 0;
        const delta = endX - heroTouchStartX;
        if (Math.abs(delta) < 48) return;
        if (delta < 0) {
          showNextHeroSlide();
        } else {
          showPreviousHeroSlide();
        }
        resetHeroTimer();
      },
      { passive: true }
    );

    heroCarousel?.addEventListener('keydown', (event) => {
      if (event.key === 'ArrowLeft') {
        event.preventDefault();
        showPreviousHeroSlide();
        resetHeroTimer();
      } else if (event.key === 'ArrowRight') {
        event.preventDefault();
        showNextHeroSlide();
        resetHeroTimer();
      }
    });
  }

  // hide/show header when scrolling down the page
  const siteHeader = document.getElementById('siteHeader');
  const navbar = document.querySelector('.navbar');
  const categoryLinks = Array.from(document.querySelectorAll('.category-bar a, .category-bar .category-pill'));
  const searchBar = document.querySelector('.search-bar');
  const searchInput = document.querySelector('.search-bar input');

  const SCROLL_DIR_THRESHOLD = 6;
  const SCROLL_HIDE_AFTER = 72;
  let lastScrollY = window.scrollY;
  let scrollTicking = false;

  const headerShouldStayVisible = () => {
    if (document.body.classList.contains('filter-drawer-open')) return true;
    const mobileNavDrawerEl = document.getElementById('mobileNavDrawer');
    if (mobileNavDrawerEl?.classList.contains('open')) return true;
    const accountMenuEl = document.getElementById('accountMenu');
    if (accountMenuEl?.classList.contains('open')) return true;
    return false;
  };

  const updateHeaderOnScroll = () => {
    const currentY = window.scrollY;

    if (siteHeader) {
      if (headerShouldStayVisible() || currentY <= SCROLL_HIDE_AFTER) {
        siteHeader.classList.remove('header-hidden');
      } else if (currentY > lastScrollY + SCROLL_DIR_THRESHOLD) {
        siteHeader.classList.add('header-hidden');
      } else if (currentY < lastScrollY - SCROLL_DIR_THRESHOLD) {
        siteHeader.classList.remove('header-hidden');
      }
    }

    navbar?.classList.toggle('scrolled', currentY > 10);
    lastScrollY = currentY;
  };

  if (siteHeader || navbar) {
    const onScroll = () => {
      if (scrollTicking) return;
      scrollTicking = true;
      requestAnimationFrame(() => {
        updateHeaderOnScroll();
        scrollTicking = false;
      });
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  if (categoryLinks.length) {
    const params = new URLSearchParams(window.location.search);
    const searchParam = (params.get('search') || '').trim().toLowerCase();
    categoryLinks.forEach((link) => {
      link.classList.remove('active');
      try {
        const u = new URL(link.href);
        const linkSearch = (u.searchParams.get('search') || '').trim().toLowerCase();
        if (searchParam && linkSearch === searchParam) {
          link.classList.add('active');
        }
      } catch (e) {
      }
    });
  }

  if (searchInput && searchBar) {
    searchInput.addEventListener('focus', () => searchBar.classList.add('focused'));
    searchInput.addEventListener('blur', () => searchBar.classList.remove('focused'));
  }

  const filterToggle = document.getElementById('filterToggle');
  const filterPanel = document.getElementById('filterPanel') || document.querySelector('.filter-panel');
  const filterBackdrop = document.getElementById('filterBackdrop');
  const filterForm = document.getElementById('filterForm');
  const syncSiteHeaderHeight = () => {
    const siteHeader = document.getElementById('siteHeader');
    if (!siteHeader) return;
    const height = Math.ceil(siteHeader.getBoundingClientRect().height);
    document.documentElement.style.setProperty('--site-header-height', `${height}px`);
  };

  syncSiteHeaderHeight();
  window.addEventListener('resize', syncSiteHeaderHeight);
  window.addEventListener('orientationchange', syncSiteHeaderHeight);

  const closeFilterPanel = () => {
    filterPanel?.classList.remove('open');
    filterBackdrop?.classList.remove('open');
    filterToggle?.setAttribute('aria-expanded', 'false');
    document.body.classList.remove('filter-drawer-open');
  };

  const revealSiteHeader = () => {
    document.getElementById('siteHeader')?.classList.remove('header-hidden');
  };

  const openFilterPanel = () => {
    syncSiteHeaderHeight();
    revealSiteHeader();
    filterPanel?.classList.add('open');
    filterBackdrop?.classList.add('open');
    filterToggle?.setAttribute('aria-expanded', 'true');
    document.body.classList.add('filter-drawer-open');
  };

  if (filterToggle && filterPanel) {
    filterToggle.addEventListener('click', () => {
      if (filterPanel.classList.contains('open')) {
        closeFilterPanel();
      } else {
        openFilterPanel();
      }
    });
  }

  document.querySelector('.filter-panel-close')?.addEventListener('click', closeFilterPanel);
  filterBackdrop?.addEventListener('click', closeFilterPanel);

  filterForm?.addEventListener('submit', () => {
    if (window.innerWidth <= 1050) {
      closeFilterPanel();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && filterPanel?.classList.contains('open')) {
      closeFilterPanel();
    }
  });

  window.addEventListener('resize', () => {
    syncSiteHeaderHeight();
    if (window.innerWidth > 1050) {
      closeFilterPanel();
    }
  });

  // logged-in account dropdown in the header
  const accountMenu = document.getElementById('accountMenu');
  const accountMenuTrigger = document.getElementById('accountMenuTrigger');
  const accountDropdown = document.getElementById('accountDropdown');

  const closeAccountMenu = () => {
    accountMenu?.classList.remove('open');
    accountMenuTrigger?.setAttribute('aria-expanded', 'false');
    accountDropdown?.setAttribute('hidden', '');
  };

  const openAccountMenu = () => {
    accountMenu?.classList.add('open');
    accountMenuTrigger?.setAttribute('aria-expanded', 'true');
    accountDropdown?.removeAttribute('hidden');
  };

  const toggleAccountMenu = () => {
    if (accountMenu?.classList.contains('open')) {
      closeAccountMenu();
    } else {
      openAccountMenu();
    }
  };

  if (accountMenu && accountMenuTrigger && accountDropdown) {
    accountMenuTrigger.addEventListener('click', (event) => {
      event.preventDefault();
      event.stopPropagation();
      toggleAccountMenu();
    });

    accountDropdown.querySelectorAll('a[role="menuitem"]').forEach((link) => {
      link.addEventListener('click', () => {
        closeAccountMenu();
      });
    });

    document.addEventListener('click', (event) => {
      if (!accountMenu.contains(event.target)) {
        closeAccountMenu();
      }
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') {
        closeAccountMenu();
        accountMenuTrigger.focus();
      }
    });
  }

  // mobile hamburger menu (separate drawer — not the header icon row)
  const mobileNavToggle = document.getElementById('mobileNavToggle');
  const mobileNavBackdrop = document.getElementById('mobileNavBackdrop');
  const mobileNavDrawer = document.getElementById('mobileNavDrawer');

  const closeMobileNav = () => {
    mobileNavDrawer?.classList.remove('open');
    mobileNavDrawer?.setAttribute('aria-hidden', 'true');
    mobileNavBackdrop?.classList.remove('open');
    mobileNavBackdrop?.setAttribute('aria-hidden', 'true');
    mobileNavToggle?.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
  };

  const openMobileNav = () => {
    closeAccountMenu();
    revealSiteHeader();
    mobileNavDrawer?.classList.add('open');
    mobileNavDrawer?.setAttribute('aria-hidden', 'false');
    mobileNavBackdrop?.classList.add('open');
    mobileNavBackdrop?.setAttribute('aria-hidden', 'false');
    mobileNavToggle?.setAttribute('aria-expanded', 'true');
    if (window.innerWidth <= 960) {
      document.body.style.overflow = 'hidden';
    }
  };

  mobileNavToggle?.addEventListener('click', () => {
    if (mobileNavDrawer?.classList.contains('open')) {
      closeMobileNav();
    } else {
      openMobileNav();
    }
  });

  mobileNavBackdrop?.addEventListener('click', () => {
    closeMobileNav();
    closeAccountMenu();
  });

  mobileNavDrawer?.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => {
      closeMobileNav();
    });
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && mobileNavDrawer?.classList.contains('open')) {
      closeMobileNav();
      mobileNavToggle?.focus();
    }
  });

  window.addEventListener('resize', () => {
    if (window.innerWidth > 960) {
      closeMobileNav();
    }
  });

  const brandsViewport = document.getElementById('brandsViewport');
  const brandsPrev = document.getElementById('brandsPrev');
  const brandsNext = document.getElementById('brandsNext');
  const brandsProgress = document.getElementById('brandsProgress');
  const brandsProgressFill = document.getElementById('brandsProgressFill');
  const brandsProgressBar = document.getElementById('brandsProgressBar');

  const updateBrandsCarousel = () => {
    if (!brandsViewport) return;

    const maxScroll = brandsViewport.scrollWidth - brandsViewport.clientWidth;
    const hasOverflow = maxScroll > 4;

    if (brandsProgress) {
      brandsProgress.hidden = !hasOverflow;
    }

    if (brandsPrev) {
      brandsPrev.disabled = brandsViewport.scrollLeft <= 2;
    }
    if (brandsNext) {
      brandsNext.disabled = brandsViewport.scrollLeft >= maxScroll - 2;
    }

    if (brandsProgressFill && hasOverflow) {
      const ratio = brandsViewport.clientWidth / brandsViewport.scrollWidth;
      const fillWidth = Math.max(12, ratio * 100);
      const offset = (brandsViewport.scrollLeft / maxScroll) * (100 - fillWidth);
      brandsProgressFill.style.width = `${fillWidth}%`;
      brandsProgressFill.style.marginLeft = `${offset}%`;
      if (brandsProgressBar) {
        const progress = Math.round((brandsViewport.scrollLeft / maxScroll) * 100);
        brandsProgressBar.setAttribute('aria-valuenow', String(progress));
      }
    }
  };

  const scrollBrands = (direction) => {
    if (!brandsViewport) return;
    const amount = Math.min(280, brandsViewport.clientWidth * 0.75);
    brandsViewport.scrollBy({ left: direction * amount, behavior: 'smooth' });
  };

  if (brandsViewport) {
    brandsViewport.addEventListener('scroll', updateBrandsCarousel, { passive: true });
    brandsPrev?.addEventListener('click', () => scrollBrands(-1));
    brandsNext?.addEventListener('click', () => scrollBrands(1));
    window.addEventListener('resize', updateBrandsCarousel);
    updateBrandsCarousel();
  }

  const portalSidebar = document.getElementById('portalSidebar');
  const portalSidebarToggle = document.getElementById('portalSidebarToggle');
  const portalBackdrop = document.getElementById('portalBackdrop');

  const closePortalSidebar = () => {
    portalSidebar?.classList.remove('open');
    portalBackdrop?.classList.remove('open');
  };

  portalSidebarToggle?.addEventListener('click', () => {
    portalSidebar?.classList.toggle('open');
    portalBackdrop?.classList.toggle('open');
  });

  portalBackdrop?.addEventListener('click', closePortalSidebar);

  const registerForm = document.getElementById('registerForm');
  if (registerForm) {
    registerForm.addEventListener('submit', (event) => {
      const password = registerForm.querySelector('#password');
      const confirm = registerForm.querySelector('#confirm_password');
      if (password && confirm && password.value !== confirm.value) {
        event.preventDefault();
        confirm.setCustomValidity('Passwords do not match');
        confirm.reportValidity();
      } else if (confirm) {
        confirm.setCustomValidity('');
      }
    });
  }

  // star rating picker on the product review form
  const reviewForm = document.getElementById('reviewComposeForm');
  const ratingInput = document.getElementById('reviewRating');
  const ratingGroup = document.querySelector('[data-rating-input]');
  const ratingHint = document.getElementById('starRatingHint');
  const reviewComment = document.getElementById('comment');
  const reviewCharCount = document.getElementById('reviewCharCount');

  if (ratingGroup && ratingInput) {
    const starButtons = Array.from(ratingGroup.querySelectorAll('.star-rating-btn'));
    const hintLabels = ['', 'Poor', 'Fair', 'Good', 'Very good', 'Excellent'];

    const setRating = (value, { announce = true } = {}) => {
      const rating = parseInt(value, 10) || 0;
      ratingInput.value = rating > 0 ? String(rating) : '';
      starButtons.forEach((btn) => {
        const starVal = parseInt(btn.dataset.value, 10);
        btn.classList.toggle('is-active', rating > 0 && starVal <= rating);
        btn.setAttribute('aria-pressed', starVal <= rating && rating > 0 ? 'true' : 'false');
      });
      if (ratingHint) {
        if (rating > 0) {
          ratingHint.textContent = `${rating} out of 5 — ${hintLabels[rating]}`;
          ratingHint.classList.add('is-set');
        } else {
          ratingHint.textContent = 'Select a star rating';
          ratingHint.classList.remove('is-set');
        }
      }
      if (announce && rating > 0) {
        ratingInput.setCustomValidity('');
      }
    };

    const setHover = (value) => {
      const hover = parseInt(value, 10) || 0;
      if (hover > 0) {
        ratingGroup.dataset.hover = '1';
      } else {
        delete ratingGroup.dataset.hover;
      }
      starButtons.forEach((btn) => {
        const starVal = parseInt(btn.dataset.value, 10);
        btn.classList.toggle('is-hover', hover > 0 && starVal <= hover);
      });
    };

    starButtons.forEach((btn) => {
      btn.addEventListener('click', () => setRating(btn.dataset.value));
      btn.addEventListener('mouseenter', () => setHover(btn.dataset.value));
      btn.addEventListener('focus', () => setHover(btn.dataset.value));
    });

    ratingGroup.addEventListener('mouseleave', () => setHover(0));
    ratingGroup.addEventListener('keydown', (event) => {
      const current = parseInt(ratingInput.value, 10) || 0;
      if (event.key === 'ArrowRight' || event.key === 'ArrowUp') {
        event.preventDefault();
        setRating(Math.min(5, current + 1));
      } else if (event.key === 'ArrowLeft' || event.key === 'ArrowDown') {
        event.preventDefault();
        setRating(Math.max(1, current - 1));
      }
    });
  }

  if (reviewComment && reviewCharCount) {
    const updateCount = () => {
      reviewCharCount.textContent = String(reviewComment.value.length);
    };
    reviewComment.addEventListener('input', updateCount);
    updateCount();
  }

  reviewForm?.addEventListener('submit', (event) => {
    if (!ratingInput?.value) {
      event.preventDefault();
      ratingInput?.setCustomValidity('Please select a star rating.');
      ratingInput?.reportValidity();
      ratingHint?.classList.remove('is-set');
      if (ratingHint) {
        ratingHint.textContent = 'Please select a star rating before submitting.';
      }
    } else {
      ratingInput.setCustomValidity('');
    }
  });

  document.querySelectorAll('form.auth-form input[required]').forEach((input) => {
    input.addEventListener('invalid', () => {
      input.classList.add('input-invalid');
    });
    input.addEventListener('input', () => {
      input.classList.remove('input-invalid');
    });
  });
});
