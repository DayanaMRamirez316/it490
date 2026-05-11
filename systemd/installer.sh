#!/bin/bash
# no matter where the script would run, move to the root of the project 
cd "$(dirname "$0")/.."

echo "Reading deployment metadata"

metadataFile="deployment/metadata.json"

#does metadata exist? 
if [ ! -f "$metadataFile" ]; then
    echo "metadata.json not found"
    exit 1
fi
#get the values from JSON 
fileLocation=$(grep 'file_location' "$metadataFile" | cut -d '"' -f 4)
version=$(grep 'version' "$metadataFile" | cut -d '"' -f 4)
echo "Succes in reading the metadata. Deployment file location: $fileLocation"
echo "Version: $version" 

#does tar exitst
if [ ! -f "$fileLocation" ]; then
    echo "package.tar not found."
    exit 1
fi
echo "package.tar found"


tar -xf "$fileLocation" #extract package.tar
echo "Unpacked package.tar"
echo "Deployment done"
