<?php
session_start();
session_destroy();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Logout</title>
    <script type="text/javascript">
        // Limpa o localStorage quando o usuário desloga
        localStorage.removeItem('chatHistory');
        localStorage.removeItem('chatMinimized');
        // Redireciona para a página de login após limpar o localStorage
        window.location.href = "login.php";
    </script>
</head>
<body>
    <!-- Conteúdo opcional durante o processo de logout -->
    <p>Saindo...</p>
</body>
</html>
