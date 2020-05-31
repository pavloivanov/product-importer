# Product Importer

## Prerequisites (Tested on MacOS)
Please install: docker, docker-compose and git

## Installation

Clone git repository to your folder:

`git clone git@github.com:pavloivanov/product-importer.git`


Go to **product-importer** folder and run following commands:

`docker-compose build`

`docker-compose up -d`


Install Symfony dependencies bu running command:

`docker-compose exec php composer install`


Execute migrations:

`docker-compose exec php bin/console doctrine:migrations:migrate`


## Usage

Product importer searches files in folder **./app/data/input** (there is testing file **products.csv** that can be used).
Command has required argument **csvFilename**.

Run Import Product Command:

`docker-compose exec php bin/console app:import-product products.csv`

## Testing
Run unit tests:

`docker-compose exec php php bin/phpunit`

### Adminer - MySQL web ui
You can use adminer to connect to mysql server by following link

http://localhost:8080/?server=mysql&username=root&db=store&select=product