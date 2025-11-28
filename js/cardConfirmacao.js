function mostrarCardConfirmacao() {
    const fundo = document.createElement("div");
    fundo.className = "card-fundo";

    const card = document.createElement("div");
    card.className = "card-confirmacao";
    card.innerText = "Compra confirmada com sucesso!";

    fundo.appendChild(card);
    document.body.appendChild(fundo);

    setTimeout(() => {
        fundo.classList.add("fadeout");
        setTimeout(() => fundo.remove(), 400);
    }, 2200);
}
