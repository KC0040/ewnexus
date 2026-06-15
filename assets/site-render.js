/*
 * EWNexus Site Render Engine
 * 讀取全域 SITE config object，自動生成整個頁面
 * 每個 demo/client 頁面只需要 SITE config，其餘全自動
 */

document.addEventListener('DOMContentLoaded', function () {
  _renderBanner();
  _renderEmergencyBar();
  _renderNav();
  _renderChatWidget();
  _renderHero();
  _renderStats();
  _renderBrands();
  _renderServices();
  _renderInventory();
  _renderReviews();
  _renderFaq();
  _renderCta();
  _renderInfo();
  _renderFooter();
});

/* ── Helpers ──────────────────────────────────────────── */
function _stars(n) { return '★'.repeat(n) + '☆'.repeat(5 - n); }
function _el(id)   { return document.getElementById(id); }
function _set(id, html) { const e = _el(id); if (e) e.innerHTML = html; }
function _hide(id) { const e = _el(id); if (e) e.style.display = 'none'; }

/* ── EWNexus Demo Banner ──────────────────────────────── */
function _renderBanner() {
  if (!SITE.ewnexus?.demo_mode) return;
  const b = document.createElement('div');
  b.className = 'ew-demo-banner';
  b.innerHTML = `<span class="material-symbols-outlined" style="font-size:16px">info</span>
    這是 <strong>EWNexus</strong> 展示網站 &nbsp;·&nbsp;
    <a href="https://www.ewnexus.com/contact.html">$500 全包 — 3 天上線 →</a>`;
  document.body.prepend(b);
}

/* ── Emergency Bar（HVAC / 水電緊急） ─────────────────── */
function _renderEmergencyBar() {
  const eb = SITE.emergency_bar;
  if (!eb?.enabled) return;
  document.body.classList.add('has-emergency');
  const bar = document.createElement('div');
  bar.className = 'emergency-bar';
  bar.innerHTML = `<span class="material-symbols-outlined" style="font-size:18px">emergency_home</span>
    <strong>${eb.text}</strong> &nbsp;—&nbsp;
    <a href="tel:${SITE.business.phone_raw}">${eb.phone || SITE.business.phone}</a>`;
  // insert after banner if exists, else prepend
  const banner = document.querySelector('.ew-demo-banner');
  if (banner) banner.after(bar);
  else document.body.prepend(bar);
}

/* ── Nav ──────────────────────────────────────────────── */
function _renderNav() {
  const b = SITE.business, t = SITE.theme;
  _set('nav', `<div class="nav-inner">
    <div class="nav-logo">
      <span class="material-symbols-outlined" style="color:var(--accent);font-size:22px">${t.nav_icon}</span>
      <em>${b.name}</em>
    </div>
    <div class="nav-links">
      <a href="#services">Services</a>
      ${SITE.inventory ? '<a href="#inventory">Inventory</a>' : ''}
      <a href="#reviews">Reviews</a>
      <a href="#faq">FAQ</a>
      <a href="tel:${b.phone_raw}" class="nav-cta">
        <span class="material-symbols-outlined" style="font-size:15px">call</span>
        ${b.phone}
      </a>
    </div>
  </div>`);
}

