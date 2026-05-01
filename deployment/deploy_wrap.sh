#!/bin/bash

command=$1
path="/home/dmr49/deployment/"
archive_prefix="version_${2}"

wrap() {
    cd "$path" || exit 1

    packages=( "${archive_prefix}"_*.tar )

    if [[ ! -e "${packages[0]}" ]]; then
        echo "No packages found matching ${archive_prefix}_*.tar"
        exit 1
    fi

    tar -cvf "${archive_prefix}.tar" "${files[@]}"
}

unwrap() {
    cd "$path" || exit 1
    tar -xf "${path}${archive_prefix}.tar" -C "$path"
}



case "$command" in
    "wrap")
        wrap
        ;;
    "unwrap")
        unwrap
        ;;
    *)
        echo enter an acceptable command. OPTIONS: wrap, unwrap
        exit 1
esac
