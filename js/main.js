// js/main.js
function toggleSenha(id, btn) {
  const campo = document.getElementById(id);
  if (!campo) return;
  campo.type = campo.type === 'password' ? 'text' : 'password';
  if (btn) btn.innerHTML = campo.type === 'password' ? 'Mostrar' : 'Ocultar';
}

function confirmDelete(msg) {
  return confirm(msg || 'Confirma?');
}

// Small function to auto-hide toasts if using dynamic toasts
document.addEventListener('DOMContentLoaded', () => {
  const t = document.querySelector('.toast');
  if (t) setTimeout(() => t.style.display = 'none', 4000);
});
