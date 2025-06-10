| Bagian | Path | Code | Jalur |
|--------|------|------|-------|
| A1 | Username tidak ditemukan | ![A1](./path1.png) | 1. Cek `$result->num_rows > 0`<br>2. Jika `true`, lanjutkan cek password <br>3. Jika `false`, tampilkan error|
| A2 | Username ditemukan, password salah | ![A2](path2.png) | 1. Cek `password_verify($password, $row['password'])`<br>2. Jika `true`, lanjutkan cek verifikasi email <br>3. Jika `false`, tampilkan error |
| A3 | Username & password benar, belum diverifikasi | ![A3](./path3.png) | 1. Cek `$row['is_verified']`<br>2. Jika `false`, tampilkan error |
| A4 | Username & password benar, sudah diverifikasi | ![A4](./path4.png) | 1. Cek `$row['is_verified']`<br>2. Jika `true`, set session dan redirect ke `dashboard.php` |

Cyclomatic Complexity (V(G))<br>
• Jumlah simpul (Node) = 6 (cek username, cek password, cek verifikasi, cek login, cek hasil, error) <br>
• Jumlah edge = 8 (jalur cabang dan arah) <br>
• Jumlah komponen terhubung = 1 <br>
Rumus: V(G) = E - N + 2P <br>
V(G) = 8 - 6 + 2(1) = 4 <br>
Hasil: Ada 4 jalur independen yang perlu diuji (A1, A2, A3, A4).

Kesimpulan:
| Bagian | Path | Jalur Diharapkan |
|--------|------|------|
| A1 | Username not found | tampilkan pesan username tidak ditemukan |
| A2 | Username ditemukan, password salah | tampilkan pesan password salah |
| A3 | Username & password benar, belum diverifikasi | tampilkan pesan akun belum diverifikasi |
| A4 | Username & password benar, sudah diverifikasi | redirect ke `dashboard.php` |