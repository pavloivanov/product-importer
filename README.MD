# Product Importer

## Prerequisites (Tested on MacOS)
Please install: docker, docker-compose and git

Note: product-importer uses PHP 7.4 features. Please take into account this in case you don't want to use provided docker image

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

#### Allowed columns in csv files:
    * sku - required. Allowed chars: alpanumeric, minus and underscore
    * description - required. Allowed printable chars
    * normalPrice - required. Allowed positive numbers with maximum two decimal numbers
    * specialPrice - optional. Same constraints as for normalPrice except it must be less than normalPrice

#### Import notes:
    * In case record with sku already exists in database then record will be updated.
    * In case record is identical to record in database then counter of updated products will not incremented
    * Total number of successfull records that were read from csv file will be displayed at the end of script
    * Total number of failed records will be skipped and count will be dispayed at the end of script

## Testing
Run unit tests:

`docker-compose exec php php bin/phpunit`

### Adminer - MySQL web ui
You can use adminer to connect to mysql server by following link

http://localhost:8080/?server=mysql&username=root&db=store&select=product