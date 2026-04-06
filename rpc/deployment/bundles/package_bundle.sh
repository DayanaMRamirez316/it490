#!/bin/bash

VERSION=$1
SOURCE=""
DEST=""

mkdir -p "$DEST/rpc" "$DEST/sampled" "$DEST/sql"

rsync -avz "$SOURCE/rpc/"
rsync -avz "$SOURCE/sample/" "$DEST/sample/"
rsync -avz "$SOURCE/sql/" "$DEST/sql/"

cp "$SOURCE/001-sample.conf" "$DEST/"
cp "$SOURCE/local.inf" "$DEST/"

echo "Bundle $VERSION created successfully at $DEST"