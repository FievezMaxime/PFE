<?php
session_start();

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Vérifier que les paramètres GET sont présents
if (!isset($_GET['item_type']) || !isset($_GET['id'])) {
    echo "Paramètres manquants.";
    exit;
}

$itemType = $_GET['item_type'];
$id = intval($_GET['id']);

// Vérifier que le type d'article est autorisé
$allowedTypes = ['vaisseau', 'pack'];
if (!in_array($itemType, $allowedTypes)) {
    echo "Type d'article invalide.";
    exit;
}

// Connexion à la base de données
$host = 'localhost';
$dbname = 'StarFarerDB';
$username = 'maxime';
$password = 'Sigma$7568';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Récupérer les informations de l'article en fonction du type
if ($itemType === 'vaisseau') {
    $stmt = $pdo->prepare("SELECT * FROM ships WHERE ship_id = ? AND stock > 0");
} else { // pack
    $stmt = $pdo->prepare("SELECT * FROM packs WHERE id = ?");
}

$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    echo "Article non trouvé ou indisponible.";
    exit;
}

// Traitement du formulaire d'achat
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ici vous pouvez ajouter la logique de traitement de l'achat :
    // par exemple, vérifier le paiement, mettre à jour le stock, enregistrer la commande, etc.
    
    $payment_method = $_POST['payment_method']; // 'paypal' ou 'credits'
    
    // Si paiement par crédits, vérifier que l'utilisateur a assez de crédits
    if ($payment_method === 'credits') {
        // Récupérer le solde de crédits de l'utilisateur
        $stmtCredits = $pdo->prepare("SELECT credits FROM users WHERE user_id = ?");
        $stmtCredits->execute([$_SESSION['user_id']]);
        $userData = $stmtCredits->fetch(PDO::FETCH_ASSOC);
        
        if (!$userData) {
            echo "Utilisateur non trouvé.";
            exit;
        }
        
        $userCredits = $userData['credits'];
        if ($userCredits < $item['price']) {
            echo "Crédits insuffisants pour cet achat.";
            exit;
        }
    }
    
    // Simuler le paiement via Paypal ou déduire les crédits
    if ($payment_method === 'paypal') {
        // Simulation de paiement Paypal (paiement toujours réussi dans cet exemple)
    } elseif ($payment_method === 'credits') {
        // Déduire le montant du vaisseau des crédits de l'utilisateur
        $updateCredits = $pdo->prepare("UPDATE users SET credits = credits - ? WHERE user_id = ?");
        $updateCredits->execute([$item['price'], $_SESSION['user_id']]);
    } else {
        echo "Moyen de paiement invalide.";
        exit;
    }

    if ($itemType === 'vaisseau') {
        // Exemple : décrémenter le stock du vaisseau
        $updateStmt = $pdo->prepare("UPDATE ships SET stock = stock - 1 WHERE ship_id = ?");
        $updateStmt->execute([$id]);
    }
    // Pour les packs, vous pourriez enregistrer la commande sans gérer de stock
    // ...

    // Enregistrer l'achat dans la base de données
    $insertPurchase = $pdo->prepare("INSERT INTO purchases (user_id, ship_id, price, purchase_date) VALUES (?, ?, ?, NOW())");
    $insertPurchase->execute([$_SESSION['user_id'], $id, $item['price']]);

    // Redirection vers la page de compte après l'achat
    header("Location: account.php?purchase_success=1");

    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Achat - StarFarer Inc.</title>
        <link rel="stylesheet" href="css/styles.css">
    </head>

    <body>
        <div class="container">
            <h1>Achat de <?php echo htmlspecialchars($item['name']); ?></h1>
            <div class="item-details">
                <?php
                // Pour un vaisseau, afficher l'image si définie
                if ($itemType === 'vaisseau') {
                    if (!empty($item['image'])) {
                        echo '<img src="' . htmlspecialchars($item['image']) . '" alt="' . htmlspecialchars($item['name']) . '">';
                    } else {
                        echo '<img src="default-ship.png" alt="Image par défaut">';
                    }
                }
                ?>
                <p><?php echo htmlspecialchars($item['description']); ?></p>
                <p>Prix : <?php echo htmlspecialchars($item['price']); ?> <?php echo $itemType === 'vaisseau' ? 'crédits' : '€'; ?></p>
                <?php if ($itemType === 'vaisseau'): ?>
                    <p>Stock restant : <?php echo htmlspecialchars($item['stock']); ?></p>
                <?php endif; ?>
            </div>
            <!-- Formulaire de confirmation d'achat -->
            <form method="post" action="">
                <input type="hidden" name="item_type" value="<?php echo htmlspecialchars($itemType); ?>">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                <p>Choisissez un moyen de paiement :</p>
                <label for="paypal">
                    <input type="radio" name="payment_method" value="paypal" id="paypal" checked> Paypal
                </label>
                <label for="credits">
                    <input type="radio" name="payment_method" value="credits" id="credits"> Crédits
                </label>
                <button type="submit" class="btn">Confirmer l'achat</button>
            </form>
            <a href="store.php" class="back-link">Retour au magasin</a>
        </div>
    </body>
</html>