<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Sistema Ativus</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link href="<?php echo isset($basePath) ? $basePath : ''; ?>assets/css/style.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo isset($basePath) ? $basePath : ''; ?>assets/img/favicon.ico">
</head>
<body>
    <div class="wrapper">
        <!-- Navbar -->
        <?php include __DIR__ . '/navbar.php'; ?>
        
        <!-- Main Content -->
        <main class="main-content">
            <div class="container-fluid py-4">

