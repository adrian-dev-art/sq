### Analisis White-Box Testing (Metode: Data Flow Testing)

Pengujian ini melacak siklus hidup variabel (definisi, penggunaan, penghancuran) untuk menemukan anomali dalam alur data.

---

### Skenario 1: Alur Data Otentikasi dan Profil Pengguna

Skenario ini melacak data pengguna dari sesi hingga ditampilkan di halaman `dashboard.php`.

| Variabel | Definisi (Definition) | Penggunaan (Usage) | Penghancuran (Destruction) | Analisis Alur Data |
| :--- | :--- | :--- | :--- | :--- |
| `$_SESSION['user_id']` | Didefinisikan saat pengguna berhasil **login** (di `login.php`). | 1. **`dashboard.php`:** Diperiksa (`isset`) untuk memastikan pengguna sudah login.<br>2. **`dashboard.php`:** Disalin ke variabel lokal `$user_id`.<br>3. **`dashboard.php` & `game_folder_details.php`:** Digunakan dalam *prepared statement* untuk mengambil data yang hanya milik pengguna tersebut. | Saat pengguna **logout** (`session_destroy()` di `logout.php`). | **Baik:** Alur data ini aman dan benar. Data `user_id` dari sesi secara konsisten digunakan sebagai kunci untuk otorisasi dan pengambilan data spesifik pengguna, mencegah pengguna melihat data orang lain. |
| `$username`, `$email`, `$name`, dll. | **`dashboard.php`:** Didefinisikan setelah mengambil data dari database (`$result->fetch_assoc()`) dan langsung disanitasi dengan `htmlspecialchars()`. | **`dashboard.php`:** Dicetak (`echo`) di dalam tabel HTML untuk ditampilkan kepada pengguna. | Di akhir eksekusi skrip `dashboard.php`. | **Sangat Baik:** Alur ini menunjukkan praktik keamanan yang baik. Data diambil dari database, segera disanitasi untuk mencegah XSS, lalu digunakan untuk tampilan. Alur dari sumber (DB) -> sanitasi -> penggunaan (tampilan) sudah benar. |

---

### Skenario 2: Alur Data Pesan Notifikasi (Flash Messages)

Skenario ini melacak pesan sukses atau error yang perlu ditampilkan setelah pengalihan halaman (redirect).

| Variabel | Definisi (Definition) | Penggunaan (Usage) | Penghancuran (Destruction) | Analisis Alur Data |
| :--- | :--- | :--- | :--- | :--- |
| `$_SESSION['success_message']` & `$_SESSION['error_message']` | **`dashboard.php`:** Didefinisikan dalam berbagai blok logika (misal: setelah berhasil membuat folder atau menambahkan game) sebelum `header('Location: ...')`. | **`dashboard.php` (setelah redirect):**<br>1. Diperiksa keberadaannya (`isset`).<br>2. Disalin ke variabel lokal (`$successMessage`, `$errorMessage`). | **`dashboard.php` (setelah redirect):**<br>Langsung dihancurkan dengan `unset()` setelah disalin ke variabel lokal. | **Sangat Baik:** Ini adalah implementasi Pola **Post-Redirect-Get (PRG)** yang sempurna. Data (pesan) didefinisikan, disimpan sementara di sesi agar bisa "selamat" dari redirect, digunakan sekali untuk ditampilkan, lalu segera dihancurkan untuk mencegah pesan muncul berulang kali. |

---

### Skenario 3: Alur Data Penambahan Game ke Folder

Ini adalah alur data yang paling kompleks, melacak ID game dari API hingga masuk ke database.

| Variabel | Definisi (Definition) | Penggunaan (Usage) | Penghancuran (Destruction) | Analisis Alur Data |
| :--- | :--- | :--- | :--- | :--- |
| `rawg_id` (Game ID dari API) | **1. Hasil API:** Didefinisikan sebagai bagian dari hasil pencarian game dari `fetchGamesFromRawg()`.<br>**2. Parameter GET:** Didefinisikan sebagai `$_GET['add_game_rawg_id']` saat pengguna mengklik tautan "Add to Folder".<br>**3. Parameter POST:** Didefinisikan sebagai `$_POST['rawg_id_to_add']` dari *hidden input* saat form penambahan game disubmit. | **1. `dashboard.php` (Search Result):** Digunakan untuk membuat URL dengan parameter GET.<br>**2. `dashboard.php` (Handle GET):** Dibaca dengan `filter_input()`, digunakan untuk memanggil `fetchGameDetailsFromRawg()` dan menampilkan detail game yang dipilih.<br>**3. `dashboard.php` (Handle POST):** Dibaca dengan `filter_input()`, digunakan untuk:<br> a. Memeriksa apakah game sudah ada di tabel `games` lokal.<br> b. Jika tidak, digunakan untuk mengambil detail lengkap dari API lalu menyimpannya ke tabel `games`. | Variabel `$_GET` dan `$_POST` hancur di akhir setiap request. Nilai dari `rawg_id` pada akhirnya "hidup abadi" sebagai data di dalam tabel `games` di database. | **Baik:** Alur data ini logis. Sebuah ID tunggal berpindah dari API -> Tampilan -> GET -> POST -> Database. Penggunaan `filter_input` untuk membaca ID dari `$_GET` dan `$_POST` adalah praktik yang baik untuk validasi dan sanitasi. |

---

### Temuan Anomali Alur Data (Data Flow Anomalies)

| Lokasi | Variabel | Anomali Terdeteksi | Dampak | Rekomendasi Perbaikan |
| :--- | :--- | :--- | :--- | :--- |
| **`dashboard.php`**, di dalam `foreach ($userGameFolders as $folder)` | `$conn_temp`, `$stmt_count` | **Anomali Performa & Redundansi:**<br>Di dalam perulangan untuk menampilkan daftar folder, sebuah **koneksi database baru (`$conn_temp`)** dibuat untuk **setiap folder** hanya untuk menghitung jumlah game di dalamnya. | 1. **Sangat Tidak Efisien:** Jika pengguna memiliki 20 folder, maka akan terjadi 20 koneksi database terpisah yang dibuka dan ditutup, membebani server database.<br>2. **Redundansi:** Kode ini mendefinisikan ulang variabel koneksi (`$conn_temp`) dan statement (`$stmt_count`) berulang kali, padahal koneksi utama (`$conn`) sudah tersedia. | **Ganti dengan `JOIN` dan `COUNT`:**<br>Modifikasi kueri awal yang mengambil daftar folder untuk langsung menyertakan jumlah game. Ini akan menghilangkan perulangan koneksi database sepenuhnya. <br><br>**Contoh Kueri Perbaikan:** <br>```sql <br>SELECT f.id, f.folder_name, COUNT(fg.game_id) as game_count <br>FROM game_folders f <br>LEFT JOIN game_folder_games fg ON f.id = fg.folder_id <br>WHERE f.user_id = ? <br>GROUP BY f.id, f.folder_name <br>ORDER BY f.folder_name ASC <br>```<br>Dengan ini, Anda hanya butuh satu kueri untuk mendapatkan semua informasi yang dibutuhkan. |

---