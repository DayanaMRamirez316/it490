#!/bin/bash

#cleanup tempDir and exit out of code
cleanup_and_exit() {
    rm -rf "$tmpdir"
    exit 1
}

#path variables
filename="version_${1}_$(cat machineInfo.txt).tar"
deploydir="/opt/it490/deployment"
archive="$deploydir/$filename"
tmpdir=$(mktemp -d)
manifest="manifest.txt"
manifest_path="deployment/$manifest"

#downloads file
ftp -inv 100.125.53.7 <<EOF 
user dmr49 Michi100
binary
cd deployment
lcd $deploydir
get $filename "$archive"
bye
EOF

#checks for download, exits if failed
[ -f "$archive" ] || {
    echo "FTP failed: $archive not found"
    cleanup_and_exit
}

#extracts manifest from archive, checks if it exists, exits if not
tar -xf "$archive" -C "$tmpdir" "$manifest_path" || {
    echo "Failed to extract manifest"
    cleanup_and_exit
}

while IFS='|' read -r archived_file final_path; do
    [ -z "$archived_file" ] && continue

    tar -xf "$archive" -C "$tmpdir" "$archived_file" || cleanup_and_exit

    mkdir -p "$(dirname "$final_path")" || cleanup_and_exit
    cp -f "$tmpdir/$archived_file" "$final_path" || cleanup_and_exit
    echo "$archived_file : $final_path"
done < "$manifest_path"

rm -rf "$tmpdir"
rm -f "$archive"
echo "all done"
