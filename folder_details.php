<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$folder_id = filter_input(INPUT_GET, 'folder_id', FILTER_VALIDATE_INT);

if (!$folder_id) {
    header('Location: dashboard.php#my-folders'); // Redirect if no valid folder ID
    exit();
}

// Connect to the database
$conn = new mysqli("localhost", "root", "", "login");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Verify that the folder belongs to the current user
$stmt = $conn->prepare("SELECT folder_name FROM folders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $folder_id, $user_id);
$stmt->execute();
$folderResult = $stmt->get_result();

if ($folderResult->num_rows == 0) {
    // Folder not found or doesn't belong to the user
    header('Location: dashboard.php#my-folders');
    exit();
}

$folder_name = htmlspecialchars($folderResult->fetch_assoc()['folder_name']);
$stmt->close();

// Fetch movies in this folder
$moviesInFolder = [];
$stmt = $conn->prepare("
    SELECT m.id, m.title, m.year, m.poster, m.plot, m.genre, m.director, m.actors, m.imdb_rating, m.runtime
    FROM movies m
    JOIN folder_movies fm ON m.id = fm.movie_id
    WHERE fm.folder_id = ?
    ORDER BY m.title ASC
");
$stmt->bind_param("i", $folder_id);
$stmt->execute();
$moviesResult = $stmt->get_result();

while ($movie = $moviesResult->fetch_assoc()) {
    $moviesInFolder[] = $movie;
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Folder: <?php echo $folder_name; ?></title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7fc; margin: 0; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; background-color: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
        h1 { text-align: center; color: #333; margin-bottom: 25px; }
        .back-link { display: inline-block; margin-bottom: 20px; text-decoration: none; color: #007bff; font-weight: bold; }
        .back-link:hover { text-decoration: underline; }
        .movie-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; }
        .movie-card { border: 1px solid #eee; border-radius: 8px; overflow: hidden; background-color: #f9f9f9; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .movie-card img { width: 100%; height: 300px; object-fit: cover; border-bottom: 1px solid #eee; }
        .movie-card .details { padding: 15px; }
        .movie-card h3 { margin-top: 0; margin-bottom: 10px; font-size: 1.1em; color: #333; }
        .movie-card p { font-size: 0.9em; color: #666; margin-bottom: 5px; }
        .movie-card p strong { color: #333; }
        .no-movies-message { text-align: center; color: #555; font-style: italic; margin-top: 30px; }
    </style>
</head>
<body>

<div class="container">
    <a href="dashboard.php#my-folders" class="back-link">&larr; Back to Dashboard</a>
    <h1>Movies in Folder: "<?php echo $folder_name; ?>"</h1>

    <?php if (!empty($moviesInFolder)): ?>
        <div class="movie-grid">
            <?php foreach ($moviesInFolder as $movie): ?>
                <div class="movie-card">
                    <img src="<?php echo ($movie['poster'] && $movie['poster'] != 'N/A' ? htmlspecialchars($movie['poster']) : 'https://via.placeholder.com/200x300?text=No+Poster'); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?> Poster">
                    <div class="details">
                        <h3><?php echo htmlspecialchars($movie['title']); ?> (<?php echo htmlspecialchars($movie['year']); ?>)</h3>
                        <p><strong>Genre:</strong> <?php echo htmlspecialchars($movie['genre']); ?></p>
                        <p><strong>Director:</strong> <?php echo htmlspecialchars($movie['director']); ?></p>
                        <p><strong>IMDb Rating:</strong> <?php echo htmlspecialchars($movie['imdb_rating']); ?></p>
                        <p><strong>Runtime:</strong> <?php echo htmlspecialchars($movie['runtime']); ?></p>
                        <p><strong>Plot:</strong> <?php echo substr(htmlspecialchars($movie['plot']), 0, 150); ?>...</p>
                        </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="no-movies-message">This folder is empty. Search for movies and add them!</p>
    <?php endif; ?>
</div>

</body>
</html>