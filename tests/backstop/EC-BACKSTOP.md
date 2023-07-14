# EC-BACKSTOP.md
================
Updated by Wilbur, 2022-10-21

# Overview
Use BackstopJS to test the state of your webpages after making development changes in order to help verify intended differences or identify any regressions that may have occurred in the UI.  

Do so by first making screenshots of the existing or "reference" environment. After your development changes are complete, run a test.  This will generate a new set of test bitmaps and compare them to the reference screenshots, finding any visual differences.

# Installation

Requires backstopjs npm package.  

You can install globally using
```npm install -g backstopjs:latest```

Last tested and working on Node version 14.17.0 and NPM version 6.14.13 on 10.10.2022 by Mark. You can use NVM to switch to these versions if yours are different and not working.

# Configuration

## /tests/backstop
This folder contains all the resources for Backstop testing

## /tests/backstop/backdrop.json
Testing URLs and breakpoints are configured here

## /tests/backstop/csbgov.cookies.json
This folder contains the consent cookie files.

## /tests/backstop/backstop_data
Reference and testing data are created in the bitmaps_reference amd bitmap_test folders, and the HTML report is added in html_report folder. 
All these files are git ignored so they are not added to the repo.  

# Backstop Testing Process

## STEP 1: Generate or Re-generate reference bitmaps
1. cd into /tests/backstop/ directory
2. run ```backstop reference``` 

Reference screenshots are saved to /tests/backstop/backstop_data/bitmaps_reference and are not committed to the git repository.

In the event that backstop.json is updated, reference bitmaps need to be re-generated using the ```backstop reference``` command before any future tests can be run.  When reference bitmaps are re-generated, any and all existing reference bitmaps are first wiped clean by Backstop.

## STEP 2: Run a test
1. cd into your project's /tests/backstop/ directory
2. run ```backstop test```

The HTML report UI should open in your default browser allowing you to inspect which pages did or did not change as a result of your work. 

Run as many tests as needed - each test's bitmaps are saved independently in the /tests/backstop/backstop_data/bitmaps_test directory and are not committed in the git repo.

The HTML report is saved to /tests/backstop/backstop_data/html_report/index.html, uses the latest set of test bitmaps (if multiple tests have been run), and is not committed to the git repo.

# NOTES

## Cookie Expirations
/tests/backstop/backstop_data/csbgov.cookies.json contains expiration timestamps that may need to be updated in the future.


## Backstop 
--------
https://github.com/garris/BackstopJS
