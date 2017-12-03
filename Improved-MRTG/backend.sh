#!/bin/sh


while :
do
	START=$(date +%s)
	perl ./backend.pl
	END=$(date +%s)

	script_time=$(($END-$START))
	sleep_by=$((300 - $script_time))
	echo "==========="
	echo "Script Run time: $script_time"
	echo "==========="
	
	sleep $sleep_by
done
