CREATE TABLE IF NOT EXISTS hackathons (
                                          id SERIAL PRIMARY KEY,
                                          nom VARCHAR(255) NOT NULL,
                                          description TEXT NOT NULL,
                                          date_event TIMESTAMP NOT NULL,
                                          prix DECIMAL(10, 2) DEFAULT 0,
                                          latitude DOUBLE PRECISION NOT NULL,
                                          longitude DOUBLE PRECISION NOT NULL,
                                          ville VARCHAR(100),
                                          photo_url VARCHAR(255),
                                          email_organisateur VARCHAR(255) NOT NULL,
                                          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);