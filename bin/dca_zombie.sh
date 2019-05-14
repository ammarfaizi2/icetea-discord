#!/bin/bash
pd=$(pstree -pagH 1 | grep -oP '^\s+\|-dca.+decode' | grep -oP '(?<=\,)\d+\,' | cut -d "," -f 1);
echo $pd | grep -oP '\d+' && kill -9 $pd && echo killed || echo nothing;
sleep 120;
