<?php
    session_start();
    //apagar a sessao usuario
    unset($_SESSION["usuario"]);
    //redirecionar para o login
    header("Location: index.php");