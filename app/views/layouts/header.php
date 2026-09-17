<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'FixMyDevice - Hardware Service Ticketing') ?></title>
    <!-- Dark/Light Theme Immediate Initializer -->
    <script>
        (function() {
            try {
                const savedTheme = localStorage.getItem('fmd_theme') || 'light';
                document.documentElement.setAttribute('data-theme', savedTheme);
            } catch (e) {}
        })();
    </script>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= (!empty($baseUrl) && $baseUrl !== '/') ? rtrim($baseUrl, '/') : '' ?>/favicon.svg">
    <link rel="alternate icon" type="image/x-icon" href="<?= (!empty($baseUrl) && $baseUrl !== '/') ? rtrim($baseUrl, '/') : '' ?>/favicon.ico">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= (!empty($baseUrl) && $baseUrl !== '/') ? rtrim($baseUrl, '/') : '' ?>/assets/css/style.css">
</head>
<body>