/* ── Hero ─────────────────────────────────────────────── */
function _renderHero() {
  const b = SITE.business, h = SITE.hero, t = SITE.theme;
  _set('hero', `
    <div class="hero-bg"></div>
    <div class="glow-blob" style="width:500px;height:500px;background:${t.accent};top:-100px;right:-150px;opacity:.08;position:absolute;border-radius:9999px;filter:blur(120px);pointer-events:none;"></div>
    <div class="hero-content">
      <div>
        <p class="hero-eyebrow">${h.eyebrow}</p>
        <h1>${h.headline}</h1>
        <p class="hero-sub">${h.sub}</p>
        <div class="hero-actions">
          <a href="#quote" class="btn btn-primary">
            <span class="material-symbols-outlined" style="font-size:18px">request_quote</span>
            ${h.cta_primary}
          </a>
          <a href="${h.cta_secondary_href || '#services'}" class="btn btn-outline">${h.cta_secondary}</a>
        </div>
        <div class="badges">${h.badges.map(x => `<span class="badge">${x}</span>`).join('')}</div>
      </div>
      <div class="quote-card" id="quote">
        <h3>${h.cta_primary}</h3>
        <form id="cf" onsubmit="ewSubmitForm(event)">
          <div class="form-group">
            <label class="form-label">Your Name</label>
            <input id="cf-name" class="form-input" type="text" placeholder="John Smith" required/>
          </div>
          <div class="form-group">
            <label class="form-label">${h.form_field2_label || 'Phone Number'}</label>
            <input id="cf-phone" class="form-input" type="tel" placeholder="${h.form_field2_placeholder || '(972) 555-0000'}" required/>
          </div>
          ${h.form_services?.length ? `<div class="form-group">
            <label class="form-label">${h.form_field3_label || 'Service Needed'}</label>
            <select id="cf-service" class="form-input" style="width:100%">
              <option value="">Select…</option>
              ${h.form_services.map(s => `<option>${s}</option>`).join('')}
            </select>
          </div>` : ''}
          <div class="form-group">
            <label class="form-label">${h.form_field4_label || 'Message (optional)'}</label>
            <input id="cf-msg" class="form-input" type="text" placeholder="${h.form_field4_placeholder || ''}"/>
          </div>
          <button type="submit" class="form-submit" id="cf-btn">Send Request</button>
          <div id="cf-ok" style="display:none;text-align:center;padding:var(--sp-5) 0;color:#34d399;font-weight:600;">
            ✅ Got it! We'll be in touch shortly.
          </div>
        </form>
      </div>
    </div>`);
}

/* ── Trust Stats ──────────────────────────────────────── */
function _renderStats() {
  if (!SITE.stats?.length) return _hide('stats');
  _set('stats', `<div class="trust-bar-inner">
    ${SITE.stats.map(s => `<div class="stat">
      <div class="num">${s.num}</div>
      <div class="lbl">${s.lbl}</div>
    </div>`).join('')}
  </div>`);
}

/* ── Brands (optional) ────────────────────────────────── */
function _renderBrands() {
  if (!SITE.brands?.length) return _hide('brands');
  _set('brands', `
    <span style="font-size:12px;font-family:var(--font-mono);text-transform:uppercase;letter-spacing:.12em;color:var(--text-muted);margin-right:8px;">Brands in stock:</span>
    ${SITE.brands.map(br => `<span class="brand-chip">${br}</span>`).join('')}
    <span class="brand-chip">+ More</span>`);
}

/* ── Services ─────────────────────────────────────────── */
function _renderServices() {
  const b = SITE.business;
  _set('services', `<div class="section-wrap">
    <div class="section-header">
      <p class="eyebrow">What We Do</p>
      <h2>Our Services</h2>
      <p>Located in ${b.city}, ${b.state_full}, ${b.name} serves ${b.area_served.slice(0,3).join(', ')} and surrounding areas.</p>
    </div>
    <div class="grid-3">
      ${SITE.services.map(s => `<article class="svc-card">
        <div class="svc-icon"><span class="material-symbols-outlined">${s.icon}</span></div>
        <h3>${s.name}</h3>
        <p>${s.desc}</p>
        <div class="svc-price">${s.price}</div>
      </article>`).join('')}
    </div>
  </div>`);
}

/* ── Inventory (optional) ─────────────────────────────── */
function _renderInventory() {
  const inv = SITE.inventory;
  if (!inv?.items?.length) return _hide('inventory');

  const uniqueVals = key => [...new Set(inv.items.map(i => i[key]))].sort();
  const filterSelects = inv.filters.map(f => `
    <div class="inv-fg">
      <label>${f.charAt(0).toUpperCase() + f.slice(1)}</label>
      <select id="f-${f}" class="inv-select" onchange="ewFilterInv()">
        <option value="">All ${f.charAt(0).toUpperCase() + f.slice(1)}s</option>
        ${uniqueVals(f).map(v => `<option>${v}</option>`).join('')}
      </select>
    </div>`).join('');

  _set('inventory', `<div class="section-wrap">
    <div class="section-header">
      <p class="eyebrow">In Stock Now</p>
      <h2>Inventory</h2>
      <p>Browse available stock. Call to confirm availability.</p>
    </div>
    <div class="inv-filters">
      ${filterSelects}
      <button class="inv-reset" onclick="ewResetInv()">Reset</button>
    </div>
    <p class="inv-count">Showing <span id="inv-n">${inv.items.length}</span> of ${inv.items.length} items</p>
    <div id="inv-wrap">
      <table class="inv-table" id="inv-tbl">
        <thead><tr>${Object.keys(inv.items[0]).map(k => `<th>${k}</th>`).join('')}</tr></thead>
        <tbody id="inv-body"></tbody>
      </table>
    </div>
    <p style="font-size:13px;color:var(--text-muted);margin-top:var(--sp-4);">
      * Don't see your size? Call <a href="tel:${SITE.business.phone_raw}" style="color:var(--accent);font-weight:600;">${SITE.business.phone}</a>
    </p>
  </div>`);

  ewRenderInvRows(inv.items);
}

