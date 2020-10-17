#!/usr/bin/env bash

PATH=/sbin:/usr/sbin:${PATH}

KEY_DIR="/etc/letsencrypt/live"
LOG_FILE="/tmp/letsencrypt-key-changed.log"
CHANGED=""
RESTART_SERVICES="nginx postfix"

NOW=`date +%s`
for FULL_CHAIN in ${KEY_DIR}/*/fullchain.pem; do
	CTIME=`stat -c %Y ${FULL_CHAIN}`
	DIFF=$(($NOW-$CTIME))
	if [ $DIFF -lt 28800 ]; then
		CHANGED=$NOW
	fi
done
if [ -n "$CHANGED" ]; then
	date >> ${LOG_FILE}
	for RESTART_SERVICE in $RESTART_SERVICES; do
		systemctl restart ${RESTART_SERVICE}
	done
fi
