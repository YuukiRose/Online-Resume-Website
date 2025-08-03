<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Testimonial - Rose Webb</title>
    <link rel="stylesheet" href="css/vendor.css">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .testimonial-form {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background: #1a1a1a;
            border-radius: 10px;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #fff;
            font-weight: 500;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #333;
            border-radius: 5px;
            background: #2a2a2a;
            color: #fff;
            font-size: 1rem;
        }
        .form-group textarea {
            height: 120px;
            resize: vertical;
        }
        .file-upload {
            position: relative;
            display: inline-block;
            cursor: pointer;
            background: #333;
            padding: 10px 20px;
            border-radius: 5px;
            border: 1px solid #555;
        }
        .file-upload input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        .submit-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            width: 100%;
        }
        .submit-btn:hover {
            opacity: 0.9;
        }
        .success-message,
        .error-message {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 5px;
            text-align: center;
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error-message {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <header class="s-header">
        <div class="row wide main-nav-wrap">
            <nav class="column lg-12 main-nav">
                <ul>
                    <li><a href="index.html" class="home-link">Rose Webb</a></li>
                    <li><a href="index.html#intro">Intro</a></li>
                    <li><a href="index.html#about">About</a></li>
                    <li><a href="index.html#works">Works</a></li>
                    <li><a href="index.html#contact">Contact</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="s-content" style="padding-top: 100px;">
        <div class="testimonial-form">
            <h2 style="text-align: center; color: #fff; margin-bottom: 2rem;">Submit a Testimonial</h2>
            
            <?php
            if (isset($_GET['success'])) {
                echo '<div class="success-message">Thank you! Your testimonial has been submitted and is awaiting approval.</div>';
            }
            if (isset($_GET['error'])) {
                echo '<div class="error-message">There was an error submitting your testimonial. Please try again.</div>';
            }
            ?>

            <form action="submit_testimonial.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="company">Company</label>
                    <input type="text" id="company" name="company">
                </div>

                <div class="form-group">
                    <label for="position">Position/Title</label>
                    <input type="text" id="position" name="position">
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="testimonial">Your Testimonial *</label>
                    <textarea id="testimonial" name="testimonial" placeholder="Share your experience working with Rose..." required></textarea>
                </div>

                <div class="form-group">
                    <label for="avatar">Profile Picture (Optional)</label>
                    <div class="file-upload">
                        <input type="file" id="avatar" name="avatar" accept="image/*">
                        <span>Choose file or drag here</span>
                    </div>
                    <small style="color: #ccc;">Accepted formats: JPG, PNG, GIF (Max 2MB)</small>
                </div>

                <button type="submit" class="submit-btn">Submit Testimonial</button>
            </form>
        </div>
    </main>

    <script>
        // File upload enhancement
        document.getElementById('avatar').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || 'Choose file or drag here';
            e.target.nextElementSibling.textContent = fileName;
        });
    </script>
</body>
</html>
