# Table of Contents
- [How to run](#how-to-run)
- [Overview and Features](#overview-and-features)
- [APIs](#apis)
- [Approaches](#approaches)
- [Query Structures](#query-structures)
- [Response](#response)


## How to run
- cd to the <project_folder> and run `composer install && php artisan serve` (accessible through `http://127.0.0.1:8000/`).


## Overview and Features
- Laravel 12 with MySQL App. to filter jobs based on a complex filter query.
- Migrations, Factories and Seeders are performed to fill DB with data for (`jobs`, `locations`, `categories`, `languages`, `job_location`, `job_category`, `job_language`, `attributes`, `attribute_job`) tables.
- Run `php artisan db:seed --class=JobSeeder` to seed.
- `app/Services/JobFilterService` holds the core logic.
- Custom Pagination is applied.
- Rate Limiting is applied to limit the requests by the user's IP.
- Unit tests are written. (`returns jobs matching the complex filter`, `no data found`, `wrong query structure`)


## APIs
-Validated through a custom request handler `JobSearchRequest` (`filter`, `offset`, and `limit`).<br/>
-Responses are returned using a helper trait `responseHandler()`.
- (GET) `/api/v1/jobs` <br/>
[complex_query_filtering.postman_collection.json](https://github.com/user-attachments/files/19411865/complex_query_filtering.postman_collection.json)


## Approaches
### Two approaches were available:
1. Modify the query string in-place, replace every token to produce a final correct sql query and use it with `whereRaw`.
   - **Example**: Replace `HAS_ANY` to `IN`, Replace `locations` to `locations.city`
   - **Limitation**: Difficult to use eager loading with this approach and not flexible solution.
2. **(Used Approach)** Parse the query string to a nested-objects data structure, each node holds `field`, `operator`, `value`, `logical operator`.
   - Filter string is passed through `customFilterParser()` to process the parse the query groups.
   - Then, `splitConditions()` splits conditions into groups.
   - `parseCondition()` parsing the splitted current condition into a node in the parent array.
   - `customFilterParser()` then returns an array `$filters` containing all the parsed conditions each in a single node.
   - `applyFilters()` is then called with the `$filters` array to get the query prepared.
   - It then handles basic conditions, eager loading conditions, and custom attributes conditions using `applyBasicCondition`, `handleEagerLoadCondition`, `handleAttributeCondition`.
   - Prepared query is then called at the `JobController`.


## Query Structures
1. Query after being parsed<br/>

```
array:3 [
  0 => array:2 [
    "group" => array:2 [
      0 => array:4 [
        "field" => "job_type"
        "operator" => "="
        "value" => "full-time"
        "logical_operator" => null
      ]
      1 => array:2 [
        "group" => array:1 [
          0 => array:4 [
            "field" => "languages"
            "operator" => "HAS_ANY"
            "value" => array:2 [
              0 => "PHP"
              1 => "JavaScript"
            ]
            "logical_operator" => null
          ]
        ]
        "logical_operator" => "AND"
      ]
    ]
    "logical_operator" => null
  ]
  1 => array:2 [
    "group" => array:1 [
      0 => array:4 [
        "field" => "locations"
        "operator" => "IS_ANY"
        "value" => array:2 [
          0 => "New York"
          1 => "Remote"
        ]
        "logical_operator" => null
      ]
    ]
    "logical_operator" => "AND"
  ]
  2 => array:4 [
    "field" => "attribute:years_experience"
    "operator" => ">="
    "value" => "3"
    "logical_operator" => "AND"
  ]
]
```

<br/>

2. Query after being prepared<br/>

```
select `jobs`.* from `jobs` inner join `attribute_job` on `jobs`.`id` = `attribute_job`.`job_id` inner join `attributes` on `attribute_job`.`attribute_id` = `attributes`.`id` where (`job_type` = ? and (exists (select * from `languages` inner join `job_language` on `languages`.`id` = `job_language`.`language_id` where `jobs`.`id` = `job_language`.`job_id` and `name` in (?, ?)))) and (exists (select * from `locations` inner join `job_location` on `locations`.`id` = `job_location`.`location_id` where `jobs`.`id` = `job_location`.`job_id` and `city` in (?, ?))) and `attributes`.`name` = ? and `attribute_job`.`value` >= ?
```

<br/>

**Params:**

```
array:7 [
  0 => "full-time"
  1 => "PHP"
  2 => "JavaScript"
  3 => "New York"
  4 => "Remote"
  5 => "years_experience"
  6 => "3"
]
```
<br/>


## Response

```
{
    "success": true,
    "message": "Successfully found 1 Job",
    "data": [
        {
            "id": 42,
            "title": "Command Control Center Specialist",
            "description": "Sint nesciunt officiis sed non. Dolorum blanditiis natus ut dolor tempore est aspernatur.",
            "company_name": "Balistreri and Sons",
            "salary_min": "41457.00",
            "salary_max": "71291.00",
            "is_remote": 0,
            "job_type": "full-time",
            "status": "archived",
            "published_at": "2025-03-18 10:07:00",
            "created_at": "2025-03-23T14:37:49.000000Z",
            "updated_at": "2025-03-23T14:37:49.000000Z",
            "languages": [
                {
                    "id": 15,
                    "name": "PHP",
                    "created_at": "2025-03-23T14:37:49.000000Z",
                    "updated_at": "2025-03-23T14:37:49.000000Z",
                    "pivot": {
                        "job_id": 42,
                        "language_id": 15,
                        "created_at": "2025-03-23T14:37:49.000000Z",
                        "updated_at": "2025-03-23T14:37:49.000000Z"
                    }
                }
            ],
            "locations": [
                {
                    "id": 23,
                    "city": "Remote",
                    "state": "Florida",
                    "country": "Macedonia",
                    "created_at": "2025-03-23T14:37:49.000000Z",
                    "updated_at": "2025-03-23T14:37:49.000000Z",
                    "pivot": {
                        "job_id": 42,
                        "location_id": 23,
                        "created_at": "2025-03-23T14:37:49.000000Z",
                        "updated_at": "2025-03-23T14:37:49.000000Z"
                    }
                }
            ]
        }
    ]
}
```
