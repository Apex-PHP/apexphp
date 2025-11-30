// Framework JavaScript
console.log('ApexPHP Framework carregado!');

// Adicionar confirmação em formulários de delete
document.addEventListener('DOMContentLoaded', function() {
    const deleteForms = document.querySelectorAll('form[action*="/delete/"]');
    
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Tem certeza que deseja deletar?')) {
                e.preventDefault();
            }
        });
    });
});
