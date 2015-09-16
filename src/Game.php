<?php

namespace PF;

/**
 * @Entity @Table(name="game")
 **/
class Game {

  /** @Id @Column(type="integer") @GeneratedValue **/
  protected $id;

  /** @Column(type="integer") */
  protected $ipdb_id;

  public function getId()
  {
    return $this->id;
  }
}
