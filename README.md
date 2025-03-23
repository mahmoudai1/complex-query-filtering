# Table of Contents
- [How to run](#how-to-run)
- [Overview](#overview)
- [Approaches](#approaches)
- [Query Structures](#query-structures)
- [APIs](#apis)

## How to run
- cd to the project_folder and run `php artisan serve` (accessible through `http://127.0.0.1:8000/`).

## Overview
- Laravel 12 App. to filter jobs based on a complex filter query.
- Migrations, Factories and Seeders are performed to fill DB with dummy data for (`jobs`, `locations`, `categories`, `languages`, `job_location`, `job_category`, `job_language`, `attributes`, `attribute_job`).

## APIs
The API is validated through a custom request handler. (`filter`, `offset`, and `length`)
- (GET) `/api/v1/jobs`

## Approaches
### Two approaches were available:
1. Modify the query string in-place, replace every token to produce a final correct sql query.
   - Example: Replace `HAS_ANY` to `IN`, Replace `locations` to `locations.city`
   - Limitation: Difficult to use eager loading by this approach.
2. **(Used Approach)** Parse the query string to a nested-objects data structure, each node holds `field`, `operator`, `value`, `logical operator`.
   - Query string is passed through `customFilterParser`() to process the filter string recursively.
   - Then, `customFilterParser` calls `splitConditions`() in order to split conditions groups.


## Query Structures
