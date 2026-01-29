document.addEventListener("click", function (e) {
  const btn = e.target.closest("[data-action]");
  if (!btn) return;

  const action = btn.dataset.action;
  const format = btn.dataset.format;

  if (action === "export") {
    if (window.exportReport) {
      window.exportReport(format);
    } else if (window.exportModule) {
      window.exportModule(format);
    }
  }
});
