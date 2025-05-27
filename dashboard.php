<?php
session_start(); // ALWAYS START SESSION AT THE VERY TOP

// Include database connection
require_once 'db_connection.php';
// Include API helpers
require_once 'rawg_api_helper.php';

// Check if the user is logged in and verified
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id, username, email, name, address, phone FROM users WHERE id = ? AND is_verified = TRUE");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    // User not found or not verified
    header('Location: login.php');
    exit();
}

$row = $result->fetch_assoc();
$username = htmlspecialchars($row['username']);
$email = htmlspecialchars($row['email']);
$name = htmlspecialchars($row['name']);
$address = htmlspecialchars($row['address']);
$phone = htmlspecialchars($row['phone']);

// --- General Messages (retrieve from session and clear) ---
$successMessage = '';
$errorMessage = '';

if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    $errorMessage = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}


// --- GAME SEARCH & FOLDERS LOGIC ---
$gameSearchResults = [];
$gameSearchError = ''; // This is for search errors that don't cause a redirect
$selectedGame = null; // To store details of a game to add to folder

// Handle Game Search
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_game_query'])) {
    $gameSearchQuery = trim($_POST['search_game_query']);
    if (!empty($gameSearchQuery)) {
        $apiResponse = fetchGamesFromRawg($gameSearchQuery);
        if (isset($apiResponse['error'])) {
            $gameSearchError = $apiResponse['error']; // Assign to local variable for immediate display
        } else {
            $gameSearchResults = $apiResponse;
        }
    } else {
        $gameSearchError = "Please enter a game title to search.";
    }
    // No redirect here for search, as results/errors should show on the same page directly.
    // If you prefer a redirect here, you'd store $gameSearchResults and $gameSearchError in session too.
}

// Handle selection of a game for adding to folder (via GET parameter)
if (isset($_GET['add_game_rawg_id'])) {
    $rawgId = filter_input(INPUT_GET, 'add_game_rawg_id', FILTER_VALIDATE_INT);
    if ($rawgId) {
        $gameDetails = fetchGameDetailsFromRawg($rawgId);
        if (isset($gameDetails['error'])) {
            $_SESSION['error_message'] = "Error fetching game details to add: " . $gameDetails['error'];
            header('Location: dashboard.php#game-search'); // Redirect back with error
            exit();
        } else {
            $selectedGame = $gameDetails;
            // No redirect here, we want to stay on the page and show the selected game form
        }
    } else {
        $_SESSION['error_message'] = "Invalid Game ID provided for adding.";
        header('Location: dashboard.php#game-search'); // Redirect back with error
        exit();
    }
}

// Fetch user game folders
$userGameFolders = [];
$stmt = $conn->prepare("SELECT id, folder_name FROM game_folders WHERE user_id = ? ORDER BY folder_name ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$foldersResult = $stmt->get_result();
while ($folder = $foldersResult->fetch_assoc()) {
    $userGameFolders[] = $folder;
}
$stmt->close();

// Create new game folder
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_game_folder'])) {
    $folderName = trim($_POST['game_folder_name']);
    if (!empty($folderName)) {
        $stmt = $conn->prepare("SELECT id FROM game_folders WHERE user_id = ? AND folder_name = ?");
        $stmt->bind_param("is", $user_id, $folderName);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $_SESSION['error_message'] = "Game folder with this name already exists.";
        } else {
            $stmt = $conn->prepare("INSERT INTO game_folders (user_id, folder_name) VALUES (?, ?)");
            $stmt->bind_param("is", $user_id, $folderName);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Game folder '{$folderName}' created successfully!";
            } else {
                $_SESSION['error_message'] = "Error creating game folder: " . $stmt->error;
            }
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Game folder name cannot be empty.";
    }
    header('Location: dashboard.php#my-game-folders'); // Redirect to folder section
    exit();
}

