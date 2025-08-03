<?php
require_once 'config/database.php';

// Function to get content from database
function getContent($pdo, $section, $field, $default = '') {
    try {
        $stmt = $pdo->prepare("SELECT content FROM portfolio_content WHERE section = ? AND field_name = ?");
        $stmt->execute([$section, $field]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['content'] : $default;
    } catch (PDOException $e) {
        return $default;
    }
}

// Get basic content from database
$welcomeText = getContent($pdo, 'intro', 'welcome_text', 'Welcome to my portfolio');
$mainTitle = getContent($pdo, 'intro', 'main_title', 'I am Rose Webb,<br>An IT Technician<br>& Aspiring<br>Developer based<br>in South Yorkshire.');
$introImage = getContent($pdo, 'intro', 'image', '');
$aboutDesc = getContent($pdo, 'about', 'about_description', '');
$aboutImage = getContent($pdo, 'about', 'image', 'images/about-photo.jpg');
$cvLink = getContent($pdo, 'about', 'cv_link', 'Files/RWEBB-CV.pdf');
$worksTitle = getContent($pdo, 'works', 'works_title', 'Scripts, Code Projects & Other Nerdy Stuff');
$contactTitle = getContent($pdo, 'contact', 'contact_title', 'Get In Touch');
$contactSubtitle = getContent($pdo, 'contact', 'contact_subtitle', '');
$email = getContent($pdo, 'contact', 'email', 'rosewebb2810@gmail.com');
$phone = getContent($pdo, 'contact', 'phone', '+44 7578 777928');
$linkedinUrl = getContent($pdo, 'social', 'linkedin_url', 'https://www.linkedin.com/in/rose-webb-798014215/');
$twitterUrl = getContent($pdo, 'social', 'twitter_url', 'https://www.twitter.com/YuukiiRose');
$githubUrl = getContent($pdo, 'social', 'github_url', 'https://github.com/YuukiRose');

// Get Skills/Expertise
$skillsByCategory = [];
try {
    $stmt = $pdo->query("SELECT category, skill_name FROM portfolio_skills ORDER BY category, sort_order");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $skillsByCategory[$row['category']][] = $row['skill_name'];
    }
} catch (PDOException $e) {
    // Handle error gracefully
}

// Get Experience
$experience = [];
try {
    $stmt = $pdo->query("SELECT * FROM portfolio_experience ORDER BY is_present DESC, date_start DESC");
    $experience = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Fallback to old sorting if new columns don't exist
    try {
        $stmt = $pdo->query("SELECT * FROM portfolio_experience ORDER BY sort_order");
        $experience = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e2) {
        // Handle error gracefully
    }
}

// Get Education
$education = [];
try {
    $stmt = $pdo->query("SELECT * FROM portfolio_education ORDER BY is_present DESC, date_start DESC");
    $education = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Fallback to old sorting if new columns don't exist
    try {
        $stmt = $pdo->query("SELECT * FROM portfolio_education ORDER BY sort_order");
        $education = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e2) {
        // Handle error gracefully
    }
}

// Get Works
$works = [];
try {
    $stmt = $pdo->query("SELECT * FROM portfolio_works ORDER BY sort_order, id");
    $works = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle error gracefully
}
?>
<!DOCTYPE html>
<html class="no-js ss-preload" lang="en">
<head>

    <!--- basic page needs
    ================================================== -->
    <meta charset="utf-8">
    <title>Rose Webb - Resume</title>
    <meta name="description" content="Resume of Rose Webb, IT tech.">
    <meta name="author" content="Rose Webb">

    <!-- mobile specific metas
    ================================================== -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSS
    ================================================== -->
    <link rel="stylesheet" href="css/vendor.css">
    <link rel="stylesheet" href="css/styles.css">

    <!-- favicons
    ================================================== -->
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <link rel="manifest" href="site.webmanifest">

</head>

