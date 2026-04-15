#!/bin/bash 
destDir="/opt/it490/"
cd "$destDir"
find . \( -path "./.git" -o -path "./deployment" \) -prune -o -type f -exec sha256sum --text "{}" \; | sort > ./deployment/newVer.txt

join -j 2 \
    <(awk '{ path=substr($0, index($0,$2)); sub(/^\*/, "", path); print $1, path }' deployment/baseVer.txt | sort -k2,2) \
    <(awk '{ path=substr($0, index($0,$2)); sub(/^\*/, "", path); print $1, path }' deployment/newVer.txt | sort -k2,2) \
| awk '$2 != $3 {print $1}' > deployment/deploy.txt

comm -13 \
    <(awk '{ path=substr($0, index($0,$2)); sub(/^\*/, "", path); print path }' deployment/baseVer.txt | sort) \
    <(awk '{ path=substr($0, index($0,$2)); sub(/^\*/, "", path); print path }' deployment/newVer.txt | sort) \
>> deployment/deploy.txt

sort -u deployment/deploy.txt -o deployment/deploy.txt

awk '{ gsub(/^\.\//, "", $0); print "./"$0 "|" "/opt/it490/" $0 }' deployment/deploy.txt > deployment/manifest.txt

tar -cvf deployment/"version_${1}".tar -T deployment/deploy.txt deployment/manifest.txt

ftp -inv insert deploy server ip <<EOF 
user your_username your_password
binary
put deployment/update.tar
bye
EOF

# metadata stuff

echo "Metadata Construction"

echo "metadata.json created with version: $currentVersion"

mv deployment/newVer.txt deployment/baseVer.txt

rm -f deployment/deploy.txt