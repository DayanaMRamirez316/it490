#!/bin/bash

archive="deployment/version_1.0.tar"
manifest="deployment/manifest.txt"
tmpdir=$(mktemp -d)

tar -xf "$archive" -C "$tmpdir" "$manifest" || {
    echo "Failed to extract manifest"
    rm -rf "$tmpdir"
    exit 1
}

while IFS='|' read -r archived_file final_path; do
    [ -z "$archived_file" ] && continue

    tar -xf "$archive" -C "$tmpdir" "$archived_file" || exit 1

    mkdir -p "$(dirname "$final_path")" || exit 1
    cp -f "$tmpdir/$archived_file" "$final_path" || exit 1
    echo "looped"
done < "$manifest"

rm -rf "$tmpdir"

echo "all done"
