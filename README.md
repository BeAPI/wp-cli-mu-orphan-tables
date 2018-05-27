<a href="https://beapi.fr">![Be API Github Banner](.wordpress.org/banner-github.png)</a>

# BEA - WP-CLI Orphan Tables

A WP-CLI command to remove orphan tables from WordPress Multisite.

## What is the purpose of this command

Sometimes there are orphan tables in your network.
A multisite installation generates a lot of tables (most of the time because of some plugins) so with this command you can list oprhan tables.

This will print SQL DROP statements you might use to remove those useless tables.

## How to use

Like any other WP-CLI command, just run this in a terminal :

    wp orphan tables list

This will prompt DROP statement if orphan tables are found.
But please BE CAREFUL, a drop statement cannot be undone so please make a backup before proceeding.
