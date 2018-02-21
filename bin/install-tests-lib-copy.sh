#!/usr/bin/env bash

TMPDIR=${TMPDIR-/tmp}
WP_TESTS_DIR=${WP_TESTS_DIR-$TMPDIR/wordpress-tests-lib}

if [ -d "$WP_TESTS_DIR" ]; then
    echo "Doing: cp -r ${WP_TESTS_DIR} tests/wordpress-tests-lib"
    cd ../ && cp -r $WP_TESTS_DIR tests/wordpress-tests-lib
fi