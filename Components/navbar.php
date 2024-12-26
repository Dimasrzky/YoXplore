<!-- Components/navbar.php -->
<header>
    <nav class="container">
        <a href="../Client/Home.php"><img class="logo" src="../Image/Yoxplore logo text.png" alt="Yoxplore Logo"></a>
        <div class="nav-links">
            <a href="../Client/Home.php"><img src="../Image/home.png" alt="Home Icon"> Home</a>
            <a href="../Client/Yotrip.php"><img src="../Image/yotrip.png" alt="Trip Icon"> YoTrip</a>
            <a href="../Client/Yoconcert.php"><img src="../Image/yoconcert.png" alt="Concert Icon"> YoConcert</a>
            <a href="../Client/YoTaste.php"><img src="../Image/yotaste.png" alt="Taste Icon"> YoTaste</a>
            <a href="../Client/YoStay.php"><img src="../Image/yostay.png" alt="Stay Icon"> YoStay</a>
        </div>
        <div class="user-profile">
            <span id="usernameDisplay"><?= htmlspecialchars($_SESSION['username'] ?? '') ?></span>
        </div