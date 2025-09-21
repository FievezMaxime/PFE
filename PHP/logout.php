<?php
session_start(); // Démarrer la session si elle existe
session_unset(); // Supprime toutes les variables de session
session_destroy(); // Détruit complètement la session

// Rediriger vers la page d'accueil après la déconnexion
header("Location: index.php");
exit();
?>