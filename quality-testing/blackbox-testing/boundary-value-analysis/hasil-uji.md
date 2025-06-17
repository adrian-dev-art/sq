### Analisis Black-Box Testing (Metode: Boundary Value Analysis)

Pengujian ini berfokus pada pengujian nilai-nilai batas pada field input untuk menemukan potensi error.

*Asumsi: Batasan panjang karakter berikut didasarkan pada praktik umum desain database (misalnya, `VARCHAR(50)`). Dalam pengujian nyata, batasan ini harus dikonfirmasi dari dokumentasi spesifikasi.*

---

#### 1. Input Field: Nama Folder (`game_folder_name` di Dashboard)

**Asumsi Batasan:** Panjang karakter minimal 1, maksimal 50.

| ID Tes | Deskripsi Batas | Data Uji (Panjang Karakter) | Input yang Digunakan | Hasil yang Diharapkan |
| :--- | :--- | :--- | :--- | :--- |
| **BVA-F1** | Minimum - 1 (Tidak Valid) | 0 | (string kosong) | Sistem menampilkan error: **"Game folder name cannot be empty."** Folder tidak dibuat. |
| **BVA-F2** | Minimum (Valid) | 1 | `A` | Folder berhasil dibuat dengan nama "A". Pesan sukses ditampilkan. |
| **BVA-F3** | Minimum + 1 (Valid) | 2 | `My` | Folder berhasil dibuat dengan nama "My". Pesan sukses ditampilkan. |
| **BVA-F4** | Maksimum - 1 (Valid) | 49 | (string dengan 49 karakter) | Folder berhasil dibuat. Pesan sukses ditampilkan. |
| **BVA-F5** | Maksimum (Valid) | 50 | (string dengan 50 karakter) | Folder berhasil dibuat. Pesan sukses ditampilkan. |
| **BVA-F6** | Maksimum + 1 (Tidak Valid) | 51 | (string dengan 51 karakter) | Sistem menampilkan error: **"Folder name is too long."** atau sejenisnya. Folder tidak dibuat. |

---

#### 2. Input Field: Pencarian Game (`search_game_query` di Dashboard)

**Asumsi Batasan:** Karena ini adalah input pencarian, batasannya lebih longgar. Kita akan fokus pada kekosongan dan input yang sangat pendek/panjang. Asumsi maksimal 255 karakter.

| ID Tes | Deskripsi Batas | Data Uji (Panjang Karakter) | Input yang Digunakan | Hasil yang Diharapkan |
| :--- | :--- | :--- | :--- | :--- |
| **BVA-S1** | Minimum - 1 (Tidak Valid) | 0 | (string kosong) | Sistem menampilkan pesan: **"Please enter a game title to search."** Tidak ada pencarian yang dilakukan. |
| **BVA-S2** | Minimum (Valid) | 1 | `Z` | Sistem melakukan pencarian. Hasilnya bisa "No games found for 'Z'." atau menampilkan daftar game yang relevan. |
| **BVA-S3** | Maksimum (Valid) | 255 | (string dengan 255 karakter) | Sistem melakukan pencarian. Kemungkinan besar tidak ada hasil yang ditemukan, tetapi aplikasi tidak boleh error atau crash. |
| **BVA-S4** | Maksimum + 1 (Berpotensi Tidak Valid) | 256 | (string dengan 256 karakter) | Sistem harus menangani input ini dengan baik. Idealnya, input akan dipotong hingga 255 karakter atau sistem menampilkan pesan error bahwa input terlalu panjang, tetapi tidak boleh menyebabkan crash. |

---

#### 3. Input Field: Username (pada halaman Login)

**Asumsi Batasan:** Panjang karakter minimal 4, maksimal 20.

| ID Tes | Deskripsi Batas | Data Uji (Panjang Karakter) | Input yang Digunakan | Hasil yang Diharapkan |
| :--- | :--- | :--- | :--- | :--- |
| **BVA-U1** | Minimum - 1 (Tidak Valid) | 3 | `abc` | Sistem menampilkan error validasi di sisi klien atau server, seperti **"Username must be at least 4 characters."** |
| **BVA-U2** | Minimum (Valid) | 4 | `user` | Sistem menerima input dan melanjutkan proses verifikasi password. |
| **BVA-U3** | Maksimum (Valid) | 20 | (string dengan 20 karakter) | Sistem menerima input dan melanjutkan proses verifikasi password. |
| **BVA-U4** | Maksimum + 1 (Tidak Valid) | 21 | (string dengan 21 karakter) | Sistem menampilkan error validasi, seperti **"Username cannot exceed 20 characters."** |