/* ── Reviews ──────────────────────────────────────────── */
function _renderReviews() {
  const rv = SITE.reviews;
  _set('reviews', `<div class="section-wrap">
    <div class="section-header">
      <p class="eyebrow">What Customers Say</p>
      <h2>Real Reviews</h2>
    </div>
    <div class="rating-box">
      <div class="rating-num">${rv.avg}</div>
      <div>
        <div class="rating-stars">${_stars(Math.round(rv.avg))}</div>
        <div style="font-weight:700;font-size:16px;margin-bottom:2px;">Excellent</div>
        <div class="rating-sub">Based on ${rv.count} ${rv.source_label || 'Google'} reviews</div>
      </div>
    </div>
    <div class="grid-2">
      ${rv.items.map(r => `<div class="review-card">
        <div class="review-stars">${_stars(r.rating)}</div>
        <p class="review-text">"${r.text}"</p>
        <div class="review-author">${r.author} <span style="font-weight:400;color:var(--text-muted)">· ${r.date}</span></div>
      </div>`).join('')}
    </div>
  </div>`);
}

/* ── FAQ ──────────────────────────────────────────────── */
function _renderFaq() {
  _set('faq', `<div class="faq-inner">
    <div style="margin-bottom:var(--sp-8)">
      <p class="eyebrow">Common Questions</p>
      <h2>FAQs</h2>
    </div>
    ${SITE.faq.map(f => `<div class="faq-item">
      <button class="faq-btn" onclick="ewToggleFaq(this)" aria-expanded="false">
        ${f.q}
        <span class="material-symbols-outlined faq-icon">add</span>
      </button>
      <div class="faq-ans">${f.a}</div>
    </div>`).join('')}
  </div>`);
}

/* ── CTA Band ─────────────────────────────────────────── */
function _renderCta() {
  const b = SITE.business, t = SITE.theme;
  _set('cta', `
    <div class="glow-blob" style="width:600px;height:400px;background:${t.accent};top:50%;left:50%;transform:translate(-50%,-50%);position:absolute;border-radius:9999px;filter:blur(120px);opacity:.08;pointer-events:none;"></div>
    <div class="cta-inner">
      <p class="eyebrow">Ready?</p>
      <h2>${SITE.hero.cta_primary}</h2>
      <p>Serving ${b.area_served.join(' · ')}.</p>
      <div class="cta-actions">
        <a href="tel:${b.phone_raw}" class="btn btn-primary">
          <span class="material-symbols-outlined" style="font-size:18px">call</span>
          Call ${b.phone}
        </a>
        <a href="#quote" class="btn btn-outline">Get a Free Quote</a>
      </div>
    </div>`);
}

/* ── Info Bar ─────────────────────────────────────────── */
function _renderInfo() {
  const b = SITE.business;
  _set('info', `<div class="info-bar-inner">
    <div class="info-block">
      <h4>Location</h4>
      <address>
        ${b.address ? b.address + '<br/>' : ''}
        ${b.city}, ${b.state} ${b.zip || ''}<br/>
        <a href="tel:${b.phone_raw}">${b.phone}</a><br/>
        <a href="mailto:${b.email}">${b.email}</a>
      </address>
    </div>
    <div class="info-block">
      <h4>Hours</h4>
      <p>${b.hours.weekday}<br/>${b.hours.saturday}<br/>${b.hours.sunday}</p>
    </div>
    <div class="info-block">
      <h4>Service Area</h4>
      <p>${b.area_served.join(' · ')}</p>
    </div>
  </div>`);
}

