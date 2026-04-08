#!/bin/bash

VERSION=$1
SOURCE="$HOME/git/it490"
DEST="$HOME/deployment/bundles/$VERSION"

mkdir -p "$DEST/rpc" "$DEST/sample" "$DEST/sql"


rsync -avz "$SOURCE/rpc/" "$DEST/rpc/"
rsync -avz "$SOURCE/sample/" "$DEST/sample/"
rsync -avz "$SOURCE/sql/" "$DEST/sql/"

cp "$SOURCE/001-sample.conf" "$DEST/"
cp "$SOURCE/local.inf" "$DEST/"

echo "Bundle $VERSION created successfully at $DEST"