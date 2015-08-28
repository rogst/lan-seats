# LAN Seats

LAN Seats is a PHP application meant to provide a seat booking system for LAN parties.

## Requirements

* Webserver with PHP installed
* MySQL/MariaDB database
* PHP mysql client
* PHP cli

## Configuration

First you need to create a floorplan in the floorplan.php file. The numbers used in the floorplan is defined in the floortypes table in the database. 

When you are done with the floorplan you import it to the database using the import_floorplan.php script which can be launched with PHP CLI on the command line.

## Usage

You need to add tickets in the tickets table, and you send the ticket code and ticket password to the users that have payed for the tickets. The users can then use that information to book their seat.

## License

The MIT License (MIT)
