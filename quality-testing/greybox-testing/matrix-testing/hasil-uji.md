### Analisis Grey-Box Testing (Metode: Matrix Testing / Decision Table)

Pengujian ini menggunakan tabel keputusan untuk memvalidasi logika bisnis yang kompleks dalam aplikasi.

---

#### Matriks 1: Fungsionalitas "Create New Game Folder"

Matriks ini menguji semua aturan bisnis yang terkait dengan proses pembuatan folder game baru di `dashboard.php`.

| | **Aturan 1** | **Aturan 2** | **Aturan 3** | **Aturan 4** |
| :--- | :---: | :---: | :---: | :---: |
| **Kondisi (Inputs)** | | | | |
| Nama Folder Disediakan | Ya | Ya | Tidak | - |
| Nama Folder Sudah Ada | Tidak | Ya | - | - |
| Form Dikirim (POST) | Ya | Ya | Ya | Tidak |
| | | | | |
| **Aksi (Outputs)** | | | | |
| Simpan folder baru ke DB | ✓ | | | |
| Set `$_SESSION['success_message']` | ✓ | | | |
| Set `$_SESSION['error_message']` (Nama Ada) | | ✓ | | |
| Set `$_SESSION['error_message']` (Nama Kosong) | | | ✓ | |
| Redirect ke `dashboard.php#my-game-folders`| ✓ | ✓ | ✓ | |
| Tampilkan halaman normal | | | | ✓ |

**Analisis Matriks 1:**
* **Aturan 1 (Happy Path):** Jika pengguna mengirim form dengan nama folder yang valid dan belum ada, sistem akan menyimpannya ke database, mengatur pesan sukses, dan melakukan redirect.
* **Aturan 2 (Duplikat):** Jika nama folder sudah ada, sistem tidak akan menyimpan, melainkan mengatur pesan error yang sesuai dan melakukan redirect.
* **Aturan 3 (Input Tidak Valid):** Jika nama folder kosong, sistem akan mengatur pesan error yang relevan dan melakukan redirect.
* **Aturan 4 (Akses Normal):** Jika halaman diakses tanpa mengirim form (metode GET), tidak ada aksi yang diambil dan halaman ditampilkan seperti biasa.

Logika untuk fitur ini terbukti **kuat** karena mencakup semua skenario input utama.

---

#### Matriks 2: Fungsionalitas "View Game Folder Details"

Matriks ini menguji semua aturan otorisasi dan validasi saat mengakses halaman `game_folder_details.php`.

| | **Aturan 1** | **Aturan 2** | **Aturan 3** | **Aturan 4** |
| :--- | :---: | :---: | :---: | :---: |
| **Kondisi (Inputs)** | | | | |
| `folder_id` Disediakan & Valid | Ya | Ya | Tidak | - |
| `folder_id` Milik Pengguna | Ya | Tidak | - | - |
| Pengguna Sudah Login | Ya | Ya | Ya | Tidak |
| | | | | |
| **Aksi (Outputs)** | | | | |
| Ambil data folder & game | ✓ | | | |
| Tampilkan halaman detail folder | ✓ | | | |
| Set `$_SESSION['error_message']` | | ✓ | ✓ | |
| Redirect ke `dashboard.php` | | ✓ | ✓ | |
| Redirect ke `login.php` | | | | ✓ |

**Analisis Matriks 2:**
* **Aturan 1 (Happy Path):** Jika pengguna yang sudah login mengakses halaman dengan ID folder yang valid dan merupakan miliknya, sistem akan mengambil data dan menampilkannya.
* **Aturan 2 (Akses Ilegal):** Jika pengguna mencoba mengakses folder milik orang lain, sistem akan mencegahnya, mengatur pesan error, dan mengembalikannya ke dashboard. Ini adalah pemeriksaan keamanan yang penting.
* **Aturan 3 (Input Tidak Valid):** Jika ID folder tidak disediakan atau formatnya salah, sistem akan menampilkan error dan mengembalikan pengguna ke dashboard.
* **Aturan 4 (Tidak Terotentikasi):** Jika pengguna belum login, sistem akan langsung mengalihkannya ke halaman login.

Logika untuk fitur ini juga terbukti **aman dan kuat**, dengan mekanisme perlindungan yang jelas terhadap akses tidak sah dan input yang buruk.