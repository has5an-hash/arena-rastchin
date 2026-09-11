/**
 * Beautia — front-end interactions.
 * Vanilla JS, no jQuery, no external libraries.
 */
(function () {
  'use strict';

  var d = document;
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function $(s, c) { return (c || d).querySelector(s); }
  function $$(s, c) { return Array.prototype.slice.call((c || d).querySelectorAll(s)); }

  /* ---------------------------------------------------------------- */
  /* Preloader                                                         */
  /* ---------------------------------------------------------------- */
  window.addEventListener('load', function () {
    var pre = $('#beautia-preloader');
    if (pre) { setTimeout(function () { pre.classList.add('is-done'); }, 350); }
  });

  /* ---------------------------------------------------------------- */
  /* Copy-to-clipboard buttons (article share, booking codes)          */
  /* ---------------------------------------------------------------- */
  d.addEventListener('click', function (e) {
    var btn = e.target.closest ? e.target.closest('[data-copy]') : null;
    if (!btn) { return; }
    var text = btn.getAttribute('data-copy');
    var done = function () {
      var old = btn.textContent;
      btn.textContent = (BeautiaData.i18n && BeautiaData.i18n.copied) || '✓';
      setTimeout(function () { btn.textContent = old; }, 1800);
    };
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(text).then(done);
    } else {
      var ta = d.createElement('textarea');
      ta.value = text; d.body.appendChild(ta); ta.select();
      try { d.execCommand('copy'); done(); } catch (err) {}
      d.body.removeChild(ta);
    }
  });

  /* ---------------------------------------------------------------- */
  /* Dark / light mode                                                 */
  /* ---------------------------------------------------------------- */
  (function () {
    var root = d.documentElement;
    var toggle = $('#beautia-theme-toggle');
    var media = window.matchMedia('(prefers-color-scheme: dark)');

    function apply(theme, persist) {
      if (theme === 'dark') { root.setAttribute('data-theme', 'dark'); }
      else { root.removeAttribute('data-theme'); }
      if (persist) {
        try { localStorage.setItem('beautia-theme', theme); } catch (e) {}
      }
      if (toggle) {
        toggle.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
        toggle.setAttribute('aria-label', theme === 'dark' ? 'فعال‌کردن حالت روشن' : 'فعال‌کردن حالت شب');
        toggle.setAttribute('title', theme === 'dark' ? 'حالت روشن' : 'حالت شب');
      }
      d.dispatchEvent(new CustomEvent('beautia:themechange', { detail: { theme: theme } }));
    }

    // Follow the OS while the visitor has not made an explicit choice.
    if (media.addEventListener) {
      media.addEventListener('change', function (e) {
        var saved = null;
        try { saved = localStorage.getItem('beautia-theme'); } catch (err) {}
        if (!saved) { apply(e.matches ? 'dark' : 'light', false); }
      });
    }

    if (toggle) {
      apply(root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light', false);
      toggle.addEventListener('click', function () {
        apply(root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark', true);
      });
    }
  })();

  /* ---------------------------------------------------------------- */
  /* Sticky header + back to top                                       */
  /* ---------------------------------------------------------------- */
  var header = $('.site-header');
  var toTop = $('#beautia-top');

  /* Reading progress bar — a small touch that makes long pages feel premium. */
  var progress = null;
  if (!reduce) {
    progress = d.createElement('div');
    progress.className = 'beautia-progress';
    progress.setAttribute('aria-hidden', 'true');
    d.body.appendChild(progress);
  }

  var ticking = false;
  function onScroll() {
    var y = window.pageYOffset;
    if (header) { header.classList.toggle('is-scrolled', y > 40); }
    if (toTop) { toTop.classList.toggle('is-visible', y > 600); }
    if (progress) {
      var max = d.documentElement.scrollHeight - window.innerHeight;
      progress.style.width = (max > 0 ? (y / max) * 100 : 0) + '%';
    }
    ticking = false;
  }
  function requestScroll() {
    if (ticking) { return; }
    ticking = true;
    window.requestAnimationFrame(onScroll);
  }
  window.addEventListener('scroll', requestScroll, { passive: true });
  window.addEventListener('resize', requestScroll, { passive: true });
  onScroll();
  if (toTop) {
    toTop.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' });
    });
  }

  /* ---------------------------------------------------------------- */
  /* Desktop mega menu hover intent                                   */
  /* ---------------------------------------------------------------- */
  $$('.main-nav .beautia-mega').forEach(function (item) {
    var openTimer = null;
    var closeTimer = null;
    item.addEventListener('mouseenter', function () {
      if (closeTimer) { window.clearTimeout(closeTimer); }
      if (openTimer) { window.clearTimeout(openTimer); }
      openTimer = window.setTimeout(function () {
        item.classList.add('is-hovering');
        openTimer = null;
      }, 280);
    });
    item.addEventListener('mouseleave', function () {
      if (openTimer) { window.clearTimeout(openTimer); openTimer = null; }
      if (closeTimer) { window.clearTimeout(closeTimer); }
      closeTimer = window.setTimeout(function () {
        item.classList.remove('is-hovering');
        closeTimer = null;
      }, 380);
    });
  });

  /* ---------------------------------------------------------------- */
  /* Mobile nav + search                                               */
  /* ---------------------------------------------------------------- */
  var burger = $('#beautia-burger');
  var mnav = $('#beautia-mobile-nav');
  if (burger && mnav) {
    var closeMobileNav = function () { mnav.classList.remove('is-open'); burger.classList.remove('is-open'); burger.setAttribute('aria-expanded','false'); mnav.setAttribute('aria-hidden','true'); d.body.classList.remove('mobile-nav-open'); d.body.style.overflow=''; };
    burger.addEventListener('click', function () {
      var open = mnav.classList.toggle('is-open');
      burger.classList.toggle('is-open', open);
      burger.setAttribute('aria-expanded', open ? 'true' : 'false');
      mnav.setAttribute('aria-hidden', open ? 'false' : 'true');
      d.body.classList.toggle('mobile-nav-open', open);
      d.body.style.overflow = open ? 'hidden' : '';
    });
    $$('a', mnav).forEach(function (a) {
      a.addEventListener('click', closeMobileNav);
    });
    var mobileClose = $('.mobile-nav-close', mnav);
    if (mobileClose) mobileClose.addEventListener('click', closeMobileNav);
  }
  var sToggle = $('.search-toggle');
  var sBox = $('#beautia-search');
  if (sToggle && sBox) {
    var sInput = $('#beautia-live-search', sBox);
    var sResults = $('#beautia-search-suggestions', sBox);
    var sClose = $('.search-close', sBox);
    var searchTimer;
    var renderSearchMessage = function (message) {
      var paragraph = d.createElement('p');
      paragraph.textContent = message;
      sResults.replaceChildren(paragraph);
    };
    var safeWebUrl = function (value) {
      try {
        var parsed = new URL(String(value || ''), window.location.href);
        return /^(https?:)$/.test(parsed.protocol) ? parsed.href : '';
      } catch (e) {
        return '';
      }
    };
    var renderSearchItems = function (items) {
      var fragment = d.createDocumentFragment();
      items.forEach(function (item) {
        var url = safeWebUrl(item.url);
        if (!url) { return; }
        var link = d.createElement('a');
        link.className = 'search-suggestion';
        link.href = url;

        var imageUrl = safeWebUrl(item.image);
        if (imageUrl) {
          var img = d.createElement('img');
          img.src = imageUrl;
          img.alt = '';
          link.appendChild(img);
        } else {
          var placeholder = d.createElement('span');
          placeholder.className = 'search-suggestion-placeholder';
          placeholder.textContent = '✦';
          link.appendChild(placeholder);
        }

        var title = d.createElement('strong');
        title.textContent = String(item.title || '');
        link.appendChild(title);
        var type = d.createElement('small');
        type.textContent = String(item.type || '');
        link.appendChild(type);
        fragment.appendChild(link);
      });
      if (!fragment.childNodes.length) {
        renderSearchMessage('نتیجه‌ای پیدا نشد؛ عبارت کوتاه‌تری امتحان کنید.');
        return;
      }
      sResults.replaceChildren(fragment);
    };
    var closeSearch = function () { sBox.classList.remove('is-open'); sToggle.setAttribute('aria-expanded','false'); };
    sToggle.addEventListener('click', function () {
      sBox.classList.toggle('is-open');
      sToggle.setAttribute('aria-expanded',sBox.classList.contains('is-open')?'true':'false');
      var f = $('.search-field', sBox);
      if (f && sBox.classList.contains('is-open')) { setTimeout(function () { f.focus(); }, 260); }
    });
    if(sClose) sClose.addEventListener('click',closeSearch);
    d.addEventListener('keydown',function(e){if(e.key==='Escape')closeSearch();});
    if(sInput&&sResults) sInput.addEventListener('input',function(){
      clearTimeout(searchTimer);var q=sInput.value.trim();
      if(q.length<2){renderSearchMessage('حداقل دو حرف بنویسید؛ مثلاً «کوتاهی» یا «فیشیال».');return;}
      searchTimer=setTimeout(function(){
        var url=BeautiaData.ajaxUrl+'?action=beautia_live_search&nonce='+encodeURIComponent(BeautiaData.nonce)+'&q='+encodeURIComponent(q);
        fetch(url,{credentials:'same-origin'}).then(function(r){return r.json();}).then(function(res){
          var items=res&&res.success?res.data:[];
          if(!items.length){renderSearchMessage('نتیجه‌ای پیدا نشد؛ عبارت کوتاه‌تری امتحان کنید.');return;}
          renderSearchItems(items);
        }).catch(function(){renderSearchMessage('جستجو در دسترس نیست؛ دوباره تلاش کنید.');});
      },220);
    });
  }

  /* ---------------------------------------------------------------- */
  /* Scroll reveal + character split + counters                        */
  /* ---------------------------------------------------------------- */
  /**
   * Reveal headings word by word.
   *
   * Persian (and every Arabic-script language) shapes its letters according to
   * their neighbours, so splitting a heading per character - the usual trick in
   * Latin themes - disconnects the word and allows a line break in the middle
   * of it. We therefore split on spaces only and keep each word unbreakable.
   */
  function splitWords(el) {
    if (el.dataset.split === '1') { return; }
    el.dataset.split = '1';
    var parts = el.innerHTML.split(/(<br\s*\/?>)/i);
    var out = '';
    var i = 0;
    parts.forEach(function (part) {
      if (/^<br/i.test(part)) { out += part; return; }
      part.split(/(\s+)/).forEach(function (chunk) {
        if (chunk === '') { return; }
        if (/^\s+$/.test(chunk)) { out += ' '; return; }
        out += '<span class="word" style="transition-delay:' + (i * 90) + 'ms">' + chunk + '</span>';
        i++;
      });
    });
    el.innerHTML = out;
  }

  function animateCount(el) {
    var target = parseFloat(el.dataset.count || '0');
    var decimals = parseInt(el.dataset.decimals || '0', 10);
    var suffix = el.dataset.suffix || '';
    var dur = 1600;
    var start = performance.now();
    function tick(now) {
      var p = Math.min(1, (now - start) / dur);
      var eased = 1 - Math.pow(1 - p, 3);
      var val = target * eased;
      var out = decimals ? val.toFixed(decimals) : Math.round(val).toLocaleString('en-US');
      if (window.BeautiaData && BeautiaData.faDigits) {
        out = Beautia.toFaDigits(out).replace(/,/g, '٬').replace(/\./g, '٫');
      }
      el.textContent = out + suffix;
      if (p < 1) { requestAnimationFrame(tick); }
    }
    requestAnimationFrame(tick);
  }

  var io = ('IntersectionObserver' in window) ? new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (!entry.isIntersecting) { return; }
      var el = entry.target;
      var delay = parseInt(el.dataset.delay || '0', 10);
      setTimeout(function () { el.classList.add('is-in'); }, delay);
      if (el.hasAttribute('data-count')) { animateCount(el); }
      io.unobserve(el);
    });
  }, { threshold: 0.15, rootMargin: '0px 0px -60px' }) : null;

  $$('[data-anim]').forEach(function (el) {
    if (el.dataset.anim === 'chars' || el.dataset.anim === 'words') { splitWords(el); el.classList.add('split'); }
    if (io && !reduce) { io.observe(el); } else { el.classList.add('is-in'); }
  });
  $$('[data-count]').forEach(function (el) {
    if (io && !reduce) {
      io.observe(el);
    } else {
      el.textContent = (window.BeautiaData && BeautiaData.faDigits)
        ? Beautia.toFaDigits(el.dataset.count).replace(/\./g, '٫')
        : el.dataset.count;
    }
  });

  /* ---------------------------------------------------------------- */
  /* Parallax                                                          */
  /* ---------------------------------------------------------------- */
  var parallax = $$('[data-parallax]');
  if (parallax.length && !reduce) {
    var ticking = false;
    window.addEventListener('scroll', function () {
      if (ticking) { return; }
      ticking = true;
      requestAnimationFrame(function () {
        parallax.forEach(function (el) {
          var rect = el.getBoundingClientRect();
          if (rect.bottom < -200 || rect.top > window.innerHeight + 200) { return; }
          var speed = parseFloat(el.dataset.parallax) || 0.15;
          var offset = (rect.top - window.innerHeight / 2) * speed;
          el.style.transform = 'translate3d(0,' + offset.toFixed(1) + 'px,0)';
        });
        ticking = false;
      });
    }, { passive: true });
  }

  /* ---------------------------------------------------------------- */
  /* Tilt cards                                                        */
  /* ---------------------------------------------------------------- */
  if (!reduce && window.matchMedia('(hover:hover)').matches) {
    $$('.tilt').forEach(function (card) {
      card.addEventListener('mousemove', function (e) {
        var r = card.getBoundingClientRect();
        var x = (e.clientX - r.left) / r.width - 0.5;
        var y = (e.clientY - r.top) / r.height - 0.5;
        card.style.transform = 'perspective(900px) rotateX(' + (-y * 4).toFixed(2) + 'deg) rotateY(' + (x * 5).toFixed(2) + 'deg) translateY(-8px)';
      });
      card.addEventListener('mouseleave', function () { card.style.transform = ''; });
    });
  }

  /* ---------------------------------------------------------------- */
  /* Custom cursor                                                     */
  /* ---------------------------------------------------------------- */
  var cursor = $('#beautia-cursor');
  if (cursor && window.matchMedia('(hover:hover)').matches) {
    var cx = 0, cy = 0, tx = 0, ty = 0;
    d.addEventListener('mousemove', function (e) { tx = e.clientX; ty = e.clientY; });
    (function loop() {
      cx += (tx - cx) * 0.18; cy += (ty - cy) * 0.18;
      cursor.style.transform = 'translate(' + cx + 'px,' + cy + 'px) translate(-50%,-50%)';
      requestAnimationFrame(loop);
    })();
    $$('a,button,.pick,.slot,.filter').forEach(function (el) {
      el.addEventListener('mouseenter', function () { cursor.classList.add('is-hover'); });
      el.addEventListener('mouseleave', function () { cursor.classList.remove('is-hover'); });
    });
  }

  /* ---------------------------------------------------------------- */
  /* Portfolio filter                                                  */
  /* ---------------------------------------------------------------- */
  $$('.filters').forEach(function (bar) {
    var items = $$('.folio');
    $$('.filter', bar).forEach(function (btn) {
      btn.addEventListener('click', function () {
        $$('.filter', bar).forEach(function (b) { b.classList.remove('is-active'); });
        btn.classList.add('is-active');
        var f = btn.dataset.filter;
        items.forEach(function (item) {
          var show = f === '*' || (item.dataset.cats || '').split(' ').indexOf(f) > -1;
          item.classList.toggle('is-hidden', !show);
          if (show) {
            item.style.animation = 'none';
            void item.offsetWidth;
            item.style.animation = 'paneIn .5s var(--ease)';
          }
        });
      });
    });
  });

  /* ---------------------------------------------------------------- */
  /* Lightbox                                                          */
  /* ---------------------------------------------------------------- */
  var lb = $('#beautia-lightbox');
  if (lb) {
    var lbStage = $('.lightbox-stage', lb);
    $$('[data-lightbox]').forEach(function (a) {
      a.addEventListener('click', function (e) {
        e.preventDefault();
        if(lbStage){var lbImg=d.createElement('img');lbImg.alt=a.querySelector('img')?.alt||'';lbImg.src=a.getAttribute('href');lbStage.replaceChildren(lbImg);}
        lb.hidden = false;
      });
    });
    var closeLightbox=function(){lb.hidden=true;if(lbStage)lbStage.replaceChildren();};
    lb.addEventListener('click', closeLightbox);
    d.addEventListener('keyup', function (e) { if (e.key === 'Escape') closeLightbox(); });
  }

  /* ---------------------------------------------------------------- */
  /* Slider                                                            */
  /* ---------------------------------------------------------------- */
  $$('[data-slider]').forEach(function (slider) {
    var track = $('.slider-track', slider);
    var slides = $$('.slide', track);
    var dots = $('.slider-dots', slider);
    var index = 0;
    if (!slides.length) { return; }

    function perView() {
      var w = window.innerWidth;
      return w > 1024 ? 3 : (w > 640 ? 2 : 1);
    }
    function pages() { return Math.max(1, Math.ceil(slides.length / perView())); }
    function go(i) {
      index = (i + pages()) % pages();
      track.style.transform = 'translateX(' + (-index * 100) + '%)';
      if (dots) {
        $$('button', dots).forEach(function (b, bi) { b.classList.toggle('is-active', bi === index); });
      }
    }
    if (dots) {
      dots.innerHTML = '';
      for (var p = 0; p < pages(); p++) {
        (function (pi) {
          var b = d.createElement('button');
          b.type = 'button';
          b.addEventListener('click', function () { go(pi); });
          dots.appendChild(b);
        })(p);
      }
    }
    var prev = $('.slider-prev', slider), next = $('.slider-next', slider);
    if (prev) { prev.addEventListener('click', function () { go(index - 1); }); }
    if (next) { next.addEventListener('click', function () { go(index + 1); }); }

    // touch
    var sx = 0;
    track.addEventListener('touchstart', function (e) { sx = e.touches[0].clientX; }, { passive: true });
    track.addEventListener('touchend', function (e) {
      var dx = e.changedTouches[0].clientX - sx;
      if (Math.abs(dx) > 50) { go(index + (dx < 0 ? 1 : -1)); }
    });

    var auto = parseInt(slider.dataset.autoplay || '0', 10);
    var timer;
    function start() { if (auto && !reduce) { timer = setInterval(function () { go(index + 1); }, auto); } }
    slider.addEventListener('mouseenter', function () { clearInterval(timer); });
    slider.addEventListener('mouseleave', start);
    window.addEventListener('resize', function () { go(0); });
    go(0); start();
  });

  /* ---------------------------------------------------------------- */
  /* Before / after                                                    */
  /* ---------------------------------------------------------------- */
  $$('[data-ba]').forEach(function (ba) {
    var before = $('.ba-before', ba);
    var handle = $('.ba-handle', ba);
    function set(x) {
      var r = ba.getBoundingClientRect();
      var p = Math.max(0, Math.min(100, ((x - r.left) / r.width) * 100));
      before.style.width = p + '%';
      handle.style.left = p + '%';
    }
    var down = false;
    ba.addEventListener('mousedown', function (e) { down = true; set(e.clientX); });
    d.addEventListener('mouseup', function () { down = false; });
    d.addEventListener('mousemove', function (e) { if (down) { set(e.clientX); } });
    ba.addEventListener('touchmove', function (e) { set(e.touches[0].clientX); }, { passive: true });
  });

  /* ---------------------------------------------------------------- */
  /* Shared AJAX helper                                                */
  /* ---------------------------------------------------------------- */
  window.Beautia = {
    post: function (action, data) {
      var body = new FormData();
      body.append('demo', window.BeautiaData ? BeautiaData.demo || '' : '');
      body.append('action', action);
      body.append('nonce', window.BeautiaData ? BeautiaData.nonce : '');
      Object.keys(data || {}).forEach(function (k) { body.append(k, data[k]); });
      return fetch(BeautiaData.ajaxUrl, { method: 'POST', body: body, credentials: 'same-origin' })
        .then(function (r) { return r.json(); });
    },
    msg: function (el, text, type) {
      if (!el) { return; }
      el.textContent = text;
      el.className = 'msg ' + (type === 'error' ? 'is-error' : 'is-ok');
    },
    /* Convert Persian / Arabic-Indic digits to latin so every numeric field
       accepts what an Iranian keyboard actually types. */
    toEnDigits: function (str) {
      return String(str)
        .replace(/[۰-۹]/g, function (c) { return String(c.charCodeAt(0) - 1776); })
        .replace(/[٠-٩]/g, function (c) { return String(c.charCodeAt(0) - 1632); });
    },
    toFaDigits: function (str) {
      return String(str).replace(/[0-9]/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'[d]; });
    },
    otpInputs: function (wrap, onComplete) {
      if (!wrap || wrap.dataset.built === '1') { return; }
      wrap.dataset.built = '1';
      var len = parseInt(wrap.dataset.length || '5', 10);
      for (var i = 0; i < len; i++) {
        var input = d.createElement('input');
        input.type = 'tel';
        input.inputMode = 'numeric';
        input.maxLength = 1;
        input.autocomplete = i === 0 ? 'one-time-code' : 'off';
        wrap.appendChild(input);
      }
      var inputs = $$('input', wrap);
      inputs.forEach(function (input, i) {
        input.addEventListener('input', function () {
          input.value = Beautia.toEnDigits(input.value).replace(/\D/g, '').slice(0, 1);
          if (input.value && inputs[i + 1]) { inputs[i + 1].focus(); }
          var code = inputs.map(function (x) { return x.value; }).join('');
          if (code.length === len && onComplete) { onComplete(code); }
        });
        input.addEventListener('keydown', function (e) {
          if (e.key === 'Backspace' && !input.value && inputs[i - 1]) { inputs[i - 1].focus(); }
        });
        input.addEventListener('paste', function (e) {
          var txt = Beautia.toEnDigits((e.clipboardData || window.clipboardData).getData('text')).replace(/\D/g, '');
          if (!txt) { return; }
          e.preventDefault();
          inputs.forEach(function (x, xi) { x.value = txt[xi] || ''; });
          if (txt.length >= len && onComplete) { onComplete(txt.slice(0, len)); }
        });
      });
      wrap.getCode = function () { return inputs.map(function (x) { return x.value; }).join(''); };
      wrap.clear = function () { inputs.forEach(function (x) { x.value = ''; }); inputs[0].focus(); };
      inputs[0].focus();
    },
    countdown: function (btn, seconds, labelTpl, doneLabel) {
      var left = seconds;
      btn.disabled = true;
      btn.textContent = labelTpl.replace('%s', BeautiaData.faDigits ? Beautia.toFaDigits(left) : left);
      var t = setInterval(function () {
        left--;
        if (left <= 0) {
          clearInterval(t);
          btn.disabled = false;
          btn.textContent = doneLabel;
          return;
        }
        btn.textContent = labelTpl.replace('%s', BeautiaData.faDigits ? Beautia.toFaDigits(left) : left);
      }, 1000);
    }
  };

  /* ---------------------------------------------------------------- */
  /* Favourites + cancel + profile                                     */
  /* ---------------------------------------------------------------- */
  $$('.beautia-fav').forEach(function (btn) {
    btn.addEventListener('click', function () {
      Beautia.post('beautia_toggle_favorite', { id: btn.dataset.id }).then(function (res) {
        if (!res.success) {
          if (res.data && res.data.needLogin) { window.location = BeautiaData.loginUrl; }
          return;
        }
        btn.classList.toggle('is-active', res.data.state === 'added');
        var svg = $('svg', btn);
        if (svg) { svg.classList.toggle('is-active', res.data.state === 'added'); }
      });
    });
  });

  $$('.beautia-cancel').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (!window.confirm(btn.dataset.confirm || 'Cancel this appointment?')) { return; }
      Beautia.post('beautia_cancel_booking', { id: btn.dataset.id }).then(function (res) {
        window.alert(res.data.message);
        if (res.success) { window.location.reload(); }
      });
    });
  });


  /* ---------------------------------------------------------------- */
  /* Jalali (Shamsi) date field — [data-jdate]                         */
  /* A text field the visitor reads in Persian, backed by a hidden      */
  /* Gregorian value the server understands.                            */
  /* ---------------------------------------------------------------- */
  var JD = {
    months: ['فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور','مهر','آبان','آذر','دی','بهمن','اسفند'],
    dows: ['ش','ی','د','س','چ','پ','ج'],
    div: function (a, b) { return Math.floor(a / b); },
    fa: function (n) { return String(n).replace(/[0-9]/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'[d]; }); },
    pad: function (n) { return n < 10 ? '0' + n : '' + n; },
    g2j: function (gy, gm, gd) {
      var gdm = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
      var gy2 = (gm > 2) ? (gy + 1) : gy;
      var days = 355666 + (365 * gy) + JD.div(gy2 + 3, 4) - JD.div(gy2 + 99, 100) +
        JD.div(gy2 + 399, 400) + gd + gdm[gm - 1];
      var jy = -1595 + (33 * JD.div(days, 12053));
      days %= 12053;
      jy += 4 * JD.div(days, 1461);
      days %= 1461;
      if (days > 365) { jy += JD.div(days - 1, 365); days = (days - 1) % 365; }
      var jm, jd;
      if (days < 186) { jm = 1 + JD.div(days, 31); jd = 1 + (days % 31); }
      else { jm = 7 + JD.div(days - 186, 30); jd = 1 + ((days - 186) % 30); }
      return [jy, jm, jd];
    },
    j2g: function (jy, jm, jd) {
      jy += 1595;
      var days = -355668 + (365 * jy) + (JD.div(jy, 33) * 8) + JD.div((jy % 33) + 3, 4) + jd +
        ((jm < 7) ? (jm - 1) * 31 : ((jm - 7) * 30) + 186);
      var gy = 400 * JD.div(days, 146097);
      days %= 146097;
      if (days > 36524) {
        gy += 100 * JD.div(--days, 36524);
        days %= 36524;
        if (days >= 365) { days++; }
      }
      gy += 4 * JD.div(days, 1461);
      days %= 1461;
      if (days > 365) { gy += JD.div(days - 1, 365); days = (days - 1) % 365; }
      var gd = days + 1;
      var sal = [0, 31, ((gy % 4 === 0 && gy % 100 !== 0) || (gy % 400 === 0)) ? 29 : 28,
        31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
      var gm;
      for (gm = 1; gm <= 12 && gd > sal[gm]; gm++) { gd -= sal[gm]; }
      return [gy, gm, gd];
    },
    monthLength: function (jy, jm) {
      if (jm <= 6) { return 31; }
      if (jm <= 11) { return 30; }
      var g = JD.j2g(jy + 1, 1, 1);
      var prev = new Date(g[0], g[1] - 1, g[2]);
      prev.setDate(prev.getDate() - 1);
      return JD.g2j(prev.getFullYear(), prev.getMonth() + 1, prev.getDate())[2];
    },
    label: function (jy, jm, jd) { return JD.fa(jd) + ' ' + JD.months[jm - 1] + ' ' + JD.fa(jy); },
    iso: function (jy, jm, jd) {
      var g = JD.j2g(jy, jm, jd);
      return g[0] + '-' + JD.pad(g[1]) + '-' + JD.pad(g[2]);
    }
  };
  Beautia.jalali = JD;

  function initJDate(input) {
    if (input.dataset.jdateReady === '1') { return; }
    input.dataset.jdateReady = '1';
    input.setAttribute('readonly', 'readonly');
    input.setAttribute('autocomplete', 'off');

    var hidden = null;
    if (input.name) {
      hidden = document.createElement('input');
      hidden.type = 'hidden';
      hidden.name = input.name;
      input.removeAttribute('name');
      input.parentNode.appendChild(hidden);
    }

    var today = new Date();
    var startIso = input.dataset.value || '';
    var base = startIso ? new Date(startIso + 'T00:00:00') : today;
    var cur = JD.g2j(base.getFullYear(), base.getMonth() + 1, base.getDate());
    var view = [cur[0], cur[1]];
    var picked = startIso ? cur.slice() : null;
    var minIso = input.dataset.min || '';

    function commit() {
      if (!picked) { return; }
      input.value = JD.label(picked[0], picked[1], picked[2]);
      var iso = JD.iso(picked[0], picked[1], picked[2]);
      if (hidden) { hidden.value = iso; }
      input.dataset.iso = iso;
      input.dispatchEvent(new CustomEvent('beautia:datechange', { bubbles: true, detail: { iso: iso } }));
    }
    if (picked) { commit(); }

    var pop = document.createElement('div');
    pop.className = 'jdate-pop';
    pop.setAttribute('hidden', 'hidden');
    input.parentNode.appendChild(pop);

    function render() {
      var jy = view[0], jm = view[1];
      var g1 = JD.j2g(jy, jm, 1);
      var first = new Date(g1[0], g1[1] - 1, g1[2]);
      var offset = (first.getDay() + 1) % 7;               /* Saturday first */
      var len = JD.monthLength(jy, jm);
      var html = '<div class="jdate-head">' +
        '<button type="button" class="jdate-nav" data-step="-1" aria-label="ماه قبل">‹</button>' +
        '<strong>' + JD.months[jm - 1] + ' ' + JD.fa(jy) + '</strong>' +
        '<button type="button" class="jdate-nav" data-step="1" aria-label="ماه بعد">›</button></div>' +
        '<div class="jdate-dows">' + JD.dows.map(function (d) { return '<span>' + d + '</span>'; }).join('') + '</div>' +
        '<div class="jdate-grid">';
      var i;
      for (i = 0; i < offset; i++) { html += '<span></span>'; }
      for (i = 1; i <= len; i++) {
        var iso = JD.iso(jy, jm, i);
        var dis = minIso && iso < minIso ? ' disabled' : '';
        var sel = picked && picked[0] === jy && picked[1] === jm && picked[2] === i ? ' is-sel' : '';
        html += '<button type="button" class="jdate-day' + sel + '" data-d="' + i + '"' + dis + '>' + JD.fa(i) + '</button>';
      }
      pop.innerHTML = html + '</div>';
    }

    function open() { render(); pop.removeAttribute('hidden'); input.classList.add('is-open'); }
    function close() { pop.setAttribute('hidden', 'hidden'); input.classList.remove('is-open'); }

    input.addEventListener('click', function () { pop.hasAttribute('hidden') ? open() : close(); });
    input.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); open(); }
      if (e.key === 'Escape') { close(); }
    });
    pop.addEventListener('click', function (e) {
      var nav = e.target.closest('.jdate-nav');
      if (nav) {
        var step = parseInt(nav.dataset.step, 10);
        view[1] += step;
        if (view[1] > 12) { view[1] = 1; view[0]++; }
        if (view[1] < 1) { view[1] = 12; view[0]--; }
        render();
        return;
      }
      var day = e.target.closest('.jdate-day');
      if (day && !day.disabled) {
        picked = [view[0], view[1], parseInt(day.dataset.d, 10)];
        commit();
        close();
      }
    });
    document.addEventListener('click', function (e) {
      if (!pop.contains(e.target) && e.target !== input) { close(); }
    });
  }

  $$('[data-jdate]').forEach(initJDate);
  Beautia.initJDate = initJDate;


  /* ---------------------------------------------------------------- */
  /* Before / after comparison slider — [data-compare]                 */
  /* Pointer, touch and keyboard driven; RTL aware.                    */
  /* ---------------------------------------------------------------- */
  function initCompare(box) {
    if (box.dataset.compareReady === '1') { return; }
    box.dataset.compareReady = '1';
    var after = box.querySelector('.cmp-after');
    var handle = box.querySelector('.cmp-handle');
    if (!after || !handle) { return; }
    var pos = parseFloat(box.dataset.start || '50');

    function apply() {
      pos = Math.max(0, Math.min(100, pos));
      after.style.clipPath = 'inset(0 ' + (100 - pos) + '% 0 0)';
      handle.style.left = pos + '%';
      handle.style.right = 'auto';
      handle.setAttribute('aria-valuenow', Math.round(pos));
      handle.setAttribute('aria-valuetext', new Intl.NumberFormat('fa-IR').format(Math.round(pos)) + ' درصد تصویر بعد');
      box.querySelectorAll('.cmp-tag-before').forEach(function(tag){tag.hidden=pos>94;});
      box.querySelectorAll('.cmp-tag-after').forEach(function(tag){tag.hidden=pos<6;});
    }
    apply();

    function fromEvent(e) {
      var r = box.getBoundingClientRect();
      var x = (e.touches ? e.touches[0].clientX : e.clientX) - r.left;
      var p = (x / r.width) * 100;
      pos = p;
      apply();
    }
    function down(e) {
      box.classList.add('is-dragging');
      fromEvent(e);
      window.addEventListener('pointermove', fromEvent);
      window.addEventListener('pointerup', up, { once: true });
    }
    function up() {
      box.classList.remove('is-dragging');
      window.removeEventListener('pointermove', fromEvent);
    }
    box.addEventListener('pointerdown', down);
    handle.addEventListener('keydown', function (e) {
      var step = e.shiftKey ? 10 : 2;
      if (e.key === 'ArrowLeft') { pos -= step; apply(); e.preventDefault(); }
      if (e.key === 'ArrowRight') { pos += step; apply(); e.preventDefault(); }
      if (e.key === 'Home') { pos = 0; apply(); }
      if (e.key === 'End') { pos = 100; apply(); }
    });
  }
  $$('[data-compare]').forEach(initCompare);
  Beautia.initCompare = initCompare;


  /* ---------------------------------------------------------------- */
  /* Post-visit review (rating + short text)                           */
  /* ---------------------------------------------------------------- */
  var reviewModal = $('#beautia-review');
  if (reviewModal) {
    var reviewId = 0;
    var reviewScore = 0;
    var stars = $$('#review-stars .star');

    function paint(n) {
      stars.forEach(function (s, i) { s.classList.toggle('is-on', i < n); });
    }
    stars.forEach(function (s) {
      s.addEventListener('mouseenter', function () { paint(parseInt(s.dataset.value, 10)); });
      s.addEventListener('focus', function () { paint(parseInt(s.dataset.value, 10)); });
      s.addEventListener('click', function () {
        reviewScore = parseInt(s.dataset.value, 10);
        paint(reviewScore);
      });
    });
    $('#review-stars').addEventListener('mouseleave', function () { paint(reviewScore); });

    function openReview(id) {
      reviewId = id;
      reviewScore = 0;
      paint(0);
      $('#review-text').value = '';
      Beautia.msg($('#review-msg'), '', 'ok');
      $('#review-msg').textContent = '';
      reviewModal.removeAttribute('hidden');
      document.body.classList.add('has-modal');
      $('#review-stars .star').focus();
    }
    function closeReview() {
      reviewModal.setAttribute('hidden', 'hidden');
      document.body.classList.remove('has-modal');
    }
    $$('.js-review').forEach(function (btn) {
      btn.addEventListener('click', function () { openReview(btn.dataset.id); });
    });
    $('.review-close', reviewModal).addEventListener('click', closeReview);
    reviewModal.addEventListener('click', function (e) { if (e.target === reviewModal) { closeReview(); } });
    d.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !reviewModal.hasAttribute('hidden')) { closeReview(); }
    });

    $('#review-send').addEventListener('click', function () {
      if (!reviewScore) {
        Beautia.msg($('#review-msg'), (BeautiaData.i18n && BeautiaData.i18n.pickStars) || '★', 'error');
        return;
      }
      Beautia.post('beautia_submit_review', {
        id: reviewId,
        rating: reviewScore,
        text: $('#review-text').value
      }).then(function (res) {
        Beautia.msg($('#review-msg'), res.data.message, res.success ? 'ok' : 'error');
        if (res.success) { setTimeout(function () { window.location.reload(); }, 1400); }
      });
    });
  }

  var profileForm = $('#beautia-profile');
  if (profileForm) {
    profileForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var fd = new FormData(profileForm);
      var data = {};
      fd.forEach(function (v, k) { data[k] = v; });
      Beautia.post('beautia_update_profile', data).then(function (res) {
        Beautia.msg($('#profile-msg'), res.data.message, res.success ? 'ok' : 'error');
      });
    });
  }
})();
