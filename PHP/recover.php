<?php
session_start();

// Paramètres de connexion à la base de données
$host = 'localhost';
$dbname = 'StarFarerDB';
$username = 'maxime';
$password = 'Sigma$7568';

// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Demande de réinitialisation via email
    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);
        
        // Vérifier le format de l'email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "Adresse email invalide.";
            exit;
        }
        
        // Vérifier si l'email existe dans la base
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Générer un token unique et définir une expiration (ici 1 heure)
            $token = bin2hex(random_bytes(16));
            $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));
            
            // Sauvegarder le token et la date d'expiration dans la base
            $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expiry = ? WHERE user_id = ?");
            $stmt->execute([$token, $expiry, $user['user_id']]);
            
            // Préparer le lien de réinitialisation
            $reset_link = "recover.php?token=" . $token;
            
            // Au lieu d'envoyer un email, on affiche le lien de réinitialisation directement
            echo "Lien de réinitialisation : <a href='$reset_link'>$reset_link</a>";
        } else {
            echo "Aucun utilisateur trouvé avec cette adresse email.";
        }
        
    // Réinitialisation du mot de passe avec confirmation
    } elseif (isset($_POST['new_password'], $_POST['confirm_password'], $_POST['token'])) {
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        $token = $_POST['token'];
        
        // Vérifier que les deux mots de passe correspondent
        if ($new_password !== $confirm_password) {
            echo "Les mots de passe ne correspondent pas.";
            exit;
        }
        
        // Vérifier le token dans la base de données
        $stmt = $pdo->prepare("SELECT user_id, reset_expiry FROM users WHERE reset_token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Vérifier que le token n'a pas expiré
            if (strtotime($user['reset_expiry']) < time()) {
                echo "Le lien de réinitialisation a expiré.";
                exit;
            }
            
            // Hash du nouveau mot de passe et mise à jour dans la base de données
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_expiry = NULL WHERE user_id = ?");
            $stmt->execute([$password_hash, $user['user_id']]);
            echo "Votre mot de passe a été mis à jour avec succès. Vous allez être redirigé vers la page d'accueil.";
            
            // Redirection vers index.php après 3 secondes via meta refresh
            echo '<meta http-equiv="refresh" content="3;url=index.php">';
        } else {
            echo "Lien de réinitialisation invalide.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Réinitialisation du mot de passe</title>
        <link rel="stylesheet" href="css/styles.css">
    </head>
    
    <body>
        <!-- En-tête -->
        <header class="navbar">
            <div class="logo">StarFarer Inc.</div>
            <ul class="navbar-links">
                <li><a href="index.php" class="btn-pw">Accueil</a></li>
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <li><a href="signup.php" class="btn-pw">Inscription</a></li>
                    <li><a href="login.php" class="btn-pw">Connexion</a></li>
                <?php else: ?>
                    <li><a href="account.php" class="btn-pw">Compte</a></li>
                <?php endif; ?>
                <li><a href="store.php" class="btn-pw">Magasin</a></li>
            </ul>
        </header>

        <main>
        <div class="container-signup"> 
            <?php if (!isset($_GET['token'])): ?>
                <!-- Formulaire de demande de réinitialisation -->
                <h2>Demande de réinitialisation du mot de passe</h2>
                <form method="post" action="recover.php">
                <h2><label for="email">Adresse email :</label></h2>
                    <input type="email" name="email" required>
                    <input type="submit" value="Envoyer" class="btn-pw">
                </form>
            <?php else: ?>
                <!-- Formulaire de réinitialisation du mot de passe -->
                <h2>Réinitialisez votre mot de passe</h2></br>
                <form method="post" action="recover.php">
                    <!-- Transmettre le token caché -->
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
                    <label for="new_password">Nouveau mot de passe :</label>
                    <input type="password" name="new_password" required>
                    <label for="confirm_password">Confirmez le mot de passe :</label>
                    <input type="password" name="confirm_password" required>
                    <input type="submit" value="Changer le mot de passe" class="btn-pw">
                </form>
            <?php endif; ?>
            </div>
        </main>

        <!-- Pied de page -->
        <footer class="footer">
            <p>© 2025 StarFarer Inc. Tous droits réservés.</p>
            <p>Contact : <a href="mailto:support@starfarer.com">support@starfarer.com</a></p>
        </footer>
    </body>
</html>