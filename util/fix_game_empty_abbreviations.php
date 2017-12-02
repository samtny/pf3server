<?php

require_once __DIR__ . "/../bootstrap.php";

use \Symfony\Component\Yaml\Parser;
use \PF\Utilities\GameUtil;

$parser = new Parser();

$credentials = $parser->parse(file_get_contents(__DIR__ . '/../credentials.yml'));

$conn = array(
  'driver' => 'pdo_mysql',
  'dbname' => $credentials['pf3server_db_name'],
  'user' => $credentials['pf3server_db_user'],
  'password' => $credentials['pf3server_db_password'],
  'host' => $credentials['pf3server_db_host'],
  'driverOptions' => array(
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))"
  )
);

$dsn = 'mysql:host='. $credentials['pf3server_db_host'] . ';dbname='. $credentials['pf3server_db_name'] . ';port=3306;charset=utf8mb4';

$opt = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES   => false,
];

$pdo = new PDO($dsn, $credentials['pf3server_db_user'], $credentials['pf3server_db_password'], $opt);

$stmt = $pdo->prepare('SELECT id, name FROM game WHERE abbreviation IS NULL');
$stmt->execute();

$stmt_update = $pdo->prepare('UPDATE game SET abbreviation = :abbreviation WHERE id = :id LIMIT 1');

while ($row = $stmt->fetch())
{
  $id = $row['id'];
  $name = $row['name'];

  $game = new \PF\Game();
  $game->setName($name);

  $abbreviation = GameUtil::generateAbbreviation($game);

  $stmt_update->execute(array(':id' => $id, ':abbreviation' => $abbreviation));

  $game = NULL;
}
