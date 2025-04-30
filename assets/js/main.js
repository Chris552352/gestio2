/**
 * Script principal pour le système de gestion de présence
 */

// Attendre que le document soit chargé
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des tooltips Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Gestion des alertes autodismiss après 5 secondes
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
    
    // Confirmation avant suppression
    var deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cet élément ?')) {
                e.preventDefault();
            }
        });
    });
    
    // Gestion des formulaires de filtrage
    var filterForm = document.getElementById('filter-form');
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            // Supprimer les champs vides pour éviter des paramètres d'URL inutiles
            var inputs = filterForm.querySelectorAll('input, select');
            inputs.forEach(function(input) {
                if (input.value === '') {
                    input.disabled = true;
                }
            });
        });
    }
    
    // Fonction pour sélectionner/désélectionner tous les étudiants (page de présence)
    var selectAllPresent = document.getElementById('select-all-present');
    if (selectAllPresent) {
        selectAllPresent.addEventListener('click', function(e) {
            e.preventDefault();
            var radioButtons = document.querySelectorAll('input[value="present"]');
            radioButtons.forEach(function(radio) {
                radio.checked = true;
            });
        });
    }
    
    var selectAllAbsent = document.getElementById('select-all-absent');
    if (selectAllAbsent) {
        selectAllAbsent.addEventListener('click', function(e) {
            e.preventDefault();
            var radioButtons = document.querySelectorAll('input[value="absent"]');
            radioButtons.forEach(function(radio) {
                radio.checked = true;
            });
        });
    }
    
    // Si on change de cours dans le formulaire de présence, actualiser la page
    var coursSelect = document.getElementById('cours-select');
    var dateInput = document.getElementById('date-presence');
    
    if (coursSelect && dateInput) {
        coursSelect.addEventListener('change', function() {
            if (dateInput.value) {
                document.getElementById('filter-presence-form').submit();
            }
        });
        
        dateInput.addEventListener('change', function() {
            if (coursSelect.value) {
                document.getElementById('filter-presence-form').submit();
            }
        });
    }
    
    // Validation des formulaires côté client
    var forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
});

/**
 * Fonction pour exporter un tableau en PDF
 * @param {string} tableId - ID du tableau à exporter
 * @param {string} filename - Nom du fichier PDF
 */
function exportTableToPDF(tableId, filename) {
    // Cette fonction serait normalement implémentée avec jsPDF ou une bibliothèque similaire
    // Comme nous n'utilisons pas de bibliothèques externes, nous redirigeons vers une page PHP qui génère le PDF côté serveur
    var url = 'export_pdf.php?table=' + tableId + '&filename=' + filename;
    window.open(url, '_blank');
}
