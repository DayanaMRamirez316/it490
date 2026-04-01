#!/bin/bash 
cd ..
find . \( -path "./.git" -o -path "./deployment" \) -prune -o -type f -exec sha256sum "{}" \; | sort > deployment/newVer.txt

join -j 2 <(awk '{print $1, $2}' deployment/baseVer.txt | sort -k2,2) \
          <(awk '{print $1, $2}' deployment/newVer.txt | sort -k2,2) \
| awk '$2 != $3 {print $1}' > deployment/deploy.txt

comm -13 <(awk '{print $2}' deployment/baseVer.txt | sort) \
         <(awk '{print $2}' deployment/newVer.txt | sort) >> deployment/deploy.txt

tar -cvf deployment/update.tar -T deployment/deploy.txt