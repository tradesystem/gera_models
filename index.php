<?php
$table_name = isset($_GET['table'])  ?  $_GET['table'] : '';

if ($table_name === '') {
  echo "erro!";
  exit();
}

$servername = "192.168.15.150";
$username = "webtrade";
$password = "T3760S";
$dbname = "dbdemanda";
$port = 3306;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname,$port);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
  exit();
}

$sql = "DESCRIBE $table_name";

$result = $conn->query($sql);
$array = [];

if ($result->num_rows > 0) {
  // output data of each row
  while ($row = $result->fetch_assoc()) {
    $field = $row['Field'];
    $type = $row['Type'];
    $pri = $row['Key'];
    echo "campo: $field  tipo: $type primary $pri <br>";
    $teste = new stdClass();
    $teste->name = $field;
    $teste->type = $type;
    $teste->primary = $pri === "PRI" ? 1 : 0;
    $array[] = $teste;
  }
} else {
  echo "0 results";
  exit();
}
$conn->close();

function renderType($type)
{

  if (
    $type === 'int' ||
    $type === 'double' ||
    $type === 'float' ||
    $type === "decimal" ||
    $type === 'tinyint'
  ) {
    return 'number';

  }

  if (
    $type === 'date' ||
    $type === 'timestamp' ||
    $type === 'datetime'

  ) {
    return 'Date';
  }

  return "string";
}
;



function renderColumn($name, $type, $primary)
{

  $column = $primary === 1 ? "@PrimaryColumn" : "@Column";
  $name_lower_case = strtolower($name);
  $texto = explode('(', $type);  
  $type = $texto[0];
  $type_java = renderType(strtolower($type));
  $texto = explode(')', $texto[1]);
  $len = $texto[0];
  $len = $type === 'varchar' ? "length: $len ," : '';
  return ("
    $column({ 
      name: '$name', 
      type: '$type',  
      $len 
    }) 
    $name_lower_case: $type_java | undefined;
  ");
}
;

$init = 
"
import {
  Column,
  Entity,
  Index,
  JoinColumn,
  ManyToOne,
  OneToMany,
  OneToOne,
  PrimaryColumn,
  PrimaryGeneratedColumn,
} from 'typeorm';


@Entity('$table_name')
  export default class ".ucfirst($table_name)."{ ";


$columns = " ";


foreach( $array as $row ) {

  $columns .= renderColumn($row->name, $row->type, $row->primary);

}

$teste = $init . $columns . '}';

echo $init . $columns . '}';

$name = strtolower($table_name).".models.ts";

file_put_contents($name, $teste);


?>