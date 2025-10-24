function toggleSenha(id, btn) {
  const campo = document.getElementById(id);
  const isVisible = campo.type === 'text';
  campo.type = isVisible ? 'password' : 'text';
  btn.innerHTML = isVisible ? '<span class="material-symbols-outlined">visibility</span>' 
                            : '<span class="material-symbols-outlined">visibility_off</span>';
}
