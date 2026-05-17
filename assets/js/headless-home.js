(function(){
  const wp = window.wp || {};
  const e = wp.element ? wp.element.createElement : null;
  const render = wp.element ? wp.element.render : null;
  const useState = wp.element ? wp.element.useState : null;
  const createPortal = wp.element ? wp.element.createPortal : null;
  const root = document.getElementById('zole-headless-root');
  const preload = document.getElementById('zole-headless-preload');
  if (!e || !render || !root || !preload) return;

  let data = {};
  try { data = JSON.parse(preload.textContent || '{}'); } catch (err) { data = {}; }
  const L = data.labels || {};
  const months = data.months || [];
  const gallery = data.gallery || [];
  function galleryFull(item){ return (item && typeof item === 'object') ? (item.fullAvif || item.full_avif || item.full || item.sourceAvif || item.source || item.thumbAvif || item.thumb || '') : item; }
  function galleryThumb(item){ return (item && typeof item === 'object') ? (item.mobileAvif || item.mobile_avif || item.mobile || item.fullAvif || item.full_avif || item.full || item.thumbAvif || item.thumb_avif || item.thumb || item.source || '') : item; }
  const news = data.news || [];
  const urls = data.urls || {};
  const settings = data.settings || {};
  const sections = data.sections || [];
  const current = data.currentMonth || (new Date().getMonth() + 1);

  function Btn(props){ return e('a', { className: 'zole-btn ' + (props.kind || 'zole-btn-gold'), href: props.href || '#' }, props.children); }
  function Kicker(props){ return e('div', { className: 'zole-kicker', style: props.style || null }, props.children); }

  function Hero(){
    return e('section', { className: 'zole-hero' },
      e('div', { className: 'container' },
        e('div', { className: 'zole-hero-grid' },
          e('div', null,
            e(Kicker, null, L.kicker),
            e('h1', null, L.heroTitle),
            e('p', { className: 'zole-hero-lead' }, L.heroText),
            e('div', { className: 'zole-subline' }, settings.hero_subline || L.heroSubline),
            e('div', { className: 'zole-actions' },
              e(Btn, { href: urls.calendar }, L.calendarBtn, ' ', e('span', null, '→')),
              e(Btn, { href: urls.rules, kind: 'zole-btn-ghost' }, L.rulesBtn)
            ),
            null
          ),
          e('div', { className: 'zole-hero-card' },
            e('div', { className: 'zole-hero-card-inner' },
              e('div', { className: 'zole-card-main' },
                e('img', { className: 'zole-logo-big', src: data.logo, alt: 'Zolei.lv' }),
                e('div', { className: 'zole-card-symbol' }, '♛'),
                e('h2', { className: 'h3 fw-bold mb-0' }, L.cardTitle),
                e('p', { className: 'mb-0 text-muted' }, L.cardText),
                e(Btn, { href: urls.calendar }, L.openCalendar)
              )
            )
          )
        )
      )
    );
  }

  function QuickLinks(){
    const items = [
      ['♠', L.navCalendar, L.navCalendarText, urls.calendar],
      ['♣', L.navRules, L.navRulesText, urls.rules],
      ['♦', L.navResults, L.navResultsText, urls.results],
      ['♥', L.navContact, L.navContactText, urls.contact]
    ];
    return e('section', { className: 'zole-section zole-quicklinks-section' },
      e('div', { className: 'container' },
        e('div', { className: 'zole-section-head-centered' },
          e(Kicker, { style: { color: 'var(--green-700)' } }, L.quickKicker),
          e('h2', { className: 'zole-section-title' }, L.quickTitle)
        ),
        e('div', { className: 'zole-quick-grid' }, items.map(function(it, i){
          return e('a', { className: 'zole-card zole-quick-card', href: it[3], key: i },
            e('div', { className: 'zole-icon' }, it[0]),
            e('h3', null, it[1]),
            e('p', null, it[2])
          );
        }))
      )
    );
  }

  function Calendar(){
    return e('section', { id: 'calendar', className: 'zole-section zole-calendar-wrap' },
      e('div', { className: 'container' },
        e('div', { className: 'row align-items-end g-4' },
          e('div', { className: 'col-lg-8' },
            e(Kicker, { style: { color: 'var(--green-700)' } }, L.calendarKicker),
            e('h2', { className: 'zole-section-title' }, L.calendarTitle),
            e('p', { className: 'zole-section-text' }, L.calendarText)
          ),
          e('div', { className: 'col-lg-4 text-lg-end' }, e(Btn, { href: urls.fullCalendar, kind: 'zole-btn-green' }, L.fullCalendar))
        ),
        e('div', { className: 'zole-month-tabs', role: 'tablist' }, months.map(function(m, i){
          const active = i + 1 === current;
          return e('button', {
            key: m.slug,
            className: 'zole-month-tab ' + (active ? 'active' : ''),
            id: 'tab-' + m.slug,
            'data-bs-toggle': 'pill',
            'data-bs-target': '#pane-' + m.slug,
            type: 'button',
            role: 'tab',
            'aria-selected': active ? 'true' : 'false'
          }, e('span', null, m.name), e('em', null, (m.events || []).length));
        })),
        e('div', { className: 'tab-content zole-calendar-panel' }, months.map(function(m, i){
          const active = i + 1 === current;
          return e('div', { key: m.slug, className: 'tab-pane fade ' + (active ? 'show active' : ''), id: 'pane-' + m.slug, role: 'tabpanel' },
            e('div', { className: 'zole-month-head' },
              e('div', null, e('h3', null, m.name), e('p', null, L.monthSource)),
              e('a', { className: 'fw-bold text-success', href: m.url }, L.monthPage + ' →')
            ),
            e('div', { className: 'zole-event-grid' }, (m.events || []).map(function(ev, idx){
              return e('article', { className: 'zole-event', key: idx }, e('div', { className: 'zole-event-date' }, ev.day), e('p', null, ev.title));
            }))
          );
        }))
      )
    );
  }

  function Rules(){
    return e('section', { id: 'rules', className: 'zole-section' },
      e('div', { className: 'container' },
        e('div', { className: 'row g-4 align-items-stretch' },
          e('div', { className: 'col-lg-6' },
            e('div', { className: 'zole-panel zole-rules-card' },
              e(Kicker, null, L.rulesKicker),
              e('h2', { className: 'zole-section-title text-white' }, L.rulesTitle),
              e('p', null, L.rulesText),
              e('div', { className: 'mt-4' }, [L.rule1, L.rule2, L.rule3, L.rule4].map(function(r, i){
                return e('div', { className: 'zole-rule-row', key: i }, e('span', { className: 'zole-check' }, '✓'), e('span', null, r));
              }))
            )
          ),
          e('div', { className: 'col-lg-6' },
            e('div', { className: 'zole-panel' },
              e(Kicker, { style: { color: 'var(--green-700)' } }, L.newsKicker),
              e('h2', { className: 'zole-section-title' }, L.newsTitle),
              e('p', { className: 'mb-4' }, L.newsText),
              e('div', { className: 'p-4 rounded-4 mb-3', style: { background: '#fff8ea', border: '1px solid rgba(216,173,82,.32)' } }, e('h3', { className: 'h5 mb-2' }, L.notice1Title), e('p', { className: 'mb-0' }, L.notice1Text)),
              e('div', { className: 'p-4 rounded-4', style: { background: '#f3f7f4', border: '1px solid rgba(18,61,43,.14)' } }, e('h3', { className: 'h5 mb-2' }, L.notice2Title), e('p', { className: 'mb-0' }, L.notice2Text))
            )
          )
        )
      )
    );
  }

  function BlogSection(){
    const canUseState = typeof useState === 'function';
    const activeState = canUseState ? useState(null) : [null, function(){}];
    const active = activeState[0];
    const setActive = activeState[1];
    const [copied, setCopied] = canUseState ? useState(false) : [false, function(){}];
    if (!news.length) return null;
    const posts = news.slice(0, 3);
    function open(item){ setActive(item); setCopied(false); }
    function close(){ setActive(null); setCopied(false); }
    function shareUrl(item){ return item && item.url ? item.url : window.location.href; }
    function shareFacebook(item){ window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(shareUrl(item)), 'zoleShareFacebook', 'width=680,height=520'); }
    function shareLinkedin(item){ window.open('https://www.linkedin.com/sharing/share-offsite/?url=' + encodeURIComponent(shareUrl(item)), 'zoleShareLinkedin', 'width=680,height=520'); }
    function shareWhatsapp(item){ window.open('https://wa.me/?text=' + encodeURIComponent((item && item.title ? item.title + ' - ' : '') + shareUrl(item)), 'zoleShareWhatsapp', 'width=680,height=520'); }
    function shareX(item){ window.open('https://twitter.com/intent/tweet?url=' + encodeURIComponent(shareUrl(item)) + '&text=' + encodeURIComponent(item && item.title ? item.title : ''), 'zoleShareX', 'width=680,height=520'); }
    function copyLink(item){
      const url = shareUrl(item);
      function done(){ setCopied(true); setTimeout(function(){ setCopied(false); }, 1600); }
      if (navigator.clipboard && navigator.clipboard.writeText) { navigator.clipboard.writeText(url).then(done).catch(function(){ window.prompt(L.copyPrompt || 'Copy link:', url); }); }
      else { window.prompt(L.copyPrompt || 'Copy link:', url); }
    }
    return e('section', { id: 'news', className: 'zole-section zole-news-section zole-blog-section' },
      e('div', { className: 'container' },
        e('div', { className: 'zole-section-head-centered' },
          e(Kicker, { style: { color: 'var(--green-700)' } }, L.newsSliderKicker || L.newsKicker),
          e('h2', { className: 'zole-section-title' }, L.newsSliderTitle || L.newsTitle),
          e('p', { className: 'zole-section-text' }, L.newsSliderText || L.newsText)
        ),
        e('div', { className: 'zole-blog-grid' }, posts.map(function(item, i){
          return e('article', { key: item.id || item.url || i, className: 'zole-blog-card', tabIndex: 0, role: 'button', onClick: function(){ open(item); }, onKeyDown: function(ev){ if(ev.key === 'Enter' || ev.key === ' '){ ev.preventDefault(); open(item); } } },
            e('div', { className: 'zole-blog-image' }, e('img', { src: item.image, alt: item.title, loading: i ? 'lazy' : 'eager' })),
            e('div', { className: 'zole-blog-body' },
              e('span', { className: 'zole-news-date' }, item.date),
              e('h3', null, item.title),
              e('p', null, item.excerpt),
              e('button', { className: 'zole-blog-read', type: 'button' }, (L.readMore || 'Read more') + ' →')
            )
          );
        })),
        active ? e('div', { className: 'zole-blog-lightbox open', role: 'dialog', 'aria-modal': 'true', 'aria-labelledby': 'zoleBlogLightboxTitle' },
          e('button', { className: 'zole-blog-lightbox-backdrop', type: 'button', onClick: close, 'aria-label': L.blogClose || L.galleryClose || 'Close' }),
          e('div', { className: 'zole-blog-lightbox-inner' },
            e('button', { className: 'zole-blog-lightbox-close', type: 'button', onClick: close, 'aria-label': L.blogClose || 'Close' }, '×'),
            e('div', { className: 'zole-blog-lightbox-media' }, e('img', { src: active.image, alt: active.title })),
            e('div', { className: 'zole-blog-lightbox-content' },
              e('span', { className: 'zole-news-date' }, active.date),
              e('h3', { id: 'zoleBlogLightboxTitle' }, active.title),
              e('p', { className: 'zole-blog-lightbox-summary' }, active.excerpt),
              e('div', { className: 'zole-blog-lightbox-text', dangerouslySetInnerHTML: { __html: active.content || ('<p>' + (active.excerpt || '') + '</p>') } }),
              e('div', { className: 'zole-blog-lightbox-actions' },
                e('a', { className: 'zole-btn zole-btn-green', href: active.url }, (L.readMore || 'Read more') + ' →'),
                e('button', { className: 'zole-blog-share-btn', type: 'button', onClick: function(){ shareWhatsapp(active); }, 'aria-label': L.shareWhatsapp || 'WhatsApp' }, '☏'),
                e('button', { className: 'zole-blog-share-btn', type: 'button', onClick: function(){ shareFacebook(active); }, 'aria-label': L.shareFacebook || 'Facebook' }, 'f'),
                e('button', { className: 'zole-blog-share-btn', type: 'button', onClick: function(){ shareX(active); }, 'aria-label': L.shareX || 'X' }, '𝕏'),
                e('button', { className: 'zole-blog-share-btn ' + (copied ? 'is-copied' : ''), type: 'button', onClick: function(){ copyLink(active); }, 'aria-label': copied ? (L.copiedLink || 'Copied') : (L.copyLink || 'Copy link') }, copied ? '✓' : '⛓')
              )
            )
          )
        ) : null
      )
    );
  }

  function Gallery(){
    if (!gallery.length) return null;
    const canUseState = typeof useState === 'function';
    const stateVisible = canUseState ? useState(10) : [gallery.length, function(){}];
    const visible = stateVisible[0];
    const setVisible = stateVisible[1];
    const lightboxState = canUseState ? useState(null) : [null, function(){}];
    const lightboxIndex = lightboxState[0];
    const setLightboxIndex = lightboxState[1];
    const shown = gallery.slice(0, visible);

    function openLightbox(i){ setLightboxIndex(i); }
    function closeLightbox(){ setLightboxIndex(null); }
    function prevLightbox(){ setLightboxIndex((lightboxIndex + gallery.length - 1) % gallery.length); }
    function nextLightbox(){ setLightboxIndex((lightboxIndex + 1) % gallery.length); }

    return e('section', { id: 'gallery', className: 'zole-section zole-gallery-section' },
      e('div', { className: 'container' },
        e('div', { className: 'row align-items-end g-4 mb-5' },
          e('div', { className: 'col-lg-7' }, e(Kicker, { style: { color: 'var(--green-700)' } }, L.galleryKicker), e('h2', { className: 'zole-section-title' }, L.galleryTitle)),
          e('div', { className: 'col-lg-5' }, e('p', { className: 'zole-section-text mb-0' }, L.galleryText))
        ),
        e('div', { className: 'zole-gallery-topline' },
          e('strong', null, gallery.length),
          e('span', null, L.galleryCount || '')
        ),
        e('div', { id: 'zoleGallery', className: 'carousel slide zole-gallery-slider', 'data-bs-ride': 'carousel' },
          e('div', { className: 'carousel-inner' }, gallery.slice(0, Math.min(gallery.length, 6)).map(function(item, i){
            var full = galleryFull(item); var thumb = galleryThumb(item);
            return e('div', { className: 'carousel-item ' + (i === 0 ? 'active' : ''), key: full + i },
              e('button', { className: 'zole-gallery-hero-button', type: 'button', onClick: function(){ openLightbox(i); }, 'aria-label': L.galleryOpen || 'Open photo' },
                e('img', { src: thumb || full, alt: L.galleryTitle, loading: i ? 'lazy' : 'eager', decoding: 'async' })
              ),
              e('div', { className: 'carousel-caption' }, e('h3', null, L.gallerySlideTitle), e('p', null, L.gallerySlideText))
            );
          })),
          e('button', { className: 'carousel-control-prev', type: 'button', 'data-bs-target': '#zoleGallery', 'data-bs-slide': 'prev' }, e('span', { className: 'carousel-control-prev-icon', 'aria-hidden': 'true' }), e('span', { className: 'visually-hidden' }, 'Previous')),
          e('button', { className: 'carousel-control-next', type: 'button', 'data-bs-target': '#zoleGallery', 'data-bs-slide': 'next' }, e('span', { className: 'carousel-control-next-icon', 'aria-hidden': 'true' }), e('span', { className: 'visually-hidden' }, 'Next'))
        ),
        e('div', { className: 'zole-gallery-grid', role: 'list' }, shown.map(function(item, i){
          var full = galleryFull(item); var thumb = galleryThumb(item);
          return e('button', { key: full + i, type: 'button', className: 'zole-gallery-thumb', onClick: function(){ openLightbox(i); }, role: 'listitem', 'aria-label': (L.galleryOpen || 'Open photo') + ' ' + (i + 1) },
            e('img', { src: thumb || full, alt: L.galleryTitle + ' ' + (i + 1), loading: i < 2 ? 'eager' : 'lazy', decoding: 'async' }),
            e('span', null, L.galleryOpen || 'Open photo')
          );
        })),
        visible < gallery.length ? e('div', { className: 'text-center mt-4' },
          e('button', { className: 'zole-btn zole-btn-green', type: 'button', onClick: function(){ setVisible(Math.min(visible + 10, gallery.length)); } }, L.galleryLoadMore || 'Load more photos')
        ) : null,
        lightboxIndex !== null ? (createPortal ? createPortal(e('div', { className: 'zole-lightbox', role: 'dialog', 'aria-modal': 'true' },
          e('button', { className: 'zole-lightbox-backdrop', type: 'button', onClick: closeLightbox, 'aria-label': L.galleryClose || 'Close' }),
          e('div', { className: 'zole-lightbox-inner' },
            e('button', { className: 'zole-lightbox-close', type: 'button', onClick: closeLightbox, 'aria-label': L.galleryClose || 'Close' }, '×'),
            e('button', { className: 'zole-lightbox-arrow prev', type: 'button', onClick: prevLightbox, 'aria-label': L.previous || 'Previous' }, '‹'),
            e('img', { className: 'zole-lightbox-image', src: galleryFull(gallery[lightboxIndex]), alt: L.galleryTitle, decoding: 'async' }),
            e('button', { className: 'zole-lightbox-arrow next', type: 'button', onClick: nextLightbox, 'aria-label': L.next || 'Next' }, '›'),
            e('div', { className: 'zole-lightbox-count' }, (lightboxIndex + 1) + ' / ' + gallery.length)
          )
        ), document.body) : e('div', { className: 'zole-lightbox', role: 'dialog', 'aria-modal': 'true' },
          e('button', { className: 'zole-lightbox-backdrop', type: 'button', onClick: closeLightbox, 'aria-label': L.galleryClose || 'Close' }),
          e('div', { className: 'zole-lightbox-inner' },
            e('button', { className: 'zole-lightbox-close', type: 'button', onClick: closeLightbox, 'aria-label': L.galleryClose || 'Close' }, '×'),
            e('button', { className: 'zole-lightbox-arrow prev', type: 'button', onClick: prevLightbox, 'aria-label': L.previous || 'Previous' }, '‹'),
            e('img', { className: 'zole-lightbox-image', src: galleryFull(gallery[lightboxIndex]), alt: L.galleryTitle, decoding: 'async' }),
            e('button', { className: 'zole-lightbox-arrow next', type: 'button', onClick: nextLightbox, 'aria-label': L.next || 'Next' }, '›'),
            e('div', { className: 'zole-lightbox-count' }, (lightboxIndex + 1) + ' / ' + gallery.length)
          )
        )) : null
      )
    );
  }


  function ContactForm(){
    const formState = typeof useState === 'function' ? useState('') : ['', function(){}];
    const msg = formState[0];
    const setMsg = formState[1];
    const okState = typeof useState === 'function' ? useState(false) : [false, function(){}];
    const ok = okState[0];
    const setOk = okState[1];
    const hcaptchaKey = (window.zoleiForm && window.zoleiForm.hcaptchaSiteKey) || settings.hcaptchaSiteKey || '';

    function onSubmit(ev){
      ev.preventDefault();
      const form = ev.currentTarget;
      if (form.checkValidity && !form.checkValidity()) { form.reportValidity && form.reportValidity(); setOk(false); setMsg((window.zoleiForm && zoleiForm.labels && zoleiForm.labels.validation) || L.formRequired || 'Required'); return; }
      const fd = new FormData(form);
      fd.append('action', 'zolei_contact_submit');
      fd.append('nonce', (window.zoleiForm && zoleiForm.nonce) || '');
      const submit = form.querySelector('button[type="submit"]');
      const old = submit ? submit.textContent : '';
      if (submit) { submit.disabled = true; submit.textContent = (window.zoleiForm && zoleiForm.labels && zoleiForm.labels.sending) || 'Sending...'; }
      setMsg('');
      fetch((window.zoleiForm && zoleiForm.ajaxUrl) || '/wp-admin/admin-ajax.php', { method:'POST', credentials:'same-origin', body:fd })
        .then(function(r){ return r.json(); })
        .then(function(json){
          const text = (json && json.data && json.data.message) || (json && json.message) || '';
          if (!json || json.success !== true) { throw new Error(text || ((window.zoleiForm && zoleiForm.labels && zoleiForm.labels.error) || 'Error')); }
          setOk(true); setMsg(text || L.formSuccess || 'Thank you!'); form.reset();
          if (window.hcaptcha && window.hcaptcha.reset) { try { window.hcaptcha.reset(); } catch(e){} }
        })
        .catch(function(err){ setOk(false); setMsg((err && err.message) || ((window.zoleiForm && zoleiForm.labels && zoleiForm.labels.error) || 'Error')); if (window.hcaptcha && window.hcaptcha.reset) { try { window.hcaptcha.reset(); } catch(e){} } })
        .finally(function(){ if (submit) { submit.disabled = false; submit.textContent = old; } });
    }
    return e('div', { className: 'zole-contact-card' },
      e('div', { className: 'zole-contact-copy' },
        e(Kicker, { style: { color: 'var(--green-700)' } }, L.contactKicker),
        e('h3', null, L.formTitle || L.contactTitle),
        e('p', null, L.formIntro || L.contactText)
      ),
      e('form', { className: 'zole-contact-form', onSubmit: onSubmit },
        e('input', { type:'text', name:'website', tabIndex:'-1', autoComplete:'off', className:'zole-hp-field', 'aria-hidden':'true' }),
        e('div', { className:'zole-form-grid' },
          e('label', null, e('span', null, L.formName), e('input', { name:'name', required:true, placeholder:L.formName })),
          e('label', null, e('span', null, L.formEmail), e('input', { name:'email', type:'email', required:true, placeholder:L.formEmail })),
          e('label', null, e('span', null, L.formPhone), e('input', { name:'phone', type:'tel', placeholder:L.formPhone })),
          e('label', null, e('span', null, L.formSubject), e('input', { name:'subject', placeholder:L.formSubject })),
          e('label', { className:'zole-form-wide' }, e('span', null, L.formMessage), e('textarea', { name:'message', rows:5, required:true, placeholder:L.formMessage }))
        ),
        hcaptchaKey ? e('div', { className:'zole-hcaptcha-wrap' }, e('div', { className:'h-captcha', 'data-sitekey': hcaptchaKey, 'data-hl': data.lang || 'lv' })) : e('p', { className:'zole-hcaptcha-note' }, L.formHcaptchaMissing || ''),
        e('div', { className:'zole-form-actions' }, e('button', { className:'zole-btn zole-btn-green', type:'submit' }, L.formSubmit || 'Send'), msg ? e('div', { className:'zole-form-message ' + (ok ? 'is-success' : 'is-error'), role:'status' }, msg) : null)
      )
    );
  }


  function PartnerAndInfo(){
    const canUseState = typeof useState === 'function';
    const modalState = canUseState ? useState(null) : [null, function(){}];
    const active = modalState[0];
    const setActive = modalState[1];
    const cards = [
      { key:'board', icon:'♣', title:L.boardTitle, text:L.boardText, html:L.boardHtml, url:urls.board },
      { key:'ethics', icon:'♥', title:L.ethicsTitle, text:L.ethicsText, html:L.ethicsHtml, url:urls.ethics },
      { key:'regulations', icon:'♦', title:L.regulationsTitle, text:L.regulationsText, html:L.regulationsHtml, url:urls.regulations }
    ];
    function close(){ setActive(null); }
    return e('section', { className:'zole-section zole-info-partner-section' },
      e('div', { className:'container' },
        e('div', { className:'zole-partner-strip' },
          e('a', { className:'zole-partner-banner', href:urls.partner || 'https://goo.gl/g7jJpm', target:'_blank', rel:'noopener' },
            e('img', { src:data.partnerBanner, alt:L.partnerTitle || 'Partner', loading:'lazy' }),
            e('span', { className:'zole-partner-copy' },
              e(Kicker, null, L.partnerKicker || 'Partner'),
              e('strong', null, L.partnerTitle || 'Uzspēlē Zoli'),
              e('em', null, L.partnerText || ''),
              e('b', null, (L.partnerButton || 'Open') + ' →')
            )
          )
        ),
        e('div', { className:'zole-section-head-centered zole-info-head' },
          e(Kicker, { style:{ color:'var(--green-700)' } }, L.infoKicker || ''),
          e('h2', { className:'zole-section-title' }, L.infoTitle || ''),
          e('p', { className:'zole-section-text' }, L.infoText || '')
        ),
        e('div', { className:'zole-info-card-grid' }, cards.map(function(card){
          return e('article', { className:'zole-info-card', key:card.key },
            e('span', { className:'zole-info-icon' }, card.icon),
            e('h3', null, card.title),
            e('p', null, card.text),
            e('div', { className:'zole-info-actions' },
              e('button', { className:'zole-btn zole-btn-green', type:'button', onClick:function(){ setActive(card); } }, L.openModal || 'Open'),
              card.url ? e('a', { className:'zole-info-page-link', href:card.url, 'aria-label':(L.openModal || 'Open') + ' ' + card.title }, e('span', { className:'zole-info-page-link-icon' }, '→')) : null
            )
          );
        })),
        active ? e('div', { className:'zole-info-lightbox', role:'dialog', 'aria-modal':'true', 'aria-labelledby':'zoleInfoTitle' },
          e('button', { className:'zole-info-lightbox-backdrop', type:'button', onClick:close, 'aria-label':L.closeModal || 'Close' }),
          e('div', { className:'zole-info-lightbox-inner' },
            e('button', { className:'zole-blog-lightbox-close', type:'button', onClick:close, 'aria-label':L.closeModal || 'Close' }, '×'),
            e('span', { className:'zole-info-icon modal' }, active.icon),
            e('h3', { id:'zoleInfoTitle' }, active.title),
            e('p', { className:'zole-info-modal-lead' }, active.text),
            e('div', { className:'zole-info-modal-html', dangerouslySetInnerHTML:{ __html: active.html || '' } }),
            e('div', { className:'zole-info-modal-actions' },
              active.url ? e('a', { className:'zole-btn zole-btn-green', href:active.url }, (L.openModal || 'Open') + ' ↗') : null,
              e('button', { className:'zole-btn zole-btn-ghost', type:'button', onClick:close }, L.closeModal || 'Close')
            )
          )
        ) : null
      )
    );
  }

  function ArchiveSidebar(){
    return e('aside', { className:'zole-archive-sidebar' },
      e('div', { className:'zole-archive-sidebar-card' },
        e('span', { className:'zole-info-icon' }, '♠'),
        e('h3', null, L.archiveSidebarTitle || 'Archive'),
        e('p', null, L.archiveSidebarText || ''),
        e('ul', null,
          e('li', null, L.archiveSidebarPoint1 || ''),
          e('li', null, L.archiveSidebarPoint2 || ''),
          e('li', null, L.archiveSidebarPoint3 || '')
        )
      )
    );
  }


  function DynamicSections(){
    if (!sections.length) return null;
    const children = [];
    let blogInserted = false;
    sections.forEach(function(sec){
      if (!sec || !sec.id) return;
      if (sec.id === 'contact' && !blogInserted) { children.push(e(BlogSection, { key: 'news' })); children.push(e(PartnerAndInfo, { key: 'partner-info' })); blogInserted = true; }
      const isGallery = sec.id === 'gallery';
      const isContact = sec.id === 'contact';
      const hasHtml = !!(sec.html && String(sec.html).trim());
      children.push(e('section', { id: sec.id, className: 'zole-section zole-dynamic-section zole-section-' + sec.id, key: sec.id },
        e('div', { className: 'container' },
          e('div', { className: 'zole-section-head-centered' },
            e(Kicker, { style: { color: 'var(--green-700)' } }, sec.nav || sec.title),
            e('h2', { className: 'zole-section-title' }, sec.title),
            sec.pdfType ? e('p', { className: 'zole-section-text' }, L.navResultsText || '') : null
          ),
          sec.id === 'archive' ? e(ArchiveSidebar) : null,
          hasHtml && !isGallery && !isContact ? e('div', { className: 'zole-section-content-shell ' + (sec.pdfType ? 'zole-pdf-shell' : '') },
            e('div', { className: (sec.pdfType ? 'zole-live-content zole-live-content-pdf' : 'zole-panel zole-live-content'), dangerouslySetInnerHTML: { __html: sec.html || '' } })
          ) : null,
          isGallery ? e(Gallery) : null,
          isContact ? e(ContactForm) : null
        )
      ));
    });
    if (!blogInserted) { children.push(e(BlogSection, { key: 'news' })); children.push(e(PartnerAndInfo, { key: 'partner-info' })); }
    return e('div', { className: 'zole-one-page-sections' }, children);
  }


  function Cta(){
    return e('section', { id: 'contact', className: 'zole-section' },
      e('div', { className: 'container' },
        e('div', { className: 'zole-cta' },
          e('div', { className: 'row align-items-center g-4' },
            e('div', { className: 'col-lg-8' }, e(Kicker, null, L.contactKicker), e('h2', { className: 'zole-section-title text-white mb-3' }, L.contactTitle), e('p', { className: 'mb-0' }, L.contactText)),
            e('div', { className: 'col-lg-4 text-lg-end' }, e(Btn, { href: urls.contact }, L.contactBtn))
          )
        )
      )
    );
  }

  function App(){ return e('main', null, e(Hero), e(QuickLinks), e('div', { className: 'zole-latvian-band' }), e(DynamicSections)); }
  render(e(App), root);
})();