// Add game to folder
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_game_to_folder'])) {
    $selectedGameFolderId = filter_input(INPUT_POST, 'game_folder_id', FILTER_VALIDATE_INT);
    $rawgIdToAdd = filter_input(INPUT_POST, 'rawg_id_to_add', FILTER_VALIDATE_INT);
    $gameTitleToAdd = htmlspecialchars($_POST['game_title_to_add']);

    if (!$selectedGameFolderId) {
        $_SESSION['error_message'] = "Please select a valid game folder.";
    } elseif (!$rawgIdToAdd) {
        $_SESSION['error_message'] = "Invalid game ID to add.";
    } else {
        // 1. Check if game already exists in our 'games' table
        $stmt = $conn->prepare("SELECT id FROM games WHERE rawg_id = ?");
        $stmt->bind_param("i", $rawgIdToAdd);
        $stmt->execute();
        $gameResult = $stmt->get_result();
        $gameDbId = null;

        if ($gameResult->num_rows > 0) {
            $gameDbId = $gameResult->fetch_assoc()['id'];
        } else {
            // If not, fetch full details from RAWG and insert it
            $fullGameDetails = fetchGameDetailsFromRawg($rawgIdToAdd);
            if (isset($fullGameDetails['error'])) {
                $_SESSION['error_message'] = "Error fetching full game details: " . $fullGameDetails['error'];
            } else {
                $genres = [];
                if (isset($fullGameDetails['genres'])) {
                    foreach ($fullGameDetails['genres'] as $genre) {
                        $genres[] = $genre['name'];
                    }
                }
                $genresString = implode(', ', $genres);

                $platforms = [];
                if (isset($fullGameDetails['platforms'])) {
                    foreach ($fullGameDetails['platforms'] as $platformData) {
                        $platforms[] = $platformData['platform']['name'];
                    }
                }
                $platformsString = implode(', ', $platforms);

                $developer = isset($fullGameDetails['developers'][0]['name']) ? $fullGameDetails['developers'][0]['name'] : null;
                $publisher = isset($fullGameDetails['publishers'][0]['name']) ? $fullGameDetails['publishers'][0]['name'] : null;

                $released_val = isset($fullGameDetails['released']) && $fullGameDetails['released'] !== '' ? $fullGameDetails['released'] : null;
                $background_image_val = isset($fullGameDetails['background_image']) && $fullGameDetails['background_image'] !== '' ? $fullGameDetails['background_image'] : null;
                $rating_val = isset($fullGameDetails['rating']) ? $fullGameDetails['rating'] : null;
                $description_raw_val = isset($fullGameDetails['description_raw']) && $fullGameDetails['description_raw'] !== '' ? $fullGameDetails['description_raw'] : null;

                $stmt = $conn->prepare("INSERT INTO games (rawg_id, title, release_date, background_image, rating, genres, platforms, developer, publisher, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param(
                    "isssdsssss",
                    $fullGameDetails['id'],
                    $fullGameDetails['name'],
                    $released_val,
                    $background_image_val,
                    $rating_val,
                    $genresString,
                    $platformsString,
                    $developer,
                    $publisher,
                    $description_raw_val
                );
                if ($stmt->execute()) {
                    $gameDbId = $stmt->insert_id;
                } else {
                    $_SESSION['error_message'] = "Error saving game to database: " . $stmt->error;
                }
            }
        }
        $stmt->close();

        if ($gameDbId) {
            // 2. Add game to game_folder_games table
            // Check if game is already in this folder
            $stmt = $conn->prepare("SELECT * FROM game_folder_games WHERE folder_id = ? AND game_id = ?");
            $stmt->bind_param("ii", $selectedGameFolderId, $gameDbId);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $_SESSION['error_message'] = "Game '{$gameTitleToAdd}' is already in this folder.";
            } else {
                $stmt = $conn->prepare("INSERT INTO game_folder_games (folder_id, game_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $selectedGameFolderId, $gameDbId);
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Game '{$gameTitleToAdd}' added to folder successfully!";
                } else {
                    $_SESSION['error_message'] = "Error adding game to folder: " . $stmt->error;
                }
            }
            $stmt->close();
        }
    }
    header('Location: dashboard.php#game-search'); // Redirect to search section after adding game
    exit();
}

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - User Profile & Games</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7fc; margin: 0; padding: 0; display: flex; }
        .sidebar { width: 250px; background-color: #333; color: white; padding: 20px; box-shadow: 2px 0 5px rgba(0,0,0,0.1); }
        .sidebar h2 { text-align: center; margin-bottom: 30px; }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar ul li { margin-bottom: 10px; }
        .sidebar ul li a { color: white; text-decoration: none; display: block; padding: 10px; border-radius: 4px; transition: background-color 0.3s; }
        .sidebar ul li a:hover { background-color: #555; }
        .main-content { flex-grow: 1; padding: 20px; }
        .section-card { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .section-card h2 { text-align: center; color: #333; margin-bottom: 20px; }
        .profile-card table { width: 100%; margin-top: 20px; border-collapse: collapse; }
        .profile-card table, th, td { border: 1px solid #ddd; }
        .profile-card th, td { padding: 12px; text-align: left; }
        .profile-card th { background-color: #f4f7fc; }
        .profile-card td { background-color: #fff; }
        .logout-btn { display: block; width: 100%; padding: 12px; background-color: #f44336; color: white; text-align: center; border: none; border-radius: 4px; margin-top: 20px; cursor: pointer; }
        .logout-btn:hover { background-color: #e53935; }

        /* Game Search Styles */
        .search-form { display: flex; margin-bottom: 20px; }
        .search-form input[type="text"] { flex-grow: 1; padding: 10px; border: 1px solid #ccc; border-radius: 4px 0 0 4px; }
        .search-form button { padding: 10px 15px; background-color: #4CAF50; color: white; border: none; border-radius: 0 4px 4px 0; cursor: pointer; }
        .search-form button:hover { background-color: #45a049; }
        .search-results { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 15px; }
        .game-card { border: 1px solid #eee; border-radius: 8px; overflow: hidden; text-align: center; background-color: #f9f9f9; padding: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .game-card img { max-width: 100%; height: auto; border-radius: 4px; margin-bottom: 10px; }
        .game-card h4 { margin: 5px 0; font-size: 1em; }
        .game-card p { font-size: 0.8em; color: #666; margin: 0; }
        .game-card a { display: inline-block; background-color: #007bff; color: white; padding: 8px 12px; border-radius: 4px; text-decoration: none; margin-top: 10px; }
        .game-card a:hover { background-color: #0056b3; }

        /* Folder Management Styles */
        .folder-form { display: flex; margin-bottom: 20px; }
        .folder-form input[type="text"] { flex-grow: 1; padding: 10px; border: 1px solid #ccc; border-radius: 4px 0 0 4px; }
        .folder-form button { padding: 10px 15px; background-color: #007bff; color: white; border: none; border-radius: 0 4px 4px 0; cursor: pointer; }
        .folder-form button:hover { background-color: #0056b3; }
        .folder-list ul { list-style: none; padding: 0; }
        .folder-list li { background-color: #e9ecef; padding: 10px; border-radius: 4px; margin-bottom: 8px; display: flex; justify-content: space-between; align-items: center; }
        .folder-list li a { text-decoration: none; color: #333; font-weight: bold; }
        .folder-list li a:hover { color: #007bff; }

        .message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px; border-radius: 4px; margin-bottom: 15px; }

        /* Game details for adding to folder */
        .selected-game-details { display: flex; align-items: center; background-color: #f0f0f0; padding: 15px; border-radius: 8px; margin-top: 20px; margin-bottom: 20px; }
        .selected-game-details img { width: 100px; height: auto; margin-right: 15px; border-radius: 4px; }
        .selected-game-details .info { flex-grow: 1; }
        .selected-game-details .info h3 { margin: 0 0 5px 0; }
        .selected-game-details .info p { margin: 0; font-size: 0.9em; color: #555; }
        .add-to-folder-form { margin-top: 15px; }
        .add-to-folder-form select { padding: 8px; border-radius: 4px; border: 1px solid #ccc; margin-right: 10px; }
        .add-to-folder-form button { padding: 8px 15px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .add-to-folder-form button:hover { background-color: #218838; }

        /* RAWG Attribution */
        .rawg-attribution { font-size: 0.8em; color: #777; margin-top: 20px; text-align: center; }
        .rawg-attribution a { color: #777; text-decoration: none; }
        .rawg-attribution a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Dashboard</h2>
    <ul>
        <li><a href="#profile">My Profile</a></li>
        <li><a href="#game-search">Game Search</a></li>
        <li><a href="#my-game-folders">My Game Folders</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="main-content">
    <div id="profile" class="section-card profile-card">
        <h2>User Profile</h2>
        <table>
            <tr>
                <th>Name</th>
                <td><?php echo $name; ?></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><?php echo $email; ?></td>
            </tr>
            <tr>
                <th>Username</th>
                <td><?php echo $username; ?></td>
            </tr>
            <tr>
                <th>Phone</th>
                <td><?php echo $phone ? $phone : 'Not Available'; ?></td>
            </tr>
            <tr>
                <th>Address</th>
                <td><?php echo $address; ?></td>
            </tr>
        </table>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <div id="game-search" class="section-card">
        <h2>Search Games (RAWG.io)</h2>
        <?php if ($successMessage): ?>
            <p class="message success"><?php echo $successMessage; ?></p>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <p class="message error"><?php echo $errorMessage; ?></p>
        <?php endif; ?>
        <?php if ($gameSearchError): ?>
            <p class="message error"><?php echo $gameSearchError; ?></p>
        <?php endif; ?>

        <form method="POST" action="" class="search-form">
            <input type="text" name="search_game_query" placeholder="Search for games..." value="<?php echo isset($_POST['search_game_query']) ? htmlspecialchars($_POST['search_game_query']) : ''; ?>">
            <button type="submit">Search</button>
        </form>

        <?php if ($selectedGame): ?>
            <div class="selected-game-details">
                <img src="<?php echo ($selectedGame['background_image'] ? htmlspecialchars($selectedGame['background_image']) : 'https://via.placeholder.com/100x150?text=No+Image'); ?>" alt="<?php echo htmlspecialchars($selectedGame['name']); ?> Cover">
                <div class="info">
                    <h3><?php echo htmlspecialchars($selectedGame['name']); ?> (<?php echo htmlspecialchars($selectedGame['released']); ?>)</h3>
                    <p>Rating: <?php echo htmlspecialchars($selectedGame['rating']); ?></p>
                    <p>Genres: <?php
                        $genres = [];
                        if (isset($selectedGame['genres'])) {
                            foreach ($selectedGame['genres'] as $genre) {
                                $genres[] = $genre['name'];
                            }
                        }
                        echo implode(', ', $genres);
                    ?></p>
                    <p>Platforms: <?php
                        $platforms = [];
                        if (isset($selectedGame['platforms'])) {
                            foreach ($selectedGame['platforms'] as $platformData) {
                                $platforms[] = $platformData['platform']['name'];
                            }
                        }
                        echo implode(', ', $platforms);
                    ?></p>
                    <p>Developer: <?php echo isset($selectedGame['developers'][0]['name']) ? htmlspecialchars($selectedGame['developers'][0]['name']) : 'N/A'; ?></p>
                    <p>Publisher: <?php echo isset($selectedGame['publishers'][0]['name']) ? htmlspecialchars($selectedGame['publishers'][0]['name']) : 'N/A'; ?></p>
                    <p><?php echo isset($selectedGame['description_raw']) ? substr(htmlspecialchars($selectedGame['description_raw']), 0, 200) . (strlen($selectedGame['description_raw']) > 200 ? '...' : '') : 'No description available.'; ?></p>

                    <?php if (!empty($userGameFolders)): ?>
                        <form method="POST" action="" class="add-to-folder-form">
                            <input type="hidden" name="rawg_id_to_add" value="<?php echo htmlspecialchars($selectedGame['id']); ?>">
                            <input type="hidden" name="game_title_to_add" value="<?php echo htmlspecialchars($selectedGame['name']); ?>">
                            <label for="game_folder_id">Add to folder:</label>
                            <select name="game_folder_id" id="game_folder_id" required>
                                <option value="">-- Select Folder --</option>
                                <?php foreach ($userGameFolders as $folder): ?>
                                    <option value="<?php echo htmlspecialchars($folder['id']); ?>"><?php echo htmlspecialchars($folder['folder_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="add_game_to_folder">Add Game</button>
                        </form>
                    <?php else: ?>
                        <p>No game folders available. Please create a folder in "My Game Folders" section first.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($gameSearchResults)): ?>
            <h3>Search Results:</h3>
            <div class="search-results">
                <?php foreach ($gameSearchResults as $game): ?>
                    <div class="game-card">
                        <img src="<?php echo ($game['background_image'] ? htmlspecialchars($game['background_image']) : 'https://via.placeholder.com/100x150?text=No+Image'); ?>" alt="<?php echo htmlspecialchars($game['name']); ?> Cover">
                        <h4><?php echo htmlspecialchars($game['name']); ?></h4>
                        <p><?php echo htmlspecialchars($game['released']); ?></p>
                        <p>Rating: <?php echo htmlspecialchars($game['rating']); ?></p>
                        <a href="dashboard.php?add_game_rawg_id=<?php echo htmlspecialchars($game['id']); ?>#game-search">Add to Folder</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_game_query']) && empty($gameSearchError)): ?>
             <p>No games found for "<?php echo htmlspecialchars($_POST['search_game_query']); ?>".</p>
        <?php endif; ?>
         <div class="rawg-attribution">
            Data provided by <a href="https://rawg.io" target="_blank">RAWG.io</a>
        </div>
    </div>

    <div id="my-game-folders" class="section-card">
        <h2>My Game Folders</h2>
        <?php if ($successMessage): ?>
            <p class="message success"><?php echo $successMessage; ?></p>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <p class="message error"><?php echo $errorMessage; ?></p>
        <?php endif; ?>

        <h3>Create New Game Folder:</h3>
        <form method="POST" action="" class="folder-form">
            <input type="text" name="game_folder_name" placeholder="Enter game folder name" required>
            <button type="submit" name="create_game_folder">Create Folder</button>
        </form>

        <h3>Your Game Folders:</h3>
        <?php if (!empty($userGameFolders)): ?>
            <div class="folder-list">
                <ul>
                    <?php foreach ($userGameFolders as $folder): ?>
                        <li>
                            <a href="game_folder_details.php?folder_id=<?php echo htmlspecialchars($folder['id']); ?>">
                                <?php echo htmlspecialchars($folder['folder_name']); ?>
                            </a>
                            <span>(<?php
                                $conn_temp = new mysqli("localhost", "root", "", "login");
                                if ($conn_temp->connect_error) {
                                    echo "DB Error";
                                } else {
                                    $stmt_count = $conn_temp->prepare("SELECT COUNT(*) FROM game_folder_games WHERE folder_id = ?");
                                    $stmt_count->bind_param("i", $folder['id']);
                                    $stmt_count->execute();
                                    $count_result = $stmt_count->get_result()->fetch_row();
                                    echo $count_result[0];
                                    $stmt_count->close();
                                    $conn_temp->close();
                                }
                            ?> games)</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else: ?>
            <p>You haven't created any game folders yet.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>