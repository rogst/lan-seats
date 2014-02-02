<?php

class DAL {
    private $connection = null;
    private $queries = array();

    private function LoadQueries() {
        $this->queries['getTicketHolderName'] = 'SELECT t.holder_name FROM floorplan f INNER JOIN tickets t on f.ticket = t.id WHERE f.x = %1$d AND f.y = %2$d;';
        $this->queries['getSeatAndRow'] = 'SELECT seat,row FROM floorplan WHERE x = %1$d AND y = %2$d;';
        $this->queries['bookSeat'] = 'UPDATE floorplan SET type = 7, ticket = %3$d, reservation_date = \'%4$s\' WHERE x = %1$d AND y = %2$d AND ticket is null;';
        $this->queries['getSeat'] = 'SELECT * FROM floorplan WHERE x = %1$d AND y = %2$d;';
        $this->queries['getTicket'] = 'SELECT * FROM tickets WHERE ticket_code = \'%1$s\' AND ticket_password = \'%2$s\';';
        $this->queries['unbookSeat'] = 'UPDATE floorplan SET type = 6, ticket = null, reservation_date = null WHERE ticket = %1$d;';
        $this->queries['getFloorplan'] = 'SELECT x, y, type, ticket FROM floorplan ORDER BY y, x;';
        $this->queries['getFloortypes'] = 'SELECT * FROM floortypes;';
    }

    public function __construct($config) {
        $this->connection = new mysqli(
            $config->databaseHost
            , $config->databaseName
            , $config->databasePass
            , $config->databaseUser);
        if ($this->connection->connect_errno) {
            exit('Connection to database failed');
        }

        if (!$this->connection->set_charset($config->databaseCharset)) {
            exit('Failed to set database charset to '.$config->databaseCharset);
        }

        $this->LoadQueries();
    }

    public function Query($queryName, $args = array(), $returnFormat = 'ASSOC') {
        $escapedArgs = array();
        foreach($args as $arg) {
            $escapedArgs[] = $this->connection->real_escape_string($arg);
        }        
        $query = vsprintf($this->queries[$queryName], $escapedArgs);
        $data = $this->connection->query($query);
        if ($this->connection->error) { 
            exit($this->connection->error); 
        }
        $returnFormat = ($returnFormat == 'ASSOC') ? MYSQLI_ASSOC : MYSQLI_NUM;
        if ($data === true) {
            return true;
        }
        $result = $data->fetch_all($returnFormat);
        $data->free();
        return $result;
    }
}
?>
