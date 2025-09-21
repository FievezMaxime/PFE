<?php
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

// Traitement du formulaire d'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $server_id = intval($_POST['server_id']);
    $game_key = trim($_POST['game_key']);

    // Validation des entrées utilisateur
    if (empty($username) || empty($email) || empty($password) || empty($game_key)) {
        $error = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Adresse e-mail invalide.";
    } else {
        // Vérifier si l'utilisateur ou l'email existe déjà
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username OR email = :email");
        $stmt->execute([':username' => $username, ':email' => $email]);
        
        if ($stmt->fetchColumn() > 0) {
            $error = "Nom d'utilisateur ou e-mail déjà utilisé.";
        } else {
            // Hachage du mot de passe
            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            // Insérer les données dans la base de données
            $stmt = $pdo->prepare(
                "INSERT INTO users (username, email, password_hash, server_id, game_key) VALUES (:username, :email, :password_hash, :server_id, :game_key)"
            );

            $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password_hash' => $password_hash,
                ':server_id' => $server_id,
                ':game_key' => $game_key
            ]);

            $success = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Inscription - StarFarer Inc.</title>
        <link rel="stylesheet" href="css/styles.css">
    </head>
    
    <body>
        <!-- En-tête -->
        <header class="navbar">
            <div class="logo">StarFarer Inc.</div>
            <ul class="navbar-links">
                <li><a href="index.php" class="btn-pw">Accueil</a></li>
                <li><a href="signup.php" class="btn-pw">Inscription</a></li>
                <li><a href="login.php" class="btn-pw">Connexion</a></li>
                <li><a href="store.php" class="btn-pw">Magasin</a></li>
            </ul>
        </header>

        <main>
            <div class="container-signup">
                <h1>Inscription</h1>

                <?php if (!empty($error)): ?>
                    <div class="error"><?= htmlspecialchars($error) ?></div>
                <?php elseif (!empty($success)): ?>
                    <div class="success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="username">Nom d'utilisateur</label>
                        <input type="text" id="username" name="username" required>
                    </div>

                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" required><br/>
                    	<label for="confirm_password">Confirmez le mot de passe :</label>
                    	<input type="password" name="confirm_password" required>
                    </div>

                    <div class="form-group">
                        <label for="server_id">Serveur</label>
                        <select id="server_id" name="server_id" required>
                            <option value="1">Galactic Alpha</option>
                            <option value="2">Stellar Beta</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="game_key">Clé de jeu</label>
                        <input type="text" id="game_key" name="game_key" required>
                    </div>

                    <button type="submit" class="btn-pw">S'inscrire</button>
                </form>
            </div>
        </main>

        <!-- Pied de page -->
        <footer class="footer">
            <p>© 2025 StarFarer Inc. Tous droits réservés.</p>
            <p>Contact : <a href="mailto:support@starfarer.com">support@starfarer.com</a></p>
        </footer>
    </body>
</html>