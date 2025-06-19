<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CrisisLink - Help Those in Need</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#home">CrisisLink Network</a> <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary text-white px-3 active" href="#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary text-white px-3" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary text-white px-3" href="#gallery">Gallery</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary text-white px-3" href="#services">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary text-white px-3" href="#volunteer">Volunteer</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary text-white px-3" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary text-white px-3" href="auth.php">Join Us</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>


    <section id="home" class="hero" style="height: 100vh; min-height: 600px; position: relative; overflow: hidden; color: white;">
        <div class="hero-slides">
            <div class="hero-slide active" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('assets/images/hero-bg.jpg'); background-size: cover; background-position: center; transition: opacity 1s ease-in-out; opacity: 1;"></div>
            <div class="hero-slide" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('assets/images/hero-bg1.jpg'); background-size: cover; background-position: center; transition: opacity 1s ease-in-out; opacity: 0;"></div>
            <div class="hero-slide" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('assets/images/hero-bg2.jpg'); background-size: cover; background-position: center; transition: opacity 1s ease-in-out; opacity: 0;"></div>
            <div class="hero-slide" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('assets/images/hero-bg4.jpg'); background-size: cover; background-position: center; transition: opacity 1s ease-in-out; opacity: 0;"></div>
        </div>

        <div class="hero-content" style="position: relative; z-index: 2; padding-top:30px; padding-bottom: 30px; top: 192px;">
    <div class="container text-center">
        <h1 class="display-3 mb-4" data-aos="fade-up">Bridge the Gap, Build a Future with CrisisLink</h1>
        <p class="lead mb-5" data-aos="fade-up" data-aos-delay="200">Empowering communities through sustainable development and immediate relief.</p>
        <a href="#about" class="btn btn-lg btn-light hero-btn" data-aos="fade-up" data-aos-delay="300">Learn More <i class="fas fa-arrow-down ms-2"></i></a>
    </div>
