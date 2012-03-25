#!/bin/bash

# Benennt alle PHP Dateien in PHPS um

while IFS="\n" read L; do mv "$L" "${L}s" ; done <<< "$(find . -name \*.php)"
