<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <!-- Logo/Brand -->
        <a class="navbar-brand fw-bold" href="<?php echo isset($basePath) ? $basePath : ''; ?>index.php">
            <i class="bi bi-rocket-takeoff me-2"></i>
            ATIVUS
        </a>
        
        <!-- Mobile toggle button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Navigation items -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>" 
                       href="<?php echo isset($basePath) ? $basePath : ''; ?>index.php">
                        <i class="bi bi-house-door me-1"></i>
                        Dashboard
                    </a>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-credit-card me-1"></i>
                        Assinaturas
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="<?php echo isset($basePath) ? $basePath : ''; ?>modules/assinaturas/">
                                <i class="bi bi-calendar-check me-2"></i>
                                Assinaturas
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?php echo isset($basePath) ? $basePath : ''; ?>modules/pagamentos/">
                                <i class="bi bi-cash-coin me-2"></i>
                                Pagamentos
                            </a>
                        </li>
                    </ul>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-laptop me-1"></i>
                        Equipamentos
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="<?php echo isset($basePath) ? $basePath : ''; ?>modules/equipamentos/">
                                <i class="bi bi-pc-display me-2"></i>
                                Equipamentos
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?php echo isset($basePath) ? $basePath : ''; ?>modules/manutencoes/">
                                <i class="bi bi-tools me-2"></i>
                                Manutenções
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?php echo isset($basePath) ? $basePath : ''; ?>modules/fornecedores/">
                                <i class="bi bi-truck me-2"></i>
                                Fornecedores
                            </a>
                        </li>
                    </ul>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo isset($basePath) ? $basePath : ''; ?>modules/relatorios/">
                        <i class="bi bi-graph-up me-1"></i>
                        Relatórios
                    </a>
                </li>
            </ul>
            
            <!-- Right side items -->
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-gear me-1"></i>
                        Sistema
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="<?php echo isset($basePath) ? $basePath : ''; ?>modules/configuracoes/">
                                <i class="bi bi-sliders me-2"></i>
                                Configurações
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?php echo isset($basePath) ? $basePath : ''; ?>modules/backup/">
                                <i class="bi bi-cloud-download me-2"></i>
                                Backup
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?php echo isset($basePath) ? $basePath : ''; ?>modules/help/">
                                <i class="bi bi-question-circle me-2"></i>
                                Ajuda
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="<?php echo isset($basePath) ? $basePath : ''; ?>setup.php">
                                <i class="bi bi-database-gear me-2"></i>
                                Configurar BD
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-muted" href="#">
                                <i class="bi bi-info-circle me-2"></i>
                                Versão 2.0
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

