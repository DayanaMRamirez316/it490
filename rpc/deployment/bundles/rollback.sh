#!/bin/bash

ROLLBACK_VER = $1
BUNDLE_PATH=""
PROD_VM="user@prod-ip"
PROD_PATH="/var/www/html/"


if [ -d "BUNDLE_PATH" ]; then
    echo "Reverting Production to $ROLLBACK_VER..."

    rsync  -avz --delete "$BUNDLE_PATH/" "$PROD_VM:$PROD_PATH"

    echo "Rollback to $ROLLBOCK_VER complete."
else
    echo "Error: Version $ROLLBACK_VER complete."
fi