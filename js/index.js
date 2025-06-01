// index.js

document.addEventListener('DOMContentLoaded', function() {
    const leadForm = document.getElementById('leadForm');
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const phoneInput = document.getElementById('phone');

    // Máscara para o campo de telefone
    phoneInput.addEventListener('input', function (e) {
        let value = e.target.value.replace(/\D/g, ''); // Remove tudo que não é dígito
        let formattedValue = '';

        if (value.length > 0) {
            formattedValue = '(' + value.substring(0, 2);
        }
        if (value.length >= 3) {
            formattedValue += ') ' + value.substring(2, 7);
        }
        if (value.length >= 8) {
            formattedValue += '-' + value.substring(7, 11);
        }
        e.target.value = formattedValue;
    });

    // Função para validar o formulário no lado do cliente (JavaScript)
    function validateForm() {
        const name = nameInput.value.trim();
        if (name.split(' ').length < 2) {
            alert('Por favor, digite seu nome completo (nome e sobrenome).');
            return false;
        }

        const email = emailInput.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert('Por favor, digite um email válido.');
            return false;
        }

        const phone = phoneInput.value.replace(/\D/g, '');
        if (phone.length < 10 || phone.length > 11) {
            alert('Por favor, digite um número de telefone válido com 10 ou 11 dígitos (incluindo DDD).');
            return false;
        }

        const objetivo = document.querySelector('input[name="objetivo"]:checked');
        if (!objetivo) {
            alert('Por favor, selecione seu principal objetivo.');
            return false;
        }

        return true;
    }

    // Event listener para o envio do formulário
    leadForm.addEventListener('submit', async function(event) {
        event.preventDefault();

        if (!validateForm()) {
            return;
        }

        const formData = new FormData(leadForm);

        try {
            const response = await fetch('../php/processa_lead.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                // Redireciona para a página de obrigado
                window.location.href = 'obrigado.html';
            } else {
                alert('Erro: ' + result.message); // Ainda exibe erros que o PHP possa retornar
            }
        } catch (error) {
            console.error('Erro ao enviar formulário:', error);
            alert('Ocorreu um erro ao enviar o formulário. Tente novamente.');
        }
    });
});
