document.addEventListener('DOMContentLoaded',function(){'use strict';
const root=document.querySelector('.beautia-support');if(!root)return;
const toggle=root.querySelector('.support-toggle'),panel=root.querySelector('#support-panel'),form=root.querySelector('form'),input=root.querySelector('textarea'),log=root.querySelector('.support-messages'),error=root.querySelector('.support-error');
const key='beautia-chat-'+(BeautiaChat.demo||'hub');let token;try{token=localStorage.getItem(key);}catch(e){}
if(!/^[a-f0-9]{64}$/.test(token||'')){token=Array.from(crypto.getRandomValues(new Uint8Array(32)),b=>b.toString(16).padStart(2,'0')).join('');try{localStorage.setItem(key,token);}catch(e){}}
let timer,last=0,busy=false;
async function request(message=''){const data=new URLSearchParams({action:'beautia_chat',nonce:BeautiaChat.nonce,token,demo:BeautiaChat.demo,message});const res=await fetch(BeautiaChat.url,{method:'POST',body:data,credentials:'same-origin'});const json=await res.json();if(!json.success)throw new Error(json.data?.message||'ارتباط برقرار نشد. دوباره تلاش کنید.');return json.data;}
function render(data){root.querySelector('.support-presence').textContent=data.online?'کارشناس آماده پاسخ‌گویی است':'پیامتان را بگذارید؛ کارشناس پاسخ می‌دهد.';for(const m of data.messages){if(m.id<=last)continue;const p=document.createElement('p');p.className='support-bubble '+m.role;p.textContent=m.text;const t=document.createElement('small');t.textContent=(m.role==='agent'?'کارشناس · ':'شما · ')+m.time;p.append(t);log.append(p);last=m.id;log.scrollTop=log.scrollHeight;}}
async function poll(){if(panel.hidden||document.hidden||busy)return;try{render(await request());}catch(e){error.textContent=e.message;}}
function setOpen(open){panel.hidden=!open;toggle.setAttribute('aria-expanded',String(open));clearInterval(timer);if(open){poll();timer=setInterval(poll,5000);input.focus();}else toggle.focus();}
toggle.addEventListener('click',()=>setOpen(panel.hidden));root.querySelector('.support-close').addEventListener('click',()=>setOpen(false));root.addEventListener('keydown',e=>{if(e.key==='Escape')setOpen(false);});
form.addEventListener('submit',async e=>{e.preventDefault();if(busy||!input.value.trim())return;busy=true;const button=form.querySelector('button');button.disabled=true;error.textContent='';try{render(await request(input.value.trim()));input.value='';}catch(e){error.textContent=e.message;}finally{busy=false;button.disabled=false;}});
});
