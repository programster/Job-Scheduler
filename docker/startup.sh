# Please do not manually call this file!
# This script is run by the docker container when it is "run"

/usr/bin/java -jar /root/scheduler/scheduler.jar &

# Start the cron service in the foreground
cron -f

