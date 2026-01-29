window.exportModule = function (format) {
  const params = new URLSearchParams(window.location.search);
  const modulo = params.get("modulo");
  const id = params.get("id");

  if (!modulo) return;

  const form = document.createElement("form");
  form.method = "POST";
  form.action =
    "index.php?modulo=" + modulo + "&action=Export&_response=download";

  form.innerHTML = `
  <input type="hidden" name="format" value="${format}">
  <input type="hidden" name="context" value="module">
  `;
  
  if (id) {
    form.innerHTML += `<input type="hidden" name="id" value="${id}">`;
  }

  document.body.appendChild(form);
  form.submit();
  form.remove();
};
