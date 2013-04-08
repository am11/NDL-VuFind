#!/bin/bash

set -e
set -x

cd "`dirname $0`/import"
CLASSPATH="browse-indexing.jar:../solr/lib/*"

bib_index="../solr/biblio/index"
auth_index="../solr/authority/index"
index_dir="../solr/alphabetical_browse"

mkdir -p "$index_dir"

function build_browse
{
    browse=$1
    field=$2
    skip_authority=$3

    extra_jvm_opts=$4

    if [ "$skip_authority" = "1" ]; then
        java ${extra_jvm_opts} -Dfile.encoding="UTF-8" -Dfield.preferred=heading -Dfield.insteadof=use_for -cp $CLASSPATH PrintBrowseHeadings "$bib_index" "$field" "${browse}.tmp"
    else
        java ${extra_jvm_opts} -Dfile.encoding="UTF-8" -Dfield.preferred=heading -Dfield.insteadof=use_for -cp $CLASSPATH PrintBrowseHeadings "$bib_index" "$field" "$auth_index" "${browse}.tmp"
    fi

    sort -T /var/tmp -u -t$'\1' -k1 -k3 "${browse}.tmp" -o "sorted-${browse}.tmp"
    java -Dfile.encoding="UTF-8" -Xmx1024m -cp $CLASSPATH CreateBrowseSQLite "sorted-${browse}.tmp" "${browse}_browse.db"

#    rm -f *.tmp

    mv "${browse}_browse.db" "$index_dir/${browse}_browse.db-updated"
    touch "$index_dir/${browse}_browse.db-ready"
}
#build_browse "hierarchy" "hierarchy_browse"
build_browse "title" "title_fullStr" 1 "-Dbibleech=StoredFieldLeech -Dsortfield=title_sort -Dvaluefield=title_fullStr -Dfilterfield=building:source_str_mv"
build_browse "topic" "topic_browse" 1 "-Dbibleech=StoredFieldLeech -Dsortfield=topic -Dvaluefield=topic -Dfilterfield=building:source_str_mv"
build_browse "author" "author_browse" 1 "-Dbibleech=StoredFieldLeech -Dsortfield=author:author2 -Dvaluefield=author:author2 -Dfilterfield=building:source_str_mv"
#build_browse "lcc" "callnumber-a" 1 "-Dsortfield=callnumber-a -Dvaluefield=callnumber-a -Dbuildfield=building:source_str_mv"
#build_browse "dewey" "dewey-raw" 1 "-Dbibleech=StoredFieldLeech -Dsortfield=dewey-sort -Dvaluefield=dewey-raw -Dbuildfield=building:source_str_mv"
