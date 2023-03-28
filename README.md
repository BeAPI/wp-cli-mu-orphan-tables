# BEA - WP-CLI Orphan Tables

A WP-CLI command to remove orphan tables from WordPress Multisite.

## What is the purpose of this command

Sometimes there are orphan tables in your network.
A multisite installation generates a lot of tables (most of the time because of some plugins) so with this command you can list oprhan tables.

This will print SQL DROP statements you might use to remove those useless tables.

## Installing

Installing this package requires WP-CLI v0.23.0 or greater. Update to the latest stable release with `wp cli update`.

Once you've done so, you can install this package with `wp package install BeAPI/wp-cli-mu-orphan-tables`

## How to use

Like any other WP-CLI command, just run this in a terminal :

    wp orphan tables list

This will prompt DROP statement if orphan tables are found.
But please BE CAREFUL, a drop statement cannot be undone so please make a backup before proceeding.
