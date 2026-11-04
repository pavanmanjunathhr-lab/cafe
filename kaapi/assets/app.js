// Menu tabs: show one category at a time
(function () {
  var tabs = document.querySelectorAll('#tabs button');
  var panels = document.querySelectorAll('.items[data-panel]');
  if (!tabs.length) return;
  function show(k) {
    tabs.forEach(function (b) { b.setAttribute('aria-pressed', String(b.dataset.k === k)); });
    panels.forEach(function (p) { p.hidden = p.dataset.panel !== k; });
  }
  tabs.forEach(function (b) { b.addEventListener('click', function () { show(b.dataset.k); }); });
  show(tabs[0].dataset.k);
})();
