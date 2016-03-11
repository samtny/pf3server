<?php

namespace PF;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity @ORM\Table(name="user")
 **/
class User {
  /** @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer") **/
  protected $id;

  /** @ORM\Column(type="string") **/
  protected $name;

  public function getId()
  {
    return $this->id;
  }
}
