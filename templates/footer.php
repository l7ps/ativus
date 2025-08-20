            </div>
        </main>
    </div>
    
    <!-- Footer -->
    <footer class="footer bg-light border-top mt-auto py-3">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <span class="text-muted">© <?php echo date('Y'); ?> Sistema Ativus - Gestão Empresarial</span>
                </div>
                <div class="col-md-6 text-end">
                    <span class="text-muted">Versão 1.0</span>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?php echo isset($basePath) ? $basePath : ''; ?>assets/js/script.js"></script>
    
    <!-- Page specific scripts -->
    <?php if (isset($pageScripts)): ?>
        <?php echo $pageScripts; ?>
    <?php endif; ?>
</body>
</html>

