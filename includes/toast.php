<?php
// includes/toast.php
// Include este arquivo logo após o include do header (ou em qualquer lugar do body).
if (session_status() === PHP_SESSION_NONE) session_start();

// Pega mensagens de sessão (se existirem) e apaga-as para não reaparecerem.
$toast_success = $_SESSION['toast_success'] ?? null;
$toast_error = $_SESSION['toast_error'] ?? null;
unset($_SESSION['toast_success'], $_SESSION['toast_error']);
?>
<!-- Toast container -->
<div id="toast-container" aria-live="polite" aria-atomic="true" style="position: fixed; right: 18px; top: 18px; z-index: 99999;"></div>

<style>
/* Estilo do toast (combina com seu tema) */
.toast {
    background: linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.02));
    border: 1px solid rgba(184,134,11,0.14);
    color: #fff;
    padding: 12px 14px;
    margin-bottom: 10px;
    border-radius: 10px;
    min-width: 240px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.45);
    display: flex;
    gap: 10px;
    align-items: center;
    font-family: inherit;
    opacity: 0;
    transform: translateY(-10px);
    transition: opacity .28s ease, transform .28s ease;
}
.toast.show { opacity: 1; transform: translateY(0); }
.toast .toast-icon { font-size: 20px; color: #ffd700; margin-right: 6px; }
.toast.success { border-left: 4px solid #28a745; }
.toast.error   { border-left: 4px solid #dc3545; }
.toast .close-btn {
    margin-left: auto;
    background: transparent;
    border: none;
    color: #f5dfc9;
    cursor: pointer;
    font-size: 16px;
}
</style>

<script>
// Funções de toast
(function(){
    function createToast(message, type = 'success', timeout = 4500) {
        const container = document.getElementById('toast-container');
        if (!container) return;
        const t = document.createElement('div');
        t.className = 'toast ' + type;
        t.innerHTML = '<div class="toast-icon">' + (type === 'success' ? '✔' : '⚠') + '</div>'
                    + '<div class="toast-body" style="flex:1">' + message + '</div>'
                    + '<button class="close-btn" aria-label="Fechar">&times;</button>';
        container.appendChild(t);
        // show
        requestAnimationFrame(()=> t.classList.add('show'));
        // close button
        t.querySelector('.close-btn').addEventListener('click', ()=> hideToast(t));
        // auto hide
        if (timeout > 0) setTimeout(()=> hideToast(t), timeout);
    }
    function hideToast(el) {
        if (!el) return;
        el.classList.remove('show');
        setTimeout(()=> { if (el.parentNode) el.parentNode.removeChild(el); }, 320);
    }
    // expõe globalmente
    window.AcToasts = { create: createToast, hide: hideToast };
})();
</script>

<?php if ($toast_success): ?>
<script>
document.addEventListener('DOMContentLoaded', function(){
    if (window.AcToasts) window.AcToasts.create(<?= json_encode($toast_success) ?>, 'success', 5000);
});
</script>
<?php endif; ?>

<?php if ($toast_error): ?>
<script>
document.addEventListener('DOMContentLoaded', function(){
    if (window.AcToasts) window.AcToasts.create(<?= json_encode($toast_error) ?>, 'error', 7000);
});
</script>
<?php endif; ?>
