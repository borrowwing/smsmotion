#!/bin/bash
service tor start &
apache2ctl -D FOREGROUND
