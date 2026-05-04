</div><!-- #layout -->

<script>
(function () {
  var sidebar  = document.getElementById('sidebar');
  var overlay  = document.getElementById('sidebar-overlay');
  var toggle   = document.getElementById('menu-toggle');
  var closeBtn = document.getElementById('sidebar-close');
  function open()  { sidebar.classList.add('open');  overlay.classList.add('active'); }
  function close() { sidebar.classList.remove('open'); overlay.classList.remove('active'); }
  if (toggle)   toggle.addEventListener('click', open);
  if (closeBtn) closeBtn.addEventListener('click', close);
  if (overlay)  overlay.addEventListener('click', close);
}());

// Chart.js global defaults
if (typeof Chart !== 'undefined') {
  Chart.defaults.font.family = "'Manrope', system-ui, sans-serif";
  Chart.defaults.font.size   = 12;
  Chart.defaults.color       = '#42566f';
  Chart.defaults.borderColor = '#dbe5f2';
  Chart.defaults.plugins.legend.labels.boxWidth  = 10;
  Chart.defaults.plugins.legend.labels.padding   = 14;
  Chart.defaults.plugins.tooltip.backgroundColor = '#111827';
  Chart.defaults.plugins.tooltip.titleColor      = '#f8fafc';
  Chart.defaults.plugins.tooltip.bodyColor       = '#dbe7f5';
  Chart.defaults.plugins.tooltip.cornerRadius    = 8;
  Chart.defaults.plugins.tooltip.padding         = 10;
  Chart.defaults.plugins.tooltip.callbacks = Chart.defaults.plugins.tooltip.callbacks || {};
}

const CHART_COLORS = ['#2563eb','#0ea5e9','#10b981','#f59e0b','#ef4444','#e11d48','#14b8a6','#84cc16'];
</script>
</body>
</html>
