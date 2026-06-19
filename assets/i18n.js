/**
 * EWNexus JSON 多語言引擎 — EN/ES/DE/FR/JA/AR
 */
(function () {
  var LANGS = [
    { code: "en", label: "EN" },
    { code: "es", label: "ES" },
    { code: "de", label: "DE" },
    { code: "fr", label: "FR" },
    { code: "ja", label: "JP" },
    { code: "ar", label: "AR", rtl: true },
  ];
  var DEFAULT = "en";
  var STORAGE_KEY = "ewnexus_lang";

  function currentLang() {
    var q = new URLSearchParams(location.search).get("lang");
    if (q && LANGS.some(function (l) { return l.code === q; })) {
      localStorage.setItem(STORAGE_KEY, q);
      return q;
    }
    return localStorage.getItem(STORAGE_KEY) || DEFAULT;
  }

  function apply(lang) {
    document.documentElement.lang = lang;
    var isRtl = LANGS.some(function (l) { return l.code === lang && l.rtl; });
    document.documentElement.dir = isRtl ? "rtl" : "ltr";
    if (lang === DEFAULT) return;
    fetch("/lang/" + lang + ".json")
      .then(function (r) { return r.json(); })
      .then(function (dict) {
        document.querySelectorAll("[data-i18n]").forEach(function (el) {
          var key = el.getAttribute("data-i18n");
          if (dict[key] !== undefined) el.innerHTML = dict[key];
        });
      })
      .catch(function () {});
  }

  function buildSwitcher(lang) {
    var style = document.createElement("style");
    style.textContent =
      "#ew-lang{position:fixed;bottom:24px;left:24px;z-index:9999;display:flex;gap:0;" +
      "border:1px solid rgba(90,240,179,.25);background:#060f0a;overflow:hidden}" +
      "#ew-lang button{background:none;border:none;color:rgba(90,240,179,.5);font:700 11px/1 'Space Grotesk',sans-serif;" +
      "letter-spacing:.1em;padding:9px 13px;cursor:pointer;transition:background .2s,color .2s}" +
      "#ew-lang button.on{background:#5af0b3;color:#003825}";
    document.head.appendChild(style);

    var bar = document.createElement("div");
    bar.id = "ew-lang";
    LANGS.forEach(function (l) {
      var b = document.createElement("button");
      b.textContent = l.label;
      if (l.code === lang) b.className = "on";
      b.addEventListener("click", function () {
        localStorage.setItem(STORAGE_KEY, l.code);
        location.search = "?lang=" + l.code;
      });
      bar.appendChild(b);
    });
    document.body.appendChild(bar);
  }

  var lang = currentLang();
  apply(lang);
  document.addEventListener("DOMContentLoaded", function () {
    buildSwitcher(lang);
  });
})();
