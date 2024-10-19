<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.html");
    exit();
}
$userName = $_SESSION['user_name'];
?>


<html>
<head>
    <title>Yoxplore - Explore Yogyakarta</title>
    <link rel="stylesheet" href="../styles/Home.css">
</head>
<body>
    <header>
        <nav class="container">
            <img class="logo" src="../img/Yoxplore text.png" alt="Yoxplore Logo">
            <div class="nav-links">
                <a href="#" class="active"><img src="../img/home.png" alt="Home Icon"> Home</a>
                <a href="#"><img src="../img/yotrip.png" alt="Trip Icon"> YoTrip</a>
                <a href="#"><img src="../img/yoconcert.png" alt="Concert Icon"> YoConcert</a>
                <a href="#"><img src="../img/yotaste.png" alt="Taste Icon"> YoTaste</a>
                <a href="#"><img src="../img/yostay.png" alt="Stay Icon"> YoStay</a>
            </div>
            <div class="profile-pic"></div>
        </nav>
    </header>

    <main class="container">
        <section class="hero">
            <p>Hello, <?php echo htmlspecialchars($userName); ?></p>
            <h1>Let's Explore Yogyakarta!</h1>
            <div class="search-box">
                <input type="text" placeholder="Where you wanna go?">
                <button>Search</button>
            </div>
        </section>
    </main>

    <section class="sec">
        <h2>Recommended Places</h2>
        <div class="explore-items">
            <div class="explore-item">
                <img src="https://via.placeholder.com/150" alt="Recommended 1">
                <p>Gudeg Yu Djum</p>
            </div>
            <div class="explore-item">
                <img src="https://via.placeholder.com/150" alt="Recommended 2">
                <p>Bakpia Kurnia Sari</p>
            </div>
            <div class="explore-item">
                <img src="https://via.placeholder.com/150" alt="Recommended 3">
                <p>Candi Borobudur</p>
            </div>
            <div class="explore-item">
                <img src="https://via.placeholder.com/150" alt="Recommended 4">
                <p>Hotel Ambarukmo</p>
            </div>
            <div class="explore-item">
                <img src="https://via.placeholder.com/150" alt="Recommended 5">
                <p>Angkringan Lek Man</p>
            </div>
        </div>
    </section>

    <section class="sec">
        <h2>Popular Places</h2>
        <div class="explore-items">
            <div class="explore-item">
                <img src="https://via.placeholder.com/150" alt="Popular 1">
                <p>Malioboro</p>
            </div>
            <div class="explore-item">
                <img src="https://via.placeholder.com/150" alt="Popular 2">
                <p>The Phoenix Hotel</p>
            </div>
            <div class="explore-item">
                <img src="https://via.placeholder.com/150" alt="Popular 3">
                <p>Candi Prambanan</p>
            </div>
            <div class="explore-item">
                <img src="https://via.placeholder.com/150" alt="Popular 4">
                <p>Hotel Tentrem</p>
            </div>
            <div class="explore-item">
                <img src="https://via.placeholder.com/150" alt="Popular 5">
                <p>Kraton Yogyakarta</p>
            </div>
        </div>
    </section>

    <section class="carousel-container">
        <div class="carousel-slide">
            <div class="carousel-item">
                <img src="https://via.placeholder.com/300x150" alt="Image 1">
            </div>
            <div class="carousel-item">
                <img src="https://via.placeholder.com/300x150" alt="Image 2">
            </div>
            <div class="carousel-item">
                <img src="https://via.placeholder.com/300x150" alt="Image 3">
            </div>
        </div>
        <div class="carousel-indicators">
            <span class="indicator active"></span>
            <span class="indicator"></span>
            <span class="indicator"></span>
        </div>
    </section>       

    <footer>
        <p>&copy; 2024 Yoxplore. All rights reserved.</p>
    </footer>

    <script>
        const slides = document.querySelector('.carousel-slide');
        const indicators = document.querySelectorAll('.indicator');
        let currentIndex = 0;
    
        function updateCarousel() {
            slides.style.transform = `translateX(${-currentIndex * 100}%)`;
            indicators.forEach((indicator, index) => {
                indicator.classList.toggle('active', index === currentIndex);
            });
        }
    
        indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => {
                currentIndex = index;
                updateCarousel();
            });
        });
    
        setInterval(() => {
            currentIndex = (currentIndex + 1) % indicators.length;
            updateCarousel();
        }, 3000);
    </script>    
    
</body>
</html>
