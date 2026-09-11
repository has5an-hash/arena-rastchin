(function(){
'use strict';
var root=document;
root.querySelectorAll('[data-demo-filter]').forEach(function(button){button.addEventListener('click',function(){
 var group=button.dataset.demoFilter,count=0;
 root.querySelectorAll('[data-demo-filter]').forEach(function(b){var selected=b===button;b.classList.toggle('active',selected);b.setAttribute('aria-pressed',String(selected));});
 root.querySelectorAll('[data-demo-group]').forEach(function(card){card.hidden=group!=='all'&&card.dataset.demoGroup!==group;if(!card.hidden)count++;});
 var extra=root.querySelector('.hub-choose-card');if(extra)extra.hidden=group!=='all';
 var status=root.querySelector('.hub-filter-status');if(status)status.textContent=new Intl.NumberFormat('fa-IR').format(count)+' دمو برای کشف‌کردن';
});});
root.querySelectorAll('[data-mood]').forEach(function(button){button.addEventListener('click',function(){
 var section=button.closest('.studio-discovery');
 section.querySelectorAll('[data-mood]').forEach(function(b){b.setAttribute('aria-pressed',String(b===button));});
 section.querySelectorAll('[data-mood-result]').forEach(function(card){card.hidden=card.dataset.moodResult!==button.dataset.mood;});
});});
root.querySelectorAll('[data-nail-fit]').forEach(function(lab){
 var state={life:'daily',length:'short',mood:'minimal'};
 var shapes={short:'اسکووال کوتاه',medium:'بادامی متعادل',long:'بالرین کشیده'};
 var finishes={minimal:'نود شیری با خط ظریف',gloss:'ژل براق با درخشش کنترل‌شده',art:'طراحی اختصاصی با یک نقطه تأکیدی'};
 var care={daily:'ترمیم هر ۳ تا ۴ هفته',active:'فرم مقاوم و بررسی پس از ۳ هفته',event:'آماده‌سازی ۲ تا ۳ روز پیش از مراسم'};
 function update(){
  var title=shapes[state.length]+' و '+(state.mood==='minimal'?'مینیمال':state.mood==='gloss'?'براق':'هنری');
  lab.querySelector('[data-fit-title]').textContent=title;
  lab.querySelector('[data-fit-shape]').textContent=shapes[state.length];
  lab.querySelector('[data-fit-finish]').textContent=finishes[state.mood];
  lab.querySelector('[data-fit-care]').textContent=care[state.life];
  lab.classList.remove('is-updated');void lab.offsetWidth;lab.classList.add('is-updated');
 }
 lab.querySelectorAll('[data-fit-key]').forEach(function(button){button.addEventListener('click',function(){
  var key=button.dataset.fitKey;state[key]=button.dataset.fitValue;
  lab.querySelectorAll('[data-fit-key="'+key+'"]').forEach(function(choice){choice.setAttribute('aria-pressed',String(choice===button));});
  update();
 });});
 update();
});
root.addEventListener('click',function(e){root.querySelectorAll('.studio-switch[open]').forEach(function(menu){if(!menu.contains(e.target))menu.open=false;});});
root.addEventListener('keydown',function(e){if(e.key==='Escape')root.querySelectorAll('.studio-switch[open]').forEach(function(menu){menu.open=false;menu.querySelector('summary').focus();});});
root.querySelectorAll('.folio').forEach(function(card){
 var link=card.querySelector('a[data-lightbox]');if(!link)return;
 var key='beautia-inspiration:'+link.href,button=document.createElement('button');
 button.type='button';button.className='inspiration-save';button.setAttribute('aria-label','ذخیره در الهام‌های من');
 function show(saved){button.textContent=saved?'♥':'♡';button.setAttribute('aria-pressed',String(saved));button.setAttribute('aria-label',saved?'حذف از الهام‌های من':'ذخیره در الهام‌های من');card.dataset.saved=String(saved);}
 try{show(localStorage.getItem(key)==='1');}catch(e){show(false);}
 button.addEventListener('click',function(e){e.preventDefault();e.stopPropagation();var saved=button.getAttribute('aria-pressed')!=='true';try{if(saved)localStorage.setItem(key,'1');else localStorage.removeItem(key);}catch(err){}show(saved);root.dispatchEvent(new Event('inspirationchange'));});
 card.appendChild(button);
});
root.querySelectorAll('.masonry').forEach(function(grid){
 var cards=Array.from(grid.querySelectorAll('.folio'));if(!cards.length)return;
 var toolbar=document.createElement('div');toolbar.className='inspiration-toolbar';
 var all=document.createElement('button'),saved=document.createElement('button'),status=document.createElement('span');
 all.type=saved.type='button';all.textContent='همه ایده‌ها';saved.textContent='الهام‌های من';status.setAttribute('aria-live','polite');
 toolbar.append(all,saved,status);grid.before(toolbar);
 var empty=document.createElement('p');empty.className='inspiration-empty';empty.textContent='هنوز ایده‌ای ذخیره نکرده‌اید؛ قلب کنار عکس دلخواهتان را لمس کنید.';grid.append(empty);
 var onlySaved=false;
 function refresh(){var count=cards.filter(function(c){return c.dataset.saved==='true';}).length;all.setAttribute('aria-pressed',String(!onlySaved));saved.setAttribute('aria-pressed',String(onlySaved));cards.forEach(function(c){c.hidden=onlySaved&&c.dataset.saved!=='true';});empty.hidden=!onlySaved||count>0;status.textContent=new Intl.NumberFormat('fa-IR').format(count)+' ایده ذخیره‌شده';}
 all.addEventListener('click',function(){onlySaved=false;refresh();});
 saved.addEventListener('click',function(){onlySaved=true;var section=grid.parentElement;section.querySelectorAll('.filter').forEach(function(b){b.classList.toggle('is-active',b.dataset.filter==='*');});cards.forEach(function(c){c.classList.remove('is-hidden');});refresh();});
 root.addEventListener('inspirationchange',refresh);refresh();
});
})();