/* ── Footer ───────────────────────────────────────────── */
function _renderFooter() {
  const b = SITE.business;
  _set('footer', `<div class="footer-inner">
    <p class="footer-copy">© ${new Date().getFullYear()} ${b.name} · ${b.city}, ${b.state}</p>
    <p class="ew-footer-tag">Website by <a href="https://www.ewnexus.com">EWNexus</a> · $500 · 3-day launch</p>
  </div>`);
}

/* ══ Public helpers (called by HTML event handlers) ════ */

/* Inventory filter */
function ewFilterInv() {
  if (!SITE.inventory) return;
  const result = SITE.inventory.items.filter(item =>
    SITE.inventory.filters.every(f => {
      const val = document.getElementById('f-' + f)?.value;
      return !val || item[f] === val;
    })
  );
  ewRenderInvRows(result);
}

function ewResetInv() {
  SITE.inventory.filters.forEach(f => {
    const el = document.getElementById('f-' + f);
    if (el) el.value = '';
  });
  ewRenderInvRows(SITE.inventory.items);
}

function ewRenderInvRows(items) {
  const keys  = Object.keys(SITE.inventory.items[0]);
  const tbody = document.getElementById('inv-body');
  const count = document.getElementById('inv-n');
  if (!tbody) return;
  if (count) count.textContent = items.length;
  if (!items.length) {
    tbody.innerHTML = `<tr><td colspan="${keys.length}" style="text-align:center;color:var(--text-muted);padding:32px;">
      No items match. <a href="tel:${SITE.business.phone_raw}" style="color:var(--accent)">Call us</a> — we can order anything.
    </td></tr>`;
    return;
  }
  tbody.innerHTML = items.map(item => `<tr>${keys.map(k => {
    if (k === 'condition') return `<td><span class="inv-badge ${item[k]==='New'?'new':'used'}">${item[k]}</span></td>`;
    if (k === 'price')     return `<td style="color:var(--accent);font-weight:700;font-family:var(--font-mono)">${item[k]}</td>`;
    if (k === 'brand')     return `<td style="font-weight:600">${item[k]}</td>`;
    if (k === 'size')      return `<td style="font-family:var(--font-mono);font-size:13px">${item[k]}</td>`;
    return `<td>${item[k]}</td>`;
  }).join('')}</tr>`).join('');
}

/* FAQ toggle */
function ewToggleFaq(btn) {
  const isOpen = btn.getAttribute('aria-expanded') === 'true';
  document.querySelectorAll('.faq-btn').forEach(b => {
    b.setAttribute('aria-expanded', 'false');
    b.nextElementSibling.classList.remove('open');
  });
  if (!isOpen) {
    btn.setAttribute('aria-expanded', 'true');
    btn.nextElementSibling.classList.add('open');
  }
}

/* Form submit */
async function ewSubmitForm(e) {
  e.preventDefault();
  const btn = document.getElementById('cf-btn');
  btn.textContent = 'Sending…';
  btn.disabled = true;
  try {
    await fetch('/api/contact', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        name:    document.getElementById('cf-name')?.value,
        phone:   document.getElementById('cf-phone')?.value,
        service: document.getElementById('cf-service')?.value,
        message: document.getElementById('cf-msg')?.value,
        source:  SITE.ewnexus?.slug || SITE.business.name
      })
    });
  } catch (_) { /* demo — no server required */ }
  document.getElementById('cf').style.display = 'none';
  document.getElementById('cf-ok').style.display = 'block';
}

