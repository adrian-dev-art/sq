<?php
session_start(); // <<< IMPORTANT: MUST BE THE VERY FIRST LINE

// Include database connection
require_once 'db_connection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$folder_id = filter_input(INPUT_GET, 'folder_id', FILTER_VALIDATE_INT);

if (!$folder_id) {
    // Set an error message in the session before redirecting
    $_SESSION['error_message'] = "No game folder selected or invalid folder ID provided.";
    header('Location: dashboard.php#my-game-folders'); // Redirect to dashboard's folder section
    exit();
}

// Verify that the folder belongs to the current user
$stmt = $conn->prepare("SELECT folder_name FROM game_folders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $folder_id, $user_id);
$stmt->execute();
$folderResult = $stmt->get_result();

if ($folderResult->num_rows == 0) {
    // Folder not found or doesn't belong to the user
    // Set an error message in the session before redirecting
    $_SESSION['error_message'] = "You do not have permission to view this folder or it does not exist.";
    header('Location: dashboard.php#my-game-folders'); // Redirect to dashboard's folder section
    exit();
}

$folder_name = htmlspecialchars($folderResult->fetch_assoc()['folder_name']);
$stmt->close();

// Fetch games in this folder
$gamesInFolder = [];
$stmt = $conn->prepare("
    SELECT g.id, g.title, g.release_date, g.background_image, g.rating, g.genres, g.platforms, g.developer, g.publisher, g.description
    FROM games g
    JOIN game_folder_games fg ON g.id = fg.game_id
    WHERE fg.folder_id = ?
    ORDER BY g.title ASC
");
$stmt->bind_param("i", $folder_id);
$stmt->execute();
$gamesResult = $stmt->get_result();

while ($game = $gamesResult->fetch_assoc()) {
    $gamesInFolder[] = $game;
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Folder: <?php echo $folder_name; ?></title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7fc; margin: 0; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; background-color: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
        h1 { text-align: center; color: #333; margin-bottom: 25px; }
        .back-link { display: inline-block; margin-bottom: 20px; text-decoration: none; color: #007bff; font-weight: bold; }
        .back-link:hover { text-decoration: underline; }
        .game-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; }
        .game-card { border: 1px solid #eee; border-radius: 8px; overflow: hidden; background-color: #f9f9f9; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .game-card img { width: 100%; height: 250px; object-fit: cover; border-bottom: 1px solid #eee; }
        .game-card .details { padding: 15px; }
        .game-card h3 { margin-top: 0; margin-bottom: 10px; font-size: 1.1em; color: #333; }
        .game-card p { font-size: 0.9em; color: #666; margin-bottom: 5px; }
        .game-card p strong { color: #333; }
        .no-games-message { text-align: center; color: #555; font-style: italic; margin-top: 30px; }
        .rawg-attribution { font-size: 0.8em; color: #777; margin-top: 20px; text-align: center; }
        .rawg-attribution a { color: #777; text-decoration: none; }
        .rawg-attribution a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="container">
    <a href="dashboard.php#my-game-folders" class="back-link">&larr; Back to Dashboard</a>
    <h1>Games in Folder: "<?php echo $folder_name; ?>"</h1>

    <?php if (!empty($gamesInFolder)): ?>
        <div class="game-grid">
            <?php foreach ($gamesInFolder as $game): ?>
                <div class="game-card">
                    <img src="<?php echo ($game['background_image'] ? htmlspecialchars($game['background_image']) : 'https://via.placeholder.com/200x250?text=No+Image'); ?>" alt="<?php echo htmlspecialchars($game['title']); ?> Cover">
                    <div class="details">
                        <h3><?php echo htmlspecialchars($game['title']); ?> (<?php echo htmlspecialchars($game['release_date']); ?>)</h3>
                        <p><strong>Rating:</strong> <?php echo htmlspecialchars($game['rating']); ?></p>
                        <p><strong>Genres:</strong> <?php echo htmlspecialchars($game['genres']); ?></p>
                        <p><strong>Platforms:</strong> <?php echo htmlspecialchars($game['platforms']); ?></p>
                        <p><strong>Developer:</strong> <?php echo htmlspecialchars($game['developer']); ?></p>
                        <p><strong>Publisher:</strong> <?php echo htmlspecialchars($game['publisher']); ?></p>
                        <p><strong>Description:</strong> <?php echo substr(htmlspecialchars($game['description']), 0, 150); ?>...</p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="no-games-message">This game folder is empty. Search for games and add them!</p>
    <?php endif; ?>
    <div class="rawg-attribution">
        Data provided by <a href="https://rawg.io" target="_blank">RAWG.io</a>
    </div>
</div>

</body>
</html>