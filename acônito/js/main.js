function toggleSenha(id, btn) {
  const campo = document.getElementById(id);
  if (!campo) return;
  const isPwd = campo.type === 'password';
  campo.type = isPwd ? 'text' : 'password';
  if (btn) {
    btn.innerHTML = isPwd ? '<span class="material-symbols-outlined">visibility_off</span>' : '<span class="material-symbols-outlined">visibility</span>';
  }
}

function confirmDelete() {
  return confirm('Confirma exclusão desta bebida? Esta ação não pode ser desfeita.');
}

/* chamado nos ícones para evitar que o clique "vaze" para o link do card */
function stopPropagation(e) {
  e = e || window.event;
  if (e.stopPropagation) e.stopPropagation();
  if (e.cancelBubble !== undefined) e.cancelBubble = true;
}

/* auxilia para tornar o card clicável usando teclado (acesse com Enter) */
document.addEventListener('keydown', function (ev) {
  const focused = document.activeElement;
  if (ev.key === 'Enter' && focused && focused.classList.contains('card-link')) {
    focused.click();
  }
});
