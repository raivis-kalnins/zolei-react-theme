(function(){
  const wp = window.wp || {};
  const e = wp.element ? wp.element.createElement : null;
  const render = wp.element ? wp.element.render : null;
  const useState = wp.element ? wp.element.useState : null;
  const root = document.getElementById('zole-headless-root');
  const preload = document.getElementById('zole-headless-preload');
  if (!e || !render || !root || !preload) return;

  let data = {};
  try { data = JSON.parse(preload.textContent || '{}'); } catch (err) { data = {}; }
  const L = data.labels || {};
  const months = data.months || [];
  const gallery = data.gallery || [];
  function galleryFull(item){ return (item && typeof item === 'object') ? (item.full || item.thumb || item.source || '') : item; }
  function galleryThumb(item){ return (item && typeof item === 'object') ? (item.thumb || item.full || item.source || '') : item; }
  const news = data.news || [];
  const urls = data.urls || {};
  const settings = data.settings || {};
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
            e('div', { className: 'zole-stats' },
              e('div', { className: 'zole-stat' }, e('strong', null, '12'), e('span', null, L.monthTabs)),
              e('div', { className: 'zole-stat' }, e('strong', null, String(data.eventCount || 0)), e('span', null, L.eventsPreview)),
              e('div', { className: 'zole-stat' }, e('strong', null, 'LV/EN'), e('span', null, L.twoLang))
            )
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
    return e('section', { className: 'zole-section' },
      e('div', { className: 'container' },
        e('div', { className: 'row align-items-end g-4 mb-5' },
          e('div', { className: 'col-lg-7' },
            e(Kicker, { style: { color: 'var(--green-700)' } }, L.quickKicker),
            e('h2', { className: 'zole-section-title' }, L.quickTitle)
          ),
          e('div', { className: 'col-lg-5' }, e('p', { className: 'zole-section-text mb-0' }, L.quickText))
        ),
        e('div', { className: 'row g-4' }, items.map(function(it, i){
          return e('div', { className: 'col-md-6 col-lg-3', key: i },
            e('a', { className: 'zole-card d-block', href: it[3] },
              e('div', { className: 'zole-icon' }, it[0]),
              e('h3', { className: 'h4' }, it[1]),
              e('p', { className: 'mb-0' }, it[2])
            )
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

  function NewsSlider(){
    if (!news.length) return null;
    return e('section', { id: 'news', className: 'zole-section zole-news-section' },
      e('div', { className: 'container' },
        e('div', { className: 'row align-items-end g-4 mb-5' },
          e('div', { className: 'col-lg-7' }, e(Kicker, { style: { color: 'var(--green-700)' } }, L.newsSliderKicker), e('h2', { className: 'zole-section-title' }, L.newsSliderTitle)),
          e('div', { className: 'col-lg-5' }, e('p', { className: 'zole-section-text mb-0' }, L.newsSliderText))
        ),
        e('div', { id: 'zoleNewsCarousel', className: 'carousel slide zole-news-slider', 'data-bs-ride': 'carousel' },
          e('div', { className: 'carousel-inner' }, news.map(function(item, i){
            return e('div', { className: 'carousel-item ' + (i === 0 ? 'active' : ''), key: item.url },
              e('div', { className: 'row g-4 align-items-center' },
                e('div', { className: 'col-lg-5' }, e('img', { src: item.image, alt: item.title, loading: i ? 'lazy' : 'eager' })),
                e('div', { className: 'col-lg-7' }, e('span', { className: 'zole-news-date' }, item.date), e('h3', null, item.title), e('p', null, item.excerpt), e('a', { className: 'zole-btn zole-btn-green', href: item.url }, L.readMore + ' →'))
              )
            );
          })),
          e('button', { className: 'carousel-control-prev', type: 'button', 'data-bs-target': '#zoleNewsCarousel', 'data-bs-slide': 'prev' }, e('span', { className: 'carousel-control-prev-icon' })),
          e('button', { className: 'carousel-control-next', type: 'button', 'data-bs-target': '#zoleNewsCarousel', 'data-bs-slide': 'next' }, e('span', { className: 'carousel-control-next-icon' }))
        )
      )
    );
  }

  function Gallery(){
    if (!gallery.length) return null;
    const canUseState = typeof useState === 'function';
    const stateVisible = canUseState ? useState(8) : [gallery.length, function(){}];
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
                e('img', { src: full || thumb, alt: L.galleryTitle, loading: i ? 'lazy' : 'eager' })
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
            e('img', { src: thumb || full, alt: L.galleryTitle + ' ' + (i + 1), loading: i < 4 ? 'eager' : 'lazy' }),
            e('span', null, L.galleryOpen || 'Open photo')
          );
        })),
        visible < gallery.length ? e('div', { className: 'text-center mt-4' },
          e('button', { className: 'zole-btn zole-btn-green', type: 'button', onClick: function(){ setVisible(Math.min(visible + 8, gallery.length)); } }, L.galleryLoadMore || 'Load more photos')
        ) : null,
        lightboxIndex !== null ? e('div', { className: 'zole-lightbox', role: 'dialog', 'aria-modal': 'true' },
          e('button', { className: 'zole-lightbox-backdrop', type: 'button', onClick: closeLightbox, 'aria-label': L.galleryClose || 'Close' }),
          e('div', { className: 'zole-lightbox-inner' },
            e('button', { className: 'zole-lightbox-close', type: 'button', onClick: closeLightbox }, '×'),
            e('button', { className: 'zole-lightbox-arrow prev', type: 'button', onClick: prevLightbox }, '‹'),
            e('img', { src: galleryFull(gallery[lightboxIndex]), alt: L.galleryTitle }),
            e('button', { className: 'zole-lightbox-arrow next', type: 'button', onClick: nextLightbox }, '›'),
            e('div', { className: 'zole-lightbox-count' }, (lightboxIndex + 1) + ' / ' + gallery.length)
          )
        ) : null
      )
    );
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

  function App(){ return e('main', null, e(Hero), e(QuickLinks), e('div', { className: 'zole-latvian-band' }), e(Calendar), e(Rules), e(NewsSlider), e(Gallery), e(Cta)); }
  render(e(App), root);
})();
