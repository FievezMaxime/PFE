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

//Récupération des vaisseaux encore en stock depuis la base de données
$queryShips = "SELECT * FROM ships WHERE Promotions > 0";
$stmtShips = $pdo->prepare($queryShips);
$stmtShips->execute();
$ships = $stmtShips->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Accueil - StarFarer Inc.</title>
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

        <!-- Contenu principal -->
        <main>
            <section class="hero">
                <h1>Bienvenue dans l'univers de StarFarer Inc.</h1>
                <p>Achetez des vaisseaux spatiaux en toute simplicité et continuez à explorer les galaxies sans contrainte.</p>
            </section>

            <section class="promo-section">
                <h2>Vaisseaux en promotion</h2>
                <div class="container">
                    <div class="card-container">
                        <?php if(count($ships) > 0): ?>
                            <?php foreach ($ships as $vaisseau): ?>
                                <div class="card">
                                    <a href="store-preview.php?id=<?php echo htmlspecialchars($vaisseau['ship_id']) ?>">
                                        <?php if(!empty($vaisseau['image'])): ?>
                                            <img src="<?php echo htmlspecialchars($vaisseau['image']); ?>" alt="<?php echo htmlspecialchars($vaisseau['nom']); ?>" class="card-image">
                                        <?php else: ?>
                                            <!-- Image de remplacement si aucune image n'est définie -->
                                            <img src="default-ship.png" alt="Image par défaut">
                                        <?php endif; ?>
                                    </a>
                                    <h3><?php echo htmlspecialchars($vaisseau['name']); ?></h3>
                                    <p><?php echo htmlspecialchars($vaisseau['description']); ?></p>
                                    <p>Prix : <?php echo htmlspecialchars($vaisseau['price']); ?>€</p>
                                    <p>Stock : <?php echo htmlspecialchars($vaisseau['stock']); ?></p>
                                    <?php if ($isLoggedIn): ?>
                                        <!-- Si connecté, redirige vers purchase.php en précisant le type et l'id -->
                                        <a href="purchase.php?item_type=vaisseau&id=<?php echo htmlspecialchars($vaisseau['ship_id']); ?>" class="btn">Acheter</a>
                                    <?php else: ?>
                                        <!-- Sinon, l'utilisateur est redirigé vers la page de connexion -->
                                        <a href="login.php" class="btn">Acheter</a>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>Aucune promotion disponible actuellement.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </main>

        <!-- Pied de page -->
        <footer class="footer">
            <p>© 2025 StarFarer Inc. Tous droits réservés.</p>
            <p>Contact : <a href="mailto:support@starfarer.com">support@starfarer.com</a></p>
        </footer>
    </body>
</html>