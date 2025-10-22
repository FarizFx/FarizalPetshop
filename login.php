<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login</title>

    <link rel="shortcut icon" href="./assets/compiled/svg/favicon.svg" type="image/x-icon" />
    <link rel="shortcut icon" href="assets/images/profile" type="image/png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="./assets/compiled/css/auth.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="./assets/compiled/css/app-dark.css" />
</head>

<body>
    <?php
    // Pastikan session_start() ada di awal sebelum output apapun
    session_start();

    // Include file koneksi database
    include "./function/connection.php"; //
    include "./function/language.php"; //

    // Jika pengguna sudah login, arahkan ke beranda
    if (isset($_SESSION['nama'])) {
        header('Location: index.php?halaman=beranda');
        exit(); // Penting: Hentikan eksekusi script setelah header
    }

    // Set default theme for logged-in users
    if (isset($_SESSION['nama'])) {
        if (!isset($_SESSION['theme'])) {
            $_SESSION['theme'] = 'light';
        }
    }

    if (isset($_POST['login'])) { //
        $username = htmlspecialchars(trim($_POST['username'])); //
        $password = htmlspecialchars($_POST['password']); //

        // Menggunakan Prepared Statements untuk keamanan
        $stmt = $connection->prepare("SELECT id, nama, username, email, role, foto_profil, password FROM user WHERE username = ?");
        
        if ($stmt === false) {
            error_log("Login prepare statement failed: " . $connection->error);
            echo "
            <script>
            Swal.fire({
                title: '" . __('Failed') . "',
                text: '" . __('System error occurred. Please try again later.') . "',
                icon: 'error',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (popup) => {
                    popup.style.setProperty('background', '#1a1a1a', 'important');
                    popup.style.setProperty('color', '#ffffff', 'important');
                },
                confirmButtonColor: '#dc3545',
            }).then(() => {
                window.location.href = 'login.php';
            })
            </script>
            ";
            exit();
        }

        $stmt->bind_param("s", $username); // 's' menandakan tipe data string

        if (!$stmt->execute()) {
            error_log("Login execute statement failed: " . $stmt->error);
            echo "
            <script>
            Swal.fire({
                title: '" . __('Failed') . "',
                text: '" . __('Error occurred during verification. Please try again later.') . "',
                icon: 'error',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (popup) => {
                    popup.style.setProperty('background', '#1a1a1a', 'important');
                    popup.style.setProperty('color', '#ffffff', 'important');
                },
                confirmButtonColor: '#dc3545',
            }).then(() => {
                window.location.href = 'login.php';
            })
            </script>
            ";
            exit();
        }

        $result = $stmt->get_result(); // Mengambil hasil query

        if ($result->num_rows > 0) { //
            $data = $result->fetch_assoc(); //

            // Verifikasi password yang di-hash
            if (password_verify($password, $data['password'])) { //
                session_regenerate_id(true); // Regenerate session ID on login for security
                $_SESSION['nama'] = $data['nama']; //
                $_SESSION['username'] = $data['username']; //
                $_SESSION['email'] = $data['email'] ?? ''; //
                $_SESSION['role'] = $data['role'] ?? 'user'; //
                $_SESSION['foto_profil'] = $data['foto_profil'] ?? ''; //
                $_SESSION['user_id'] = $data['id']; //
                $_SESSION["timeout"] = time() + (24 * 60 * 60); //
                
                echo "
                <script>
                Swal.fire({
                    title: '" . __('Success') . "',
                    text: '" . __('Login successful!') . "',
                    icon: 'success',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                }).then(() => {
                    window.location.href = 'index.php?halaman=beranda';
                })
                </script>
                ";
                exit();
            } else {
            echo "
            <script>
            Swal.fire({
                title: '" . __('Failed') . "',
                text: '" . __('The username / password you entered is incorrect!') . "',
                icon: 'error',
                showConfirmButton: true,
                timer: 2000,
                timerProgressBar: true,
            }).then(() => {
                window.location.href = 'login.php';
            })
            </script>
            ";
            }
        } else {
            // Username tidak ditemukan
            echo "
            <script>
            Swal.fire({
                title: '" . __('Failed') . "',
                text: '" . __('The username / password you entered is incorrect!') . "',
                icon: 'error',
                showConfirmButton: true,
                timer: 2000,
                timerProgressBar: true,
            }).then(() => {
                window.location.href = 'login.php'; // Arahkan kembali ke login, bukan index.php
            })
            </script>
            ";
        }
        $stmt->close(); // Tutup statement
    }
    ?>
    <script>
    <?php if (isset($_SESSION['theme'])) { ?>
        window.sessionTheme = '<?php echo $_SESSION['theme']; ?>';
    <?php } ?>
    </script>
    <script src="assets/static/js/initTheme.js"></script>
    <div id="auth">
        <div class="row h-100">
            <div class="col-lg-5 col-12">
                <div id="auth-left">
                    <div class="auth-logo">
                        <a href="login.php"><img src="./assets/compiled/svg/logo.svg" alt="Logo" /></a>
                    </div>
                    <h1 class="auth-title"><?= __('Login') ?>.</h1>
                    <p class="auth-subtitle mb-5">
                        <?= __('Login with registered credentials') ?>
                    </p>

                    <form action="" method="post">
                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="text" class="form-control form-control-xl" placeholder="Username" name="username" required />
                            <div class="form-control-icon">
                                <i class="bi bi-person"></i>
                            </div>
                        </div>
                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="password" class="form-control form-control-xl" placeholder="Password" name="password" required />
                            <div class="form-control-icon">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                        </div>
                        <button class="btn btn-primary btn-block btn-lg shadow-lg mt-5" name="login">
                            <?= __('Login') ?>
                        </button>
                    </form>
                </div>
            </div>
            <div class="col-lg-7 d-none d-lg-block">
                <div id="auth-right">
                    <img src="assets/images/fp.png" alt="Logo" style="width: 100%; height: 750px;">
                </div>
                
            </div>
        </div>
    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>

</html>
