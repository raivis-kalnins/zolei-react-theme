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
      input.addEventListener('input',function(){
        var term=input.value.trim(); clearTimeout(timer);
        if(term.length<2){ box.innerHTML=''; box.classList.remove('is-visible'); return; }
        box.innerHTML='<div class="zole-search-state">'+htmlEscape((window.zoleiSearch&&zoleiSearch.labels&&zoleiSearch.labels.searching)||'Searching...')+'</div>'; box.classList.add('is-visible');
        timer=setTimeout(function(){
          var params=new URLSearchParams({action:'zolei_ajax_search',nonce:(zoleiSearch&&zoleiSearch.nonce)||'',term:term});
          fetch((zoleiSearch&&zoleiSearch.ajaxUrl)||'/wp-admin/admin-ajax.php?'+params.toString(),{credentials:'same-origin',method:'GET'}).then(function(r){return r.json();}).then(function(json){
            var data=(json&&json.data)||{}; var items=data.items||[];
            if(!items.length){ box.innerHTML='<div class="zole-search-state">'+htmlEscape((zoleiSearch&&zoleiSearch.labels&&zoleiSearch.labels.noResults)||'No results found.')+'</div>'; return; }
            box.innerHTML=items.map(function(it){return '<a class="zole-search-item" href="'+htmlEscape(it.url)+'"><strong>'+htmlEscape(it.title)+'</strong><span>'+htmlEscape(it.excerpt)+'</span></a>';}).join('')+'<a class="zole-search-all" href="'+htmlEscape(data.searchUrl||form.action+'?s='+encodeURIComponent(term))+'">'+htmlEscape((zoleiSearch&&zoleiSearch.labels&&zoleiSearch.labels.viewAll)||'View all results')+'</a>';
          }).catch(function(){ box.innerHTML=''; box.classList.remove('is-visible'); });
        },220);
      });
    });
  });
})();