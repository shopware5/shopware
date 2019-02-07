#!/usr/bin/env bash
#DESCRIPTION: download and unzip the test images

curl -k -L -o test_images.zip "http://releases.s3.shopware.com/test_images_since_5.1.zip"
unzip test_images.zip
rm test_images.zip



