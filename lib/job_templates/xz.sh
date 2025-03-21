#!/bin/bash

################################################
#
# Transcode mp4 file to 360p.
# using the helper script of the ScienceData
# MediaCMS installation.
#
################################################
#
# Batch system directives
#
#GRIDFACTORY -i IN_FILE_URL
#GRIDFACTORY -o IN_FILENAME.xz WORK_FOLDER_URL/output_files/IN_FILENAME.xz
#GRIDFACTORY -r UTIL/XZ
#GRIDFACTORY -n XZ-IN_FILENAME
#GRIDFACTORY -s MY_SSL_DN
#
################################################

xz IN_FILENAME