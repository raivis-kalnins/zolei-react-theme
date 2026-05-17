(function(){
  function ready(fn){ if(document.readyState !== 'loading') fn(); else document.addEventListener('DOMContentLoaded', fn); }
  ready(function(){

    document.querySelectorAll('.zole-menu-toggle').forEach(function(btn){
      btn.addEventListener('click', function(){
        var nav = document.getElementById(btn.getAttribute('aria-controls') || 'zolePrimaryMenu');
        var open = !btn.classList.contains('is-open');
        btn.classList.toggle('is-open', open);
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        if(nav){ nav.classList.toggle('is-open', open); }
        document.body.classList.toggle('zole-menu-open', open);
      });
    });
    document.querySelectorAll('.zole-menu a').forEach(function(link){
      link.addEventListener('click', function(){
        document.querySelectorAll('.zole-menu-toggle.is-open').forEach(function(btn){
          btn.classList.remove('is-open'); btn.setAttribute('aria-expanded','false');
          var nav = document.getElementById(btn.getAttribute('aria-controls') || 'zolePrimaryMenu');
          if(nav){ nav.classList.remove('is-open'); }
        });
        document.body.classList.remove('zole-menu-open');
      });
    });

    document.addEventListener('click',function(e){
      var toggle=e.target.closest('.zole-search-toggle');
      if(toggle){
        var wrap=toggle.closest('.zole-ajax-search');
        var open=!wrap.classList.contains('is-open');
        wrap.classList.toggle('is-open',open); toggle.setAttribute('aria-expanded',open?'true':'false');
        if(open){ setTimeout(function(){ var input=wrap.querySelector('input[type="search"]'); if(input) input.focus(); },60); }
      }
      if(!e.target.closest('.zole-ajax-search')) document.querySelectorAll('.zole-ajax-search.is-open').forEach(function(w){w.classList.remove('is-open'); var b=w.querySelector('.zole-search-toggle'); if(b)b.setAttribute('aria-expanded','false');});
    });
    document.addEventListener('keydown', function(e){
      if(e.key === 'Escape'){
        document.querySelectorAll('.zole-ajax-search.is-open').forEach(function(w){ w.classList.remove('is-open'); var b=w.querySelector('.zole-search-toggle'); if(b)b.setAttribute('aria-expanded','false'); });
        document.querySelectorAll('.zole-menu-toggle.is-open').forEach(function(btn){ btn.classList.remove('is-open'); btn.setAttribute('aria-expanded','false'); var nav=document.getElementById(btn.getAttribute('aria-controls') || 'zolePrimaryMenu'); if(nav) nav.classList.remove('is-open'); });
        document.body.classList.remove('zole-menu-open');
      }
    });

    document.querySelectorAll('.zole-search-form input[type="search"]').forEach(function(input){
      var form=input.closest('form'); var box=form.querySelector('.zole-search-results'); var timer=null;
      function htmlEscape(s){return String(s||'').replace(/[&<>"]/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c];});}
      function norm(s){return String(s||'').toLowerCase().replace(/\s+/g,' ').trim();}
      function ensureId(el, prefix){
        if(!el.id){ el.id=(prefix||'zole-search-hit')+'-'+Math.random().toString(36).slice(2,8); }
        return '#'+el.id;
      }
      function bestTitle(el){
        var h=el.querySelector('h1,h2,h3,.zole-section-title,.zole-doc-type');
        return (h && h.textContent.trim()) || el.getAttribute('aria-label') || el.getAttribute('data-title') || document.title || 'Result';
      }
      function excerpt(el){
        var text=norm(el.textContent || el.getAttribute('data-search') || '');
        return text.length>120 ? text.slice(0,120)+'…' : text;
      }
      function currentPageResults(term){
        var q=norm(term); var seen=[]; var results=[];
        var selectors='main section[id], .zole-dynamic-section, .zole-quick-card, .zole-result-card, .zole-news-card, .zole-event, article[id], .zole-gallery-thumb';
        document.querySelectorAll(selectors).forEach(function(el){
          if(el.closest('.zole-search-form')) return;
          var hay=norm((el.getAttribute('data-search')||'')+' '+(el.textContent||'')+' '+(el.getAttribute('aria-label')||''));
          if(!hay || hay.indexOf(q)===-1) return;
          var target=el.closest('.zole-result-card') || el.closest('section[id]') || el;
          if(seen.indexOf(target)!==-1) return;
          seen.push(target);
          results.push({title:bestTitle(target), excerpt:excerpt(target), url:ensureId(target,'zole-current-result')});
        });
        return results.slice(0,10);
      }
      input.addEventListener('input',function(){
        var term=input.value.trim(); clearTimeout(timer);
        if(term.length<2){ box.innerHTML=''; box.classList.remove('is-visible'); return; }
        box.innerHTML='<div class="zole-search-state">'+htmlEscape((window.zoleiSearch&&zoleiSearch.labels&&zoleiSearch.labels.searching)||'Searching...')+'</div>'; box.classList.add('is-visible');
        timer=setTimeout(function(){
          var items=currentPageResults(term);
          if(!items.length){ box.innerHTML='<div class="zole-search-state">'+htmlEscape((zoleiSearch&&zoleiSearch.labels&&zoleiSearch.labels.noResults)||'No results found.')+'</div>'; return; }
          box.innerHTML=items.map(function(it){return '<a class="zole-search-item zole-current-page-search-item" href="'+htmlEscape(it.url)+'"><strong>'+htmlEscape(it.title)+'</strong><span>'+htmlEscape(it.excerpt)+'</span></a>';}).join('');
        },120);
      });
      box.addEventListener('click',function(ev){
        var link=ev.target.closest('.zole-current-page-search-item');
        if(!link) return;
        var id=(link.getAttribute('href')||'').replace(/^#/, '');
        var target=id ? document.getElementById(id) : null;
        if(!target) return;
        ev.preventDefault();
        if(target.hidden) target.hidden=false;
        var card=target.closest('.zole-result-card');
        if(card && card.hidden) card.hidden=false;
        target.scrollIntoView({behavior:'smooth',block:'center'});
        target.classList.add('zole-search-highlight');
        setTimeout(function(){ target.classList.remove('zole-search-highlight'); },2200);
        document.querySelectorAll('.zole-ajax-search.is-open').forEach(function(w){ w.classList.remove('is-open'); var b=w.querySelector('.zole-search-toggle'); if(b)b.setAttribute('aria-expanded','false'); });
      });
    });
  });
})();
(function(){
  function ready(fn){ if(document.readyState !== 'loading') fn(); else document.addEventListener('DOMContentLoaded', fn); }
  ready(function(){
    document.querySelectorAll('[data-zole-results]').forEach(function(root){
      var search = root.querySelector('[data-zole-results-search]');
      var filters = root.querySelectorAll('[data-zole-results-filter]');
      var items = root.querySelectorAll('[data-zole-result]');
      function apply(){
        var q = search ? search.value.trim().toLowerCase() : '';
        var active = {};
        filters.forEach(function(f){ active[f.getAttribute('data-zole-results-filter')] = f.value; });
        items.forEach(function(item){
          var ok = true;
          if(q && (item.getAttribute('data-search') || '').indexOf(q) === -1) ok = false;
          Object.keys(active).forEach(function(key){ if(active[key] && item.getAttribute('data-' + key) !== active[key]) ok = false; });
          item.hidden = !ok;
        });
      }
      if(search) search.addEventListener('input', apply);
      filters.forEach(function(f){ f.addEventListener('change', apply); });
    });
  });
})();
(function(){
  function ready(fn){ if(document.readyState !== 'loading') fn(); else document.addEventListener('DOMContentLoaded', fn); }
  ready(function(){
    document.querySelectorAll('.zole-results-wrap').forEach(function(root){
      var search = root.querySelector('.zole-pdf-search');
      var year = root.querySelector('.zole-pdf-year');
      var type = root.querySelector('.zole-pdf-type');
      var cards = root.querySelectorAll('.zole-result-card');
      function apply(){
        var q = search ? search.value.trim().toLowerCase() : '';
        var y = year ? year.value : '';
        var t = type ? type.value : '';
        cards.forEach(function(card){
          var ok = true;
          if(q && (card.getAttribute('data-search') || '').indexOf(q) === -1) ok = false;
          if(y && card.getAttribute('data-year') !== y) ok = false;
          if(t && card.getAttribute('data-type') !== t) ok = false;
          card.hidden = !ok;
        });
      }
      if(search) search.addEventListener('input', apply);
      if(year) year.addEventListener('change', apply);
      if(type) type.addEventListener('change', apply);
    });
  });
})();

(function(){
  function ready(fn){ if(document.readyState !== 'loading') fn(); else document.addEventListener('DOMContentLoaded', fn); }
  var hcaptchaLoading=false;
  function loadHCaptcha(callback){
    if(window.hcaptcha && typeof window.hcaptcha.render === 'function'){ callback(); return; }
    var cfg=window.zoleiForm||{};
    if(!cfg.hcaptchaSiteKey) return;
    var existing=document.querySelector('script[src*="hcaptcha.com/1/api.js"]');
    if(existing){ existing.addEventListener('load', callback, {once:true}); return; }
    if(hcaptchaLoading) return;
    hcaptchaLoading=true;
    var s=document.createElement('script');
    s.src=cfg.hcaptchaApiUrl || ('https://js.hcaptcha.com/1/api.js?render=explicit&hl=' + encodeURIComponent(cfg.lang||'lv'));
    s.async=true; s.defer=true; s.onload=callback; document.head.appendChild(s);
  }
  function renderHCaptcha(ctx){
    ctx=ctx||document;
    var widgets=ctx.querySelectorAll ? ctx.querySelectorAll('.h-captcha[data-sitekey]:not([data-zole-hcaptcha-rendered])') : [];
    if(!widgets.length) return;
    loadHCaptcha(function(){
      if(!(window.hcaptcha && typeof window.hcaptcha.render === 'function')) return;
      widgets.forEach(function(w){
        if(w.dataset.zoleHcaptchaRendered === '1' || w.querySelector('iframe')) return;
        try { window.hcaptcha.render(w, { sitekey:w.getAttribute('data-sitekey'), hl:w.getAttribute('data-hl') || (window.zoleiForm && zoleiForm.lang) || 'lv' }); w.dataset.zoleHcaptchaRendered='1'; } catch(e) {}
      });
    });
  }
  ready(function(){
    renderHCaptcha(document);
    var observer=new MutationObserver(function(mutations){ mutations.forEach(function(m){ m.addedNodes && m.addedNodes.forEach(function(n){ if(n.nodeType===1) renderHCaptcha(n); }); }); });
    observer.observe(document.documentElement,{childList:true,subtree:true});
  });
})();


(function(){
  function ready(fn){ if(document.readyState !== 'loading') fn(); else document.addEventListener('DOMContentLoaded', fn); }
  ready(function(){
    document.addEventListener('click', function(e){
      var btn = e.target.closest('.zole-copy-link');
      if(!btn) return;
      var url = btn.getAttribute('data-copy-url') || window.location.href;
      function done(){ btn.classList.add('is-copied'); btn.textContent='✓'; setTimeout(function(){ btn.classList.remove('is-copied'); btn.textContent='⛓'; }, 1400); }
      if(navigator.clipboard && navigator.clipboard.writeText){ navigator.clipboard.writeText(url).then(done).catch(function(){ window.prompt('Copy link:', url); }); }
      else { window.prompt('Copy link:', url); }
    });
  });
})();

(function(){
  function ready(fn){ if(document.readyState !== 'loading') fn(); else document.addEventListener('DOMContentLoaded', fn); }
  ready(function(){
    document.querySelectorAll('.zole-results-wrap').forEach(function(root){
      var grid = root.querySelector('[data-zole-pdf-grid]');
      if(!grid) return;
      var search = root.querySelector('.zole-pdf-search');
      var year = root.querySelector('.zole-pdf-year');
      var type = root.querySelector('.zole-pdf-type');
      var button = root.querySelector('.zole-pdf-load-more');
      var cards = Array.prototype.slice.call(root.querySelectorAll('.zole-result-card'));
      var initial = parseInt(grid.getAttribute('data-initial') || '6', 10);
      var step = parseInt(grid.getAttribute('data-step') || '6', 10);
      var visibleLimit = initial;
      function matches(card){
        var q = search ? search.value.trim().toLowerCase() : '';
        var y = year ? year.value : '';
        var t = type ? type.value : '';
        if(q && (card.getAttribute('data-search') || '').indexOf(q) === -1) return false;
        if(y && card.getAttribute('data-year') !== y) return false;
        if(t && card.getAttribute('data-type') !== t) return false;
        return true;
      }
      function apply(reset){
        if(reset) visibleLimit = initial;
        var matched = cards.filter(matches);
        var shown = 0;
        cards.forEach(function(card){
          var ok = matched.indexOf(card) !== -1;
          if(ok && shown < visibleLimit){ card.hidden = false; shown++; }
          else { card.hidden = true; }
        });
        if(button){
          var hasMore = matched.length > visibleLimit;
          button.hidden = !hasMore;
          button.setAttribute('aria-hidden', hasMore ? 'false' : 'true');
          button.setAttribute('data-total', String(matched.length));
          button.setAttribute('data-visible', String(Math.min(visibleLimit, matched.length)));
        }
      }
      if(search) search.addEventListener('input', function(){ apply(true); });
      if(year) year.addEventListener('change', function(){ apply(true); });
      if(type) type.addEventListener('change', function(){ apply(true); });
      if(button) button.addEventListener('click', function(){ visibleLimit += step; apply(false); });
      apply(true);
    });
  });
})();

(function(){
  function ready(fn){ if(document.readyState !== 'loading') fn(); else document.addEventListener('DOMContentLoaded', fn); }
  function esc(s){ return String(s || '').replace(/[&<>"']/g,function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]; }); }
  ready(function(){
    var modal = null;
    var lastFocus = null;
    function close(){
      if(!modal) return;
      modal.remove();
      modal = null;
      document.body.classList.remove('zole-pdf-lightbox-open');
      if(lastFocus && lastFocus.focus) { try { lastFocus.focus(); } catch(e){} }
    }
    function open(trigger){
      var url = trigger.getAttribute('data-pdf-url') || trigger.getAttribute('href');
      if(!url) return;
      lastFocus = trigger;
      close();
      var title = trigger.getAttribute('data-pdf-title') || trigger.textContent || 'PDF';
      var previewLabel = trigger.getAttribute('data-preview-label') || 'PDF preview';
      var openLabel = trigger.getAttribute('data-open-label') || 'Open new tab';
      var downloadLabel = trigger.getAttribute('data-download-label') || 'Download';
      var closeLabel = trigger.getAttribute('data-close-label') || 'Close';
      var helpLabel = trigger.getAttribute('data-help-label') || 'If the preview is hard to read, open the PDF in a new tab or download it.';
      modal = document.createElement('div');
      modal.className = 'zole-pdf-lightbox';
      modal.setAttribute('role','dialog');
      modal.setAttribute('aria-modal','true');
      modal.setAttribute('aria-labelledby','zolePdfLightboxTitle');
      modal.innerHTML = ''+
        '<button class="zole-pdf-lightbox-backdrop" type="button" aria-label="'+esc(closeLabel)+'"></button>'+
        '<div class="zole-pdf-lightbox-panel">'+
          '<header class="zole-pdf-lightbox-header">'+
            '<div><span>'+esc(previewLabel)+'</span><h3 id="zolePdfLightboxTitle">'+esc(title)+'</h3><p>'+esc(helpLabel)+'</p></div>'+
            '<button class="zole-pdf-lightbox-close" type="button" aria-label="'+esc(closeLabel)+'">×</button>'+
          '</header>'+
          '<div class="zole-pdf-lightbox-framewrap">'+
            '<iframe class="zole-pdf-lightbox-frame" src="'+esc(url)+'#toolbar=1&navpanes=0&view=FitH" title="'+esc(title)+'" loading="lazy"></iframe>'+
            '<div class="zole-pdf-lightbox-fallback"><strong>'+esc(title)+'</strong><p>'+esc(helpLabel)+'</p></div>'+
          '</div>'+
          '<footer class="zole-pdf-lightbox-actions">'+
            '<a class="zole-btn zole-btn-green" href="'+esc(url)+'" target="_blank" rel="noopener">'+esc(openLabel)+' ↗</a>'+
            '<a class="zole-btn zole-btn-gold" href="'+esc(url)+'" download>'+esc(downloadLabel)+' ↓</a>'+
            '<button class="zole-btn zole-btn-ghost" type="button">'+esc(closeLabel)+'</button>'+
          '</footer>'+
        '</div>';
      document.body.appendChild(modal);
      document.body.classList.add('zole-pdf-lightbox-open');
      modal.querySelector('.zole-pdf-lightbox-close').focus();
      modal.querySelector('.zole-pdf-lightbox-backdrop').addEventListener('click', close);
      modal.querySelector('.zole-pdf-lightbox-close').addEventListener('click', close);
      modal.querySelector('.zole-pdf-lightbox-actions button').addEventListener('click', close);
    }
    document.addEventListener('click', function(ev){
      var trigger = ev.target.closest('.zole-pdf-preview-trigger');
      if(!trigger) return;
      ev.preventDefault();
      open(trigger);
    });
    document.addEventListener('keydown', function(ev){
      if(ev.key === 'Escape' && modal){ close(); }
    });
  });
})();


(function(){
  function ready(fn){ if(document.readyState !== 'loading') fn(); else document.addEventListener('DOMContentLoaded', fn); }
  ready(function(){
    if(document.querySelector('.zole-scroll-up')) return;
    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'zole-scroll-up';
    btn.setAttribute('aria-label', 'Scroll to top');
    btn.innerHTML = '<span>↑</span>';
    document.body.appendChild(btn);
    function toggle(){ if(window.scrollY > 520) btn.classList.add('is-visible'); else btn.classList.remove('is-visible'); }
    window.addEventListener('scroll', toggle, { passive:true });
    toggle();
    btn.addEventListener('click', function(){ window.scrollTo({ top:0, behavior:'smooth' }); });
  });
})();
