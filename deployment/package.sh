#!/bin/bash 
destDir="/opt/it490/"
cd "$destDir"
find . \( -path "./.git" -o -path "./deployment" \) -prune -o -type f -exec sha256sum --text "{}" \; | sort > ./deployment/newVer.txt

if [[ "$1" != "rollback" ]]; then
    echo not rollback
    join -j 2 \
        <(awk '{ path=substr($0, index($0,$2)); sub(/^\*/, "", path); print $1, path }' deployment/baseVer.txt | sort -k2,2) \
        <(awk '{ path=substr($0, index($0,$2)); sub(/^\*/, "", path); print $1, path }' deployment/newVer.txt | sort -k2,2) \
    | awk '$2 != $3 {print $1}' > deployment/deploy.txt
    comm -13 \
        <(awk '{ path=substr($0, index($0,$2)); sub(/^\*/, "", path); print path }' deployment/baseVer.txt | sort) \
        <(awk '{ path=substr($0, index($0,$2)); sub(/^\*/, "", path); print path }' deployment/newVer.txt | sort) \
    >> deployment/deploy.txt
    sort -u deployment/deploy.txt -o deployment/deploy.txt
else
    awk '{ path=substr($0, index($0,$2)); sub(/^\*/, "", path); print path }' deployment/newVer.txt \
    | sort -u > deployment/deploy.txt
fi

awk '{ gsub(/^\.\//, "", $0); print "./"$0 "|" "/opt/it490/" $0 }' deployment/deploy.txt > deployment/manifest.txt

if [ "${1}" = "rollback" ]; then
    filename="rollback.tar"
    do_transfer=false
else
    filename="version_${1}_$(cat machineInfo.txt).tar"
    do_transfer=true
fi

tar -cvf deployment/"$filename" -T deployment/deploy.txt deployment/manifest.txt

if [[ "$do_transfer" = true ]]; then
    ftp -inv 100.125.53.7 <<EOF 
    user dmr49 Michi100
    binary
    cd deployment
    lcd /opt/it490/deployment
    put "$filename" "$filename"
    bye
EOF
fi

if [[ "$1" != "rollback" ]]; then
    mv deployment/newVer.txt deployment/baseVer.txt
else
    rm -f deployment/newVer.txt
fi

rm -f deployment/deploy.txt