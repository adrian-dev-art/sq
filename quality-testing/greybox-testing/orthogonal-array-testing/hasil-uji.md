### Analisis Grey-Box Testing (Metode: Orthogonal Array Testing)

Pengujian ini berfokus pada fungsi "Tambah Game ke Folder" di `dashboard.php`.

#### Langkah 1: Identifikasi Parameter (Faktor) dan Levelnya

Kita identifikasi parameter kunci yang memengaruhi hasil dari fungsi ini. Setiap parameter memiliki beberapa kemungkinan kondisi (level).

| Parameter (Faktor) | Level 1 (-) | Level 2 (+) |
| :--- | :--- | :--- |
| **P1: Status Game Lokal** (Apakah game sudah ada di tabel `games`?) | Tidak Ada | Ada |
| **P2: Status Game di Folder** (Apakah game sudah ada di tabel `game_folder_games` untuk folder ini?) | Tidak Ada | Ada |
| **P3: Folder Dipilih** (Apakah pengguna memilih folder dari dropdown?) | Tidak (Kosong) | Ya (Valid) |
| **P4: Status API RAWG** (Apakah panggilan API untuk detail game berhasil?) | Gagal | Sukses |

*Catatan: Parameter P4 (Status API) hanya relevan jika P1 (Status Game Lokal) adalah "Tidak Ada".*

#### Langkah 2: Pembuatan Kasus Uji Menggunakan Orthogonal Array

| ID Tes | **P1: Status Game Lokal** | **P2: Status Game di Folder** | **P3: Folder Dipilih** | **P4: Status API RAWG** | Hasil yang Diharapkan (Output yang Diobservasi) |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **1** | Tidak Ada (-) | Tidak Ada (-) | Ya (+) | Sukses (+) | **Berhasil:** Game diambil dari API, disimpan ke tabel `games`, lalu ditambahkan ke `game_folder_games`. Pesan sukses ditampilkan. |
| **2** | Ada (+) | Ada (+) | Ya (+) | N/A | **Gagal (Duplikat):** Program mendeteksi game sudah ada di folder. Pesan error "Game ... is already in this folder." ditampilkan. |
| **3** | Ada (+) | Tidak Ada (-) | Tidak (-) | N/A | **Gagal (Input Pengguna):** Program mendeteksi folder tidak dipilih. Pesan error "Please select a valid game folder." ditampilkan. |
| **4** | Tidak Ada (-) | Tidak Ada (-) | Ya (+) | Gagal (-) | **Gagal (Sistem Eksternal):** Program tidak menemukan game lokal, mencoba mengambil dari API, tetapi gagal. Pesan error "Error fetching full game details: ..." ditampilkan. |
| **5** | Ada (+) | Tidak Ada (-) | Ya (+) | N/A | **Berhasil:** Program menemukan game di tabel `games` lokal (melewatkan panggilan API) dan langsung menambahkannya ke folder. Pesan sukses ditampilkan. |

#### Langkah 3: Analisis dan Kesimpulan

* **Efisiensi:** Dengan hanya **5 kasus uji**, kita telah mencakup interaksi kritis antara semua parameter. Misalnya, kita telah menguji:
    * Apa yang terjadi jika game tidak ada secara lokal **DAN** API gagal (Tes #4).
    * Apa yang terjadi jika game ada secara lokal **DAN** sudah ada di folder (Tes #2).
    * Apa yang terjadi jika game ada secara lokal **TAPI** pengguna tidak memilih folder (Tes #3).
    * Skenario "happy path" saat game ada lokal (Tes #5) dan saat tidak ada (Tes #1).
* **Temuan:** Berdasarkan kasus uji ini, logika program tampaknya **kuat**. Program memiliki penanganan untuk berbagai skenario kegagalan, termasuk input pengguna yang tidak valid (tidak memilih folder), duplikasi data, dan kegagalan sistem eksternal (API). Alur data untuk setiap skenario (seperti yang diverifikasi pada *desk checking*) mengarahkan ke hasil yang benar dan pesan yang sesuai.

Metode OAT ini membuktikan bahwa mekanisme perlindungan dan alur logika utama berfungsi dengan baik di bawah berbagai kombinasi kondisi input, memberikan kepercayaan tinggi terhadap keandalan fitur ini dengan upaya pengujian yang minimal.