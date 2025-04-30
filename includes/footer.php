                </main>
            </div>
        </div>

        <!-- Scripts Bootstrap et jQuery -->
        <script src="vendor/jquery/jquery-3.6.0.min.js"></script>
        <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
        
        <!-- Chart.js pour les graphiques -->
        <script src="vendor/chartjs/chart.min.js"></script>
        
        <!-- Script personnalisé -->
        <script src="assets/js/main.js"></script>

        <?php
        // Script pour les graphiques si on est sur la page dashboard ou rapports
        $current_page = basename($_SERVER['PHP_SELF']);
        if ($current_page == 'dashboard.php' || $current_page == 'rapports.php') {
            echo '<script src="assets/js/chart-config.js"></script>';
        }
        ?>
    </body>
</html>
