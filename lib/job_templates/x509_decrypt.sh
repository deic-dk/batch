#!/bin/bash

################################################
#
# Decrypt a file encrypted to you with openssl.
# You must run this on a worker owned by yourself
# - as only from such a pod will you be allowed to 
# download your SSL key.
#
################################################
#
# Batch system directives
#
#GRIDFACTORY -i IN_FILE_URL
#GRIDFACTORY -o IN_BASENAME WORK_FOLDER_URL/output_files/IN_BASENAME
#GRIDFACTORY -r UTIL/OpenSSL
#GRIDFACTORY -n DECRYPT-IN_FILENAME
#GRIDFACTORY -s MY_SSL_DN
#GRIDFACTORY -v MY_SSL_DN
#
################################################

curl --insecure HOME_SERVER_PRIVATE_URL/remote.php/getkey | jq -r .data.private_key > userkey_unenc.pem

openssl pkeyutl -decrypt -inkey userkey_unenc.pem -in "IN_FILENAME" -out "IN_BASENAME"
