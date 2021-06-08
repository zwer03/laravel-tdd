# Copy development env file
cp .env.development .env

# Create sqlite file
touch ./database/test.sqlite

# Bring down any running service
docker-compose down

# Clear cache
sudo rm -rf bootstrap/cache/*.php

# build images and bring up containers
docker-compose build && docker-compose up -d

# Interact with php container
docker exec -i php php artisan key:generate
docker exec -i php php artisan migrate:fresh --seed
docker exec -i php chmod o+w ./storage/ -R