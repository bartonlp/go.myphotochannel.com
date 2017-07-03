#! /bin/bash
# host: db459221984.db.1and1.com
# user: dbo459221984
# password: akipoo94
# database: db459221984
# Backup the database before starting.
# I create a file TB_BACKUP.sql which can be used to create a new database
# Day of week Mon-Sun
dir=/kunden/homepages/45/d454707514/htdocs/other
bkupdate=`date +%B-%d-%y`
filename="TP_BACKUP.$bkupdate.sql"
mysqldump --add-drop-table --no-create-db=true -h db459221984.db.1and1.com -u dbo459221984 -pakipoo94 db459221984 > $dir/$filename
gzip $dir/$filename

