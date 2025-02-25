<?php
include 'dbconfig.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $category = $_POST['category'];
    $bio = $_POST['bio'];
    // $terms_agreed = $_POST['terms_agreed'];

    // Insert into seller_registration table
    $sql = "INSERT INTO seller_registration (full_name, email, phone_number, art_category, bio, terms_agreed, status) 
            VALUES ('$name', '$email', '$phone', '$category', '$bio', 'terms_agreed', 'pending')";

    // if (mysqli_query($conn, $sql)) {
    //     $user_id = mysqli_insert_id($conn); // Get last inserted user ID

        // Insert into seller_register

        if (mysqli_query($conn, $sql)) {
            echo "<script>alert('Registration successful! Waiting for admin approval.'); window.location.href='login.html';</script>";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    } else {
        echo "Error: " . mysqli_error($conn);
    }

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Become a Seller - Pure Art Gallery</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet"> 

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">

    <style>
        .seller-form {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 40px;
            margin: 40px 0;
        }

        .form-control {
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
        }

        .benefits-card {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }

        .benefits-card:hover {
            transform: translateY(-5px);
        }

        .benefits-icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 15px;
        }

        .page-header {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('img/art-gallery-bg.jpg');
            background-size: cover;
            background-position: center;
            padding: 120px 0;
            margin-bottom: 40px;
        }
    </style>
</head>

<body>
    <!-- Spinner Start -->
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-grow text-primary" role="status"></div>
    </div>
    <!-- Spinner End -->

    <!-- Page Header Start -->
    <div class="page-header text-center">
        <div class="container">
            <h1 class="display-4 text-white animated slideInDown mb-4">Become a Seller</h1>
            <p class="text-white fs-5 mb-4 animated slideInDown">Join our community of artists and reach art enthusiasts worldwide</p>
        </div>
    </div>
    <!-- Page Header End -->

    <div class="container">
        <div class="row">
            <!-- Benefits Section -->
            <div class="col-lg-4 wow fadeInUp" data-wow-delay="0.1s">
                <h3 class="mb-4">Why Sell with Us?</h3>
                
                <div class="benefits-card">
                    <div class="benefits-icon">
                        <i class="fas fa-globe"></i>
                    </div>
                    <h5>Global Reach</h5>
                    <p>Connect with art collectors and enthusiasts from around the world.</p>
                </div>

                <div class="benefits-card">
                    <div class="benefits-icon">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <h5>Competitive Commission</h5>
                    <p>Enjoy favorable commission rates and maximize your earnings.</p>
                </div>

                <div class="benefits-card">
                    <div class="benefits-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <h5>Seller Tools</h5>
                    <p>Access professional tools to manage your artwork and sales.</p>
                </div>
            </div>

            <!-- Registration Form -->
            <div class="col-lg-8 wow fadeInUp" data-wow-delay="0.3s">
                <div class="seller-form">
                    <h3 class="mb-4">Register as a Seller</h3>
                    <form method="POST" action="" enctype="multipart/form-data" id="sellerForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Full Name *</label>
                                    <input type="text" class="form-control" name="name" required id="name">
                                    <small id="nameError" class="text-danger" style="display:none;">Please enter a valid name (letters and spaces only).</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email Address *</label>
                                    <input type="email" class="form-control" name="email" required id="email">
                                    <small id="emailError" class="text-danger" style="display:none;">Please enter a valid email.</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Phone Number *</label>
                                    <input type="tel" class="form-control" name="phone" required id="phone">
                                    <small id="phoneError" class="text-danger" style="display:none;">Please enter a valid phone number.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Art Category *</label>
                                    <select class="form-control" name="category" required id="category">
                                        <option value="">Select Category</option>
                                        <option value="paintings">Paintings</option>
                                        <option value="sculptures">Sculptures</option>
                                        <option value="digital">Digital Art</option>
                                        <option value="photography">Photography</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Brief Bio *</label>
                            <textarea class="form-control" name="bio" rows="4" required placeholder="Tell us about yourself and your art..." id="bio"></textarea>
                            <small id="bioError" class="text-danger" style="display:none;">Please provide a brief bio.</small>
                        </div>

                        <div class="form-check mb-4">
                            <input type="checkbox" class="form-check-input" id="terms" required>
                            <label class="form-check-label" for="terms">I agree to the terms and conditions *</label>
                        </div>

                        <button type="submit" class="btn btn-primary py-3 px-5">Submit Application</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/counterup/counterup.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>

    <script>
        document.getElementById('sellerForm').addEventListener('input', function() {
            // Validate Full Name
            const name = document.getElementById('name');
            const nameError = document.getElementById('nameError');
            const namePattern = /^[A-Za-z\s]+$/; // Only letters and whitespace
            if (!namePattern.test(name.value.trim())) {
                nameError.style.display = 'block';
            } else {
                nameError.style.display = 'none';
            }

            // Validate Email
            const email = document.getElementById('email');
            const emailError = document.getElementById('emailError');
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email.value)) {
                emailError.style.display = 'block';
            } else {
                emailError.style.display = 'none';
            }

            // Validate Phone Number
            const phone = document.getElementById('phone');
            const phoneError = document.getElementById('phoneError');
            const phonePattern = /^[0-9]{10}$/; // Adjust pattern as needed
            if (!phonePattern.test(phone.value)) {
                phoneError.style.display = 'block';
            } else {
                phoneError.style.display = 'none';
            }

            // Validate Bio
            const bio = document.getElementById('bio');
            const bioError = document.getElementById('bioError');
            if (bio.value.trim() === '') {
                bioError.style.display = 'block';
            } else {
                bioError.style.display = 'none';
            }
        });
    </script>
</body>
</html>