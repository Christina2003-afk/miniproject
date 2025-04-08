<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Your Artwork for Auction</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #e67e22;
            --accent-color: #3498db;
            --light-bg: #f9f9f9;
            --white: #ffffff;
            --text-dark: #2c3e50;
            --text-light: #7f8c8d;
            --border-color: #ecf0f1;
            --success-color: #2ecc71;
            --error-color: #e74c3c;
            --shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: var(--text-dark);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }
        
        .container {
            width: 100%;
            max-width: 800px;
            background: var(--white);
            border-radius: 15px;
            box-shadow: var(--shadow);
            overflow: hidden;
            position: relative;
        }
        
        .form-header {
            background: rgb(159, 108, 45);
            padding: 30px;
            color: var(--white);
            text-align: center;
            position: relative;
        }
        
        .form-header h2 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .form-header p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .form-icon {
            position: absolute;
            top: -30px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 60px;
            background: var(--secondary-color);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        
        .form-icon i {
            font-size: 30px;
            color: var(--white);
        }
        
        .form-body {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-dark);
            font-size: 14px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            color: var(--text-dark);
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--accent-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .file-upload {
            position: relative;
            display: block;
            width: 100%;
        }
        
        .file-upload-label {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
            background-color: #f1f5f9;
            border: 2px dashed var(--accent-color);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }
        
        .file-upload-label:hover {
            background-color: #e0f0ff;
        }
        
        .file-upload-label i {
            font-size: 24px;
            margin-right: 10px;
            color: var(--accent-color);
        }
        
        .file-upload-info {
            font-size: 13px;
            color: var(--text-light);
            margin-top: 5px;
            text-align: center;
        }
        
        .btn-submit {
            display: block;
            width: 100%;
            padding: 12px 0;
            background: linear-gradient(to right, var(--secondary-color), #f39c12);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 10px rgba(243, 156, 18, 0.3);
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(243, 156, 18, 0.4);
        }
        
        .form-footer {
            text-align: center;
            padding: 15px 30px 30px;
            color: var(--text-light);
            font-size: 14px;
        }
        
        .form-step {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .step {
            flex: 1;
            text-align: center;
            position: relative;
        }
        
        .step:not(:last-child):after {
            content: "";
            position: absolute;
            top: 15px;
            right: -50%;
            width: 100%;
            height: 2px;
            background-color: var(--border-color);
            z-index: 0;
        }
        
        .step-circle {
            width: 30px;
            height: 30px;
            background-color: var(--white);
            border: 2px solid var(--border-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            position: relative;
            z-index: 1;
        }
        
        .step.active .step-circle {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
            color: var(--white);
        }
        
        .step-title {
            font-size: 13px;
            color: var(--text-light);
        }
        
        .step.active .step-title {
            color: var(--accent-color);
            font-weight: 500;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .form-col {
            flex: 1;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 10;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.6);
            animation: fadeIn 0.3s;
        }
        
        .modal-content {
            background-color: var(--white);
            margin: 15% auto;
            padding: 30px;
            border-radius: 15px;
            max-width: 500px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            text-align: center;
            position: relative;
            animation: slideIn 0.4s;
        }
        
        .success-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background-color: var(--success-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .success-icon i {
            font-size: 40px;
            color: var(--white);
        }
        
        @keyframes fadeIn {
            from {opacity: 0;}
            to {opacity: 1;}
        }
        
        @keyframes slideIn {
            from {transform: translateY(-50px); opacity: 0;}
            to {transform: translateY(0); opacity: 1;}
        }
        
        /* Hide default file input */
        input[type="file"] {
            position: absolute;
            left: -9999px;
        }
        
        .selected-file {
            margin-top: 10px;
            padding: 8px 15px;
            background: #e0f0ff;
            border-radius: 5px;
            display: none;
            font-size: 13px;
        }
        
        #fileNameDisplay {
            margin-right: 10px;
        }
        
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .form-header h2 {
                font-size: 24px;
            }
            
            .container {
                margin: 20px 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-header">
            <div class="form-icon">
                <i class="fas fa-palette"></i>
            </div>
            <h2>Submit Your Artwork for Auction</h2>
            <p>Complete the form below to list your art piece</p>
        </div>
        
        <div class="form-body">
            <div class="form-step">
                <div class="step active">
                    <div class="step-circle">1</div>
                    <div class="step-title">Details</div>
                </div>
                <div class="step">
                    <div class="step-circle">2</div>
                    <div class="step-title">Pricing</div>
                </div>
                <div class="step">
                    <div class="step-circle">3</div>
                    <div class="step-title">Image</div>
                </div>
                <div class="step">
                    <div class="step-circle">4</div>
                    <div class="step-title">Submit</div>
                </div>
            </div>
            
            <form id="bidForm" action="process_bid.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="product_name">Artwork Title</label>
                    <input type="text" class="form-control" id="product_name" name="product_name" placeholder="Enter the title of your artwork" required>
                </div>
                
                <div class="form-group">
                    <label for="product_description">Artwork Description</label>
                    <textarea class="form-control" id="product_description" name="product_description" 
                              placeholder="Describe your artwork, including its style, inspiration, and any notable features" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="product_size">Artwork Dimensions</label>
                    <input type="text" class="form-control" id="product_size" name="product_size" 
                           placeholder="e.g., 24 x 36 inches, 60 x 90 cm" required>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="starting_amount">Starting Bid Amount (₹)</label>
                            <input type="number" class="form-control" id="starting_amount" name="starting_amount" 
                                   step="0.01" min="0" placeholder="Enter starting bid amount" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="start_datetime">Auction Start Date & Time</label>
                            <input type="datetime-local" class="form-control" id="start_datetime" name="start_datetime" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="end_datetime">Auction End Date & Time</label>
                            <input type="datetime-local" class="form-control" id="end_datetime" name="end_datetime" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="seller_email">Your Email Address</label>
                    <input type="email" class="form-control" id="seller_email" name="seller_email" 
                           placeholder="Enter your email address" required>
                </div>
                
                <div class="form-group">
                    <label>Artwork Image</label>
                    <div class="file-upload">
                        <label for="product_image" class="file-upload-label">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Click to upload or drag an image</span>
                        </label>
                        <input type="file" id="product_image" name="product_image" accept="image/*" required>
                        <div class="file-upload-info">Accepted formats: JPG, PNG, GIF. Max size: 5MB</div>
                        <div class="selected-file" id="selectedFile">
                            <span id="fileNameDisplay"></span>
                            <i class="fas fa-check-circle" style="color: var(--success-color);"></i>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Submit Auction
                </button>
            </form>
        </div>
        
        <div class="form-footer">
            <p>By submitting this form, you agree to our terms and conditions for artwork auctions</p>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="modal">
        <div class="modal-content">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            <h3>Submission Successful!</h3>
            <p>Your artwork has been submitted for review. Please wait for admin approval before it appears in the auction.</p>
        </div>
    </div>

    <script>
        // Display file name when selected
        document.getElementById('product_image').addEventListener('change', function(e) {
            const fileName = e.target.files[0].name;
            document.getElementById('fileNameDisplay').textContent = fileName;
            document.getElementById('selectedFile').style.display = 'block';
        });
        
        // Form submission and modal display
        document.getElementById('bidForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent form submission
            
            // Animate step indicators to show progress
            document.querySelectorAll('.step').forEach(function(step) {
                step.classList.add('active');
            });
            
            // Show success modal
            document.getElementById('successModal').style.display = 'block';
            
            // Submit the form after delay
            setTimeout(() => {
                document.getElementById('successModal').style.display = 'none';
                this.submit(); // Submit the form after showing the modal
            }, 3000); // Show modal for 3 seconds
        });
    </script>
</body>
</html>
