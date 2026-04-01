#!/bin/bash 
cd ..
find . \( -path "./.git" -o -path "./deployment" \) -prune -o -type f -exec sha256sum "{}" \; | sort > deployment/newVer.txt

join -j 2 \
    <(awk '{ path=substr($0, index($0,$2)); sub(/^\*/, "", path); print $1, path }' deployment/baseVer.txt | sort -k2,2) \
    <(awk '{ path=substr($0, index($0,$2)); sub(/^\*/, "", path); print $1, path }' deployment/newVer.txt | sort -k2,2) \
| awk '$2 != $3 {print $1}' > deployment/deploy.txt

comm -13 \
    <(awk '{ path=substr($0, index($0,$2)); sub(/^\*/, "", path); print path }' deployment/baseVer.txt | sort) \
    <(awk '{ path=substr($0, index($0,$2)); sub(/^\*/, "", path); print path }' deployment/newVer.txt | sort) \
>> deployment/deploy.txt

sort -u deployment/deploy.txt -o deployment/deploy.txt

tar -cvf deployment/update.tar -T deployment/deploy.txt

ftp -inv 100.101.227.40 <<EOF
user your_username your_password
binary
put deployment/update.tar
bye
EOF

mv deployment/newVer.txt deployment/baseVer.txt

rm deployment/deploy.txt