</div>

        <script>
            // Image slider functionality
            document.addEventListener('DOMContentLoaded', function() {
                const slides = document.querySelectorAll('.hero-slide');
                let currentSlide = 0;

                function nextSlide() {
                    slides[currentSlide].style.opacity = 0;
                    slides[currentSlide].classList.remove('active');
                    currentSlide = (currentSlide + 1) % slides.length;

                    slides[currentSlide].style.opacity = 1;
                    slides[currentSlide].classList.add('active');
                }
                
                setInterval(nextSlide, 3000);
            });
        </script>
    </section>
    <section id="about" class="about section-padding">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <img src="assets/images/about.jpg" alt="About Us"
                        class="img-fluid rounded shadow-lg">
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <h2 class="mb-4 display-5 fw-bold">Building a Better Tomorrow</h2>
                    <div class="mb-4">
                        <h4 class="text-primary mb-3">Our Mission</h4>
                        <p>At CrisisLink, our mission is to deliver immediate relief, empower through education, facilitate social development, and ensure transparent donation impact. We foster resilience and self-sufficiency in vulnerable communities worldwide by:</p>
                        <ul class="mission-list list-unstyled">
                            <li><i class="fas fa-check-circle text-primary me-2"></i>Providing critical humanitarian relief and aid</li>
                            <li><i class="fas fa-check-circle text-primary me-2"></i>Empowering individuals through quality education programs</li>
                            <li><i class="fas fa-check-circle text-primary me-2"></i>Driving sustainable social and economic development initiatives</li>
                            <li><i class="fas fa-check-circle text-primary me-2"></i>Facilitating impactful donations to reach those in need</li>
                        </ul>
                    </div>
                    <div class="mb-4">
                        <h4 class="text-primary mb-3">Our Vision</h4>
                        <p>We envision a world where every individual has access to essential resources, transformative education, and opportunities for dignified growth. Our aspirations include:</p>
                        <ul class="vision-list list-unstyled">
                            <li><i class="fas fa-lightbulb text-primary me-2"></i>Creating self-reliant communities through holistic social development</li>
                            <li><i class="fas fa-lightbulb text-primary me-2"></i>Advancing global access to empowering educational resources</li>
                            <li><i class="fas fa-lightbulb text-primary me-2"></i>Establishing robust systems for effective relief and transparent donations</li>
                            <li><i class="fas fa-lightbulb text-primary me-2"></i>Fostering collaborative global partnerships for lasting positive change</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="gallery" class="gallery py-5 section-padding">
        <div class="container">
            <h2 class="text-center mb-5 display-5 fw-bold" data-aos="fade-up">Our Gallery</h2>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6" data-aos="fade-up">
                    <div class="gallery-item shadow-sm">
                        <img src="assets/images/gallery1.jpg" class="img-fluid rounded"
                            alt="Gallery 1">
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="gallery-item shadow-sm">
                        <img src="assets/images/gallery2.jpg" class="img-fluid rounded"
                            alt="Gallery 2">
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="gallery-item shadow-sm">
                        <img src="assets/images/gallery3.jpg" class="img-fluid rounded"
                            alt="Gallery 3">
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="gallery-item shadow-sm">
                        <img src="assets/images/gallery4.jpg" class="img-fluid rounded"
                            alt="Gallery 4">
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
                    <div class="gallery-item shadow-sm">
                        <img src="assets/images/gallery5.jpg" class="img-fluid rounded"
                            alt="Gallery 5">
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="500">
                    <div class="gallery-item shadow-sm">
                        <img src="assets/images/gallery6.jpg" class="img-fluid rounded"
                            alt="Gallery 6">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="services" class="services py-5 section-padding" style="background-color: #f8f9fa;">
        <div class="container text-center">
            <h2 class="mb-5 display-5 fw-bold" data-aos="fade-up">What CrisisLink Does</h2>
            <div class="row">
                <div class="col-md-4 mb-4" data-aos="fade-up">
                    <div class="service-card p-4 text-center shadow-sm rounded-3 hover-effect">
                        <i class="fas fa-truck-loading fa-3x mb-3 text-primary"></i>
                        <h4>Disaster Relief</h4>
                        <p>Providing immediate aid and relief in times of natural disasters like floods, earthquakes,
                            and cyclones.</p>
                    </div>
                </div>

                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="service-card p-4 text-center shadow-sm rounded-3 hover-effect">
                        <i class="fas fa-book fa-3x mb-3 text-primary"></i>
                        <h4>Education Support</h4>
                        <p>Empowering the youth through education initiatives, offering scholarships, and building
                            schools in underserved areas.</p>
                    </div>
                </div>

                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="service-card p-4 text-center shadow-sm rounded-3 hover-effect">
                        <i class="fas fa-stethoscope fa-3x mb-3 text-primary"></i>
                        <h4>Healthcare</h4>
                        <p>Delivering medical services, free health camps, and healthcare resources to rural and
                            marginalized communities.</p>
                    </div>
                </div>

                <div class="col-md-4 mb-4" data-aos="fade-up">
                    <div class="service-card p-4 text-center shadow-sm rounded-3 hover-effect">
                        <i class="fas fa-leaf fa-3x mb-3 text-primary"></i>
                        <h4>Environmental Conservation</h4>
                        <p>Promoting sustainable practices, organizing clean-up drives, and planting trees to protect
                            our environment.</p>
                    </div>
                </div>

                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="service-card p-4 text-center shadow-sm rounded-3 hover-effect">
                        <i class="fas fa-users fa-3x mb-3 text-primary"></i>
                        <h4>Community Development</h4>
                        <p>Empowering local communities with resources, workshops, and support for self-sustainability
                            and growth.</p>
                    </div>
                </div>

                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="service-card p-4 text-center shadow-sm rounded-3 hover-effect">
                        <i class="fas fa-ambulance fa-3x mb-3 text-primary"></i>
                        <h4>Emergency Response</h4>
                        <p>Providing quick emergency response, including medical assistance, food supplies, and shelter
                            during crises.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="volunteer" class="volunteer section-padding">
        <div class="container">
            <div class="row g-0 align-items-center rounded-3 overflow-hidden shadow-lg">
                <div class="col-lg-6 col-md-12 left-side">
                    <img src="assets/images/volunteer.jpg" alt="Volunteer Image"
                        class="img-fluid">
                </div>

                <div class="col-lg-6 col-md-12 right-side p-5">
                    <h2 class="mb-4 display-5 fw-bold">Become a Volunteer</h2>
                    <p class="lead mb-4">Join our community of passionate volunteers and make a tangible difference in people's lives today. Your help can change the world!</p>
                    <a href="auth.php" class="btn btn-primary btn-lg">Join Us Today <i class="fas fa-hands-helping ms-2"></i></a>
                </div>
            </div>
        </div>
    </section>



    <section id="contact" class="contact py-5 section-padding">
        <div class="container">
            <h2 class="text-center mb-5 display-5 fw-bold" data-aos="fade-up">Get in Touch</h2>
            <div class="row">
                <div class="col-lg-6 mb-4" data-aos="fade-right">
                    <div class="embed-responsive embed-responsive-16by9 rounded-3 shadow-sm">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d29207.964695391867!2d90.45287303068847!3d23.783171483670976!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3755c7c964a9aead%3A0x5b5f73ab7d380383!2sWonderland%20Amusement%20Park!5e0!3m2!1sen!2sbd!4v1739354314836!5m2!1sen!2sbd"
                            width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>

                <div class="col-lg-6" data-aos="fade-left">
                    <form id="contact-form" class="p-4 rounded-3 shadow-sm bg-light">
                        <div class="mb-3">
                            <input type="text" class="form-control" placeholder="Your Name" required>
                        </div>
                        <div class="mb-3">
                            <input type="email" class="form-control" placeholder="Your Email" required>
                        </div>
                        <div class="mb-3">
                            <textarea class="form-control" rows="5" placeholder="Your Message" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Send Message <i class="fas fa-paper-plane ms-2"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section id="faq" class="faq py-5 section-padding" style="background-color: #f8f9fa;">
        <div class="container text-center">
            <h2 class="mb-5 display-5 fw-bold" data-aos="fade-up">Frequently Asked Questions</h2>
            <div class="accordion" id="faqAccordion">
                <div class="accordion-item shadow-sm mb-3">
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

                <div class="accordion-item shadow-sm mb-3">
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

                <div class="accordion-item shadow-sm mb-3">
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

                <div class="accordion-item shadow-sm mb-3">
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

                <div class="accordion-item shadow-sm mb-3">
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

                <div class="accordion-item shadow-sm mb-3">
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

                <div class="accordion-item shadow-sm mb-3">
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

                <div class="accordion-item shadow-sm mb-3">
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

  <section class="newsletter py-5 bg-white">
        <div class="container">
            <div class="row justify-content-center text-center">
                <div class="col-lg-8">
                    <h3 class="mb-4 display-6 fw-bold text-dark" data-aos="fade-up">Subscribe to Our Newsletter</h3>
                    <p class="lead mb-4 text-dark" data-aos="fade-up" data-aos-delay="100">
                        Stay updated with our latest news, initiatives, and ways you can make a difference. Join our mailing list for important updates.
                    </p>
                    <form id="newsletter-form" class="d-flex justify-content-center" data-aos="fade-up" data-aos-delay="200">
                        <div class="input-group">
                            <input type="email" class="form-control form-control-lg rounded-pill text-dark" placeholder="Enter your email" required style="border-color: #ced4da;">
                            <button type="submit" class="btn btn-lg rounded-pill ms-2" style="background-color: #e84545; color: white;">
                                Subscribe <i class="fas fa-envelope-open-text ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-dark text-light py-5" style="position: relative; background: url('assets/images/footer-bg.jpg') center/cover no-repeat; z-index: 1;">
        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(33, 37, 41, 0.85); z-index: -1;"></div>

        <div class="container" style="position: relative; z-index: 2;">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h4 class="text-white mb-3">CrisisLink</h4>
                    <p>Making the world a better place by providing relief and support to those who need it most. Join
                        us in our mission to create positive change in people's lives.</p>
                </div>

                <div class="col-md-4 mb-4">
                    <h5 class="text-white mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="#home" class="text-light text-decoration-none hover-underline"><i class="fas fa-home me-2"></i>Home</a></li>
                        <li><a href="#about" class="text-light text-decoration-none hover-underline"><i class="fas fa-info-circle me-2"></i>About Us</a></li>
                        <li><a href="#gallery" class="text-light text-decoration-none hover-underline"><i class="fas fa-image me-2"></i>Gallery</a></li>
                        <li><a href="#services" class="text-light text-decoration-none hover-underline"><i class="fas fa-hands-helping me-2"></i>Services</a></li>
                        <li><a href="#volunteer" class="text-light text-decoration-none hover-underline"><i class="fas fa-user-friends me-2"></i>Volunteer</a></li>
                        <li><a href="#contact" class="text-light text-decoration-none hover-underline"><i class="fas fa-envelope me-2"></i>Contact</a></li>
                        <li><a href="auth.php" class="text-light text-decoration-none hover-underline"><i class="fas fa-sign-in-alt me-2"></i>Join Us</a></li>
                    </ul>
                </div>

                <div class="col-md-4 mb-4">
                    <h5 class="text-white mb-3">Contact Information</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-map-marker-alt me-2"></i> Satarkul, Uttarbadda, Dhaka Bangladesh</li>
                        <li><i class="fas fa-phone me-2"></i> +880 1744-353696</li>
                        <li><i class="fas fa-envelope me-2"></i> info@crisislink.org</li>
                        <li class="social-icons mt-3">
                            <a href="https://www.facebook.com/YourPage" class="text-light me-3"><i
                                    class="fab fa-facebook fa-2x"></i></a>
                            <a href="https://twitter.com/YourHandle" class="text-light me-3"><i
                                    class="fab fa-twitter fa-2x"></i></a>
                            <a href="https://www.instagram.com/YourHandle" class="text-light me-3"><i
                                    class="fab fa-instagram fa-2x"></i></a>
                            <a href="https://www.linkedin.com/in/YourProfile" class="text-light"><i
                                    class="fab fa-linkedin fa-2x"></i></a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="row mt-4 pt-3 border-top border-secondary">
                <div class="col-md-12 text-center">
                    <p class="mb-0">© 2025 CrisisLink. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
        });

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    </script>

</body>

</html>