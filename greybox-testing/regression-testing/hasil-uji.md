### Analisis Grey-Box Testing (Metode: Regression Testing)

Pengujian ini bertujuan untuk memastikan bahwa perubahan yang dilakukan pada kode tidak menimbulkan efek samping negatif (regresi) pada fungsionalitas yang sudah ada.

#### Perubahan yang Dilakukan (Pemicu Regresi)

Berdasarkan temuan pada *Data Flow Testing*, pengembang telah melakukan **optimalisasi performa** pada `dashboard.php`. Kueri N+1 yang tidak efisien untuk menghitung jumlah game di setiap folder telah diganti dengan satu kueri `JOIN` yang efisien.

**Kode Lama (Tidak Efisien):**
```php
// Di dalam sebuah loop untuk setiap folder
$stmt_count = $conn_temp->prepare("SELECT COUNT(*) FROM game_folder_games WHERE folder_id = ?");
// ...
```

**Kode Baru (Efisien):**
```sql
// Satu kueri untuk mengambil semua folder beserta jumlah gamenya
SELECT f.id, f.folder_name, COUNT(fg.game_id) as game_count
FROM game_folders f
LEFT JOIN game_folder_games fg ON f.id = fg.folder_id
WHERE f.user_id = ?
GROUP BY f.id
...
```

#### Rencana Uji Regresi (Regression Test Suite)

Berikut adalah serangkaian kasus uji yang harus dijalankan untuk memastikan perubahan di atas tidak merusak fitur-fitur inti aplikasi.

| ID Tes | Area Fungsional | Deskripsi Kasus Uji | Hasil yang Diharapkan |
| :--- | :--- | :--- | :--- |
| **RT-01** | Otentikasi Pengguna | 1. Logout dari sistem.<br>2. Login kembali dengan kredensial yang valid.<br>3. Pastikan halaman dashboard ditampilkan. | Proses login dan logout berjalan lancar tanpa error. Sesi pengguna dikelola dengan benar. |
| **RT-02** | **Tampilan Folder (Fungsi yang Diubah)** | 1. Buka halaman dashboard.<br>2. Amati daftar "Your Game Folders".<br>3. Verifikasi bahwa nama folder dan **jumlah game** di sebelahnya (misal: "(5 games)") ditampilkan dengan **akurat** sesuai data di database. | Daftar folder berhasil ditampilkan. Jumlah game yang ditampilkan oleh kueri baru yang efisien **sama persis** dengan jumlah yang seharusnya, membuktikan logika kueri baru benar. |
| **RT-03** | Manajemen Folder | 1. Buat sebuah folder game baru dengan nama yang unik.<br>2. Pastikan folder tersebut muncul di daftar dengan hitungan "(0 games)". | Fitur pembuatan folder tidak terpengaruh. Folder baru berhasil disimpan dan ditampilkan dengan benar di daftar. |
| **RT-04** | Manajemen Game | 1. Lakukan pencarian game.<br>2. Pilih satu game.<br>3. Tambahkan game tersebut ke salah satu folder yang sudah ada. | Alur penambahan game berfungsi normal. Setelah redirect, hitungan game pada folder yang bersangkutan di daftar harus bertambah satu. |
| **RT-05** | Detail Folder | 1. Klik pada salah satu nama folder di daftar.<br>2. Pastikan halaman `game_folder_details.php` terbuka.<br>3. Pastikan halaman tersebut menampilkan daftar game yang benar untuk folder yang dipilih. | Navigasi ke halaman detail tidak terganggu. Halaman detail menampilkan data yang konsisten dengan apa yang ada di dashboard. |
| **RT-06** | Keamanan & Otorisasi | 1. Secara manual, coba akses URL `game_folder_details.php` dengan `folder_id` milik pengguna lain. | Sistem keamanan tetap utuh. Akses harus ditolak, dan pengguna harus diarahkan kembali ke dashboard dengan pesan error yang sesuai. |
| **RT-07** | Penanganan Pesan | 1. Lakukan aksi yang menghasilkan pesan sukses (misal: buat folder).<br>2. Lakukan aksi yang menghasilkan pesan error (misal: buat folder dengan nama duplikat). | Sistem *flash message* (pesan sekali tampil) melalui `$_SESSION` masih berfungsi dengan baik setelah redirect. |

#### Analisis dan Kesimpulan

* **Tujuan Suite:** Kumpulan tes ini mencakup fungsionalitas inti yang secara langsung atau tidak langsung bergantung pada data folder game.
* **Kriteria Lulus:** Jika **semua** kasus uji di atas (RT-01 hingga RT-07) berhasil, maka dapat disimpulkan bahwa perubahan optimalisasi kueri **tidak menyebabkan regresi**. Perubahan tersebut aman untuk dirilis.
* **Kriteria Gagal:** Jika **salah satu** kasus uji gagal, itu menandakan adanya regresi. Misalnya, jika RT-02 menunjukkan jumlah game yang salah, berarti logika kueri baru salah. Jika RT-04 gagal, berarti perubahan tersebut secara tidak terduga memengaruhi proses penambahan game. Kegagalan apa pun harus diperbaiki sebelum perubahan dirilis.
