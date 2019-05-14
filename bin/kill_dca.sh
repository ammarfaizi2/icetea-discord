#!/bin/bash
kill -9 $(ps ax | grep "team-reflex/discord-php" | grep -oP '(?<=^)\s*\d+')
sync
sleep 3
