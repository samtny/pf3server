<?php

namespace PF\Legacy;

use DOMDocument;

class Request {
  public $user;
  public $venues;
  function __construct() {
    $this->venues = array();
  }
  public function loadXML($xml) {

    $doc = new DOMDocument();
    $doc->loadXML($xml);

    $users = $doc->getElementsByTagName("user");
    if ($users->length == 1) {

      $u = new User();
      $u->id = $users->item(0)->getAttribute("key");

      $tokens = $users->item(0)->getElementsByTagName("token");
      foreach ($tokens as $token) {
        $t = new Token();
        $t->id = $token->getAttribute("key");
        $t->service = $token->getAttribute("service");
        $t->token = $token->nodeValue;
        $u->tokens[] = $t;
      }

      $this->user = $u;

    }

    $locs = $doc->getElementsByTagName("loc");

    foreach ($locs as $loc) {

      $venue = new Venue();

      if (!empty($loc->getAttribute("key"))) {
        $venue->id = $loc->getAttribute("key");
      }

      if (!empty($loc->getAttribute("flag"))) {
        $venue->flag = $loc->getAttribute("flag");
      }

      if (!empty($loc->getElementsByTagName("name")->item(0)->nodeValue)) {
        $venue->name = $loc->getElementsByTagName("name")->item(0)->nodeValue;
      }

      if (!empty($loc->getElementsByTagName("addr")->item(0)->nodeValue)) {
        $venue->street = $loc->getElementsByTagName("addr")->item(0)->nodeValue;
      }

      if (!empty($loc->getElementsByTagName("city")->item(0)->nodeValue)) {
        $venue->city = $loc->getElementsByTagName("city")->item(0)->nodeValue;
      }

      if (!empty($loc->getElementsByTagName("state")->item(0)->nodeValue)) {
        $venue->state = $loc->getElementsByTagName("state")->item(0)->nodeValue;
      }

      if (!empty($loc->getElementsByTagName("zipcode")->item(0)->nodeValue)) {
        $venue->zipcode = $loc->getElementsByTagName("zipcode")->item(0)->nodeValue;
      }

      if (!empty($loc->getElementsByTagName("phone")->item(0)->nodeValue)) {
        $venue->phone = $loc->getElementsByTagName("phone")->item(0)->nodeValue;
      }

      if (!empty($loc->getElementsByTagName("lat")->item(0)->nodeValue)) {
        $venue->lat = $loc->getElementsByTagName("lat")->item(0)->nodeValue;
      }

      if (!empty($loc->getElementsByTagName("lon")->item(0)->nodeValue)) {
        $venue->lon = $loc->getElementsByTagName("lon")->item(0)->nodeValue;
      }

      if (!empty($loc->getElementsByTagName("date")->item(0)->nodeValue)) {
        $venue->updated = $loc->getElementsByTagName("date")->item(0)->nodeValue;
      }

      if (!empty($loc->getElementsByTagName("source")->item(0)->nodeValue)) {
        $venue->source = $loc->getElementsByTagName("source")->item(0)->nodeValue;
      }

      if (!empty($loc->getElementsByTagName("url")->item(0)->nodeValue)) {
        $venue->url = $loc->getElementsByTagName("url")->item(0)->nodeValue;
      }

      $games = $loc->getElementsByTagName("game");

      foreach ($games as $g) {

        $game = new Game();

        $game->id = $g->getAttribute("key");
        $game->deleted = $g->getAttribute("deleted");

        if (!empty($g->getElementsByTagName("abbr")->item(0)->nodeValue)) {
          $game->abbr = $g->getElementsByTagName("abbr")->item(0)->nodeValue;
        }

        if (!empty($g->getElementsByTagName("cond")->item(0)->nodeValue)) {
          $game->cond = $g->getElementsByTagName("cond")->item(0)->nodeValue;
        }

        if (!empty($g->getElementsByTagName("price")->item(0)->nodeValue)) {
          $game->price = $g->getElementsByTagName("price")->item(0)->nodeValue;
        }

        if (!empty($g->getElementsByTagName("ipdb")->item(0)->nodeValue)) {
          $game->ipdb = $g->getElementsByTagName("ipdb")->item(0)->nodeValue;
        }

        $venue->games[] = $game;
      }

      $comments = $loc->getElementsByTagName("comment");

      foreach ($comments as $c) {

        $comment = new Comment();

        $comment->id = $c->getAttribute("key");

        if (!empty($c->getElementsByTagName("ctext")->item(0)->nodeValue)) {
          $comment->text = $c->getElementsByTagName("ctext")->item(0)->nodeValue;
        }

        if (!empty($c->getElementsByTagName("cdate")->item(0)->nodeValue)) {
          $comment->date = $c->getElementsByTagName("cdate")->item(0)->nodeValue;
        }

        $venue->comments[] = $comment;
      }

      $this->venues[] = $venue;
    }
  }
}
