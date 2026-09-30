# SISTEM PORTAL PENGURUSAN TUGASAN & PROJEK PERANCANGAN
### JABATAN PERANCANGAN BANDAR DAN DESA NEGERI PERLIS (PLANMalaysia @ Perlis)

[![PHP Version](https://img.shields.io/badge/PHP-8.1%20%7C%208.2%20%7C%208.3-blue.svg)](https://www.php.net)
[![Database](https://img.shields.io/badge/Database-MySQL%208.0%20%2F%20MariaDB-orange.svg)](https://www.mysql.com)
[![Web Server](https://img.shields.io/badge/Web%20Server-IIS%2010.0%20(Windows%20Server%202019)-0078D7.svg)](https://www.iis.net)
[![License](https://img.shields.io/badge/License-Kerajaan%20Negeri%20Perlis-gold.svg)](#)

Aplikasi web rasmi gred produksi untuk pengurusan projek rancangan pemajuan (Rancangan Tempatan, Rancangan Struktur Negeri, Rancangan Kawasan Khas) dan pemantauan sasaran tugasan kakitangan di Jabatan Perancangan Bandar dan Desa Negeri Perlis. Dibina berasaskan amalan terbaik pembangunan perisian moden dan diselaraskan untuk pelaksanaan di atas persekitaran **Microsoft IIS (Internet Information Services) Windows Server 2019**.

---

## 1. STRUKTUR DIREKTORI PROJEK

```text
planmalaysia-perlis-system/
│
├── public/                       # Titik capaian pelayan web (Web Document Root)
│   ├── index.php                 # Titik masuk utama & penghala
│   ├── login.php                 # Pengesahan log masuk & anti brute-force
│   ├── logout.php                # Penamatan sesi selamat
│   ├── dashboard.php             # Papan pemuka eksekutif & ringkasan KPI
│   ├── projects.php              # Pengurusan CRUD projek perancangan
│   ├── tasks.php                 # Pengurusan CRUD tugasan kakitangan
│   ├── users.php                 # Pengurusan kakitangan & kawalan peranan (RBAC)
│   └── reports.php               # Modul laporan kemajuan & paparan cetakan rasmi
│
├── assets/                       # Aset statik aplikasi (CSS, JS, Imej)
│   ├── css/
│   │   └── style.css             # Tema korporat PLANMalaysia & gaya cetakan
│   ├── js/
│   │   └── app.js                # Logik antaramuka, interaksi modal & validasi
│   └── images/
│       ├── logo.svg              # Logo vektor definisi tinggi (HD)
│       └── logo.png              # Logo PNG PLANMalaysia Perlis
│
├── config/                       # Konfigurasi aplikasi & pangkalan data
│   ├── config.php                # Pemalar sistem, zon waktu & tetapan kuki sesi
│   └── database.example.php      # Templat sambungan PDO MySQL yang selamat
│
├── includes/                     # Modul fungsi & templat blok boleh diguna semula
│   ├── auth.php                  # Logik pengesahan, kawalan sesi & perlindungan CSRF
│   ├── functions.php             # Operasi CRUD, sanitasi input & metrik KPI
│   ├── header.php                # Bar navigasi atas rasmi & elemen <head>
│   ├── sidebar.php               # Menu navigasi sisi responsif
│   └── footer.php                # Kaki laman kerajaan, modal info & pemuat skrip
│
├── api/                          # Antara Muka Pengaturcaraan Aplikasi (REST API)
│   ├── get_stats.php             # JSON endpoint metrik papan pemuka
│   ├── projects.php              # JSON endpoint data projek
│   ├── tasks.php                 # JSON endpoint data tugasan
│   └── export_report.php         # Penjana fail muat turun CSV / Excel dengan BOM
│
├── database/                     # Skrip SQL pangkalan data
│   ├── database.sql              # Skema struktur jadual & indeks MySQL InnoDB
│   └── sample_data.sql           # Data ujian/seed (Projek RT/RSN/RKK & pengguna demo)
│
├── web.config                    # Konfigurasi pelayan IIS 10 (Windows Server 2019)
├── .gitignore                    # Senarai fail dikecualikan daripada kawalan versi Git
├── .env.example                  # Templat pembolehubah persekitaran (Environment)
└── README.md                     # Dokumentasi komprehensif pemasangan & penyelenggaraan
```

---

## 2. CIRI-CIRI UTAMA SISTEM

1. **Keselamatan Peringkat Tinggi Sektor Awam:**
   - Sambungan pangkalan data menggunakan **PDO Prepared Statements** bagi membasmi ancaman SQL Injection.
   - Penjanaan dan pengesahan **Token CSRF** pada setiap borang `POST`.
   - Penyulitan kata laluan menggunakan algoritma **Bcrypt** (`PASSWORD_DEFAULT`).
   - Mekanisme **Anti-Brute Force** (sekat automatik 15 minit selepas 5 kali cubaan gagal).
   - Pengurusan kuki sesi selamat (`HttpOnly`, `SameSite=Lax`, `session_regenerate_id`).
   - Perlindungan fail sensitif (`.env`, fail konfigurasi, fail SQL) disekat melalui `web.config` IIS Request Filtering.

2. **Pengurusan CRUD Penuh:**
   - **Projek Perancangan:** Daftar, kemaskini kemajuan (progress bar), anggaran peruntukan (RM), semakan tarikh, dan penugasan pegawai penyelaras.
   - **Tugasan Kerja:** Pengagihan tugas mengikut kategori projek, tahap keutamaan (Tinggi, Sederhana, Rendah), penetapan tarikh akhir, dan butang pantas "Tandakan Selesai".
   - **Kakitangan (RBAC):** Penetapan tiga peringkat akses utama: `Admin` (Pentadbir ICT), `Pengarah` (Akses Eksekutif), dan `Staff` (Pegawai Perancang).

3. **Pelaporan & Analitik:**
   - Papan pemuka visual dengan kad status KPI.
   - Laporan boleh dicetak secara terus (`window.print`) dengan format rasmi kerajaan (termasuk ruang tandatangan pengesahan).
   - Fungsi eksport data penuh ke format **CSV / Excel** yang dilengkapi UTF-8 BOM bagi mengelakkan masalah fon pada Windows.

---

## 3. KEPERLUAN SISTEM

- **Pelayan Web:** Microsoft IIS 10.0 (Windows Server 2019 / Windows Server 2022)
- **Modul IIS Diperlukan:** 
  - CGI / FastCGI Feature
  - URL Rewrite Module 2.1
- **Bahasa Pengaturcaraan:** PHP 8.1 / 8.2 / 8.3 (Non-Thread Safe x64 untuk IIS)
  - Ekstensi PHP diaktifkan: `curl`, `mbstring`, `openssl`, `pdo_mysql`, `fileinfo`
- **Pangkalan Data:** MySQL Server 8.0 atau MariaDB 10.6+
- **Pelayar Web Disokong:** Microsoft Edge, Google Chrome, Mozilla Firefox (Versi moden)

---

## 4. PANDUAN PEMASANGAN PANGKALAN DATA MYSQL

1. Buka konsol MySQL atau perisian pengurusan (cth: MySQL Workbench / phpMyAdmin / HeidiSQL):
   ```bash
   mysql -u root -p
   ```

2. Jalankan skrip pembinaan skema pangkalan data:
   ```sql
   SOURCE C:/inetpub/wwwroot/planmalaysia/database/database.sql;
   ```

3. Masukkan data contoh (seed data):
   ```sql
   SOURCE C:/inetpub/wwwroot/planmalaysia/database/sample_data.sql;
   ```

4. Cipta pengguna khusus MySQL dengan kebenaran terhad (disyorkan untuk keselamatan):
   ```sql
   CREATE USER 'pm_perlis_user'@'localhost' IDENTIFIED BY 'KatalaluanKuatMySQL2024!';
   GRANT ALL PRIVILEGES ON db_planmalaysia_perlis.* TO 'pm_perlis_user'@'localhost';
   FLUSH PRIVILEGES;
   ```

---

## 5. PANDUAN DEPLOYMENT KE WINDOWS SERVER 2019 (IIS)

### Langkah A: Konfigurasi PHP pada Windows Server 2019
1. Muat turun **PHP 8.2 atau 8.3 Non-Thread Safe (NTS) x64** dari laman rasmi `windows.php.net`.
2. Ekstrak ke direktori `C:\PHP` (contoh: `C:\PHP8.2`).
3. Namakan fail `php.ini-production` kepada `php.ini`.
4. Buka `php.ini` dan aktifkan sambungan berikut (buang tanda koma bertitik `;` di hadapan):
   ```ini
   extension_dir = "ext"
   extension=curl
   extension=fileinfo
   extension=mbstring
   extension=openssl
   extension=pdo_mysql
   
   date.timezone = "Asia/Kuala_Lumpur"
   upload_max_filesize = 10M
   post_max_size = 12M
   ```

### Langkah B: Pasang Peranan IIS & FastCGI
1. Buka **Server Manager** di Windows Server 2019.
2. Klik **Add Roles and Features** > Pilih **Web Server (IIS)**.
3. Di bahagian **Role Services**, pastikan tanda semak pada:
   - `CGI` (di bawah Application Development).
   - `Default Document`, `Directory Browsing`, `HTTP Errors`, `Static Content`.
   - `Request Filtering`.
4. Muat turun dan pasang **IIS URL Rewrite Module 2.1** dari Microsoft.

### Langkah C: Konfigurasi FastCGI Handler di IIS Manager
1. Buka **Internet Information Services (IIS) Manager**.
2. Klik pada nama pelayan (Root Server Node) > Buka **Handler Mappings**.
3. Klik **Add Module Mapping...** di panel kanan:
   - **Request path:** `*.php`
   - **Module:** `FastCgiModule`
   - **Executable:** `C:\PHP\php-cgi.exe`
   - **Name:** `PHP_via_FastCGI`
4. Klik **OK** dan pilih **Yes** untuk mencipta FastCGI Application.

### Langkah D: Salin Aplikasi & Tetapkan Hak Akses Folder (Permissions)
1. Letakkan kod aplikasi di dalam direktori IIS:
   `C:\inetpub\wwwroot\planmalaysia`
2. Salin fail `.env.example` kepada `.env` dan kemaskini kata laluan MySQL.
3. Salin fail `config/database.example.php` kepada `config/database.php`.
4. Berikan kebenaran capaian kepada pengguna IIS:
   - Klik kanan folder `C:\inetpub\wwwroot\planmalaysia` > **Properties** > tab **Security**.
   - Klik **Edit** > **Add...** > Masukkan pengguna `IIS_IUSRS` dan `IUSR`.
   - Berikan kebenaran **Read & Execute**, **List folder contents**, dan **Read**.
   - Untuk folder `logs/` dan `uploads/`, berikan kebenaran tambahan **Write** dan **Modify**.

### Langkah E: Cipta Laman Web (Website) di IIS
1. Di IIS Manager, klik kanan **Sites** > **Add Website...**
   - **Site name:** `PLANMalaysia-Perlis`
   - **Physical path:** `C:\inetpub\wwwroot\planmalaysia`
   - **Port:** `80` (atau `443` jika menggunakan Sijil SSL)
2. Akses aplikasi melalui pelayar web:
   `http://localhost/public/` atau `http://localhost/`

---

## 6. MAKLUMAT AKAUN DEMO LOG MASUK

Kata laluan lalai untuk kesemua akaun demo di bawah adalah: **`password`**

| Peranan (Role) | E-mel Rasmi | Kata Laluan | Keterangan Kebenaran |
| :--- | :--- | :--- | :--- |
| **Admin ICT** | `admin@example.com` | `password` | Kuasa penuh pentadbiran, pendaftaran kakitangan, projek & tugasan |
| **Pengarah** | `pengarah@example.com` | `password` | Pemantauan eksekutif, kelulusan projek & semakan laporan |
| **Pegawai Staff** | `staff1@example.com` | `password` | Mengurus tugasan projek, kemaskini status kerja harian |

---

## 7. PANDUAN PENGURUSAN GITHUB & KAWALAN VERSI (GIT)

Ikuti langkah di bawah untuk memulakan repositori Git tempatan dan memuat naiknya ke akaun GitHub organisasi anda:

```bash
# 1. Buka terminal atau Git Bash di punca direktori projek:
cd /path/to/planmalaysia-perlis-system

# 2. Inisialisasi repositori Git baru
git init

# 3. Semak fail yang akan dipantau (memastikan fail .env tidak dimasukkan)
git status

# 4. Tambah kesemua fail projek yang telah disaring oleh .gitignore
git add .

# 5. Cipta komit pertama dengan mesej deskriptif
git commit -m "Inisialisasi Sistem Portal Pengurusan Tugasan PLANMalaysia Perlis v2.5.0"

# 6. Namakan cawangan utama sebagai 'main'
git branch -M main

# 7. Hubungkan ke repositori GitHub anda (Gantikan URL di bawah dengan URL repositori anda)
git remote add origin https://github.com/organisasi-anda/planmalaysia-perlis-system.git

# 8. Tolak kod sumber ke GitHub
git push -u origin main
```

---

## 8. INTEGRASI KESELAMATAN & SOKONGAN

Sebarang pertanyaan teknikal atau laporan kerentanan keselamatan boleh disalurkan kepada:
- **Unit Teknologi Maklumat & Komunikasi (ICT)**
- **Jabatan Perancangan Bandar dan Desa Negeri Perlis**
- Tingkat 2, Bangunan Dato' Mahmud, 01000 Kangar, Perlis Indera Kayangan.
- E-mel: `ict@planmalaysia.perlis.gov.my`
