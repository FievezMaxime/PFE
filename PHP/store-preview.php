<?php
session_start(); // Démarrer la session

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

$id = isset($_GET['id']) ? $_GET['id'] : null;

// Récupérer les détails de l'objet depuis la base de données
$stmt = $pdo->prepare("SELECT * FROM ships WHERE ship_id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    echo "Objet non trouvé.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Fiche de l'objet - <?= htmlspecialchars($item['name']) ?></title>
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
            <div class="container">
                <div class="cards-container">
                    <div class="top-row">
                        <div class="card">
                            <!-- Affichage de la photo de l'objet -->
                            <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="card-image">
                            <div class="card-content">
                                <h3><?= htmlspecialchars($item['name']) ?></h3>
                                <p><?= htmlspecialchars($item['description']) ?></p>
                                <p><strong>Prix :</strong> <?= htmlspecialchars($item['price']) ?> €</p>
                                <!-- Bouton vers purchase.php en passant l'id de l'objet -->
                                    <a href="purchase.php?item_type=vaisseau&id=<?php echo htmlspecialchars($item['ship_id']); ?>" class="btn">Acheter</a>
                            </div>
                        </div>
                        <div class="card">
                            <?php echo $item['desc_one'] ?>
                        </div>
                    </div>
                    <div class="bottom-row">
                        <div class="card card-fullwidth">
                        <?php echo $item['desc_two'] ?>
                        </div>
                        <div class="card card-fullwidth">
                        <?php echo $item['desc_three'] ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <footer class="footer">
            <p>© 2025 StarFarer Inc. Tous droits réservés.</p>
            <p>Contact : <a href="mailto:support@starfarer.com">support@starfarer.com</a></p>
        </footer>
    </body>
</html>