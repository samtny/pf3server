#!/bin/bash

curl -v -H "Content-Type: application/json" -XPOST --data "@venue.json" http://localhost/venue
