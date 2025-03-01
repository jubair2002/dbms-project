<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CrisisLink - Help Those in Need</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- AOS CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#home">CrisisLink</a> <!-- Logo here -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#gallery">Gallery</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#services">Services</a> <!-- Added Services Link -->
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#volunteer">Volunteer</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary text-white px-3" href="auth.php">Join Us</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>


    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-content">
            <div class="container">
                <h1 class="display-3 mb-4" data-aos="fade-up">Bridge the Gap, Build a Future with CrisisLink</h1>
                <!--                <a href="#volunteer" class="btn btn-primary btn-lg" data-aos="fade-up" data-aos-delay="200">Join Us Today</a>-->
            </div>
        </div>
    </section>
    <!-- About Section -->
    <section id="about" class="about">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <!-- Corrected Path for Images in index.php -->
                    <img src="assets/images/about.jpg" alt="About Us"
                        class="img-fluid rounded">
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <h2 class="mb-4">About Our Organization</h2>
                    <div class="mb-4">
                        <h4 class="text-primary">Our Mission</h4>
                        <p>To empower communities through sustainable development initiatives and create lasting
                            positive change in people's lives. We strive to:</p>
                        <ul class="mission-list">
                            <li>Provide quality education to underprivileged children</li>
                            <li>Support healthcare initiatives in rural areas</li>
                            <li>Create sustainable livelihood opportunities</li>
                            <li>Promote environmental conservation</li>
                        </ul>
                    </div>
                    <div class="mb-4">
                        <h4 class="text-primary">Our Vision</h4>
                        <p>A world where every individual has access to basic necessities, education, and opportunities
                            for growth. We envision:</p>
                        <ul class="vision-list">
                            <li>Equal opportunities for all, regardless of background</li>
                            <li>Self-sustaining communities</li>
                            <li>Global partnership for social change</li>
                            <li>Innovation in charitable initiatives</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <section id="gallery" class="gallery py-5">
        <div class="container">
            <h2 class="text-center mb-5" data-aos="fade-up">Our Gallery</h2>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6" data-aos="fade-up">
                    <div class="gallery-item">
                        <img src="assets/images/gallery1.jpg" class="img-fluid rounded"
                            alt="Gallery 1">
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="gallery-item">
                        <img src="assets/images/gallery2.jpg" class="img-fluid rounded"
                            alt="Gallery 2">
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="gallery-item">
                        <img src="assets/images/gallery3.jpg" class="img-fluid rounded"
                            alt="Gallery 3">
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="gallery-item">
                        <img src="assets/images/gallery4.jpg" class="img-fluid rounded"
                            alt="Gallery 4">
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
                    <div class="gallery-item">
                        <img src="assets/images/gallery5.jpg" class="img-fluid rounded"
                            alt="Gallery 5">
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="500">
                    <div class="gallery-item">
                        <img src="assets/images/gallery6.jpg" class="img-fluid rounded"
                            alt="Gallery 6">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services py-5" style="background-color: #f8f9fa;">
        <div class="container text-center">
            <h2 class="mb-5" data-aos="fade-up">What CrisisLink Do</h2>
            <div class="row">
                <!-- Service 1: Disaster Relief -->
                <div class="col-md-4 mb-4" data-aos="fade-up">
                    <div class="service-card p-4 text-center shadow-sm rounded">
                        <i class="fas fa-truck-loading fa-3x mb-3 text-primary"></i>
                        <h4>Disaster Relief</h4>
                        <p>Providing immediate aid and relief in times of natural disasters like floods, earthquakes,
                            and cyclones.</p>
                    </div>
                </div>

                <!-- Service 2: Education Support -->
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="service-card p-4 text-center shadow-sm rounded">
                        <i class="fas fa-book fa-3x mb-3 text-primary"></i>
                        <h4>Education Support</h4>
                        <p>Empowering the youth through education initiatives, offering scholarships, and building
                            schools in underserved areas.</p>
                    </div>
                </div>

                <!-- Service 3: Healthcare -->
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="service-card p-4 text-center shadow-sm rounded">
                        <i class="fas fa-stethoscope fa-3x mb-3 text-primary"></i>
                        <h4>Healthcare</h4>
                        <p>Delivering medical services, free health camps, and healthcare resources to rural and
                            marginalized communities.</p>
                    </div>
                </div>

                <!-- Service 4: Environmental Conservation -->
                <div class="col-md-4 mb-4" data-aos="fade-up">
                    <div class="service-card p-4 text-center shadow-sm rounded">
                        <i class="fas fa-leaf fa-3x mb-3 text-primary"></i>
                        <h4>Environmental Conservation</h4>
                        <p>Promoting sustainable practices, organizing clean-up drives, and planting trees to protect
                            our environment.</p>
                    </div>
                </div>

                <!-- Service 5: Community Development -->
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="service-card p-4 text-center shadow-sm rounded">
                        <i class="fas fa-users fa-3x mb-3 text-primary"></i>
                        <h4>Community Development</h4>
                        <p>Empowering local communities with resources, workshops, and support for self-sustainability
                            and growth.</p>
                    </div>
                </div>

                <!-- Service 6: Emergency Response -->
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="service-card p-4 text-center shadow-sm rounded">
                        <i class="fas fa-ambulance fa-3x mb-3 text-primary"></i>
                        <h4>Emergency Response</h4>
                        <p>Providing quick emergency response, including medical assistance, food supplies, and shelter
                            during crises.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Volunteer Section -->
    <section id="volunteer" class="volunteer">
        <div class="container">
            <div class="row g-0 align-items-center">
                <!-- Left Side: Image -->
                <div class="col-lg-6 col-md-12 left-side">
                    <img src="https://images.unsplash.com/photo-1607227063002-677dc5fdf96f" alt="Volunteer Image"
                        class="img-fluid">
                </div>

                <!-- Right Side: Text and Button -->
                <div class="col-lg-6 col-md-12 right-side">
                    <h2 class="mb-4">Become a Volunteer</h2>
                    <p class="lead mb-4">Join our community of volunteers and make a difference in people's lives.</p>
                    <a href="auth.php" class="btn btn-primary btn-lg">Join Us Today</a>
                </div>
            </div>
        </div>
    </section>



    <!-- Contact Section with Map on Left and Form on Right -->
    <section id="contact" class="contact py-5">
        <div class="container">
            <h2 class="text-center mb-5" data-aos="fade-up">Contact Us</h2>
            <div class="row">
                <!-- Left Side: Google Map -->
                <div class="col-lg-6 mb-4" data-aos="fade-right">
                    <div class="embed-responsive embed-responsive-16by9">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d29207.964695391867!2d90.45287303068847!3d23.783171483670976!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3755c7c964a9aead%3A0x5b5f73ab7d380383!2sWonderland%20Amusement%20Park!5e0!3m2!1sen!2sbd!4v1739354314836!5m2!1sen!2sbd"
                            width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>

                <!-- Right Side: Contact Form -->
                <div class="col-lg-6" data-aos="fade-left">
                    <form id="contact-form">
                        <div class="mb-3">
                            <input type="text" class="form-control" placeholder="Your Name" required>
                        </div>
                        <div class="mb-3">
                            <input type="email" class="form-control" placeholder="Your Email" required>
                        </div>
                        <div class="mb-3">
                            <textarea class="form-control" rows="5" placeholder="Your Message" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary"> Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="faq py-5" style="background-color: #f8f9fa;">
        <div class="container text-center">
            <h2 class="mb-5" data-aos="fade-up">Frequently Asked Questions</h2>
            <div class="accordion" id="faqAccordion">
                <!-- Question 1 -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingOne">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            What is CrisisLink?
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne"
                        data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            CrisisLink is a non-profit organization focused on providing relief and support to
                            communities affected by natural disasters and other crises.
                        </div>
                    </div>
                </div>

                <!-- Question 2 -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingTwo">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            How can I become a volunteer?
                        </button>
                    </h2>
                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo"
                        data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            You can become a volunteer by signing up on our website. Once registered, we will get in
                            touch with you about upcoming volunteer opportunities.
                        </div>
                    </div>
                </div>

                <!-- Question 3 -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingThree">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                            How can I donate to CrisisLink?
                        </button>
                    </h2>
                    <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree"
                        data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            You can donate via our website. We accept donations as well as donations of goods
                            and services.
                        </div>
                    </div>
                </div>

                <!-- Question 4 -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingFour">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                            What areas does CrisisLink serve?
                        </button>
                    </h2>
                    <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour"
                        data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            CrisisLink provides aid in areas affected by natural disasters, including remote and rural
                            areas that need urgent help.
                        </div>
                    </div>
                </div>

                <!-- Question 5 -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingFive">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                            How can I stay updated on your latest projects and initiatives?
                        </button>
                    </h2>
                    <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive"
                        data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            You can subscribe to our newsletter to receive updates on our projects, events, and how you
                            can get involved.
                        </div>
                    </div>
                </div>

                <!-- Question 6 -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingSix">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                            Can I volunteer remotely?
                        </button>
                    </h2>
                    <div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingSix"
                        data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Yes, we offer remote volunteer opportunities in areas such as social media management,
                            fundraising, and virtual support.
                        </div>
                    </div>
                </div>

                <!-- Question 7 -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingSeven">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseSeven" aria-expanded="false" aria-controls="collapseSeven">
                            Where does my donation go?
                        </button>
                    </h2>
                    <div id="collapseSeven" class="accordion-collapse collapse" aria-labelledby="headingSeven"
                        data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Your donations go directly towards funding crisis relief operations, including food, medical
                            aid, shelter, and transportation.
                        </div>
                    </div>
                </div>

                <!-- Question 8 -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingEight">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseEight" aria-expanded="false" aria-controls="collapseEight">
                            How can I become a partner organization?
                        </button>
                    </h2>
                    <div id="collapseEight" class="accordion-collapse collapse" aria-labelledby="headingEight"
                        data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            If you are a non-profit, government organization, or company, you can reach out to us to
                            discuss partnership opportunities to collaborate on crisis response efforts.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>



    <!-- Newsletter Section -->
    <section class="newsletter py-5" style="background-color: #f7f7f7;">
        <div class="container">
            <div class="row justify-content-center text-center">
                <div class="col-lg-8">
                    <h3 class="mb-4" data-aos="fade-up">Subscribe to Our Newsletter</h3>
                    <p class="lead mb-4" data-aos="fade-up" data-aos-delay="100">
                        Stay updated with our latest news, initiatives, and ways you can make a difference.
                    </p>
                    <form id="newsletter-form" class="d-flex justify-content-center" data-aos="fade-up"
                        data-aos-delay="200">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Enter your email" required>
                            <button type="submit" class="btn btn-primary">Subscribe</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-light py-5">
        <div class="container">
            <div class="row">
                <!-- Column 1: Logo & Description -->
                <div class="col-md-4">
                    <h4 class="text-white mb-3">CrisisLink</h4>
                    <p>Making the world a better place by providing relief and support to those who need it most. Join
                        us in our mission to create positive change in people's lives.</p>
                </div>

                <!-- Column 2: Quick Links -->
                <div class="col-md-4">
                    <h5 class="text-white mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="#home" class="text-light">Home</a></li>
                        <li><a href="#about" class="text-light">About Us</a></li>
                        <li><a href="#gallery" class="text-light">Gallery</a></li>
                        <li><a href="#volunteer" class="text-light">Volunteer</a></li>
                        <li><a href="#contact" class="text-light">Contact</a></li>
                        <li><a href="auth.php" class="text-light">Join Us</a></li>
                    </ul>
                </div>

                <!-- Column 3: Contact Info -->
                <div class="col-md-4">
                    <h5 class="text-white mb-3">Contact Information</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-map-marker-alt"></i> Satarkul, Uttarbadda, Dhaka Bangladesh</li>
                        <li><i class="fas fa-phone"></i> +1 234 567 890</li>
                        <li><i class="fas fa-envelope"></i> info@crisislink.org</li>
                        <li>
                            <a href="https://www.facebook.com/YourPage" class="text-light me-3"><i
                                    class="fab fa-facebook"></i></a>
                            <a href="https://twitter.com/YourHandle" class="text-light me-3"><i
                                    class="fab fa-twitter"></i></a>
                            <a href="https://www.instagram.com/YourHandle" class="text-light me-3"><i
                                    class="fab fa-instagram"></i></a>
                            <a href="https://www.linkedin.com/in/YourProfile" class="text-light"><i
                                    class="fab fa-linkedin"></i></a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Section: Copyright & Newsletter Subscription -->
            <div class="row mt-4">
                <!-- Column 1: Copyright -->
                <div class="col-md-6">
                    <p>&copy; 2025 CrisisLink. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="assets/js/main.js"></script>

</body>

</html>