/* ══ Chat Widget ════════════════════════════════════════ */
function _renderChatWidget() {
  const cb = SITE.chatbot;
  if (!cb?.enabled) return;

  const accent     = SITE.theme.accent;
  const botName    = cb.bot_name || 'Assistant';
  const endpoint   = cb.endpoint || 'https://ewnexus-chatbot.pkxdtf.easypanel.host';
  const slug       = cb.slug || SITE.ewnexus?.slug || '';
  const welcomeMsg = cb.welcome || `Hi! I'm ${botName}. How can I help you today?`;

  // Inject CSS
  const style = document.createElement('style');
  style.textContent = `
    .ew-chat-btn {
      position:fixed; bottom:24px; right:24px; z-index:9000;
      width:56px; height:56px; border-radius:9999px;
      background:${accent}; border:none; cursor:pointer;
      box-shadow:0 4px 20px rgba(0,0,0,0.35);
      display:flex; align-items:center; justify-content:center;
      transition:transform .2s, box-shadow .2s;
    }
    .ew-chat-btn:hover { transform:scale(1.08); box-shadow:0 8px 28px rgba(0,0,0,0.45); }
    .ew-chat-btn .material-symbols-outlined { color:#000; font-size:26px; }

    .ew-chat-panel {
      position:fixed; bottom:92px; right:24px; z-index:8999;
      width:340px; max-height:520px;
      background:#141414; border:1px solid rgba(255,255,255,0.1);
      border-radius:20px; overflow:hidden;
      box-shadow:0 16px 48px rgba(0,0,0,0.6);
      display:flex; flex-direction:column;
      transform:translateY(20px) scale(0.97);
      opacity:0; pointer-events:none;
      transition:transform .25s ease, opacity .25s ease;
    }
    .ew-chat-panel.open {
      transform:translateY(0) scale(1);
      opacity:1; pointer-events:all;
    }
    @media(max-width:400px){
      .ew-chat-panel { width:calc(100vw - 24px); right:12px; }
    }

    .ew-chat-head {
      background:${accent}; padding:14px 16px;
      display:flex; align-items:center; justify-content:space-between;
    }
    .ew-chat-head-info { display:flex; align-items:center; gap:10px; }
    .ew-chat-avatar {
      width:36px; height:36px; border-radius:9999px;
      background:rgba(0,0,0,0.2);
      display:flex; align-items:center; justify-content:center;
      font-size:18px;
    }
    .ew-chat-head-name { font-family:'Space Grotesk',sans-serif; font-weight:800; font-size:15px; color:#000; }
    .ew-chat-head-status { font-size:11px; color:rgba(0,0,0,0.6); margin-top:1px; }
    .ew-chat-close { background:none; border:none; cursor:pointer; color:#000; opacity:0.6; font-size:20px; line-height:1; }
    .ew-chat-close:hover { opacity:1; }

    .ew-chat-msgs {
      flex:1; overflow-y:auto; padding:16px 14px;
      display:flex; flex-direction:column; gap:10px;
      scroll-behavior:smooth;
    }
    .ew-chat-msgs::-webkit-scrollbar { width:3px; }
    .ew-chat-msgs::-webkit-scrollbar-thumb { background:rgba(255,255,255,0.1); border-radius:2px; }

    .ew-msg {
      max-width:82%; padding:10px 14px; border-radius:14px;
      font-size:14px; line-height:1.55; font-family:'Inter',sans-serif;
    }
    .ew-msg.bot {
      background:#242424; color:#f0f0f0; align-self:flex-start;
      border-bottom-left-radius:4px;
    }
    .ew-msg.user {
      background:${accent}; color:#000; align-self:flex-end;
      border-bottom-right-radius:4px; font-weight:600;
    }
    .ew-typing {
      display:flex; gap:5px; align-items:center;
      padding:10px 14px; background:#242424;
      border-radius:14px; border-bottom-left-radius:4px;
      align-self:flex-start;
    }
    .ew-typing span {
      width:7px; height:7px; border-radius:9999px;
      background:rgba(255,255,255,0.4);
      animation:ew-bounce .9s infinite;
    }
    .ew-typing span:nth-child(2) { animation-delay:.15s; }
    .ew-typing span:nth-child(3) { animation-delay:.3s; }
    @keyframes ew-bounce {
      0%,80%,100% { transform:translateY(0); }
      40%         { transform:translateY(-6px); }
    }

    .ew-chat-input-row {
      padding:10px 12px;
      border-top:1px solid rgba(255,255,255,0.07);
      display:flex; gap:8px;
    }
    .ew-chat-input {
      flex:1; background:#242424; border:1.5px solid rgba(255,255,255,0.1);
      border-radius:10px; padding:9px 12px;
      font-family:'Inter',sans-serif; font-size:14px; color:#f0f0f0;
      outline:none; transition:border-color .2s;
    }
    .ew-chat-input:focus { border-color:${accent}; }
    .ew-chat-input::placeholder { color:rgba(255,255,255,0.3); }
    .ew-chat-send {
      background:${accent}; color:#000; border:none;
      border-radius:10px; padding:0 16px;
      font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:14px;
      cursor:pointer; transition:opacity .2s;
    }
    .ew-chat-send:hover { opacity:.85; }
    .ew-chat-send:disabled { opacity:.4; cursor:default; }
  `;
  document.head.appendChild(style);

  // Build widget HTML
  const wrap = document.createElement('div');
  wrap.innerHTML = `
    <button class="ew-chat-btn" id="ew-chat-toggle" onclick="ewToggleChat()" aria-label="Open chat">
      <span class="material-symbols-outlined" id="ew-chat-icon">chat</span>
    </button>
    <div class="ew-chat-panel" id="ew-chat-panel" role="dialog" aria-label="${botName} chat">
      <div class="ew-chat-head">
        <div class="ew-chat-head-info">
          <div class="ew-chat-avatar">🤖</div>
          <div>
            <div class="ew-chat-head-name">${botName}</div>
            <div class="ew-chat-head-status">● Online — AI Assistant</div>
          </div>
        </div>
        <button class="ew-chat-close" onclick="ewToggleChat()" aria-label="Close">✕</button>
      </div>
      <div class="ew-chat-msgs" id="ew-chat-msgs"></div>
      <div class="ew-chat-input-row">
        <input class="ew-chat-input" id="ew-chat-input"
          placeholder="Type a message…"
          onkeydown="if(event.key==='Enter')ewSendChat()"/>
        <button class="ew-chat-send" id="ew-chat-send" onclick="ewSendChat()">Send</button>
      </div>
    </div>`;
  document.body.appendChild(wrap);

  // Store state
  window._ewChat = { endpoint, slug, sessionId: null, open: false };

  // Show welcome message
  _ewAppendMsg('bot', welcomeMsg);
}