<body id="top">


    <!-- # preloader
    ================================================== -->
    <div id="preloader">
        <div id="loader">
        </div>
    </div>


    <!-- # page wrap
    ================================================== -->
    <div class="s-pagewrap">

        <div class="circles">
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
        </div>


        <!-- ## site header 
        ================================================== -->
        <header class="s-header">

            <div class="header-mobile">
                <span class="mobile-home-link"><a href="index.php">Rose Webb</a></span>
                <a class="mobile-menu-toggle" href="#0"><span>Menu</span></a>
            </div>

            <div class="row wide main-nav-wrap">
                <nav class="column lg-12 main-nav">
                    <ul>
                        <li><a href="index.php" class="home-link">Rose Webb</a></li>
                        <li class="current"><a href="#intro" class="smoothscroll">Intro</a></li>
                        <li><a href="#about" class="smoothscroll">About</a></li>
                        <li><a href="#works" class="smoothscroll">Works</a></li>
                        <li><a href="#contact" class="smoothscroll">Say Hello</a></li>
                        <li><a href="user/register.php" class="testimonial-link">Share Testimonial</a></li>
                        <li><a href="user/login.php" class="login-link">Login</a></li>
                    </ul>
                </nav>
            </div>

        </header> <!-- end s-header -->


        <!-- ## main content
        ==================================================- -->
        <main class="s-content">


            <!-- ### intro
            ================================================== -->
            <section id="intro" class="s-intro target-section">

                <div class="row intro-content wide">

                    <div class="column lg-12 md-12">
                        <div class="intro-pic-block">
                            <?php if (!empty($introImage)): ?>
                                <img src="<?php echo htmlspecialchars($introImage); ?>" 
                                     srcset="<?php echo htmlspecialchars($introImage); ?> 1x, <?php echo htmlspecialchars($introImage); ?> 2x" 
                                     alt="Rose Webb Profile Picture" class="intro-pic">
                            <?php else: ?>
                                <img src="images/Profile.JPG" 
                                     srcset="images/Profile.JPG 1x, images/Profile.JPG 2x" 
                                     alt="Rose Webb Profile Picture" class="intro-pic">
                            <?php endif; ?>
                        </div>

                        <div class="text-pretitle with-line">
                            <?php echo htmlspecialchars($welcomeText); ?>
                        </div>

                        <h2 class="text-huge-title">
                            <?php echo $mainTitle; ?>
                        </h2>

                        <ul class="intro-social">
                            <li><a href="<?php echo htmlspecialchars($linkedinUrl); ?>" target="_blank">LinkedIn</a></li>
                            <li><a href="<?php echo htmlspecialchars($twitterUrl); ?>" target="_blank">Twitter</a></li>
                            <li><a href="<?php echo htmlspecialchars($githubUrl); ?>" target="_blank">GitHub</a></li>
                        </ul>

                    </div>

                </div> <!-- end intro content -->

                <div class="intro-scrolldown">
                    <a href="#about" class="smoothscroll">
                        <div class="scroll-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="36" height="36" fill="none" stroke="#97b34a" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><polyline points="7,13 12,18 17,13"></polyline><polyline points="7,6 12,11 17,6"></polyline></svg>
                        </div>
                        <span class="scroll-text">Scroll For More</span>
                    </a>
                </div> <!-- end intro-scrolldown -->

            </section> <!-- end s-intro -->


            <!-- ### about
            ================================================== -->
            <section id="about" class="s-about target-section">


                <div class="row about-info wide" data-animate-block>

                    <div class="column lg-6 md-12 about-info__pic-block">
                        <img src="<?php echo htmlspecialchars($aboutImage); ?>" 
                             srcset="<?php echo htmlspecialchars($aboutImage); ?> 1x, <?php echo htmlspecialchars($aboutImage); ?> 2x" 
                             alt="About Rose Webb" class="about-info__pic" data-animate-el>
                    </div>

                    <div class="column lg-6 md-12">
                        <div class="about-info__text" >

                            <h2 class="text-pretitle with-line" data-animate-el>
                                About
                            </h2>
                            <p class="attention-getter" data-animate-el>
                                <?php echo htmlspecialchars($aboutDesc); ?>
                            </p>
                            <a href="<?php echo htmlspecialchars($cvLink); ?>" class="btn btn--medium u-fullwidth" data-animate-el>Download CV</a>

                        </div>
                    </div>
                </div> <!-- about-info -->


                <div class="row about-expertise" data-animate-block>
                    <div class="column lg-12">

                        <h2 class="text-pretitle" data-animate-el>Expertise</h2>

                        <div class="row">
                            <?php 
                            $columnCount = 0;
                            $columnWidth = 'lg-4 md-6';
                            foreach ($skillsByCategory as $category => $skills): 
                            ?>
                            <div class="column <?php echo $columnWidth; ?>">
                                <h4><?php echo htmlspecialchars($category); ?></h4>
                                <ul class="skills-list" data-animate-el>
                                    <?php foreach ($skills as $skill): ?>
                                    <li><?php echo htmlspecialchars($skill); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php 
                            $columnCount++;
                            endforeach; 
                            ?>
                        </div>

                    </div>
                </div> <!-- end about-expertise -->


                <div class="row about-timelines" data-animate-block>

                    <div class="column lg-6 tab-12">

                        <h2 class="text-pretitle" data-animate-el>
                            Experience
                        </h2>

                        <div class="timeline" data-animate-el>
                            <?php foreach ($experience as $exp): ?>
                            <div class="timeline__block">
                                <div class="timeline__bullet"></div>
                                <div class="timeline__header">
                                    <h4 class="timeline__title"><?php echo htmlspecialchars($exp['company']); ?></h4>
                                    <h5 class="timeline__meta"><?php echo htmlspecialchars($exp['position']); ?></h5>
                                    <p class="timeline__timeframe"><?php echo htmlspecialchars($exp['timeframe'] ?? ''); ?></p>
                                </div>
                                <div class="timeline__desc">
                                    <p><?php echo htmlspecialchars($exp['description']); ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div> <!-- end timeline -->

                    </div> <!-- end column -->

                    <div class="column lg-6 tab-12">

                        <h2 class="text-pretitle" data-animate-el>
                            Education
                        </h2>

                        <div class="timeline" data-animate-el>
                            <?php foreach ($education as $edu): ?>
                            <div class="timeline__block">
                                <div class="timeline__bullet"></div>
                                <div class="timeline__header">
                                    <h4 class="timeline__title"><?php echo htmlspecialchars($edu['institution']); ?></h4>
                                    <h5 class="timeline__meta"><?php echo htmlspecialchars($edu['qualification']); ?></h5>
                                    <p class="timeline__timeframe"><?php echo htmlspecialchars($edu['timeframe'] ?? ''); ?></p>
                                </div>
                                <div class="timeline__desc">
                                    <p><?php echo htmlspecialchars($edu['description']); ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div> <!-- end timeline -->

                    </div> <!-- end column -->

                </div> <!-- end about-timelines -->

            </section> <!-- end s-about -->


            <!-- ### works
            ================================================== -->
            <section id="works" class="s-works target-section">


                <div class="row works-portfolio">

                    <div class="column lg-12" data-animate-block>

                        <h2 class="text-pretitle" data-animate-el>
                            Recent Works
                        </h2>
                        <p class="h1" data-animate-el>
                            <?php echo htmlspecialchars($worksTitle); ?>
                        </p>
    
                        <ul class="folio-list row block-lg-one-half block-tab-one-half">

                            <?php foreach ($works as $index => $work): ?>
                            <li class="folio-list__item column" data-animate-el>
                                <a class="folio-list__item-link" href="#modal-<?php echo str_pad($index + 1, 2, '0', STR_PAD_LEFT); ?>">
                                    <div class="folio-list__item-pic">
                                        <img src="<?php echo htmlspecialchars($work['image_path'] ?: 'images/portfolio/sample-image.jpg'); ?>"
                                             srcset="<?php echo htmlspecialchars($work['image_path'] ?: 'images/portfolio/sample-image.jpg'); ?> 1x" alt="<?php echo htmlspecialchars($work['title']); ?>">
                                    </div>
                                    
                                    <div class="folio-list__item-text">
                                        <div class="folio-list__item-cat">
                                            <?php echo htmlspecialchars($work['category']); ?>
                                        </div>
                                        <div class="folio-list__item-title">
                                            <?php echo htmlspecialchars($work['title']); ?>
                                        </div>
                                    </div>
                                </a>
                                <a class="folio-list__proj-link" href="<?php echo htmlspecialchars($work['project_url'] ?: '#'); ?>" title="project link" <?php echo $work['project_url'] ? 'target="_blank"' : ''; ?>>
                                    <svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8.14645 3.14645C8.34171 2.95118 8.65829 2.95118 8.85355 3.14645L12.8536 7.14645C13.0488 7.34171 13.0488 7.65829 12.8536 7.85355L8.85355 11.8536C8.65829 12.0488 8.34171 12.0488 8.14645 11.8536C7.95118 11.6583 7.95118 11.3417 8.14645 11.1464L11.2929 8H2.5C2.22386 8 2 7.77614 2 7.5C2 7.22386 2.22386 7 2.5 7H11.2929L8.14645 3.85355C7.95118 3.65829 7.95118 3.34171 8.14645 3.14645Z" fill="currentColor" fill-rule="evenodd" clip-rule="evenodd"></path></svg>
                                </a>
                            </li> <!--end folio-list__item -->
                            <?php endforeach; ?>

                        </ul> <!-- end folio-list -->

                    </div> <!-- end column -->


                    <!-- Modal Templates -->

                    <?php foreach ($works as $index => $work): ?>
                    <div id="modal-<?php echo str_pad($index + 1, 2, '0', STR_PAD_LEFT); ?>" hidden>
                        <div class="modal-popup">
                            <img src="<?php echo htmlspecialchars($work['gallery_image_path'] ?: $work['image_path'] ?: 'images/portfolio/gallery/g-sample.jpg'); ?>" alt="<?php echo htmlspecialchars($work['title']); ?>">

                            <div class="modal-popup__desc">
                                <h5><?php echo htmlspecialchars($work['title']); ?></h5>
                                <p><?php echo nl2br(htmlspecialchars($work['description'])); ?></p>
                                <ul class="modal-popup__cat">
                                    <li><?php echo htmlspecialchars($work['category']); ?></li>
                                </ul>
                            </div>

                            <?php if ($work['project_url']): ?>
                            <a href="<?php echo htmlspecialchars($work['project_url']); ?>" class="modal-popup__details" target="_blank">View Repository</a>
                            <?php endif; ?>
                        </div>
                    </div> <!-- end modal -->
                    <?php endforeach; ?>                </div> <!-- end works-portfolio -->


                <div class="row testimonials">
                    <div class="column lg-12" data-animate-block>
                        <div class="text-pretitle with-line" style="text-align: center; margin-bottom: 2rem;">
                            Testimonials
                        </div>
        
                        <div class="swiper-container testimonial-slider" data-animate-el id="testimonial-slider">
        
                            <div class="swiper-wrapper" id="testimonial-wrapper">
                                <!-- Testimonials will be loaded dynamically -->
                            </div> <!-- end swiper-wrapper -->
        
                            <div class="swiper-pagination"></div>
        
                        </div> <!-- end swiper-container -->
        
                    </div> <!-- end column -->
                </div> <!-- end row testimonials -->

            </section> <!-- end s-works -->


            <!-- ### contact
            ================================================== -->
            <section id="contact" class="s-contact target-section">

                <div class="row contact-top">
                    <div class="column lg-12">
                        <h2 class="text-pretitle">
                            <?php echo htmlspecialchars($contactTitle); ?>
                        </h2>

                        <p class="h1">
                            <?php echo htmlspecialchars($contactSubtitle); ?>
                        </p>
                    </div>
                </div> <!-- end contact-top -->

                <div class="row contact-bottom">
                    <div class="column lg-3 md-5 tab-6 stack-on-550 contact-block">
                        <h3 class="text-pretitle">Reach me at</h3>
                        <p class="contact-links">
                            <a href="mailto:<?php echo htmlspecialchars($email); ?>" class="mailtoui"><?php echo htmlspecialchars($email); ?></a> <br>
                            <a href="tel:<?php echo htmlspecialchars($phone); ?>"><?php echo htmlspecialchars($phone); ?></a>
                        </p>
                    </div>
                    <div class="column lg-4 md-5 tab-6 stack-on-550 contact-block">
                        <h3 class="text-pretitle">Social</h3>
                        <ul class="contact-social">
                            <li><a href="<?php echo htmlspecialchars($linkedinUrl); ?>">LinkedIn</a></li>
                            <li><a href="<?php echo htmlspecialchars($twitterUrl); ?>">Twitter</a></li>
                            <li><a href="<?php echo htmlspecialchars($githubUrl); ?>">GitHub</a></li>
                        </ul>
                    </div>
                    <div class="column lg-4 md-12 contact-block">
                        <a href="mailto:<?php echo htmlspecialchars($email); ?>" class="mailtoui btn btn--medium u-fullwidth contact-btn">Say Hello.</a>
                    </div>
                </div> <!-- end contact-bottom -->

            </section> <!-- end contact -->

        </main> <!-- end s-content -->


        <!-- ## footer
        ================================================== -->
        <footer class="s-footer">

            <div class="row">
                <div class="column ss-copyright">
                    <span>© Copyright RWebb 2025</span> 
                    <span>Design by Rose Webb</a></span>
                </div>

                <div class="ss-go-top">
                    <a class="smoothscroll" title="Back to Top" href="#top">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill-rule="evenodd" clip-rule="evenodd"><path d="M11 2.206l-6.235 7.528-.765-.645 7.521-9 .479-.479.479.479 7.479 9-.764.646-6.214-7.529v21.794h-1v-21.794z"/></svg>
                    </a>
                </div>
            </div>

        </footer> <!-- end s-footer -->

    </div> <!-- end -s-pagewrap -->


    <!-- Java Script
    ================================================== -->
    <script src="js/plugins.js"></script>
    <script src="js/main.js"></script>
    
    <!-- Testimonial Loading Script -->
    <script>
        // Load testimonials dynamically
        async function loadTestimonials() {
            try {
                const response = await fetch('api/get_testimonials.php');
                const testimonials = await response.json();
                
                if (testimonials.error) {
                    console.error('Error loading testimonials:', testimonials.error);
                    return;
                }
                
                const wrapper = document.getElementById('testimonial-wrapper');
                wrapper.innerHTML = '';
                
                if (testimonials.length === 0) {
                    // Show default message directing users to the navigation menu
                    wrapper.innerHTML = `
                        <div class="testimonial-slider__slide swiper-slide">
                            <div class="testimonial-slider__author">
                                <img src="images/avatars/Default.jpg" alt="Author image" class="testimonial-slider__avatar">
                                <cite class="testimonial-slider__cite">
                                    <strong>Be the first!</strong>
                                    <span>Share your experience</span>
                                </cite>
                            </div>
                            <p>
                                Be the first to share your experience working with Rose. Click "Share Testimonial" in the navigation menu above to create an account and submit your testimonial.
                            </p>
                        </div>
                    `;
                } else {
                    testimonials.forEach((testimonial, index) => {
                        // Use LinkedIn profile picture if available, otherwise use uploaded avatar or default
                        const avatarSrc = testimonial.profile_picture_url || testimonial.avatar || `images/avatars/Default.jpg`;
                        const position = testimonial.position || 'Client';
                        const company = testimonial.company ? `, ${testimonial.company}` : '';
                        const linkedinLink = testimonial.linkedin_url ? 
                            `<a href="${escapeHtml(testimonial.linkedin_url)}" target="_blank" class="linkedin-link" title="View LinkedIn Profile">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="#0077b5">
                                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                </svg>
                            </a>` : '';
                        
                        const slide = document.createElement('div');
                        slide.className = 'testimonial-slider__slide swiper-slide';
                        slide.innerHTML = `
                            <div class="testimonial-slider__author">
                                <img src="${avatarSrc}" alt="Author image" class="testimonial-slider__avatar">
                                <cite class="testimonial-slider__cite">
                                    <strong>${escapeHtml(testimonial.name)} ${linkedinLink}</strong>
                                    <span>${escapeHtml(position)}${escapeHtml(company)}</span>
                                </cite>
                            </div>
                            <p>${escapeHtml(testimonial.testimonial)}</p>
                        `;
                        wrapper.appendChild(slide);
                    });
                }
                
                // Reinitialize Swiper if it exists
                if (window.Swiper && window.testimonialSlider) {
                    window.testimonialSlider.update();
                }
                
            } catch (error) {
                console.error('Error loading testimonials:', error);
            }
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Load testimonials when page loads
        document.addEventListener('DOMContentLoaded', loadTestimonials);
    </script>

</body>
</html>
