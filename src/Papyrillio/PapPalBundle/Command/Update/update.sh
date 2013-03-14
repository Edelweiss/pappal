#!/usr/bin/env bash

ini="$(dirname $0)/update.ini"
database=$(sed -n 's/.*database *= *\([^ ]*.*\)/\1/p' < $ini)
user=$(sed -n 's/.*user *= *\([^ ]*.*\)/\1/p' < $ini)
gloin=$(sed -n 's/.*password *= *\([^ ]*.*\)/\1/p' < $ini)

mysql $database -u $user -p$gloin --execute="SELECT id, tm, hgv, ddb FROM sample" -X > sample.xml

java -Xms512m -Xmx1536m net.sf.saxon.Query -q:update.xql > update.sql

mysql $database -u $user -p$gloin --default_character_set utf8 < update.sql