document.addEventListener('DOMContentLoaded', () => {
  const header = document.querySelector('header');
  const toastContainer = document.getElementById('toast-container');
  const confirmModal = document.getElementById('confirm-modal');
  const btnFinalizar = document.getElementById('btn-finalizar');
  const confirmYes = document.getElementById('confirm-yes');
  const confirmNo = document.getElementById('confirm-no');
  const centerCard = document.getElementById('center-card');

  function updateHeaderHeight() {
    const h = header ? header.offsetHeight : 64;
    document.documentElement.style.setProperty('--header-height', h + 'px');
  }
  updateHeaderHeight();
  window.addEventListener('resize', updateHeaderHeight);

  // Remover item via botão "Remover"
  document.querySelectorAll('.remove-small').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const id = btn.dataset.cartId;
      if (!confirm('Remover este item do carrinho?')) return;
      const form = document.createElement('form');
      form.method = 'POST';
      form.style.display = 'none';
      const acao = document.createElement('input');
      acao.name = 'acao';
      acao.value = 'atualizar';
      form.appendChild(acao);
      const inputQtd = document.createElement('input');
      inputQtd.type = 'hidden';
      inputQtd.name = `quantidade[${id}]`;
      inputQtd.value = '0';
      form.appendChild(inputQtd);
      document.body.appendChild(form);
      form.submit();
    });
  });

  function showToast(message, type = 'info', duration = 3500) {
    if (!toastContainer) return null;
    const t = document.createElement('div');
    t.className = 'toast ' + (type || 'info');
    t.textContent = message;
    toastContainer.appendChild(t);
    requestAnimationFrame(() => t.classList.add('show'));
    setTimeout(() => {
      t.classList.remove('show');
      setTimeout(() => t.remove(), 420);
    }, duration);
    return t;
  }

  // showCenterCard fallback: if cardConfirmacao.js defines mostrarCardConfirmacao(), call it.
  function showCenterCardFallback(message, ms = 1600) {
    if (typeof window.mostrarCardConfirmacao === 'function') {
      window.mostrarCardConfirmacao();
      return;
    }
    // fallback internal
    if (!centerCard) return;
    centerCard.textContent = message;
    centerCard.classList.remove('hidden');
    centerCard.classList.add('show');
    setTimeout(() => {
      centerCard.classList.remove('show');
      centerCard.classList.add('hidden');
    }, ms);
  }

  btnFinalizar?.addEventListener('click', () => {
    if (!confirmModal) return;
    confirmModal.classList.remove('hidden');
    confirmModal.setAttribute('aria-hidden', 'false');
  });

  confirmNo?.addEventListener('click', () => {
    if (!confirmModal) return;
    confirmModal.classList.add('hidden');
    confirmModal.setAttribute('aria-hidden', 'true');
  });

  confirmYes?.addEventListener('click', async () => {
    if (!confirmModal) return;
    confirmModal.classList.add('hidden');
    confirmModal.setAttribute('aria-hidden', 'true');

    const loadingToast = showToast('Processando sua compra...', 'info', 10000);

    try {
      const resp = await fetch('comprar.php', {
        method: 'POST',
        headers: { 'Accept': 'application/json' }
      });
      const data = await resp.json();
      if (loadingToast) { loadingToast.classList.remove('show'); setTimeout(()=> loadingToast.remove(), 300); }

      if (resp.ok && data.success) {
        // show center card (uses existing cardConfirmacao.js mostrarCardConfirmacao if present)
        showCenterCardFallback('Compra confirmada com sucesso!', 1800);
        showToast(data.message || 'Compra confirmada', 'success', 3500);
        setTimeout(() => location.reload(), 1400);
      } else {
        showToast(data.message || 'Erro ao processar compra', 'error', 6000);
      }

    } catch (err) {
      if (loadingToast) { loadingToast.classList.remove('show'); setTimeout(()=> loadingToast.remove(), 300); }
      console.error(err);
      showToast('Erro de rede ao confirmar compra', 'error', 6000);
    }
  });

});
