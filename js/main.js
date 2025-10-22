document.addEventListener('DOMContentLoaded', () => {
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');

    addToCartButtons.forEach(button => {
        button.addEventListener('click', (event) => {
            const produtoID = event.target.dataset.id;
            
            const formData = new FormData();
            formData.append('action', 'add');
            formData.append('produtoID', produtoID);

            fetch('/backend/carrinho_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Atualiza o contador do carrinho no cabeçalho
                    document.getElementById('cart-count').innerText = data.totalItens;
                    alert('Produto adicionado ao carrinho!');
                }
            })
            .catch(error => console.error('Erro:', error));
        });
    });
});
