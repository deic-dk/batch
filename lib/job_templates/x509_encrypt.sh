#!/bin/bash

################################################
#
# Encrypt a small file with openssl.
# !! NOTICE: The max size of the input file is 470 bytes. !!
# You can change the user you encrypt the file to
# by replacing SD_USER with the ScienceData username
# of a  user _who has generated a certificate in her
# settings. E.g. some_user@dtu.dk.
#
################################################
#
# Batch system directives
#
#GRIDFACTORY -i IN_FILE_URL
#GRIDFACTORY -o IN_FILENAME.enc WORK_FOLDER_URL/output_files/IN_FILENAME.enc
#GRIDFACTORY -r UTIL/OpenSSL
#GRIDFACTORY -n ENCRYPT-IN_FILENAME
#GRIDFACTORY -s MY_SSL_DN
#
################################################

curl --insecure HOME_SERVER_PRIVATE_URL/remote.php/getcert?user=SD_USER | jq -r .data.certificate > usercert.pem

openssl x509 -pubkey -in usercert.pem -nocert > pubkey.pem

openssl pkeyutl -encrypt -pubin -inkey pubkey.pem -in "IN_FILENAME" -out "IN_FILENAME.enc"
