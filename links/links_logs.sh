#!/bin/bash

# log rotation script
ROOT=/home/jah/links
PARSELOG=$ROOT/parselogs.pl
LOGFILE="#lanfoolz.log"
LOGPATH=/home/jah/.eggdrop/mirclogs
OLDLOGS=oldlogs/
PDIR=${0%`basename $0`}
LCK_FILE=$ROOT/`basename $0`.lck
VAR_LOG=$ROOT/logs/links_logs.log


if [ -f "${LCK_FILE}" ]; then

  # The file exists so read the PID
  # to see if it is still running
  MYPID=`head -n 1 "${LCK_FILE}"`

  TEST_RUNNING=`ps -p ${MYPID} | grep ${MYPID}`

  if [ -z "${TEST_RUNNING}" ]; then

    # The process is not running
    # Echo current PID into lock file
    #echo "Not running"
    echo $$ > "${LCK_FILE}"

  else

    echo "`date` `basename $0` is already running [${MYPID}]" >> $VAR_LOG
    #echo "Already running[${MYPID}]: `date`" >> $VAR_LOG

    exit 0

  fi

else

    #echo "Not running"
    echo $$ > "${LCK_FILE}"
fi

# Move this stuff to a working file in case it takes a long time to process.
# Then we won't wipe the active log if something gets added while we are processing.

if [ -f $LOGPATH/$LOGFILE ]; then

	mv $LOGPATH/$LOGFILE $LOGPATH/$LOGFILE.working.tmp
	#cp $LOGPATH/$LOGFILE $LOGPATH/$LOGFILE.working.tmp

	$PARSELOG $LOGPATH/$LOGFILE.working.tmp &>> $VAR_LOG
	
	cat $LOGPATH/$LOGFILE.working.tmp >> $LOGPATH/$OLDLOGS/$LOGFILE
	rm $LOGPATH/$LOGFILE.working.tmp

fi


    rm -f "${LCK_FILE}"


exit 0
