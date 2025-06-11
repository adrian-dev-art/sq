### Analisis White-Box Testing (Metode: Desk Checking)

**Skenario:** Menambahkan game "Grand Theft Auto V" ke folder "My Favorites", di mana game tersebut sudah ada di dalam folder tersebut.

| Langkah | Baris Kode yang Dieksekusi | Variabel & Nilainya | Catatan / Output |
| :--- | :--- | :--- | :--- |
| 1 | `if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_game_to_folder']))` | `$_SERVER['REQUEST_METHOD']` = `'POST'`<br>`$_POST['add_game_to_folder']` = `isset (true)` | Kondisi `if` terpenuhi. Masuk ke blok logika. |
| 2 | `$selectedGameFolderId = filter_input(INPUT_POST, 'game_folder_id', ...)` | `$selectedGameFolderId` = `10` | Input `game_folder_id` valid. |
| 3 | `$rawgIdToAdd = filter_input(INPUT_POST, 'rawg_id_to_add', ...)` | `$rawgIdToAdd` = `3498` | Input `rawg_id_to_add` valid. |
| 4 | `$gameTitleToAdd = htmlspecialchars($_POST['game_title_to_add'])` | `$gameTitleToAdd` = `"Grand Theft Auto V"` | Judul game diambil dan disanitasi. |
| 5 | `if (!$selectedGameFolderId)` | `$selectedGameFolderId` = `10` | Kondisi `false`, dilewati. |
| 6 | `elseif (!$rawgIdToAdd)` | `$rawgIdToAdd` = `3498` | Kondisi `false`, dilewati. |
| 7 | **Blok `else` dieksekusi** | - | Masuk ke logika utama. |
| 8 | `$stmt = $conn->prepare("SELECT id FROM games WHERE rawg_id = ?")` | `$stmt` = (objek prepared statement) | Menyiapkan kueri untuk memeriksa apakah game ada di tabel `games`. |
| 9 | `$stmt->bind_param("i", $rawgIdToAdd)` | `$rawgIdToAdd` = `3498` | Mengikat parameter `rawg_id`. |
| 10 | `$stmt->execute()` | - | Kueri dieksekusi. |
| 11 | `$gameResult = $stmt->get_result()` | `$gameResult` = (objek hasil) | Mendapatkan hasil kueri. |
| 12 | `if ($gameResult->num_rows > 0)` | `$gameResult->num_rows` = `1` | Kondisi `true` (game ditemukan di DB). |
| 13 | `$gameDbId = $gameResult->fetch_assoc()['id']` | `$gameDbId` = `55` | Variabel `$gameDbId` sekarang berisi ID internal game dari tabel `games`. |
| 14 | `$stmt->close()` | - | Menutup statement pertama. |
| 15 | `if ($gameDbId)` | `$gameDbId` = `55` | Kondisi `true`, masuk ke blok untuk menambahkan game ke folder. |
| 16 | `$stmt = $conn->prepare("SELECT * FROM game_folder_games WHERE folder_id = ? AND game_id = ?")` | `$stmt` = (objek prepared statement) | Menyiapkan kueri untuk memeriksa apakah game **sudah ada di dalam folder spesifik ini**. |
| 17 | `$stmt->bind_param("ii", $selectedGameFolderId, $gameDbId)` | `$selectedGameFolderId` = `10`<br>`$gameDbId` = `55` | Mengikat parameter `folder_id` dan `game_id`. |
| 18 | `$stmt->execute()` | - | Kueri dieksekusi. |
| 19 | `$stmt->store_result()` | - | Menyimpan hasil untuk `num_rows`. |
| 20 | `if ($stmt->num_rows > 0)` | `$stmt->num_rows` = `1` | **Kondisi `true`!** Game sudah ada di folder ini. |
| 21 | `$_SESSION['error_message'] = "Game '{$gameTitleToAdd}' is already in this folder."` | `$_SESSION['error_message']` = `"Game 'Grand Theft Auto V' is already in this folder."` | **Pesan error yang benar diatur di session.** |
| 22 | `$stmt->close()` | - | Menutup statement kedua. |
| 23 | `header('Location: dashboard.php#game-search')` | - | Mengirim header untuk mengalihkan pengguna kembali ke dashboard. |
| 24 | `exit()` | - | **Menghentikan eksekusi skrip.** |

### Hasil Desk Checking

Proses *desk checking* menunjukkan bahwa logika program berjalan dengan benar untuk skenario ini. Alur program secara akurat mendeteksi bahwa game tersebut sudah ada di dalam folder dan mengatur pesan error yang sesuai di dalam `$_SESSION`. Setelah itu, program mengalihkan pengguna dan menghentikan eksekusi, mencegah kode untuk menambahkan duplikat dijalankan. Ini membuktikan bahwa mekanisme perlindungan terhadap duplikasi data berfungsi seperti yang diharapkan.