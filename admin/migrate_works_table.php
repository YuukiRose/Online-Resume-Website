<?php
require_once '../config/database.php';

echo "Creating portfolio_works table...\n";

try {
    // Create portfolio_works table
    $stmt = $pdo->prepare("
        CREATE TABLE IF NOT EXISTS portfolio_works (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            category VARCHAR(100) NOT NULL,
            description TEXT,
            project_url VARCHAR(500),
            image_path VARCHAR(500),
            gallery_image_path VARCHAR(500),
            sort_order INT DEFAULT 999,
            is_featured BOOLEAN DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    $stmt->execute();
    echo "✅ portfolio_works table created successfully!\n";
    
    // Check if table has any data
    $stmt = $pdo->query("SELECT COUNT(*) FROM portfolio_works");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        echo "Adding default portfolio works...\n";
        
        // Insert default works
        $defaultWorks = [
            [
                'title' => 'Fuji',
                'category' => 'Website',
                'description' => 'Distinctio laudantium ratione facere explicabo dolorem.',
                'project_url' => 'https://www.behance.net/',
                'image_path' => 'images/portfolio/fuji.jpg',
                'gallery_image_path' => 'images/portfolio/gallery/g-fuji.jpg',
                'sort_order' => 1
            ],
            [
                'title' => 'Lamp',
                'category' => 'Web Design',
                'description' => 'Quisquam vel libero consequuntur autem voluptas.',
                'project_url' => 'https://www.behance.net/',
                'image_path' => 'images/portfolio/lamp.jpg',
                'gallery_image_path' => 'images/portfolio/gallery/g-lamp.jpg',
                'sort_order' => 2
            ],
            [
                'title' => 'Rucksack',
                'category' => 'Branding',
                'description' => 'Odio soluta eum illum laboriosam corporis sint.',
                'project_url' => 'https://www.behance.net/',
                'image_path' => 'images/portfolio/rucksack.jpg',
                'gallery_image_path' => 'images/portfolio/gallery/g-rucksack.jpg',
                'sort_order' => 3
            ],
            [
                'title' => 'Since Day One',
                'category' => 'Frontend Design',
                'description' => 'Neque dicta enim quasi voluptatem repudiandae et.',
                'project_url' => 'https://www.behance.net/',
                'image_path' => 'images/portfolio/skaterboy.jpg',
                'gallery_image_path' => 'images/portfolio/gallery/g-skaterboy.jpg',
                'sort_order' => 4
            ],
            [
                'title' => 'Sand Dunes',
                'category' => 'Branding',
                'description' => 'Proin gravida nibh vel velit auctor aliquet.',
                'project_url' => 'https://www.behance.net/',
                'image_path' => 'images/portfolio/sanddunes.jpg',
                'gallery_image_path' => 'images/portfolio/gallery/g-sanddunes.jpg',
                'sort_order' => 5
            ],
            [
                'title' => 'Minimalismo',
                'category' => 'Product Design',
                'description' => 'Exercitationem reprehenderit quod explicabo consequatur.',
                'project_url' => 'https://www.behance.net/',
                'image_path' => 'images/portfolio/minimalismo.jpg',
                'gallery_image_path' => 'images/portfolio/gallery/g-minimalismo.jpg',
                'sort_order' => 6
            ]
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO portfolio_works (title, category, description, project_url, image_path, gallery_image_path, sort_order) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($defaultWorks as $work) {
            $stmt->execute([
                $work['title'],
                $work['category'],
                $work['description'],
                $work['project_url'],
                $work['image_path'],
                $work['gallery_image_path'],
                $work['sort_order']
            ]);
        }
        
        echo "✅ Default portfolio works added successfully!\n";
    } else {
        echo "ℹ️ Table already contains $count works.\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\nMigration completed!\n";
?>
