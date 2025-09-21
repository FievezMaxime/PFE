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

// Page Compte (account.php)
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Récupérer les informations utilisateur et les achats
$stmt = $pdo->prepare(
    "SELECT u.username, u.email, u.credits, p.purchase_date, s.name AS ship_name, p.price, s.image AS ship_image
     FROM users u
     LEFT JOIN purchases p ON u.user_id = p.user_id
     LEFT JOIN ships s ON p.ship_id = s.ship_id
     WHERE u.user_id = :user_id"
);
$stmt->execute([':user_id' => $user_id]);
$user_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Mon Compte - StarFarer Inc.</title>
        <link rel="stylesheet" href="css/styles.css">
    </head>
    
    <body>
        <!-- En-tête -->
        <header class="navbar">
            <div class="logo">StarFarer Inc.</div>
            <ul class="navbar-links">
                <li><a href="index.php" class="btn-pw">Accueil</a></li>
                <li><a href="account.php" class="btn-pw">Compte</a></li>
                <li><a href="store.php" class="btn-pw">Magasin</a></li>
            </ul>
        </header>
        <main>
            <div class="container">
                <h1>Mon Compte</h1>

                <?php if (isset($_GET['purchase_success'])): ?>
                    <p style="color: green;">Achat réussi ! Votre vaisseau a été ajouté à votre compte.</p>
                <?php endif; ?>

                <h2>Informations personnelles</h2>
                <p><strong>Nom d'utilisateur :</strong> <?= htmlspecialchars($user_data[0]['username']) ?></p>
                <p><strong>Email :</strong> <?= htmlspecialchars($user_data[0]['email']) ?></p>
                <p><strong>Crédits :</strong> <?= htmlspecialchars($user_data[0]['credits']) ?></p>

                <h2>Historique des achats</h2>
                <?php if (count($user_data) > 0 && $user_data[0]['purchase_date']): ?>
                    <div class="table-container">
                        <table class="purchase-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Vaisseau</th>
                                    <th></th>
                                    <th>Prix</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($user_data as $purchase): ?>
                                    <?php if ($purchase['purchase_date']): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($purchase['purchase_date']) ?></td>
                                            <td>
                                                <?php if (!empty($purchase['ship_image'])): ?>
                                                    <img src="<?= htmlspecialchars($purchase['ship_image']) ?>" alt="<?= htmlspecialchars($purchase['ship_name']) ?>" style="max-width: 100px;">
                                                <?php else: ?>
                                                    <img src="default-ship.png" alt="Image par défaut" style="max-width: 100px;">
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($purchase['ship_name']) ?></td>
                                            <td><?= htmlspecialchars($purchase['price']) ?> €</td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>Aucun achat effectué.</p>
                <?php endif; ?>

                <a href="logout.php" class="btn-pw">Déconnexion</a>
            </div>
        </main>

        <!-- Pied de page -->
        <footer class="footer">
            <p>© 2025 StarFarer Inc. Tous droits réservés.</p>
            <p>Contact : <a href="mailto:support@starfarer.com">support@starfarer.com</a></p>
        </footer>
    </body>
</html>