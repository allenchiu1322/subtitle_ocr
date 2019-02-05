#!/bin/bash
rm -rfv ./img_ocr/* && mv -v ./img_hit/* ./img/ && mv -v ./img_miss/* ./img/ && ls -1 ./img/ | wc -l