/* ── Chat helpers (global) ── */
function ewToggleChat() {
  const panel = document.getElementById('ew-chat-panel');
  const icon  = document.getElementById('ew-chat-icon');
  if (!panel) return;
  window._ewChat.open = !window._ewChat.open;
  panel.classList.toggle('open', window._ewChat.open);
  icon.textContent = window._ewChat.open ? 'close' : 'chat';
  if (window._ewChat.open) {
    setTimeout(() => document.getElementById('ew-chat-input')?.focus(), 300);
  }
}

function _ewAppendMsg(role, text) {
  const msgs = document.getElementById('ew-chat-msgs');
  if (!msgs) return;
  const div = document.createElement('div');
  div.className = `ew-msg ${role}`;
  div.textContent = text;
  msgs.appendChild(div);
  msgs.scrollTop = msgs.scrollHeight;
  return div;
}

function _ewShowTyping() {
  const msgs = document.getElementById('ew-chat-msgs');
  if (!msgs) return null;
  const el = document.createElement('div');
  el.className = 'ew-typing';
  el.innerHTML = '<span></span><span></span><span></span>';
  msgs.appendChild(el);
  msgs.scrollTop = msgs.scrollHeight;
  return el;
}

async function ewSendChat() {
  const input  = document.getElementById('ew-chat-input');
  const sendBtn = document.getElementById('ew-chat-send');
  const text   = input?.value?.trim();
  if (!text || !window._ewChat) return;

  input.value = '';
  sendBtn.disabled = true;
  _ewAppendMsg('user', text);

  const typing = _ewShowTyping();

  try {
    const res = await fetch(`${window._ewChat.endpoint}/api/chat`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        slug:       window._ewChat.slug,
        message:    text,
        session_id: window._ewChat.sessionId
      })
    });
    const data = await res.json();
    window._ewChat.sessionId = data.session_id;
    typing?.remove();
    _ewAppendMsg('bot', data.reply || 'Sorry, I had trouble responding. Please call us directly!');
  } catch (_) {
    typing?.remove();
    const phone = SITE?.business?.phone || 'us';
    _ewAppendMsg('bot', `Sorry, I'm having a connection issue. Please call ${phone} directly!`);
  }

  sendBtn.disabled = false;
  input?.focus();
}
