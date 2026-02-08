/**
 * assets/js/chat.js - Chat independiente
 * - Lógica de chat, polling, notificaciones, saludo, alias handleSend
 * - Funciones de pre-chat (startChatSession, showChatArea, showForm)
 */
(function(){
  'use strict';

  const CONFIG = {
    CHAT_POLL_BASE: 3000,
    CHAT_TIMEOUT_MS: 7000,
    TYPING_TIMEOUT: 2000
  };

  const APP_STATE = { typingTimer: null };
  const Logger = {
    debug: (msg, ...args) => { if (window.location.hostname === 'localhost') console.log('[DEBUG]', msg, ...args); },
    warn: (msg, ...args) => console.warn('[WARN]', msg, ...args),
    error: (msg, ...args) => console.error('[ERROR]', msg, ...args)
  };

  const isValidEmail = (e)=>/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e);

  const Chat = (() => {
    let notificationSound = null, soundMuted = false;
    let pollInterval = CONFIG.CHAT_POLL_BASE, pollActive = false, pollTimer = null, chatOpen = false;
    let lastServerHtml = null;

    function getEmail(){
      const s = localStorage.getItem('uwf_chat_email');
      if (s && isValidEmail(s)) return s;
      const el = document.getElementById('u-email'); const v = el ? el.value.trim() : '';
      return isValidEmail(v) ? v : '';
    }
    function hasEmail(){ return !!getEmail(); }

    function showAndAutoHideAdminStatus(){
      try {
        const status = document.querySelector('#chat-main-area .admin-status');
        if (status) {
          status.style.display = 'flex';
          status.classList.remove('fade-away');
          setTimeout(()=>{
            if (status && !status.classList.contains('fade-away')) {
              status.classList.add('fade-away');
              status.addEventListener('transitionend', () => { try { status.style.display='none'; } catch(_){ } }, { once: true });
            }
          }, 5000);
        }
      } catch(_){}
    }

    function scrollBottom(){ const h = document.getElementById('chat-history'); if (h) { h.scrollTop = h.scrollHeight; } }

    function updateSendAvailability(){
      const btn = document.querySelector('.btn-send-chat');
      const msg = (document.getElementById('u-msg')?.value || '').trim();
      if (!btn) return;
      btn.toggleAttribute('disabled', !(hasEmail() && msg.length>0));
      btn.classList.toggle('btn-loading', false);
    }

    function init(){
      try { notificationSound = new Audio('assets/sounds/notify.mp3'); notificationSound.volume = 0.5; } catch(_){}
      const soundBtn = document.getElementById('toggle-sound');
      try {
        soundMuted = localStorage.getItem('uwf_sound_muted') === '1';
        if (notificationSound) notificationSound.muted = soundMuted;
        if (soundBtn) {
          soundBtn.setAttribute('aria-pressed', soundMuted ? 'true' : 'false');
          soundBtn.innerHTML = soundMuted ? '<i class="fa fa-bell-slash"></i>' : '<i class="fa fa-bell"></i>';
          soundBtn.addEventListener('click',(e)=>{ e.stopPropagation(); soundMuted=!soundMuted;
            try{ if(notificationSound) notificationSound.muted = soundMuted; localStorage.setItem('uwf_sound_muted', soundMuted?'1':'0'); }catch(_){}
            soundBtn.setAttribute('aria-pressed', soundMuted ? 'true' : 'false');
            soundBtn.innerHTML = soundMuted ? '<i class="fa fa-bell-slash"></i>' : '<i class="fa fa-bell"></i>';
          });
        }
      } catch(_){}

      window.openChat = openChat;
      window.closeChat = closeChat;
      window.toggleChat = debounce(toggleChat, 120);
      window.startChatPolling = startPolling;
      window.loadHistory = loadHistory;
      window.openChatSettings = openChatSettings;

      setupInputs();
      setupHeaderAccessibility();
      setupVisibility();

      // Setup resize listeners for mobile bounds
      window.addEventListener('resize', ensureMobileBounds, { passive: true });
      try { 
        if (window.visualViewport) {
          window.visualViewport.addEventListener('resize', ensureMobileBounds, { passive: true }); 
        }
      } catch(_){}
      
      // Initialize mobile bounds on load
      document.addEventListener('DOMContentLoaded', ensureMobileBounds);
      ensureMobileBounds();

      startPolling(true);
      updateSendAvailability();

      // Alias compatibilidad
      window.sendSignal = sendSignal;
      window.handleSend = function(){ if (typeof window.sendSignal === 'function') window.sendSignal(); };
    }

    function debounce(fn, wait){ let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), wait); }; }

    // Mobile detection helper
    function isMobile(){ return window.matchMedia('(max-width: 480px)').matches; }

    // Ensure mobile bounds for chat container
    function ensureMobileBounds(){
      const container = document.getElementById('uwf-chat-container');
      if (!container) return;
      if (!isMobile()) { 
        container.style.width=''; 
        container.style.maxHeight=''; 
        container.style.minHeight=''; 
        return; 
      }
      
      // Batch all DOM reads first to avoid forced reflow
      const vw = Math.min(window.innerWidth, document.documentElement.clientWidth);
      const isFullscreen = container.classList.contains('chat-fullscreen');
      const vv = window.visualViewport;
      const viewportHeight = vv ? vv.height : window.innerHeight;
      
      // Then perform all DOM writes together
      const margin = 20;
      container.style.width = Math.max(280, vw - margin) + 'px';
      
      if (!isFullscreen) {
        container.style.minHeight = '60vh';
      } else {
        container.style.minHeight = '';
      }
      
      try {
        if (!isFullscreen && vv) {
          const available = Math.max(360, Math.floor(viewportHeight - 80));
          container.style.maxHeight = available + 'px';
        } else if (!isFullscreen) {
          container.style.maxHeight = '85dvh';
        } else {
          container.style.maxHeight = '';
        }
      } catch(_){}
    }

    // Fullscreen toggle
    window.toggleFullScreen = function(){
      const container = document.getElementById('uwf-chat-container');
      const btn = document.getElementById('toggle-fullscreen');
      if (!container || !btn) return;
      const willEnter = !container.classList.contains('chat-fullscreen');
      container.classList.toggle('chat-fullscreen', willEnter);
      btn.setAttribute('aria-pressed', willEnter ? 'true' : 'false');
      btn.setAttribute('aria-label', willEnter ? 'Contraer chat' : 'Expandir chat');
      btn.setAttribute('title', willEnter ? 'Contraer chat' : 'Expandir chat');
      const icon = btn.querySelector('i');
      if (icon) { 
        icon.classList.remove('fa-expand','fa-compress'); 
        icon.classList.add(willEnter ? 'fa-compress' : 'fa-expand'); 
      }
      if (willEnter) {
        document.body.classList.add('no-scroll');
      } else {
        document.body.classList.remove('no-scroll');
      }
      ensureMobileBounds();
      // Do NOT auto-focus on mobile to avoid forcing keyboard
    };

    function setupHeaderAccessibility(){
      // Header controls setup - only title minimizes chat
      // Sound toggle is already set up in init()
      
      // Add stopPropagation to control buttons
      document.getElementById('toggle-sound')?.addEventListener('click', (e)=>{ e.stopPropagation(); });
      document.getElementById('toggle-settings')?.addEventListener('click', (e)=>{ 
        e.stopPropagation(); 
        (window.openChatSettings||function(){})(); 
      });
      document.getElementById('toggle-fullscreen')?.addEventListener('click', (e)=>{ e.stopPropagation(); });
      document.getElementById('toggle-min')?.addEventListener('click', (e)=>{ 
        e.stopPropagation(); 
        (window.toggleChat||function(){})(); 
      });
    }

    function setupInputs(){
      const input = document.getElementById('u-msg');
      if (input){
        const ih = debounce((e)=>{
          const ind = document.getElementById('chat-status-indicator');
          if (ind){
            if (e.target.value.length>0){
              ind.innerHTML = '<span class="typing-dots"><span>•</span><span>•</span><span>•</span></span>'; ind.style.opacity=1;
              clearTimeout(APP_STATE.typingTimer);
              APP_STATE.typingTimer=setTimeout(()=>{ ind.innerHTML=''; ind.style.opacity=0; }, CONFIG.TYPING_TIMEOUT);
            } else { ind.innerHTML=''; ind.style.opacity=0; }
          }
          updateSendAvailability();
        }, 200);
        input.addEventListener('input', ih);
        input.addEventListener('keypress', (e)=>{ if (e.key==='Enter' && !e.shiftKey) { e.preventDefault(); sendSignal(); } });
      }
      const sendBtn = document.querySelector('.btn-send-chat');
      sendBtn?.addEventListener('click', (e)=>{ e.preventDefault(); sendSignal(); });

      const emailEl = document.getElementById('u-email');
      emailEl?.addEventListener('input', debounce(updateSendAvailability, 200));
    }

    function setupVisibility(){
      document.addEventListener('visibilitychange', ()=>{ if (document.hidden) stopPolling(); else { pollInterval=CONFIG.CHAT_POLL_BASE; startPolling(true);} });
      window.addEventListener('blur', ()=> stopPolling());
      window.addEventListener('focus', ()=> { pollInterval=CONFIG.CHAT_POLL_BASE; startPolling(true); });
    }

    async function loadHistory(force=false){
      try{
        const email = getEmail(); if (!email) return;
        const controller = new AbortController(); const tId = setTimeout(()=>controller.abort(), CONFIG.CHAT_TIMEOUT_MS);
        const res = await fetch('chat_engine.php?action=load&email='+encodeURIComponent(email), { method:'GET', cache:'no-store', signal: controller.signal });
        clearTimeout(tId);
        if (!res.ok) throw new Error('HTTP '+res.status);
        const html = await res.text();
        updateHistory(html, !!force);
      } catch(_){}
    }

    function updateHistory(serverHtml, fromPoll=false){
      if (serverHtml === lastServerHtml) return;
      lastServerHtml = serverHtml;

      const c = document.getElementById('chat-history'); if (!c) return;
      c.innerHTML = serverHtml;

      if (!serverHtml || !serverHtml.trim()) {
        // primera vez sin historial: opcionalmente podrías insertar un saludo aquí
      }

      scrollBottom();

      if (fromPoll && !chatOpen) {
        const lastBubble = c.querySelector('.msg-bubble:last-child');
        const adminLast = lastBubble && lastBubble.classList.contains('msg-admin');
        if (adminLast) {
          const badge = document.getElementById('chat-notif-badge');
          if (badge) {
            badge.style.display='block';
            const cur = parseInt(badge.textContent || '0', 10) || 0;
            badge.textContent = String(cur + 1);
          }
          try{ if (!soundMuted && notificationSound) notificationSound.play().catch(()=>{}); }catch(_){}
        }
      }
    }

    async function sendSignal(){
      const input = document.getElementById('u-msg');
      const message = input ? input.value.trim() : '';
      const email = getEmail();
      if (!email || !message){ updateSendAvailability(); return; }

      const form = new FormData(); form.append('email', email); form.append('message', message);
      const btn = document.querySelector('.btn-send-chat'); if (btn) { btn.classList.add('btn-loading'); btn.setAttribute('disabled','disabled'); }
      try{
        const controller = new AbortController(); const tId = setTimeout(()=>controller.abort(), CONFIG.CHAT_TIMEOUT_MS);
        const res = await fetch('chat_engine.php', { method:'POST', body: form, signal: controller.signal });
        clearTimeout(tId); if (!res.ok) throw new Error('HTTP '+res.status);
        if (input) input.value = '';
        await loadHistory(true);
      } catch(e){ Logger.error('sendSignal failed', e); }
      finally { if (btn) { btn.classList.remove('btn-loading'); btn.removeAttribute('disabled'); } updateSendAvailability(); }
    }

    async function fetchOnce(){
      try{
        const email = getEmail(); if (!email) return;
        const controller = new AbortController(); const tId = setTimeout(()=>controller.abort(), CONFIG.CHAT_TIMEOUT_MS);
        const res = await fetch('chat_engine.php?action=load&email='+encodeURIComponent(email), { method:'GET', cache:'no-store', signal: controller.signal });
        clearTimeout(tId); if (!res.ok) throw new Error('HTTP '+res.status);
        const html = await res.text();
        updateHistory(html, true);
        pollInterval = CONFIG.CHAT_POLL_BASE;
      } catch(_){ pollInterval = Math.min(CONFIG.CHAT_POLL_BASE*8, pollInterval*2); }
    }

    function schedule(){ if (!pollActive) return; clearTimeout(pollTimer);
      pollTimer=setTimeout(async()=>{ if(!pollActive) return; await fetchOnce(); schedule(); }, pollInterval);
    }
    function startPolling(immediate=false){ if (pollActive) return; pollActive=true; pollInterval = CONFIG.CHAT_POLL_BASE; if (immediate) fetchOnce().then(schedule); else schedule(); }
    function stopPolling(){ pollActive=false; clearTimeout(pollTimer); }

    function openChat(){
      const container = document.getElementById('uwf-chat-container'); if (!container) return;
      const mainArea = document.getElementById('chat-main-area'); const preForm = document.getElementById('pre-chat-form');
      if (getEmail()) { preForm && (preForm.style.display='none'); mainArea && (mainArea.style.display='grid'); }
      else { preForm && (preForm.style.display='flex'); mainArea && (mainArea.style.display='none'); }
      container.classList.remove('chat-closed'); chatOpen=true;

      const badge=document.getElementById('chat-notif-badge'); if (badge){ badge.style.display='none'; badge.textContent='0'; }
      const toast=document.getElementById('chat-toast'); if (toast){ toast.classList.remove('show'); toast.style.display='none'; }

      // Apply mobile bounds
      ensureMobileBounds();
      
      // Set min-height for history if empty
      const history = document.getElementById('chat-history');
      if (history && !history.innerHTML.trim()) {
        history.style.minHeight = '220px';
      }

      pollInterval=CONFIG.CHAT_POLL_BASE; fetchOnce(); updateSendAvailability();

      // auto-hide admin-status a los 5s - only if user has already entered data
      if (getEmail()) {
        showAndAutoHideAdminStatus();
      }

      // Re-apply mobile bounds after a delay for layout settlement
      setTimeout(()=>{ ensureMobileBounds(); }, 150);

      requestAnimationFrame(()=>{ scrollBottom();
        // Only focus on desktop, not on mobile to avoid forcing keyboard
        if (!isMobile()) {
          try{
            const first = container.querySelector('#u-msg') || container.querySelector('input,button,[tabindex]:not([tabindex="-1"])');
            first?.focus?.({preventScroll:true});
          }catch(_){ }
        }
      });
    }
    function closeChat(){
      const container = document.getElementById('uwf-chat-container'); if (!container) return;
      container.classList.add('chat-closed'); const badge=document.getElementById('chat-notif-badge'); if (badge){ badge.style.display='none'; badge.textContent='0'; } chatOpen=false;
    }
    function toggleChat(){ const c=document.getElementById('uwf-chat-container'); if (!c) return; const willOpen=c.classList.contains('chat-closed'); if (willOpen) openChat(); else closeChat(); }

    function openChatSettings(){
      const pre = document.getElementById('pre-chat-form'); const main = document.getElementById('chat-main-area');
      if (pre) pre.style.display = 'flex'; if (main) main.style.display = 'none';
      // focus primer campo
      setTimeout(()=>{ document.getElementById('chat-name')?.focus(); }, 100);
    }

    return { init, startPolling, stopPolling, loadHistory, openChat, closeChat, toggleChat, showAndAutoHideAdminStatus };
  })();

  // Pre-chat API global
  window.startChatSession = function(event){
    if (event && event.preventDefault) event.preventDefault();
    const nameEl = document.getElementById('chat-name');
    const emailEl = document.getElementById('u-email');
    const phoneEl = document.getElementById('chat-phone');
    if (!nameEl || !emailEl) return false;

    const name = nameEl.value.trim();
    const email = emailEl.value.trim();
    const phone = phoneEl ? phoneEl.value.trim() : '';

    if (!name || name.length < 2) { alert((document.documentElement.lang||'en').startsWith('es') ? 'Por favor ingresa tu nombre completo (mínimo 2 caracteres)' : 'Please enter your full name (min 2 characters)'); nameEl.focus(); return false; }
    if (!email || !isValidEmail(email)) { alert((document.documentElement.lang||'en').startsWith('es') ? 'Por favor ingresa un correo electrónico válido' : 'Please enter a valid email address'); emailEl.focus(); return false; }

    const userData = { name, email, phone };
    try {
      localStorage.setItem('uwf_user', JSON.stringify(userData));
      localStorage.setItem('uwf_chat_email', email);
    } catch (e) { 
      // Log error but continue - chat should work even if localStorage fails
      console.error("localStorage error:", e); 
    }
    // Show chat area regardless of localStorage success - we have userData in memory
    showChatArea(userData);
    if (typeof window.loadHistory === 'function') setTimeout(() => window.loadHistory(true), 200);
    return false;
  };
  window.showChatArea = function(data){
    const preForm = document.getElementById('pre-chat-form');
    const mainArea = document.getElementById('chat-main-area');
    if (preForm) preForm.style.display = 'none';
    if (mainArea) { mainArea.style.display = 'grid'; }
    
    // Show and auto-hide admin-status when user enters chat
    if (typeof Chat !== 'undefined' && typeof Chat.showAndAutoHideAdminStatus === 'function') {
      Chat.showAndAutoHideAdminStatus();
    }
    
    setTimeout(()=>{ document.getElementById('u-msg')?.focus(); }, 150);
  };
  window.showForm = function(){
    const preForm = document.getElementById('pre-chat-form');
    const mainArea = document.getElementById('chat-main-area');
    if (preForm) preForm.style.display = 'flex';
    if (mainArea) mainArea.style.display = 'none';
  };

  document.addEventListener('DOMContentLoaded', () => {
    try { Chat.init(); } catch(e){ Logger.error('Chat init error', e); }
  });
})();