<?php
session_start();

// 1. Kunin ang data mula sa session
$errors = [
    'login' => $_SESSION['login_error'] ?? '',
    'signup' => $_SESSION['signup_error'] ?? ''
];
$activeForm = $_SESSION['active_form'] ?? ''; // Gawing empty string ang default

// 2. CRITICAL: I-unset na sila agad dito
// Para sa susunod na refresh, malinis na ulit ang page.
unset($_SESSION['login_error']);
unset($_SESSION['signup_error']);
unset($_SESSION['active_form']);

function showerror($error) {
    return !empty($error) ? "<p class='error-message'>$error</p>" : '';
}

function isActiveForm($formName, $activeForm) {
    // Kung walang activeForm, default natin ang login class sa HTML
    if (empty($activeForm) && $formName === 'login') return 'active';
    return $formName === $activeForm ? 'active' : '';
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PET ADOPTION</title>
    <!--Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Header| Navbar -->
    <header>
        <nav class="navbar section-content">
            <a href="#" class="nav-logo">
                <h2 class="logo-text">🐾PetPoint </h2>
            </a>
            <ul class="nav-menu">
                <button id="menu-close-button" class="fas fa-times"></button>

                <li class="nav-item">
                    <a href="#home" class="nav-link">🐾Home</a>
                </li>
                <li class="nav-item">
                    <a href="#about" class="nav-link">🐾About</a>
                </li>
                <li class="nav-item">
                    <a href="#gallery" class="nav-link">🐾Gallery</a>
                </li>
                <li class="nav-item">
                    <a href="#contact" class="nav-link">Contact</a>
                </li>
                <button class="login-btn">LOGIN</button>
            </ul>
            <button id="menu-open-button" class="fas fa-bars"></button>
        </nav>
    </header>
    <!--Popup From-->
    <div class="blur-bg-overlay"></div>
    <div class="form-popup">
        <span class="close-btn material-symbols-rounded">close</span>
        <div class="form-box login">
            <div class="form-details">
                <h2>Welcome to PetPoint🐾</h2>
                <p>Sign in to manage your adoptions and find a new best friend.
                </p>
            </div>
            <div class="form-content" <?= isActiveForm('login', $activeForm); ?> id="login-form">
                <h2>LOGIN</h2>
                <?=  showError($errors['login']); ?>
                <form action="login_signup.php" method="post">
                    <div class="input-field">
                        <input type="text" name="email" placeholder="Email" required>
                        <label>Email: </label>
                    </div>
                     <div class="input-field">
                        <input type="password" name="password" placeholder="Password" required>
                        <label>Password: </label>
                    </div>
                    <a href="#" class="forget-pass">Forget password?</a>
                    <button type="submit" name="login">Log In</button>
                </form>
                <div class="bottom-link">
                    Don't have an account?
                    <a href="#" id="signup-link">Signup</a>
                </div>
            </div>
        </div>
        <div class="form-box signup">
            <div class="form-details">
                <h2>Create Account</h2>
                <p>Join PetPoint today and start your journey toward finding a loving furry companion.
                </p>
            </div>
            <div class="form-content" <?= isActiveForm('signup', $activeForm); ?> id="signup-form">
                <h2>SIGNUP</h2>
                <?=  showError($errors['signup']); ?>
                <form action="login_signup.php" method="post">
                    <div class="input-field">
                        <input type="text" name="name" placeholder="Name" required>
                        <label>Enter your username: </label>
                    </div>
                    <div class="input-field">
                        <input type="email" name="email" placeholder="Email" required>
                        <label>Enter your email: </label>
                    </div>
                     <div class="input-field">
                        <input type="password" name="password" placeholder="Password" required>
                        <label>Create password: </label>
                    </div>
                    <select name="role" required>
                        <option value=""disabled selected>--Select Role--</option>
                        <option value="USER">USER</option>
                        <option value="ADMIN">ADMIN</option>
                    </select>
                    <div class="policy-text">
                    
                        <input type="checkbox" id="policy">
                        <label for="policy">
                            I agree the
                            <a href="#">Terms & Conditions</a>
                        </label>
                    </div>
                    <button type="submit" name="signup">Sign Up</button>
                </form>
                <div class="bottom-link">
                    Already have an account?
                    <a href="#" id="login-link">Login</a>
                </div>
            </div>
        </div>
    </div>

    <main>
        <!-- HERO/HOME SECTION -->
        <section class="hero-section" id="home">
            <div class="section-content">
                <div class="hero-details">
                    <h2 class="title">Pet Adoption</h2>
                    <h3 class="subtitle">Find your perfect companion and give a pet a loving home.</h3>
                    <p class="descript">Welcome to our 🐾PetPoint paradise, where you can adopt pets you want.</p>
                    <div class="buttons">
                        <a href="adopt.php" class="button adopt-me">Adopt me!</a>
                        <a href="#contact" class="button contact-us">Contact Us</a>
                    </div>
                </div>
                <div class="hero-image-wrapper">
                    <img src="img/doggy.png.png " alt="img" class="hero-image">
                </div>
            </div>
        </section>

        <!-- ABOUT SECTION -->
         <section class="about-section" id="about">
            <div class="section-content">
                <div class="about-image-wrapper">
                    <img src="img/cat2.webp" alt="About" class="about-image">
                </div>
                <div class="about-details">
                    <h2 class="section-title">About Us</h2>
                    <p class="text">PetPoint 🐾 is a pet adoption platform that helps loving animals find their forever homes. Our goal is to connect responsible adopters with pets that need care, love, and a safe environment. Through our website, users can browse available pets and start the adoption process. We believe every pet deserves a second chance and every home deserves the joy a pet can bring.
                    </p>
                    <div class="social-link-list">
                        <a href="#" class="social-link"><i class="fa-brands fa-facebook"></i></a>
                        <a href="#" class="social-link"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fa-brands fa-tiktok"></i></a>
                    </div>
                    </div>
                    </div>
                </div>
            </div>
         </section>

        <!-- GALLERY SECTION -->
         <section class="gallery-section" id="gallery">
            <h2 class="section-title">Gallery</h2>
            <ul class="gallery-list">
                <li class="gallery-item">
                    <img src="img/gallery-1.jpg" alt="Gallery" class="gallery-image">
                </li>
                 <li class="gallery-item">
                    <img src="img/gallery-2.jpg" alt="Gallery" class="gallery-image">
                </li>
                 <li class="gallery-item">
                    <img src="img/gallery-3.jpg" alt="Gallery" class="gallery-image">
                </li>
                 <li class="gallery-item">
                    <img src="img/gallery-4.jpg" alt="Gallery" class="gallery-image">
                </li>
                 <li class="gallery-item">
                    <img src="img/gallery-5.jpg" alt="Gallery" class="gallery-image">
                </li>
                 <li class="gallery-item">
                    <img src="img/gallery-6.jpg" alt="Gallery" class="gallery-image">
                </li>
            </ul>
         </section>
         <!-- CONTACT SECTION -->
         <section class="contact-section" id="contact">
            <div class="section-content">
                <h2 class="section-title">Contact Us</h2>
                <div class="contact-details">
                    <p>Email us about adoption applications, questions, or pet availability.</p>
                    <p><strong>Email:</strong> <a href="mailto:yrpotane1926lag@student.fatima.edu.ph">yrpotane1926lag@student.fatima.edu.ph</a></p>
                    <p><strong>Number:</strong> <a href="tel:09123456789">09123456789</a></p>
                </div>
            </div>
         </section>
         <!-- FOOTER SECTION -->
          <footer class="footer-section">
            <div class="section-content">
                <p class="copyright-text">© 2026 🐾PET POINT </p>
                <div class="social-link-list">
                    <a href="#" class="social-link"><i class="fa-brands fa-facebook"></i></a>
                    <a href="#" class="social-link"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" class="social-link"><i class="fa-brands fa-tiktok"></i></a>
                </div>
                <p class="policy-text">
                    <a href="#" class="policy-link">Privacy policy</a>
                    <span class="seperator">・</span>
                    <a href="#" class="policy-link">Terms of service</a>
                    <span class="seperator">・</span>
                    <a href="#contact" class="policy-link">Contact Us</a>
                </p>
            </div>
          </footer>
    </main>
    <script src="script.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", () => {
        const activeFormFromPHP = "<?php echo $activeForm; ?>";
        
        if (activeFormFromPHP !== "") {
            // Automatic buksan ang popup
            document.body.classList.add("show-mobile-menu"); 
            document.body.classList.add("show-popup");

            // Kung sa signup nagka-error i-switch ang view sa signup form
            if (activeFormFromPHP === "signup") {
                document.querySelector(".form-popup").classList.add("show-signup");
            }
        }
    });
</script>
</body>
</html>
