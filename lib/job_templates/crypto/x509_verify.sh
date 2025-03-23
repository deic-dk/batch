#!/bin/bash

################################################
#
# Verify a file signature with openssl.
# The input file is the file from which the
# signature has been generated. Next to this
# file, the file containing the signature must be
# present and carry the same name plus the extension
# ".sig".
#
################################################
#
# Batch system directives
#
#GRIDFACTORY -i IN_FILE_URL
#GRIDFACTORY -r UTIL/OpenSSL
#GRIDFACTORY -n VERIFY-IN_FILENAME
#GRIDFACTORY -s MY_SSL_DN
#GRIDFACTORY -v MY_SSL_DN
#
################################################

curl --insecure HOME_SERVER_PRIVATE_URL/remote.php/getcert?user=SD_USER | jq -r .data.certificate > usercert.pem

openssl x509 -pubkey -in usercert.pem -nocert > pubkey.pem

# Fetch the signature file
curl --insecure "IN_FILE_URL.sig" > "IN_FILENAME.sig"

openssl dgst -sha256 -verify pubkey.pem -signature "IN_FILENAME.sig" "IN_FILENAME"
