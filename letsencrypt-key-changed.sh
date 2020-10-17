#!/usr/bin/env bash

PATH=/sbin:/usr/sbin:${PATH}

RESTART_SERVICES="nginx postfix"

# Old: Check if key changed, executed by cron job
#KEY_DIR="/etc/letsencrypt/live"
#LOG_FILE="/tmp/letsencrypt-key-changed.log"
#CHANGED=""
#NOW=`date +%s`
#for FULL_CHAIN in ${KEY_DIR}/*/fullchain.pem; do
#	CTIME=`stat -c %Y ${FULL_CHAIN}`
#	DIFF=$(($NOW-$CTIME))
#	if [ $DIFF -lt 28800 ]; then
#		CHANGED=$NOW
#	fi
#done
#if [ -n "$CHANGED" ]; then
#	date >> ${LOG_FILE}
#	for RESTART_SERVICE in $RESTART_SERVICES; do
#		systemctl restart ${RESTART_SERVICE}
#	done
#fi

# New: Certbot hooked scripts will be executed if any key was changed
for RESTART_SERVICE in $RESTART_SERVICES; do
	systemctl restart ${RESTART_SERVICE}
done
