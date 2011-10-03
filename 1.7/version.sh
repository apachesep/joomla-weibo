#!/bin/bash
# usage:  ./version.sh outdir versionnum  file1 file2 ....

outdir="$1"
shift
version="$1"
shift 

until [ $# -eq 0 ]
 do
    b=`basename $1`
    echo "sed s/%VERSION%/$version/g $1 > $outdir/$b"
    eval "sed s/%VERSION%/$version/g $1 > $outdir/$b"
    shift
 done
