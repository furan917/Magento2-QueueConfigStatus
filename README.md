# Furan_QueueConfigStatus

A Magento 2 module that provides a command to check if your queue configuration is out of sync with the database.

## Overview

This module adds a `queue:config:status` command that validates whether your queue topology configuration matches what's registered in the database. It helps you determine if you need to run `setup:upgrade` after modifying queue configuration files.

## Why Use This?

When you modify `queue_topology.xml` or `queue_consumer.xml` files in your modules, those changes don't take effect until you run `setup:upgrade`. This command lets you quickly check if your queue configuration is up to date without running the full upgrade process.

## Usage

Check queue configuration status:
```bash
bin/magento queue:config:status
```

**Possible outputs:**

- `Queue Consumer files are up to date.` - No action needed
- `Queue consumer files have changed. Run setup:upgrade command to synchronize queue consumer config.` - You need to run `setup:upgrade`, exit code 2

## Requirements

- PHP 8.0+

## How It Works

The command compares:
1. Queue names defined in your `queue_topology.xml` files across all modules
2. Queue names registered in the `queue` database table

If there are queues in your configuration that don't exist in the database, `setup:upgrade` is required.

## Notes

- This command reads configuration directly from XML files to bypass cache
- Database may contain legacy queue entries from removed modules - this is expected and not flagged as an issue
- Only detects missing queues, not configuration changes to existing queues

