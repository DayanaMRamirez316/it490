#!/bin/bash

command=$1
path="/home/dmr49/deployment/"
archive_prefix="version_${2}"

wrap() {
    tar -cvf "${path}${archive_prefix}.tar" $archive_prefix*
}

unwrap() {
    tar -xf "${path}${archive_prefix}.tar"
}



case "$command" in
    "wrap")
        wrap_packages
        ;;
    "unwrap")
        unwrap
        ;;
    *)
        echo enter an acceptable command. OPTIONS: wrap, unwrap
        exit 1
esac