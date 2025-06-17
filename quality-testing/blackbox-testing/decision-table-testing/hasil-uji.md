### Analisis Black-Box Testing (Metode: Decision Table Testing)

Pengujian ini menggunakan tabel keputusan untuk memvalidasi logika bisnis yang kompleks pada fungsionalitas Login.

#### Tabel Keputusan: Proses Login Pengguna

| | **Aturan 1** | **Aturan 2** | **Aturan 3** | **Aturan 4** |
| :--- | :---: | :---: | :---: | :---: |
| **Kondisi (Inputs)** | | | | |
| Username Terdaftar di Sistem | Ya | Ya | Ya | Tidak |
| Password Sesuai dengan Username | Ya | Ya | Tidak | - |
| Akun Sudah Terverifikasi | Ya | Tidak | - | - |
| | | | | |
| **Aksi (Outputs)** | | | | |
| Alihkan ke Halaman Dashboard | ✓ | | | |
| Tampilkan Pesan Error: "Incorrect password." | | | ✓ | |
| Tampilkan Pesan Error: "Username not found." | | | | ✓ |
| Tampilkan Pesan Peringatan: "Please verify your email..." | | ✓ | | |
| Tetap di Halaman Login | | ✓ | ✓ | ✓ |

*Catatan: Tanda `-` berarti kondisi tersebut tidak relevan untuk aturan (kasus uji) tersebut.*

#### Penjelasan Aturan (Kasus Uji)

* **Aturan 1 (Login Sukses):**
    * **Kondisi:** Pengguna memasukkan username yang benar, password yang benar, dan akunnya sudah terverifikasi.
    * **Aksi yang Diharapkan:** Sistem harus mengalihkan pengguna ke halaman Dashboard.

* **Aturan 2 (Akun Belum Terverifikasi):**
    * **Kondisi:** Pengguna memasukkan username dan password yang benar, tetapi akunnya belum diverifikasi.
    * **Aksi yang Diharapkan:** Sistem harus menampilkan pesan peringatan untuk verifikasi email dan tetap berada di halaman Login.

* **Aturan 3 (Password Salah):**
    * **Kondisi:** Pengguna memasukkan username yang benar tetapi password yang salah. Status verifikasi tidak relevan.
    * **Aksi yang Diharapkan:** Sistem harus menampilkan pesan error password salah dan tetap berada di halaman Login.

* **Aturan 4 (Username Tidak Ditemukan):**
    * **Kondisi:** Pengguna memasukkan username yang tidak terdaftar. Kondisi lain menjadi tidak relevan.
    * **Aksi yang Diharapkan:** Sistem harus menampilkan pesan error username tidak ditemukan dan tetap berada di halaman Login.