            </div> <!-- Fin de .row -->
        </div> <!-- Fin de .card-body -->
    </div> <!-- Fin de .card -->
</main> <!-- Fin de .container -->

<!-- DataTables JS (jQuery est requis) -->
<script src="../public/js/jquery-3.6.0.min.js"></script>
<script src="../public/js/jquery.dataTables.min.js"></script>
<script src="../public/js/js/dataTables.bootstrap4.min.js"></script>

<!-- Chargement de Bootstrap JS (et Popper.js) -->
<script src="../public/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const themeSwitch = document.getElementById('themeSwitch');
    const currentTheme = localStorage.getItem('theme') || 'light';

    // Initialiser l'état du bouton
    if (currentTheme === 'dark') {
        themeSwitch.checked = true;
    }

    themeSwitch.addEventListener('change', function() {
        if (this.checked) {
            document.documentElement.setAttribute('data-bs-theme', 'dark');
            localStorage.setItem('theme', 'dark');
        } else {
            document.documentElement.setAttribute('data-bs-theme', 'light');
            localStorage.setItem('theme', 'light');
        }
    });

    // --- Script pour faire disparaître l'alerte de session ---
    const sessionAlert = document.getElementById('session-alert');
    if (sessionAlert) {
        // Attendre 5 secondes
        setTimeout(() => {
            // Utiliser l'API de Bootstrap pour fermer l'alerte avec une animation
            const alertInstance = new bootstrap.Alert(sessionAlert);
            alertInstance.close();
        }, 5000); // 5000 millisecondes = 5 secondes
    }
});
</script>
</body>
</html>