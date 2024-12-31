CREATE TABLE IF NOT EXISTS pm_vocabulary (
    id INT AUTO_INCREMENT PRIMARY KEY,
    word VARCHAR(255) NOT NULL,
    part_of_speech VARCHAR(50) NOT NULL,
    meaning TEXT NOT NULL,
    example TEXT,
    example_cn TEXT,
    letter CHAR(1) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
); 