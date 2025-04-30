<div class="position-sticky pt-3">
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link text-white <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                <i class="fas fa-tachometer-alt me-2"></i>
                Tableau de Bord
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?php echo (basename($_SERVER['PHP_SELF']) == 'etudiants.php') ? 'active' : ''; ?>" href="etudiants.php">
                <i class="fas fa-user-graduate me-2"></i>
                Étudiants
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?php echo (basename($_SERVER['PHP_SELF']) == 'cours.php') ? 'active' : ''; ?>" href="cours.php">
                <i class="fas fa-book me-2"></i>
                Cours
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?php echo (basename($_SERVER['PHP_SELF']) == 'presence.php') ? 'active' : ''; ?>" href="presence.php">
                <i class="fas fa-clipboard-check me-2"></i>
                Marquer Présence
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?php echo (basename($_SERVER['PHP_SELF']) == 'rapports.php') ? 'active' : ''; ?>" href="rapports.php">
                <i class="fas fa-chart-bar me-2"></i>
                Rapports
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?php echo (basename($_SERVER['PHP_SELF']) == 'profil.php') ? 'active' : ''; ?>" href="profil.php">
                <i class="fas fa-user-circle me-2"></i>
                Profil
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white" href="deconnexion.php">
                <i class="fas fa-sign-out-alt me-2"></i>
                Déconnexion
            </a>
        </li>
    </ul>
    
    <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
        <span>Administration</span>
    </h6>
    <ul class="nav flex-column mb-2">
        <li class="nav-item">
            <a class="nav-link text-white" href="gestion_utilisateurs.php">
                <i class="fas fa-users-cog me-2"></i>
                Gestion Utilisateurs
            </a>
        </li>
    </ul>
    <?php endif; ?>
</div>
